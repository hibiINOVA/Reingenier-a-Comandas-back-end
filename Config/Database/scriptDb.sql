DEFAULT CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE `db_restaurant`;

CREATE TABLE IF NOT EXISTS `category` (
  `idcategory` VARCHAR(36) NOT NULL PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `description` TEXT NULL,
  `active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_category_active` (`active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `products` (
  `idproducts` VARCHAR(36) NOT NULL PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `price` DECIMAL(10,2) NOT NULL,
  `description` TEXT NULL,
  `category_idcategory` VARCHAR(36) NOT NULL,
  `active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_products_category` (`category_idcategory`),
  INDEX `idx_products_active` (`active`),
  CONSTRAINT `fk_products_category` 
    FOREIGN KEY (`category_idcategory`) 
    REFERENCES `category` (`idcategory`) 
    ON DELETE RESTRICT 
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `ingredients` (
  `idingredients` VARCHAR(36) NOT NULL PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `extra` DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Precio extra si se agrega',
  `cost` DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Costo del ingrediente',
  `stock` INT NOT NULL DEFAULT 0 COMMENT 'Stock disponible',
  `required` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1=obligatorio, 0=opcional',
  `active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_ingredients_active` (`active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `products_ingredients` (
  `id` VARCHAR(36) NOT NULL PRIMARY KEY,
  `products_idProducts` VARCHAR(36) NOT NULL,
  `ingredients_idingredients` VARCHAR(36) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_prod_ingr_product` (`products_idProducts`),
  INDEX `idx_prod_ingr_ingredient` (`ingredients_idingredients`),
  CONSTRAINT `fk_prod_ingr_product` 
    FOREIGN KEY (`products_idProducts`) 
    REFERENCES `products` (`idproducts`) 
    ON DELETE CASCADE 
    ON UPDATE CASCADE,
  CONSTRAINT `fk_prod_ingr_ingredient` 
    FOREIGN KEY (`ingredients_idingredients`) 
    REFERENCES `ingredients` (`idingredients`) 
    ON DELETE CASCADE 
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `users` (
  `idusers` VARCHAR(36) NOT NULL PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(20) NOT NULL,
  `rol` TINYINT(1) NOT NULL DEFAULT 2 COMMENT '0=admin, 1=cajero, 2=cocinero, 3=cliente, 4=cliente registrado',
  `actual_order` VARCHAR(36) NULL COMMENT 'Orden actual que está preparando',
  `active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_users_rol` (`rol`),
  INDEX `idx_users_active` (`active`),
  UNIQUE KEY `uk_users_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `order` (
  `idorder` VARCHAR(36) NOT NULL PRIMARY KEY,
  `date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `total` DECIMAL(10,2) NOT NULL,
  `status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '0=pendiente, 1=en preparación, 2=lista, 3=entregada, 4=cancelada',
  `origin` VARCHAR(50) NULL COMMENT 'Origen de la orden: mostrador, app, etc',
  `comments` TEXT NULL,
  `client` VARCHAR(100) NOT NULL,
  `users_idusers` VARCHAR(36) NOT NULL,
  `start_order` TIMESTAMP NULL COMMENT 'Hora de inicio de preparación',
  `finish_order` TIMESTAMP NULL COMMENT 'Hora de finalización',
  `active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_order_status` (`status`),
  INDEX `idx_order_date` (`date`),
  INDEX `idx_order_user` (`users_idusers`),
  INDEX `idx_order_active` (`active`),
  CONSTRAINT `fk_order_user` 
    FOREIGN KEY (`users_idusers`) 
    REFERENCES `users` (`idusers`) 
    ON DELETE RESTRICT 
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `order_details` (
  `idorderdetail` VARCHAR(36) NOT NULL PRIMARY KEY,
  `unit_price` DECIMAL(10,2) NOT NULL,
  `order_type` VARCHAR(50) NULL COMMENT 'Tipo de preparación: para llevar, comer aquí, etc',
  `comments` TEXT NULL,
  `order_idorder` VARCHAR(36) NOT NULL,
  `products_idproducts` VARCHAR(36) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_order_details_order` (`order_idorder`),
  INDEX `idx_order_details_product` (`products_idproducts`),
  CONSTRAINT `fk_order_details_order` 
    FOREIGN KEY (`order_idorder`) 
    REFERENCES `order` (`idorder`) 
    ON DELETE CASCADE 
    ON UPDATE CASCADE,
  CONSTRAINT `fk_order_details_product` 
    FOREIGN KEY (`products_idproducts`) 
    REFERENCES `products` (`idproducts`) 
    ON DELETE RESTRICT 
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `not_ingredient` (
  `idnot_ingredient` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `ingredients_idingredients` VARCHAR(36) NOT NULL,
  `order_details_idorderdetail` VARCHAR(36) NOT NULL,
  `type` TINYINT(1) NOT NULL COMMENT '0=excluir ingrediente, 1=agregar extra',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_not_ingr_ingredient` (`ingredients_idingredients`),
  INDEX `idx_not_ingr_order_detail` (`order_details_idorderdetail`),
  CONSTRAINT `fk_not_ingr_ingredient` 
    FOREIGN KEY (`ingredients_idingredients`) 
    REFERENCES `ingredients` (`idingredients`) 
    ON DELETE CASCADE 
    ON UPDATE CASCADE,
  CONSTRAINT `fk_not_ingr_order_detail` 
    FOREIGN KEY (`order_details_idorderdetail`) 
    REFERENCES `order_details` (`idorderdetail`) 
    ON DELETE CASCADE 
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `favorites` (
  `idfavorite` VARCHAR(36) NOT NULL PRIMARY KEY,
  `users_idusers` VARCHAR(36) NOT NULL,
  `products_idproducts` VARCHAR(36) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uk_fav_user_product` (`users_idusers`, `products_idproducts`),
  INDEX `idx_fav_user` (`users_idusers`),
  INDEX `idx_fav_product` (`products_idproducts`),
  CONSTRAINT `fk_fav_user`
    FOREIGN KEY (`users_idusers`)
    REFERENCES `users` (`idusers`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_fav_product`
    FOREIGN KEY (`products_idproducts`)
    REFERENCES `products` (`idproducts`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- DATOS DE EJEMPLO

INSERT INTO `category` (`idcategory`, `name`, `description`) VALUES
('cat-001', 'Hamburguesas Clásicas', 'Hamburguesas tradicionales'),
('cat-002', 'Hamburguesas Premium', 'Hamburguesas gourmet'),
('cat-003', 'Bebidas', 'Refrescos y bebidas'),
('cat-004', 'Acompañamientos', 'Papas, aros de cebolla, etc');

INSERT INTO `ingredients` (`idingredients`, `name`, `extra`, `cost`, `stock`, `required`) VALUES
('ing-001', 'Pan de hamburguesa', 0.00, 5.00, 100, 1),
('ing-002', 'Carne de res 150g', 0.00, 25.00, 50, 1),
('ing-003', 'Queso cheddar', 8.00, 5.00, 80, 0),
('ing-004', 'Lechuga', 0.00, 2.00, 100, 0),
('ing-005', 'Tomate', 0.00, 3.00, 100, 0),
('ing-006', 'Cebolla', 0.00, 2.00, 100, 0),
('ing-007', 'Pepinillos', 0.00, 3.00, 80, 0),
('ing-008', 'Tocino', 10.00, 8.00, 60, 0),
('ing-009', 'Salsa especial', 0.00, 1.00, 200, 0),
('ing-010', 'Mayonesa', 0.00, 1.00, 200, 0);

INSERT INTO `products_ingredients` (`id`, `products_idProducts`, `ingredients_idingredients`) VALUES
('pi-001', 'prod-001', 'ing-001'),
('pi-002', 'prod-001', 'ing-002'),
('pi-003', 'prod-001', 'ing-004'),
('pi-004', 'prod-001', 'ing-005'),
('pi-005', 'prod-001', 'ing-006'),
('pi-006', 'prod-002', 'ing-001'),
('pi-007', 'prod-002', 'ing-002'),
('pi-008', 'prod-002', 'ing-003'),
('pi-009', 'prod-002', 'ing-004'),
('pi-010', 'prod-002', 'ing-005');

INSERT INTO `products` (`idproducts`, `name`, `price`, `description`, `category_idcategory`) VALUES
('prod-001', 'Hamburguesa Clásica', 80.00, 'Hamburguesa tradicional con los ingredientes básicos', 'cat-001'),
('prod-002', 'Hamburguesa con Queso', 95.00, 'Hamburguesa con queso cheddar', 'cat-001'),
('prod-003', 'Hamburguesa BBQ', 120.00, 'Hamburguesa premium con tocino y salsa BBQ', 'cat-002'),
('prod-004', 'Refresco 600ml', 25.00, 'Refresco de cola', 'cat-003'),
('prod-005', 'Papas Fritas', 35.00, 'Papas a la francesa', 'cat-004');

INSERT INTO `products_ingredients` (`id`, `products_idProducts`, `ingredients_idingredients`) VALUES
('pi-001', 'prod-001', 'ing-001'),
('pi-002', 'prod-001', 'ing-002'),
('pi-003', 'prod-001', 'ing-004'),
('pi-004', 'prod-001', 'ing-005'),
('pi-005', 'prod-001', 'ing-006'),
('pi-006', 'prod-002', 'ing-001'),
('pi-007', 'prod-002', 'ing-002'),
('pi-008', 'prod-002', 'ing-003'),
('pi-009', 'prod-002', 'ing-004'),
('pi-010', 'prod-002', 'ing-005');

INSERT INTO `users` (`idusers`, `name`, `password`, `phone`, `rol`) VALUES
('user-001', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '1234567890', 1);

INSERT INTO `users` (`idusers`, `name`, `password`, `phone`, `rol`) VALUES
('user-002', 'chef', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0987654321', 2);

-- VISTAS

CREATE OR REPLACE VIEW `v_orders_details` AS
SELECT 
    o.idorder,
    o.date,
    o.client,
    o.total,
    o.status,
    o.comments AS order_comments,
    od.idorderdetail,
    p.name AS product_name,
    c.name AS category_name,
    od.unit_price,
    od.order_type,
    od.comments AS product_comments,
    u.name AS user_name
FROM `order` o
JOIN `order_details` od ON o.idorder = od.order_idorder
JOIN `products` p ON od.products_idproducts = p.idproducts
JOIN `category` c ON p.category_idcategory = c.idcategory
JOIN `users` u ON o.users_idusers = u.idusers
WHERE o.active = 1;

