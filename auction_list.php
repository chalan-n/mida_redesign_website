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

// Pagination Logic
$limit = 9;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
if ($page < 1)
    $page = 1;
$start = ($page - 1) * $limit;
$schedule_id = isset($_GET['schedule_id']) ? (int) $_GET['schedule_id'] : 0;
if ($schedule_id < 1)
    $schedule_id = 0;
$selected_schedule = array();

if ($schedule_id > 0) {
    try {
        $stmt_schedule = $db->prepare("SELECT * FROM auction_schedules WHERE id = :schedule_id AND is_active = 1");
        $stmt_schedule->bindValue(':schedule_id', $schedule_id, PDO::PARAM_INT);
        $stmt_schedule->execute();
        $selected_schedule = $stmt_schedule->fetch();
    } catch (PDOException $e) {
        $selected_schedule = array();
    }
}

// Fetch Filter Options
$brands = array();
$car_types = array();
$grades = array();

try {
    $filter_scope = $schedule_id > 0 ? " AND schedule_id = :schedule_id" : "";

    // Brands
    $sql_brands = "SELECT DISTINCT brand, COUNT(*) as count FROM auction_cars WHERE brand != '' $filter_scope GROUP BY brand ORDER BY brand";
    $stmt_brands = $db->prepare($sql_brands);
    if ($schedule_id > 0)
        $stmt_brands->bindValue(':schedule_id', $schedule_id, PDO::PARAM_INT);
    $stmt_brands->execute();
    $brands = $stmt_brands->fetchAll();

    // Car Types
    $sql_types = "SELECT DISTINCT car_type, COUNT(*) as count FROM auction_cars WHERE car_type != '' $filter_scope GROUP BY car_type ORDER BY car_type";
    $stmt_types = $db->prepare($sql_types);
    if ($schedule_id > 0)
        $stmt_types->bindValue(':schedule_id', $schedule_id, PDO::PARAM_INT);
    $stmt_types->execute();
    $car_types = $stmt_types->fetchAll();

    // Grades
    $sql_grades = "SELECT DISTINCT grade, COUNT(*) as count FROM auction_cars WHERE grade != '' $filter_scope GROUP BY grade ORDER BY grade";
    $stmt_grades = $db->prepare($sql_grades);
    if ($schedule_id > 0)
        $stmt_grades->bindValue(':schedule_id', $schedule_id, PDO::PARAM_INT);
    $stmt_grades->execute();
    $grades = $stmt_grades->fetchAll();

} catch (PDOException $e) {
}

// Fetch Cars
$cars = array();
$total_cars = 0;
$total_pages = 0;

// Build Filter Query
$where_clauses = array("1=1");
$params = array();

if ($schedule_id > 0) {
    $where_clauses[] = "schedule_id = :schedule_id";
    $params[':schedule_id'] = $schedule_id;
}

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

if (isset($_GET['grades']) && is_array($_GET['grades'])) {
    $grade_placeholders = array();
    foreach ($_GET['grades'] as $key => $grade) {
        $placeholder = ":grade_" . $key;
        $grade_placeholders[] = $placeholder;
        $params[$placeholder] = $grade;
    }
    if (!empty($grade_placeholders)) {
        $where_clauses[] = "grade IN (" . implode(', ', $grade_placeholders) . ")";
    }
}

$where_sql = implode(' AND ', $where_clauses);

