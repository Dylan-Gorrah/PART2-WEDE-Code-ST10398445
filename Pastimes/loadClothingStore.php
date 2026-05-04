<?php
/*
 * Student 1      : ST10398445 — Dylan Gorrah
 * Student 2      : ST10452409 — Liyema Masala
 * Student 3      : ST10444003 — Makwatsa Mabizela
 * Declaration    : This code is my own work where not referenced.
 * File           : loadClothingStore.php
 * Description    : Drops ALL tables in ClothingStore, recreates them with
 *                  correct structure and foreign keys, then inserts 30 rows
 *                  into EACH table.
 *                  Lecturers run this script to fully restore the database.
 *
 *                  Run at: http://localhost/pastimes/loadClothingStore.php
 *
 * References:
 *   MySQLi — https://www.php.net/manual/en/book.mysqli.php
 *   password_hash() — https://www.php.net/manual/en/function.password-hash.php
 *   ENUM type — https://dev.mysql.com/doc/refman/8.0/en/enum.html
 */

require_once 'DBConn.php';

// Collect a log of everything that happens
$log = [];

// ─── Helper: run a query and log the result ──────────────────────────────────
function run($conn, $sql, $label, &$log) {
    if ($conn->query($sql)) {
        $log[] = ['ok', "✅ $label"];
        return true;
    }
    $log[] = ['err', "❌ $label — " . $conn->error];
    return false;
}

// ════════════════════════════════════════════════════════════════════════════
//  STEP 1 — Drop tables in reverse dependency order (foreign keys first)
// ════════════════════════════════════════════════════════════════════════════
$log[] = ['head', 'STEP 1 — Dropping existing tables'];

run($conn, "SET FOREIGN_KEY_CHECKS = 0", "Disabled FK checks", $log);
run($conn, "DROP TABLE IF EXISTS tblAorder",  "Dropped tblAorder",  $log);
run($conn, "DROP TABLE IF EXISTS tblClothes", "Dropped tblClothes", $log);
run($conn, "DROP TABLE IF EXISTS tblAdmin",   "Dropped tblAdmin",   $log);
run($conn, "DROP TABLE IF EXISTS tblUser",    "Dropped tblUser",    $log);
run($conn, "SET FOREIGN_KEY_CHECKS = 1", "Re-enabled FK checks", $log);

// ════════════════════════════════════════════════════════════════════════════
//  STEP 2 — Create all tables
// ════════════════════════════════════════════════════════════════════════════
$log[] = ['head', 'STEP 2 — Creating tables'];

