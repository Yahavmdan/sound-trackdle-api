"""One-off: set difficulty/poster/IMDb for prod movies whose names don't match a Wikipedia article."""
import os
import sys

sys.path.insert(0, r"C:\Users\PC-YV-DN\work\sound-trackdle-api\tools\track-pipeline")
sys.stdout.reconfigure(encoding="utf-8", errors="replace")

from pipeline.discover import _candidate, _entities, fill_posters  # noqa: E402
from pipeline.http import post_multipart  # noqa: E402
from pipeline.stages import _login  # noqa: E402

API = "https://sound-trackdle-api-production.up.railway.app/api"

# (name in DB, year in DB) -> English Wikipedia article
MAPPING = {
    ("Star Wars", 2015): "Star Wars: The Force Awakens",
    ("The Good, the Bad and the Ugly", 1966): "The Good, the Bad and the Ugly",
    ("Harry Potter", 2001): "Harry Potter and the Philosopher's Stone (film)",
    ("The Lord of the Rings", 2003): "The Lord of the Rings: The Return of the King",
    ("A Star is Born", 2018): "A Star Is Born (2018 film)",
    ("Aladdin", 1992): "Aladdin (1992 Disney film)",
    ("Beauty and the Beast (1991)", 1991): "Beauty and the Beast (1991 film)",
    ("Dances with Wolves", 1990): "Dances with Wolves",
    ("Harry Potter and the Sorcerer's Stone", 2001): "Harry Potter and the Philosopher's Stone (film)",
    ("Kill Bill", 2003): "Kill Bill: Volume 1",
    ("Lion King (1994)", 1994): "The Lion King",
    ("Mad Max", 2015): "Mad Max: Fury Road",
    ("Silence of the Lambs", 1991): "The Silence of the Lambs (film)",
    ("Star Wars: Episode IV - A New Hope", 1977): "Star Wars (film)",
}

ents = _entities({"sites": "enwiki", "props": "claims|sitelinks|labels", "redirects": "yes"}, "titles",
                 sorted(set(MAPPING.values())))
by_title = {e.get("sitelinks", {}).get("enwiki", {}).get("title"): (q, e) for q, e in ents.items()}

headers = _login(API, "admin", os.environ["TARGET_API_PASSWORD"])
for (name, year), article in MAPPING.items():
    found = by_title.get(article)
    if not found:
        print(f"  ? {name}: article {article!r} not resolved")
        continue
    qid, ent = found
    c = _candidate(qid, name, year, len(ent.get("sitelinks", {})), ent)
    fill_posters([(c, ent)])
    post_multipart(API + "/admin/tracks", {"name": name, "year": year, "main_actor": "-", "genre": "-",
                                           "difficulty": c.difficulty, "poster_url": c.poster_url,
                                           "imdb_id": c.imdb_id}, None, None, headers)
    print(f"  = {name}: {c.difficulty} ({c.sitelinks} wikis) {c.imdb_id} poster={'yes' if c.poster_url else 'NO'}")
