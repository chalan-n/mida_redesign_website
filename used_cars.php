<?php
require_once 'admin/config/db.php';
$database = new Database();
$db = $database->getConnection();

// Track visitor
@include_once 'track_visitor.php';

// Fetch Settings
$settings = [];
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
$brands = [];
$car_types = [];

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
$where_clauses = ["is_active = 1"];
$params = [];

if (isset($_GET['brands']) && is_array($_GET['brands'])) {
    $brand_placeholders = [];
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
    $type_placeholders = [];
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
$cars = [];
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
        .page-header {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            color: white;
            padding: 140px 0 60px;
            text-align: center;
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
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            border: 1px solid #eee;
            height: fit-content;
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
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 3px 15px rgba(0, 0, 0, 0.08);
            border: 1px solid #eee;
            transition: all 0.3s;
        }

        .car-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
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
            .layout-grid {
                grid-template-columns: 1fr;
            }

            .filter-sidebar {
                display: none;
            }
        }
    </style>
</head>

<body>

    <!-- Header -->
    <?php $active_page = 'used_cars';
    include 'includes/nav.php'; ?>

    <!-- Page Header -->
    <div class="page-header">
        <div class="container">
            <h1 style="font-size: 2.5rem; margin-bottom: 10px; font-weight: 700; color: #fec435;">รถสวยพร้อมขาย</h1>
            <p style="opacity: 0.8; font-size: 1.1rem;">รถสวย สภาพเยี่ยม ผ่านการตรวจสภาพ พร้อมจัดไฟแนนซ์</p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="section" style="padding-top: 0; background-color: #f8f9fa; min-height: 80vh;">
        <div class="container">
            <div class="layout-grid">

                <!-- Sidebar Filter -->
                <aside class="filter-sidebar">
                    <form action="" method="GET" id="filterForm">
                        <h3 style="margin-bottom: 20px; font-size: 1.2rem;">
                            <i class="fa-solid fa-filter"></i> กรองข้อมูล
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
                            <i class="fa-solid fa-search"></i> ค้นหา
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
                        <h2 style="font-size: 1.5rem; margin: 0;">รถทั้งหมด
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
                                                <div style="font-size: 0.8rem; color: #888;">ราคา</div>
                                                <div class="car-price">
                                                    <?php echo htmlspecialchars($car['price']); ?>
                                                    <?php if (!empty($car['price_original'])): ?>
                                                        <span
                                                            class="car-price-original"><?php echo htmlspecialchars($car['price_original']); ?></span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <a href="used_car_detail.php?id=<?php echo $car['id']; ?>" class="btn btn-accent"
                                                style="padding: 8px 15px; font-size: 0.9rem;">ดูรายละเอียด</a>
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
                        <?php echo nl2br(htmlspecialchars($settings['site_address'] ?? '')); ?>
                    </p>
                    <p style="color: #ccc; margin-bottom: 20px; font-size: 1rem;">
                        <i class="fa-solid fa-phone" style="margin-right: 10px;"></i>
                        <?php echo htmlspecialchars($settings['site_phone'] ?? ''); ?>
                    </p>
                    <div style="display: flex; gap: 15px;">
                        <a href="<?php echo htmlspecialchars($settings['site_facebook'] ?? ''); ?>" target="_blank"
                            style="text-decoration: none;">
                            <i class="fa-brands fa-facebook" style="font-size: 2rem; color: #1877F2;"></i>
                        </a>
                        <a href="<?php echo htmlspecialchars($settings['site_line'] ?? ''); ?>" target="_blank"
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