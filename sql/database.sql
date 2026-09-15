-- ==============================================================================
-- ModelCars Pro - E-Commerce Database Schema
-- Database: model_cars_db
-- Designed for ICT2142 E-Business Systems Project
-- ==============================================================================

CREATE DATABASE IF NOT EXISTS `model_cars_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `model_cars_db`;

-- ------------------------------------------------------------------------------
-- 1. Table: users
-- ------------------------------------------------------------------------------
DROP TABLE IF EXISTS `order_items`;
DROP TABLE IF EXISTS `orders`;
DROP TABLE IF EXISTS `products`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `full_name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(150) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `phone` VARCHAR(30) NULL,
    `address` TEXT NULL,
    `city` VARCHAR(50) NULL,
    `postal_code` VARCHAR(20) NULL,
    `role` ENUM('customer', 'admin') DEFAULT 'customer',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------------------------
-- 2. Table: categories
-- ------------------------------------------------------------------------------
CREATE TABLE `categories` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `slug` VARCHAR(100) NOT NULL UNIQUE,
    `description` TEXT NULL,
    `icon` VARCHAR(50) DEFAULT '🏎',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------------------------
-- 3. Table: products
-- ------------------------------------------------------------------------------
CREATE TABLE `products` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `category_id` INT NOT NULL,
    `brand` VARCHAR(80) NOT NULL,
    `name` VARCHAR(150) NOT NULL,
    `slug` VARCHAR(150) NOT NULL UNIQUE,
    `scale` VARCHAR(20) DEFAULT '1:64',
    `description` TEXT NOT NULL,
    `price` DECIMAL(10, 2) NOT NULL,
    `old_price` DECIMAL(10, 2) NULL,
    `stock_quantity` INT DEFAULT 10,
    `rating` DECIMAL(2, 1) DEFAULT 5.0,
    `reviews_count` INT DEFAULT 0,
    `badge` VARCHAR(50) NULL,
    `image` VARCHAR(255) NOT NULL,
    `is_featured` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_products_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------------------------
-- 4. Table: orders
-- ------------------------------------------------------------------------------
CREATE TABLE `orders` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `order_number` VARCHAR(50) NOT NULL UNIQUE,
    `user_id` INT NULL,
    `customer_name` VARCHAR(100) NOT NULL,
    `customer_email` VARCHAR(150) NOT NULL,
    `customer_phone` VARCHAR(30) NOT NULL,
    `shipping_address` TEXT NOT NULL,
    `city` VARCHAR(50) NOT NULL,
    `postal_code` VARCHAR(20) NOT NULL,
    `payment_method` VARCHAR(50) NOT NULL DEFAULT 'cod',
    `subtotal` DECIMAL(10, 2) NOT NULL,
    `shipping_fee` DECIMAL(10, 2) DEFAULT 0.00,
    `total_amount` DECIMAL(10, 2) NOT NULL,
    `order_status` ENUM('Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled') DEFAULT 'Pending',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_orders_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------------------------
