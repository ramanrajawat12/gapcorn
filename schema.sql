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
    expires_time TIME NOT NULL DEFAULT '23:59:59',
    is_enabled TINYINT(1) NOT NULL DEFAULT 1,
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

CREATE TABLE IF NOT EXISTS store_products (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    sku VARCHAR(48) NOT NULL UNIQUE,
    name VARCHAR(180) NOT NULL,
    description TEXT NOT NULL,
    price_paise INT UNSIGNED NOT NULL,
    stock INT UNSIGNED NOT NULL,
    image_filename VARCHAR(255) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS store_orders (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    product_id INT UNSIGNED NOT NULL,
    customer_id INT UNSIGNED NOT NULL,
    quantity INT UNSIGNED NOT NULL CHECK (quantity > 0),
    unit_price_paise INT UNSIGNED NOT NULL,
    status ENUM('processing', 'shipped', 'delivered', 'cancelled') NOT NULL DEFAULT 'processing',
    paid_at DATETIME(3) NULL,
    ordered_at DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
    CONSTRAINT store_orders_product_fk FOREIGN KEY (product_id) REFERENCES store_products(id),
    CONSTRAINT store_orders_customer_fk FOREIGN KEY (customer_id) REFERENCES customers(id),
    INDEX store_orders_product_date (product_id, ordered_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO store_products (sku, name, description, price_paise, stock, image_filename)
VALUES ('GCI-ELX-WP-1KG', 'Gapcorn Elixirs Washing Powder',
        'Laundry detergent powder for everyday washing, available in a 1 kg paperboard carton.', 24900, 120, 'gapcorn-elixirs-carton.png')
ON DUPLICATE KEY UPDATE id = id;

SET @gapcorn_previous_timezone = @@SESSION.time_zone;
SET SESSION time_zone = '+00:00';

INSERT INTO reward_settings (id, points) VALUES (1, 10)
ON DUPLICATE KEY UPDATE id = id;

INSERT INTO purchase_reward_settings (id, points) VALUES (1, 0)
ON DUPLICATE KEY UPDATE id = id;

SET SESSION time_zone = @gapcorn_previous_timezone;
