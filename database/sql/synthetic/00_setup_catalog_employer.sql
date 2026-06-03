-- Hirevo synthetic catalog: employer user for 20k demo jobs
-- Default password (change after import): ChangeMeCatalog!

INSERT INTO `users` (`name`, `email`, `phone`, `email_verified_at`, `password`, `role`, `status`, `created_at`, `updated_at`)
SELECT 'Hirevo Catalog Employer', 'catalog-employer@hirevo.com', NULL, '2026-06-02 23:36:51', '$2y$10$ErdGq7fZgelnehJCPA5ROO.oUpILAKHkA7kjC0DWo8ZvPdBWfvuUC', 'referrer', 'active', '2026-06-02 23:36:51', '2026-06-02 23:36:51'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `users` WHERE `email` = 'catalog-employer@hirevo.com' LIMIT 1);

SET @catalog_user_id = (SELECT `id` FROM `users` WHERE `email` = 'catalog-employer@hirevo.com' LIMIT 1);

INSERT INTO `referrer_profiles` (`user_id`, `company_name`, `company_email`, `is_approved`, `credits`, `created_at`, `updated_at`)
SELECT @catalog_user_id, 'Hirevo Catalog', 'catalog-employer@hirevo.com', 1, 100, '2026-06-02 23:36:51', '2026-06-02 23:36:51'
FROM DUAL
WHERE @catalog_user_id IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM `referrer_profiles` WHERE `user_id` = @catalog_user_id LIMIT 1);