-- 5. Table: order_items
-- ------------------------------------------------------------------------------
CREATE TABLE `order_items` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `order_id` INT NOT NULL,
    `product_id` INT NOT NULL,
    `product_name` VARCHAR(150) NOT NULL,
    `product_price` DECIMAL(10, 2) NOT NULL,
    `quantity` INT NOT NULL,
    `total_price` DECIMAL(10, 2) NOT NULL,
    CONSTRAINT `fk_order_items_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_order_items_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==============================================================================
-- SAMPLE SEED DATA
-- ==============================================================================

-- Categories
INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `icon`) VALUES
(1, 'Sports Cars', 'sports-cars', 'Modern supercars and exotic high-performance racing models', '🏁'),
(2, 'Classics', 'classics', 'Iconic legends from the golden age of motoring', '🚗'),
(3, 'Vintage', 'vintage', 'Timeless pre-war and mid-century collectibles', '🔧'),
(4, 'Limited Edition', 'limited-edition', 'Rare collector die-cast models with numbered certificates', '⭐'),
(5, 'Gift Sets', 'gift-sets', 'Curated multipacks and collector presentation sets', '🎁'),
(6, 'Premium Collection', 'premium-collection', 'Highly detailed precision scale models for serious collectors', '🏆');

-- Products matching the images in images/products/
INSERT INTO `products` (`id`, `category_id`, `brand`, `name`, `slug`, `scale`, `description`, `price`, `old_price`, `stock_quantity`, `rating`, `reviews_count`, `badge`, `image`, `is_featured`) VALUES
(1, 1, 'Hot Wheels', 'Ferrari F40 Die-Cast', 'ferrari-f40-die-cast', '1:64', 'The legendary Ferrari F40 rendered in stunning crimson red with detailed headlights, rear wing, and authentic alloy wheels.', 599.00, 899.00, 15, 5.0, 24, 'Hot Deal', 'Ferrari F40.jpeg', 1),
(2, 4, 'Matchbox', 'Lamborghini Countach 1:64', 'lamborghini-countach-1-64', '1:64', 'Vibrant yellow Lamborghini Countach featuring iconic wedge styling, flared wheel arches, and collector display pedestal.', 750.00, NULL, 8, 4.8, 18, 'Limited', 'Lamborghini Countach.jpeg', 1),
(3, 1, 'Hot Wheels', 'Bugatti Chiron Special Edition', 'bugatti-chiron-special-edition', '1:64', 'Two-tone metallic French racing blue and carbon black Bugatti Chiron hypercar with precision grille casting.', 699.00, NULL, 20, 4.9, 32, 'New', 'Bugatti Chiron.jpeg', 1),
(4, 6, 'Tamiya', 'Porsche 911 GT2 Precision Model', 'porsche-911-gt2-precision-model', '1:24', 'White competition Porsche 911 GT2 with oversized bi-plane rear wing, racing livery (#24 Shell Bosch), and multi-piece BBS wheels.', 1299.00, NULL, 6, 4.7, 21, NULL, 'Porsche 911 GT2.jpeg', 1),
(5, 1, 'Hot Wheels', 'McLaren F1 Collector\'s Series', 'mclaren-f1-collectors-series', '1:64', 'Metallic silver McLaren F1 three-seater supercar model with dihedral door contours and gold-foil heat shield engine bay detail.', 599.00, 799.00, 12, 5.0, 45, 'Popular', 'McLaren F1 .jpeg', 1),
(6, 2, 'Matchbox', 'Classic Jaguar E-Type Vintage', 'classic-jaguar-e-type-vintage', '1:43', 'Classic British Racing Green Jaguar E-Type Coupe with wire-spoke wheels, chrome bumpers, and classic license plate E-TYPE 1.', 850.00, NULL, 10, 4.6, 19, NULL, 'Classic Jaguar E-Type.jpeg', 1),
(7, 1, 'Hot Wheels', 'Mazda RX-7 FD Die-Cast', 'mazda-rx-7-fd-die-cast', '1:64', 'Iconic crimson red Mazda RX-7 FD twin-turbo rotary sports car. Features aerodynamic front lip, smooth coupe body contours, 5-spoke silver alloy wheels, and rear wing spoiler.', 650.00, 799.00, 14, 4.9, 28, 'New', 'Mazda RX-7 FD.jpeg', 1),
(8, 1, 'Hot Wheels', 'Toyota GR Supra 1:64', 'toyota-gr-supra-1-64', '1:64', 'Vibrant Lightning Yellow Toyota GR Supra scale model. Features sculpted aerodynamic ducktail spoiler, double-bubble roof, dual exhaust outlets, and gloss multi-spoke dark alloy wheels.', 699.00, NULL, 18, 4.8, 22, 'Hot Deal', 'Toyota GR Supra.jpeg', 1),
(9, 1, 'Matchbox', 'McLaren 720S Supercar', 'mclaren-720s-supercar', '1:64', 'Signature Azores Orange metallic McLaren 720S die-cast model with gloss black roof canopy, eye-socket aero intakes, detailed headlights, and twin high-exit titanium-style exhaust tips.', 799.00, 950.00, 10, 5.0, 34, 'Popular', 'McLaren 720S.jpeg', 1),
(10, 1, 'Hot Wheels', 'Chevrolet Corvette C8 Stingray', 'chevrolet-corvette-c8-stingray', '1:64', 'Amplify Orange mid-engine Chevrolet Corvette C8 Stingray with dual matte black racing stripes, aggressive front splitter, side air-intake boomerangs, and black sport wheels.', 750.00, NULL, 16, 4.9, 25, 'New', 'Chevrolet Corvette C8.jpeg', 1),
(11, 1, 'Tamiya', 'Porsche 911 GT3 RS (992)', 'porsche-911-gt3-rs-992', '1:64', 'Lizard Green Porsche 911 GT3 RS (992) track weapon featuring massive swan-neck rear wing with DRS actuator, carbon-fiber hood vents, front fender louvers, and lightweight BBS center-lock wheels.', 1199.00, 1399.00, 8, 5.0, 41, 'Limited', 'Porsche 911 GT3 RS.jpeg', 1);

-- Default Demo Admin User (Password: password123)
INSERT INTO `users` (`id`, `full_name`, `email`, `password`, `phone`, `city`, `role`) VALUES
(1, 'ModelCars Admin', 'admin@modelcars.com', '$2y$10$4n9xHwM7gCeqYjKx0W8tfeU0Gk05j4C2J1lK7lS0yU1qF5n9wQn2O', '+94 77 123 4567', 'Colombo', 'admin');
