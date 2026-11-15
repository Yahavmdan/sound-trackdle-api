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
