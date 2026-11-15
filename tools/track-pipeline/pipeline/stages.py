"""Pipeline stages: fetch -> cut -> publish."""
import json
import os
import urllib.error

from . import media
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
    best = results[0]
    info = media.video_info(best["id"])
    c.video_id = best["id"]
    c.source_url = f"https://www.youtube.com/watch?v={best['id']}"
    c.heatmap = json.dumps(info.get("heatmap") or [])
    c.audio_path = media.download_audio(best["id"], os.path.join(work_dir, "audio"))
    c.duration = media.duration(c.audio_path)
    c.status, c.reason = "fetched", None
    state.save(c)
    log(f"  + {c.title}: {best.get('title')} ({best.get('channel')})")


def cut(state: State, c: Candidate, work_dir: str, start: float | None = None, log=print) -> None:
    try:
        if start is None:
            heatmap = json.loads(c.heatmap or "[]")
            rms = None if heatmap else media.rms_per_second(c.audio_path)
            start = pick_start(c.duration, heatmap, rms)
        c.clip_path = os.path.join(work_dir, "clips", f"{c.qid}.mp3")
        media.cut(c.audio_path, start, c.clip_path)
        c.clip_start, c.status, c.reason = round(start, 2), "cut", None
    except ValueError as e:
        c.status, c.reason = "failed", str(e)
    state.save(c)
    log(f"  {'~' if c.status == 'cut' else 'x'} {c.title}: start {c.clip_start} {c.reason or ''}")


def next_video(state: State, c: Candidate, work_dir: str, log=print) -> None:
    """Reject the current video and try the next-ranked one."""
    c.tried_videos = ",".join(filter(None, [c.tried_videos, c.video_id or ""]))
    fetch(state, c, work_dir, log)
    if c.status == "fetched":
        cut(state, c, work_dir, log=log)


def publish(state: State, api_url: str, username: str, password: str, log=print) -> int:
    api = api_url.rstrip("/")
    login = post_json(api + "/user/login", {"username": username, "password": password})
    token = login.get("token")
    if not token:
        raise SystemExit(f"login failed: {login.get('message')}")
    headers = {"Authorization": f"Bearer {token}"}
    done = 0
    for c in state.by_status("approved"):
        try:
            res = post_multipart(api + "/admin/tracks", {
                "tmdb_id": c.tmdb_id, "name": c.title, "year": c.year, "main_actor": c.main_actor,
                "genre": c.genre, "source_url": c.source_url, "clip_start": c.clip_start,
            }, "file", c.clip_path, headers)
            c.status, c.reason = "published", None
            done += 1
            log(f"  ^ {c.title} -> id {res.get('id')} ({'new' if res.get('created') else 'updated'})")
        except urllib.error.HTTPError as e:
            c.reason = f"HTTP {e.code}: {e.read()[:200]!r}"
            log(f"  x {c.title}: {c.reason}")
        state.save(c)
    return done
