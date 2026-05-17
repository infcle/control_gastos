-- Step 2: Create suppliers table
CREATE TABLE IF NOT EXISTS `suppliers` (
  `id_supplier` INT(11)       NOT NULL AUTO_INCREMENT,
  `name`        VARCHAR(255)  NOT NULL,
  `location`    TEXT          NULL,
  `deleted_at`  TIMESTAMP     NULL DEFAULT NULL,
  `created_at`  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_supplier`),
  UNIQUE KEY `uk_supplier_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
