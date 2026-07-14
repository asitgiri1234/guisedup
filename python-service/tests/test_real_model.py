"""Integration test against the real Sentence-Transformers model.

Loads `all-MiniLM-L6-v2` (downloaded and cached on first run) and asserts the
embeddings are the right width and semantically sensible: a query is closer to
a related sentence than to an unrelated one.
"""

from __future__ import annotations

import math

from app.embedder import Embedder


def _cosine(a: list[float], b: list[float]) -> float:
    dot = sum(x * y for x, y in zip(a, b))
    na = math.sqrt(sum(x * x for x in a))
    nb = math.sqrt(sum(y * y for y in b))
    return dot / (na * nb) if na and nb else 0.0


def test_real_model_dimension_and_determinism() -> None:
    embedder = Embedder()

    assert embedder.dimensions == 384

    first = embedder.embed(["a tailored linen summer outfit"])[0]
    second = embedder.embed(["a tailored linen summer outfit"])[0]

    assert len(first) == 384
    assert _cosine(first, second) > 0.999  # deterministic for identical text


def test_related_text_is_closer_than_unrelated() -> None:
    embedder = Embedder()

    query, related, unrelated = embedder.embed(
        [
            "summer beach outfit with sandals",
            "lightweight clothes for a hot day at the sea",
            "quarterly financial audit spreadsheet",
        ]
    )

    assert _cosine(query, related) > _cosine(query, unrelated)
