-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: mycart_marketplace
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `mc_activity_logs`
--

DROP TABLE IF EXISTS `mc_activity_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mc_activity_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `actor_type` enum('admin','sub_admin','vendor','customer') NOT NULL,
  `actor_id` int(10) unsigned NOT NULL,
  `actor_name` varchar(150) NOT NULL,
  `action` varchar(100) NOT NULL,
  `module` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_actor` (`actor_type`,`actor_id`),
  KEY `idx_module` (`module`),
  KEY `idx_created` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mc_activity_logs`
--

LOCK TABLES `mc_activity_logs` WRITE;
/*!40000 ALTER TABLE `mc_activity_logs` DISABLE KEYS */;
INSERT INTO `mc_activity_logs` VALUES (1,'admin',1,'Super Admin','login','auth','Admin logged in','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36','2026-04-03 11:04:59'),(2,'admin',1,'Super Admin','login','auth','Admin logged in','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36','2026-04-03 18:48:28'),(3,'admin',1,'Super Admin','login','auth','Admin logged in','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36','2026-04-04 18:07:03'),(4,'admin',1,'Super Admin','login','auth','Admin logged in','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36','2026-04-06 14:04:13');
/*!40000 ALTER TABLE `mc_activity_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mc_admin_permissions`
--

DROP TABLE IF EXISTS `mc_admin_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mc_admin_permissions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `admin_id` int(10) unsigned NOT NULL,
  `module` enum('products','payments','reports','activity','banners','settlements','returns','disputes','coupons','gift_cards','brands') NOT NULL,
  `can_view` tinyint(1) NOT NULL DEFAULT 1,
  `can_create` tinyint(1) NOT NULL DEFAULT 0,
  `can_edit` tinyint(1) NOT NULL DEFAULT 0,
  `can_delete` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_admin_module` (`admin_id`,`module`),
  CONSTRAINT `fk_perm_admin` FOREIGN KEY (`admin_id`) REFERENCES `mc_admins` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mc_admin_permissions`
--

