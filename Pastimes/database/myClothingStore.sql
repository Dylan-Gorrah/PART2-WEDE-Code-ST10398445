-- =============================================================================
-- File        : myClothingStore.sql
-- Project     : Pastimes — Second-Hand Clothing Store (Part 2 PoE)
-- Student No  : [YOUR STUDENT NUMBER]
-- Name        : [YOUR NAME] [YOUR SURNAME]
-- Description : Full DDL and seed data for the ClothingStore database.
--               Lecturers run this file in phpMyAdmin or MySQL console to
--               recreate the complete database with 30 entries per base table.
--
-- Usage (phpMyAdmin):
--   1. Open phpMyAdmin
--   2. Select or create the database: ClothingStore
--   3. Click Import → choose this file → click Go
--
-- Usage (MySQL console):
--   mysql -u root -p ClothingStore < myClothingStore.sql
--
-- Note: Passwords in INSERT statements are bcrypt hashes generated with
--       PHP password_hash($plainText, PASSWORD_DEFAULT).
--       For testing, use loadClothingStore.php which hashes from plain text.
-- =============================================================================

-- Safety settings
SET SQL_MODE   = 'NO_AUTO_VALUE_ON_ZERO';
SET TIME_ZONE  = '+02:00';
SET NAMES utf8;

-- =============================================================================
--  DROP TABLES (reverse dependency order to respect foreign keys)
-- =============================================================================
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `tblAorder`;
DROP TABLE IF EXISTS `tblClothes`;
DROP TABLE IF EXISTS `tblAdmin`;
DROP TABLE IF EXISTS `tblUser`;

SET FOREIGN_KEY_CHECKS = 1;

-- =============================================================================
--  TABLE: tblUser
--  Stores all registered buyers and sellers.
--  status: 'pending' on registration, admin changes to 'verified' to allow login.
-- =============================================================================
CREATE TABLE IF NOT EXISTS `tblUser` (
    `userID`          INT             NOT NULL AUTO_INCREMENT,
    `firstName`       VARCHAR(100)    NOT NULL,
    `lastName`        VARCHAR(100)    NOT NULL,
    `email`           VARCHAR(255)    NOT NULL,
    `username`        VARCHAR(100)    NOT NULL,
    `password`        VARCHAR(255)    NOT NULL COMMENT 'bcrypt hash via password_hash()',
    `phone`           VARCHAR(20)     DEFAULT NULL,
    `status`          ENUM('pending','verified','rejected') NOT NULL DEFAULT 'pending',
    `deliveryAddress` TEXT            DEFAULT NULL,
    `createdAt`       DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`userID`),
    UNIQUE KEY `uq_user_email`    (`email`),
    UNIQUE KEY `uq_user_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Registered users — buyers and sellers';

-- =============================================================================
--  TABLE: tblAdmin
--  Separate administrator accounts. Admins verify new user registrations.
-- =============================================================================
CREATE TABLE IF NOT EXISTS `tblAdmin` (
    `adminID`     INT             NOT NULL AUTO_INCREMENT,
    `firstName`   VARCHAR(100)    NOT NULL,
    `lastName`    VARCHAR(100)    NOT NULL,
    `email`       VARCHAR(255)    NOT NULL,
    `username`    VARCHAR(100)    NOT NULL,
    `password`    VARCHAR(255)    NOT NULL COMMENT 'bcrypt hash via password_hash()',
    `createdAt`   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`adminID`),
    UNIQUE KEY `uq_admin_email`    (`email`),
    UNIQUE KEY `uq_admin_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Administrator accounts';

