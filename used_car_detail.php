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

// Fetch Car Details
$car = null;
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    try {
        $stmt = $db->prepare("SELECT * FROM used_cars WHERE id = :id AND is_active = 1");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $car = $stmt->fetch();
    } catch (PDOException $e) {
    }
}

// Redirect if not found
if (!$car) {
    header("Location: used_cars.php");
    exit;
}

// Fetch Related Cars
$related_cars = [];
try {
    $stmt = $db->prepare("SELECT * FROM used_cars WHERE id != :id AND is_active = 1 ORDER BY RAND() LIMIT 4");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $related_cars = $stmt->fetchAll();
} catch (PDOException $e) {
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?php echo htmlspecialchars($car['title']); ?> - รถสวยพร้อมขาย MIDA LEASING
    </title>
    <meta name="description"
        content="<?php echo htmlspecialchars($car['title']); ?> รถสวยพร้อมขาย ราคา <?php echo htmlspecialchars($car['price']); ?> พร้อมจัดไฟแนนซ์">

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
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            color: white;
            padding: 140px 0 60px;
            text-align: center;
        }

        .gallery-container {
            display: grid;
            gap: 10px;
            margin-bottom: 30px;
        }

        .main-image {
            width: 100%;
            height: 450px;
            background-color: #e2e8f0;
            border-radius: 12px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 5rem;
            color: #94a3b8;
            position: relative;
        }

        .thumb-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 10px;
        }

        .thumb-image {
            height: 80px;
            background-color: #f1f5f9;
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ccc;
            transition: all 0.2s;
            overflow: hidden;
        }

        .thumb-image:hover,
        .thumb-image.active {
            border: 3px solid var(--primary-blue);
        }

        .price-tag {
            font-size: 2.2rem;
            font-weight: 700;
            color: var(--primary-blue);
            margin: 10px 0;
        }

        .price-original {
            text-decoration: line-through;
            color: #999;
            font-size: 1.2rem;
            margin-left: 15px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin: 20px 0;
        }

        .info-item {
            display: flex;
            flex-direction: column;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 10px;
        }

        .info-label {
            font-size: 0.85rem;
            color: #666;
            margin-bottom: 4px;
        }

        .info-value {
            font-weight: 600;
            color: #333;
            font-size: 1.05rem;
        }

        .action-box {
            background: white;
            border: 1px solid #eee;
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            position: sticky;
            top: 100px;
        }

        .inspection-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #eee;
        }

        .inspection-status {
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
        }

        .status-pass {
            color: #10b981;
        }

        .status-warn {
            color: #eab308;
        }

        .related-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 20px;
        }

        @media (max-width: 992px) {
            .detail-grid {
                grid-template-columns: 1fr !important;
            }

            .action-box {
                position: static;
                margin-top: 30px;
            }
        }
    </style>
</head>

