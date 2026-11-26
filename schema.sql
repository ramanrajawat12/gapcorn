CREATE DATABASE IF NOT EXISTS gapcorn CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE gapcorn;

CREATE TABLE IF NOT EXISTS reward_settings (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    points INT NOT NULL CHECK (points >= 0),
    effective_at DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS purchase_reward_settings (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    points INT NOT NULL CHECK (points >= 0),
    effective_at DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS purchase_reward_campaigns (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    points INT NOT NULL CHECK (points >= 0),
    starts_on DATE NOT NULL,
    expires_on DATE NOT NULL,
    created_at DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
    CHECK (expires_on >= starts_on)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS customers (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NULL,
    email VARCHAR(254) COLLATE utf8mb4_bin NOT NULL UNIQUE,
    phone VARCHAR(30) NULL,
    address TEXT NULL,
    password_hash VARCHAR(255) NOT NULL,
    points INT NOT NULL CHECK (points >= 0),
    reward_id INT UNSIGNED NOT NULL,
    rewarded_at DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
    last_active_at DATETIME(3) NULL,
    CONSTRAINT customers_welcome_reward_fk FOREIGN KEY (reward_id) REFERENCES reward_settings(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS customer_purchases (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    customer_id INT UNSIGNED NOT NULL,
    product_name VARCHAR(180) NOT NULL,
    purchased_at DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
    CONSTRAINT customer_purchases_customer_fk FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    INDEX customer_purchases_customer_date (customer_id, purchased_at)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS demo_customers (
    id INT UNSIGNED NOT NULL PRIMARY KEY,
    name VARCHAR(80) NOT NULL,
    email VARCHAR(254) COLLATE utf8mb4_bin NOT NULL UNIQUE,
    points INT NOT NULL CHECK (points >= 0),
    joined_at DATETIME(3) NOT NULL
) ENGINE=InnoDB;

SET @gapcorn_previous_timezone = @@SESSION.time_zone;
SET SESSION time_zone = '+00:00';

INSERT INTO reward_settings (id, points) VALUES (1, 10)
ON DUPLICATE KEY UPDATE id = id;

INSERT INTO purchase_reward_settings (id, points) VALUES (1, 0)
ON DUPLICATE KEY UPDATE id = id;

SET SESSION time_zone = @gapcorn_previous_timezone;
