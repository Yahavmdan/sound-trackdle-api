"""Pipeline stages: fetch -> cut -> publish."""
import json
import os
import shutil
import subprocess
import urllib.error

from . import media
from .discover import fill_posters, lookup
from .http import get_json, post_json, post_multipart
from .search import rank
from .segment import pick_start
from .state import Candidate, State


def existing_movies(api_urls: list[str]) -> list[tuple[str, int | None]]:
    """(name, year) of every movie already in the game, from all given APIs (local + production)."""
    seen: dict[str, int | None] = {}
    for url in api_urls:
        for m in get_json(url.rstrip("/") + "/movie"):
            name = m["name"]
            if seen.get(name) is None:
                seen[name] = m.get("year")
    return list(seen.items())


def fetch(state: State, c: Candidate, work_dir: str, log=print) -> None:
    tried = set(filter(None, c.tried_videos.split(",")))
    query = f"{c.title} {c.year or ''} main theme soundtrack"
    results = [v for v in rank(media.search(query), c.title, c.year) if v["id"] not in tried]
    if not results:
        c.status, c.reason = "failed", "no suitable YouTube result"
        state.save(c)
        log(f"  x {c.title}: {c.reason}")
        return
    for best in results[:3]:  # a video can fail to download (blocked/region); try the next ones
        try:
            info = media.video_info(best["id"])
            audio = media.download_audio(best["id"], os.path.join(work_dir, "audio"))
        except subprocess.CalledProcessError:
            c.tried_videos = ",".join(filter(None, [c.tried_videos, best["id"]]))
            log(f"  ! {c.title}: download failed for {best['id']}, trying next")
            continue
        c.video_id = best["id"]
        c.source_url = f"https://www.youtube.com/watch?v={best['id']}"
        c.heatmap = json.dumps(info.get("heatmap") or [])
        c.audio_path = audio
        c.duration = media.duration(audio)
        c.status, c.reason = "fetched", None
        state.save(c)
        log(f"  + {c.title}: {best.get('title')} ({best.get('channel')})")
        return
    c.status, c.reason = "failed", "downloads failed for the top results"
    state.save(c)
    log(f"  x {c.title}: {c.reason}")


def _remove(path: str | None) -> None:
    if path and os.path.exists(path):
        os.remove(path)


def cleanup(state: State, work_dir: str, log=print) -> None:
    """Free disk: full tracks once cut, clips once published/rejected, and the HTTP cache."""
    freed = 0
    for c in state.all():
        if c.status != "fetched" and c.audio_path:
            freed += os.path.getsize(c.audio_path) if os.path.exists(c.audio_path) else 0
            _remove(c.audio_path)
            c.audio_path = None
            state.save(c)
        if c.status in ("published", "rejected", "failed") and c.clip_path:
            freed += os.path.getsize(c.clip_path) if os.path.exists(c.clip_path) else 0
            _remove(c.clip_path)
            c.clip_path = None
            state.save(c)
    known = {c.audio_path for c in state.all() if c.audio_path} | {c.clip_path for c in state.all() if c.clip_path}
    for sub in ("audio", "clips"):
        d = os.path.join(work_dir, sub)
        for name in os.listdir(d) if os.path.isdir(d) else []:
            path = os.path.join(d, name)
            if path not in known:
                freed += os.path.getsize(path)
                os.remove(path)
    cache = os.path.join(work_dir, "http-cache")
    if os.path.isdir(cache):
        freed += sum(os.path.getsize(os.path.join(r, f)) for r, _, fs in os.walk(cache) for f in fs)
        shutil.rmtree(cache)
    log(f"cleanup: freed {freed / 1e6:.0f} MB")


def cut(state: State, c: Candidate, work_dir: str, start: float | None = None, log=print) -> None:
    """Cut the clip, then delete the full track (re-downloaded on demand for a re-cut)."""
    try:
        if not (c.audio_path and os.path.exists(c.audio_path)):
            c.audio_path = media.download_audio(c.video_id, os.path.join(work_dir, "audio"))
        if start is None:
            heatmap = json.loads(c.heatmap or "[]")
            rms = None if heatmap else media.rms_per_second(c.audio_path)
            start = pick_start(c.duration, heatmap, rms)
        c.clip_path = os.path.join(work_dir, "clips", f"{c.qid}.mp3")
        media.cut(c.audio_path, start, c.clip_path)
        c.clip_start, c.status, c.reason = round(start, 2), "cut", None
    except ValueError as e:
        c.status, c.reason = "failed", str(e)
    _remove(c.audio_path)
    c.audio_path = None
    state.save(c)
    log(f"  {'~' if c.status == 'cut' else 'x'} {c.title}: start {c.clip_start} {c.reason or ''}")


def next_video(state: State, c: Candidate, work_dir: str, log=print) -> None:
    """Reject the current video and try the next-ranked one."""
    c.tried_videos = ",".join(filter(None, [c.tried_videos, c.video_id or ""]))
    fetch(state, c, work_dir, log)
    if c.status == "fetched":
        cut(state, c, work_dir, log=log)


def _login(api: str, username: str, password: str) -> dict:
    login = post_json(api + "/user/login", {"username": username, "password": password})
    token = login.get("token")
    if not token:
        raise SystemExit(f"login failed: {login.get('message')}")
    return {"Authorization": f"Bearer {token}"}


def backfill(api_url: str, username: str, password: str, skip_names: set[str] | None = None, log=print) -> int:
    """Set difficulty / poster / IMDb on movies already in the API (metadata only, no clip)."""
    api = api_url.rstrip("/")
    headers = _login(api, username, password)
    done = 0
    for m in get_json(api + "/movie"):
        if skip_names and m["name"] in skip_names:
            continue
        found = lookup(m["name"], m.get("year"))
        if not found:
            log(f"  ? {m['name']} ({m.get('year')}): not found on Wikipedia")
            continue
        c, ent = found
        fill_posters([found])
        fields = {"name": m["name"], "year": m.get("year") or c.year, "main_actor": "-", "genre": "-",
                  "tmdb_id": c.tmdb_id, "difficulty": c.difficulty, "poster_url": c.poster_url, "imdb_id": c.imdb_id}
        try:
            post_multipart(api + "/admin/tracks", fields, None, None, headers)
            done += 1
            log(f"  = {m['name']}: {c.difficulty} ({c.sitelinks} wikis) imdb={c.imdb_id} poster={'yes' if c.poster_url else 'no'}")
        except urllib.error.HTTPError as e:
            log(f"  x {m['name']}: HTTP {e.code} {e.read()[:150]!r}")
    return done


def publish(state: State, api_url: str, username: str, password: str, log=print) -> int:
    api = api_url.rstrip("/")
    headers = _login(api, username, password)
    done = 0
    for c in state.by_status("approved"):
        try:
            res = post_multipart(api + "/admin/tracks", {
                "tmdb_id": c.tmdb_id, "name": c.title, "year": c.year, "main_actor": c.main_actor,
                "genre": c.genre, "source_url": c.source_url, "clip_start": c.clip_start,
                "difficulty": c.difficulty, "poster_url": c.poster_url, "imdb_id": c.imdb_id,
            }, "file", c.clip_path, headers)
            c.status, c.reason, c.api_id = "published", None, res.get("id")
            _remove(c.clip_path)
            c.clip_path = None
            done += 1
            log(f"  ^ {c.title} -> id {res.get('id')} ({'new' if res.get('created') else 'updated'})")
        except urllib.error.HTTPError as e:
            c.reason = f"HTTP {e.code}: {e.read()[:200]!r}"
            log(f"  x {c.title}: {c.reason}")
        state.save(c)
    return done
