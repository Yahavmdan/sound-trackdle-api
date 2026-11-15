# Soundtrack Pipeline + Answer-Leak Hardening — Design

Date: 2026-11-15
Repos: `sound-trackdle-api` (Laravel 10), `sound-trackdle` (Angular)

## Intent

Sound-trackdle is a daily "guess the movie from its soundtrack" game. Today the pool is 159
hand-seeded movies, with clips uploaded manually as a zip. The owner wants to **significantly
grow the number of playable tracks** so the game repeats less and stays challenging, without
hand-curating every clip.

Second goal, discovered during recon: the API currently **leaks the answer** to anyone who opens
DevTools. Fixing that is in scope, because a bigger pool is pointless if the answer is readable.

## Goals

- An automated, re-runnable pipeline that: picks movies, finds each movie's main theme on
  YouTube, cuts the most recognizable 15 s, lets the owner approve clips, then uploads them to a
  Railway bucket and registers them in the DB.
- First run target: ~500 playable movies (configurable `--target`).
- No way to learn today's answer from API responses, file URLs or localStorage before the game
  ends.

## Non-goals

- Difficulty levels / difficulty tagging (explicitly deferred by owner).
- "Guess the soundtrack" compilation videos as a source (approach B — maybe later).
- Running the pipeline on Railway / on a schedule. It runs locally on the owner's PC.
- TV shows / games (`type` stays `movie`).
- Copyright handling (owner's decision; clips are ≤15 s).

## Anti-goals (failure even if it "works")

- Clips that are the wrong song, a trailer, a cover, a reaction video, or mostly silence/dialogue.
  This is why there is a human review step.
- Clips whose filename, URL or API payload reveals the movie.
- Breaking the existing game for current players (daily rotation, archive of last 9 days,
  hints).

## How it works today (relevant facts)

- `files` table: `id, name (unique), type, year, file_path, main_actor, genre, played_at`.
- `GET file` → today's track `{id, main_actor, year, genre}`: first row with `file_path` and
  `played_at` null or ≥ today; when all are played, recycles the oldest-played.
- `POST stream {id}` → `{path}` public URL of `storage/app/public/tracks/{id}_{name}.mp3`
  (local disk, `public` disk). Sets `played_at` on first stream.
- `GET movie` → all `{id, name}` for the autocomplete; frontend caches it in localStorage.
- `POST file/id {id}` → full row incl. `name`; unauthenticated.
- `GET files` → last 9 played tracks (archive).
- `POST upload` (auth) → zip of `{id}_name.mp3`, extracted to local disk.
- Frontend checks answers **client-side**: `input.value === file.id` (selected autocomplete id
  vs today's id). Player reveals 5 / 9 / 12 / 15 s across 4 steps; hints (actor, year, genre)
  are shown on wrong guesses; after step 4 it calls `file/id` to show the name.

### Leaks

1. `GET file` id + `GET movie` id→name map ⇒ answer.
2. `stream` URL contains `{id}_{name}.mp3`.
3. `file/id` returns `name` at any time, no auth.
4. Client-side answer check means the correct id must be on the client.

## Approaches considered (for sourcing clips)

| | A. Per-movie search (**chosen**) | B. Compilation "guess the soundtrack" videos |
|---|---|---|
| How | Movie list from TMDB → `yt-dlp ytsearch` per movie → pick segment → cut | Download compilations, split by chapters/timestamps |
| Pros | Scales to hundreds+, metadata (year/actor/genre) automatic, one clean track per movie | Iconic moments pre-picked, many clips per video |
| Cons | Need search filtering + segment picking + review | Answers often only on screen, voiceover/countdown overlap, heavy duplication |

Owner picked **A**. B may be added later as a secondary source.

## Design

### 1. Pipeline (new): `sound-trackdle-api/tools/track-pipeline/`

Python 3.11+ CLI, run locally. Deps: `yt-dlp`, `ffmpeg` (binary on PATH), `requests`, `boto3`,
`numpy` (loudness fallback). Config via `.env` (git-ignored): `TMDB_API_KEY`, `S3_ENDPOINT`,
`S3_BUCKET`, `S3_ACCESS_KEY`, `S3_SECRET_KEY`, `API_BASE_URL`, `API_TOKEN`.

State lives in a local SQLite file `pipeline.db` (one row per candidate movie with `status`:
`new → downloaded → cut → approved | rejected → published`, plus `tmdb_id`, chosen YouTube
`video_id`, `clip_start`, failure reason). Every stage is idempotent and resumable; re-running
skips done work.

Stages (subcommands):

1. **`discover`** — TMDB `/discover/movie` sorted by popularity, `vote_count >= 2000`
   (configurable), paged until `--target` candidates beyond the movies already in the API.
   For each: `/movie/{id}?append_to_response=credits` → title, release year, top-billed cast
   member (`main_actor`), genres joined with ", " (`genre`). Skip movies already in the API
   (match by `tmdb_id`, else by normalized name + year).
   Existing 159 seeded movies with no clip are also queued (matched to TMDB by name + year).
2. **`fetch`** — per movie `yt-dlp "ytsearch8:{title} {year} main theme soundtrack"`
   (metadata only), score results and download the best one's audio:
   - reject duration < 60 s or > 12 min, titles containing `trailer|reaction|cover|piano
     tutorial|remix|1 hour|10 hours|8d|slowed|ranking|top 10`
   - require fuzzy title match (normalized movie title in video title)
   - boost: channel ends with `- Topic`, verified/official channels, words `main theme|theme|
     suite|opening`, view count (log)
   - download `bestaudio`, convert to mp3 via ffmpeg. Keep the yt-dlp `heatmap` from metadata.
3. **`cut`** — choose `clip_start`:
   - if `heatmap` exists: centre of the highest-intensity window of 15 s, start shifted to
     ≥ 0 and so the clip fits inside the track;
   - else: RMS loudness over 1 s frames, start of the 15 s window with the highest mean RMS,
     ignoring the first 10 s.
   - ffmpeg: `-ss {start} -t 15`, 0.3 s fade-in, 0.5 s fade-out, `loudnorm` to −14 LUFS,
     mp3 128 kbps mono-or-stereo 44.1 kHz. Output `out/{tmdb_id}.mp3`.
4. **`review`** — starts a local web page (stdlib `http.server`) listing cut clips: movie
   title, year, YouTube link, `<audio>` player, buttons **Approve / Reject / Re-cut at
   mm:ss / Try next video**. Decisions write to `pipeline.db`. "Try next video" re-runs fetch
   with the next-ranked result; "Re-cut" re-runs cut with the given start.
5. **`publish`** — for each approved clip:
   - object key `tracks/{random 16-hex}.mp3` (random, not derived from id/name)
   - upload to the Railway bucket via boto3 (S3 API)
   - call the API admin endpoint (below) in batches of 50 to create/update rows
   - mark `published`.
6. **`run`** — convenience: discover → fetch → cut, then prints "open review".

### 2. Storage: Railway bucket

- Add `league/flysystem-aws-s3-v3` to the API; configure the `s3` disk with
  `AWS_ENDPOINT`, `AWS_USE_PATH_STYLE_ENDPOINT=true`, bucket creds from Railway.
- New config `TRACKS_DISK` (default `s3`). All track reads/writes go through it.
- `stream` returns `Storage::disk(TRACKS_DISK)->temporaryUrl($path, now()->addMinutes(30))`
  (works with private buckets; no stable public URL).
- **Migration of existing clips:** one-off artisan command `tracks:migrate-to-bucket` copies each
  existing `storage/app/public/tracks/*` file to the bucket under a random key and updates
  `file_path`.
- Old zip `upload` endpoint: kept but rewritten to write to `TRACKS_DISK` with random keys.

### 3. API changes (`sound-trackdle-api`)

Migration on `files`:
- add `tmdb_id` (unsigned int, nullable, unique)
- add `source_url` (string, nullable) and `clip_start` (decimal, nullable) — provenance, for
  re-cutting later
- drop unique on `name`, add unique on (`name`, `year`) — TMDB has remakes (e.g. The Lion King
  1994 / 2019).

New endpoint (auth:sanctum, ability:user):
- `POST admin/tracks/bulk` — body `[{tmdb_id, name, year, main_actor, genre, file_path,
  source_url, clip_start}]`; upsert by `tmdb_id`, else by (`name`, `year`); returns created /
  updated counts.

Leak hardening:
- **Daily token instead of id.** `GET file` returns `{token, main_actor, year, genre}` where
  `token` is an opaque random string stored on the row (new column `token`, char(32),
  generated on insert / backfilled by migration). The movie id never leaves the server
  before the game ends.
- **`POST stream {token}`** — looks up by token. Same `played_at` behaviour.
- **`POST guess {token, movie_id, step}`** — returns `{correct: bool}`; when `correct` or
  `step >= 4`, also returns `{name, year}` (the reveal). Answer check moves server-side.
- **`GET movie`** — returns `{id, name, year}` for autocomplete. Ids here are fine because
  today's id is no longer exposed.
- **`POST file/id`** — removed (replaced by `guess` reveal). Archive uses tokens too.
- **`GET files`** (archive) — returns `{token, main_actor, year, genre, played_at}` instead of
  id.
- Rate limit `guess` (e.g. `throttle:30,1`) so it can't be brute-forced across all movie ids.
  The server cannot know a client's step honestly, so additionally: a reveal for step ≥ 4 is
  only returned when the request's `step` is 4 and at least 4 guesses for that token were made
  from that IP in the last 24 h (counted in cache). Good enough for a hobby game; not a
  security boundary.

Daily rotation ordering: new rows are inserted in a shuffled order by the pipeline, so new
tracks don't play in popularity order. `getFile` gets an explicit `orderBy('id')` (it relies on
implicit order today).

### 4. Frontend changes (`sound-trackdle`)

- `File` type: `id` → `token` for the daily/archived track; movie list items keep `id`.
- `_checkAnswer` calls `POST guess` instead of comparing ids; on wrong answer advance step as
  now; on step 4 use the reveal from the `guess` response instead of `file/id`.
- Archive (play a past track by clicking it) uses `token`.
- Autocomplete shows `Name (year)` when two movies share a name.
- Bump `version.ts` so cached `movies` in localStorage are refreshed (existing version check).

### 5. Rollout order

1. API: S3 disk + bucket config + `tracks:migrate-to-bucket`; deploy; run migration command.
   Game keeps working (same endpoints).
2. API + frontend together: token / guess hardening (breaking API change — deploy API first
   with old endpoints kept one release, then frontend, then remove old endpoints).
3. API: schema columns + `admin/tracks/bulk`.
4. Pipeline: build stages; first run with `--target 50` to tune filters; then full run.

## Constraints

- Clip length exactly 15 s (player uses 5/9/12/15).
- Railway bucket is S3-compatible; credentials from Railway dashboard (owner provides).
- Local Windows PC: ffmpeg + Python must be installed; yt-dlp may need updating when YouTube
  changes (`yt-dlp -U`).
- TMDB API key (free) — owner creates one.

## Decisions settled during review (designer's calls — owner may override)

- **Skip (hold-to-skip) counts as a guess.** The frontend calls `POST guess {token,
  movie_id: null, step}`; server treats it as a wrong guess and counts it toward the 4 needed
  for the reveal.
- **Existing 159 seeded rows keep their hand-written name / actor / genre.** Matching to TMDB
  (normalized name + year, then year ±1) only fills `tmdb_id`. Rows that don't match are
  listed in the review page under "unmatched" with a TMDB search box to map them by hand;
  they are never auto-duplicated. `discover` also skips TMDB movies whose normalized title
  contains an existing row's name with the same year (e.g. "Harry Potter" 2001).
- **Rows that already have a clip are left alone** by the pipeline. Only clip-less rows and new
  movies get pipeline clips.
- **"No clip" = `file_path` null or `''`.** All playable filters (`getFile`, `getRecentFiles`)
  change to `whereNotNull('file_path')->where('file_path', '!=', '')`; a migration normalizes
  `''` to null.
- **Version bump** in both `src/environments/version.ts` and `AppServiceProvider::appVersion()`.

## Left to the builder

- Exact scoring weights in `fetch`; exact heatmap window maths.
- Review page layout (functional, no design polish needed).
- Test strategy: unit tests for scoring/segment picking and API endpoints; pipeline stages
  tested against a few fixed movies.
- Whether `tools/track-pipeline` gets its own README / Makefile.
