-- Add the reCAPTCHA v3 settings rows to an existing database.
-- Safe to run more than once: it only creates the rows, never overwrites keys
-- already entered in Admin -> Settings -> reCAPTCHA.
USE onemillioncar_db;
SET NAMES utf8mb4;

INSERT IGNORE INTO settings (`key`, `value`) VALUES
    ('recaptcha_enabled', '0'),
    ('recaptcha_site_key', ''),
    ('recaptcha_secret_key', ''),
    ('recaptcha_min_score', '0.5');
