-- Migration: Add id_category column to products table
-- Adds foreign key to categories table

ALTER TABLE products
    ADD COLUMN id_category INT NULL AFTER description,
    ADD CONSTRAINT fk_products_category
        FOREIGN KEY (id_category) REFERENCES categories(id_category)
        ON DELETE SET NULL;
