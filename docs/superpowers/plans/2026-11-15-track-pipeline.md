# Soundtrack Pipeline + Leak Hardening Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Automatically add soundtrack clips (first: 10 for a local test) and stop the API from leaking the daily answer.

**Architecture:** Laravel API gets new columns, an admin clip-upload endpoint and token/guess endpoints; Angular frontend checks answers via `POST guess`; a local Python CLI (`tools/track-pipeline`) discovers movies, finds themes on YouTube, cuts 15 s clips, offers a review page, then publishes to the API.

**Tech Stack:** Laravel 10 / PHP 8.3 (docker `composer:2.7.7`, SQLite locally), Angular 17, Python 3.12, yt-dlp, ffmpeg, Wikidata SPARQL.

**Spec:** `docs/superpowers/specs/2026-11-15-track-pipeline-design.md`

## Global Constraints

- Clip length exactly 15 s; fade-in 0.3 s, fade-out 0.5 s, `loudnorm` I=-14, mp3 128 kbps 44.1 kHz.
- Stored clip names: `tracks/{16 random hex}.mp3` — never derived from id or movie name.
- A movie is "playable" only when `file_path` is not null and not `''`.
- New movies must not duplicate any existing movie (local DB **and** production `GET /api/movie`).
- Seeded rows keep hand-written `name`, `main_actor`, `genre`.
- Nothing is pushed or deployed to production in this plan; everything runs locally.

## Deviation from spec (owner informed)

- Movie discovery uses **Wikidata SPARQL** (no API key) instead of TMDB, because no TMDB key is available. Wikidata provides title, year, cast, genres and the TMDB id (P4947), so `tmdb_id` is still filled. Popularity = Wikipedia sitelink count.

## Review Focus

- Movie titles that differ only in punctuation/case/leading "The" from an existing one ("Spider-Man: Homecoming" vs "spider man homecoming") must count as existing — test in Task 5.
- Franchise titles where the seeded name is a prefix ("Harry Potter" 2001 vs "Harry Potter and the Philosopher's Stone") must count as existing — test in Task 5.
- YouTube results with no heatmap must still yield a valid clip start inside the track — test in Task 6.
- Track shorter than 15 s + offset must be rejected, not cut short — test in Task 6.
- Guessing with an unknown/expired token returns 404, never 500 — test in Task 3.

---

### Task 1: Schema + playable fix + random names

**Files:**
- Create: `database/migrations/2026_11_15_000001_add_pipeline_columns_to_files_table.php`
- Create: `app/Console/Commands/RandomizeTrackNames.php`
- Modify: `app/Models/File.php`, `app/Helpers/Helpers.php`, `app/Http/Controllers/FileController.php`
- Test: `tests/Feature/TracksTest.php`

**Interfaces — Produces:**
- columns `tmdb_id` (unsigned int, nullable, unique), `source_url` (nullable string), `clip_start` (nullable decimal 8,2), `token` (char 32, unique, filled for all rows); unique(`name`,`year`) replaces unique(`name`); `''` file_path → null.
- `File::booted()` generates `token = Str::random(32)` on create.
- `File::scopePlayable($q)`.
- `Helpers::randomTrackPath(): string` → `tracks/{16 hex}.mp3`.
- artisan `tracks:randomize-names` (skips files matching `/^[0-9a-f]{16}\.mp3$/`).

- [ ] Test: `test_playable_excludes_empty_path`, `test_token_generated_on_create`, `test_randomize_names_renames_and_updates_path` (fake `public` disk). Run `php artisan test --filter TracksTest` → FAIL.
- [ ] Implement; `getFile` adds `orderBy('id')`; `getFile`/`getRecentFiles` use `playable()`; zip `upload` stores with random names.
- [ ] Run → PASS. Commit `feat: pipeline columns, playable scope, random track names`.

### Task 2: `POST admin/tracks`

**Files:** Create `app/Http/Controllers/AdminTrackController.php`, `app/Http/Requests/File/StoreTrackRequest.php`; Modify `routes/api.php`; Test `tests/Feature/AdminTrackTest.php`.

**Interfaces — Produces:** `POST api/admin/tracks` (auth:sanctum, ability:user), multipart `file` (mp3, max 2048 KB), `tmdb_id?`, `name`, `year`, `main_actor`, `genre`, `source_url?`, `clip_start?` → `{id:int, created:bool}`. Upsert by `tmdb_id`, else (`name`,`year`); on existing row only `tmdb_id`, `file_path`, `source_url`, `clip_start` are set.

- [ ] Tests: `test_creates_new_track`, `test_existing_row_keeps_hand_written_fields`, `test_requires_auth`. FAIL → implement → PASS. Commit.

### Task 3: Token + guess hardening (API)

**Files:** Modify `app/Http/Controllers/FileController.php`, `routes/api.php`; Create `app/Http/Controllers/GuessController.php`; Test `tests/Feature/GuessTest.php`.

