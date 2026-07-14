"""API-contract tests for the embedding service.

These use a fake embedder (dependency override) so they run fast and offline,
without downloading model weights. A separate, opt-in test exercises the real
model — see `test_real_model.py`.
"""

from __future__ import annotations

from fastapi.testclient import TestClient

from app.main import app, get_embedder

DIMS = 384


class FakeEmbedder:
    model_name = "fake-model"

    def embed(self, texts: list[str]) -> list[list[float]]:
        # Deterministic, model-free vectors of the expected width.
        return [[float((len(t) + i) % 7) for i in range(DIMS)] for t in texts]


client = TestClient(app)


def setup_module() -> None:
    app.dependency_overrides[get_embedder] = lambda: FakeEmbedder()


def teardown_module() -> None:
    app.dependency_overrides.clear()


def test_health_reports_model_and_dimensions() -> None:
    response = client.get("/health")

    assert response.status_code == 200
    body = response.json()
    assert body["status"] == "ok"
    assert body["dimensions"] == DIMS


def test_embed_returns_one_vector_per_text() -> None:
    response = client.post("/embed", json={"texts": ["a summer look", "winter layers"]})

    assert response.status_code == 200
    body = response.json()
    assert body["dimensions"] == DIMS
    assert len(body["embeddings"]) == 2
    assert all(len(vector) == DIMS for vector in body["embeddings"])


def test_embed_requires_at_least_one_text() -> None:
    response = client.post("/embed", json={"texts": []})

    assert response.status_code == 422
