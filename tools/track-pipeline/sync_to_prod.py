"""Copy tracks + movie metadata from the local API database to another API (e.g. production).

  py sync_to_prod.py --target https://.../api   (needs TARGET_API_PASSWORD env var)

Rows with a clip are uploaded with their mp3 (target stores it under a random name).
Rows without a clip only get difficulty / poster / IMDb / TMDB id (metadata-only update).
Idempotent: the target upserts by tmdb_id, else (name, year).
"""
import argparse
import os
import sqlite3
import sys
import urllib.error

HERE = os.path.dirname(os.path.abspath(__file__))
API_ROOT = os.path.dirname(os.path.dirname(HERE))
sys.path.insert(0, HERE)

from pipeline.http import get_json, post_multipart  # noqa: E402
from pipeline.stages import _login  # noqa: E402


def main() -> None:
    sys.stdout.reconfigure(encoding="utf-8", errors="replace")
    p = argparse.ArgumentParser()
    p.add_argument("--target", required=True)
    p.add_argument("--username", default="admin")
    p.add_argument("--only-new", action="store_true", help="skip rows the target already has with a clip")
    args = p.parse_args()

    api = args.target.rstrip("/")
    headers = _login(api, args.username, os.environ["TARGET_API_PASSWORD"])
    db = sqlite3.connect(os.path.join(API_ROOT, "database", "database.sqlite"))
    db.row_factory = sqlite3.Row
    rows = db.execute("SELECT * FROM files ORDER BY id").fetchall()
    target_names = {(m["name"], m.get("year")) for m in get_json(api + "/movie")}

    uploaded = updated = skipped = 0
    for r in rows:
        fields = {k: r[k] for k in ("name", "year", "main_actor", "genre", "tmdb_id", "source_url", "clip_start",
                                    "difficulty", "poster_url", "imdb_id")}
        clip = os.path.join(API_ROOT, "storage", "app", "public", r["file_path"]) if r["file_path"] else None
        has_clip = bool(clip and os.path.exists(clip))
        exists = (r["name"], r["year"]) in target_names
        try:
            if has_clip and not exists:
                res = post_multipart(api + "/admin/tracks", fields, "file", clip, headers)
                uploaded += 1
                print(f"  ^ {r['name']} -> {res.get('id')}")
            elif exists and (r["difficulty"] or r["poster_url"] or r["imdb_id"]):
                meta = {k: fields[k] for k in ("name", "year", "main_actor", "genre", "tmdb_id",
                                               "difficulty", "poster_url", "imdb_id")}
                post_multipart(api + "/admin/tracks", meta, None, None, headers)
                updated += 1
            else:
                skipped += 1
        except urllib.error.HTTPError as e:
            print(f"  x {r['name']}: HTTP {e.code} {e.read()[:150]!r}")
    print(f"uploaded {uploaded}, metadata updated {updated}, skipped {skipped}")


if __name__ == "__main__":
    main()
