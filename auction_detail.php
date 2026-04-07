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

// Fetch Car Details with Schedule Info
$car = null;
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    try {
        $stmt = $db->prepare("
            SELECT c.*, s.branch_name, s.auction_date 
            FROM auction_cars c 
            LEFT JOIN auction_schedules s ON c.schedule_id = s.id 
            WHERE c.id = :id
        ");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $car = $stmt->fetch();
    } catch (PDOException $e) {
    }
}

// Redirect if not found
if (!$car) {
    header("Location: auction_list.php");
    exit;
}

// Fetch Related Cars (Random 4 excluding current)
$related_cars = [];
try {
    $stmt = $db->prepare("SELECT * FROM auction_cars WHERE id != :id ORDER BY RAND() LIMIT 2");
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
    <title><?php echo htmlspecialchars($car['title']); ?> - ประมูลรถยนต์ MIDA LEASING</title>
    <meta name="description"
        content="ประมูลรถ <?php echo htmlspecialchars($car['title']); ?> สภาพดี ไมล์น้อย ประมูลราคาดีที่ MIDA LEASING">

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
            height: 400px;
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
            height: 70px;
            background-color: #f1f5f9;
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ccc;
            transition: all 0.2s;
        }

        .thumb-image:hover {
            opacity: 0.8;
            border: 2px solid var(--primary-blue);
        }

        .grade-badge {
            display: inline-flex;
            align-items: center;
            background: var(--accent-gold);
            color: #000;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 1rem;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .price-tag {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-blue);
            margin: 10px 0;
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
            padding: 10px;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .info-label {
            font-size: 0.85rem;
            color: #666;
            margin-bottom: 4px;
        }

        .info-value {
            font-weight: 600;
            color: #333;
        }

        .action-box {
            background: white;
            border: 1px solid #eee;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
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

        @media (max-width: 992px) {
            .gallery-container {
                margin-bottom: 20px;
            }

            .action-box {
                margin-top: 30px;
                position: static;
            }
        }
    </style>
</head>

<body>

    <!-- Header -->
    <?php $active_page = 'auction'; include 'includes/nav.php'; ?>

    <!-- Page Header (Mini) -->
    <div class="page-header" style="padding: 120px 0 40px;">
        <div class="container">
            <h1 style="font-size: 2rem; margin-bottom: 5px; color: #fec435;">
                <?php echo htmlspecialchars($car['title']); ?>
            </h1>
            <p style="opacity: 0.9; font-size: 1.6rem; font-weight: 600;">
                <?php if (!empty($car['queue_number'])): ?>
                    คันที่: <?php echo htmlspecialchars($car['queue_number']); ?>
                <?php else: ?>
                    คันที่: <?php echo $car['id']; ?>
                <?php endif; ?>
            </p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="section" style="padding-top: 40px; background-color: #fff; min-height: 80vh;">
        <div class="container">

            <div style="margin-bottom: 20px;">
                <a href="auction_list.php"
                    style="text-decoration: none; color: #666; display: inline-flex; align-items: center;">
                    <i class="fa-solid fa-arrow-left" style="margin-right: 8px;"></i> กลับหน้ารายการ
                </a>
            </div>

            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 40px;">

                <!-- Left Content: Images & Details -->
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
                                รูปภาพ <?php echo count($images); ?> รูป
                            </span>
                        </div>
                        <div class="thumb-grid"
                            style="grid-template-columns: repeat(<?php echo count($images); ?>, 1fr);">
                            <?php foreach ($images as $idx => $img): ?>
                                <div class="thumb-image <?php echo $idx === 0 ? 'active' : ''; ?>"
                                    onclick="document.getElementById('main-display-img').src='<?php echo htmlspecialchars($img); ?>'; setActiveThumb(this);"
                                    style="cursor: pointer;">
                                    <img src="<?php echo htmlspecialchars($img); ?>"
                                        style="width: 100%; height: 100%; object-fit: cover; border-radius: 8px;">
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <script>
                            function setActiveThumb(el) {
                                document.querySelectorAll('.thumb-image').forEach(t => t.classList.remove('active'));
                                el.classList.add('active');
                            }
                        </script>
                        <style>
                            .thumb-image.active {
                                border: 3px solid var(--primary-blue);
                            }
                        </style>
                    </div>

                    <div style="margin-top: 40px;">
                        <h3
                            style="font-size: 1.5rem; border-bottom: 2px solid #eee; padding-bottom: 10px; margin-bottom: 20px;">
                            รายละเอียดรถยนต์</h3>

                        <div class="info-grid" style="grid-template-columns: repeat(3, 1fr);">
                            <div class="info-item">
                                <span class="info-label">ยี่ห้อ / รุ่น</span>
                                <span class="info-value"><?php echo htmlspecialchars($car['title']); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">ระบบเกียร์</span>
                                <span class="info-value"><?php echo htmlspecialchars($car['transmission']); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">ปีรถ</span>
                                <span
                                    class="info-value"><?php echo !empty($car['car_year']) ? htmlspecialchars($car['car_year']) : '-'; ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">CC</span>
                                <span
                                    class="info-value"><?php echo !empty($car['cc']) ? htmlspecialchars($car['cc']) : '-'; ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">ราคา</span>
                                <?php if (!empty($car['no_starting_price']) && $car['no_starting_price'] == 1): ?>
                                    <span class="info-value" style="color: #e74c3c;">ไม่มีราคาเริ่มต้น</span>
                                <?php else: ?>
                                    <span class="info-value"><?php echo htmlspecialchars($car['price']); ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="info-item">
                                <span class="info-label">เลขไมล์</span>
                                <span class="info-value"><?php echo htmlspecialchars($car['mileage']); ?></span>
                            </div>
                        </div>
                    </div>

                    <div style="margin-top: 40px;">
                        <h3
                            style="font-size: 1.5rem; border-bottom: 2px solid #eee; padding-bottom: 10px; margin-bottom: 20px;">
                            รายงานการตรวจสภาพ (Inspection)</h3>

                        <div style="background: #f8f9fa; padding: 20px; border-radius: 12px;">
                            <?php
                            $inspection_fields = [
                                'สภาพสีตัวถังภายนอก (Body)' => $car['inspection_body'] ?? '',
                                'สภาพเครื่องยนต์ (Engine)' => $car['inspection_engine'] ?? '',
                                'ระบบเกียร์ / ช่วงล่าง (Suspension)' => $car['inspection_suspension'] ?? '',
                                'สภาพภายในห้องโดยสาร (Interior)' => $car['inspection_interior'] ?? '',
                                'ยางรถยนต์ (Tires)' => $car['inspection_tires'] ?? '',
                            ];

                            foreach ($inspection_fields as $label => $value):
                                $status_class = 'status-pass';
                                $icon_class = 'fa-circle-check';
                                $val_lower = strtolower($value);

                                // Logic to determine if warning/fail
                                if (
                                    strpos($val_lower, 'ควร') !== false ||
                                    strpos($val_lower, 'ต้อง') !== false ||
                                    strpos($val_lower, 'fail') !== false ||
                                    strpos($val_lower, 'warn') !== false ||
                                    strpos($val_lower, 'เปลี่ยน') !== false
                                ) {
                                    $status_class = 'status-warn';
                                    $icon_class = 'fa-triangle-exclamation';
                                }

                                if (empty($value))
                                    $value = "-";
                                ?>
                                <div class="inspection-item">
                                    <span><?php echo $label; ?></span>
                                    <span class="inspection-status <?php echo $status_class; ?>">
                                        <i class="fa-solid <?php echo $icon_class; ?>"></i>
                                        <?php echo htmlspecialchars($value); ?>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <p style="font-size: 0.9rem; color: #888; margin-top: 15px;">*
                            รายงานนี้เป็นเพียงการตรวจสอบเบื้องต้น ผู้ประมูลควรตรวจสอบสภาพรถจริงด้วยตนเองอีกครั้ง</p>
                    </div>

                </div>

                <!-- Right Content: Action Box -->
                <div>
                    <div class="action-box">
                        <div style="font-size: 0.9rem; color: #666;">ราคาเปิดประมูล</div>
                        <?php if (!empty($car['no_starting_price']) && $car['no_starting_price'] == 1): ?>
                            <div class="price-tag" style="color: #e74c3c;">ไม่มีราคาเริ่มต้น</div>
                        <?php else: ?>
                            <div class="price-tag"><?php echo htmlspecialchars($car['price']); ?></div>
                        <?php endif; ?>

                        <div style="background: #ecf3fc; padding: 15px; border-radius: 8px; margin: 20px 0;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                                <span style="color: #666;">ลานประมูล:</span>
                                <strong><?php echo !empty($car['branch_name']) ? htmlspecialchars($car['branch_name']) : '-'; ?></strong>
                            </div>
                            <div style="display: flex; justify-content: space-between;">
                                <span style="color: #666;">ประมูลรอบวันที่:</span>
                                <strong><?php echo !empty($car['auction_date']) ? htmlspecialchars($car['auction_date']) : '-'; ?></strong>
                            </div>
                        </div>

                        <a href="https://auction.mida-leasing.com" target="_blank" class="btn btn-primary"
                            style="width: 100%; text-align: center; margin-bottom: 10px;">
                            <i class="fa-solid fa-gavel" style="margin-right: 5px;"></i> ลงทะเบียนประมูล
                        </a>
                        <a href="https://line.me/ti/p/..." target="_blank" class="btn btn-primary btn-outline"
                            style="width: 100%; text-align: center;">
                            <i class="fa-brands fa-line" style="margin-right: 5px;"></i> สอบถามเพิ่มเติม
                        </a>

                        <hr style="border: 0; border-top: 1px solid #eee; margin: 25px 0;">

                        <div style="text-align: center;">
                            <h4 style="font-size: 1rem; margin-bottom: 15px;">แชร์หน้านี้</h4>
                            <?php
                            $current_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

                            // Fetch active share buttons
                            $share_buttons = [];
                            try {
                                $stmt_share = $db->query("SELECT * FROM share_buttons WHERE is_active = 1 ORDER BY sort_order ASC");
                                $share_buttons = $stmt_share->fetchAll();
                            } catch (PDOException $e) {
                                // Fallback if table doesn't exist
                            }
                            ?>
                            <div style="display: flex; justify-content: center; gap: 15px;">
                                <?php foreach ($share_buttons as $btn): ?>
                                    <?php if ($btn['url_pattern'] === 'copy'): ?>
                                        <a href="javascript:void(0)" onclick="copyToClipboard('<?php echo $current_url; ?>')"
                                            style="color: <?php echo htmlspecialchars($btn['color']); ?>; font-size: 1.5rem;"
                                            title="<?php echo htmlspecialchars($btn['name']); ?>">
                                            <i class="<?php echo htmlspecialchars($btn['icon']); ?>"></i>
                                        </a>
                                    <?php else: ?>
                                        <?php
                                        $share_url = str_replace(
                                            ['{URL}', '{TITLE}'],
                                            [urlencode($current_url), urlencode($car['title'])],
                                            $btn['url_pattern']
                                        );
                                        ?>
                                        <a href="<?php echo $share_url; ?>" target="_blank"
                                            style="color: <?php echo htmlspecialchars($btn['color']); ?>; font-size: 1.5rem;"
                                            title="<?php echo htmlspecialchars($btn['name']); ?>">
                                            <i class="<?php echo htmlspecialchars($btn['icon']); ?>"></i>
                                        </a>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                            <script>
                                function copyToClipboard(text) {
                                    navigator.clipboard.writeText(text).then(function () {
                                        alert('คัดลอกลิงก์เรียบร้อยแล้ว');
                                    }, function (err) {
                                        console.error('Could not copy text: ', err);
                                    });
                                }
                            </script>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Related Cars -->
            <div style="margin-top: 60px;">
                <h3 style="margin-bottom: 30px;">รถในลานประมูลเดียวกัน</h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 20px;">
                    <?php foreach ($related_cars as $related): ?>
                        <div class="car-card"
                            style="background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08); border: 1px solid #eee;">
                            <div class="car-img"
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
                                    <span><i class="fa-solid fa-gauge"></i>
                                        <?php echo htmlspecialchars($related['mileage']); ?></span>
                                    <span><i class="fa-solid fa-gear"></i>
                                        <?php echo htmlspecialchars($related['transmission']); ?></span>
                                </div>
                                <div style="display: flex; justify-content: space-between; align-items: end;">
                                    <div>
                                        <div style="font-size: 0.8rem; color: #888;">ราคาเปิดประมูล</div>
                                        <?php if (!empty($related['no_starting_price']) && $related['no_starting_price'] == 1): ?>
                                            <div style="color: #e74c3c; font-size: 1.2rem; font-weight: 700;">ไม่มีราคาเริ่มต้น
                                            </div>
                                        <?php else: ?>
                                            <div style="color: var(--primary-blue); font-size: 1.2rem; font-weight: 700;">
                                                <?php echo htmlspecialchars($related['price']); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <a href="auction_detail.php?id=<?php echo $related['id']; ?>" class="btn btn-accent"
                                        style="padding: 5px 15px; font-size: 0.9rem;">ดูรายละเอียด</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
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