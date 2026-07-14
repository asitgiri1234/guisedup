"""FastAPI embedding service for Guised Up.

Exposes a single responsibility: turn text into vector embeddings that the
Laravel API stores in pgvector and queries for semantic search / feed ranking.
"""

from __future__ import annotations

from fastapi import Depends, FastAPI
from pydantic import BaseModel, Field

from .config import settings
from .embedder import Embedder

app = FastAPI(
    title="Guised Up — Embedding Service",
    version="1.0.0",
    description="Sentence-Transformers embedding generation for semantic search and feed ranking.",
)

# Single shared embedder; the underlying model loads lazily on first /embed.
_embedder = Embedder()


def get_embedder() -> Embedder:
    """Dependency seam so tests can override with a fake embedder."""
    return _embedder


class EmbedRequest(BaseModel):
    texts: list[str] = Field(..., min_length=1, description="One or more texts to embed.")


class EmbedResponse(BaseModel):
    model: str
    dimensions: int
    embeddings: list[list[float]]


class HealthResponse(BaseModel):
    status: str
    model: str
    dimensions: int


@app.get("/health", response_model=HealthResponse)
def health() -> HealthResponse:
    # Reports the configured dimension without forcing a model load.
    return HealthResponse(status="ok", model=settings.model_name, dimensions=settings.dimensions)


@app.post("/embed", response_model=EmbedResponse)
def embed(request: EmbedRequest, embedder: Embedder = Depends(get_embedder)) -> EmbedResponse:
    vectors = embedder.embed(request.texts)
    dimensions = len(vectors[0]) if vectors else settings.dimensions

    return EmbedResponse(
        model=embedder.model_name,
        dimensions=dimensions,
        embeddings=vectors,
    )
