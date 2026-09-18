-- Update the public consultant and contact information without resetting data.
-- Safe to run more than once.
USE onemillioncar_db;
SET NAMES utf8mb4;

INSERT INTO settings (`key`, `value`) VALUES
    ('agent_name', 'Bünyamin Akkaya'),
    ('phone', '+16479368096'),
    ('whatsapp', '16479368096'),
    ('address_line', '4-8044 Dixie Rd'),
    ('city', 'Brampton, ON L6T 5G8'),
    ('google_maps_url', 'https://maps.app.goo.gl/HzKh1ci3JHszJxrB7'),
    ('google_maps_embed', 'https://www.google.com/maps?q=43.6990399,-79.7089&z=17&output=embed')
ON DUPLICATE KEY UPDATE `value` = VALUES(`value`);
