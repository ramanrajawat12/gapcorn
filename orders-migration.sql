CREATE TABLE IF NOT EXISTS store_products (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    sku VARCHAR(48) NOT NULL UNIQUE,
    name VARCHAR(180) NOT NULL,
    description TEXT NOT NULL,
    price_paise INT UNSIGNED NOT NULL,
    stock INT UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS store_orders (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    product_id INT UNSIGNED NOT NULL,
    customer_id INT UNSIGNED NOT NULL,
    quantity INT UNSIGNED NOT NULL CHECK (quantity > 0),
    unit_price_paise INT UNSIGNED NOT NULL,
    status ENUM('processing', 'shipped', 'delivered', 'cancelled') NOT NULL DEFAULT 'processing',
    ordered_at DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
    CONSTRAINT store_orders_product_fk FOREIGN KEY (product_id) REFERENCES store_products(id),
    CONSTRAINT store_orders_customer_fk FOREIGN KEY (customer_id) REFERENCES customers(id),
    INDEX store_orders_product_date (product_id, ordered_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO store_products (sku, name, description, price_paise, stock)
VALUES ('GCI-ELX-WP-1KG', 'Gapcorn Elixirs Washing Powder',
        'Laundry detergent powder for everyday washing, available in a 1 kg paperboard carton.', 24900, 120)
ON DUPLICATE KEY UPDATE id = id;
