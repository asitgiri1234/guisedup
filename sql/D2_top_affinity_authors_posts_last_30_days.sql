-- D2 — Posts from the authors a given user interacts with most (last 30 days)
--
-- Step 1: rank the authors whose posts :user_id has engaged with most over the
--         trailing 30 days (their "affinity").
-- Step 2: return those authors' posts, strongest affinity first.
--
-- :user_id is a psql variable. Run:
--   psql -h 127.0.0.1 -U postgres -d guisedup -v user_id=1 \
--        -f sql/D2_top_affinity_authors_posts_last_30_days.sql
-- (In application code, bind it as a parameter, e.g. $1, instead.)

WITH top_authors AS (
    SELECT
        p.user_id                 AS author_id,
        COUNT(*)                  AS interaction_count
    FROM interactions i
    JOIN posts p ON p.id = i.post_id
    WHERE i.user_id = :user_id
      AND p.user_id <> :user_id                       -- exclude the user's own posts
      AND i.created_at >= now() - INTERVAL '30 days'
    GROUP BY p.user_id
    ORDER BY interaction_count DESC
    LIMIT 5
)
SELECT
    p.id,
    p.user_id                     AS author_id,
    p.caption,
    p.created_at,
    ta.interaction_count          AS author_affinity
FROM posts p
JOIN top_authors ta ON ta.author_id = p.user_id
ORDER BY ta.interaction_count DESC, p.created_at DESC;
