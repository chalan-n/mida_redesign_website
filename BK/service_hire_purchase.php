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
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สินเชื่อเช่าซื้อรถยนต์ - MIDA LEASING</title>
    <meta name="description"
        content="บริการสินเชื่อเช่าซื้อรถยนต์มือสอง รถเก๋ง รถกระบะ รถบรรทุก อนุมัติไว ดอกเบี้ยเป็นธรรม">

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
    <meta property="og:url" content="https://www.midaleasing.com/service_hire_purchase.php">
    <meta property="og:title" content="สินเชื่อเช่าซื้อรถยนต์ - MIDA LEASING">
    <meta property="og:description"
        content="บริการสินเชื่อเช่าซื้อรถยนต์มือสอง รถเก๋ง รถกระบะ รถบรรทุก อนุมัติไว ดอกเบี้ยเป็นธรรม">
    <meta property="og:image" content="https://www.midaleasing.com/img/mida_logo_5.png">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="https://www.midaleasing.com/service_hire_purchase.php">
    <meta property="twitter:title" content="สินเชื่อเช่าซื้อรถยนต์ - MIDA LEASING">
    <meta property="twitter:description"
        content="บริการสินเชื่อเช่าซื้อรถยนต์มือสอง รถเก๋ง รถกระบะ รถบรรทุก อนุมัติไว ดอกเบี้ยเป็นธรรม">
    <meta property="twitter:image" content="https://www.midaleasing.com/img/mida_logo_5.png">

    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/style.css">

    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        .service-hero {
            background: linear-gradient(135deg, var(--primary-blue) 0%, #004a99 100%);
            color: white;
            padding: 140px 0 50px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .service-hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI0MCIgaGVpZ2h0PSI0MCIgdmlld0JveD0iMCAwIDQwIDQwIj48ZyBmaWxsLXJ1bGU9ImV2ZW5vZGQiPjxwYXRoIGQ9Ik0wIDQwaDQwVjBIMHY0MHptMjAgMjBoMjBWMjBIMjB2MjB6TTAgMjBoMjBWMHwyMHYyMHoiIGZpbGw9IiNmZmZmZmYiIGZpbGwtb3BhY2l0eT0iMC4wNSIvPjwvZz48L3N2Zz4=');
            opacity: 0.3;
        }

        .service-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            border: 1px solid #eee;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .service-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
        }

        .service-img {
            height: 200px;
            background-color: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 4rem;
            color: var(--primary-blue);
        }

        .service-content {
            padding: 30px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .service-content h3 {
            color: var(--primary-blue);
            margin-bottom: 15px;
            font-size: 1.4rem;
        }

        .service-content p {
            flex-grow: 1;
        }

        .feature-box-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 30px;
            margin-top: 40px;
        }

        .feature-box {
            text-align: center;
            padding: 30px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            border-bottom: 3px solid var(--accent-gold);
        }

        .feature-box i {
            font-size: 2.5rem;
            color: var(--primary-blue);
            margin-bottom: 20px;
        }

        .feature-box h4 {
            font-size: 1.1rem;
            margin-bottom: 10px;
        }

        .doc-section {
            background-color: #f9f9f9;
        }

        .doc-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
        }

        .doc-list {
            list-style: none;
            padding: 0;
        }

        .doc-list li {
            position: relative;
            padding-left: 30px;
            margin-bottom: 12px;
            color: #555;
        }

        .doc-list li::before {
            content: '\f00c';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            left: 0;
            color: var(--connected-green, #2ecc71);
        }

        .cta-section {
            background: var(--primary-blue);
            color: white;
            text-align: center;
        }

        @media (max-width: 768px) {
            .doc-grid {
                grid-template-columns: 1fr;
            }

            .service-hero {
                padding: 60px 0 30px;
            }

            .service-hero h1 {
                font-size: 2rem !important;
            }

            .cta-section {
                padding: 40px 0;
            }
        }
    </style>
</head>

<body>

    <!-- Header -->
    <?php $active_page = 'services'; include 'includes/nav.php'; ?>

    <!-- Hero Section -->
    <section class="service-hero">
        <div class="container">
            <h1 style="font-size: 3rem; margin-bottom: 10px; font-weight: 700; color: #fec435;">สินเชื่อเช่าซื้อรถยนต์
            </h1>
            <p style="font-size: 1.2rem; font-weight: 300; max-width: 800px; margin: 0 auto;">
                บริการทางการเงินมืออาชีพสำหรับรถที่คุณต้องการ อนุมัติไว ให้วงเงินสูง <span
                    style="display: block;">รองรับทั้งรถเก๋ง รถกระบะ
                    และรถบรรทุก</span>
            </p>
        </div>
    </section>

    <!-- Why Choose Us -->
    <section class="section">
        <div class="container">
            <div class="section-title">
                <h2 style="font-size: 1.85rem;">ทำไมต้องเลือกไมด้า ลิสซิ่ง?</h2>
                <p>เราพร้อมดูแลคุณด้วยบริการที่เป็นมืออาชีพและจริงใจ</p>
            </div>

            <div class="feature-box-grid">
                <div class="feature-box">
                    <i class="fa-regular fa-clock"></i>
                    <h4>อนุมัติรวดเร็ว</h4>
                    <p class="text-secondary">รู้ผลไว ไม่ต้องรอนาน เอกสารครบอนุมัติทันที</p>
                </div>
                <div class="feature-box">
                    <i class="fa-solid fa-hand-holding-dollar"></i>
                    <h4>วงเงินสูง</h4>
                    <p class="text-secondary">ให้วงเงินสูงประเมินตามสภาพจริงของรถยนต์</p>
                </div>
                <div class="feature-box">
                    <i class="fa-solid fa-percent"></i>
                    <h4>ดอกเบี้ยเป็นธรรม</h4>
                    <p class="text-secondary">อัตราดอกเบี้ยมาตรฐาน คิดตามปีรถและประเภทรถ</p>
                </div>
                <div class="feature-box">
                    <i class="fa-regular fa-calendar-check"></i>
                    <h4>ผ่อนนานสูงสุด 60 เดือน</h4>
                    <p class="text-secondary">เลือกระยะเวลาผ่อนได้ตามความสามารถในการชำระ</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section class="section" style="background-color: #f4f6f9;">
        <div class="container">
            <div class="section-title">
                <h2>ประเภทรถที่ให้บริการ</h2>
                <p>เลือกสินเชื่อที่เหมาะกับความต้องการของคุณ</p>
            </div>

            <div class="features-grid">

                <!-- Sedan -->
                <div class="service-card" id="sedan">
                    <div class="service-img">
                        <i class="fa-solid fa-car-side"></i>
                    </div>
                    <div class="service-content">
                        <h3>รถเก๋ง (Sedan)</h3>
                        <p style="color: #666; line-height: 1.6; margin-bottom: 20px;">
                            สินเชื่อรถเก๋งมือสอง วงเงินสูง อนุมัติไว ให้คุณเป็นเจ้าของรถในฝันได้ง่ายๆ
                            ไม่ว่าจะเป็นรถญี่ปุ่นหรือรถยุโรป เรารับพิจารณาทุกเงื่อนไข
                        </p>
                        <a href="register_hire_purchase.php?type=sedan" class="btn btn-primary"
                            style="width: 100%; text-align: center;">สนใจสมัคร</a>
                    </div>
                </div>

                <!-- Pickup -->
                <div class="service-card" id="pickup">
                    <div class="service-img">
                        <i class="fa-solid fa-truck-pickup"></i>
                    </div>
                    <div class="service-content">
                        <h3>รถกระบะ (Pickup)</h3>
                        <p style="color: #666; line-height: 1.6; margin-bottom: 20px;">
                            เสริมแกร่งให้งานของคุณด้วยสินเชื่อรถกระบะ จะบรรทุกหนักหรือใช้งานทั่วไปก็กู้ได้
                            รับทุุกรุ่น ทุกยี่ห้อ ดอกเบี้ยพิเศษสำหรับรถเพื่อการพาณิชย์
                        </p>
                        <a href="register_hire_purchase.php?type=pickup" class="btn btn-primary"
                            style="width: 100%; text-align: center;">สนใจสมัคร</a>
                    </div>
                </div>

                <!-- Truck -->
                <div class="service-card" id="truck">
                    <div class="service-img">
                        <i class="fa-solid fa-truck"></i>
                    </div>
                    <div class="service-content">
                        <h3>รถบรรทุก (Truck)</h3>
                        <p style="color: #666; line-height: 1.6; margin-bottom: 20px;">
                            สินเชื่อเพื่อธุรกิจขนส่ง ขยายกองรถของคุณด้วยสินเชื่อรถบรรทุก 6 ล้อ 10 ล้อ หัวลาก
                            วงเงินสูง ยืดหยุ่นตามความต้องการของธุรกิจคุณ
                        </p>
                        <a href="register_hire_purchase.php?type=truck" class="btn btn-primary"
                            style="width: 100%; text-align: center;">สนใจสมัคร</a>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- Documents Section -->
    <section class="section doc-section">
        <div class="container">
            <div class="section-title">
                <h2>เอกสารประกอบการสมัคร</h2>
                <p>เตรียมเอกสารให้พร้อม เพื่อความรวดเร็วในการอนุมัติ</p>
            </div>

            <div class="doc-grid">
                <div
                    style="background: white; padding: 30px; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.05);">
                    <h3
                        style="color: var(--primary-blue); margin-bottom: 20px; border-bottom: 2px solid #eee; padding-bottom: 15px;">
                        <i class="fa-solid fa-user"></i> บุคคลธรรมดา
                    </h3>
                    <ul class="doc-list">
                        <li>สำเนาบัตรประชาชน</li>
                        <li>สำเนาทะเบียนบ้าน</li>
                        <li>สลิปเงินเดือน หรือ หนังสือรับรองเงินเดือน</li>
                        <li>สำเนาบัญชีเงินฝากธนาคารย้อนหลัง 6 เดือนล่าสุด (Statement)</li>
                        <li>แผนที่บ้านและที่ทำงาน</li>
                    </ul>
                </div>

                <div
                    style="background: white; padding: 30px; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.05);">
                    <h3
                        style="color: var(--primary-blue); margin-bottom: 20px; border-bottom: 2px solid #eee; padding-bottom: 15px;">
                        <i class="fa-solid fa-briefcase"></i> นิติบุคคล
                    </h3>
                    <ul class="doc-list">
                        <li>หนังสือรับรองบริษัท (อายุไม่เกิน 3 เดือน)</li>
                        <li>สำเนาบัตรประชาชนกรรมการผู้มีอำนาจ</li>
                        <li>สำเนาทะเบียนบ้านกรรมการผู้มีอำนาจ</li>
                        <li>สำเนาบัญชีเงินฝากธนาคารย้อนหลัง 6 เดือนล่าสุด (Statement)</li>
                        <li>งบการเงินปีล่าสุด</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- GTA Section -->
    <section class="section cta-section">
        <div class="container">
            <h2 style="margin-bottom: 20px; color: white;">สนใจสินเชื่อ หรือต้องการคำปรึกษา?</h2>
            <p style="margin-bottom: 30px; font-size: 1.1rem;">
                เจ้าหน้าที่ของเราพร้อมให้คำแนะนำและข้อเสนอที่ดีที่สุดสำหรับคุณ</p>
            <div style="display: flex; justify-content: center; gap: 20px; flex-wrap: wrap;">
                <a href="#" class="btn"
                    style="background: white; color: var(--primary-blue); min-width: 180px;">ติดต่อเรา</a>
                <a href="register_hire_purchase.php" class="btn"
                    style="background: var(--accent-gold); color: white; min-width: 180px;">สมัครสินเชื่อออนไลน์</a>
            </div>
            <p style="margin-top: 30px;font-size: 0.9rem; color: #ccc;"><i class="fa-solid fa-triangle-exclamation"
                    style="margin-right: 8px;"></i>กู้เท่าที่จำเป็นและชำระคืนไหว</p>
            <p style="font-size: 0.9rem; color: #ccc;">อัตราดอกเบี้ย 11.99% - 23.99% ต่อปี
                เงื่อนไขเป็นไปตามที่บริษัทฯกำหนด</p>
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