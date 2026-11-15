"""Soundtrack pipeline CLI.

  py cli.py discover --target 10    find new movies (skips ones already in the game)
  py cli.py fetch                   find + download each movie's theme from YouTube
  py cli.py cut                     cut the 15 s clip
  py cli.py run --target 10         discover + fetch + cut
  py cli.py review                  open the review page (approve / reject / re-cut)
  py cli.py publish                 upload approved clips to the API
  py cli.py backfill                set difficulty/poster/IMDb on movies already in the API
  py cli.py status                  counts per status
"""
import argparse
import collections
import os
import sys

HERE = os.path.dirname(os.path.abspath(__file__))
sys.path.insert(0, HERE)

from pipeline import review, stages  # noqa: E402
from pipeline.discover import discover  # noqa: E402
from pipeline.state import State  # noqa: E402


def load_env(path: str) -> dict:
    env = {}
    if os.path.exists(path):
        for line in open(path, encoding="utf-8"):
            line = line.strip()
            if line and not line.startswith("#") and "=" in line:
                k, v = line.split("=", 1)
                env[k.strip()] = v.strip()
    env.update({k: v for k, v in os.environ.items() if k.startswith(("API_", "EXISTING_"))})
    return env


def main() -> None:
    env = load_env(os.path.join(HERE, ".env"))
    work = os.path.join(HERE, "work")
    os.makedirs(work, exist_ok=True)
    state = State(os.path.join(work, "pipeline.db"))

    p = argparse.ArgumentParser(description=__doc__, formatter_class=argparse.RawDescriptionHelpFormatter)
    p.add_argument("command", choices=["discover", "fetch", "cut", "run", "review", "publish", "backfill", "status"])
    p.add_argument("--target", type=int, default=10)
    args = p.parse_args()

    api_url = env.get("API_BASE_URL", "http://localhost:8000/api")
    existing_urls = [api_url] + [u for u in env.get("EXISTING_API_URLS", "").split(",") if u]

    if args.command in ("discover", "run"):
        existing = stages.existing_movies(existing_urls)
        existing += [(c.title, c.year) for c in state.all()]
        print(f"{len(existing)} movies already known; discovering {args.target} new...")
        for c in discover(args.target, existing, {c.qid for c in state.all()}):
            state.add(c)
            print(f"  * {c.title} ({c.year}) - {c.main_actor} - {c.genre}")

    if args.command in ("fetch", "run"):
        for c in state.by_status("new"):
            try:
                stages.fetch(state, c, work)
            except Exception as e:
                c.status, c.reason = "failed", f"fetch: {e}"
                state.save(c)
                print(f"  x {c.title}: {c.reason}")

    if args.command in ("cut", "run"):
        for c in state.by_status("fetched"):
            stages.cut(state, c, work)

    if args.command == "review":
        review.serve(state.db.execute("PRAGMA database_list").fetchone()[2], work)

    if args.command == "publish":
        n = stages.publish(state, api_url, env["API_USERNAME"], env["API_PASSWORD"])
        print(f"published {n}")

    if args.command == "backfill":
        print(f"backfilled {stages.backfill(api_url, env['API_USERNAME'], env['API_PASSWORD'])}")

    counts = collections.Counter(c.status for c in state.all())
    print("status:", dict(counts))


if __name__ == "__main__":
    main()
