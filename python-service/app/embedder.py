"""Thin wrapper around a Sentence Transformers model.

The model is loaded lazily on first use so that importing the app (and running
fast API-contract tests with a fake embedder) does not pull the weights into
memory.
"""

from __future__ import annotations

from functools import cached_property

from sentence_transformers import SentenceTransformer

from .config import settings


class Embedder:
    def __init__(self, model_name: str | None = None, normalize: bool | None = None) -> None:
        self.model_name = model_name or settings.model_name
        self.normalize = settings.normalize if normalize is None else normalize

    @cached_property
    def model(self) -> SentenceTransformer:
        return SentenceTransformer(self.model_name)

    @property
    def dimensions(self) -> int:
        # Newer sentence-transformers renamed this method; support both.
        getter = getattr(self.model, "get_embedding_dimension", None) \
            or self.model.get_sentence_embedding_dimension
        return int(getter())

    def embed(self, texts: list[str]) -> list[list[float]]:
        """Encode a batch of texts into a list of float vectors."""
        vectors = self.model.encode(
            texts,
            normalize_embeddings=self.normalize,
            convert_to_numpy=True,
        )
        return [vector.tolist() for vector in vectors]
