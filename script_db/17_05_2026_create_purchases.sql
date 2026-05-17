-- Migration: Create purchases and purchase_details tables
-- Tracks shopping trips with multiple products from multiple suppliers

CREATE TABLE IF NOT EXISTS purchases (
    id_purchase INT AUTO_INCREMENT PRIMARY KEY,
    purchase_date DATE NOT NULL,
    id_user INT NOT NULL,
    observation TEXT NULL,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_purchases_user FOREIGN KEY (id_user) REFERENCES users(id_user)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS purchase_details (
    id_purchase_detail INT AUTO_INCREMENT PRIMARY KEY,
    id_purchase INT NOT NULL,
    id_product INT NOT NULL,
    id_supplier INT NOT NULL,
    quantity DECIMAL(10,2) NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    observation TEXT NULL,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_details_purchase FOREIGN KEY (id_purchase) REFERENCES purchases(id_purchase) ON DELETE CASCADE,
    CONSTRAINT fk_details_product FOREIGN KEY (id_product) REFERENCES products(id_product),
    CONSTRAINT fk_details_supplier FOREIGN KEY (id_supplier) REFERENCES suppliers(id_supplier)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Indexes for purchase_details
CREATE INDEX idx_details_purchase ON purchase_details(id_purchase);
CREATE INDEX idx_details_product ON purchase_details(id_product);
CREATE INDEX idx_details_supplier ON purchase_details(id_supplier);
