<?php
require_once 'admin/config/db.php';
$database = new Database();
$db = $database->getConnection();

// Track visitor
@include_once 'track_visitor.php';

// Fetch Settings
$settings = array();
try {
    $stmt = $db->query("SELECT * FROM settings WHERE id = 1");
    $settings = $stmt->fetch();
} catch (PDOException $e) {
}

// Pagination
$limit = 12;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
if ($page < 1)
    $page = 1;
$start = ($page - 1) * $limit;

// Fetch Filter Options
$brands = array();
$car_types = array();

try {
    // Brands
    $sql_brands = "SELECT DISTINCT brand, COUNT(*) as count FROM used_cars WHERE brand != '' AND is_active = 1 GROUP BY brand ORDER BY brand";
    $stmt_brands = $db->query($sql_brands);
    $brands = $stmt_brands->fetchAll();

    // Car Types
    $sql_types = "SELECT DISTINCT car_type, COUNT(*) as count FROM used_cars WHERE car_type != '' AND is_active = 1 GROUP BY car_type ORDER BY car_type";
    $stmt_types = $db->query($sql_types);
    $car_types = $stmt_types->fetchAll();
} catch (PDOException $e) {
}

// Build Filter Query
$where_clauses = array("is_active = 1");
$params = array();

if (isset($_GET['brands']) && is_array($_GET['brands'])) {
    $brand_placeholders = array();
    foreach ($_GET['brands'] as $key => $brand) {
        $placeholder = ":brand_" . $key;
        $brand_placeholders[] = $placeholder;
        $params[$placeholder] = $brand;
    }
    if (!empty($brand_placeholders)) {
        $where_clauses[] = "brand IN (" . implode(', ', $brand_placeholders) . ")";
    }
}

if (isset($_GET['types']) && is_array($_GET['types'])) {
    $type_placeholders = array();
    foreach ($_GET['types'] as $key => $type) {
        $placeholder = ":type_" . $key;
        $type_placeholders[] = $placeholder;
        $params[$placeholder] = $type;
    }
    if (!empty($type_placeholders)) {
        $where_clauses[] = "car_type IN (" . implode(', ', $type_placeholders) . ")";
    }
}

$where_sql = implode(' AND ', $where_clauses);

// Fetch Cars
$cars = array();
$total_cars = 0;
$total_pages = 0;

