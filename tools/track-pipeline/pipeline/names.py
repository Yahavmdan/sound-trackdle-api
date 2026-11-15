"""Title normalization and duplicate detection against movies already in the game."""
import re
import unicodedata

_ROMAN = {"ii": "2", "iii": "3", "iv": "4", "v": "5", "vi": "6", "vii": "7"}


def normalize(title: str) -> str:
    s = unicodedata.normalize("NFKD", title).encode("ascii", "ignore").decode()
    s = s.lower().replace("&", " and ")
    s = re.sub(r"[^a-z0-9]+", " ", s).strip()
    words = [_ROMAN.get(w, w) for w in s.split()]
    if words and words[0] == "the":
        words = words[1:]
    return " ".join(words)


def is_existing(title: str, year: int | None, existing: list[tuple[str, int | None]]) -> bool:
    """True when the candidate matches an existing movie.

    Match = same normalized title, or an existing title is a whole-word prefix of the
    candidate (e.g. seeded "Harry Potter" vs "Harry Potter and the Philosopher's Stone")
    with the same year, or with no year known on either side.
    """
    cand = normalize(title)
    for name, ex_year in existing:
        ex = normalize(name)
        if not ex:
            continue
        if cand == ex:
            return True
        if cand.startswith(ex + " ") and (year is None or ex_year is None or abs(year - ex_year) <= 1):
            return True
    return False
