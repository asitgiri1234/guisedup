# Architecture

Guised Up is a mobile-first social feed. Three services share one PostgreSQL
database (with the pgvector extension) and communicate over HTTP/REST.

## Components

| Component        | Tech                              | Responsibility |
|------------------|-----------------------------------|----------------|
| **Mobile app**   | React Native (Expo), TypeScript   | Feed UI, semantic search, reactions; holds a Sanctum token |
| **Backend API**  | Laravel 13, PHP 8.3               | System of record; auth, posts, feed ranking, search orchestration |
| **Embedding svc**| FastAPI, Sentence Transformers    | Turns text into 384-dim vectors |
| **Database**     | PostgreSQL 16 + pgvector          | Users, posts (with vector column), interactions, follows |

## System diagram

```
        ┌────────────────────┐
        │   Mobile app        │  Expo / React Native
        │   (Feed screen)     │
        └─────────┬───────────┘
                  │ REST + Bearer token (Sanctum)
                  ▼
        ┌────────────────────┐        POST /embed        ┌─────────────────────┐
        │   Laravel API       │ ─────────────────────────▶│  Python embedding    │
        │  posts / feed /     │◀───────────────────────── │  service (FastAPI)   │
        │  search / interact  │        embeddings          │  all-MiniLM-L6-v2    │
        └─────────┬───────────┘                            └─────────────────────┘
                  │ SQL (Eloquent) + vector <=> queries
                  ▼
        ┌────────────────────┐
        │  PostgreSQL         │  users · posts(embedding vector(384)) ·
        │  + pgvector         │  interactions · follows
        └────────────────────┘
```

## Request flows

**Create post** — `POST /api/posts` → `PostService` asks the `EmbeddingService`
contract for a vector (the `http` driver calls the Python service) → post + embedding
saved to Postgres.

**Semantic search** — `GET /api/search?q=…` → query embedded via the same contract →
`SearchService` orders posts by cosine distance (`embedding <=> :q::vector`, backed by
an HNSW index) → paginated.

**Personalized feed** — `GET /api/feed` → `FeedService` pulls a bounded candidate set,
builds a `RankingContext` (who the viewer follows, past interactions, and their "taste
vector" = mean embedding of engaged posts), then `FeedRanker` scores each post and
paginates.

## Feed ranking

A reusable, weighted blend of four signals, each normalized to `[0, 1]`
(`config/feed.php`):

```
score = 0.20·authenticity + 0.30·relationship + 0.35·semantic + 0.15·time_decay
```

- **Authenticity** — the author's `authenticity_score`.
- **Relationship depth** — follow + past-interaction strength between viewer and author.
- **Semantic similarity** — cosine of the post embedding vs. the viewer's taste vector.
- **Time decay** — exponential recency, `exp(-age_hours / τ)`.

Each signal is a small `RankingSignal` class composed by `FeedRanker`, so signals and
weights can be tuned or added without touching the feed pipeline.

## Embedding pipeline

`EmbeddingService` is an interface with two implementations selected by
`config('embedding.driver')`:

- **`mock`** — deterministic hash-based vectors (used in tests; no network).
- **`http`** — calls the Python service (`all-MiniLM-L6-v2`, 384-dim, L2-normalized).

This seam let each phase build against the mock and later swap in the real model with
zero changes to callers.

## Authentication

Laravel **Sanctum** personal-access tokens. `POST /register` / `POST /login` issue a
token; all content routes sit behind `auth:sanctum`. The mobile app persists the token
in `expo-secure-store` and attaches it as a bearer header.

## Data model

```
users (id, name, email, authenticity_score, …)
  1───∞ posts (id, user_id, caption, image_url, embedding vector(384), …)
  1───∞ interactions (id, user_id, post_id, type[like|fire|clap|view|save|share], …)
  1───∞ comments (id, user_id, post_id, body, …)
follows (follower_id, followed_id)               -- viewer ↔ author graph
posts 1───∞ interactions,  posts 1───∞ comments
```

## Key decisions & trade-offs

- **Separate Python service** keeps heavy ML deps out of the PHP runtime and lets
  embeddings scale independently; cost is an extra network hop (mitigated by the mock
  driver in tests).
- **In-memory feed ranking** over a bounded candidate set is simple and explainable;
  for large-scale use this would move to precomputed scores / a candidate retrieval
  stage.
- **pgvector HNSW** gives fast approximate cosine search inside the primary database —
  no separate vector store to operate.
