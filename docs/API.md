# API Reference

Two services:

- **Laravel REST API** — base URL `http://127.0.0.1:8000/api`
- **Python embedding service** — base URL `http://127.0.0.1:8001` (called internally by the backend)

All Laravel responses are JSON. Send `Accept: application/json`. Protected routes
require a **Sanctum bearer token**:

```
Authorization: Bearer <token>
```

Conventions:
- Validation errors → **422** with `{ "message": string, "errors": { field: string[] } }`.
- Missing/invalid token on a protected route → **401**.
- List endpoints are paginated **20 per page** and return `data`, `links`, `meta`.

---

## Authentication

### POST `/api/register`
Create an account and receive a token. **Public.**

Request:
```json
{ "name": "Ada", "email": "ada@example.com", "password": "secret123", "password_confirmation": "secret123" }
```
Response `201`:
```json
{ "user": { "id": 4, "name": "Ada" }, "token": "4|xxxxxxxx" }
```

### POST `/api/login`
Exchange credentials for a token. **Public.**

Request:
```json
{ "email": "alice@example.com", "password": "password", "device_name": "mobile" }
```
Response `200`: `{ "user": { "id": 1, "name": "Alice Example" }, "token": "1|xxxx" }`
Invalid credentials → `422`.

### POST `/api/logout`
Revoke the token used for the request. **Auth required.**
Response `200`: `{ "message": "Logged out." }`

### GET `/api/user`
Return the authenticated user. **Auth required.**
Response `200`: the user model.

---

## Posts

### POST `/api/posts`
Create a post. An embedding is generated (via the Python service) and stored.
**Auth required.**

Request:
```json
{ "caption": "olive utility jacket with cargo trousers", "image_url": "https://example.com/look.jpg" }
```
| Field       | Rules                                  |
|-------------|----------------------------------------|
| `caption`   | required, string, max 2000             |
| `image_url` | optional, valid URL, max 2048          |

Response `201`:
```json
{
  "data": {
    "id": 25,
    "caption": "olive utility jacket with cargo trousers",
    "image_url": "https://example.com/look.jpg",
    "author": { "id": 1, "name": "Alice Example" },
    "created_at": "2026-07-14T05:20:11+00:00"
  }
}
```

---

## Feed

### GET `/api/feed`
Ranked, personalized feed for the current user (excludes their own posts).
**Auth required.** Paginated 20/page.

Query: `page` (default 1).

Response `200`:
```json
{
  "data": [
    {
      "id": 12,
      "caption": "…",
      "image_url": "…",
      "interactions_count": 3,
      "author": { "id": 2, "name": "Bob Example" },
      "ranking_score": 0.5148,
      "created_at": "2026-07-14T05:09:38+00:00"
    }
  ],
  "links": { "first": "…", "last": "…", "prev": null, "next": "…" },
  "meta": { "current_page": 1, "last_page": 2, "per_page": 20, "total": 24 }
}
```

`ranking_score` blends authenticity, relationship depth, semantic similarity and
time decay (see [ARCHITECTURE.md](ARCHITECTURE.md)).

---

## Search

### GET `/api/search`
Semantic vector search over posts (pgvector cosine distance). **Auth required.**
Paginated 20/page.

| Query | Rules                          |
|-------|--------------------------------|
| `q`   | required, string, 1–1000 chars |
| `page`| optional                       |

Response `200`: same shape as the feed `data[]`, ordered by similarity to `q`
(closest first). Missing `q` → `422`.

---

## Interactions

### POST `/api/interactions`
Log an engagement event. **Auth required.**

Request:
```json
{ "post_id": 12, "type": "like" }
```
| Field     | Rules                                                  |
|-----------|--------------------------------------------------------|
| `post_id` | required, integer, must exist in `posts`               |
| `type`    | required, one of `like`, `view`, `save`, `share`       |

Response `201`:
```json
{ "data": { "id": 31, "post_id": 12, "user_id": 1, "type": "like", "created_at": "…" } }
```

---

## Python embedding service

### GET `/health`
Response `200`: `{ "status": "ok", "model": "sentence-transformers/all-MiniLM-L6-v2", "dimensions": 384 }`

### POST `/embed`
Generate embeddings for one or more texts.

Request:
```json
{ "texts": ["a summer linen outfit", "winter layers"] }
```
Response `200`:
```json
{
  "model": "sentence-transformers/all-MiniLM-L6-v2",
  "dimensions": 384,
  "embeddings": [[0.01, -0.03, …], [0.02, 0.05, …]]
}
```
Empty `texts` → `422`. Vectors are L2-normalized and 384-dimensional (matching the
`posts.embedding vector(384)` column).
