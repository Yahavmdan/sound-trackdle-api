"""Local review page: listen to each clip, then approve / reject / re-cut / try next video."""
import html
import json
import os
from http.server import BaseHTTPRequestHandler, ThreadingHTTPServer

from . import stages
from .state import State

PAGE = """<!doctype html><html><head><meta charset="utf-8"><title>Clip review</title>
<style>
body{font-family:system-ui,sans-serif;max-width:960px;margin:24px auto;padding:0 16px;background:#111;color:#eee}
.row{display:grid;grid-template-columns:1fr 330px;gap:8px;padding:12px;border-bottom:1px solid #333;align-items:center}
.meta small{color:#999}.st{font-size:12px;padding:2px 6px;border-radius:4px;background:#333}
.approved{background:#1d5e2e}.rejected{background:#6b1d1d}.published{background:#1d3f6b}.failed{background:#6b4a1d}
button{margin:2px;padding:4px 8px;cursor:pointer}input{width:60px}a{color:#8cf}
</style></head><body>
<h2>Clip review <small>(%(count)d)</small></h2>
<p>Approve good clips, then run <code>py cli.py publish</code>.</p>
%(rows)s
<script>
async function act(qid, action){
  const start = document.getElementById('s-'+qid).value;
  const btns = document.querySelectorAll('#r-'+CSS.escape(qid)+' button'); btns.forEach(b=>b.disabled=true);
  const r = await fetch('/action',{method:'POST',body:JSON.stringify({qid,action,start})});
  const j = await r.json(); if(!r.ok){alert(j.error||'error')}
  location.reload();
}
</script></body></html>"""

ROW = """<div class="row" id="r-%(qid)s"><div class="meta"><b>%(title)s</b> (%(year)s)
<span class="st %(status)s">%(status)s</span><br><small>%(actor)s · %(genre)s</small><br>
<small><a href="%(src)s&t=%(t)ds" target="_blank">source @ %(start)s s</a> %(reason)s</small><br>
<audio controls preload="none" src="/clip/%(qid)s?v=%(v)s"></audio></div><div>
<button onclick="act('%(qid)s','approve')">Approve</button><button onclick="act('%(qid)s','reject')">Reject</button>
<button onclick="act('%(qid)s','next')">Next video</button><br>
Re-cut at <input id="s-%(qid)s" value="%(start)s"> s <button onclick="act('%(qid)s','recut')">Re-cut</button>
</div></div>"""


def serve(state_path: str, work_dir: str, port: int = 8765) -> None:
    class Handler(BaseHTTPRequestHandler):
        def log_message(self, *a):  # quiet
            pass

        def _state(self) -> State:
            return State(state_path)

        def _send(self, code: int, body: bytes, ctype: str):
            self.send_response(code)
            self.send_header("Content-Type", ctype)
            self.send_header("Content-Length", str(len(body)))
            self.end_headers()
            self.wfile.write(body)

        def do_GET(self):
            st = self._state()
            if self.path.startswith("/clip/"):
                c = st.get(self.path[6:].split("?")[0])
                if not c or not c.clip_path or not os.path.exists(c.clip_path):
                    return self._send(404, b"no clip", "text/plain")
                with open(c.clip_path, "rb") as f:
                    return self._send(200, f.read(), "audio/mpeg")
            cands = [c for c in st.all() if c.status in ("cut", "approved", "rejected", "published", "failed")]
            rows = "".join(ROW % {
                "qid": c.qid, "title": html.escape(c.title), "year": c.year, "status": c.status,
                "actor": html.escape(c.main_actor), "genre": html.escape(c.genre),
                "src": html.escape(c.source_url or "#"), "t": int(c.clip_start or 0), "start": c.clip_start or 0,
                "reason": html.escape(c.reason or ""), "v": int(os.path.getmtime(c.clip_path)) if c.clip_path and os.path.exists(c.clip_path) else 0,
            } for c in cands)
            self._send(200, (PAGE % {"rows": rows, "count": len(cands)}).encode(), "text/html; charset=utf-8")

        def do_POST(self):
            body = json.loads(self.rfile.read(int(self.headers.get("Content-Length", 0))) or b"{}")
            st = self._state()
            c = st.get(body.get("qid", ""))
            if not c:
                return self._send(404, b'{"error":"unknown clip"}', "application/json")
            try:
                action = body.get("action")
                if action == "approve":
                    c.status = "approved"
                    st.save(c)
                elif action == "reject":
                    c.status = "rejected"
                    st.save(c)
                elif action == "recut":
                    stages.cut(st, c, work_dir, start=float(body.get("start") or 0))
                elif action == "next":
                    stages.next_video(st, c, work_dir)
                else:
                    return self._send(400, b'{"error":"bad action"}', "application/json")
            except Exception as e:  # surface to the page
                return self._send(500, json.dumps({"error": str(e)}).encode(), "application/json")
            self._send(200, b"{}", "application/json")

    print(f"Review page: http://localhost:{port}  (Ctrl+C to stop)")
    ThreadingHTTPServer(("127.0.0.1", port), Handler).serve_forever()
