-- D4 — Users creating more than 20 posts within any 24-hour window
--
-- Uses a per-user sliding window: for each post, count that user's posts in the
-- [post_time, post_time + 24h) interval. If any window exceeds 20, the user is
-- flagged (typical burst / spam detection). Catches bursts that straddle
-- calendar days, which a simple "last 24 hours" filter would miss.
--
-- Run: psql -h 127.0.0.1 -U postgres -d guisedup -f sql/D4_burst_posting_over_20_in_24h.sql

WITH windowed AS (
    SELECT
        user_id,
        created_at,
        COUNT(*) OVER (
            PARTITION BY user_id
            ORDER BY created_at
            RANGE BETWEEN CURRENT ROW AND INTERVAL '24 hours' FOLLOWING
        ) AS posts_in_window
    FROM posts
)
SELECT
    u.id,
    u.name,
    MAX(w.posts_in_window) AS max_posts_in_24h
FROM windowed w
JOIN users u ON u.id = w.user_id
WHERE w.posts_in_window > 20
GROUP BY u.id, u.name
ORDER BY max_posts_in_24h DESC;

-- Simpler "in the last 24 hours" variant:
--
--   SELECT u.id, u.name, COUNT(p.id) AS posts_24h
--   FROM users u
--   JOIN posts p ON p.user_id = u.id
--   WHERE p.created_at >= now() - INTERVAL '24 hours'
--   GROUP BY u.id, u.name
--   HAVING COUNT(p.id) > 20
--   ORDER BY posts_24h DESC;
