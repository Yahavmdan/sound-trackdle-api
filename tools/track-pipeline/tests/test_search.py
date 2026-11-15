from pipeline.search import rank, score


def video(title, duration=200, channel="Someone", views=1000, verified=False):
    return {"title": title, "duration": duration, "channel": channel, "view_count": views,
            "channel_is_verified": verified}


def test_rejects_trailer_and_wrong_duration_and_wrong_movie():
    assert score(video("Gladiator Official Trailer"), "Gladiator", 2000) is None
    assert score(video("Gladiator Main Theme", duration=30), "Gladiator", 2000) is None
    assert score(video("Gladiator Main Theme 10 hours", duration=36000), "Gladiator", 2000) is None
    assert score(video("Braveheart Main Theme"), "Gladiator", 2000) is None


def test_topic_channel_beats_random_upload():
    topic = video("Now We Are Free (From Gladiator)", channel="Hans Zimmer - Topic", views=100)
    # title lacks "gladiator"? it contains it
    rnd = video("gladiator theme", views=1000)
    ranked = rank([rnd, topic], "Gladiator", 2000)
    assert ranked[0] is topic


def test_rank_drops_rejected():
    ranked = rank([video("Gladiator trailer"), video("Gladiator Suite")], "Gladiator", 2000)
    assert [v["title"] for v in ranked] == ["Gladiator Suite"]


def test_rejects_fan_made_inspired_live_and_rejected():
    for title in ["BLACK WIDOW (2021) SOUNDTRACK | EPIC THEME BY TOMMY LUCAS",
                  "Billie Eilish - WHEN I WAS OLDER (Music Inspired By The Film ROMA)",
                  "Spectre (2015) Main Title with Radiohead rejected song",
                  "No Time To Die (Live From The BRIT Awards)",
                  "Gladiator theme fan made"]:
        movie = "Black Widow" if "WIDOW" in title else "Roma" if "ROMA" in title else \
            "Spectre" if "Spectre" in title else "No Time To Die" if "Time" in title else "Gladiator"
        assert score(video(title), movie, None) is None, title


def test_min_views():
    import pipeline.search as s
    s.MIN_VIEWS = 1_000_000
    try:
        assert score(video("Gladiator Main Theme", views=999_999), "Gladiator", 2000) is None
        assert score(video("Gladiator Main Theme", views=2_000_000), "Gladiator", 2000) is not None
    finally:
        s.MIN_VIEWS = 0
