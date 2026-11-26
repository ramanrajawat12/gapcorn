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

CREATE TABLE IF NOT EXISTS customers (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(254) COLLATE utf8mb4_bin NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    points INT NOT NULL CHECK (points >= 0),
    reward_id INT UNSIGNED NOT NULL,
    rewarded_at DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
    last_active_at DATETIME(3) NULL,
    CONSTRAINT customers_welcome_reward_fk FOREIGN KEY (reward_id) REFERENCES reward_settings(id)
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
