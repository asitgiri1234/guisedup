# Guised Up

Monorepo for the Guised Up take-home assessment — a mobile-first social feed with
personalized ranking and semantic (vector) search.

**Stack:** React Native (Expo) · Laravel (REST API) · Python service · PostgreSQL + pgvector · Laravel Sanctum

> This repository is in the **setup & design** phase. No business logic is implemented yet.
> The technical design lives in [`docs/TSD.md`](docs/TSD.md).

## Repository structure

```
guised-up/
├── backend/          # Laravel 13 REST API (auth via Sanctum, PostgreSQL)
├── mobile/           # Expo / React Native app (TypeScript)
├── python-service/   # Python microservice — embeddings & semantic search (FastAPI)
├── docs/             # Technical Solution Document and design notes
│   └── TSD.md
├── sql/              # Database bootstrap & extension scripts (e.g. pgvector)
└── README.md
```

### `backend/` — Laravel API
The REST API and system of record. Owns users, content, social graph, and the feed
endpoints. Authenticates clients with Laravel Sanctum (token-based). Talks to
PostgreSQL and delegates embedding/search work to the Python service.

### `mobile/` — Expo React Native app
The client. Consumes the Laravel REST API, renders the ranked feed, and drives
semantic search. Android is the primary target for this phase (iOS not configured).

### `python-service/` — Embeddings & search
A Python (FastAPI) microservice responsible for generating vector embeddings and
running similarity search against pgvector. Isolated so ML dependencies stay out of
the PHP runtime.

### `sql/` — Database scripts
Idempotent setup scripts, including enabling the `pgvector` extension and any
index/bootstrap SQL that sits outside Laravel migrations.

### `docs/` — Design
`TSD.md` is the Technical Solution Document: architecture, schema, ranking and
embedding strategy, auth, API surface, trade-offs, and assumptions.

## Getting started

Prerequisites and one-time environment setup (PHP, Composer, Node, Python, PostgreSQL,
Android SDK) are documented in [`docs/ENVIRONMENT.md`](docs/ENVIRONMENT.md).

```bash
# Backend (Laravel REST API)
cd backend && php artisan serve          # http://127.0.0.1:8000

# Mobile (Expo)
cd mobile && npm run android             # requires a running Android emulator

# Python service
cd python-service && .\venv\Scripts\Activate.ps1
```

Copy `backend/.env.example` to `backend/.env` and set your local database credentials
before running the API. Secrets are never committed.
