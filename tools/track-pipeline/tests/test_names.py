from pipeline.names import is_existing, normalize


def test_normalize_punctuation_case_and_leading_the():
    assert normalize("Spider-Man: Homecoming") == "spider man homecoming"
    assert normalize("The Lord of the Rings: The Return of the King") == "lord of the rings the return of the king"
    assert normalize("Amélie") == "amelie"
    assert normalize("Fast & Furious") == "fast and furious"
    assert normalize("Rocky II") == "rocky 2"


def test_same_title_is_existing():
    assert is_existing("spider man homecoming", 2017, [("Spider-Man: Homecoming", None)])
    assert is_existing("Matrix", 1999, [("The Matrix", None)])


def test_prefix_franchise_is_existing():
    existing = [("Harry Potter", 2001)]
    assert is_existing("Harry Potter and the Philosopher's Stone", 2001, existing)


def test_prefix_with_unknown_year_is_existing():
    # production list has names only
    assert is_existing("Harry Potter and the Philosopher's Stone", 2001, [("Harry Potter", None)])


def test_different_movie_is_new():
    existing = [("Alien", 1979), ("Toy Story", 1995)]
    assert not is_existing("Aliens", 1986, existing)
    assert not is_existing("Toy Story 3", 2010, [("Toy Story", 1995)])
    assert not is_existing("Gladiator", 2000, existing)
