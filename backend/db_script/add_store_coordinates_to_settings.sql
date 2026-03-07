-- Adds store coordinates used by mobile delivery fee + ETA computations.
-- Safe to run on MySQL 8+ / MariaDB that supports IF NOT EXISTS.

    ALTER TABLE site_settings
        ADD COLUMN IF NOT EXISTS store_latitude DECIMAL(10, 8) NULL AFTER free_delivery_threshold,
        ADD COLUMN IF NOT EXISTS store_longitude DECIMAL(11, 8) NULL AFTER store_latitude;

-- Optional: set an initial value (replace with your real store coordinates).
-- UPDATE site_settings
-- SET store_latitude = 14.59950000,
--     store_longitude = 120.98420000
-- WHERE id = 1;