try {
    // Count total cars
    $count_sql = "SELECT COUNT(*) FROM used_cars WHERE $where_sql";
    $stmt_count = $db->prepare($count_sql);
    foreach ($params as $key => $value) {
        $stmt_count->bindValue($key, $value);
    }
    $stmt_count->execute();
    $total_cars = $stmt_count->fetchColumn();
    $total_pages = ceil($total_cars / $limit);

    // Fetch cars for current page
    $sql = "SELECT * FROM used_cars WHERE $where_sql ORDER BY is_featured DESC, created_at DESC LIMIT :start, :limit";
    $stmt = $db->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':start', $start, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    $cars = $stmt->fetchAll();
} catch (PDOException $e) {
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รถสวยพร้อมขาย - MIDA LEASING</title>
    <meta name="description" content="รถสวยพร้อมขาย ราคาคุ้มค่า ผ่านการตรวจสภาพ พร้อมจัดไฟแนนซ์ จากไมด้าลิสซิ่ง">

    <!-- Favicon -->
    <?php if (!empty($settings['site_favicon'])): ?>
        <link rel="icon" href="<?php echo $settings['site_favicon']; ?>" type="image/x-icon">
    <?php else: ?>
        <link rel="icon" href="favicon.ico" type="image/x-icon">
    <?php endif; ?>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://www.midaleasing.com/used_cars.php">
    <meta property="og:title" content="รถสวยพร้อมขาย - MIDA LEASING">
    <meta property="og:description" content="รถสวยพร้อมขาย ราคาคุ้มค่า ผ่านการตรวจสภาพ พร้อมจัดไฟแนนซ์ จากไมด้าลิสซิ่ง">
    <meta property="og:image" content="https://www.midaleasing.com/img/mida_logo_5.png">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="https://www.midaleasing.com/used_cars.php">
    <meta property="twitter:title" content="รถสวยพร้อมขาย - MIDA LEASING">
    <meta property="twitter:description"
        content="รถสวยพร้อมขาย ราคาคุ้มค่า ผ่านการตรวจสภาพ พร้อมจัดไฟแนนซ์ จากไมด้าลิสซิ่ง">
    <meta property="twitter:image" content="https://www.midaleasing.com/img/mida_logo_5.png">

    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/style.css">

    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        .used-cars-hero {
            position: relative;
            overflow: hidden;
            background:
                radial-gradient(circle at 18% 12%, rgba(255, 199, 44, 0.26), transparent 28%),
                linear-gradient(108deg, #fffaf0 0%, #eef5ff 58%, #1f5fb8 58%, #17488f 100%);
            color: white;
            padding: 118px 0 58px;
        }

        .used-cars-hero::after {
            content: "";
            position: absolute;
            inset: auto -8% -42% 44%;
            height: 58%;
            background: rgba(255, 255, 255, 0.12);
            transform: skewX(-12deg);
            pointer-events: none;
        }

        .used-cars-hero-grid {
            position: relative;
            z-index: 1;
            display: grid;
            grid-template-columns: minmax(0, 0.95fr) minmax(340px, 0.72fr);
            gap: 42px;
            align-items: center;
        }

        .used-cars-hero-copy {
            max-width: 570px;
        }

        .used-cars-kicker {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 14px;
            color: var(--primary-blue);
            font-size: 0.88rem;
            font-weight: 900;
            letter-spacing: 0.14em;
            text-transform: uppercase;
        }

        .used-cars-kicker::before {
            content: "";
            width: 34px;
            height: 3px;
            border-radius: 999px;
            background: var(--accent-gold);
        }

        .used-cars-hero h1 {
            max-width: 560px;
            margin: 0 0 14px;
            color: var(--primary-blue);
            font-size: clamp(2.1rem, 4.4vw, 3.75rem);
            line-height: 1.02;
            letter-spacing: -0.05em;
        }

        .used-cars-hero p {
            max-width: 520px;
            margin: 0;
            color: #56677d;
            font-size: 1.06rem;
            line-height: 1.75;
        }

        .used-cars-hero-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 13px;
            margin-top: 24px;
        }

        .used-cars-hero-actions a,
        .used-car-guide-card a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: fit-content;
            min-height: 44px;
            padding: 0 18px;
            border-radius: 999px;
            background: var(--primary-blue);
            color: #fff;
            font-weight: 800;
            text-decoration: none;
            transition: transform 0.18s ease, box-shadow 0.18s ease;
        }

        .used-cars-hero-actions a:hover,
        .used-car-guide-card a:hover {
            transform: translateY(-2px);
        }

        .used-cars-hero-actions .is-gold,
        .used-car-guide-card a.is-gold {
            background: linear-gradient(135deg, var(--accent-gold) 0%, #ffe07a 100%);
            color: #0f2d5c;
            box-shadow: 0 12px 26px rgba(255, 199, 44, 0.24);
        }

        .used-cars-hero-panel {
            position: relative;
            z-index: 1;
            padding: 30px;
            border-radius: 30px;
            background: rgba(255, 255, 255, 0.92);
            border: 1px solid rgba(255, 255, 255, 0.45);
            box-shadow: 0 28px 70px rgba(16, 48, 95, 0.24);
            backdrop-filter: blur(14px);
        }

        .used-cars-hero-panel h2 {
            margin: 0 0 18px;
            color: var(--primary-blue);
            font-size: 1.45rem;
        }

        .used-car-type-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
        }

        .used-car-type-item {
            padding: 18px 12px;
            border: 1px solid #dfe8f3;
            border-radius: 20px;
            background: #fff;
            color: #183153;
            text-align: center;
            font-weight: 800;
        }

        .used-car-type-item i {
            display: block;
            margin-bottom: 9px;
            color: var(--primary-blue);
            font-size: 1.55rem;
        }

        .used-car-guide-section {
            padding: 34px 0 0;
            background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
        }

        .used-car-guide-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
        }

        .used-car-guide-card {
            display: flex;
            flex-direction: column;
            gap: 12px;
            min-height: 190px;
            padding: 22px;
            border-radius: 24px;
            background: #ffffff;
            border: 1px solid rgba(23, 69, 143, 0.1);
            box-shadow: 0 16px 36px rgba(18, 72, 148, 0.08);
        }

        .used-car-guide-card i {
            width: 46px;
            height: 46px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 16px;
            background: #fff4cf;
            color: #b98600;
            font-size: 1.25rem;
        }

        .used-car-guide-card h3 {
            margin: 0;
            color: #0f2d5c;
            font-size: 1.08rem;
        }

        .used-car-guide-card p {
            margin: 0;
            color: #617187;
            line-height: 1.65;
            font-size: 0.95rem;
        }

        .layout-grid {
            display: grid;
            grid-template-columns: 280px 1fr;
            gap: 30px;
            margin-top: 40px;
        }

        .filter-sidebar {
            background: white;
            padding: 25px;
            border-radius: 24px;
            box-shadow: 0 18px 42px rgba(23, 69, 143, 0.08);
            border: 1px solid rgba(23, 69, 143, 0.09);
            height: fit-content;
            position: sticky;
            top: 96px;
        }

        .filter-group {
            margin-bottom: 25px;
            border-bottom: 1px solid #eee;
            padding-bottom: 15px;
        }

        .filter-group:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }

        .filter-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 15px;
            display: block;
        }

        .filter-checkbox {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
            cursor: pointer;
            color: #666;
        }

        .filter-checkbox input {
            margin-right: 10px;
            width: 16px;
            height: 16px;
        }

        .car-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
        }

        .car-card {
            background: white;
            border-radius: 22px;
            overflow: hidden;
            box-shadow: 0 16px 36px rgba(23, 69, 143, 0.08);
            border: 1px solid rgba(23, 69, 143, 0.09);
            transition: all 0.3s;
        }

        .car-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 22px 44px rgba(23, 69, 143, 0.14);
        }

        .car-img {
            height: 200px;
            background-color: #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #94a3b8;
            font-size: 3rem;
            position: relative;
        }

        .car-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: #e74c3c;
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .featured-badge {
            position: absolute;
            top: 10px;
            left: 10px;
            background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
            color: #000;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .car-info {
            padding: 20px;
        }

        .car-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 8px;
            color: #333;
        }

        .car-details {
            font-size: 0.9rem;
            color: #666;
            margin-bottom: 15px;
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .car-price {
            color: var(--primary-blue);
            font-size: 1.3rem;
            font-weight: 700;
        }

        .car-price-original {
            text-decoration: line-through;
            color: #999;
            font-size: 0.9rem;
            margin-left: 10px;
        }

        .pagination {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 40px;
        }

        .pagination a,
        .pagination span {
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            text-decoration: none;
            color: #333;
            transition: all 0.2s;
        }

        .pagination a:hover {
            background: var(--primary-blue);
            color: white;
            border-color: var(--primary-blue);
        }

        .pagination .active {
            background: var(--primary-blue);
            color: white;
            border-color: var(--primary-blue);
        }

        @media (max-width: 992px) {
            .used-cars-hero {
                padding: 104px 0 44px;
                background:
                    radial-gradient(circle at 18% 12%, rgba(255, 199, 44, 0.26), transparent 34%),
                    linear-gradient(160deg, #fffaf0 0%, #eef5ff 66%, #e5f0ff 100%);
            }

            .used-cars-hero-grid,
            .used-car-guide-grid {
                grid-template-columns: 1fr;
            }

            .layout-grid {
                grid-template-columns: 1fr;
            }

            .filter-sidebar {
                position: static;
            }
        }

        @media (max-width: 640px) {
            .used-cars-hero h1 {
                font-size: 2.45rem;
            }

            .used-car-type-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: 8px;
            }

            .used-car-type-item {
                padding: 14px 8px;
                font-size: 0.86rem;
                border-radius: 16px;
            }

            .used-car-type-item i {
                font-size: 1.25rem;
            }

            .used-cars-hero-panel {
                padding: 22px;
            }
        }
    </style>
</head>

<body>

    <!-- Header -->
    <?php $active_page = 'used_cars';
    include 'includes/nav.php'; ?>

    <!-- Page Header -->
    <section class="used-cars-hero">
        <div class="container">
            <div class="used-cars-hero-grid">
                <div class="used-cars-hero-copy">
                    <span class="used-cars-kicker">MIDA USED CARS</span>
                    <h1>รถพร้อมขายจากไมด้า</h1>
                    <p>เลือกรถมือสองสภาพดี ดูราคา รายละเอียด และสอบถามเจ้าหน้าที่ได้ง่าย เหมาะสำหรับลูกค้าที่กำลังมองหารถใช้งานจริง</p>
                    <div class="used-cars-hero-actions">
                        <a href="#used-car-listing" class="is-gold"><i class="fa-solid fa-car-side"></i> ดูรายการรถ</a>
                        <a href="#used-car-guide"><i class="fa-solid fa-headset"></i> สอบถามเจ้าหน้าที่</a>
                    </div>
                </div>

                <aside class="used-cars-hero-panel">
                    <h2>เลือกดูรถที่สนใจ</h2>
                    <div class="used-car-type-grid">
                        <div class="used-car-type-item">
                            <i class="fa-solid fa-car"></i>
                            รถเก๋ง
                        </div>
                        <div class="used-car-type-item">
                            <i class="fa-solid fa-truck-pickup"></i>
                            รถกระบะ
                        </div>
                        <div class="used-car-type-item">
                            <i class="fa-solid fa-clipboard-check"></i>
                            พร้อมขาย
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </section>

    <section class="used-car-guide-section" id="used-car-guide">
        <div class="container">
            <div class="used-car-guide-grid">
                <div class="used-car-guide-card">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <h3>ค้นหารถที่ต้องการ</h3>
                    <p>เลือกดูตามยี่ห้อหรือประเภทรถ เพื่อเจอรถที่ตรงกับการใช้งานของคุณเร็วขึ้น</p>
                    <a href="#used-car-listing" class="is-gold">ค้นหารายการรถ</a>
                </div>
                <div class="used-car-guide-card">
                    <i class="fa-solid fa-file-lines"></i>
                    <h3>ดูรายละเอียดก่อนตัดสินใจ</h3>
                    <p>ดูรูป ราคา ปีรถ เลขไมล์ และข้อมูลเบื้องต้นของรถแต่ละคันก่อนสอบถามเพิ่มเติม</p>
                    <a href="#used-car-listing">ดูรถทั้งหมด</a>
                </div>
                <div class="used-car-guide-card">
                    <i class="fa-solid fa-comments"></i>
                    <h3>ให้เจ้าหน้าที่ช่วยดูแล</h3>
                    <p>สนใจรถคันไหน สามารถติดต่อเจ้าหน้าที่เพื่อสอบถามรายละเอียดและนัดหมายได้</p>
                    <a href="contact_us.php">ติดต่อเรา</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <div class="section" id="used-car-listing" style="padding-top: 0; background-color: #f8f9fa; min-height: 80vh;">
        <div class="container">
            <div class="layout-grid">

                <!-- Sidebar Filter -->
                <aside class="filter-sidebar">
                    <form action="" method="GET" id="filterForm">
                        <h3 style="margin-bottom: 20px; font-size: 1.2rem; color: #0f2d5c;">
                            <i class="fa-solid fa-filter"></i> ค้นหารถที่สนใจ
                        </h3>

                        <!-- Brands Filter -->
                        <div class="filter-group">
                            <label class="filter-title">ยี่ห้อรถ</label>
                            <?php if (count($brands) > 0): ?>
                                <?php foreach ($brands as $b): ?>
                                    <?php $checked = (isset($_GET['brands']) && in_array($b['brand'], $_GET['brands'])) ? 'checked' : ''; ?>
                                    <label class="filter-checkbox">
                                        <input type="checkbox" name="brands[]"
                                            value="<?php echo htmlspecialchars($b['brand']); ?>" <?php echo $checked; ?>>
                                        <?php echo htmlspecialchars($b['brand']); ?> (<?php echo $b['count']; ?>)
                                    </label>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p style="color: #999; font-size: 0.9rem;">ไม่มียี่ห้อรถ</p>
                            <?php endif; ?>
                        </div>

                        <!-- Car Types Filter -->
                        <div class="filter-group">
                            <label class="filter-title">ประเภทรถ</label>
                            <?php if (count($car_types) > 0): ?>
                                <?php foreach ($car_types as $t): ?>
                                    <?php $checked = (isset($_GET['types']) && in_array($t['car_type'], $_GET['types'])) ? 'checked' : ''; ?>
                                    <label class="filter-checkbox">
                                        <input type="checkbox" name="types[]"
                                            value="<?php echo htmlspecialchars($t['car_type']); ?>" <?php echo $checked; ?>>
                                        <?php echo htmlspecialchars($t['car_type']); ?> (<?php echo $t['count']; ?>)
                                    </label>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p style="color: #999; font-size: 0.9rem;">ไม่มีข้อมูลประเภทรถ</p>
                            <?php endif; ?>
                        </div>

                        <button type="submit" class="btn btn-primary" style="width: 100%;">
                            <i class="fa-solid fa-search"></i> ค้นหารถ
                        </button>
                        <a href="used_cars.php" class="btn"
                            style="width: 100%; margin-top: 10px; display: block; text-align: center; border: 1px solid #eee; color: #666;">
                            <i class="fa-solid fa-times"></i> ล้างค่า
                        </a>
                    </form>
                </aside>

                <!-- Car Grid -->
                <div>
                    <div
                        style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                        <h2 style="font-size: 1.5rem; margin: 0; color: #0f2d5c;">รายการรถพร้อมขาย
                            <span style="font-size: 1rem; color: #666; font-weight: 400;">(<?php echo $total_cars; ?>
                                รายการ)</span>
                        </h2>
                    </div>

                    <div class="car-grid">
                        <?php if (count($cars) > 0): ?>
                            <?php foreach ($cars as $car): ?>
                                <div class="car-card">
                                    <div class="car-img">
                                        <?php if (!empty($car['image_path'])): ?>
                                            <img src="<?php echo htmlspecialchars($car['image_path']); ?>"
                                                alt="<?php echo htmlspecialchars($car['title']); ?>"
                                                style="width: 100%; height: 100%; object-fit: cover;">
                                        <?php else: ?>
                                            <i class="fa-solid fa-car-side"></i>
                                        <?php endif; ?>
                                        <?php if (!empty($car['price_original'])): ?>
                                            <span class="car-badge">ลดราคา</span>
                                        <?php endif; ?>
                                        <?php if ($car['is_featured']): ?>
                                            <span class="featured-badge"><i class="fa-solid fa-star"></i> แนะนำ</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="car-info">
                                        <h3 class="car-title"><?php echo htmlspecialchars($car['title']); ?></h3>
                                        <div class="car-details">
                                            <?php if (!empty($car['car_year'])): ?>
                                                <span><i class="fa-solid fa-calendar"></i>
                                                    <?php echo htmlspecialchars($car['car_year']); ?></span>
                                            <?php endif; ?>
                                            <?php if (!empty($car['mileage'])): ?>
                                                <span><i class="fa-solid fa-gauge"></i>
                                                    <?php echo htmlspecialchars($car['mileage']); ?></span>
                                            <?php endif; ?>
                                            <?php if (!empty($car['transmission'])): ?>
                                                <span><i class="fa-solid fa-gear"></i>
                                                    <?php echo htmlspecialchars($car['transmission']); ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <div style="display: flex; justify-content: space-between; align-items: end;">
                                            <div>
                                                <div style="font-size: 0.8rem; color: #888;">ราคาขาย</div>
                                                <div class="car-price">
                                                    <?php echo htmlspecialchars($car['price']); ?>
                                                    <?php if (!empty($car['price_original'])): ?>
                                                        <span
                                                            class="car-price-original"><?php echo htmlspecialchars($car['price_original']); ?></span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <a href="used_car_detail.php?id=<?php echo $car['id']; ?>" class="btn btn-accent"
                                                style="padding: 8px 15px; font-size: 0.9rem;">ดูรถคันนี้</a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div
                                style="grid-column: 1/-1; text-align: center; padding: 60px; background: white; border-radius: 12px;">
                                <i class="fa-solid fa-car-side"
                                    style="font-size: 4rem; color: #ccc; margin-bottom: 20px;"></i>
                                <p style="color: #888; font-size: 1.1rem;">ยังไม่มีรถในขณะนี้</p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <div class="pagination">
                            <?php if ($page > 1): ?>
                                <a href="?page=<?php echo $page - 1; ?>"><i class="fa-solid fa-chevron-left"></i></a>
                            <?php endif; ?>

                            <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                <?php if ($i == $page): ?>
                                    <span class="active"><?php echo $i; ?></span>
                                <?php else: ?>
                                    <a href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                <?php endif; ?>
                            <?php endfor; ?>

                            <?php if ($page < $total_pages): ?>
                                <a href="?page=<?php echo $page + 1; ?>"><i class="fa-solid fa-chevron-right"></i></a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer id="footer">
        <div class="container">
            <div class="footer-content">
                <div>
                    <div class="footer-logo">MIDA LEASING</div>
                    <p style="color: #ccc; margin-bottom: 10px;">บริษัท ไมด้าลิสซิ่ง จำกัด (มหาชน)</p>
                    <p style="color: #ccc; margin-bottom: 10px; font-size: 1rem;">
                        <?php echo nl2br(htmlspecialchars(isset($settings['site_address']) ? $settings['site_address'] : '')); ?>
                    </p>
                    <p style="color: #ccc; margin-bottom: 20px; font-size: 1rem;">
                        <i class="fa-solid fa-phone" style="margin-right: 10px;"></i>
                        <?php echo htmlspecialchars(isset($settings['site_phone']) ? $settings['site_phone'] : ''); ?>
                    </p>
                    <div style="display: flex; gap: 15px;">
                        <a href="<?php echo htmlspecialchars(isset($settings['site_facebook']) ? $settings['site_facebook'] : ''); ?>" target="_blank"
                            style="text-decoration: none;">
                            <i class="fa-brands fa-facebook" style="font-size: 2rem; color: #1877F2;"></i>
                        </a>
                        <a href="<?php echo htmlspecialchars(isset($settings['site_line']) ? $settings['site_line'] : ''); ?>" target="_blank"
                            style="text-decoration: none;">
                            <i class="fa-brands fa-line" style="font-size: 2rem; color: #00B900;"></i>
                        </a>
                    </div>
                </div>

                <div class="footer-links">
                    <h4>บริการของเรา</h4>
                    <ul>
                        <li><a href="service_hire_purchase.php">สินเชื่อเช่าซื้อ</a></li>
                        <li><a href="service_title_loan.php">สินเชื่อจำนำทะเบียน</a></li>
                        <li><a href="service_personal_loan.php">สินเชื่อส่วนบุคคล</a></li>
                        <li><a href="service_insurance.php">ต่อภาษีและประกันภัย</a></li>
                    </ul>
                </div>

                <div class="footer-links">
                    <h4>นักลงทุนสัมพันธ์</h4>
                    <ul>
                        <li><a href="investor_business.php">วิสัยทัศน์และพันธกิจ</a></li>
                        <li><a href="investor_financial.php">ข้อมูลทางการเงิน</a></li>
                        <li><a href="investor_publications.php">เอกสารเผยแพร่</a></li>
                    </ul>
                </div>

                <div class="footer-links">
                    <h4>ติดต่อเรา</h4>
                    <ul>
                        <li><a href="contact_branches.php">แผนที่สาขา</a></li>
                        <li><a href="contact_career.php">ร่วมงานกับเรา</a></li>
                        <li><a href="contact_us.php">ติดต่อสอบถาม</a></li>
                    </ul>
                </div>
            </div>

            <div class="copyright">
                &copy; 2026 Mida Leasing Public Company Limited. All Rights Reserved.
                <br>
                <div style="margin-top: 10px;">
                    <a href="privacy_policy.php"
                        style="color: #888; text-decoration: none; margin: 0 10px;">นโยบายความเป็นส่วนตัว</a> |
                    <a href="cookie_policy.php"
                        style="color: #888; text-decoration: none; margin: 0 10px;">นโยบายเกี่ยวกับ cookie</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- JS -->
    <script src="assets/js/main.js"></script>

</body>

</html>
