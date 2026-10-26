-- Week 08 Admin Dashboard migration
-- Safe to run once against the existing model_cars_db database.
USE model_cars_db;

ALTER TABLE products
    ADD COLUMN IF NOT EXISTS is_active TINYINT(1) NOT NULL DEFAULT 1 AFTER is_featured;

-- Existing products remain active by default.
UPDATE products SET is_active = 1 WHERE is_active IS NULL;
