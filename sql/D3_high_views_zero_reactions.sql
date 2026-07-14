-- D3 — Posts with over 100 views but zero reactions
--
-- A "view" is an interaction of type 'view'; a "reaction" is any engagement
-- beyond a view (like / save / share). Surfaces content that is seen a lot but
-- never reacted to — a useful signal for quality or ranking review.
--
-- Run: psql -h 127.0.0.1 -U postgres -d guisedup -f sql/D3_high_views_zero_reactions.sql

SELECT
    p.id,
    p.user_id AS author_id,
    p.caption,
    COUNT(*) FILTER (WHERE i.type = 'view')  AS views,
    COUNT(*) FILTER (WHERE i.type <> 'view') AS reactions
FROM posts p
JOIN interactions i ON i.post_id = p.id
GROUP BY p.id, p.user_id, p.caption
HAVING COUNT(*) FILTER (WHERE i.type = 'view')  > 100
   AND COUNT(*) FILTER (WHERE i.type <> 'view') = 0
ORDER BY views DESC;
