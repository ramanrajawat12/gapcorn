ALTER TABLE store_products ADD COLUMN image_filename VARCHAR(255) NULL AFTER stock;

UPDATE store_products SET image_filename = 'gapcorn-elixirs-carton.png'
WHERE sku = 'GCI-ELX-WP-1KG' AND image_filename IS NULL;
