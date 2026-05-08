-- Step 1: Create products table
-- price_id is a plain integer (no FK) pointing to the latest price record
CREATE TABLE IF NOT EXISTS `products` (
  `id_product`  INT(11)       NOT NULL AUTO_INCREMENT,
  `name`        VARCHAR(255)  NOT NULL,
  `description` TEXT          NULL,
  `price_id`    INT(11)       NULL,
  `status`      TINYINT(1)    NOT NULL DEFAULT 1,
  `created_at`  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_product`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Step 2: Create prices table with FK to products
-- The only FK is prices.id_product -> products.id_product
CREATE TABLE IF NOT EXISTS `prices` (
  `id_price`   INT(11)        NOT NULL AUTO_INCREMENT,
  `id_product` INT(11)        NOT NULL,
  `price`      DECIMAL(10,2)  NOT NULL,
  `created_at` TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_price`),
  CONSTRAINT `fk_prices_product`
    FOREIGN KEY (`id_product`) REFERENCES `products` (`id_product`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
