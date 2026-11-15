"""Tiny stdlib HTTP helpers (no third-party deps)."""
import json
import time
import urllib.error
import urllib.parse
import urllib.request
import uuid

UA = "soundtrack-pipeline/0.1"


def get_json(url: str, params: dict | None = None, headers: dict | None = None, timeout: int = 90,
             retries: int = 5):
    """GET JSON; on 429/503 waits (Retry-After or exponential) and retries."""
    if params:
        url += "?" + urllib.parse.urlencode(params)
    req = urllib.request.Request(url, headers={"User-Agent": UA, "Accept": "application/json", **(headers or {})})
    for attempt in range(retries + 1):
        try:
            with urllib.request.urlopen(req, timeout=timeout) as r:
                return json.load(r)
        except urllib.error.HTTPError as e:
            if e.code not in (429, 503) or attempt == retries:
                raise
            wait = e.headers.get("Retry-After")
            time.sleep(min(float(wait) if wait and wait.isdigit() else 5 * 2 ** attempt, 90))


def post_json(url: str, body: dict, headers: dict | None = None, timeout: int = 60):
    req = urllib.request.Request(url, data=json.dumps(body).encode(), method="POST", headers={
        "User-Agent": UA, "Accept": "application/json", "Content-Type": "application/json", **(headers or {})})
    with urllib.request.urlopen(req, timeout=timeout) as r:
        return json.load(r)


def post_multipart(url: str, fields: dict, file_field: str, file_path: str,
                   headers: dict | None = None, timeout: int = 120):
    boundary = uuid.uuid4().hex
    parts = []
    for k, v in fields.items():
        if v is None:
            continue
        parts.append(f'--{boundary}\r\nContent-Disposition: form-data; name="{k}"\r\n\r\n{v}\r\n'.encode())
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
