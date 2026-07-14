# python-service — Embedding Service

FastAPI microservice that turns text into vector embeddings using
[Sentence Transformers](https://www.sbert.net/). The Laravel API calls it when
creating posts and when running semantic search; the vectors are stored in
PostgreSQL via `pgvector`.

Model: `all-MiniLM-L6-v2` → **384-dim** vectors (matches `posts.embedding vector(384)`).

## Run

```bash
# from python-service/
.\venv\Scripts\Activate.ps1
uvicorn app.main:app --host 127.0.0.1 --port 8001
```

## Endpoints

| Method | Path      | Body                          | Response                                   |
|--------|-----------|-------------------------------|--------------------------------------------|
| GET    | `/health` | –                             | `{status, model, dimensions}`              |
| POST   | `/embed`  | `{"texts": ["...", "..."]}`   | `{model, dimensions, embeddings: [[...]]}` |

## Test

```bash
pytest                      # all tests (first run downloads the model)
pytest tests/test_api.py    # fast, offline API-contract tests only
```

## Configuration

See `.env.example`. Vectors are L2-normalized by default so cosine similarity
equals the dot product.
