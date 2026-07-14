# Guised Up

A mobile-first social feed with **personalized ranking** and **semantic (vector) search**.

**Stack:** React Native (Expo) · Laravel 13 REST API · Python (FastAPI) embedding service · PostgreSQL + pgvector · Laravel Sanctum

- Full API reference → [`docs/API.md`](docs/API.md)
- System architecture → [`docs/ARCHITECTURE.md`](docs/ARCHITECTURE.md)
- Environment/tooling setup → [`docs/ENVIRONMENT.md`](docs/ENVIRONMENT.md)
- Demo walkthrough script → [`docs/DEMO_SCRIPT.md`](docs/DEMO_SCRIPT.md)

## Repository structure

```
guised-up/
├── backend/          # Laravel 13 REST API (Sanctum auth, PostgreSQL + pgvector)
├── mobile/           # Expo / React Native app (TypeScript) — Feed screen
├── python-service/   # FastAPI embedding service (Sentence Transformers)
├── docs/             # API, architecture, environment, demo docs
├── sql/              # pgvector bootstrap + analytics query challenge (D1–D4)
└── README.md
```

## Prerequisites

PHP 8.3, Composer, Node 20+, Python 3.11+, PostgreSQL 16+ with the **pgvector**
extension, and (for the app) the Android SDK/emulator. One-time install steps are in
[`docs/ENVIRONMENT.md`](docs/ENVIRONMENT.md).

---

## Setup

```bash
# 1. Database — create the app database and enable pgvector
createdb guisedup
psql -d guisedup -f sql/001_enable_pgvector.sql

# 2. Backend
cd backend
cp .env.example .env            # set DB_* credentials; EMBEDDING_DRIVER=http
composer install
php artisan key:generate
php artisan migrate --seed      # requires the Python service running (real embeddings)

# 3. Python service
cd ../python-service
python -m venv venv
./venv/Scripts/Activate.ps1      # Windows  (source venv/bin/activate on macOS/Linux)
pip install -r requirements.txt

# 4. Mobile
cd ../mobile
npm install
```

> Seeding calls the embedding service for each post. Start the Python service
> (below) **before** running `migrate --seed`, or set `EMBEDDING_DRIVER=mock` to
> seed without it.

---

## Running the backend (Laravel REST API)

```bash
cd backend
php artisan serve                # http://127.0.0.1:8000  (API under /api)
```

## Running the Python service (embeddings)

```bash
cd python-service
./venv/Scripts/Activate.ps1
uvicorn app.main:app --host 127.0.0.1 --port 8001
# GET /health · POST /embed
```

## Running the frontend (Expo / React Native)

```bash
cd mobile
npm run android                  # builds to a running Android emulator
# or: npx expo start   (press 'a' for Android)
```

The app auto-authenticates with a seeded demo user and reaches the API at
`http://10.0.2.2:8000` (the Android emulator's alias for the host). Override with
`EXPO_PUBLIC_API_URL`, `EXPO_PUBLIC_DEMO_EMAIL`, `EXPO_PUBLIC_DEMO_PASSWORD`.

---

## Running the tests

```bash
# Backend — 44 tests (feature + unit), against a dedicated Postgres test DB
cd backend
createdb guisedup_test           # once
php artisan test

# Python service — API-contract + real-model tests
cd python-service
./venv/Scripts/Activate.ps1
pytest                           # or: pytest tests/test_api.py  (fast, offline)

# Mobile — type checking
cd mobile
npx tsc --noEmit
```

Backend tests use the deterministic **mock** embedder (no network); the `http`
driver is used at runtime.

---

## SQL challenge

Raw analytics queries live in [`sql/`](sql/):

| File | Question |
|------|----------|
| `D1_top_active_users_last_7_days.sql`             | Top 10 active users in the last 7 days |
| `D2_top_affinity_authors_posts_last_30_days.sql`  | Posts from the authors a given user interacts with most (30 days) |
| `D3_high_views_zero_reactions.sql`                | Posts with over 100 views but zero reactions |
| `D4_burst_posting_over_20_in_24h.sql`             | Users creating more than 20 posts within any 24-hour window |

```bash
psql -d guisedup -f sql/D1_top_active_users_last_7_days.sql
psql -d guisedup -v user_id=1 -f sql/D2_top_affinity_authors_posts_last_30_days.sql
```
