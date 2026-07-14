-- D1 — Top 10 active users in the last 7 days
--
-- "Activity" = content created (posts) + engagement produced (interactions)
-- within the trailing 7-day window. Users with no activity are excluded.
--
-- Run: psql -h 127.0.0.1 -U postgres -d guisedup -f sql/D1_top_active_users_last_7_days.sql

SELECT
    u.id,
    u.name,
    COUNT(DISTINCT p.id)                          AS posts_created,
    COUNT(DISTINCT i.id)                          AS interactions_made,
    COUNT(DISTINCT p.id) + COUNT(DISTINCT i.id)   AS activity_score
FROM users u
LEFT JOIN posts p
       ON p.user_id = u.id
      AND p.created_at >= now() - INTERVAL '7 days'
LEFT JOIN interactions i
       ON i.user_id = u.id
      AND i.created_at >= now() - INTERVAL '7 days'
GROUP BY u.id, u.name
HAVING COUNT(DISTINCT p.id) + COUNT(DISTINCT i.id) > 0
ORDER BY activity_score DESC, u.id ASC
LIMIT 10;