try {
    // Count total cars
    $count_sql = "SELECT COUNT(*) FROM auction_cars WHERE $where_sql";
    $stmt_count = $db->prepare($count_sql);
    foreach ($params as $key => $value) {
        $stmt_count->bindValue($key, $value);
    }
    $stmt_count->execute();
    $total_cars = $stmt_count->fetchColumn();
    $total_pages = ceil($total_cars / $limit);

    // Fetch cars for current page
    $sql = "SELECT * FROM auction_cars WHERE $where_sql ORDER BY CASE WHEN queue_number IS NULL OR queue_number = '' THEN 1 ELSE 0 END, CAST(queue_number AS UNSIGNED) ASC, created_at DESC LIMIT :start, :limit";
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

$auction_round_label = '';
$auction_branch_label = '';
$auction_register_label = '';
$auction_start_label = '';
if (!empty($selected_schedule)) {
    $auction_round_label = trim((string) (isset($selected_schedule['auction_date']) ? $selected_schedule['auction_date'] : ''));
    $auction_branch_label = trim(preg_replace('/^สาขา\s*/u', '', (string) (isset($selected_schedule['branch_name']) ? $selected_schedule['branch_name'] : '')));
    $auction_register_label = trim((string) (isset($selected_schedule['time_register']) ? $selected_schedule['time_register'] : ''));
    $auction_start_label = trim((string) (isset($selected_schedule['time_start']) ? $selected_schedule['time_start'] : ''));
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายการรถประมูล - MIDA LEASING</title>
    <meta name="description" content="ค้นหารถประมูล รถมือสองสภาพดี หลากหลายรุ่น ยี่ห้อ ราคาคุ้มค่า">

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

    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/style.css">

    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        .page-header {
            background:
                radial-gradient(circle at 18% 8%, rgba(116, 169, 255, 0.22), transparent 34%),
                radial-gradient(circle at 86% 18%, rgba(255, 255, 255, 0.12), transparent 28%),
                linear-gradient(135deg, #0f356f 0%, #174b99 46%, #2b68c8 100%);
            color: white;
            padding: 112px 0 36px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .page-header::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                linear-gradient(90deg, rgba(255, 255, 255, 0.05) 1px, transparent 1px),
                linear-gradient(180deg, rgba(255, 255, 255, 0.04) 1px, transparent 1px);
            background-size: 48px 48px;
            opacity: 0.22;
            pointer-events: none;
        }

        .page-header .container {
            position: relative;
            z-index: 1;
        }

        .auction-list-hero {
            max-width: 920px;
            margin: 0 auto;
            text-align: left;
        }

        .auction-list-kicker {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 10px;
            padding: 6px 11px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.22);
            color: #ffe08a;
            font-size: 0.8rem;
            font-weight: 800;
            letter-spacing: 0.08em;
        }

        .auction-list-title {
            max-width: 760px;
            margin: 0 0 8px;
            color: #ffffff;
            font-size: clamp(1.8rem, 4.2vw, 2.75rem);
            line-height: 1.12;
            letter-spacing: -0.04em;
        }

        .auction-round-card {
            display: flex;
            flex-wrap: wrap;
            gap: 9px;
            margin-top: 16px;
        }

        .auction-round-pill {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            min-height: 36px;
            padding: 0 12px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.14);
            border: 1px solid rgba(255, 255, 255, 0.22);
            color: #fff;
            font-weight: 800;
            font-size: 0.9rem;
            backdrop-filter: blur(10px);
        }

        .auction-round-pill i {
            color: var(--accent-gold);
        }

        .auction-hero-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 18px;
        }

        .auction-hero-actions a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 40px;
            padding: 0 16px;
            border-radius: 999px;
            font-weight: 900;
            font-size: 0.92rem;
            text-decoration: none;
            transition: transform 0.18s ease, box-shadow 0.18s ease;
        }

        .auction-hero-actions a:hover {
            transform: translateY(-2px);
        }

        .auction-register-link {
            background: linear-gradient(135deg, var(--accent-gold) 0%, #ffe07a 100%);
            color: #0f2d5c;
            box-shadow: 0 14px 30px rgba(255, 199, 44, 0.24);
        }

        .auction-back-link {
            background: rgba(255, 255, 255, 0.14);
            color: #fff;
            border: 1px solid rgba(255, 255, 255, 0.24);
        }

        .auction-breadcrumb {
            padding: 22px 0 8px;
        }

        .auction-breadcrumb a {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #49627d;
            font-weight: 700;
            text-decoration: none;
        }

        .auction-list-intro {
            display: flex;
            justify-content: space-between;
            gap: 18px;
            align-items: flex-end;
            margin-bottom: 22px;
            padding: 22px;
            border-radius: 24px;
            background: #ffffff;
            border: 1px solid rgba(23, 69, 143, 0.08);
            box-shadow: 0 16px 36px rgba(23, 69, 143, 0.07);
        }

        .auction-list-intro h2 {
            margin: 0 0 8px;
            color: #0f2d5c;
            font-size: 1.55rem;
        }

        .auction-list-intro p {
            max-width: 640px;
            margin: 0;
            color: #607086;
            line-height: 1.65;
        }

        .auction-total-badge {
            flex: 0 0 auto;
            padding: 10px 14px;
            border-radius: 999px;
            background: #fff4cf;
            color: #0f2d5c;
            font-weight: 900;
        }

        .auction-filter-toggle {
            display: none;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 42px;
            padding: 0 15px;
            border: 0;
            border-radius: 999px;
            background: var(--primary-blue);
            color: #fff;
            font-family: 'Prompt', sans-serif;
            font-weight: 900;
            cursor: pointer;
            box-shadow: 0 12px 26px rgba(23, 69, 143, 0.18);
        }

        .auction-filter-close {
            display: none;
            width: 38px;
            height: 38px;
            border: 0;
            border-radius: 999px;
            background: #f3f7fb;
            color: #0f2d5c;
            cursor: pointer;
        }

        .auction-filter-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 20px;
        }

        .auction-filter-heading h3 {
            margin: 0;
            font-size: 1.2rem;
            color: #0f2d5c;
        }

        .auction-filter-backdrop {
            display: none;
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
            font-weight: 800;
            margin-bottom: 15px;
            display: block;
            color: #0f2d5c;
        }

        .filter-checkbox {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
            cursor: pointer;
            color: #465a73;
            line-height: 1.5;
        }

        .filter-checkbox input {
            margin-right: 10px;
            width: 16px;
            height: 16px;
        }

        .car-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 22px;
        }

        .car-card {
            background: white;
            border-radius: 22px;
            overflow: hidden;
            box-shadow: 0 16px 36px rgba(23, 69, 143, 0.08);
            border: 1px solid rgba(23, 69, 143, 0.09);
            transition: all 0.3s;
            display: flex;
            flex-direction: column;
        }

        .car-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 24px 50px rgba(23, 69, 143, 0.14);
        }

        .car-img {
            height: 196px;
            background-color: #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #94a3b8;
            font-size: 3rem;
            position: relative;
            overflow: hidden;
        }

        .car-queue-badge {
            position: absolute;
            top: 12px;
            left: 12px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            min-height: 30px;
            padding: 0 10px;
            border-radius: 999px;
            background: rgba(15, 45, 92, 0.88);
            color: #fff;
            font-size: 0.82rem;
            font-weight: 900;
            backdrop-filter: blur(8px);
        }

        .car-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: var(--accent-gold);
            color: #000;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .car-info {
            padding: 18px;
            display: flex;
            flex-direction: column;
            flex: 1;
        }

        .car-title {
            font-size: 1.1rem;
            font-weight: 800;
            margin: 0 0 10px;
            color: #0f2d5c;
            line-height: 1.45;
        }

        .car-details {
            font-size: 0.9rem;
            color: #607086;
            margin-bottom: 18px;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .car-details span {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 9px;
            border-radius: 999px;
            background: #f6f9fd;
        }

        .car-price {
            color: var(--primary-blue);
            font-size: 1.2rem;
            font-weight: 700;
        }

        .car-card-footer {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            gap: 12px;
            margin-top: auto;
        }

        .car-card-footer .btn {
            flex: 0 0 auto;
            border-radius: 999px;
            padding: 9px 16px !important;
            font-size: 0.88rem !important;
            font-weight: 900;
        }

        .empty-auction-list {
            grid-column: 1/-1;
            padding: 48px 24px;
            border-radius: 24px;
            background: #ffffff;
            border: 1px solid rgba(23, 69, 143, 0.08);
            text-align: center;
            box-shadow: 0 16px 36px rgba(23, 69, 143, 0.07);
        }

        .empty-auction-list i {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 70px;
            height: 70px;
            margin-bottom: 14px;
            border-radius: 24px;
            background: #fff4cf;
            color: #b98600;
            font-size: 1.8rem;
        }

        @media (max-width: 992px) {
            .page-header {
                padding: 100px 0 34px;
            }

            .auction-list-hero {
                text-align: center;
            }

            .auction-round-card,
            .auction-hero-actions {
                justify-content: center;
            }

            .layout-grid {
                grid-template-columns: 1fr;
                gap: 20px;
                margin-top: 24px;
            }

            .auction-filter-toggle {
                display: inline-flex;
            }

            .filter-sidebar {
                display: none;
                position: fixed;
                left: 12px;
                right: 12px;
                bottom: 12px;
                top: auto;
                z-index: 1002;
                max-height: min(76vh, 640px);
                margin: 0;
                overflow-y: auto;
                border-radius: 28px;
                box-shadow: 0 28px 80px rgba(8, 26, 54, 0.28);
            }

            .filter-sidebar.is-open {
                display: block;
            }

            .auction-filter-close {
                display: inline-flex;
                align-items: center;
                justify-content: center;
            }

            .auction-filter-backdrop {
                position: fixed;
                inset: 0;
                z-index: 1001;
                background: rgba(8, 20, 38, 0.45);
                backdrop-filter: blur(4px);
            }

            .auction-filter-backdrop.is-open {
                display: block;
            }

            body.auction-filter-open {
                overflow: hidden;
            }

            .auction-list-intro {
                flex-direction: column;
                align-items: flex-start;
            }
        }

        @media (max-width: 576px) {
            .page-header {
                padding: 92px 0 28px;
            }

            .auction-list-title {
                font-size: 1.75rem;
            }

            .auction-list-intro {
                padding: 18px;
            }

            .auction-list-intro > div {
                width: 100%;
            }

            .auction-filter-toggle {
                width: 100%;
            }

            .auction-hero-actions a {
                width: 100%;
            }

            .car-card-footer {
                flex-direction: column;
                align-items: stretch;
            }

            .car-card-footer .btn {
                width: 100%;
            }
        }
    </style>
</head>

<body>

    <!-- Header -->
    <?php $active_page = 'auction'; include 'includes/nav.php'; ?>

    <!-- Page Header -->
    <div class="page-header">
        <div class="container">
            <div class="auction-list-hero">
                <span class="auction-list-kicker"><i class="fa-solid fa-gavel"></i> MIDA AUCTION</span>
                <h1 class="auction-list-title">ประมูลรถยนต์</h1>

                <?php if (!empty($selected_schedule)): ?>
                    <div class="auction-round-card" aria-label="ข้อมูลรอบประมูล">
                        <span class="auction-round-pill">
                            <i class="fa-solid fa-calendar-day"></i>
                            <?php echo htmlspecialchars($auction_round_label); ?>
                        </span>
                        <?php if ($auction_branch_label !== ''): ?>
                            <span class="auction-round-pill">
                                <i class="fa-solid fa-location-dot"></i>
                                <?php echo htmlspecialchars($auction_branch_label); ?>
                            </span>
                        <?php endif; ?>
                        <?php if ($auction_start_label !== ''): ?>
                            <span class="auction-round-pill">
                                <i class="fa-solid fa-clock"></i>
                                เริ่มประมูล <?php echo htmlspecialchars($auction_start_label); ?>
                            </span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <div class="auction-hero-actions">
                    <a href="https://auction.mida-leasing.com" target="_blank" rel="noopener" class="auction-register-link">
                        <i class="fa-solid fa-pen-to-square"></i> ลงทะเบียนเข้าร่วมประมูล
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="section" style="padding-top: 0; background-color: #f8f9fa; min-height: 80vh;">
        <div class="container">

            <div class="auction-breadcrumb">
                <a href="auction.php">
                    <i class="fa-solid fa-arrow-left"></i> กลับไปหน้าปฏิทินการประมูล
                </a>
            </div>

            <div class="layout-grid">

                <!-- Sidebar Filter -->
                <div class="auction-filter-backdrop" id="auctionFilterBackdrop" aria-hidden="true"></div>

                <aside class="filter-sidebar" id="auctionFilterPanel" aria-label="ตัวกรองรายการรถประมูล">
                    <form action="" method="GET" id="filterForm">
                        <div class="auction-filter-heading">
                            <h3><i class="fa-solid fa-filter"></i> ค้นหารถที่สนใจ</h3>
                            <button type="button" class="auction-filter-close" id="auctionFilterClose" aria-label="ปิดตัวกรอง">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>

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



                        <button type="button" id="btnSearch" class="btn btn-primary" style="width: 100%;">
                            <i class="fa-solid fa-search"></i> ค้นหารายการรถ
                        </button>
                        <button type="button" id="btnClear" class="btn"
                            style="width: 100%; margin-top: 10px; display: block; text-align: center; border: 1px solid #eee; color: #666;">
                            <i class="fa-solid fa-times"></i> ล้างตัวกรอง
                        </button>
                    </form>
                </aside>

                <!-- Car Grid -->
                <div>
                    <div class="auction-list-intro">
                        <div>
                            <h2>รายการรถประมูล</h2>
                            <p>กดดูรายละเอียดรถแต่ละคันเพื่อดูรูป ข้อมูลเบื้องต้น และราคาเปิดประมูล แนะนำตรวจสอบข้อมูลอีกครั้งก่อนลงทะเบียนเข้าร่วมประมูล</p>
                        </div>
                        <span class="auction-total-badge" id="totalCarsCount"><?php echo $total_cars; ?> คัน</span>
                        <button type="button" class="auction-filter-toggle" id="auctionFilterToggle" aria-controls="auctionFilterPanel" aria-expanded="false">
                            <i class="fa-solid fa-sliders"></i> ตัวกรอง
                        </button>
                    </div>

                    <div class="car-grid" id="carGrid">
                        <!-- Cars will be loaded via AJAX -->
                        <div style="grid-column: 1/-1; text-align: center; padding: 40px;">
                            <i class="fa-solid fa-spinner fa-spin fa-2x"></i>
                            <p style="color: #888; margin-top: 15px;">กำลังโหลดข้อมูล...</p>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div id="paginationContainer"
                        style="display: flex; justify-content: center; gap: 10px; margin-top: 40px;">
                        <!-- Pagination will be rendered via JavaScript -->
                    </div>

                    <!-- Hidden data for JavaScript -->
                    <input type="hidden" id="currentFilters" value="<?php
                    $filters = array();
                    if ($schedule_id > 0)
                        $filters['schedule_id'] = $schedule_id;
                    if (isset($_GET['brands']))
                        $filters['brands'] = implode(',', $_GET['brands']);
                    if (isset($_GET['types']))
                        $filters['types'] = implode(',', $_GET['types']);
                    if (isset($_GET['grades']))
                        $filters['grades'] = implode(',', $_GET['grades']);
                    echo htmlspecialchars(json_encode($filters));
                    ?>">

                    <script>
                        (function () {
                            let currentPage = 1;
                            let totalPages = 1;
                            let isLoading = false;
                            const filterPanel = document.getElementById('auctionFilterPanel');
                            const filterBackdrop = document.getElementById('auctionFilterBackdrop');
                            const filterToggle = document.getElementById('auctionFilterToggle');
                            const filterClose = document.getElementById('auctionFilterClose');

                            function setFilterOpen(isOpen) {
                                if (!filterPanel || !filterBackdrop || !filterToggle) return;
                                filterPanel.classList.toggle('is-open', isOpen);
                                filterBackdrop.classList.toggle('is-open', isOpen);
                                document.body.classList.toggle('auction-filter-open', isOpen);
                                filterToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
                            }

                            if (filterToggle) {
                                filterToggle.addEventListener('click', function () {
                                    setFilterOpen(true);
                                });
                            }

                            if (filterClose) {
                                filterClose.addEventListener('click', function () {
                                    setFilterOpen(false);
                                });
                            }

                            if (filterBackdrop) {
                                filterBackdrop.addEventListener('click', function () {
                                    setFilterOpen(false);
                                });
                            }

                            document.addEventListener('keydown', function (event) {
                                if (event.key === 'Escape') {
                                    setFilterOpen(false);
                                }
                            });

                            // Get filters from checkboxes (dynamic)
                            function getFiltersFromForm() {
                                const filters = {};

                                // Get checked brands
                                const checkedBrands = [];
                                document.querySelectorAll('input[name="brands[]"]').forEach(cb => {
                                    if (cb.checked) checkedBrands.push(cb.value);
                                });
                                if (checkedBrands.length > 0) filters.brands = checkedBrands.join(',');

                                // Get checked types
                                const checkedTypes = [];
                                document.querySelectorAll('input[name="types[]"]').forEach(cb => {
                                    if (cb.checked) checkedTypes.push(cb.value);
                                });
                                if (checkedTypes.length > 0) filters.types = checkedTypes.join(',');

                                // Get checked grades
                                const checkedGrades = [];
                                document.querySelectorAll('input[name="grades[]"]').forEach(cb => {
                                    if (cb.checked) checkedGrades.push(cb.value);
                                });
                                if (checkedGrades.length > 0) filters.grades = checkedGrades.join(',');

                                // Get schedule_id from URL if present
                                const urlParams = new URLSearchParams(window.location.search);
                                if (urlParams.get('schedule_id')) {
                                    filters.schedule_id = urlParams.get('schedule_id');
                                }

                                return filters;
                            }

                            // Load cars via AJAX
                            async function loadCars(page, filters = null) {
                                if (isLoading) return;
                                isLoading = true;

                                // Use provided filters or get from form
                                const filtersData = filters || getFiltersFromForm();

                                const carGrid = document.getElementById('carGrid');
                                const paginationContainer = document.getElementById('paginationContainer');

                                // Show loading
                                carGrid.innerHTML = `
                                <div style="grid-column: 1/-1; text-align: center; padding: 40px;">
                                    <i class="fa-solid fa-spinner fa-spin fa-2x" style="color: #002D62;"></i>
                                    <p style="color: #888; margin-top: 15px;">กำลังโหลดข้อมูล...</p>
                                </div>
                            `;

                                // Build query params
                                let params = new URLSearchParams();
                                params.append('page', page);
                                if (filtersData.brands) params.append('brands', filtersData.brands);
                                if (filtersData.types) params.append('types', filtersData.types);
                                if (filtersData.grades) params.append('grades', filtersData.grades);
                                if (filtersData.schedule_id) params.append('schedule_id', filtersData.schedule_id);

                                try {
                                    const response = await fetch('api_auction_list.php?' + params.toString());
                                    const result = await response.json();

                                    if (result.success) {
                                        currentPage = result.data.current_page;
                                        totalPages = result.data.total_pages;

                                        // Update total cars count
                                        const totalCarsEl = document.getElementById('totalCarsCount');
                                        if (totalCarsEl) {
                                            totalCarsEl.textContent = `${result.data.total_cars} คัน`;
                                        }

                                        // Render cars
                                        renderCars(result.data.cars);

                                        // Render pagination
                                        renderPagination(currentPage, totalPages);

                                    } else {
                                        carGrid.innerHTML = `
                                        <div style="grid-column: 1/-1; text-align: center; padding: 40px; background: #fff3cd; border-radius: 10px;">
                                            <p style="color: #856404;">เกิดข้อผิดพลาด: ${result.error || 'ไม่สามารถโหลดข้อมูลได้'}</p>
                                        </div>
                                    `;
                                    }
                                } catch (error) {
                                    carGrid.innerHTML = `
                                    <div style="grid-column: 1/-1; text-align: center; padding: 40px; background: #f8d7da; border-radius: 10px;">
                                        <p style="color: #721c24;">เกิดข้อผิดพลาดในการเชื่อมต่อ</p>
                                    </div>
                                `;
                                }

                                isLoading = false;
                            }

                            // Render car cards
                            function renderCars(cars) {
                                const carGrid = document.getElementById('carGrid');

                                if (cars.length === 0) {
                                    carGrid.innerHTML = `
                                    <div class="empty-auction-list">
                                        <i class="fa-solid fa-car-side"></i>
                                        <h3 style="margin: 0 0 8px; color: #0f2d5c;">ยังไม่มีรถในเงื่อนไขนี้</h3>
                                        <p style="color: #607086; margin: 0;">ลองล้างตัวกรอง หรือกลับไปดูรอบประมูลอื่นในปฏิทินการประมูล</p>
                                    </div>
                                `;
                                    return;
                                }

                                let html = '';
                                cars.forEach(car => {
                                    const imageHtml = car.image_path
                                        ? `<img src="${escapeHtml(car.image_path)}" alt="${escapeHtml(car.title)}" style="width: 100%; height: 100%; object-fit: cover;">`
                                        : `<i class="fa-solid fa-car-side"></i>`;

                                    const queueBadge = car.queue_number
                                        ? `<span class="car-queue-badge"><i class="fa-solid fa-hashtag"></i> คันที่ ${escapeHtml(car.queue_number)}</span>`
                                        : '';

                                    html += `
                                    <div class="car-card" style="opacity: 0; animation: fadeInUp 0.4s ease forwards;">
                                        <div class="car-img">
                                            ${imageHtml}
                                            ${queueBadge}
                                        </div>
                                        <div class="car-info">
                                            <h3 class="car-title">${escapeHtml(car.title)}</h3>
                                            <div class="car-details">
                                                <span><i class="fa-solid fa-gauge"></i> ${escapeHtml(car.mileage || '-')}</span>
                                                <span><i class="fa-solid fa-gear"></i> ${escapeHtml(car.transmission || '-')}</span>
                                            </div>
                                            <div class="car-card-footer">
                                                <div>
                                                    <div style="font-size: 0.8rem; color: #607086;">ราคาเปิดประมูล</div>
                                                    <div class="car-price" ${car.no_starting_price == 1 ? 'style="color: #e74c3c;"' : ''}>${car.no_starting_price == 1 ? 'ไม่มีราคาเริ่มต้น' : escapeHtml(car.price || '-')}</div>
                                                </div>
                                                <a href="auction_detail.php?id=${car.id}" class="btn btn-accent">ดูรายละเอียด</a>
                                            </div>
                                        </div>
                                    </div>
                                `;
                                });

                                carGrid.innerHTML = html;
                            }

                            // Render pagination buttons
                            function renderPagination(current, total) {
                                const container = document.getElementById('paginationContainer');

                                if (total <= 1) {
                                    container.innerHTML = '';
                                    return;
                                }

                                let html = '';

                                // Previous button
                                if (current > 1) {
                                    html += `<button onclick="window.loadAuctionPage(${current - 1})" class="btn" style="background: white; border: 1px solid #ddd; padding: 8px 15px; cursor: pointer;">
                                    <i class="fa-solid fa-chevron-left"></i>
                                </button>`;
                                }

                                // Page numbers (show max 5 pages around current)
                                let startPage = Math.max(1, current - 2);
                                let endPage = Math.min(total, current + 2);

                                if (startPage > 1) {
                                    html += `<button onclick="window.loadAuctionPage(1)" class="btn" style="background: white; border: 1px solid #ddd; padding: 8px 15px; cursor: pointer;">1</button>`;
                                    if (startPage > 2) {
                                        html += `<span style="padding: 8px; color: #999;">...</span>`;
                                    }
                                }

                                for (let i = startPage; i <= endPage; i++) {
                                    const isActive = i === current;
                                    const activeStyle = isActive
                                        ? 'background: #002D62; color: white; border: 1px solid #002D62;'
                                        : 'background: white; border: 1px solid #ddd; color: #333;';
                                    html += `<button onclick="window.loadAuctionPage(${i})" class="btn" style="${activeStyle} padding: 8px 15px; cursor: pointer;">${i}</button>`;
                                }

                                if (endPage < total) {
                                    if (endPage < total - 1) {
                                        html += `<span style="padding: 8px; color: #999;">...</span>`;
                                    }
                                    html += `<button onclick="window.loadAuctionPage(${total})" class="btn" style="background: white; border: 1px solid #ddd; padding: 8px 15px; cursor: pointer;">${total}</button>`;
                                }

                                // Next button
                                if (current < total) {
                                    html += `<button onclick="window.loadAuctionPage(${current + 1})" class="btn" style="background: white; border: 1px solid #ddd; padding: 8px 15px; cursor: pointer;">
                                    <i class="fa-solid fa-chevron-right"></i>
                                </button>`;
                                }

                                container.innerHTML = html;
                            }

                            // Helper: escape HTML
                            function escapeHtml(text) {
                                if (!text) return '';
                                const div = document.createElement('div');
                                div.textContent = text;
                                return div.innerHTML;
                            }

                            // Expose loadCars to window for pagination buttons
                            window.loadAuctionPage = function (page) {
                                loadCars(page);
                                // Update URL without refresh
                                updateUrlWithFilters(page);
                            };

                            // Update URL with current filters
                            function updateUrlWithFilters(page) {
                                const filters = getFiltersFromForm();
                                const url = new URL(window.location);

                                // Clear existing filter params
                                url.searchParams.delete('brands[]');
                                url.searchParams.delete('types[]');
                                url.searchParams.delete('grades[]');
                                url.searchParams.delete('page');
                                url.searchParams.delete('schedule_id');

                                // Set new params
                                url.searchParams.set('page', page);
                                if (filters.schedule_id) {
                                    url.searchParams.set('schedule_id', filters.schedule_id);
                                }
                                if (filters.brands) {
                                    filters.brands.split(',').forEach(b => url.searchParams.append('brands[]', b));
                                }
                                if (filters.types) {
                                    filters.types.split(',').forEach(t => url.searchParams.append('types[]', t));
                                }
                                if (filters.grades) {
                                    filters.grades.split(',').forEach(g => url.searchParams.append('grades[]', g));
                                }

                                window.history.pushState({ page: page, filters: filters }, '', url);
                            }

                            // Handle browser back/forward buttons
                            window.addEventListener('popstate', function (e) {
                                const page = e.state?.page || 1;
                                // Restore checkbox states from state if available
                                if (e.state?.filters) {
                                    restoreFiltersToForm(e.state.filters);
                                }
                                loadCars(page);
                            });

                            // Restore filters to checkboxes
                            function restoreFiltersToForm(filters) {
                                // Clear all checkboxes first
                                document.querySelectorAll('#filterForm input[type="checkbox"]').forEach(cb => cb.checked = false);

                                if (filters.brands) {
                                    filters.brands.split(',').forEach(brand => {
                                        const cb = document.querySelector(`input[name="brands[]"][value="${brand}"]`);
                                        if (cb) cb.checked = true;
                                    });
                                }
                                if (filters.types) {
                                    filters.types.split(',').forEach(type => {
                                        const cb = document.querySelector(`input[name="types[]"][value="${type}"]`);
                                        if (cb) cb.checked = true;
                                    });
                                }
                                if (filters.grades) {
                                    filters.grades.split(',').forEach(grade => {
                                        const cb = document.querySelector(`input[name="grades[]"][value="${grade}"]`);
                                        if (cb) cb.checked = true;
                                    });
                                }
                            }

                            // Search button handler
                            document.getElementById('btnSearch').addEventListener('click', function () {
                                loadCars(1); // Always start from page 1 when filtering
                                updateUrlWithFilters(1);
                                setFilterOpen(false);
                            });

                            // Clear button handler
                            document.getElementById('btnClear').addEventListener('click', function () {
                                // Uncheck all checkboxes
                                document.querySelectorAll('#filterForm input[type="checkbox"]').forEach(cb => cb.checked = false);
                                // Load all cars
                                loadCars(1);
                                // Update URL
                                const filters = getFiltersFromForm();
                                const clearUrl = filters.schedule_id ? `auction_list.php?schedule_id=${encodeURIComponent(filters.schedule_id)}` : 'auction_list.php';
                                window.history.pushState({ page: 1, filters: filters.schedule_id ? { schedule_id: filters.schedule_id } : {} }, '', clearUrl);
                                setFilterOpen(false);
                            });

                            // Initial load
                            const urlParams = new URLSearchParams(window.location.search);
                            const initialPage = parseInt(urlParams.get('page')) || 1;
                            loadCars(initialPage);
                        })();
                    </script>

                    <style>
                        @keyframes fadeInUp {
                            from {
                                opacity: 0;
                                transform: translateY(20px);
                            }

                            to {
                                opacity: 1;
                                transform: translateY(0);
                            }
                        }

                        .car-card:nth-child(1) {
                            animation-delay: 0.05s;
                        }

                        .car-card:nth-child(2) {
                            animation-delay: 0.1s;
                        }

                        .car-card:nth-child(3) {
                            animation-delay: 0.15s;
                        }

                        .car-card:nth-child(4) {
                            animation-delay: 0.2s;
                        }

                        .car-card:nth-child(5) {
                            animation-delay: 0.25s;
                        }

                        .car-card:nth-child(6) {
                            animation-delay: 0.3s;
                        }

                        .car-card:nth-child(7) {
                            animation-delay: 0.35s;
                        }

                        .car-card:nth-child(8) {
                            animation-delay: 0.4s;
                        }

                        .car-card:nth-child(9) {
                            animation-delay: 0.45s;
                        }

                        #paginationContainer button {
                            transition: all 0.2s ease;
                        }

                        #paginationContainer button:hover {
                            transform: translateY(-2px);
                            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
                        }
                    </style>

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
                        <?php echo nl2br(htmlspecialchars($settings['site_address'])); ?>
                    </p>
                    <p style="color: #ccc; margin-bottom: 20px; font-size: 1rem;"><i class="fa-solid fa-phone"
                            style="margin-right: 10px;"></i>
                        <?php echo htmlspecialchars($settings['site_phone']); ?>
                    </p>
                    <div style="display: flex; gap: 15px;">
                        <a href="<?php echo htmlspecialchars($settings['site_facebook']); ?>" target="_blank"
                            style="text-decoration: none;">
                            <i class="fa-brands fa-facebook" style="font-size: 2rem; color: #1877F2;"></i>
                        </a>
                        <a href="<?php echo htmlspecialchars($settings['site_line']); ?>" target="_blank"
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
                        style="color: #888; text-decoration: none; margin: 0 10px;">นโยบายเกี่ยวกับ
                        cookie</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- JS -->
    <script src="assets/js/main.js"></script>

</body>

</html>
