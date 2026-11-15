"""Rank YouTube search results for a movie's main theme."""
import math
import re

from .names import normalize

MIN_DURATION = 60
MAX_DURATION = 720

BANNED = re.compile(
    r"\b(trailer|teaser|reaction|cover|tutorial|remix|lofi|lo fi|8d|slowed|reverb|nightcore|"
    r"ranking|top \d+|(?:1|2|3|5|8|10|12) hours?|extended|guitar|piano version|karaoke|scene|clip|"
    r"behind the scenes|review|explained)\b"
)
GOOD = re.compile(r"\b(main theme|theme|suite|main title|opening|soundtrack|ost|score)\b")


def score(video: dict, title: str, year: int | None) -> float | None:
    """Score a yt-dlp search entry; None means rejected."""
    duration = video.get("duration") or 0
    if not (MIN_DURATION <= duration <= MAX_DURATION):
        return None

    vtitle = normalize(video.get("title") or "")
    if BANNED.search(vtitle):
        return None
    if normalize(title) not in vtitle:
        return None

    points = 0.0
    channel = (video.get("channel") or video.get("uploader") or "")
    if channel.endswith("- Topic"):
        points += 3
    if video.get("channel_is_verified"):
        points += 1
    if GOOD.search(vtitle):
        points += 2
    if "main theme" in vtitle or "main title" in vtitle:
        points += 1
    if year and str(year) in (video.get("title") or ""):
        points += 0.5
    points += math.log10((video.get("view_count") or 0) + 1)
    return points


def rank(videos: list[dict], title: str, year: int | None) -> list[dict]:
    scored = [(score(v, title, year), v) for v in videos]
    return [v for s, v in sorted(((s, v) for s, v in scored if s is not None), key=lambda x: -x[0])]
