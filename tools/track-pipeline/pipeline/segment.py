"""Choose where the 15 s clip starts."""

HEATMAP_MIN_START = 5.0
RMS_SKIP_START = 10.0


def _window_mean(segments: list[dict], start: float, length: float) -> float:
    end = start + length
    total = 0.0
    for seg in segments:
        overlap = min(end, seg["end_time"]) - max(start, seg["start_time"])
        if overlap > 0:
            total += overlap * seg["value"]
    return total / length


def pick_start(duration: float, heatmap: list[dict] | None, rms: list[float] | None, clip: float = 15.0) -> float:
    """Start time (s) of the most recognizable `clip`-second window.

    heatmap: yt-dlp "Most replayed" segments [{start_time, end_time, value}].
    rms: loudness per 1 s frame, used when there is no heatmap.
    """
    if duration < clip + 1:
        raise ValueError(f"track too short ({duration:.1f}s) for a {clip:.0f}s clip")
    latest = duration - clip - 0.5

    if heatmap:
        first = min(HEATMAP_MIN_START, latest)
        candidates = [first + i for i in range(int(latest - first) + 1)]
        return float(max(candidates, key=lambda s: (_window_mean(heatmap, s, clip), -s)))

    if rms:
        n = int(clip)
        first = int(min(RMS_SKIP_START, latest))
        last = int(min(latest, len(rms) - n))
        if last >= first:
            best = max(range(first, last + 1), key=lambda s: (sum(rms[s:s + n]), -s))
            return float(best)

    # nothing to go on: a third of the way in, usually past the intro
    return round(min(max(duration / 3, 0.0), latest), 2)
