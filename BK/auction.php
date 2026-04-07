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

// Fetch Auction Schedules with actual car count
$schedules = [];
try {
    $stmt = $db->query("
        SELECT s.*, 
               (SELECT COUNT(*) FROM auction_cars c WHERE c.schedule_id = s.id) as actual_car_count
        FROM auction_schedules s 
        WHERE s.is_active = 1 
        ORDER BY s.id DESC
    ");
    $schedules = $stmt->fetchAll();
} catch (PDOException $e) {
}

// Fetch Featured Cars (is_featured=1, fallback to random if less than 4)
$highlight_cars = [];
try {
    // First, try to get featured cars
    $stmt = $db->query("
        SELECT c.*, s.branch_name as schedule_name, s.auction_date as schedule_date
        FROM auction_cars c
        LEFT JOIN auction_schedules s ON c.schedule_id = s.id
        WHERE c.is_featured = 1 AND c.schedule_id IS NOT NULL
        ORDER BY CAST(c.queue_number AS UNSIGNED) ASC
        LIMIT 4
    ");
    $highlight_cars = $stmt->fetchAll();

    // If less than 4 featured, fill with random cars
    if (count($highlight_cars) < 4) {
        $featured_ids = array_map(function ($c) {
            return $c['id'];
        }, $highlight_cars);
        $exclude = !empty($featured_ids) ? "AND c.id NOT IN (" . implode(',', $featured_ids) . ")" : "";
        $remaining = 4 - count($highlight_cars);

        $stmt = $db->query("
            SELECT c.*, s.branch_name as schedule_name, s.auction_date as schedule_date
            FROM auction_cars c
            LEFT JOIN auction_schedules s ON c.schedule_id = s.id
            WHERE c.schedule_id IS NOT NULL $exclude
            ORDER BY RAND()
            LIMIT $remaining
        ");
        $highlight_cars = array_merge($highlight_cars, $stmt->fetchAll());
    }
} catch (PDOException $e) {
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ประมูลรถยนต์ - MIDA LEASING</title>
    <meta name="description"
        content="ศูนย์ประมูลรถยนต์มาตรฐาน รถยึดสภาพดี ราคาเริ่มต้นต่ำกว่าท้องตลาด ประมูลอย่างเปิดเผยและโปร่งใส">

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
    <meta property="og:url" content="https://www.midaleasing.com/auction.php">
    <meta property="og:title" content="ประมูลรถยนต์ - MIDA LEASING">
    <meta property="og:description"
        content="ศูนย์ประมูลรถยนต์มาตรฐาน รถยึดสภาพดี ราคาเริ่มต้นต่ำกว่าท้องตลาด ประมูลอย่างเปิดเผยและโปร่งใส">
    <meta property="og:image" content="https://www.midaleasing.com/img/mida_logo_5.png">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="https://www.midaleasing.com/auction.php">
    <meta property="twitter:title" content="ประมูลรถยนต์ - MIDA LEASING">
    <meta property="twitter:description"
        content="ศูนย์ประมูลรถยนต์มาตรฐาน รถยึดสภาพดี ราคาเริ่มต้นต่ำกว่าท้องตลาด ประมูลอย่างเปิดเผยและโปร่งใส">
    <meta property="twitter:image" content="https://www.midaleasing.com/img/mida_logo_5.png">

    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/style.css">

    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        .auction-hero {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            color: white;
            padding: 140px 0 50px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .auction-hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: radial-gradient(circle at 20% 50%, rgba(254, 196, 53, 0.1) 0%, transparent 50%);
            pointer-events: none;
        }

        .schedule-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            border: 1px solid #eee;
            margin-bottom: 20px;
            transition: transform 0.3s;
        }

        .schedule-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .schedule-header {
            background: var(--primary-blue);
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .schedule-body {
            padding: 20px;
        }

        .schedule-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px dashed #eee;
        }

        .schedule-row:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .car-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
            margin-top: 30px;
        }

        .car-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
            border: 1px solid #eee;
            transition: all 0.3s;
        }

        .car-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }

        .car-img {
            height: 180px;
            background-color: #ddd;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #888;
            font-size: 3rem;
            position: relative;
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
            padding: 15px;
        }

        .car-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 5px;
            color: #333;
        }

        .car-details {
            font-size: 0.9rem;
            color: #666;
            margin-bottom: 15px;
            display: flex;
            gap: 15px;
        }

        .car-price {
            color: var(--primary-blue);
            font-size: 1.2rem;
            font-weight: 700;
        }

        .step-circle {
            width: 50px;
            height: 50px;
            background: var(--primary-blue);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            font-weight: bold;
            margin: 0 auto 15px;
        }
    </style>