<body>

    <!-- Header -->
    <?php $active_page = 'used_cars';
    include 'includes/nav.php'; ?>

    <!-- Page Header -->
    <div class="page-header" style="padding: 120px 0 40px;">
        <div class="container">
            <h1 style="font-size: 2rem; margin-bottom: 5px; color: #fec435;">
                <?php echo htmlspecialchars($car['title']); ?>
            </h1>
            <p style="opacity: 0.9; font-size: 1.1rem;">
                <?php if (!empty($car['car_year'])): ?>ปี
                    <?php echo htmlspecialchars($car['car_year']); ?>
                <?php endif; ?>
                <?php if (!empty($car['mileage'])): ?> | ไมล์
                    <?php echo htmlspecialchars($car['mileage']); ?>
                <?php endif; ?>
            </p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="section" style="padding-top: 40px; background-color: #fff; min-height: 80vh;">
        <div class="container">

            <div style="margin-bottom: 20px;">
                <a href="used_cars.php"
                    style="text-decoration: none; color: #666; display: inline-flex; align-items: center;">
                    <i class="fa-solid fa-arrow-left" style="margin-right: 8px;"></i> กลับหน้ารายการ
                </a>
            </div>

            <div class="detail-grid" style="display: grid; grid-template-columns: 2fr 1fr; gap: 40px;">

                <!-- Left Content -->
                <div>
                    <!-- Gallery -->
                    <?php
                    $images = [];
                    if (!empty($car['image_path']))
                        $images[] = $car['image_path'];
                    if (!empty($car['image_path_2']))
                        $images[] = $car['image_path_2'];
                    if (!empty($car['image_path_3']))
                        $images[] = $car['image_path_3'];
                    if (!empty($car['image_path_4']))
                        $images[] = $car['image_path_4'];
                    if (!empty($car['image_path_5']))
                        $images[] = $car['image_path_5'];
                    ?>
                    <div class="gallery-container">
                        <div class="main-image">
                            <?php if (count($images) > 0): ?>
                                <img id="main-display-img" src="<?php echo htmlspecialchars($images[0]); ?>"
                                    alt="<?php echo htmlspecialchars($car['title']); ?>"
                                    style="width: 100%; height: 100%; object-fit: cover;">
                            <?php else: ?>
                                <i class="fa-solid fa-car-side"></i>
                            <?php endif; ?>
                            <span
                                style="position: absolute; top: 15px; left: 15px; background: rgba(0,0,0,0.6); color: white; padding: 5px 15px; border-radius: 20px; font-size: 1rem;">
                                รูปภาพ
                                <?php echo count($images); ?> รูป
                            </span>
                        </div>
                        <?php if (count($images) > 1): ?>
                            <div class="thumb-grid"
                                style="grid-template-columns: repeat(<?php echo count($images); ?>, 1fr);">
                                <?php foreach ($images as $idx => $img): ?>
                                    <div class="thumb-image <?php echo $idx === 0 ? 'active' : ''; ?>"
                                        onclick="document.getElementById('main-display-img').src='<?php echo htmlspecialchars($img); ?>'; setActiveThumb(this);">
                                        <img src="<?php echo htmlspecialchars($img); ?>"
                                            style="width: 100%; height: 100%; object-fit: cover;">
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <script>
                                function setActiveThumb(el) {
                                    document.querySelectorAll('.thumb-image').forEach(t => t.classList.remove('active'));
                                    el.classList.add('active');
                                }
                            </script>
                        <?php endif; ?>
                    </div>

                    <!-- Car Details -->
                    <div style="margin-top: 40px;">
                        <h3
                            style="font-size: 1.5rem; border-bottom: 2px solid #eee; padding-bottom: 10px; margin-bottom: 20px;">
                            รายละเอียดรถยนต์
                        </h3>

                        <div class="info-grid" style="grid-template-columns: repeat(3, 1fr);">
                            <div class="info-item">
                                <span class="info-label">ยี่ห้อ</span>
                                <span class="info-value">
                                    <?php echo !empty($car['brand']) ? htmlspecialchars($car['brand']) : '-'; ?>
                                </span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">รุ่น</span>
                                <span class="info-value">
                                    <?php echo !empty($car['model']) ? htmlspecialchars($car['model']) : '-'; ?>
                                </span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">ปีรถ</span>
                                <span class="info-value">
                                    <?php echo !empty($car['car_year']) ? htmlspecialchars($car['car_year']) : '-'; ?>
                                </span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">ประเภท</span>
                                <span class="info-value">
                                    <?php echo !empty($car['car_type']) ? htmlspecialchars($car['car_type']) : '-'; ?>
                                </span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">สี</span>
                                <span class="info-value">
                                    <?php echo !empty($car['car_color']) ? htmlspecialchars($car['car_color']) : '-'; ?>
                                </span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">เลขไมล์</span>
                                <span class="info-value">
                                    <?php echo !empty($car['mileage']) ? htmlspecialchars($car['mileage']) : '-'; ?>
                                </span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">CC</span>
                                <span class="info-value">
                                    <?php echo !empty($car['cc']) ? htmlspecialchars($car['cc']) : '-'; ?>
                                </span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">เกียร์</span>
                                <span class="info-value">
                                    <?php echo !empty($car['transmission']) ? htmlspecialchars($car['transmission']) : '-'; ?>
                                </span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">ทะเบียน</span>
                                <span class="info-value">
                                    <?php echo !empty($car['license_plate']) ? htmlspecialchars($car['license_plate']) : '-'; ?>
                                </span>
                            </div>
                        </div>

                        <?php if (!empty($car['description'])): ?>
                            <div style="margin-top: 30px;">
                                <h4 style="margin-bottom: 15px;">รายละเอียดเพิ่มเติม</h4>
                                <p style="color: #555; line-height: 1.8;">
                                    <?php echo nl2br(htmlspecialchars($car['description'])); ?>
                                </p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Inspection Report -->
                    <?php
                    $has_inspection = !empty($car['inspection_body']) || !empty($car['inspection_engine']) ||
                        !empty($car['inspection_suspension']) || !empty($car['inspection_interior']) ||
                        !empty($car['inspection_tires']);
                    if ($has_inspection):
                        ?>
                        <div style="margin-top: 40px;">
                            <h3
                                style="font-size: 1.5rem; border-bottom: 2px solid #eee; padding-bottom: 10px; margin-bottom: 20px;">
                                รายงานการตรวจสภาพ
                            </h3>

                            <div style="background: #f8f9fa; padding: 20px; border-radius: 12px;">
                                <?php
                                $inspection_fields = [
                                    'สภาพตัวถัง (Body)' => $car['inspection_body'] ?? '',
                                    'สภาพเครื่องยนต์ (Engine)' => $car['inspection_engine'] ?? '',
                                    'ระบบช่วงล่าง (Suspension)' => $car['inspection_suspension'] ?? '',
                                    'สภาพภายใน (Interior)' => $car['inspection_interior'] ?? '',
                                    'ยาง (Tires)' => $car['inspection_tires'] ?? '',
                                ];

                                foreach ($inspection_fields as $label => $value):
                                    if (empty($value))
                                        continue;
                                    $status_class = 'status-pass';
                                    $icon_class = 'fa-circle-check';
                                    ?>
                                    <div class="inspection-item">
                                        <span>
                                            <?php echo $label; ?>
                                        </span>
                                        <span class="inspection-status <?php echo $status_class; ?>">
                                            <i class="fa-solid <?php echo $icon_class; ?>"></i>
                                            <?php echo htmlspecialchars($value); ?>
                                        </span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Right Content: Action Box -->
                <div>
                    <div class="action-box">
                        <div style="font-size: 0.9rem; color: #666;">ราคาขาย</div>
                        <div class="price-tag">
                            <?php echo htmlspecialchars($car['price']); ?>
                            <?php if (!empty($car['price_original'])): ?>
                                <span class="price-original">
                                    <?php echo htmlspecialchars($car['price_original']); ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <?php if (!empty($car['price_original'])): ?>
                            <div
                                style="background: #e74c3c; color: white; padding: 8px 15px; border-radius: 8px; text-align: center; margin-bottom: 20px;">
                                <i class="fa-solid fa-tag"></i> ราคาพิเศษ! ประหยัดทันที
                            </div>
                        <?php endif; ?>

                        <div style="background: #ecf3fc; padding: 15px; border-radius: 10px; margin: 20px 0;">
                            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                                <i class="fa-solid fa-shield-check"
                                    style="color: var(--primary-blue); font-size: 1.2rem;"></i>
                                <span style="font-weight: 600;">รับประกันสภาพ</span>
                            </div>
                            <p style="color: #666; font-size: 0.9rem; margin: 0;">รถทุกคันผ่านการตรวจสภาพอย่างละเอียด
                                พร้อมรับประกันคุณภาพ</p>
                        </div>

                        <a href="register_hire_purchase.php" class="btn btn-primary"
                            style="width: 100%; text-align: center; margin-bottom: 10px; padding: 15px;">
                            <i class="fa-solid fa-file-signature" style="margin-right: 5px;"></i> สมัครสินเชื่อเช่าซื้อ
                        </a>
                        <a href="https://line.me/R/ti/p/@midaleasing" target="_blank"
                            class="btn btn-primary btn-outline" style="width: 100%; text-align: center; padding: 15px;">
                            <i class="fa-brands fa-line" style="margin-right: 5px;"></i> สอบถามเพิ่มเติม
                        </a>

                        <hr style="border: 0; border-top: 1px solid #eee; margin: 25px 0;">

                        <div style="text-align: center;">
                            <h4 style="font-size: 1rem; margin-bottom: 15px;">แชร์หน้านี้</h4>
                            <div style="display: flex; justify-content: center; gap: 15px;">
                                <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode('https://www.midaleasing.com/used_car_detail.php?id=' . $car['id']); ?>"
                                    target="_blank" style="color: #1877F2; font-size: 1.5rem;">
                                    <i class="fa-brands fa-facebook"></i>
                                </a>
                                <a href="https://social-plugins.line.me/lineit/share?url=<?php echo urlencode('https://www.midaleasing.com/used_car_detail.php?id=' . $car['id']); ?>"
                                    target="_blank" style="color: #00B900; font-size: 1.5rem;">
                                    <i class="fa-brands fa-line"></i>
                                </a>
                                <a href="javascript:void(0)" onclick="copyToClipboard()"
                                    style="color: #333; font-size: 1.5rem;">
                                    <i class="fa-solid fa-link"></i>
                                </a>
                            </div>
                            <script>
                                function copyToClipboard() {
                                    navigator.clipboard.writeText(window.location.href).then(function () {
                                        alert('คัดลอกลิงก์เรียบร้อยแล้ว');
                                    });
                                }
                            </script>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Related Cars -->
            <?php if (count($related_cars) > 0): ?>
                <div style="margin-top: 60px;">
                    <h3 style="margin-bottom: 30px;">รถสวยอื่นๆ ที่น่าสนใจ</h3>
                    <div class="related-grid">
                        <?php foreach ($related_cars as $related): ?>
                            <div class="car-card"
                                style="background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 3px 15px rgba(0, 0, 0, 0.08); border: 1px solid #eee;">
                                <div
                                    style="height: 180px; background-color: #f1f5f9; display: flex; align-items: center; justify-content: center; color: #94a3b8; font-size: 3rem; position: relative;">
                                    <?php if (!empty($related['image_path'])): ?>
                                        <img src="<?php echo htmlspecialchars($related['image_path']); ?>"
                                            alt="<?php echo htmlspecialchars($related['title']); ?>"
                                            style="width: 100%; height: 100%; object-fit: cover;">
                                    <?php else: ?>
                                        <i class="fa-solid fa-car-side"></i>
                                    <?php endif; ?>
                                </div>
                                <div style="padding: 15px;">
                                    <h3 style="font-size: 1.1rem; font-weight: 600; margin-bottom: 5px; color: #333;">
                                        <?php echo htmlspecialchars($related['title']); ?>
                                    </h3>
                                    <div style="font-size: 0.9rem; color: #666; margin-bottom: 15px; display: flex; gap: 15px;">
                                        <?php if (!empty($related['mileage'])): ?>
                                            <span><i class="fa-solid fa-gauge"></i>
                                                <?php echo htmlspecialchars($related['mileage']); ?>
                                            </span>
                                        <?php endif; ?>
                                        <?php if (!empty($related['transmission'])): ?>
                                            <span><i class="fa-solid fa-gear"></i>
                                                <?php echo htmlspecialchars($related['transmission']); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <div style="display: flex; justify-content: space-between; align-items: end;">
                                        <div>
                                            <div style="font-size: 0.8rem; color: #888;">ราคา</div>
                                            <div style="color: var(--primary-blue); font-size: 1.2rem; font-weight: 700;">
                                                <?php echo htmlspecialchars($related['price']); ?>
                                            </div>
                                        </div>
                                        <a href="used_car_detail.php?id=<?php echo $related['id']; ?>" class="btn btn-accent"
                                            style="padding: 5px 15px; font-size: 0.9rem;">ดูรายละเอียด</a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

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