// tblUser — registered buyers and sellers
run($conn, "
    CREATE TABLE IF NOT EXISTS tblUser (
        userID          INT             NOT NULL AUTO_INCREMENT,
        firstName       VARCHAR(100)    NOT NULL,
        lastName        VARCHAR(100)    NOT NULL,
        email           VARCHAR(255)    NOT NULL,
        username        VARCHAR(100)    NOT NULL,
        password        VARCHAR(255)    NOT NULL,
        phone           VARCHAR(20)     DEFAULT NULL,
        status          ENUM('pending','verified','rejected') NOT NULL DEFAULT 'pending',
        deliveryAddress TEXT            DEFAULT NULL,
        createdAt       DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (userID),
        UNIQUE KEY uq_email    (email),
        UNIQUE KEY uq_username (username)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
", "Created tblUser", $log);

// tblAdmin — administrator accounts (separate from tblUser)
run($conn, "
    CREATE TABLE IF NOT EXISTS tblAdmin (
        adminID     INT             NOT NULL AUTO_INCREMENT,
        firstName   VARCHAR(100)    NOT NULL,
        lastName    VARCHAR(100)    NOT NULL,
        email       VARCHAR(255)    NOT NULL,
        username    VARCHAR(100)    NOT NULL,
        password    VARCHAR(255)    NOT NULL,
        createdAt   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (adminID),
        UNIQUE KEY uq_admin_email    (email),
        UNIQUE KEY uq_admin_username (username)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
", "Created tblAdmin", $log);

// tblClothes — clothing listings uploaded by sellers (via admin)
run($conn, "
    CREATE TABLE IF NOT EXISTS tblClothes (
        clothesID   INT             NOT NULL AUTO_INCREMENT,
        userID      INT             NOT NULL,
        title       VARCHAR(200)    NOT NULL,
        brand       VARCHAR(100)    NOT NULL,
        size        VARCHAR(20)     NOT NULL,
        price       DECIMAL(10,2)   NOT NULL,
        description TEXT            DEFAULT NULL,
        category    VARCHAR(80)     DEFAULT NULL,
        itemCondition VARCHAR(50)   DEFAULT NULL,
        status      ENUM('available','sold','removed') NOT NULL DEFAULT 'available',
        createdAt   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (clothesID),
        CONSTRAINT fk_clothes_user FOREIGN KEY (userID) REFERENCES tblUser(userID)
            ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
", "Created tblClothes", $log);

// tblAorder — orders placed by buyers
run($conn, "
    CREATE TABLE IF NOT EXISTS tblAorder (
        orderID         INT             NOT NULL AUTO_INCREMENT,
        buyerID         INT             NOT NULL,
        clothesID       INT             NOT NULL,
        quantity        INT             NOT NULL DEFAULT 1,
        totalPrice      DECIMAL(10,2)   NOT NULL,
        status          ENUM('pending','confirmed','shipped','delivered','cancelled')
                        NOT NULL DEFAULT 'pending',
        deliveryAddress TEXT            DEFAULT NULL,
        createdAt       DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (orderID),
        CONSTRAINT fk_order_buyer   FOREIGN KEY (buyerID)   REFERENCES tblUser(userID)
            ON DELETE CASCADE ON UPDATE CASCADE,
        CONSTRAINT fk_order_clothes FOREIGN KEY (clothesID) REFERENCES tblClothes(clothesID)
            ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
", "Created tblAorder", $log);

// ════════════════════════════════════════════════════════════════════════════
//  STEP 3 — Insert 30 rows into tblUser
// ════════════════════════════════════════════════════════════════════════════
$log[] = ['head', 'STEP 3 — Seeding tblUser (30 rows)'];

// 30 South African user entries — mix of verified and pending
$users = [
    ['Thabo',    'Mokoena',       'thabo.mokoena@gmail.com',       'thabom',       'Password1!',   '+27821234567', 'verified'],
    ['Naledi',   'Khumalo',       'naledi.k@outlook.com',          'naledik',      'SecurePass8',  '+27731098765', 'verified'],
    ['Sipho',    'Dlamini',       'sipho.dlamini@yahoo.com',       'siphod',       'MyClothes9',   '+27611234500', 'verified'],
    ['Ayesha',   'Patel',         'ayesha.patel@webmail.co.za',    'ayeshap',      'Fashion88!',   '+27845567890', 'verified'],
    ['Liam',     'van der Merwe', 'liam.vdm@gmail.com',            'liamvdm',      'Vintage99#',   '+27799001122', 'verified'],
    ['Zanele',   'Sithole',       'zanele.s@mweb.co.za',           'zaneles',      'Zanele2024!',  '+27820011223', 'verified'],
    ['Pieter',   'Botha',         'pieter.botha@live.com',         'pieterb',      'Pieter777@',   '+27831122334', 'verified'],
    ['Amahle',   'Zulu',          'amahle.zulu@gmail.com',         'amahlez',      'Amahle55#',    '+27844433221', 'verified'],
    ['Ruan',     'Jacobs',        'ruan.jacobs@gmail.com',         'ruanj',        'Ruan1234!',    '+27855544332', 'verified'],
    ['Fatima',   'Hendricks',     'fatima.h@telkomsa.net',         'fatimah',      'Fatima99$',    '+27866655443', 'verified'],
    ['Kagiso',   'Molete',        'kagiso.molete@outlook.com',     'kagism',       'Kagiso2023#',  '+27877766554', 'verified'],
    ['Bianca',   'Ferreira',      'bianca.f@gmail.com',            'biancaf',      'Bianca888!',   '+27888877665', 'verified'],
    ['Sandile',  'Nkosi',         'sandile.nkosi@yahoo.com',       'sandilen',     'Sandile66!',   '+27899988776', 'verified'],
    ['Chloé',    'du Plessis',    'chloe.dup@webmail.co.za',       'chloedup',     'Chloe2024$',   '+27811199887', 'verified'],
    ['Mpho',     'Matlala',       'mpho.matlala@gmail.com',        'mphom',        'Mpho1234@',    '+27822200998', 'verified'],
    ['Jurgen',   'Conradie',      'jurgen.c@live.com',             'jurgenc',      'Jurgen55!',    '+27833311009', 'pending'],
    ['Nosipho',  'Cele',          'nosipho.cele@mweb.co.za',       'nosiphoc',     'Nosipho88#',   '+27844422110', 'pending'],
    ['Andre',    'Visser',        'andre.visser@telkomsa.net',     'andrev',       'Andre2023!',   '+27855533221', 'pending'],
    ['Lerato',   'Mokoena',       'lerato.mok@gmail.com',          'leratom',      'Lerato77@',    '+27866644332', 'verified'],
    ['Cara',     'Swanepoel',     'cara.s@outlook.com',            'caras',        'Cara1234#',    '+27877755443', 'verified'],
    ['Bongani',  'Ntuli',         'bongani.n@gmail.com',           'bonganin',     'Bongani99$',   '+27888866554', 'verified'],
    ['Mia',      'Smit',          'mia.smit@webmail.co.za',        'mias',         'Mia2024!',     '+27899977665', 'verified'],
    ['Thandeka', 'Mkhize',        'thandeka.m@yahoo.com',          'thandekam',    'Thandeka55!',  '+27810088776', 'verified'],
    ['Francois', 'Kruger',        'francois.k@live.com',           'francoisk',    'Francois88#',  '+27821199887', 'verified'],
    ['Siyanda',  'Zondo',         'siyanda.z@gmail.com',           'siyandaz',     'Siyanda66$',   '+27832200998', 'pending'],
    ['Elmarie',  'Viljoen',       'elmarie.v@mweb.co.za',          'elmarieven',   'Elmarie22!',   '+27843311009', 'verified'],
    ['Khaya',    'Madiba',        'khaya.madiba@telkomsa.net',     'khayam',       'Khaya2023#',   '+27854422110', 'verified'],
    ['Nicola',   'Burger',        'nicola.burger@gmail.com',       'nicolab',      'Nicola77@',    '+27865533221', 'verified'],
    ['Siphamandla','Shabalala',   'sipham.s@outlook.com',          'siphams',      'Sipham88!',    '+27876644332', 'verified'],
    ['Ilze',     'le Roux',       'ilze.lr@webmail.co.za',         'ilzelr',       'Ilze1234$',    '+27887755443', 'verified'],
];

$insertedUsers = 0;
$stmt = $conn->prepare(
    "INSERT INTO tblUser (firstName, lastName, email, username, password, phone, status)
     VALUES (?, ?, ?, ?, ?, ?, ?)"
);

foreach ($users as $u) {
    $hashed = password_hash($u[4], PASSWORD_DEFAULT);
    $stmt->bind_param("sssssss", $u[0], $u[1], $u[2], $u[3], $hashed, $u[5], $u[6]);
    if ($stmt->execute()) {
        $insertedUsers++;
    } else {
        $log[] = ['err', "❌ Failed inserting user {$u[3]}: " . $stmt->error];
    }
}
$stmt->close();
$log[] = ['ok', "✅ tblUser — inserted $insertedUsers / 30 rows"];

// ════════════════════════════════════════════════════════════════════════════
//  STEP 4 — Insert 30 rows into tblAdmin
// ════════════════════════════════════════════════════════════════════════════
$log[] = ['head', 'STEP 4 — Seeding tblAdmin (30 rows)'];

$admins = [
    ['Super',      'Admin',      'superadmin@pastimes.co.za',      'superadmin',      'Admin1234!'],
    ['Priya',      'Naidoo',     'priya.naidoo@pastimes.co.za',    'priya_admin',     'Priya5678@'],
    ['Marcus',     'Botha',      'marcus.botha@pastimes.co.za',    'marcus_mod',      'Marcus99#'],
    ['Zanele',     'Admin',      'zanele.a@pastimes.co.za',        'zanele_mod',      'Zanele77!'],
    ['Ruben',      'Jacobs',     'ruben.j@pastimes.co.za',         'ruben_admin',     'Ruben2024$'],
    ['Tumi',       'Nkosi',      'tumi.n@pastimes.co.za',          'tumi_admin',      'Tumi5555!'],
    ['Elsa',       'Venter',     'elsa.v@pastimes.co.za',          'elsa_mod',        'Elsa2222@'],
    ['Abdul',      'Karriem',    'abdul.k@pastimes.co.za',         'abdulk',          'Abdul88#'],
    ['Sasha',      'Pretorius',  'sasha.p@pastimes.co.za',         'sashap',          'Sasha99!'],
    ['Lindani',    'Dube',       'lindani.d@pastimes.co.za',       'lindanid',        'Lindani44$'],
    ['Charmaine',  'October',    'charmaine.o@pastimes.co.za',     'charmaino',       'Charm2024!'],
    ['Byron',      'Kotzé',      'byron.k@pastimes.co.za',         'byronk',          'Byron7777#'],
    ['Nomazizi',   'Mthembu',    'nomaz.m@pastimes.co.za',         'nomazm',          'Nomaz33@'],
    ['Hannes',     'Potgieter',  'hannes.p@pastimes.co.za',        'hannesp',         'Hannes66!'],
    ['Sumaiya',    'Bhoola',     'sumaiya.b@pastimes.co.za',       'sumaiyab',        'Sumaiya55$'],
    ['Rethabile',  'Moseneke',   'rethabile.m@pastimes.co.za',     'rethablem',       'Reth2024#'],
    ['Deon',       'Erasmus',    'deon.e@pastimes.co.za',          'doene',           'Deon1234!'],
    ['Yolanda',    'September',  'yolanda.s@pastimes.co.za',       'yolandas',        'Yol2023@'],
    ['Tshepo',     'Nkgophi',    'tshepo.n@pastimes.co.za',        'tshepoon',        'Tshepo88#'],
    ['Anneke',     'du Toit',    'anneke.dt@pastimes.co.za',       'annekedt',        'Anneke77!'],
    ['Sibongile',  'Hadebe',     'sibongile.h@pastimes.co.za',     'siboh',           'Sibo2024$'],
    ['Pieter-Jan', 'Lötter',     'pj.l@pastimes.co.za',            'pjlotter',        'PJLot55@'],
    ['Mihlali',    'Ndamase',    'mihlali.n@pastimes.co.za',       'mihlalin',        'Mih1234!'],
    ['Corné',      'Rousseau',   'corne.r@pastimes.co.za',         'cornerouss',      'Corne99#'],
    ['Kwanele',    'Magwaza',    'kwanele.m@pastimes.co.za',       'kwanelem',        'Kwan2024@'],
    ['Marié',      'van Wyk',    'marie.vw@pastimes.co.za',        'marievw',         'Marie55!'],
    ['Lunga',      'Dyosopu',    'lunga.d@pastimes.co.za',         'lungad',          'Lunga33$'],
    ['Riette',     'Lombaard',   'riette.l@pastimes.co.za',        'riettel',         'Riette88#'],
    ['Nhlanhla',   'Mthiyane',   'nhlanhla.m@pastimes.co.za',      'nhlanh',          'Nhla2023!'],
    ['Cornelia',   'Steyn',      'cornelia.s@pastimes.co.za',      'cornelias',       'Corn2024@'],
];

$insertedAdmins = 0;
$stmt = $conn->prepare(
    "INSERT INTO tblAdmin (firstName, lastName, email, username, password)
     VALUES (?, ?, ?, ?, ?)"
);

foreach ($admins as $a) {
    $hashed = password_hash($a[4], PASSWORD_DEFAULT);
    $stmt->bind_param("sssss", $a[0], $a[1], $a[2], $a[3], $hashed);
    if ($stmt->execute()) {
        $insertedAdmins++;
    } else {
        $log[] = ['err', "❌ Failed inserting admin {$a[3]}: " . $stmt->error];
    }
}
$stmt->close();
$log[] = ['ok', "✅ tblAdmin — inserted $insertedAdmins / 30 rows"];

// ════════════════════════════════════════════════════════════════════════════
//  STEP 5 — Insert 30 rows into tblClothes (references tblUser IDs 1-10)
// ════════════════════════════════════════════════════════════════════════════
$log[] = ['head', 'STEP 5 — Seeding tblClothes (30 rows)'];

$clothes = [
    [1, 'Classic White Shirt',           'Ralph Lauren',  'M',   450.00, 'Barely worn Oxford button-down. No stains.',              'Tops',         'Very Good', 'available'],
    [1, 'High-Waist Skinny Jeans',       "Levi's",        '32',  320.00, 'Dark indigo slim jeans. Light wash on knees.',            'Bottoms',      'Good',      'available'],
    [1, 'Camel Wool Winter Coat',        'Zara',          'L',   780.00, 'Worn one season. All buttons intact.',                    'Outerwear',    'Like New',  'available'],
    [2, 'Floral Midi Sundress',          'H&M',           'S',   180.00, 'Pastel floral print. Light pilling on inside.',           'Dresses',      'Good',      'available'],
    [2, 'Genuine Leather Belt',          'Tommy Hilfiger','95cm',150.00, 'Black reversible with gold buckle. Barely used.',         'Accessories',  'Like New',  'available'],
    [2, 'Navy Blazer',                   'Woolworths',    '40',  560.00, 'Classic cut navy blazer. Dry cleaned.',                   'Jackets',      'Very Good', 'available'],
    [3, 'Slim Chino Trousers',           'Banana Republic','34', 290.00, 'Khaki slim chinos. One small mark near pocket.',          'Bottoms',      'Good',      'available'],
    [3, 'Striped Boatneck Top',          'Gap',           'XS',  120.00, 'Blue and white Breton stripe. Soft cotton.',              'Tops',         'Very Good', 'available'],
    [3, 'Black Leather Ankle Boots',     'Steve Madden',  '7',   490.00, 'Genuine leather. Heel has minor scuff.',                  'Shoes',        'Good',      'available'],
    [4, 'Wrap Maxi Skirt',              'ASOS',           'M',   160.00, 'Terracotta wrap skirt. Perfect condition.',               'Bottoms',      'Like New',  'available'],
    [4, 'Oversized Denim Jacket',        "Levi's",        'L',   420.00, 'Light wash oversized trucker jacket.',                    'Jackets',      'Very Good', 'available'],
    [4, 'Silk Blouse',                   'Reiss',         'S',   380.00, 'Cream silk blouse. Hand wash only.',                      'Tops',         'Good',      'available'],
    [5, 'Pleated Midi Skirt',            'Massimo Dutti', 'M',   340.00, 'Dusty rose pleated skirt. Elegant.',                     'Bottoms',      'Like New',  'available'],
    [5, 'Merino Wool Sweater',           'Uniqlo',        'L',   280.00, 'Navy merino crew neck. Pilling on cuffs.',                'Tops',         'Good',      'available'],
    [5, 'Canvas Sneakers',               'Converse',      '9',   190.00, 'Classic white Chuck Taylors. Yellowing on sole.',         'Shoes',        'Good',      'available'],
    [6, 'Tailored Suit Trousers',        'Hugo Boss',     '32',  650.00, 'Charcoal grey. Part of a suit. Excellent.',               'Bottoms',      'Like New',  'available'],
    [6, 'Puffer Vest',                   'The North Face','M',   520.00, 'Black 700-fill down vest. No damage.',                    'Outerwear',    'Very Good', 'available'],
    [6, 'Flare Leg Trousers',            'Mango',         'S',   210.00, 'Cream wide-leg pants. Summer weight.',                    'Bottoms',      'Like New',  'available'],
    [7, 'Lightweight Trench Coat',       'Marks & Spencer','M',  480.00, 'Tan trench with belt. Classic.',                          'Outerwear',    'Good',      'available'],
    [7, 'Corset Top',                    'Pretty Little Thing','XS',95.00,'Black satin corset. Worn once.',                         'Tops',         'Like New',  'available'],
    [7, 'Wide Brim Hat',                 'Cotton On',     'One Size',80.00,'Straw sun hat. Minor shape issue at brim.',             'Accessories',  'Good',      'available'],
    [8, 'Maxi Wrap Dress',               'Faithfull',     'S',   720.00, 'Floral resort dress. Holiday wear.',                     'Dresses',      'Very Good', 'available'],
    [8, 'Chelsea Boots',                 'Aldo',          '8',   350.00, 'Brown suede Chelsea boots. Some salt marks.',             'Shoes',        'Good',      'sold'],
    [8, 'Cashmere Scarf',                'Scottish Cashmere','One Size',450.00,'Grey cashmere. Luxuriously soft.',                 'Accessories',  'Like New',  'available'],
    [9, 'Gym Leggings',                  'Nike',          'M',   220.00, 'Black high-waist 7/8 length. Washed 10 times.',           'Activewear',   'Good',      'available'],
    [9, 'Running Jacket',                'Adidas',        'L',   310.00, 'Reflective windbreaker. No tears.',                       'Activewear',   'Very Good', 'available'],
    [9, 'Linen Shirt',                   'Country Road',  'M',   195.00, 'White linen button-down. Summer essential.',              'Tops',         'Good',      'available'],
    [10,'Cargo Trousers',                'G-Star Raw',    '32',  390.00, 'Olive green cargo pants. Multiple pockets.',              'Bottoms',      'Good',      'available'],
    [10,'Graphic Band Tee',              'H&M',           'L',    85.00, 'Vintage-style rock band print. Faded.',                   'Tops',         'Good',      'available'],
    [10,'Structured Handbag',            'Forever New',   'One Size',260.00,'Nude faux-leather structured bag. Scuff on base.',   'Accessories',  'Good',      'available'],
];

$insertedClothes = 0;
$stmt = $conn->prepare(
    "INSERT INTO tblClothes (userID, title, brand, size, price, description, category, itemCondition, status)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
);

foreach ($clothes as $c) {
    $stmt->bind_param("isssdssss", $c[0], $c[1], $c[2], $c[3], $c[4], $c[5], $c[6], $c[7], $c[8]);
    if ($stmt->execute()) {
        $insertedClothes++;
    } else {
        $log[] = ['err', "❌ Failed inserting clothes item: " . $stmt->error];
    }
}
$stmt->close();
$log[] = ['ok', "✅ tblClothes — inserted $insertedClothes / 30 rows"];

// ════════════════════════════════════════════════════════════════════════════
//  STEP 6 — Insert 30 rows into tblAorder
// ════════════════════════════════════════════════════════════════════════════
$log[] = ['head', 'STEP 6 — Seeding tblAorder (30 rows)'];

$orders = [
    [2,  1,  1, 450.00,  'confirmed',  'TYPE: residential' . "\n" . 'STREET: 45 Berea Rd' . "\n" . 'CITY: Johannesburg' . "\n" . 'CODE: 2001'],
    [3,  2,  1, 320.00,  'shipped',    'TYPE: work' . "\n" . 'STREET: 12 Long Street' . "\n" . 'CITY: Cape Town' . "\n" . 'CODE: 8001'],
    [4,  3,  1, 780.00,  'delivered',  'TYPE: residential' . "\n" . 'STREET: 7 Umgeni Rd' . "\n" . 'CITY: Durban' . "\n" . 'CODE: 4001'],
    [5,  4,  1, 180.00,  'delivered',  'TYPE: residential' . "\n" . 'STREET: 3 Church St' . "\n" . 'CITY: Pretoria' . "\n" . 'CODE: 0028'],
    [2,  5,  1, 150.00,  'pending',    'TYPE: residential' . "\n" . 'STREET: 45 Berea Rd' . "\n" . 'CITY: Johannesburg' . "\n" . 'CODE: 2001'],
    [6,  6,  1, 560.00,  'confirmed',  'TYPE: work' . "\n" . 'STREET: 22 Main Rd' . "\n" . 'CITY: Johannesburg' . "\n" . 'CODE: 2001'],
    [7,  7,  1, 290.00,  'shipped',    'TYPE: residential' . "\n" . 'STREET: 10 Oak Ave' . "\n" . 'CITY: Bloemfontein' . "\n" . 'CODE: 9301'],
    [8,  8,  1, 120.00,  'delivered',  'TYPE: residential' . "\n" . 'STREET: 99 Voortrekker Rd' . "\n" . 'CITY: Cape Town' . "\n" . 'CODE: 7500'],
    [9,  9,  1, 490.00,  'confirmed',  'TYPE: work' . "\n" . 'STREET: 5 Empire Blvd' . "\n" . 'CITY: Sandton' . "\n" . 'CODE: 2196'],
    [10, 10, 1, 160.00,  'pending',    'TYPE: residential' . "\n" . 'STREET: 17 Maple St' . "\n" . 'CITY: Port Elizabeth' . "\n" . 'CODE: 6001'],
    [11, 11, 1, 420.00,  'shipped',    'TYPE: residential' . "\n" . 'STREET: 2 Rose St' . "\n" . 'CITY: George' . "\n" . 'CODE: 6530'],
    [12, 12, 1, 380.00,  'delivered',  'TYPE: work' . "\n" . 'STREET: 88 West St' . "\n" . 'CITY: Durban' . "\n" . 'CODE: 4001'],
    [13, 13, 1, 340.00,  'confirmed',  'TYPE: residential' . "\n" . 'STREET: 33 Kerk St' . "\n" . 'CITY: Pretoria' . "\n" . 'CODE: 0001'],
    [14, 14, 1, 280.00,  'pending',    'TYPE: residential' . "\n" . 'STREET: 71 Jan Smuts Ave' . "\n" . 'CITY: Johannesburg' . "\n" . 'CODE: 2193'],
    [15, 15, 1, 190.00,  'cancelled',  'TYPE: residential' . "\n" . 'STREET: 6 Beach Rd' . "\n" . 'CITY: Muizenberg' . "\n" . 'CODE: 7950'],
    [2,  16, 1, 650.00,  'confirmed',  'TYPE: work' . "\n" . 'STREET: 45 Berea Rd' . "\n" . 'CITY: Johannesburg' . "\n" . 'CODE: 2001'],
    [3,  17, 1, 520.00,  'delivered',  'TYPE: residential' . "\n" . 'STREET: 12 Long St' . "\n" . 'CITY: Cape Town' . "\n" . 'CODE: 8001'],
    [4,  18, 1, 210.00,  'shipped',    'TYPE: residential' . "\n" . 'STREET: 7 Umgeni Rd' . "\n" . 'CITY: Durban' . "\n" . 'CODE: 4001'],
    [5,  19, 1, 480.00,  'pending',    'TYPE: work' . "\n" . 'STREET: 3 Church St' . "\n" . 'CITY: Pretoria' . "\n" . 'CODE: 0028'],
    [6,  20, 1,  95.00,  'confirmed',  'TYPE: residential' . "\n" . 'STREET: 22 Main Rd' . "\n" . 'CITY: Johannesburg' . "\n" . 'CODE: 2001'],
    [7,  21, 1,  80.00,  'delivered',  'TYPE: residential' . "\n" . 'STREET: 10 Oak Ave' . "\n" . 'CITY: Bloemfontein' . "\n" . 'CODE: 9301'],
    [8,  22, 1, 720.00,  'shipped',    'TYPE: residential' . "\n" . 'STREET: 99 Voortrekker Rd' . "\n" . 'CITY: Cape Town' . "\n" . 'CODE: 7500'],
    [9,  24, 1, 450.00,  'confirmed',  'TYPE: work' . "\n" . 'STREET: 5 Empire Blvd' . "\n" . 'CITY: Sandton' . "\n" . 'CODE: 2196'],
    [10, 25, 1, 220.00,  'pending',    'TYPE: residential' . "\n" . 'STREET: 17 Maple St' . "\n" . 'CITY: Port Elizabeth' . "\n" . 'CODE: 6001'],
    [11, 26, 1, 310.00,  'delivered',  'TYPE: residential' . "\n" . 'STREET: 2 Rose St' . "\n" . 'CITY: George' . "\n" . 'CODE: 6530'],
    [12, 27, 1, 195.00,  'confirmed',  'TYPE: work' . "\n" . 'STREET: 88 West St' . "\n" . 'CITY: Durban' . "\n" . 'CODE: 4001'],
    [13, 28, 1, 390.00,  'shipped',    'TYPE: residential' . "\n" . 'STREET: 33 Kerk St' . "\n" . 'CITY: Pretoria' . "\n" . 'CODE: 0001'],
    [14, 29, 1,  85.00,  'pending',    'TYPE: residential' . "\n" . 'STREET: 71 Jan Smuts Ave' . "\n" . 'CITY: Johannesburg' . "\n" . 'CODE: 2193'],
    [15, 30, 1, 260.00,  'cancelled',  'TYPE: residential' . "\n" . 'STREET: 6 Beach Rd' . "\n" . 'CITY: Muizenberg' . "\n" . 'CODE: 7950'],
    [2,  1,  1, 450.00,  'delivered',  'TYPE: work' . "\n" . 'STREET: 100 Commissioner St' . "\n" . 'CITY: Johannesburg' . "\n" . 'CODE: 2001'],
];

$insertedOrders = 0;
$stmt = $conn->prepare(
    "INSERT INTO tblAorder (buyerID, clothesID, quantity, totalPrice, status, deliveryAddress)
     VALUES (?, ?, ?, ?, ?, ?)"
);

foreach ($orders as $o) {
    $stmt->bind_param("iiidss", $o[0], $o[1], $o[2], $o[3], $o[4], $o[5]);
    if ($stmt->execute()) {
        $insertedOrders++;
    } else {
        $log[] = ['err', "❌ Failed inserting order: " . $stmt->error];
    }
}
$stmt->close();
$log[] = ['ok', "✅ tblAorder — inserted $insertedOrders / 30 rows"];

// ════════════════════════════════════════════════════════════════════════════
//  STEP 7 — Summary
// ════════════════════════════════════════════════════════════════════════════
$log[] = ['head', 'DONE'];
$log[] = ['ok',   "tblUser:    $insertedUsers rows"];
$log[] = ['ok',   "tblAdmin:   $insertedAdmins rows"];
$log[] = ['ok',   "tblClothes: $insertedClothes rows"];
$log[] = ['ok',   "tblAorder:  $insertedOrders rows"];
$log[] = ['ok',   "Login as admin: username=superadmin / password=Admin1234!"];
$log[] = ['ok',   "Login as user:  username=thabom / password=Password1!"];

// ════════════════════════════════════════════════════════════════════════════
//  Render the output page
// ════════════════════════════════════════════════════════════════════════════
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <title>loadClothingStore.php — Pastimes</title>
    <link rel="preconnect" href="https://fonts.googleapis.com"/>
    <link href="https://fonts.googleapis.com/css2?family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet"/>
    <style>
        body  { font-family: 'Jost', sans-serif; background: #1C130B; color: #FAF6EF; padding: 40px; margin: 0; }
        h1    { font-size: 1.3rem; color: #D4A855; margin-bottom: 6px; }
        p     { font-size: 13px; color: rgba(250,246,239,0.5); margin-bottom: 24px; }
        .log  { font-family: monospace; font-size: 13px; line-height: 2; }
        .ok   { color: #6FCF97; }
        .err  { color: #EB5757; }
        .head { color: #D4A855; font-weight: 600; font-size: 11px; letter-spacing: 0.2em;
                text-transform: uppercase; margin-top: 16px; }
        .links{ margin-top: 32px; display: flex; gap: 16px; }
        a     { display: inline-block; padding: 10px 22px; border-radius: 6px;
                font-size: 11px; font-weight: 600; letter-spacing: 0.14em;
                text-transform: uppercase; text-decoration: none; }
        .a-primary { background: #BF8B30; color: #fff; }
        .a-outline  { border: 1.5px solid rgba(250,246,239,0.3); color: #FAF6EF; }
    </style>
</head>
<body>
    <h1>loadClothingStore.php</h1>
    <p>Dropped all tables → recreated structure → inserted seed data. Run this again any time to reset.</p>
    <div class="log">
        <?php foreach ($log as $entry): ?>
            <div class="<?= $entry[0] ?>">
                <?= htmlspecialchars($entry[1]) ?>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="links">
        <a href="login.php" class="a-primary">→ Go to Login</a>
        <a href="adminLogin.php" class="a-outline">→ Admin Login</a>
    </div>
</body>
</html>
