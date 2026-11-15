"""yt-dlp + ffmpeg wrappers."""
import array
import json
import math
import os
import subprocess

CLIP_SECONDS = 15.0
_NO_WINDOW = getattr(subprocess, "CREATE_NO_WINDOW", 0)


def _run(args: list[str], **kw) -> subprocess.CompletedProcess:
    return subprocess.run(args, capture_output=True, check=True, creationflags=_NO_WINDOW, **kw)


def search(query: str, n: int = 8) -> list[dict]:
    """Metadata of the first n YouTube results (no download)."""
    out = _run(["yt-dlp", f"ytsearch{n}:{query}", "--flat-playlist", "-J", "--no-warnings"]).stdout
    entries = json.loads(out).get("entries") or []
    return [e for e in entries if e.get("id")]


def video_info(video_id: str) -> dict:
    """Full metadata incl. heatmap ("Most replayed") when YouTube provides it."""
    out = _run(["yt-dlp", "-J", "--no-warnings", "--skip-download", f"https://www.youtube.com/watch?v={video_id}"]).stdout
    return json.loads(out)


def download_audio(video_id: str, out_dir: str) -> str:
    os.makedirs(out_dir, exist_ok=True)
    tmpl = os.path.join(out_dir, f"{video_id}.%(ext)s")
    _run(["yt-dlp", "-f", "bestaudio", "-x", "--audio-format", "mp3", "--audio-quality", "2",
          "--no-playlist", "--no-warnings", "-o", tmpl, f"https://www.youtube.com/watch?v={video_id}"])
    return os.path.join(out_dir, f"{video_id}.mp3")


def rms_per_second(path: str, rate: int = 8000) -> list[float]:
    raw = _run(["ffmpeg", "-v", "error", "-i", path, "-ac", "1", "-ar", str(rate), "-f", "s16le", "-"]).stdout
    samples = array.array("h")
    samples.frombytes(raw[: len(raw) // 2 * 2])
    out = []
    for i in range(0, len(samples) - rate + 1, rate):
        frame = samples[i:i + rate]
        out.append(math.sqrt(sum(s * s for s in frame[::4]) / (len(frame) / 4)))
    return out


def cut(src: str, start: float, dst: str) -> None:
    """15 s clip, 0.3 s fade-in, 0.5 s fade-out, loudness -14 LUFS, mp3 128 kbps 44.1 kHz."""
    os.makedirs(os.path.dirname(dst), exist_ok=True)
    fade_out_at = CLIP_SECONDS - 0.5
    _run(["ffmpeg", "-y", "-v", "error", "-ss", f"{start:.2f}", "-t", f"{CLIP_SECONDS}", "-i", src,
          "-af", f"afade=t=in:d=0.3,afade=t=out:st={fade_out_at}:d=0.5,loudnorm=I=-14:TP=-1.5:LRA=11",
          "-ar", "44100", "-b:a", "128k", dst])


def duration(path: str) -> float:
    out = _run(["ffprobe", "-v", "error", "-show_entries", "format=duration", "-of", "csv=p=0", path]).stdout
    return float(out.strip())
