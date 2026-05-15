-- Create products and prices tables with versioning
-- Products table with basic information
CREATE TABLE IF NOT EXISTS `products` (
    `id_product` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `description` TEXT,
    `price_id` INT,
    `status` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` TIMESTAMP NULL,
    FOREIGN KEY (`price_id`) REFERENCES `prices`(`id_price`)
);

-- Prices table with price history
CREATE TABLE IF NOT EXISTS `prices` (
    `id_price` INT AUTO_INCREMENT PRIMARY KEY,
    `price` DECIMAL(10, 2) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert some initial price records
INSERT IGNORE INTO `prices` (`price`, `created_at`) VALUES
(0.00, NOW()),
(10.00, NOW()),
(25.50, NOW()),
(99.99, NOW()),
(150.00, NOW());

-- Insert some sample products
INSERT IGNORE INTO `products` (`name`, `description`, `price_id`, `status`, `created_at`) VALUES
('Sample Product 1', 'This is a sample product description', 2, 1, NOW()),
('Sample Product 2', 'Another sample product with detailed description', 3, 1, NOW()),
('Sample Product 3', 'Premium product with features', 5, 1, NOW()),
('Sample Product 4', 'Basic product for testing', 1, 1, NOW()),
('Sample Product 5', 'Advanced product with multiple options', 4, 1, NOW());

-- Indexes for better performance
CREATE INDEX IF NOT EXISTS `idx_products_name` ON `products`(`name`);
CREATE INDEX IF NOT EXISTS `idx_products_status` ON `products`(`status`);
CREATE INDEX IF NOT EXISTS `idx_products_deleted_at` ON `products`(`deleted_at`);
CREATE INDEX IF NOT EXISTS `idx_prices_created_at` ON `prices`(`created_at`);
