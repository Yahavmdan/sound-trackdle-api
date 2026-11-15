from pipeline.discover import starring_from_wikitext

PSYCHO = """{{Infobox film
| name = Psycho
| starring = {{Plainlist|
* [[Anthony Perkins]]
* [[Vera Miles]]
* [[John Gavin]]
}}
| music = [[Bernard Herrmann]]
}}"""


def test_first_billed_from_plainlist():
    assert starring_from_wikitext(PSYCHO) == "Anthony Perkins"


def test_piped_link_and_disambiguation():
    text = "| starring = [[Tom Hardy (actor)|Tom Hardy]]<br>[[Someone]]\n| music = x"
    assert starring_from_wikitext(text) == "Tom Hardy"


def test_animated_voice_without_links():
    text = "{{Infobox film\n| starring = {{ubl|Tom Hanks|Tim Allen}}\n| music = x\n}}"
    assert starring_from_wikitext(text) == "Tom Hanks"


def test_missing_field():
    assert starring_from_wikitext("{{Infobox film\n| name = X\n}}") is None


def test_poster_file_from_infobox():
    from pipeline.discover import image_from_wikitext
    assert image_from_wikitext("| name = X\n| image = Cars 2006.jpg\n| alt = poster") == "Cars 2006.jpg"
    assert image_from_wikitext("| image = [[File:Frozen II (film) poster.jpg|220px]]") == "Frozen II (film) poster.jpg"
    assert image_from_wikitext("| image = \n| caption = x") is None
