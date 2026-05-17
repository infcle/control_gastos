-- Migration: Add id_category column to products table
-- Adds foreign key to categories table
-- Note: products must be InnoDB (not MyISAM) for FK support

-- Step 1: Convert to InnoDB if still MyISAM
ALTER TABLE products ENGINE = InnoDB;

-- Step 2: Add column if not exists (use try/ignore pattern)
ALTER TABLE products
    ADD COLUMN id_category INT NULL AFTER description;

-- Step 3: Add FK constraint
ALTER TABLE products
    ADD CONSTRAINT fk_products_category
        FOREIGN KEY (id_category) REFERENCES categories(id_category)
        ON DELETE SET NULL;
