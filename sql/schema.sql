-- Lucid Auto Haus — database schema
-- Import:  mysql -u root < sql/schema.sql && mysql -u root < sql/seed.sql

CREATE DATABASE IF NOT EXISTS onemillioncar_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE onemillioncar_db;

DROP TABLE IF EXISTS leads;
DROP TABLE IF EXISTS vehicle_images;
DROP TABLE IF EXISTS vehicles;
DROP TABLE IF EXISTS testimonials;
DROP TABLE IF EXISTS faqs;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS settings;

CREATE TABLE settings (
    `key`   VARCHAR(64) NOT NULL PRIMARY KEY,
    `value` TEXT NULL
) ENGINE=InnoDB;

CREATE TABLE users (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    username      VARCHAR(50)  NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    display_name  VARCHAR(100) NOT NULL DEFAULT '',
    last_login_at DATETIME NULL,
    created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE vehicles (
    id             INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    slug           VARCHAR(160) NOT NULL UNIQUE,
    year           SMALLINT UNSIGNED NOT NULL,
    make           VARCHAR(60)  NOT NULL,
    model          VARCHAR(80)  NOT NULL,
    trim           VARCHAR(80)  NOT NULL DEFAULT '',
    body_type      VARCHAR(30)  NOT NULL DEFAULT 'Sedan',
    `condition`    ENUM('used','certified','new') NOT NULL DEFAULT 'used',
    status         ENUM('available','pending','sold','hidden') NOT NULL DEFAULT 'available',
    price          DECIMAL(10,2) NOT NULL DEFAULT 0,
    sale_price     DECIMAL(10,2) NULL,
    mileage        INT UNSIGNED NOT NULL DEFAULT 0,
    transmission   VARCHAR(30)  NOT NULL DEFAULT 'Automatic',
    drivetrain     VARCHAR(20)  NOT NULL DEFAULT 'AWD',
    fuel_type      VARCHAR(20)  NOT NULL DEFAULT 'Gasoline',
    engine         VARCHAR(80)  NOT NULL DEFAULT '',
    horsepower     SMALLINT UNSIGNED NULL,
    fuel_economy   VARCHAR(40)  NOT NULL DEFAULT '',
    exterior_color VARCHAR(40)  NOT NULL DEFAULT '',
    interior_color VARCHAR(40)  NOT NULL DEFAULT '',
    color_hex      CHAR(7)      NOT NULL DEFAULT '#3a3a3f',
    doors          TINYINT UNSIGNED NOT NULL DEFAULT 4,
    seats          TINYINT UNSIGNED NOT NULL DEFAULT 5,
    vin            VARCHAR(17)  NOT NULL DEFAULT '',
    stock_number   VARCHAR(30)  NOT NULL DEFAULT '',
    description    TEXT NULL,
    features       TEXT NULL,
    cover_image    VARCHAR(255) NOT NULL DEFAULT '',
    is_featured    TINYINT(1)   NOT NULL DEFAULT 0,
    views          INT UNSIGNED NOT NULL DEFAULT 0,
    sort_order     INT NOT NULL DEFAULT 0,
    created_at     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_make (make),
    INDEX idx_body (body_type),
    INDEX idx_price (price),
    INDEX idx_year (year)
) ENGINE=InnoDB;

CREATE TABLE vehicle_images (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    vehicle_id INT UNSIGNED NOT NULL,
    path       VARCHAR(255) NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_vehicle_images_vehicle FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE leads (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    type       ENUM('contact','inquiry','test_drive','trade_in','financing') NOT NULL,
    status     ENUM('new','contacted','closed') NOT NULL DEFAULT 'new',
    vehicle_id INT UNSIGNED NULL,
    name       VARCHAR(100) NOT NULL,
    email      VARCHAR(150) NOT NULL,
    phone      VARCHAR(40)  NOT NULL DEFAULT '',
    message    TEXT NULL,
    details    TEXT NULL,
    ip         VARCHAR(45)  NOT NULL DEFAULT '',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_type (type),
    CONSTRAINT fk_leads_vehicle FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE testimonials (
    id           INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name         VARCHAR(100) NOT NULL,
    location     VARCHAR(100) NOT NULL DEFAULT '',
    vehicle      VARCHAR(120) NOT NULL DEFAULT '',
    rating       TINYINT UNSIGNED NOT NULL DEFAULT 5,
    quote        TEXT NOT NULL,
    is_published TINYINT(1) NOT NULL DEFAULT 1,
    sort_order   INT NOT NULL DEFAULT 0,
    created_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE faqs (
    id           INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    question     VARCHAR(255) NOT NULL,
    answer       TEXT NOT NULL,
    is_published TINYINT(1) NOT NULL DEFAULT 1,
    sort_order   INT NOT NULL DEFAULT 0
) ENGINE=InnoDB;
