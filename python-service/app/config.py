"""Runtime configuration for the embedding service.

Values are read from the environment so the same image can run in dev, test
and production. Defaults are chosen to match the Laravel side:
`all-MiniLM-L6-v2` produces 384-dimensional vectors, which is exactly the
width of the `posts.embedding vector(384)` column.
"""

from __future__ import annotations

import os
from dataclasses import dataclass


@dataclass(frozen=True)
class Settings:
    model_name: str = os.getenv("EMBEDDING_MODEL", "sentence-transformers/all-MiniLM-L6-v2")
    dimensions: int = int(os.getenv("EMBEDDING_DIMENSIONS", "384"))
    # Whether to L2-normalize embeddings (so dot product == cosine similarity).
    normalize: bool = os.getenv("EMBEDDING_NORMALIZE", "true").lower() == "true"


settings = Settings()