**Interfaces — Produces:**
- `GET file` → `{token, main_actor, year, genre}`; `GET files` → `[{token, main_actor, year, genre, played_at}]`
- `POST stream {token}` → `{path}`; 404 on unknown token.
- `POST guess {token, movie_id|null, step:int 0..4}` (throttle 30/min) → `{correct:bool}` plus `{name, year}` when correct, or when `step >= 4` and ≥ 4 guesses for that token+IP were counted in cache (24 h). 404 unknown token.
- `GET movie` → `[{id, name, year}]`. `POST file/id` removed.

- [ ] Tests: `test_file_hides_id`, `test_correct_guess_reveals`, `test_wrong_guess_hides_name`, `test_reveal_after_four_guesses`, `test_no_reveal_step4_without_guesses`, `test_unknown_token_404`. FAIL → implement → PASS. Commit.

### Task 4: Frontend guess flow

**Files:** Modify `src/app/shared/types/file.type.ts`, `src/app/shared/services/file.service.ts`, `src/app/components/home/home.component.ts` (+ html if it reads `file.id`/`file.name`), `src/environments/version.ts`; local-only `src/environments/environment*.ts` (git-ignored).

**Interfaces — Consumes:** Task 3 endpoints. **Produces:** `FileService.guess(token, movieId|null, step): Observable<{correct:boolean; name?:string; year?:number}>`.

- [ ] `_checkAnswer` → `guess`; wrong → step++ as today; step 4 / skip (`movie_id: null`) uses the reveal from `guess`; archive replay uses `token`; autocomplete shows `Name (year)` when a name repeats. Version `1.1.0` in `version.ts` and `AppServiceProvider::appVersion()`.
- [ ] Verify `npx ng build` succeeds. Commit.

### Task 5: Pipeline — state, existing check, discovery

**Files:** Create under `tools/track-pipeline/`: `pipeline/config.py`, `pipeline/state.py`, `pipeline/names.py`, `pipeline/discover.py`, `cli.py`, `requirements.txt`, `.env.example`, `.gitignore`; tests `tests/test_names.py`.

**Interfaces — Produces:**
- `names.normalize(title:str) -> str` (lowercase, `&`→and, strip accents/punctuation, collapse spaces, drop leading "the ").
- `names.is_existing(title:str, year:int|None, existing:list[tuple[str,int|None]]) -> bool` — equal normalized, or existing normalized name is a whole-word prefix of the candidate with year equal or unknown.
- `state.State(path)` sqlite `candidates(qid PK, tmdb_id, title, year, main_actor, genre, status, video_id, source_url, clip_start, clip_path, reason, rank)`.
- `discover.discover(limit:int, existing) -> list[Candidate]` — Wikidata SPARQL films (Q11424) with ≥ 60 sitelinks, year ≥ 1970, ordered by sitelinks desc; `main_actor` = first cast member (P161) by statement order; genre labels without " film", max 3.
- `cli.py discover --target N` pulls existing names from local API and production `GET /api/movie`.

- [ ] Tests for `normalize`/`is_existing` incl. Review Focus cases. FAIL → implement → PASS. Commit.

### Task 6: Pipeline — fetch, segment, cut

**Files:** Create `pipeline/search.py`, `pipeline/segment.py`, `pipeline/cut.py`; tests `tests/test_search.py`, `tests/test_segment.py`.

**Interfaces — Produces:**
- `search.score(video:dict, title:str, year:int|None) -> float|None` (None = rejected per spec filters: duration 60–720 s, banned words, title must contain normalized movie title).
- `segment.pick_start(duration:float, heatmap:list[dict]|None, rms:list[float]|None, clip=15.0) -> float` — heatmap: max mean value over 15 s window, starts ≥ 5 s; else max mean RMS (1 s frames) ignoring first 10 s; raises `ValueError` if `duration < clip + 1`.
- `cut.cut(src, start, dst)` ffmpeg per Global Constraints.
- `cli.py fetch`, `cli.py cut`.

- [ ] Tests incl. no-heatmap and too-short Review Focus cases. FAIL → implement → PASS. Commit.

### Task 7: Pipeline — review + publish

**Files:** Create `pipeline/review.py`, `pipeline/publish.py`.

**Interfaces — Produces:** `cli.py review` (http://localhost:8765: list cut clips with audio, Approve / Reject / Re-cut mm:ss / Next video); `cli.py publish` (login `POST user/login`, then `POST admin/tracks` per approved clip, mark `published`).

- [ ] Manual verification: review page plays a clip; publish to local API creates rows. Commit.

### Task 8: Run 10 + local stack

- [ ] `cli.py discover --target 10`, `fetch`, `cut`, owner reviews, `publish` to local API.
- [ ] Run API (`php artisan serve` in docker, port 8000) and frontend (`ng serve`, port 4200) for the owner to test.