</head>

<body>

    <!-- Header -->
    <?php $active_page = 'auction';
    include 'includes/nav.php'; ?>

    <!-- Hreo Section -->
    <section class="auction-hero">
        <div class="container">
            <h1 style="font-size: 3rem; margin-bottom: 10px; font-weight: 700; color: #fec435;">ประมูลรถยนต์มือสอง
            </h1>
            <p style="font-size: 1.2rem; font-weight: 300; max-width: 800px; margin: 0 auto; color: #cbd5e1;">
                รถสวย สภาพดี ราคาโดนใจ ประมูลง่าย โปร่งใสทุกขั้นตอน <span
                    style="display: block;">มีรถให้เลือกมากมายทุกประเภท</span>
            </p>
        </div>
    </section>

    <!-- Schedule Section -->
    <section class="section" style="background-color: #f8f9fa;">
        <div class="container">
            <div class="section-title">
                <h2>ตารางการประมูล</h2>
            </div>

            <div class="row"
                style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 30px;">
                <?php if (count($schedules) > 0): ?>
                    <?php foreach ($schedules as $index => $schedule):
                        $count = $schedule['actual_car_count'];
                        ?>
                        <div class="schedule-card"
                            style="border-radius: 16px; overflow: hidden; box-shadow: 0 8px 24px rgba(0,0,0,0.1);">
                            <div class="schedule-header"
                                style="background: linear-gradient(135deg, <?php echo $index % 2 == 0 ? '#002D62, #1c4587' : '#1c4587, #2e5d9e'; ?>); padding: 20px 25px;">
                                <div>
                                    <div style="font-size: 0.85rem; opacity: 0.8; margin-bottom: 5px;">
                                        <i class="fa-solid fa-calendar-check"></i> รอบประมูล
                                    </div>
                                    <div style="font-weight: 600; font-size: 1.3rem;">
                                        <i class="fa-solid fa-location-dot"></i>
                                        <?php echo htmlspecialchars($schedule['branch_name']); ?>
                                    </div>
                                </div>
                                <div style="text-align: right;">
                                    <div
                                        style="background: rgba(255,255,255,0.2); padding: 8px 15px; border-radius: 8px; font-size: 1rem; font-weight: 500;">
                                        <?php echo htmlspecialchars($schedule['auction_date']); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="schedule-body" style="padding: 25px;">
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px;">
                                    <div style="background: #f8f9fa; padding: 15px; border-radius: 10px; text-align: center;">
                                        <div style="color: #888; font-size: 0.85rem; margin-bottom: 5px;">
                                            <i class="fa-solid fa-user-plus"></i> ลงทะเบียน
                                        </div>
                                        <div style="font-weight: 600; font-size: 1.1rem; color: #333;">
                                            <?php echo htmlspecialchars($schedule['time_register']); ?>
                                        </div>
                                    </div>
                                    <div style="background: #f8f9fa; padding: 15px; border-radius: 10px; text-align: center;">
                                        <div style="color: #888; font-size: 0.85rem; margin-bottom: 5px;">
                                            <i class="fa-solid fa-gavel"></i> เริ่มประมูล
                                        </div>
                                        <div style="font-weight: 600; font-size: 1.1rem; color: #333;">
                                            <?php echo htmlspecialchars($schedule['time_start']); ?>
                                        </div>
                                    </div>
                                </div>

                                <div
                                    style="display: flex; align-items: center; justify-content: center; gap: 15px; padding: 20px; border-radius: 12px; margin-bottom: 20px; <?php echo $count > 0 ? 'background: #f8f9fa;' : 'background: #f5f5f5; color: #999; border: 2px dashed #ddd;'; ?>">
                                    <div style="text-align: center;">
                                        <?php if ($count > 0): ?>
                                            <i class="fa-solid fa-car" style="font-size: 2rem; opacity: 0.8;"></i>
                                        <?php else: ?>
                                            <i class="fa-solid fa-clock" style="font-size: 1.5rem;"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <div style="font-size: 0.85rem; opacity: 0.8;">จำนวนรถในรอบนี้</div>
                                        <div style="font-size: 1.6rem; font-weight: 700;">
                                            <?php echo $count > 0 ? $count . ' คัน' : 'เร็วๆ นี้'; ?>
                                        </div>
                                    </div>
                                </div>

                                <?php if ($count > 0): ?>
                                    <a href="auction_list.php?schedule_id=<?php echo $schedule['id']; ?>" class="btn btn-primary"
                                        style="width: 100%; text-align: center; padding: 12px; font-size: 1rem; border-radius: 10px;">
                                        <i class="fa-solid fa-eye"></i> ดูรายการรถ
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div
                        style="grid-column: 1/-1; text-align: center; padding: 40px; background: white; border-radius: 10px;">
                        <p style="color: #888;">ยังไม่มีตารางการประมูลในขณะนี้</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Hightlight Cars -->
    <section class="section">
        <div class="container">
            <div class="section-title">
                <h2>รถเด่นประจำรอบ</h2>
                <p>รถสวยคัดพิเศษ สภาพพร้อมใช้งาน</p>
            </div>

            <div class="car-grid">
                <?php if (count($highlight_cars) > 0): ?>
                    <?php foreach ($highlight_cars as $car): ?>
                        <div class="car-card">
                            <div class="car-img">
                                <?php if (!empty($car['image_path'])): ?>
                                    <img src="<?php echo htmlspecialchars($car['image_path']); ?>"
                                        alt="<?php echo htmlspecialchars($car['title']); ?>"
                                        style="width: 100%; height: 100%; object-fit: cover;">
                                <?php else: ?>
                                    <i class="fa-solid fa-car-side"></i>
                                <?php endif; ?>
                                <?php if (!empty($car['queue_number'])): ?>
                                    <span
                                        style="position: absolute; top: 10px; left: 10px; background: rgba(0,0,0,0.7); color: white; padding: 2px 8px; border-radius: 4px; font-size: 0.8rem;">
                                        คันที่: <?php echo htmlspecialchars($car['queue_number']); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <div class="car-info">
                                <h3 class="car-title"><?php echo htmlspecialchars($car['title']); ?></h3>
                                <div class="car-details">
                                    <span><i class="fa-solid fa-gauge"></i>
                                        <?php echo htmlspecialchars($car['mileage']); ?></span>
                                    <span><i class="fa-solid fa-gear"></i>
                                        <?php echo htmlspecialchars($car['transmission']); ?></span>
                                </div>
                                <div style="display: flex; justify-content: space-between; align-items: end;">
                                    <div>
                                        <div style="font-size: 0.8rem; color: #888;">ราคาเปิดประมูล</div>
                                        <?php if (!empty($car['no_starting_price']) && $car['no_starting_price'] == 1): ?>
                                            <div class="car-price" style="color: #e74c3c;">ไม่มีราคาเริ่มต้น</div>
                                        <?php else: ?>
                                            <div class="car-price"><?php echo htmlspecialchars($car['price']); ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <a href="auction_detail.php?id=<?php echo $car['id']; ?>" class="btn btn-accent"
                                        style="padding: 5px 15px; font-size: 0.9rem;">ดูรูป</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div
                        style="grid-column: 1/-1; text-align: center; padding: 40px; background: white; border-radius: 10px;">
                        <p style="color: #888;">ยังไม่มีรถแนะนำในขณะนี้</p>
                    </div>
                <?php endif; ?>
            </div>

            <div style="text-align: center; margin-top: 40px;">
                <a href="auction_list.php" class="btn btn-primary btn-outline">ดูรายการรถทั้งหมด <i
                        class="fa-solid fa-arrow-right"></i></a>
            </div>
        </div>
    </section>

    <!-- How to -->
    <section class="section" style="background-color: #f0f4f8;">
        <div class="container">
            <div class="section-title">
                <h2>ขั้นตอนการประมูล</h2>
                <p>ง่ายๆ ใครก็ประมูลได้</p>
            </div>

            <div
                style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 30px; text-align: center;">
                <div>
                    <div class="step-circle">1</div>
                    <h4 style="margin-bottom: 10px;">ลงทะเบียน</h4>
                    <p class="text-secondary">นำบัตรประชาชนมาลงทะเบียน<br>และวางเงินมัดจำป้าย</p>
                </div>
                <div>
                    <div class="step-circle">2</div>
                    <h4 style="margin-bottom: 10px;">ตรวจดูสภาพรถ</h4>
                    <p class="text-secondary">เดินชมรถที่ลานประมูล<br>สตาร์ทเครื่องยนต์ ตรวจสอบสภาพ</p>
                </div>
                <div>
                    <div class="step-circle">3</div>
                    <h4 style="margin-bottom: 10px;">ยกป้ายสู้ราคา</h4>
                    <p class="text-secondary">เมื่อถึงคิวรถที่ชอบ<br>ยกป้ายเสนอราคาแข่งกัน</p>
                </div>
                <div>
                    <div class="step-circle">4</div>
                    <h4 style="margin-bottom: 10px;">ชำระเงินและรับรถ</h4>
                    <p class="text-secondary">ชนะประมูล ชำระเงินส่วนที่เหลือ<br>และรับรถกลับบ้านได้เลย</p>
                </div>
            </div>
        </div>
    </section>

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