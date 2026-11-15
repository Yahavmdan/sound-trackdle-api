"""Find well-known movies (no API key needed).

Candidates = films linked from curated Wikipedia lists (famous scores, top grossing), resolved
through the Wikidata entity API. Popularity = number of Wikipedia language editions (sitelinks).
"""
import re

from .http import get_json
from .names import is_existing
from .state import Candidate

WIKIPEDIA_API = "https://en.wikipedia.org/w/api.php"
WIKIDATA_API = "https://www.wikidata.org/w/api.php"
HEADERS = {"User-Agent": "SoundtrackPipeline/0.1 (personal non-commercial game; python-urllib)"}

SOURCE_PAGES = [
    "AFI's 100 Years of Film Scores",
    "List of highest-grossing films",
    "List of films considered the best",
]

FILM = "Q11424"
ANIMATED_FILM = "Q202866"


def _page_links(page: str) -> list[str]:
    data = get_json(WIKIPEDIA_API, {"action": "parse", "page": page, "prop": "links", "format": "json",
                                    "redirects": 1}, HEADERS)
    return [l["*"] for l in data.get("parse", {}).get("links", []) if l.get("ns") == 0 and "exists" in l]


def _entities(params: dict, batch_key: str, values: list[str]) -> dict:
    out: dict = {}
    for i in range(0, len(values), 50):
        data = get_json(WIKIDATA_API, {"action": "wbgetentities", batch_key: "|".join(values[i:i + 50]),
                                       "languages": "en", "format": "json", **params}, HEADERS)
        out.update({k: v for k, v in data.get("entities", {}).items() if not k.startswith("-")})
    return out


def _claim_ids(entity: dict, prop: str) -> list[str]:
    ids = []
    for claim in entity.get("claims", {}).get(prop, []):
        value = claim.get("mainsnak", {}).get("datavalue", {}).get("value")
        if isinstance(value, dict) and "id" in value:
            ids.append(value["id"])
    return ids


def _claim_str(entity: dict, prop: str) -> str | None:
    for claim in entity.get("claims", {}).get(prop, []):
        value = claim.get("mainsnak", {}).get("datavalue", {}).get("value")
        if isinstance(value, str):
            return value
    return None


def _year(entity: dict) -> int | None:
    years = []
    for claim in entity.get("claims", {}).get("P577", []):
        t = claim.get("mainsnak", {}).get("datavalue", {}).get("value", {}).get("time")
        if t and re.match(r"^\+\d{4}", t):
            years.append(int(t[1:5]))
    return min(years) if years else None


def _clean_genre(label: str) -> str:
    return re.sub(r"\s+(film|movie)$", "", label.strip()).title()


def discover(limit: int, existing: list[tuple[str, int | None]], skip_qids: set[str],
             min_year: int = 1960) -> list[Candidate]:
    titles: list[str] = []
    for page in SOURCE_PAGES:
        titles += [t for t in _page_links(page) if t not in titles]

    ents = _entities({"sites": "enwiki", "props": "claims|sitelinks|labels"}, "titles", titles)
    films = []
    for qid, ent in ents.items():
        if not ({FILM, ANIMATED_FILM} & set(_claim_ids(ent, "P31"))):
            continue
        year = _year(ent)
        title = ent.get("labels", {}).get("en", {}).get("value")
        if not title or not year or year < min_year or qid in skip_qids:
            continue
        films.append((len(ent.get("sitelinks", {})), qid, title, year, ent))
    films.sort(key=lambda f: -f[0])

    picked: list[tuple[Candidate, dict]] = []
    seen = list(existing)
    for links, qid, title, year, ent in films:
        if is_existing(title, year, seen):
            continue
        tmdb = _claim_str(ent, "P4947")
        picked.append((Candidate(qid=qid, title=title, year=year, main_actor="", genre="",
                                 tmdb_id=int(tmdb) if tmdb and tmdb.isdigit() else None, sitelinks=links), ent))
        seen.append((title, year))
        if len(picked) >= limit * 2:  # spare ones replace candidates missing actor/genre
            break

    _fill_details(picked)
    return [c for c, _ in picked if c.main_actor and c.genre][:limit]


def starring_from_wikitext(wikitext: str) -> str | None:
    """First name in the infobox `starring` field (billing order)."""
    m = re.search(r"\|\s*starring\s*=(.*?)(?:\n\s*\|\s*[a-z_ ]+=|\n\}\})", wikitext, re.S | re.I)
    if not m:
        return None
    field = re.sub(r"<!--.*?-->|<ref[^>]*/>|<ref.*?</ref>", "", m.group(1), flags=re.S)
    link = re.search(r"\[\[([^\]|#]+)(?:\|([^\]]+))?\]\]", field)
    if link:
        return re.sub(r"\s*\(.*?\)$", "", (link.group(2) or link.group(1))).strip()
    plain = re.search(r"(?:\*|\{\{[^|]*\|)\s*([A-Z][^|}\n*<]+)", field)
    return plain.group(1).strip() if plain else None


def _infobox_star(enwiki_title: str) -> str | None:
    try:
        data = get_json(WIKIPEDIA_API, {"action": "parse", "page": enwiki_title, "prop": "wikitext",
                                        "section": 0, "format": "json", "redirects": 1}, HEADERS)
    except Exception:
        return None
    return starring_from_wikitext(data.get("parse", {}).get("wikitext", {}).get("*", ""))


def _fill_details(picked: list[tuple[Candidate, dict]]) -> None:
    """Main actor = first billed in the Wikipedia infobox (fallback: first Wikidata cast); up to 3 genres."""
    wanted = {c.qid: (_claim_ids(ent, "P161")[:1], _claim_ids(ent, "P136")[:3]) for c, ent in picked}
    ids = sorted({i for cast, genres in wanted.values() for i in cast + genres})
    labels = {k: v.get("labels", {}).get("en", {}).get("value", "")
              for k, v in _entities({"props": "labels"}, "ids", ids).items()} if ids else {}
    for c, ent in picked:
        cast, genres = wanted[c.qid]
        enwiki = ent.get("sitelinks", {}).get("enwiki", {}).get("title")
        c.main_actor = (_infobox_star(enwiki) if enwiki else None) or (labels.get(cast[0], "") if cast else "")
        names: list[str] = []
        for g in genres:
            name = _clean_genre(labels.get(g, ""))
            if name and name not in names:
                names.append(name)
        c.genre = ", ".join(names)
