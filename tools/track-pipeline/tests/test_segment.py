import pytest

from pipeline.segment import pick_start


def seg(a, b, v):
    return {"start_time": a, "end_time": b, "value": v}


def test_heatmap_peak_is_chosen():
    heatmap = [seg(i * 10, i * 10 + 10, 0.1) for i in range(18)]  # 180 s track
    heatmap[9] = seg(90, 100, 1.0)
    heatmap[10] = seg(100, 110, 1.0)
    start = pick_start(180, heatmap, None)
    assert 90 <= start <= 95
    assert start + 15 <= 180


def test_no_heatmap_uses_loudest_window_after_intro():
    rms = [0.1] * 120
    for i in range(60, 75):
        rms[i] = 0.9
    assert pick_start(120, None, rms) == 60


def test_no_heatmap_no_rms_stays_inside_track():
    start = pick_start(40, None, None)
    assert 0 <= start and start + 15 <= 40


def test_too_short_track_is_rejected():
    with pytest.raises(ValueError):
        pick_start(15.5, None, None)


def test_heatmap_clip_never_runs_past_end():
    heatmap = [seg(0, 50, 0.1), seg(50, 60, 1.0)]
    start = pick_start(60, heatmap, None)
    assert start + 15 <= 60