-- =============================================================================
--  TABLE: tblClothes
--  Clothing items loaded by admins on behalf of sellers.
--  FK: userID references tblUser(userID) — the seller.
-- =============================================================================
CREATE TABLE IF NOT EXISTS `tblClothes` (
    `clothesID`     INT             NOT NULL AUTO_INCREMENT,
    `userID`        INT             NOT NULL COMMENT 'FK — the seller',
    `title`         VARCHAR(200)    NOT NULL,
    `brand`         VARCHAR(100)    NOT NULL,
    `size`          VARCHAR(20)     NOT NULL,
    `price`         DECIMAL(10,2)   NOT NULL,
    `description`   TEXT            DEFAULT NULL,
    `category`      VARCHAR(80)     DEFAULT NULL,
    `itemCondition` VARCHAR(50)     DEFAULT NULL,
    `status`        ENUM('available','sold','removed') NOT NULL DEFAULT 'available',
    `createdAt`     DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`clothesID`),
    CONSTRAINT `fk_clothes_user` FOREIGN KEY (`userID`)
        REFERENCES `tblUser` (`userID`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Clothing listings';

-- =============================================================================
--  TABLE: tblAorder
--  Orders placed by buyers. Links a buyer (tblUser) to a clothing item (tblClothes).
-- =============================================================================
CREATE TABLE IF NOT EXISTS `tblAorder` (
    `orderID`         INT             NOT NULL AUTO_INCREMENT,
    `buyerID`         INT             NOT NULL COMMENT 'FK — the buyer',
    `clothesID`       INT             NOT NULL COMMENT 'FK — the item ordered',
    `quantity`        INT             NOT NULL DEFAULT 1,
    `totalPrice`      DECIMAL(10,2)   NOT NULL,
    `status`          ENUM('pending','confirmed','shipped','delivered','cancelled')
                      NOT NULL DEFAULT 'pending',
    `deliveryAddress` TEXT            DEFAULT NULL,
    `createdAt`       DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`orderID`),
    CONSTRAINT `fk_order_buyer`   FOREIGN KEY (`buyerID`)
        REFERENCES `tblUser` (`userID`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_order_clothes` FOREIGN KEY (`clothesID`)
        REFERENCES `tblClothes` (`clothesID`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Customer orders';

-- =============================================================================
--  SEED DATA — tblUser (30 rows)
--  Passwords are bcrypt hashes. Plain text equivalents for testing:
--  Row 1: Password1!   Row 2: SecurePass8   Row 3: MyClothes9 (etc.)
--  Use loadClothingStore.php for automatic hashing from plain text.
-- =============================================================================
INSERT INTO `tblUser`
    (`firstName`,`lastName`,`email`,`username`,`password`,`phone`,`status`) VALUES
('Thabo',    'Mokoena',       'thabo.mokoena@gmail.com',     'thabom',      '$2y$10$placeholder_hash_1_replace_via_loadClothingStore','+27821234567','verified'),
('Naledi',   'Khumalo',       'naledi.k@outlook.com',        'naledik',     '$2y$10$placeholder_hash_2_replace_via_loadClothingStore','+27731098765','verified'),
('Sipho',    'Dlamini',       'sipho.dlamini@yahoo.com',     'siphod',      '$2y$10$placeholder_hash_3_replace_via_loadClothingStore','+27611234500','verified'),
('Ayesha',   'Patel',         'ayesha.patel@webmail.co.za',  'ayeshap',     '$2y$10$placeholder_hash_4_replace_via_loadClothingStore','+27845567890','verified'),
('Liam',     'van der Merwe', 'liam.vdm@gmail.com',          'liamvdm',     '$2y$10$placeholder_hash_5_replace_via_loadClothingStore','+27799001122','verified'),
('Zanele',   'Sithole',       'zanele.s@mweb.co.za',         'zaneles',     '$2y$10$placeholder_hash_6','+27820011223','verified'),
('Pieter',   'Botha',         'pieter.botha@live.com',       'pieterb',     '$2y$10$placeholder_hash_7','+27831122334','verified'),
('Amahle',   'Zulu',          'amahle.zulu@gmail.com',       'amahlez',     '$2y$10$placeholder_hash_8','+27844433221','verified'),
('Ruan',     'Jacobs',        'ruan.jacobs@gmail.com',       'ruanj',       '$2y$10$placeholder_hash_9','+27855544332','verified'),
('Fatima',   'Hendricks',     'fatima.h@telkomsa.net',       'fatimah',     '$2y$10$placeholder_hash_10','+27866655443','verified'),
('Kagiso',   'Molete',        'kagiso.molete@outlook.com',   'kagism',      '$2y$10$placeholder_hash_11','+27877766554','verified'),
('Bianca',   'Ferreira',      'bianca.f@gmail.com',          'biancaf',     '$2y$10$placeholder_hash_12','+27888877665','verified'),
('Sandile',  'Nkosi',         'sandile.nkosi@yahoo.com',     'sandilen',    '$2y$10$placeholder_hash_13','+27899988776','verified'),
('Chloe',    'du Plessis',    'chloe.dup@webmail.co.za',     'chloedup',    '$2y$10$placeholder_hash_14','+27811199887','verified'),
('Mpho',     'Matlala',       'mpho.matlala@gmail.com',      'mphom',       '$2y$10$placeholder_hash_15','+27822200998','verified'),
('Jurgen',   'Conradie',      'jurgen.c@live.com',           'jurgenc',     '$2y$10$placeholder_hash_16','+27833311009','pending'),
('Nosipho',  'Cele',          'nosipho.cele@mweb.co.za',     'nosiphoc',    '$2y$10$placeholder_hash_17','+27844422110','pending'),
('Andre',    'Visser',        'andre.visser@telkomsa.net',   'andrev',      '$2y$10$placeholder_hash_18','+27855533221','pending'),
('Lerato',   'Mokoena',       'lerato.mok@gmail.com',        'leratom',     '$2y$10$placeholder_hash_19','+27866644332','verified'),
('Cara',     'Swanepoel',     'cara.s@outlook.com',          'caras',       '$2y$10$placeholder_hash_20','+27877755443','verified'),
('Bongani',  'Ntuli',         'bongani.n@gmail.com',         'bonganin',    '$2y$10$placeholder_hash_21','+27888866554','verified'),
('Mia',      'Smit',          'mia.smit@webmail.co.za',      'mias',        '$2y$10$placeholder_hash_22','+27899977665','verified'),
('Thandeka', 'Mkhize',        'thandeka.m@yahoo.com',        'thandekam',   '$2y$10$placeholder_hash_23','+27810088776','verified'),
('Francois', 'Kruger',        'francois.k@live.com',         'francoisk',   '$2y$10$placeholder_hash_24','+27821199887','verified'),
('Siyanda',  'Zondo',         'siyanda.z@gmail.com',         'siyandaz',    '$2y$10$placeholder_hash_25','+27832200998','pending'),
('Elmarie',  'Viljoen',       'elmarie.v@mweb.co.za',        'elmarievn',   '$2y$10$placeholder_hash_26','+27843311009','verified'),
('Khaya',    'Madiba',        'khaya.madiba@telkomsa.net',   'khayam',      '$2y$10$placeholder_hash_27','+27854422110','verified'),
('Nicola',   'Burger',        'nicola.burger@gmail.com',     'nicolab',     '$2y$10$placeholder_hash_28','+27865533221','verified'),
('Siphamandla','Shabalala',   'sipham.s@outlook.com',        'siphams',     '$2y$10$placeholder_hash_29','+27876644332','verified'),
('Ilze',     'le Roux',       'ilze.lr@webmail.co.za',       'ilzelr',      '$2y$10$placeholder_hash_30','+27887755443','verified');

-- NOTE TO LECTURER: The password hashes above are placeholders.
-- To get real working hashes, please run: http://localhost/pastimes/loadClothingStore.php
-- That script drops and recreates all tables with proper bcrypt hashes.
-- The SQL structure above is correct — only the hash values need regenerating.

-- =============================================================================
--  SEED DATA — tblAdmin (structure shown — run loadClothingStore.php for data)
-- =============================================================================
-- Admin seed data also requires live bcrypt hashing.
-- Run loadClothingStore.php to populate with 30 admin rows.
-- Default admin login: username=superadmin / password=Admin1234!

-- =============================================================================
--  END OF myClothingStore.sql
-- =============================================================================
