"""Local SQLite state so every stage is resumable."""
import sqlite3
from dataclasses import dataclass, fields


@dataclass
class Candidate:
    qid: str
    title: str
    year: int | None
    main_actor: str
    genre: str
    tmdb_id: int | None = None
    sitelinks: int = 0
    status: str = "new"  # new -> fetched -> cut -> approved|rejected -> published | failed
    video_id: str | None = None
    source_url: str | None = None
    audio_path: str | None = None
    duration: float | None = None
    heatmap: str | None = None  # json
    clip_start: float | None = None
    clip_path: str | None = None
    tried_videos: str = ""  # comma separated ids already rejected
    reason: str | None = None
    imdb_id: str | None = None
    poster_url: str | None = None
    difficulty: str | None = None  # easy | medium | hard
    api_id: int | None = None  # row id in the API once published


_COLS = [f.name for f in fields(Candidate)]


class State:
    def __init__(self, path: str):
        self.db = sqlite3.connect(path)
        self.db.row_factory = sqlite3.Row
        cols = ", ".join(f"{c} {'TEXT PRIMARY KEY' if c == 'qid' else ''}" for c in _COLS)
        self.db.execute(f"CREATE TABLE IF NOT EXISTS candidates ({cols})")
        have = {r[1] for r in self.db.execute("PRAGMA table_info(candidates)")}
        for c in _COLS:
            if c not in have:
                self.db.execute(f"ALTER TABLE candidates ADD COLUMN {c}")
        self.db.commit()

    def add(self, c: Candidate) -> bool:
        cur = self.db.execute(
            f"INSERT OR IGNORE INTO candidates ({', '.join(_COLS)}) VALUES ({', '.join('?' * len(_COLS))})",
            [getattr(c, k) for k in _COLS],
        )
        self.db.commit()
        return cur.rowcount == 1

    def save(self, c: Candidate) -> None:
        sets = ", ".join(f"{k} = ?" for k in _COLS if k != "qid")
        self.db.execute(f"UPDATE candidates SET {sets} WHERE qid = ?",
                        [getattr(c, k) for k in _COLS if k != "qid"] + [c.qid])
        self.db.commit()

    def get(self, qid: str) -> Candidate | None:
        row = self.db.execute("SELECT * FROM candidates WHERE qid = ?", [qid]).fetchone()
        return Candidate(**dict(row)) if row else None

    def by_status(self, *statuses: str) -> list[Candidate]:
        q = f"SELECT * FROM candidates WHERE status IN ({', '.join('?' * len(statuses))}) ORDER BY sitelinks DESC"
        return [Candidate(**dict(r)) for r in self.db.execute(q, statuses)]

    def all(self) -> list[Candidate]:
        return [Candidate(**dict(r)) for r in self.db.execute("SELECT * FROM candidates ORDER BY sitelinks DESC")]
