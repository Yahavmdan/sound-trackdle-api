"""Tiny stdlib HTTP helpers (no third-party deps)."""
import hashlib
import json
import os
import time
import urllib.error
import urllib.parse
import urllib.request
import uuid

UA = "soundtrack-pipeline/0.1"

# Polite pacing + on-disk cache for public reference APIs (Wikipedia/Wikidata), so reruns are cheap.
CACHE_DIR = os.path.join(os.path.dirname(os.path.dirname(os.path.abspath(__file__))), "work", "http-cache")
CACHED_HOSTS = ("wikipedia.org", "wikidata.org")
MIN_GAP_SECONDS = 1.0
_last_call = 0.0


def _cache_path(url: str) -> str:
    return os.path.join(CACHE_DIR, hashlib.sha1(url.encode()).hexdigest() + ".json")


def get_json(url: str, params: dict | None = None, headers: dict | None = None, timeout: int = 60,
             retries: int = 6):
    """GET JSON; caches Wikimedia responses; on 429/503 waits (Retry-After or exponential) and retries."""
    global _last_call
    if params:
        url += "?" + urllib.parse.urlencode(params)
    cacheable = any(h in url for h in CACHED_HOSTS)
    if cacheable and os.path.exists(_cache_path(url)):
        with open(_cache_path(url), encoding="utf-8") as f:
            return json.load(f)
    req = urllib.request.Request(url, headers={"User-Agent": UA, "Accept": "application/json", **(headers or {})})
    for attempt in range(retries + 1):
        if cacheable:
            time.sleep(max(0.0, MIN_GAP_SECONDS - (time.monotonic() - _last_call)))
            _last_call = time.monotonic()
        try:
            with urllib.request.urlopen(req, timeout=timeout) as r:
                data = json.load(r)
            if cacheable:
                os.makedirs(CACHE_DIR, exist_ok=True)
                with open(_cache_path(url), "w", encoding="utf-8") as f:
                    json.dump(data, f)
            return data
        except urllib.error.HTTPError as e:
            if e.code not in (429, 503) or attempt == retries:
                raise
            wait = e.headers.get("Retry-After")
            secs = min(float(wait) if wait and wait.isdigit() else 5 * 2 ** attempt, 120)
            print(f"    (rate limited, waiting {secs:.0f}s)", flush=True)
            time.sleep(secs)


def post_json(url: str, body: dict, headers: dict | None = None, timeout: int = 60):
    req = urllib.request.Request(url, data=json.dumps(body).encode(), method="POST", headers={
        "User-Agent": UA, "Accept": "application/json", "Content-Type": "application/json", **(headers or {})})
    with urllib.request.urlopen(req, timeout=timeout) as r:
        return json.load(r)


def post_multipart(url: str, fields: dict, file_field: str | None, file_path: str | None,
                   headers: dict | None = None, timeout: int = 120):
    boundary = uuid.uuid4().hex
    parts = []
    for k, v in fields.items():
        if v is None:
            continue
        parts.append(f'--{boundary}\r\nContent-Disposition: form-data; name="{k}"\r\n\r\n{v}\r\n'.encode())
    if file_field and file_path:
        with open(file_path, "rb") as f:
            data = f.read()
        parts.append(f'--{boundary}\r\nContent-Disposition: form-data; name="{file_field}"; filename="clip.mp3"\r\n'
                     f'Content-Type: audio/mpeg\r\n\r\n'.encode() + data + b"\r\n")
    parts.append(f"--{boundary}--\r\n".encode())
    req = urllib.request.Request(url, data=b"".join(parts), method="POST", headers={
        "User-Agent": UA, "Accept": "application/json",
        "Content-Type": f"multipart/form-data; boundary={boundary}", **(headers or {})})
    with urllib.request.urlopen(req, timeout=timeout) as r:
        return json.load(r)
