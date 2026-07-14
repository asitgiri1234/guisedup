# Requirements Checklist

Every assignment requirement mapped to its implementation. ✅ satisfied.

## Environment

| Requirement | Status | Where |
|---|---|---|
| PHP, Composer, Laravel | ✅ | PHP 8.3, Composer 2.10, Laravel 13 — `docs/ENVIRONMENT.md` |
| Node, Python, Git | ✅ | Node 22, Python 3.13, Git 2.50 |
| PostgreSQL + pgvector | ✅ | PG 18, pgvector 0.8.5 built from source — `sql/001_enable_pgvector.sql` |
| Expo / React Native env | ✅ | Expo SDK 57, Android SDK + AVD |

## Backend (Laravel)

| Requirement | Status | Where |
|---|---|---|
| Configure PostgreSQL | ✅ | `config/database.php`, `.env` (pgsql) |
| Laravel Sanctum auth | ✅ | `HasApiTokens` on `User`, `auth:sanctum` group, `AuthController` |
| Migrations | ✅ | `database/migrations/` (posts, interactions, authenticity, follows) |
| Seeders (≥2 users) | ✅ | `DatabaseSeeder` — Alice, Bob (+ posts, follows, interactions) |
| Models + relationships | ✅ | `User`/`Post`/`Interaction` — hasMany/belongsTo, follows belongsToMany |
| `POST /api/posts` | ✅ | `PostController` → `PostService` (generates embedding) |
| `GET /api/feed` | ✅ | `FeedController` → `FeedService` (ranked, personalised) |
| `GET /api/search` | ✅ | `SearchController` → `SearchService` (cosine kNN) |
| `POST /api/interactions` | ✅ | `InteractionController` → `InteractionService` |
| Clean architecture (controllers/services/routes) | ✅ | thin controllers, `app/Services/*`, FormRequests, Resources |
| Pagination (20) | ✅ | feed + search 20/page; `config/feed.php` |
| Mock embedding via interface | ✅ | `Contracts\EmbeddingService` + `MockEmbeddingService` |
| Tests: post creation, feed, interaction logging | ✅ | `tests/Feature/{PostCreation,Feed,Interaction}Test` |

## Recommendation engine (Phase 3)

| Requirement | Status | Where |
|---|---|---|
| FastAPI service | ✅ | `python-service/app/main.py` |
| Sentence Transformers | ✅ | `all-MiniLM-L6-v2` (384-dim) — `app/embedder.py` |
| Embedding endpoint | ✅ | `POST /embed`, `GET /health` |
| Laravel ↔ Python integration | ✅ | `HttpEmbeddingService` (config-selected driver) |
| Store embeddings in pgvector | ✅ | `posts.embedding vector(384)` + HNSW index |
| Semantic search | ✅ | `SearchService` — `embedding <=> :q::vector` |
| Cosine similarity search | ✅ | HNSW `vector_cosine_ops` |
| Ranking: authenticity | ✅ | `Signals\AuthenticitySignal` |
| Ranking: relationship depth | ✅ | `Signals\RelationshipDepthSignal` (follows + engagement) |
| Ranking: semantic similarity | ✅ | `Signals\SemanticSimilaritySignal` (taste vector) |
| Ranking: time decay | ✅ | `Signals\TimeDecaySignal` |
| Reusable ranking services | ✅ | `FeedRanker` composes weighted `RankingSignal`s |
| Replace mock with real | ✅ | `EMBEDDING_DRIVER=http` at runtime; mock in tests |
| Tests: ranking + semantic search | ✅ | `tests/Unit/Ranking/*`, `SemanticSearchTest`, `FeedPersonalizationTest` |

## Mobile (React Native / Expo)

| Requirement | Status | Where |
|---|---|---|
| Feed Screen | ✅ | `mobile/src/screens/FeedScreen.tsx` |
| Auth token support | ✅ | `AuthContext` + `tokenStore` (expo-secure-store), bearer header |
| Fetch paginated feed | ✅ | `usePosts` → `/feed?page=` |
| Infinite scrolling | ✅ | `FlatList onEndReached` |
| Search bar + inline results | ✅ | `SearchBar` + debounced `usePosts` (search) |
| Post card: avatar placeholder | ✅ | `Avatar` (initials) |
| Post card: username | ✅ | `PostCard` |
| Post card: post text | ✅ | `PostCard` caption |
| Post card: time ago | ✅ | `utils/timeAgo` |
| Post card: reaction button | ✅ | `ReactionButton` → `POST /interactions` |
| Loading / empty / error states | ✅ | `StateViews` (LoadingState/EmptyState/ErrorState) |
| Avoid default RN styling | ✅ | `theme/theme.ts` design tokens |
| Clean component organisation | ✅ | `api/ auth/ components/ hooks/ screens/ theme/ utils/` |

## SQL challenge

| Requirement | Status | File |
|---|---|---|
| D1 — top 10 active users (7d) | ✅ | `sql/D1_top_active_users_last_7_days.sql` |
| D2 — posts from most-interacted authors (30d) | ✅ | `sql/D2_top_affinity_authors_posts_last_30_days.sql` |
| D3 — >100 views, zero reactions | ✅ | `sql/D3_high_views_zero_reactions.sql` |
| D4 — >20 posts in 24h | ✅ | `sql/D4_burst_posting_over_20_in_24h.sql` |

## Documentation

| Requirement | Status | Where |
|---|---|---|
| README: setup / run backend / frontend / python / tests | ✅ | `README.md` |
| API documentation (every endpoint) | ✅ | `docs/API.md` |
| Architecture summary | ✅ | `docs/ARCHITECTURE.md` |
| Demo video script | ✅ | `docs/DEMO_SCRIPT.md` |

## Quality gates

| Check | Status |
|---|---|
| Backend tests | ✅ 45 passing (105 assertions) |
| Python tests | ✅ API-contract + real-model |
| Mobile type-check | ✅ `tsc --noEmit` clean |
| Coding standards | ✅ Laravel Pint passes |
| SQL queries validated | ✅ all four run against the DB |
| No secrets committed | ✅ `.env` gitignored; verified across history |
