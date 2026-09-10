-- Ethiopian Bookstore schema (utf8mb4)
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE DATABASE IF NOT EXISTS `www_project` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `www_project`;

DROP TABLE IF EXISTS `order_items`;
DROP TABLE IF EXISTS `orders`;
DROP TABLE IF EXISTS `messages`;
DROP TABLE IF EXISTS `login_attempts`;
DROP TABLE IF EXISTS `books`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `publisher`;
DROP TABLE IF EXISTS `customers`;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `admin`;

CREATE TABLE `admin` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(60) NOT NULL,
  `pass` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `admin_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `users` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(190) NOT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(120) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `publisher` (
  `publisherid` int unsigned NOT NULL AUTO_INCREMENT,
  `publisher_name` varchar(120) NOT NULL,
  PRIMARY KEY (`publisherid`),
  UNIQUE KEY `publisher_name_unique` (`publisher_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `categories` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(80) NOT NULL,
  `slug` varchar(80) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `categories_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `books` (
  `book_isbn` varchar(20) NOT NULL,
  `book_title` varchar(160) NOT NULL,
  `book_author` varchar(120) NOT NULL,
  `book_image` varchar(120) DEFAULT NULL,
  `book_descr` text,
  `book_price` decimal(10,2) NOT NULL,
  `publisherid` int unsigned NOT NULL,
  `category_id` int unsigned DEFAULT NULL,
  `language` varchar(10) NOT NULL DEFAULT 'am',
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`book_isbn`),
  KEY `books_publisher_fk` (`publisherid`),
  KEY `books_category_fk` (`category_id`),
  CONSTRAINT `books_publisher_fk` FOREIGN KEY (`publisherid`) REFERENCES `publisher` (`publisherid`),
  CONSTRAINT `books_category_fk` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `customers` (
  `customerid` int unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned DEFAULT NULL,
  `name` varchar(120) NOT NULL,
  `address` varchar(160) NOT NULL,
  `city` varchar(80) NOT NULL,
  `zip_code` varchar(20) NOT NULL,
  `country` varchar(80) NOT NULL,
  `email` varchar(190) DEFAULT NULL,
  PRIMARY KEY (`customerid`),
  KEY `customers_user_fk` (`user_id`),
  CONSTRAINT `customers_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `orders` (
  `orderid` int unsigned NOT NULL AUTO_INCREMENT,
  `customerid` int unsigned NOT NULL,
  `user_id` int unsigned DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `delivery_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ship_name` varchar(120) NOT NULL,
  `ship_address` varchar(160) NOT NULL,
  `ship_city` varchar(80) NOT NULL,
  `ship_zip_code` varchar(20) NOT NULL,
  `ship_country` varchar(80) NOT NULL,
  `status` enum('new','paid','shipped','cancelled') NOT NULL DEFAULT 'new',
  `payment_status` enum('pending','paid','failed') NOT NULL DEFAULT 'pending',
  `payment_tx_ref` varchar(120) DEFAULT NULL,
  `shipment_notes` text,
  PRIMARY KEY (`orderid`),
  UNIQUE KEY `orders_tx_ref_unique` (`payment_tx_ref`),
  KEY `orders_customer_fk` (`customerid`),
  KEY `orders_user_fk` (`user_id`),
  CONSTRAINT `orders_customer_fk` FOREIGN KEY (`customerid`) REFERENCES `customers` (`customerid`),
  CONSTRAINT `orders_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `order_items` (
  `orderid` int unsigned NOT NULL,
  `book_isbn` varchar(20) NOT NULL,
  `item_price` decimal(10,2) NOT NULL,
  `quantity` smallint unsigned NOT NULL,
  PRIMARY KEY (`orderid`, `book_isbn`),
  KEY `order_items_book_fk` (`book_isbn`),
  CONSTRAINT `order_items_order_fk` FOREIGN KEY (`orderid`) REFERENCES `orders` (`orderid`) ON DELETE CASCADE,
  CONSTRAINT `order_items_book_fk` FOREIGN KEY (`book_isbn`) REFERENCES `books` (`book_isbn`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `messages` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(120) NOT NULL,
  `email` varchar(190) NOT NULL,
  `body` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