LOCK TABLES `mc_admin_permissions` WRITE;
/*!40000 ALTER TABLE `mc_admin_permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `mc_admin_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mc_admins`
--

DROP TABLE IF EXISTS `mc_admins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mc_admins` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('super_admin','sub_admin') NOT NULL DEFAULT 'sub_admin',
  `avatar` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `last_login` datetime DEFAULT NULL,
  `failed_login_attempts` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `locked_until` datetime DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_role` (`role`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mc_admins`
--

LOCK TABLES `mc_admins` WRITE;
/*!40000 ALTER TABLE `mc_admins` DISABLE KEYS */;
INSERT INTO `mc_admins` VALUES (1,'Super Admin','admin@mycart.com','$2y$12$05jI66oNhr6kverzc.FfD.bP6.7OgvfhWH.RoHtkLu4nRXvgR1jXK','super_admin',NULL,1,'2026-04-06 14:04:13',0,NULL,NULL,NULL,'2026-04-02 20:32:33','2026-04-06 14:04:13');
/*!40000 ALTER TABLE `mc_admins` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mc_banners`
--

DROP TABLE IF EXISTS `mc_banners`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mc_banners` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `link_url` varchar(500) DEFAULT NULL,
  `position` enum('hero','sidebar','popup','top_bar') NOT NULL DEFAULT 'hero',
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `starts_at` datetime DEFAULT NULL,
  `ends_at` datetime DEFAULT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_position` (`position`),
  KEY `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mc_banners`
--

LOCK TABLES `mc_banners` WRITE;
/*!40000 ALTER TABLE `mc_banners` DISABLE KEYS */;
/*!40000 ALTER TABLE `mc_banners` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mc_brands`
--

DROP TABLE IF EXISTS `mc_brands`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mc_brands` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `slug` varchar(160) NOT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mc_brands`
--

LOCK TABLES `mc_brands` WRITE;
/*!40000 ALTER TABLE `mc_brands` DISABLE KEYS */;
/*!40000 ALTER TABLE `mc_brands` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mc_cart_items`
--

DROP TABLE IF EXISTS `mc_cart_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mc_cart_items` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `cart_id` int(10) unsigned NOT NULL,
  `product_id` int(10) unsigned NOT NULL,
  `variant_id` int(10) unsigned DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `price` decimal(12,2) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_ci_cart` (`cart_id`),
  KEY `fk_ci_prod` (`product_id`),
  CONSTRAINT `fk_ci_cart` FOREIGN KEY (`cart_id`) REFERENCES `mc_carts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ci_prod` FOREIGN KEY (`product_id`) REFERENCES `mc_products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mc_cart_items`
--

LOCK TABLES `mc_cart_items` WRITE;
/*!40000 ALTER TABLE `mc_cart_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `mc_cart_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mc_carts`
--

DROP TABLE IF EXISTS `mc_carts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mc_carts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned DEFAULT NULL,
  `session_id` varchar(100) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_session` (`session_id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mc_carts`
--

LOCK TABLES `mc_carts` WRITE;
/*!40000 ALTER TABLE `mc_carts` DISABLE KEYS */;
INSERT INTO `mc_carts` VALUES (1,NULL,'71f1j1k4j8lgjrb5n59jll36p1','2026-04-02 20:41:26','2026-04-02 20:41:26'),(2,NULL,'hji6ev3380tgugnj20mt43890d','2026-04-03 11:03:54','2026-04-03 11:03:54'),(3,NULL,'tpgfq5ggc4oeqv5ct9npm0n4t3','2026-04-03 13:25:04','2026-04-03 13:25:04'),(4,NULL,'1m3ko71ee7sdupo7t0emqr475v','2026-04-11 20:10:45','2026-04-11 20:10:45'),(5,NULL,'g1k110hqj9950r0sb9cg17d7a4','2026-04-13 13:09:12','2026-04-13 13:09:12'),(6,NULL,'qfh01un3gulouo15cimlqiqvcd','2026-04-15 12:01:12','2026-04-15 12:01:12'),(7,NULL,'s903kt52k20glo0fer7k3b1gev','2026-04-20 18:30:22','2026-04-20 18:30:22'),(8,NULL,'km48u58hfkm76f500elj89qvef','2026-04-24 19:21:42','2026-04-24 19:21:42'),(9,NULL,'gk3hlqpsdkff3pmaov5putd2su','2026-04-24 19:21:42','2026-04-24 19:21:42'),(10,NULL,'ullk94b6matooqbcgcet7k6ge3','2026-04-27 19:01:36','2026-04-27 19:01:36'),(11,NULL,'214hugk8fmosluivae34eeepqh','2026-05-06 19:56:51','2026-05-06 19:56:51'),(12,NULL,'g4dfmdercnik7fif2l72hkebau','2026-07-01 13:41:21','2026-07-01 13:41:21'),(14,NULL,'fmt7ta925g1ovfj09mphohu95c','2026-07-18 02:03:19','2026-07-18 02:03:19'),(15,NULL,'pffuucojo9mqg81r7gb63au8o5','2026-07-18 02:03:31','2026-07-18 02:03:31'),(16,2,'sve3bhv9rl8782ob783bvn9qvm','2026-07-18 02:19:22','2026-07-18 02:19:22'),(17,NULL,'bsm5ulpo5bo3l4nghuqecl9vhn','2026-07-18 23:11:19','2026-07-18 23:11:19'),(18,NULL,'bp1o5bs76rjcv1a203vj1rkc7n','2026-07-19 10:53:09','2026-07-19 10:53:09');
/*!40000 ALTER TABLE `mc_carts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mc_categories`
--

DROP TABLE IF EXISTS `mc_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mc_categories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` int(10) unsigned DEFAULT NULL,
  `name` varchar(150) NOT NULL,
  `slug` varchar(150) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `idx_parent` (`parent_id`),
  CONSTRAINT `fk_cat_parent` FOREIGN KEY (`parent_id`) REFERENCES `mc_categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mc_categories`
--

LOCK TABLES `mc_categories` WRITE;
/*!40000 ALTER TABLE `mc_categories` DISABLE KEYS */;
INSERT INTO `mc_categories` VALUES (1,NULL,'Electronics','electronics',NULL,NULL,1,1,'2026-04-02 20:32:33','2026-04-02 20:32:33'),(2,NULL,'Fashion','fashion',NULL,NULL,2,1,'2026-04-02 20:32:33','2026-04-02 20:32:33'),(3,NULL,'Home & Kitchen','home-kitchen',NULL,NULL,3,1,'2026-04-02 20:32:33','2026-04-02 20:32:33'),(4,NULL,'Sports','sports',NULL,NULL,4,1,'2026-04-02 20:32:33','2026-04-02 20:32:33'),(5,NULL,'Books','books',NULL,NULL,5,1,'2026-04-02 20:32:33','2026-04-02 20:32:33'),(6,NULL,'Beauty','beauty',NULL,NULL,6,1,'2026-04-02 20:32:33','2026-04-02 20:32:33'),(7,NULL,'Grocery','grocery',NULL,NULL,7,1,'2026-04-02 20:32:33','2026-04-02 20:32:33'),(8,NULL,'Gaming','gaming',NULL,NULL,8,1,'2026-04-02 20:32:33','2026-04-02 20:32:33');
/*!40000 ALTER TABLE `mc_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mc_commissions`
--

DROP TABLE IF EXISTS `mc_commissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mc_commissions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int(10) unsigned NOT NULL,
  `order_item_id` int(10) unsigned NOT NULL,
  `vendor_id` int(10) unsigned NOT NULL,
  `product_id` int(10) unsigned NOT NULL,
  `plan_id` int(10) unsigned DEFAULT NULL,
  `plan_name` varchar(100) NOT NULL,
  `item_amount` decimal(12,2) NOT NULL,
  `turnover_before` decimal(14,2) NOT NULL DEFAULT 0.00,
  `commission_pct` decimal(5,2) NOT NULL,
  `commission_amount` decimal(12,2) NOT NULL,
  `vendor_earning` decimal(12,2) NOT NULL,
  `calculated_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_vendor` (`vendor_id`),
  KEY `idx_order` (`order_id`),
  KEY `idx_calc` (`calculated_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mc_commissions`
--

LOCK TABLES `mc_commissions` WRITE;
/*!40000 ALTER TABLE `mc_commissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `mc_commissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mc_coupon_usages`
--

DROP TABLE IF EXISTS `mc_coupon_usages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mc_coupon_usages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `coupon_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `order_id` int(10) unsigned NOT NULL,
  `discount_amount` decimal(10,2) NOT NULL,
  `used_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_cu_coupon` (`coupon_id`),
  KEY `idx_user_coupon` (`user_id`,`coupon_id`),
  KEY `idx_order` (`order_id`),
  CONSTRAINT `fk_cu_coupon` FOREIGN KEY (`coupon_id`) REFERENCES `mc_coupons` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mc_coupon_usages`
--

LOCK TABLES `mc_coupon_usages` WRITE;
/*!40000 ALTER TABLE `mc_coupon_usages` DISABLE KEYS */;
/*!40000 ALTER TABLE `mc_coupon_usages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mc_coupons`
--

DROP TABLE IF EXISTS `mc_coupons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mc_coupons` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL,
  `label` varchar(150) DEFAULT NULL,
  `value_type` enum('fixed','percentage') NOT NULL DEFAULT 'fixed',
  `value` decimal(10,2) NOT NULL,
  `max_discount_amount` decimal(10,2) DEFAULT NULL COMMENT 'cap for percentage coupons',
  `min_order_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `scope` enum('platform','vendor','category','brand','product','user','first_order','festival') NOT NULL DEFAULT 'platform',
  `scope_id` int(10) unsigned DEFAULT NULL,
  `usage_limit_total` int(10) unsigned DEFAULT NULL COMMENT 'NULL = unlimited',
  `usage_limit_per_user` int(10) unsigned NOT NULL DEFAULT 1,
  `used_count` int(10) unsigned NOT NULL DEFAULT 0,
  `starts_at` datetime DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` int(10) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `idx_scope` (`scope`,`scope_id`),
  KEY `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mc_coupons`
--

LOCK TABLES `mc_coupons` WRITE;
/*!40000 ALTER TABLE `mc_coupons` DISABLE KEYS */;
/*!40000 ALTER TABLE `mc_coupons` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mc_customer_wallet_transactions`
--

DROP TABLE IF EXISTS `mc_customer_wallet_transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mc_customer_wallet_transactions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `type` enum('credit','debit') NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `balance_after` decimal(12,2) NOT NULL,
  `reference_type` enum('topup','order_payment','refund','gift_card_redeem','admin_adjustment') NOT NULL,
  `reference_id` int(10) unsigned DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mc_customer_wallet_transactions`
--

LOCK TABLES `mc_customer_wallet_transactions` WRITE;
/*!40000 ALTER TABLE `mc_customer_wallet_transactions` DISABLE KEYS */;
/*!40000 ALTER TABLE `mc_customer_wallet_transactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mc_customer_wallets`
--

DROP TABLE IF EXISTS `mc_customer_wallets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mc_customer_wallets` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `balance` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_added` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_used` decimal(12,2) NOT NULL DEFAULT 0.00,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`),
  CONSTRAINT `fk_cw_user` FOREIGN KEY (`user_id`) REFERENCES `mc_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mc_customer_wallets`
--

LOCK TABLES `mc_customer_wallets` WRITE;
/*!40000 ALTER TABLE `mc_customer_wallets` DISABLE KEYS */;
/*!40000 ALTER TABLE `mc_customer_wallets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mc_disputes`
--

DROP TABLE IF EXISTS `mc_disputes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mc_disputes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int(10) unsigned NOT NULL,
  `vendor_id` int(10) unsigned NOT NULL,
  `raised_by` enum('customer','vendor','admin') NOT NULL,
  `raised_by_id` int(10) unsigned NOT NULL,
  `reason` varchar(255) NOT NULL,
  `status` enum('open','resolved') NOT NULL DEFAULT 'open',
  `resolution_note` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `resolved_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_disp_order` (`order_id`),
  KEY `idx_vendor_status` (`vendor_id`,`status`),
  CONSTRAINT `fk_disp_order` FOREIGN KEY (`order_id`) REFERENCES `mc_orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mc_disputes`
--

LOCK TABLES `mc_disputes` WRITE;
/*!40000 ALTER TABLE `mc_disputes` DISABLE KEYS */;
/*!40000 ALTER TABLE `mc_disputes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mc_gift_card_transactions`
--

DROP TABLE IF EXISTS `mc_gift_card_transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mc_gift_card_transactions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `gift_card_id` int(10) unsigned NOT NULL,
  `type` enum('issue','redeem','refund') NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `order_id` int(10) unsigned DEFAULT NULL,
  `balance_after` decimal(10,2) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_gct_card` (`gift_card_id`),
  CONSTRAINT `fk_gct_card` FOREIGN KEY (`gift_card_id`) REFERENCES `mc_gift_cards` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mc_gift_card_transactions`
--

LOCK TABLES `mc_gift_card_transactions` WRITE;
/*!40000 ALTER TABLE `mc_gift_card_transactions` DISABLE KEYS */;
/*!40000 ALTER TABLE `mc_gift_card_transactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mc_gift_cards`
--

DROP TABLE IF EXISTS `mc_gift_cards`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mc_gift_cards` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(30) NOT NULL,
  `type` enum('company','vendor','recharge') NOT NULL DEFAULT 'company',
  `vendor_id` int(10) unsigned DEFAULT NULL COMMENT 'set only for type=vendor',
  `initial_balance` decimal(10,2) NOT NULL,
  `current_balance` decimal(10,2) NOT NULL,
  `issued_to_email` varchar(150) DEFAULT NULL,
  `issued_to_user_id` int(10) unsigned DEFAULT NULL,
  `purchased_by` int(10) unsigned DEFAULT NULL,
  `status` enum('active','redeemed','expired','inactive') NOT NULL DEFAULT 'active',
  `expires_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `idx_status` (`status`),
  KEY `idx_issued_user` (`issued_to_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mc_gift_cards`
--

LOCK TABLES `mc_gift_cards` WRITE;
/*!40000 ALTER TABLE `mc_gift_cards` DISABLE KEYS */;
/*!40000 ALTER TABLE `mc_gift_cards` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mc_invoice_items`
--

DROP TABLE IF EXISTS `mc_invoice_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mc_invoice_items` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `invoice_id` int(10) unsigned NOT NULL,
  `order_item_id` int(10) unsigned NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `hsn_code` varchar(20) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(12,2) NOT NULL,
  `taxable_amount` decimal(12,2) NOT NULL,
  `gst_rate` decimal(5,2) NOT NULL,
  `cgst_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `sgst_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `igst_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `line_total` decimal(12,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_ii_invoice` (`invoice_id`),
  CONSTRAINT `fk_ii_invoice` FOREIGN KEY (`invoice_id`) REFERENCES `mc_invoices` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mc_invoice_items`
--

LOCK TABLES `mc_invoice_items` WRITE;
/*!40000 ALTER TABLE `mc_invoice_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `mc_invoice_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mc_invoices`
--

DROP TABLE IF EXISTS `mc_invoices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mc_invoices` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `invoice_number` varchar(50) NOT NULL,
  `order_id` int(10) unsigned NOT NULL,
  `order_vendor_split_id` int(10) unsigned NOT NULL,
  `vendor_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `invoice_date` date NOT NULL,
  `place_of_supply` varchar(100) NOT NULL,
  `is_interstate` tinyint(1) NOT NULL DEFAULT 0,
  `taxable_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `cgst_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `sgst_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `igst_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_tax` decimal(12,2) NOT NULL DEFAULT 0.00,
  `grand_total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `pdf_path` varchar(255) DEFAULT NULL,
  `emailed_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `invoice_number` (`invoice_number`),
  UNIQUE KEY `uq_split_invoice` (`order_vendor_split_id`),
  KEY `fk_inv_order` (`order_id`),
  KEY `idx_vendor` (`vendor_id`),
  KEY `idx_user` (`user_id`),
  CONSTRAINT `fk_inv_order` FOREIGN KEY (`order_id`) REFERENCES `mc_orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mc_invoices`
--

LOCK TABLES `mc_invoices` WRITE;
/*!40000 ALTER TABLE `mc_invoices` DISABLE KEYS */;
/*!40000 ALTER TABLE `mc_invoices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mc_loyalty_points`
--

DROP TABLE IF EXISTS `mc_loyalty_points`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mc_loyalty_points` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `balance` int(10) unsigned NOT NULL DEFAULT 0,
  `total_earned` int(10) unsigned NOT NULL DEFAULT 0,
  `total_redeemed` int(10) unsigned NOT NULL DEFAULT 0,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`),
  CONSTRAINT `fk_lp_user` FOREIGN KEY (`user_id`) REFERENCES `mc_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mc_loyalty_points`
--

LOCK TABLES `mc_loyalty_points` WRITE;
/*!40000 ALTER TABLE `mc_loyalty_points` DISABLE KEYS */;
/*!40000 ALTER TABLE `mc_loyalty_points` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mc_loyalty_transactions`
--

DROP TABLE IF EXISTS `mc_loyalty_transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mc_loyalty_transactions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `type` enum('earned','redeemed','expired','adjusted') NOT NULL,
  `points` int(11) NOT NULL COMMENT 'positive for earned/adjusted-up, negative for redeemed/expired',
  `order_id` int(10) unsigned DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mc_loyalty_transactions`
--

LOCK TABLES `mc_loyalty_transactions` WRITE;
/*!40000 ALTER TABLE `mc_loyalty_transactions` DISABLE KEYS */;
/*!40000 ALTER TABLE `mc_loyalty_transactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mc_notifications`
--

DROP TABLE IF EXISTS `mc_notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mc_notifications` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_type` enum('customer','vendor','admin') NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `type` varchar(50) NOT NULL COMMENT 'order_placed|order_status|shipment|return|withdrawal|low_stock|dispute|generic',
  `title` varchar(150) NOT NULL,
  `message` varchar(255) NOT NULL,
  `link` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_type`,`user_id`,`is_read`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mc_notifications`
--

LOCK TABLES `mc_notifications` WRITE;
/*!40000 ALTER TABLE `mc_notifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `mc_notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mc_order_items`
--

DROP TABLE IF EXISTS `mc_order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mc_order_items` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int(10) unsigned NOT NULL,
  `product_id` int(10) unsigned NOT NULL,
  `vendor_id` int(10) unsigned NOT NULL,
  `variant_id` int(10) unsigned DEFAULT NULL,
  `product_name` varchar(255) NOT NULL,
  `variant_label` varchar(100) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(12,2) NOT NULL,
  `subtotal` decimal(14,2) NOT NULL,
  `status` enum('placed','confirmed','shipped','delivered','cancelled','returned') NOT NULL DEFAULT 'placed',
  PRIMARY KEY (`id`),
  KEY `idx_order` (`order_id`),
  KEY `idx_vendor` (`vendor_id`),
  CONSTRAINT `fk_oi_order` FOREIGN KEY (`order_id`) REFERENCES `mc_orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mc_order_items`
--

LOCK TABLES `mc_order_items` WRITE;
/*!40000 ALTER TABLE `mc_order_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `mc_order_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mc_order_status_timeline`
--

DROP TABLE IF EXISTS `mc_order_status_timeline`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mc_order_status_timeline` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int(10) unsigned NOT NULL,
  `status` varchar(50) NOT NULL,
  `note` text DEFAULT NULL,
  `changed_by_type` enum('admin','vendor','system') NOT NULL DEFAULT 'system',
  `changed_by_id` int(10) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_order` (`order_id`),
  CONSTRAINT `fk_ost_order` FOREIGN KEY (`order_id`) REFERENCES `mc_orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mc_order_status_timeline`
--

LOCK TABLES `mc_order_status_timeline` WRITE;
/*!40000 ALTER TABLE `mc_order_status_timeline` DISABLE KEYS */;
/*!40000 ALTER TABLE `mc_order_status_timeline` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mc_order_vendor_splits`
--

DROP TABLE IF EXISTS `mc_order_vendor_splits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mc_order_vendor_splits` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int(10) unsigned NOT NULL,
  `vendor_id` int(10) unsigned NOT NULL,
  `gross_amount` decimal(14,2) NOT NULL,
  `commission_pct` decimal(5,2) NOT NULL DEFAULT 0.00,
  `commission_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `vendor_earning` decimal(12,2) NOT NULL,
  `payout_status` enum('pending','processing','paid','on_hold') NOT NULL DEFAULT 'pending',
  `payout_date` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_order_vendor` (`order_id`,`vendor_id`),
  KEY `idx_vendor` (`vendor_id`),
  CONSTRAINT `fk_ovs_order` FOREIGN KEY (`order_id`) REFERENCES `mc_orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mc_order_vendor_splits`
--

LOCK TABLES `mc_order_vendor_splits` WRITE;
/*!40000 ALTER TABLE `mc_order_vendor_splits` DISABLE KEYS */;
/*!40000 ALTER TABLE `mc_order_vendor_splits` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mc_orders`
--

DROP TABLE IF EXISTS `mc_orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mc_orders` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order_number` varchar(50) NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `shipping_name` varchar(150) NOT NULL,
  `shipping_phone` varchar(20) NOT NULL,
  `shipping_address` text NOT NULL,
  `shipping_city` varchar(100) NOT NULL,
  `shipping_state` varchar(100) NOT NULL,
  `shipping_pincode` varchar(20) NOT NULL,
  `payment_method` enum('cod','online','wallet') NOT NULL DEFAULT 'cod',
  `payment_status` enum('pending','paid','failed','refunded') NOT NULL DEFAULT 'pending',
  `order_status` enum('placed','confirmed','processing','shipped','delivered','cancelled','returned') NOT NULL DEFAULT 'placed',
  `subtotal` decimal(14,2) NOT NULL DEFAULT 0.00,
  `shipping_charge` decimal(10,2) NOT NULL DEFAULT 0.00,
  `discount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `coupon_id` int(10) unsigned DEFAULT NULL,
  `coupon_code` varchar(50) DEFAULT NULL,
  `gift_card_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `wallet_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `loyalty_points_used` int(10) unsigned NOT NULL DEFAULT 0,
  `loyalty_points_earned` int(10) unsigned NOT NULL DEFAULT 0,
  `loyalty_credited` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'guards against double-crediting if delivered status is set more than once',
  `total` decimal(14,2) NOT NULL DEFAULT 0.00,
  `notes` text DEFAULT NULL,
  `placed_at` datetime NOT NULL DEFAULT current_timestamp(),
  `delivered_at` datetime DEFAULT NULL,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_number` (`order_number`),
  KEY `idx_user` (`user_id`),
  KEY `idx_status` (`order_status`),
  KEY `idx_placed` (`placed_at`),
  CONSTRAINT `fk_ord_user` FOREIGN KEY (`user_id`) REFERENCES `mc_users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mc_orders`
--

LOCK TABLES `mc_orders` WRITE;
/*!40000 ALTER TABLE `mc_orders` DISABLE KEYS */;
/*!40000 ALTER TABLE `mc_orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mc_otps`
--

DROP TABLE IF EXISTS `mc_otps`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mc_otps` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `identifier` varchar(150) NOT NULL COMMENT 'email or phone the OTP was sent to',
  `purpose` varchar(50) NOT NULL COMMENT 'vendor_email_verify|login|password_reset|...',
  `otp_code` varchar(10) NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `is_used` tinyint(1) NOT NULL DEFAULT 0,
  `expires_at` datetime NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_lookup` (`identifier`,`purpose`,`is_used`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mc_otps`
--

LOCK TABLES `mc_otps` WRITE;
/*!40000 ALTER TABLE `mc_otps` DISABLE KEYS */;
/*!40000 ALTER TABLE `mc_otps` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mc_pages`
--

DROP TABLE IF EXISTS `mc_pages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mc_pages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `slug` varchar(100) NOT NULL,
  `icon` varchar(16) NOT NULL DEFAULT 'Ôä╣´©Å',
  `title` varchar(150) NOT NULL,
  `short_description` varchar(255) DEFAULT NULL,
  `content` mediumtext DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_pages_slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mc_pages`
--

LOCK TABLES `mc_pages` WRITE;
/*!40000 ALTER TABLE `mc_pages` DISABLE KEYS */;
INSERT INTO `mc_pages` VALUES (1,'about-us','🏬','About Us','Who we are and what we stand for','<p><strong>{{site_name}}</strong> (\"Namma\" means <em>ours</em>) is a multi-vendor online marketplace built with one simple idea — bring your neighbourhood stores online and deliver them to your doorstep. We connect trusted local sellers with shoppers across India, so every purchase you make supports a real business in your own community.</p>\r\n\r\n<h2>What we offer</h2>\r\n<ul>\r\n  <li>Thousands of products across Electronics, Fashion, Home &amp; Kitchen, Sports, Books, Beauty, Grocery and Gaming.</li>\r\n  <li>Verified vendors — every seller is reviewed and approved before their shop goes live.</li>\r\n  <li>Secure payments, GST invoices, easy returns and doorstep delivery.</li>\r\n  <li>Loyalty points on every order, redeemable at checkout.</li>\r\n</ul>\r\n\r\n<h2>Our mission</h2>\r\n<p>To make quality products from local sellers accessible to everyone, with the convenience of modern e-commerce and the trust of a neighbourhood shop.</p>\r\n\r\n<h2>Why shop with us?</h2>\r\n<ul>\r\n  <li><strong>Trust:</strong> transparent seller ratings and genuine customer reviews.</li>\r\n  <li><strong>Value:</strong> competitive prices, regular offers and free delivery above ₹499.</li>\r\n  <li><strong>Support:</strong> a responsive support team and a 7-day hassle-free return window.</li>\r\n</ul>\r\n\r\n<h2>Sell with us</h2>\r\n<p>Are you a seller? Join our growing vendor family — register on our <a href=\"{{vendor_url}}/register\">Seller Panel</a> and start selling in minutes.</p>',1,10,'2026-07-18 01:32:33','2026-07-18 01:32:33'),(2,'contact-us','📞','Contact Us','Reach our support team anytime','<p>Have a question about an order, a product, or selling with us? We\'d love to hear from you.</p>',1,20,'2026-07-18 01:32:33','2026-07-18 01:32:33'),(3,'faq','❓','FAQ','Answers to common questions','<p>Quick answers to the questions we hear most often.</p>\r\n\r\n<h2>Orders &amp; delivery</h2>\r\n<details class=\"faq-item\"><summary>How do I track my order?</summary><div class=\"faq-a\">Use the <a href=\"{{app_url}}/track\">Track Order</a> page with your order number, or open the order inside <a href=\"{{app_url}}/account/orders\">My Account → My Orders</a>.</div></details>\r\n<details class=\"faq-item\"><summary>Why did my order arrive in separate parcels?</summary><div class=\"faq-a\">We\'re a multi-vendor marketplace — items from different sellers ship separately. You\'re never charged extra for split deliveries.</div></details>\r\n<details class=\"faq-item\"><summary>Is Cash on Delivery available?</summary><div class=\"faq-a\">Yes, COD is available on most products. You\'ll see the option at checkout if every item in your cart supports it.</div></details>\r\n\r\n<h2>Returns &amp; refunds</h2>\r\n<details class=\"faq-item\"><summary>How do I return a product?</summary><div class=\"faq-a\">Open the delivered order in <a href=\"{{app_url}}/account/orders\">My Orders</a> and click <strong>Request Return</strong> within 7 days of delivery. Full details in our <a href=\"{{app_url}}/info/return-policy\">Return Policy</a>.</div></details>\r\n<details class=\"faq-item\"><summary>When will I get my refund?</summary><div class=\"faq-a\">Wallet refunds are instant on approval; bank/card refunds take 3–7 business days. See <a href=\"{{app_url}}/info/cancellation-refund\">Cancellation &amp; Refund</a>.</div></details>\r\n\r\n<h2>Account &amp; payments</h2>\r\n<details class=\"faq-item\"><summary>How do loyalty points work?</summary><div class=\"faq-a\">You earn points automatically when an order is delivered (1 point per ₹100 by default) and can redeem them for a discount at checkout once you cross the minimum balance.</div></details>\r\n<details class=\"faq-item\"><summary>Is it safe to save my payment details?</summary><div class=\"faq-a\">We never store card numbers or CVVs — payments are processed by secure, bank-verified gateways with OTP confirmation.</div></details>\r\n<details class=\"faq-item\"><summary>I forgot my password. What do I do?</summary><div class=\"faq-a\">Use the <a href=\"{{app_url}}/forgot-password\">Forgot Password</a> link on the login page to reset it via email.</div></details>\r\n\r\n<h2>Selling on the marketplace</h2>\r\n<details class=\"faq-item\"><summary>How do I become a seller?</summary><div class=\"faq-a\">Register on the <a href=\"{{vendor_url}}/register\">Vendor Panel</a>, verify your email with the OTP, complete your shop profile and start listing products.</div></details>\r\n<details class=\"faq-item\"><summary>What commission does the marketplace charge?</summary><div class=\"faq-a\">The default commission is set by the marketplace admin and shown in your vendor dashboard before you list. Settlements are credited to your vendor wallet after the return window closes.</div></details>',1,30,'2026-07-18 01:32:33','2026-07-18 01:32:33'),(4,'shipping-policy','🚚','Shipping Policy','Delivery timelines and charges','<p>We deliver across India through trusted courier partners. Here\'s everything about how your order reaches you.</p>\r\n\r\n<h2>Delivery charges</h2>\r\n<table>\r\n  <tr><th>Order value</th><th>Delivery charge</th></tr>\r\n  <tr><td>₹499 and above</td><td><strong>FREE</strong></td></tr>\r\n  <tr><td>Below ₹499</td><td>₹49 flat</td></tr>\r\n</table>\r\n\r\n<h2>Delivery timelines</h2>\r\n<ul>\r\n  <li><strong>Metro cities:</strong> 2–4 business days.</li>\r\n  <li><strong>Other cities &amp; towns:</strong> 4–7 business days.</li>\r\n  <li><strong>Remote areas:</strong> 7–10 business days.</li>\r\n</ul>\r\n<p>Since items may ship from different vendors, products in one order can arrive in separate parcels on different days — you are never charged extra for split shipments.</p>\r\n\r\n<h2>Order tracking</h2>\r\n<p>Once shipped, you\'ll receive the courier name and tracking number by notification and email. Track any order anytime on our <a href=\"{{app_url}}/track\">Track Order</a> page.</p>\r\n\r\n<h2>Delivery attempts</h2>\r\n<p>Couriers make up to 3 delivery attempts. If all fail, the item returns to the vendor and the order is refunded (minus shipping for COD refusals).</p>\r\n\r\n<h2>Cash on Delivery (COD)</h2>\r\n<p>COD is available on most items. Please keep the exact amount ready — delivery executives may not carry change.</p>',1,40,'2026-07-18 01:32:33','2026-07-18 01:32:33'),(5,'return-policy','🔄','Return Policy','How returns and replacements work','<p>We want you to love what you ordered. If something isn\'t right, our return process is simple and hassle-free.</p>\r\n\r\n<h2>Return window</h2>\r\n<p>Most products can be returned within <strong>7 days of delivery</strong>. The item must be unused, in its original packaging, with all tags, accessories and the invoice.</p>\r\n\r\n<h2>How to request a return</h2>\r\n<ol>\r\n  <li>Go to <a href=\"{{app_url}}/account/orders\">My Account → My Orders</a> and open the delivered order.</li>\r\n  <li>Click <strong>Request Return</strong>, choose <em>Return</em> or <em>Replacement</em>, and tell us the reason.</li>\r\n  <li>Our team (or the vendor) reviews the request — you\'ll get a notification once it\'s approved.</li>\r\n  <li>Hand the item to the pickup executive or ship it back as instructed.</li>\r\n</ol>\r\n\r\n<h2>Non-returnable items</h2>\r\n<ul>\r\n  <li>Perishable goods (fresh grocery, food items).</li>\r\n  <li>Personal care and beauty products that have been opened or used.</li>\r\n  <li>Innerwear, socks and similar hygiene-sensitive apparel.</li>\r\n  <li>Digital goods, gift cards and vouchers.</li>\r\n</ul>\r\n\r\n<h2>Refunds after return</h2>\r\n<p>Once the returned item passes a quality check, your refund is issued to your <strong>original payment method or store wallet</strong> within 5–7 business days. Wallet refunds are instant on approval. See our <a href=\"{{app_url}}/info/cancellation-refund\">Cancellation &amp; Refund policy</a> for details.</p>\r\n\r\n<h2>Damaged or wrong item received?</h2>\r\n<p>Raise a return request within 48 hours of delivery with photos of the item and packaging — these cases get priority replacement at no cost to you.</p>',1,50,'2026-07-18 01:32:33','2026-07-18 01:32:33'),(6,'cancellation-refund','💰','Cancellation & Refund','Cancelling orders and getting refunds','<h2>Cancelling an order</h2>\r\n<ul>\r\n  <li>Orders can be cancelled free of charge any time <strong>before they are shipped</strong>.</li>\r\n  <li>Go to <a href=\"{{app_url}}/account/orders\">My Account → My Orders</a>, open the order and choose <strong>Cancel</strong>.</li>\r\n  <li>Once an item is shipped, it can no longer be cancelled — but you can refuse the delivery or request a return after it arrives.</li>\r\n</ul>\r\n\r\n<h2>Refund timelines</h2>\r\n<table>\r\n  <tr><th>Payment method</th><th>Refund time after approval</th></tr>\r\n  <tr><td>Store Wallet</td><td>Instant</td></tr>\r\n  <tr><td>UPI / Net-banking</td><td>3–5 business days</td></tr>\r\n  <tr><td>Credit / Debit card</td><td>5–7 business days</td></tr>\r\n  <tr><td>Cash on Delivery</td><td>Refunded to your store wallet, or bank transfer on request (5–7 days)</td></tr>\r\n</table>\r\n\r\n<h2>What gets refunded</h2>\r\n<ul>\r\n  <li><strong>Cancellation before shipping:</strong> 100% of the amount paid, including shipping.</li>\r\n  <li><strong>Approved return:</strong> full item price; original shipping is refunded only if the item was defective, damaged or wrong.</li>\r\n  <li><strong>Coupons / points used:</strong> coupon value is not re-issued in cash; loyalty points used are credited back to your account.</li>\r\n</ul>\r\n\r\n<h2>Refund not received?</h2>\r\n<p>If a refund hasn\'t arrived after the timeline above, first check with your bank, then reach us via <a href=\"{{app_url}}/info/contact-us\">Contact Us</a> with your order number — we\'ll trace it within 48 hours.</p>',1,60,'2026-07-18 01:32:33','2026-07-18 01:32:33'),(7,'payment-methods','💳','Payment Methods','Ways you can pay securely','<p>Pay the way you like — every transaction is encrypted and processed through secure, PCI-compliant payment gateways.</p>\r\n\r\n<h2>Available payment options</h2>\r\n<ul>\r\n  <li><strong>💵 Cash on Delivery (COD):</strong> pay in cash when your order arrives. Available on most products.</li>\r\n  <li><strong>📲 UPI:</strong> Google Pay, PhonePe, Paytm and any UPI app.</li>\r\n  <li><strong>💳 Cards:</strong> all major credit and debit cards (Visa, Mastercard, RuPay).</li>\r\n  <li><strong>🏦 Net Banking:</strong> all major Indian banks.</li>\r\n  <li><strong>👛 Store Wallet:</strong> refunds and cashback land here — use the balance at checkout.</li>\r\n  <li><strong>🎁 Gift Cards:</strong> redeem gift voucher codes at checkout.</li>\r\n  <li><strong>⭐ Loyalty Points:</strong> earn points on every delivered order and redeem them for instant discounts.</li>\r\n</ul>\r\n\r\n<h2>EMI</h2>\r\n<p>EMI options are available on card payments for orders above ₹3,000, subject to your bank\'s terms.</p>\r\n\r\n<h2>Payment security</h2>\r\n<ul>\r\n  <li>We never store your full card number or CVV.</li>\r\n  <li>All payment pages use 256-bit SSL encryption.</li>\r\n  <li>Online payments are protected by your bank\'s OTP / 3-D Secure verification.</li>\r\n</ul>\r\n\r\n<h2>Payment failed but money deducted?</h2>\r\n<p>Don\'t worry — failed transaction amounts are auto-reversed by the bank within 5–7 business days. If not, contact us with the transaction reference via <a href=\"{{app_url}}/info/contact-us\">Contact Us</a>.</p>',1,70,'2026-07-18 01:32:33','2026-07-18 01:32:33'),(8,'privacy-policy','🔒','Privacy Policy','How we handle your data','<p>At {{site_name}}, your privacy matters. This policy explains what information we collect, why we collect it, and how we protect it.</p>\r\n\r\n<h2>1. Information we collect</h2>\r\n<ul>\r\n  <li><strong>Account details:</strong> name, email address and phone number when you register.</li>\r\n  <li><strong>Order details:</strong> shipping address, order history and payment method (we never store full card numbers).</li>\r\n  <li><strong>Usage data:</strong> pages visited, products viewed and searches, used to improve your experience.</li>\r\n</ul>\r\n\r\n<h2>2. How we use your information</h2>\r\n<ul>\r\n  <li>To process orders, deliveries, returns and refunds.</li>\r\n  <li>To send order updates, invoices and important account notifications.</li>\r\n  <li>To personalise product recommendations and offers.</li>\r\n  <li>To prevent fraud and keep the platform secure.</li>\r\n</ul>\r\n\r\n<h2>3. Sharing with vendors and partners</h2>\r\n<p>When you place an order, the selling vendor receives only the details needed to fulfil it (name, address, phone). Delivery partners receive your shipping details. We never sell your personal data to third parties.</p>\r\n\r\n<h2>4. Cookies</h2>\r\n<p>We use cookies to keep you signed in, remember your cart and understand how the site is used. You can disable cookies in your browser, but some features (like the cart) may stop working.</p>\r\n\r\n<h2>5. Data security &amp; retention</h2>\r\n<p>Your data is stored securely and passwords are encrypted. We retain order records as required by tax law (GST) and delete or anonymise data that is no longer needed.</p>\r\n\r\n<h2>6. Your rights</h2>\r\n<p>You may access, correct or request deletion of your personal data at any time from your <a href=\"{{app_url}}/account/profile\">account profile</a> or by writing to us via the <a href=\"{{app_url}}/info/contact-us\">Contact Us</a> page.</p>\r\n\r\n<p><em>Last updated: July 2026.</em></p>',1,80,'2026-07-18 01:32:33','2026-07-18 01:32:33'),(9,'terms-conditions','📜','Terms & Conditions','Rules for using our marketplace','<p>Welcome to {{site_name}}. By accessing or purchasing on this website, you agree to the terms below. Please read them carefully.</p>\r\n\r\n<h2>1. About the marketplace</h2>\r\n<p>{{site_name}} is a multi-vendor marketplace. Products are listed and sold by independent vendors; {{site_name}} facilitates the transaction, payment and dispute resolution between you and the vendor.</p>\r\n\r\n<h2>2. Your account</h2>\r\n<ul>\r\n  <li>You must provide accurate information when registering and keep your credentials confidential.</li>\r\n  <li>You are responsible for all activity under your account.</li>\r\n  <li>Accounts engaging in fraud, abuse or misuse of offers may be suspended.</li>\r\n</ul>\r\n\r\n<h2>3. Orders, pricing &amp; availability</h2>\r\n<ul>\r\n  <li>All prices are in Indian Rupees (₹) and include GST unless stated otherwise.</li>\r\n  <li>An order is confirmed only after successful payment (or COD confirmation).</li>\r\n  <li>In rare cases of stock errors or obvious pricing mistakes, we may cancel the order with a full refund.</li>\r\n</ul>\r\n\r\n<h2>4. Delivery</h2>\r\n<p>Delivery timelines shown at checkout are estimates. Risk in the goods passes to you on delivery. See our <a href=\"{{app_url}}/info/shipping-policy\">Shipping Policy</a>.</p>\r\n\r\n<h2>5. Returns &amp; refunds</h2>\r\n<p>Returns are governed by our <a href=\"{{app_url}}/info/return-policy\">Return Policy</a> and <a href=\"{{app_url}}/info/cancellation-refund\">Cancellation &amp; Refund Policy</a>.</p>\r\n\r\n<h2>6. Reviews &amp; content</h2>\r\n<p>Reviews must be genuine and respectful. We may remove content that is abusive, misleading, or violates any law. By posting a review you grant us the right to display it on the platform.</p>\r\n\r\n<h2>7. Intellectual property</h2>\r\n<p>All site content — logo, design, text and images (except vendor product images) — belongs to {{site_name}} and may not be copied without permission.</p>\r\n\r\n<h2>8. Limitation of liability</h2>\r\n<p>To the maximum extent permitted by law, our liability for any claim related to an order is limited to the amount you paid for that order.</p>\r\n\r\n<h2>9. Governing law</h2>\r\n<p>These terms are governed by the laws of India. Disputes are subject to the exclusive jurisdiction of the courts at the company\'s registered office location.</p>\r\n\r\n<p><em>Last updated: July 2026.</em></p>',1,90,'2026-07-18 01:32:33','2026-07-18 01:32:33'),(10,'disclaimer','⚠️','Disclaimer','Important legal information','<h2>Marketplace disclaimer</h2>\r\n<p>{{site_name}} is an intermediary platform. Product listings — including descriptions, images, pricing and stock — are provided by independent vendors. While we verify sellers and monitor listings, we do not manufacture the products and cannot guarantee that every description is complete or error-free.</p>\r\n\r\n<h2>Product information</h2>\r\n<ul>\r\n  <li>Colours may vary slightly from the images due to screen settings and photography.</li>\r\n  <li>Packaging may differ from what is shown if the manufacturer updates it.</li>\r\n  <li>For food, cosmetics and healthcare items, always read the label, ingredients and directions on the actual product before use.</li>\r\n</ul>\r\n\r\n<h2>Pricing &amp; offers</h2>\r\n<p>Discounts, coupons and flash-sale prices are time-bound and may change or be withdrawn without notice. In case of an obvious pricing error, the order may be cancelled with a full refund.</p>\r\n\r\n<h2>External links</h2>\r\n<p>Pages may contain links to third-party sites (courier tracking, payment gateways). We are not responsible for the content or privacy practices of those sites.</p>\r\n\r\n<h2>No professional advice</h2>\r\n<p>Nothing on this site constitutes medical, legal or financial advice. Consult a qualified professional before acting on any product claim.</p>\r\n\r\n<p>If you find an incorrect listing or have a concern about a product, please report it via our <a href=\"{{app_url}}/info/contact-us\">Contact Us</a> page.</p>',1,100,'2026-07-18 01:32:33','2026-07-18 01:32:33');
/*!40000 ALTER TABLE `mc_pages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mc_password_resets`
--

DROP TABLE IF EXISTS `mc_password_resets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mc_password_resets` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(150) NOT NULL,
  `token` varchar(255) NOT NULL,
  `user_type` enum('admin','user') NOT NULL DEFAULT 'user',
  `expires_at` datetime NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_email_token` (`email`,`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mc_password_resets`
--

LOCK TABLES `mc_password_resets` WRITE;
/*!40000 ALTER TABLE `mc_password_resets` DISABLE KEYS */;
/*!40000 ALTER TABLE `mc_password_resets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mc_payments`
--

DROP TABLE IF EXISTS `mc_payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mc_payments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `transaction_id` varchar(150) DEFAULT NULL,
  `gateway` varchar(50) DEFAULT NULL,
  `amount` decimal(14,2) NOT NULL,
  `currency` varchar(10) NOT NULL DEFAULT 'INR',
  `status` enum('pending','success','failed','refunded') NOT NULL DEFAULT 'pending',
  `gateway_response` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`gateway_response`)),
  `paid_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `transaction_id` (`transaction_id`),
  KEY `idx_order` (`order_id`),
  KEY `idx_status` (`status`),
  CONSTRAINT `fk_pay_order` FOREIGN KEY (`order_id`) REFERENCES `mc_orders` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mc_payments`
--

LOCK TABLES `mc_payments` WRITE;
/*!40000 ALTER TABLE `mc_payments` DISABLE KEYS */;
/*!40000 ALTER TABLE `mc_payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mc_product_images`
--

DROP TABLE IF EXISTS `mc_product_images`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mc_product_images` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(10) unsigned NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `alt_text` varchar(255) DEFAULT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT 0,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `fk_pimg_prod` (`product_id`),
  CONSTRAINT `fk_pimg_prod` FOREIGN KEY (`product_id`) REFERENCES `mc_products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mc_product_images`
--

LOCK TABLES `mc_product_images` WRITE;
/*!40000 ALTER TABLE `mc_product_images` DISABLE KEYS */;
INSERT INTO `mc_product_images` VALUES (1,1,'products/wireless-bluetooth-headphones.jpg','Wireless Bluetooth Headphones',1,0),(2,2,'products/smart-fitness-band.jpg','Smart Fitness Band',1,0),(3,3,'products/men-s-casual-denim-jacket.jpg','Men\'s Casual Denim Jacket',1,0),(4,4,'products/women-s-floral-summer-dress.jpg','Women\'s Floral Summer Dress',1,0),(5,5,'products/stainless-steel-cookware-set-5-pcs.jpg','Stainless Steel Cookware Set (5 Pcs)',1,0),(6,6,'products/electric-kettle-1-8l.jpg','Electric Kettle 1.8L',1,0),(7,7,'products/yoga-mat-with-carry-strap.jpg','Yoga Mat with Carry Strap',1,0),(8,8,'products/adjustable-dumbbell-set-10kg.jpg','Adjustable Dumbbell Set 10kg',1,0),(9,9,'products/the-art-of-clean-code-paperback.jpg','The Art of Clean Code (Paperback)',1,0),(10,10,'products/herbal-face-wash-combo-pack.jpg','Herbal Face Wash Combo Pack',1,0),(11,11,'products/organic-assorted-dry-fruits-box-500g.jpg','Organic Assorted Dry Fruits Box 500g',1,0),(12,12,'products/wireless-gaming-mouse-rgb.jpg','Wireless Gaming Mouse RGB',1,0);
/*!40000 ALTER TABLE `mc_product_images` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mc_product_variants`
--

DROP TABLE IF EXISTS `mc_product_variants`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mc_product_variants` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(10) unsigned NOT NULL,
  `variant_name` varchar(100) NOT NULL,
  `variant_value` varchar(100) NOT NULL,
  `price_modifier` decimal(10,2) NOT NULL DEFAULT 0.00,
  `stock` int(11) NOT NULL DEFAULT 0,
  `sku` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `fk_pvar_prod` (`product_id`),
  CONSTRAINT `fk_pvar_prod` FOREIGN KEY (`product_id`) REFERENCES `mc_products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mc_product_variants`
--

LOCK TABLES `mc_product_variants` WRITE;
/*!40000 ALTER TABLE `mc_product_variants` DISABLE KEYS */;
/*!40000 ALTER TABLE `mc_product_variants` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mc_products`
--

DROP TABLE IF EXISTS `mc_products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mc_products` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `vendor_id` int(10) unsigned NOT NULL,
  `category_id` int(10) unsigned DEFAULT NULL,
  `brand_id` int(10) unsigned DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `sku` varchar(100) DEFAULT NULL,
  `barcode` varchar(50) DEFAULT NULL,
  `hsn_code` varchar(20) DEFAULT NULL,
  `gst_rate` decimal(5,2) NOT NULL DEFAULT 18.00,
  `description` text DEFAULT NULL,
  `short_description` text DEFAULT NULL,
  `price` decimal(12,2) NOT NULL,
  `sale_price` decimal(12,2) DEFAULT NULL,
  `cost_price` decimal(12,2) DEFAULT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `low_stock_threshold` int(11) NOT NULL DEFAULT 5,
  `weight` decimal(8,3) DEFAULT NULL,
  `status` enum('draft','active','inactive','rejected') NOT NULL DEFAULT 'draft',
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `rejection_note` text DEFAULT NULL,
  `approved_by` int(10) unsigned DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `views` int(10) unsigned NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  UNIQUE KEY `sku` (`sku`),
  KEY `fk_prod_cat` (`category_id`),
  KEY `idx_vendor` (`vendor_id`),
  KEY `idx_status` (`status`),
  KEY `fk_prod_brand` (`brand_id`),
  CONSTRAINT `fk_prod_brand` FOREIGN KEY (`brand_id`) REFERENCES `mc_brands` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_prod_cat` FOREIGN KEY (`category_id`) REFERENCES `mc_categories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_prod_vendor` FOREIGN KEY (`vendor_id`) REFERENCES `mc_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mc_products`
--

LOCK TABLES `mc_products` WRITE;
/*!40000 ALTER TABLE `mc_products` DISABLE KEYS */;
INSERT INTO `mc_products` VALUES (1,1,1,NULL,'Wireless Bluetooth Headphones','wireless-bluetooth-headphones','DEMO-WIRELE-589',NULL,NULL,18.00,'Wireless Bluetooth Headphones\n\nThis is a demo product added to showcase the Namma E Store marketplace catalog. Features premium quality, fast shipping, and easy returns. Category: Electronics.','Great quality Wireless Bluetooth Headphones, perfect for everyday use. Demo product for showcase purposes.',1999.00,1599.00,NULL,40,5,NULL,'active',1,NULL,NULL,'2026-07-18 01:54:09',0,'2026-07-18 01:54:09','2026-07-18 01:54:09'),(2,1,1,NULL,'Smart Fitness Band','smart-fitness-band','DEMO-SMARTF-582',NULL,NULL,18.00,'Smart Fitness Band\n\nThis is a demo product added to showcase the Namma E Store marketplace catalog. Features premium quality, fast shipping, and easy returns. Category: Electronics.','Great quality Smart Fitness Band, perfect for everyday use. Demo product for showcase purposes.',1499.00,NULL,NULL,60,5,NULL,'active',0,NULL,NULL,'2026-07-18 01:54:13',0,'2026-07-18 01:54:13','2026-07-18 01:54:13'),(3,1,2,NULL,'Men\'s Casual Denim Jacket','men-s-casual-denim-jacket','DEMO-MENSCA-853',NULL,NULL,18.00,'Men\'s Casual Denim Jacket\n\nThis is a demo product added to showcase the Namma E Store marketplace catalog. Features premium quality, fast shipping, and easy returns. Category: Fashion.','Great quality Men\'s Casual Denim Jacket, perfect for everyday use. Demo product for showcase purposes.',2199.00,1799.00,NULL,25,5,NULL,'active',1,NULL,NULL,'2026-07-18 01:54:16',1,'2026-07-18 01:54:16','2026-07-18 23:11:33'),(4,1,2,NULL,'Women\'s Floral Summer Dress','women-s-floral-summer-dress','DEMO-WOMENS-517',NULL,NULL,18.00,'Women\'s Floral Summer Dress\n\nThis is a demo product added to showcase the Namma E Store marketplace catalog. Features premium quality, fast shipping, and easy returns. Category: Fashion.','Great quality Women\'s Floral Summer Dress, perfect for everyday use. Demo product for showcase purposes.',1299.00,NULL,NULL,30,5,NULL,'active',0,NULL,NULL,'2026-07-18 01:54:20',0,'2026-07-18 01:54:20','2026-07-18 01:54:20'),(5,1,3,NULL,'Stainless Steel Cookware Set (5 Pcs)','stainless-steel-cookware-set-5-pcs','DEMO-STAINL-209',NULL,NULL,18.00,'Stainless Steel Cookware Set (5 Pcs)\n\nThis is a demo product added to showcase the Namma E Store marketplace catalog. Features premium quality, fast shipping, and easy returns. Category: Home Kitchen.','Great quality Stainless Steel Cookware Set (5 Pcs), perfect for everyday use. Demo product for showcase purposes.',3499.00,2999.00,NULL,15,5,NULL,'active',1,NULL,NULL,'2026-07-18 01:54:24',0,'2026-07-18 01:54:24','2026-07-18 01:54:24'),(6,1,3,NULL,'Electric Kettle 1.8L','electric-kettle-1-8l','DEMO-ELECTR-863',NULL,NULL,18.00,'Electric Kettle 1.8L\n\nThis is a demo product added to showcase the Namma E Store marketplace catalog. Features premium quality, fast shipping, and easy returns. Category: Home Kitchen.','Great quality Electric Kettle 1.8L, perfect for everyday use. Demo product for showcase purposes.',899.00,NULL,NULL,50,5,NULL,'active',0,NULL,NULL,'2026-07-18 01:54:28',0,'2026-07-18 01:54:28','2026-07-18 01:54:28'),(7,1,4,NULL,'Yoga Mat with Carry Strap','yoga-mat-with-carry-strap','DEMO-YOGAMA-151',NULL,NULL,18.00,'Yoga Mat with Carry Strap\n\nThis is a demo product added to showcase the Namma E Store marketplace catalog. Features premium quality, fast shipping, and easy returns. Category: Sports.','Great quality Yoga Mat with Carry Strap, perfect for everyday use. Demo product for showcase purposes.',699.00,549.00,NULL,80,5,NULL,'active',0,NULL,NULL,'2026-07-18 01:54:33',0,'2026-07-18 01:54:33','2026-07-18 01:54:33'),(8,1,4,NULL,'Adjustable Dumbbell Set 10kg','adjustable-dumbbell-set-10kg','DEMO-ADJUST-714',NULL,NULL,18.00,'Adjustable Dumbbell Set 10kg\n\nThis is a demo product added to showcase the Namma E Store marketplace catalog. Features premium quality, fast shipping, and easy returns. Category: Sports.','Great quality Adjustable Dumbbell Set 10kg, perfect for everyday use. Demo product for showcase purposes.',2499.00,NULL,NULL,20,5,NULL,'active',0,NULL,NULL,'2026-07-18 01:54:36',0,'2026-07-18 01:54:36','2026-07-18 01:54:36'),(9,1,5,NULL,'The Art of Clean Code (Paperback)','the-art-of-clean-code-paperback','DEMO-THEART-517',NULL,NULL,18.00,'The Art of Clean Code (Paperback)\n\nThis is a demo product added to showcase the Namma E Store marketplace catalog. Features premium quality, fast shipping, and easy returns. Category: Books.','Great quality The Art of Clean Code (Paperback), perfect for everyday use. Demo product for showcase purposes.',499.00,399.00,NULL,100,5,NULL,'active',0,NULL,NULL,'2026-07-18 01:54:42',0,'2026-07-18 01:54:42','2026-07-18 01:54:42'),(10,1,6,NULL,'Herbal Face Wash Combo Pack','herbal-face-wash-combo-pack','DEMO-HERBAL-344',NULL,NULL,18.00,'Herbal Face Wash Combo Pack\n\nThis is a demo product added to showcase the Namma E Store marketplace catalog. Features premium quality, fast shipping, and easy returns. Category: Beauty.','Great quality Herbal Face Wash Combo Pack, perfect for everyday use. Demo product for showcase purposes.',399.00,NULL,NULL,70,5,NULL,'active',0,NULL,NULL,'2026-07-18 01:54:46',0,'2026-07-18 01:54:46','2026-07-18 01:54:46'),(11,1,7,NULL,'Organic Assorted Dry Fruits Box 500g','organic-assorted-dry-fruits-box-500g','DEMO-ORGANI-671',NULL,NULL,18.00,'Organic Assorted Dry Fruits Box 500g\n\nThis is a demo product added to showcase the Namma E Store marketplace catalog. Features premium quality, fast shipping, and easy returns. Category: Grocery.','Great quality Organic Assorted Dry Fruits Box 500g, perfect for everyday use. Demo product for showcase purposes.',649.00,599.00,NULL,45,5,NULL,'active',1,NULL,NULL,'2026-07-18 01:54:51',0,'2026-07-18 01:54:51','2026-07-18 01:54:51'),(12,1,8,NULL,'Wireless Gaming Mouse RGB','wireless-gaming-mouse-rgb','DEMO-WIRELE-876',NULL,NULL,18.00,'Wireless Gaming Mouse RGB\n\nThis is a demo product added to showcase the Namma E Store marketplace catalog. Features premium quality, fast shipping, and easy returns. Category: Gaming.','Great quality Wireless Gaming Mouse RGB, perfect for everyday use. Demo product for showcase purposes.',1199.00,999.00,NULL,35,5,NULL,'active',0,NULL,NULL,'2026-07-18 01:54:56',0,'2026-07-18 01:54:56','2026-07-18 01:54:56');
/*!40000 ALTER TABLE `mc_products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mc_rate_limits`
--

DROP TABLE IF EXISTS `mc_rate_limits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mc_rate_limits` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `rate_key` varchar(190) NOT NULL,
  `attempts` int(10) unsigned NOT NULL DEFAULT 1,
  `first_attempt_at` datetime NOT NULL DEFAULT current_timestamp(),
  `blocked_until` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `rate_key` (`rate_key`),
  KEY `idx_blocked` (`blocked_until`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mc_rate_limits`
--

LOCK TABLES `mc_rate_limits` WRITE;
/*!40000 ALTER TABLE `mc_rate_limits` DISABLE KEYS */;
/*!40000 ALTER TABLE `mc_rate_limits` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mc_returns`
--

DROP TABLE IF EXISTS `mc_returns`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mc_returns` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int(10) unsigned NOT NULL,
  `order_item_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `vendor_id` int(10) unsigned NOT NULL,
  `type` enum('return','replacement','cancel') NOT NULL DEFAULT 'return',
  `reason` varchar(255) NOT NULL,
  `note` text DEFAULT NULL,
  `status` enum('requested','approved','rejected','picked_up','refunded','completed') NOT NULL DEFAULT 'requested',
  `refund_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `requested_at` datetime NOT NULL DEFAULT current_timestamp(),
  `resolved_at` datetime DEFAULT NULL,
  `resolved_by` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_ret_item` (`order_item_id`),
  KEY `idx_vendor` (`vendor_id`),
  KEY `idx_status` (`status`),
  KEY `idx_order` (`order_id`),
  CONSTRAINT `fk_ret_item` FOREIGN KEY (`order_item_id`) REFERENCES `mc_order_items` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ret_order` FOREIGN KEY (`order_id`) REFERENCES `mc_orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mc_returns`
--

LOCK TABLES `mc_returns` WRITE;
/*!40000 ALTER TABLE `mc_returns` DISABLE KEYS */;
/*!40000 ALTER TABLE `mc_returns` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mc_reviews`
--

DROP TABLE IF EXISTS `mc_reviews`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mc_reviews` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `order_id` int(10) unsigned DEFAULT NULL,
  `rating` tinyint(3) unsigned NOT NULL,
  `title` varchar(200) DEFAULT NULL,
  `body` text DEFAULT NULL,
  `is_approved` tinyint(1) NOT NULL DEFAULT 0,
  `is_flagged` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_user_prod_ord` (`user_id`,`product_id`,`order_id`),
  KEY `fk_rev_prod` (`product_id`),
  CONSTRAINT `fk_rev_prod` FOREIGN KEY (`product_id`) REFERENCES `mc_products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_rev_user` FOREIGN KEY (`user_id`) REFERENCES `mc_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mc_reviews`
--

LOCK TABLES `mc_reviews` WRITE;
/*!40000 ALTER TABLE `mc_reviews` DISABLE KEYS */;
/*!40000 ALTER TABLE `mc_reviews` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mc_settings`
--

DROP TABLE IF EXISTS `mc_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mc_settings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(100) NOT NULL,
  `value` text DEFAULT NULL,
  `group` varchar(50) NOT NULL DEFAULT 'general',
  `label` varchar(200) DEFAULT NULL,
  `type` enum('text','number','boolean','json','file') NOT NULL DEFAULT 'text',
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `key` (`key`)
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mc_settings`
--

LOCK TABLES `mc_settings` WRITE;
/*!40000 ALTER TABLE `mc_settings` DISABLE KEYS */;
INSERT INTO `mc_settings` VALUES (1,'site_name','Bazario','general','Site Name','text','2026-04-02 20:32:33'),(2,'site_email','info@mycart.com','general','Contact Email','text','2026-04-02 20:32:33'),(3,'site_phone','+91 9999999999','general','Contact Phone','text','2026-04-02 20:32:33'),(4,'currency','INR','general','Currency Code','text','2026-04-02 20:32:33'),(5,'currency_symbol','₹','general','Currency Symbol','text','2026-04-02 20:32:33'),(6,'default_commission','10','commission','Default Commission %','number','2026-04-02 20:32:33'),(7,'maintenance_mode','0','general','Maintenance Mode','boolean','2026-04-02 20:32:33'),(8,'order_prefix','MC','orders','Order Number Prefix','text','2026-04-02 20:32:33'),(17,'settlement_min_withdrawal','1000','settlement','Minimum Withdrawal Amount (₹)','number','2026-07-18 01:26:59'),(18,'settlement_return_window_days','7','settlement','Return Window (days after delivery)','number','2026-07-18 01:26:59'),(19,'settlement_auto_approve','0','settlement','Auto-approve withdrawals under threshold','boolean','2026-07-18 01:26:59'),(20,'settlement_platform_charge_pct','0','settlement','Flat Platform Charge (%)','number','2026-07-18 01:26:59'),(21,'company_name','Namma E Store Pvt Ltd','gst','Company Legal Name','text','2026-07-18 01:31:59'),(22,'company_gst_number','','gst','Company GSTIN','text','2026-07-18 01:31:59'),(23,'company_address','','gst','Registered Address','text','2026-07-18 01:31:59'),(24,'company_state','Maharashtra','gst','Registered State','text','2026-07-18 01:31:59'),(25,'company_pan','','gst','Company PAN','text','2026-07-18 01:31:59'),(26,'low_stock_notifications','1','inventory','Notify vendor when stock hits threshold','boolean','2026-07-18 01:31:59'),(27,'default_courier','Manual / Self-Ship','shipping','Default Courier Name','text','2026-07-18 01:31:59'),(35,'loyalty_enabled','1','loyalty','Enable Loyalty Points','boolean','2026-07-18 01:32:14'),(36,'loyalty_earn_rate','1','loyalty','Points earned per Ôé╣100 spent','number','2026-07-18 01:32:14'),(37,'loyalty_redeem_rate','0.5','loyalty','Rupee value per point redeemed','number','2026-07-18 01:32:14'),(38,'loyalty_min_redeem','100','loyalty','Minimum points required to redeem','number','2026-07-18 01:32:14');
/*!40000 ALTER TABLE `mc_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mc_shipment_tracking`
--

DROP TABLE IF EXISTS `mc_shipment_tracking`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mc_shipment_tracking` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `shipment_id` int(10) unsigned NOT NULL,
  `status` varchar(50) NOT NULL,
  `location` varchar(150) DEFAULT NULL,
  `note` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_st_shipment` (`shipment_id`),
  CONSTRAINT `fk_st_shipment` FOREIGN KEY (`shipment_id`) REFERENCES `mc_shipments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mc_shipment_tracking`
--

LOCK TABLES `mc_shipment_tracking` WRITE;
/*!40000 ALTER TABLE `mc_shipment_tracking` DISABLE KEYS */;
/*!40000 ALTER TABLE `mc_shipment_tracking` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mc_shipments`
--

DROP TABLE IF EXISTS `mc_shipments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mc_shipments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int(10) unsigned NOT NULL,
  `order_vendor_split_id` int(10) unsigned NOT NULL,
  `vendor_id` int(10) unsigned NOT NULL,
  `courier_name` varchar(100) NOT NULL,
  `tracking_number` varchar(100) NOT NULL,
  `tracking_url` varchar(255) DEFAULT NULL,
  `status` enum('pending','picked_up','in_transit','out_for_delivery','delivered','failed','rto') NOT NULL DEFAULT 'pending',
  `weight_kg` decimal(6,2) DEFAULT NULL,
  `dimensions` varchar(50) DEFAULT NULL COMMENT 'LxWxH cm, free text',
  `shipping_charge` decimal(10,2) NOT NULL DEFAULT 0.00,
  `shipped_at` datetime DEFAULT NULL,
  `delivered_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_split_shipment` (`order_vendor_split_id`),
  KEY `fk_ship_order` (`order_id`),
  KEY `idx_vendor` (`vendor_id`),
  KEY `idx_tracking` (`tracking_number`),
  CONSTRAINT `fk_ship_order` FOREIGN KEY (`order_id`) REFERENCES `mc_orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mc_shipments`
--

LOCK TABLES `mc_shipments` WRITE;
/*!40000 ALTER TABLE `mc_shipments` DISABLE KEYS */;
/*!40000 ALTER TABLE `mc_shipments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mc_subscription_plans`
--

DROP TABLE IF EXISTS `mc_subscription_plans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mc_subscription_plans` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `commission_pct` decimal(5,2) NOT NULL DEFAULT 10.00,
  `free_threshold` decimal(12,2) NOT NULL DEFAULT 0.00,
  `commission_after_threshold` decimal(5,2) NOT NULL DEFAULT 10.00,
  `billing_cycle` enum('monthly','yearly','lifetime') NOT NULL DEFAULT 'monthly',
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mc_subscription_plans`
--

LOCK TABLES `mc_subscription_plans` WRITE;
/*!40000 ALTER TABLE `mc_subscription_plans` DISABLE KEYS */;
INSERT INTO `mc_subscription_plans` VALUES (1,'Free Plan','free',0.00,10.00,0.00,10.00,'lifetime','10% commission on all sales',1,1,'2026-04-02 20:32:33','2026-04-02 20:32:33'),(2,'Starter Plan','starter',500.00,0.00,10000.00,10.00,'monthly','0% commission up to ₹10,000/month',1,2,'2026-04-02 20:32:33','2026-04-02 20:32:33'),(3,'Growth Plan','growth',1000.00,0.00,20000.00,10.00,'monthly','0% commission up to ₹20,000/month',1,3,'2026-04-02 20:32:33','2026-04-02 20:32:33');
/*!40000 ALTER TABLE `mc_subscription_plans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mc_user_addresses`
--

DROP TABLE IF EXISTS `mc_user_addresses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mc_user_addresses` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `label` varchar(50) DEFAULT 'Home',
  `recipient_name` varchar(150) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `address_line1` varchar(255) NOT NULL,
  `address_line2` varchar(255) DEFAULT NULL,
  `city` varchar(100) NOT NULL,
  `state` varchar(100) NOT NULL,
  `pincode` varchar(20) NOT NULL,
  `country` varchar(100) NOT NULL DEFAULT 'India',
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  CONSTRAINT `fk_addr_user` FOREIGN KEY (`user_id`) REFERENCES `mc_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mc_user_addresses`
--

LOCK TABLES `mc_user_addresses` WRITE;
/*!40000 ALTER TABLE `mc_user_addresses` DISABLE KEYS */;
/*!40000 ALTER TABLE `mc_user_addresses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mc_users`
--

DROP TABLE IF EXISTS `mc_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mc_users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('customer','vendor') NOT NULL DEFAULT 'customer',
  `avatar` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `is_verified` tinyint(1) NOT NULL DEFAULT 0,
  `email_verified_at` datetime DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `failed_login_attempts` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `locked_until` datetime DEFAULT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_role` (`role`),
  KEY `idx_active` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mc_users`
--

LOCK TABLES `mc_users` WRITE;
/*!40000 ALTER TABLE `mc_users` DISABLE KEYS */;
INSERT INTO `mc_users` VALUES (1,'Demo Vendor','vendor.demo@mycart.com','9876543210','$2y$10$8rlOsUJCjieOPHgcSHbzQ.NazCJgQnqh1eT6x54PZVqC9Is7sO8Fy','vendor',NULL,1,1,'2026-07-18 01:54:06',NULL,'2026-07-18 02:16:35',0,NULL,NULL,'2026-07-18 01:54:06','2026-07-18 02:16:35'),(2,'Demo Customer','customer.demo@mycart.com','9876500000','$2y$10$04BafNXKYegeHkFAbw26e.iNrUI6mmBBJbMr02SH3p52H0nyd9zz.','customer',NULL,1,1,'2026-07-18 01:54:06',NULL,'2026-07-18 02:19:22',0,NULL,NULL,'2026-07-18 01:54:06','2026-07-18 02:19:22');
/*!40000 ALTER TABLE `mc_users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mc_vendor_documents`
--

DROP TABLE IF EXISTS `mc_vendor_documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mc_vendor_documents` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `vendor_id` int(10) unsigned NOT NULL,
  `doc_type` enum('gst_certificate','pan_card','cancelled_cheque','shop_license','other') NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `status` enum('pending','verified','rejected') NOT NULL DEFAULT 'pending',
  `uploaded_at` datetime NOT NULL DEFAULT current_timestamp(),
  `verified_by` int(10) unsigned DEFAULT NULL,
  `verified_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_vendor` (`vendor_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mc_vendor_documents`
--

LOCK TABLES `mc_vendor_documents` WRITE;
/*!40000 ALTER TABLE `mc_vendor_documents` DISABLE KEYS */;
/*!40000 ALTER TABLE `mc_vendor_documents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mc_vendor_monthly_turnover`
--

DROP TABLE IF EXISTS `mc_vendor_monthly_turnover`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mc_vendor_monthly_turnover` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `vendor_id` int(10) unsigned NOT NULL,
  `month` tinyint(3) unsigned NOT NULL,
  `year` smallint(5) unsigned NOT NULL,
  `total_turnover` decimal(14,2) NOT NULL DEFAULT 0.00,
  `total_orders` int(10) unsigned NOT NULL DEFAULT 0,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_vendor_month_year` (`vendor_id`,`month`,`year`),
  CONSTRAINT `fk_vmt_vendor` FOREIGN KEY (`vendor_id`) REFERENCES `mc_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mc_vendor_monthly_turnover`
--

LOCK TABLES `mc_vendor_monthly_turnover` WRITE;
/*!40000 ALTER TABLE `mc_vendor_monthly_turnover` DISABLE KEYS */;
/*!40000 ALTER TABLE `mc_vendor_monthly_turnover` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mc_vendor_profiles`
--

DROP TABLE IF EXISTS `mc_vendor_profiles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mc_vendor_profiles` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `shop_name` varchar(200) NOT NULL,
  `shop_slug` varchar(200) NOT NULL,
  `shop_logo` varchar(255) DEFAULT NULL,
  `shop_banner` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `shop_phone` varchar(20) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `pincode` varchar(20) DEFAULT NULL,
  `gst_number` varchar(50) DEFAULT NULL,
  `pan_number` varchar(50) DEFAULT NULL,
  `bank_account` varchar(50) DEFAULT NULL,
  `bank_ifsc` varchar(20) DEFAULT NULL,
  `bank_name` varchar(100) DEFAULT NULL,
  `bank_account_name` varchar(150) DEFAULT NULL,
  `bank_account_number` varchar(50) DEFAULT NULL,
  `status` enum('pending','active','suspended','rejected') NOT NULL DEFAULT 'pending',
  `vendor_type` enum('subscription','commission') NOT NULL DEFAULT 'commission',
  `rejection_note` text DEFAULT NULL,
  `approved_by` int(10) unsigned DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`),
  UNIQUE KEY `shop_slug` (`shop_slug`),
  KEY `idx_status` (`status`),
  CONSTRAINT `fk_vp_user` FOREIGN KEY (`user_id`) REFERENCES `mc_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mc_vendor_profiles`
--

LOCK TABLES `mc_vendor_profiles` WRITE;
/*!40000 ALTER TABLE `mc_vendor_profiles` DISABLE KEYS */;
INSERT INTO `mc_vendor_profiles` VALUES (1,1,'Namma E Store Demo Shop','namma-demo-shop',NULL,NULL,'Demo shop seeded for showcasing the marketplace.',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'active','commission',NULL,NULL,NULL,'2026-07-18 01:54:06','2026-07-18 01:54:06');
/*!40000 ALTER TABLE `mc_vendor_profiles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mc_vendor_settlements`
--

DROP TABLE IF EXISTS `mc_vendor_settlements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mc_vendor_settlements` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `vendor_id` int(10) unsigned NOT NULL,
  `order_id` int(10) unsigned NOT NULL,
  `order_vendor_split_id` int(10) unsigned NOT NULL,
  `gross_amount` decimal(12,2) NOT NULL,
  `tax_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `shipping_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `platform_charge` decimal(12,2) NOT NULL DEFAULT 0.00,
  `commission_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `penalty_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `refund_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `subscription_due` decimal(12,2) NOT NULL DEFAULT 0.00,
  `net_payable` decimal(12,2) NOT NULL DEFAULT 0.00,
  `status` enum('not_eligible','eligible','credited','on_hold') NOT NULL DEFAULT 'not_eligible',
  `hold_reason` varchar(255) DEFAULT NULL,
  `eligible_at` datetime DEFAULT NULL,
  `credited_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_split` (`order_vendor_split_id`),
  KEY `fk_settle_order` (`order_id`),
  KEY `idx_vendor_status` (`vendor_id`,`status`),
  CONSTRAINT `fk_settle_order` FOREIGN KEY (`order_id`) REFERENCES `mc_orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mc_vendor_settlements`
--

LOCK TABLES `mc_vendor_settlements` WRITE;
/*!40000 ALTER TABLE `mc_vendor_settlements` DISABLE KEYS */;
/*!40000 ALTER TABLE `mc_vendor_settlements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mc_vendor_subscriptions`
--

DROP TABLE IF EXISTS `mc_vendor_subscriptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mc_vendor_subscriptions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `vendor_id` int(10) unsigned NOT NULL,
  `plan_id` int(10) unsigned NOT NULL,
  `status` enum('active','expired','cancelled') NOT NULL DEFAULT 'active',
  `started_at` datetime NOT NULL,
  `expires_at` datetime DEFAULT NULL,
  `payment_ref` varchar(100) DEFAULT NULL,
  `amount_paid` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_vs_plan` (`plan_id`),
  KEY `idx_vendor_status` (`vendor_id`,`status`),
  CONSTRAINT `fk_vs_plan` FOREIGN KEY (`plan_id`) REFERENCES `mc_subscription_plans` (`id`),
  CONSTRAINT `fk_vs_vendor` FOREIGN KEY (`vendor_id`) REFERENCES `mc_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mc_vendor_subscriptions`
--

LOCK TABLES `mc_vendor_subscriptions` WRITE;
/*!40000 ALTER TABLE `mc_vendor_subscriptions` DISABLE KEYS */;
/*!40000 ALTER TABLE `mc_vendor_subscriptions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mc_vendor_wallet_transactions`
--

DROP TABLE IF EXISTS `mc_vendor_wallet_transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mc_vendor_wallet_transactions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `vendor_id` int(10) unsigned NOT NULL,
  `type` enum('credit','debit') NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `balance_after` decimal(14,2) NOT NULL,
  `reference_type` enum('settlement','withdrawal','penalty','refund_deduction','adjustment') NOT NULL,
  `reference_id` int(10) unsigned DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_vendor` (`vendor_id`),
  KEY `idx_reference` (`reference_type`,`reference_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mc_vendor_wallet_transactions`
--

LOCK TABLES `mc_vendor_wallet_transactions` WRITE;
/*!40000 ALTER TABLE `mc_vendor_wallet_transactions` DISABLE KEYS */;
/*!40000 ALTER TABLE `mc_vendor_wallet_transactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mc_vendor_wallets`
--

DROP TABLE IF EXISTS `mc_vendor_wallets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mc_vendor_wallets` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `vendor_id` int(10) unsigned NOT NULL,
  `balance` decimal(14,2) NOT NULL DEFAULT 0.00,
  `total_earned` decimal(14,2) NOT NULL DEFAULT 0.00,
  `total_withdrawn` decimal(14,2) NOT NULL DEFAULT 0.00,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `vendor_id` (`vendor_id`),
  CONSTRAINT `fk_vw_vendor` FOREIGN KEY (`vendor_id`) REFERENCES `mc_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mc_vendor_wallets`
--

LOCK TABLES `mc_vendor_wallets` WRITE;
/*!40000 ALTER TABLE `mc_vendor_wallets` DISABLE KEYS */;
/*!40000 ALTER TABLE `mc_vendor_wallets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mc_vendor_withdrawals`
--

DROP TABLE IF EXISTS `mc_vendor_withdrawals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mc_vendor_withdrawals` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `vendor_id` int(10) unsigned NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `method` enum('upi','bank_transfer','wallet') NOT NULL,
  `method_details` varchar(255) DEFAULT NULL,
  `status` enum('pending','approved','rejected','paid') NOT NULL DEFAULT 'pending',
  `admin_note` varchar(255) DEFAULT NULL,
  `requested_at` datetime NOT NULL DEFAULT current_timestamp(),
  `processed_at` datetime DEFAULT NULL,
  `processed_by` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_vendor_status` (`vendor_id`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mc_vendor_withdrawals`
--

LOCK TABLES `mc_vendor_withdrawals` WRITE;
/*!40000 ALTER TABLE `mc_vendor_withdrawals` DISABLE KEYS */;
/*!40000 ALTER TABLE `mc_vendor_withdrawals` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mc_wishlists`
--

DROP TABLE IF EXISTS `mc_wishlists`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mc_wishlists` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `product_id` int(10) unsigned NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_user_product` (`user_id`,`product_id`),
  KEY `fk_wl_prod` (`product_id`),
  CONSTRAINT `fk_wl_prod` FOREIGN KEY (`product_id`) REFERENCES `mc_products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_wl_user` FOREIGN KEY (`user_id`) REFERENCES `mc_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mc_wishlists`
--

LOCK TABLES `mc_wishlists` WRITE;
/*!40000 ALTER TABLE `mc_wishlists` DISABLE KEYS */;
/*!40000 ALTER TABLE `mc_wishlists` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping events for database 'mycart_marketplace'
--

--
-- Dumping routines for database 'mycart_marketplace'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-07-20 12:45:33
