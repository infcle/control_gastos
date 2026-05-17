-- Fix: Convert products table to InnoDB for FK support
-- Run this BEFORE add_category_to_products if the FK still fails

-- Convert MyISAM to InnoDB (preserves all data)
ALTER TABLE products ENGINE = InnoDB;

-- Now add FK constraints that MyISAM blocked
-- FK to categories (if not already present)
ALTER TABLE products
    ADD CONSTRAINT fk_products_category
        FOREIGN KEY (id_category) REFERENCES categories(id_category)
        ON DELETE SET NULL;

-- FK to prices (circular dependency - only if price_id column exists and products is InnoDB)
-- Note: any FK on price_id must be added manually if needed, since the circular
-- dependency (products->prices->products) requires specific handling
