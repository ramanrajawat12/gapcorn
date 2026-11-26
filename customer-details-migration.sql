USE gapcorn;

ALTER TABLE customers
    ADD COLUMN name VARCHAR(120) NULL AFTER id,
    ADD COLUMN phone VARCHAR(30) NULL AFTER email,
    ADD COLUMN address TEXT NULL AFTER phone;

CREATE TABLE IF NOT EXISTS customer_purchases (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    customer_id INT UNSIGNED NOT NULL,
    product_name VARCHAR(180) NOT NULL,
    purchased_at DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
    CONSTRAINT customer_purchases_customer_fk FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    INDEX customer_purchases_customer_date (customer_id, purchased_at)
) ENGINE=InnoDB;
