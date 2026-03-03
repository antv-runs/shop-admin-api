SELECT
    c.id,
    c.name,
    COUNT(p.id) AS total_products
FROM categories c
LEFT JOIN products p
    ON p.category_id = c.id
    AND p.deleted_at IS NULL   -- ignore soft deleted products
WHERE c.deleted_at IS NULL     -- ignore soft deleted categories
GROUP BY c.id, c.name
ORDER BY total_products DESC;
