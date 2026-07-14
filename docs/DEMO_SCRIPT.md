# Demo Video Script (~4 minutes)

A concise walkthrough for the assignment demo. Each section lists **[SHOW]** (screen)
and **[SAY]** (narration).

---

### 0:00 — Intro (20s)
**[SHOW]** README top / repo structure.
**[SAY]** "Guised Up is a mobile-first social feed with a personalized ranking feed and
semantic search. It's a monorepo: a React Native app, a Laravel REST API, a Python
embedding service, and PostgreSQL with pgvector."

### 0:20 — Architecture (30s)
**[SHOW]** `docs/ARCHITECTURE.md` diagram.
**[SAY]** "The app talks to Laravel over REST with a Sanctum token. When a post is
created or searched, Laravel calls the Python service, which uses a Sentence
Transformers model to produce 384-dimension vectors stored in pgvector. The feed is
ranked by four signals: authenticity, relationship depth, semantic similarity, and time
decay."

### 0:50 — Services up (20s)
**[SHOW]** Two terminals: `uvicorn app.main:app --port 8001` and `php artisan serve`.
**[SAY]** "The embedding service is running on 8001 and the API on 8000." Hit
`GET /health` → show `{status: ok, dimensions: 384}`.

### 1:10 — Embeddings are real (30s)
**[SHOW]** `curl POST /api/posts` with a caption, then `psql` `SELECT vector_dims(embedding)` → `384`.
**[SAY]** "Creating a post generates a real embedding and stores it as a pgvector value."

### 1:40 — Semantic search (30s)
**[SHOW]** `GET /api/search?q=cargo trousers outfit` → the cargo/utility post ranks first.
**[SAY]** "Search embeds the query and orders posts by cosine distance — this is
meaning-based, not keyword matching."

### 2:10 — Ranked feed (25s)
**[SHOW]** `GET /api/feed` JSON with `ranking_score`; point out own posts are excluded.
**[SAY]** "The feed is personalized per viewer and each post carries its blended
ranking score."

### 2:35 — Mobile app (50s)
**[SHOW]** App on the Android emulator: feed loads → scroll (infinite pagination) →
type in the search bar (inline results) → tap a heart (reaction). Briefly show pull-to-
refresh and an empty search ("no matches").
**[SAY]** "The app auto-authenticates with a token, fetches the paginated feed with
infinite scroll, does semantic search inline, and logs reactions. Loading, empty, and
error states are all handled."

### 3:25 — SQL challenge (20s)
**[SHOW]** `sql/` files; run `D1` (top active users) and `D3` (high views, zero reactions).
**[SAY]** "Four raw analytics queries — active users, affinity-based posts, high-view
zero-reaction posts, and burst posting detection with a sliding 24-hour window."

### 3:45 — Tests & wrap (15s)
**[SHOW]** `php artisan test` (44 passing) and `pytest` (passing).
**[SAY]** "44 backend tests and the Python tests pass. Thanks for watching."

---

## Pre-record checklist
- [ ] `guisedup` DB seeded (`php artisan migrate:fresh --seed` with the Python service up)
- [ ] Python service on `:8001`, Laravel on `:8000`
- [ ] Android emulator booted, `npm run android` installed the app
- [ ] Terminal font large enough to read
