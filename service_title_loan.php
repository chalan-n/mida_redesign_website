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
    <title>สินเชื่อจำนำทะเบียนรถยนต์ - MIDA LEASING</title>
    <meta name="description"
        content="เปลี่ยนเล่มทะเบียนรถเป็นเงินสดกับ ไมด้า ลิสซิ่ง ได้เงินก้อน รถยังมีขับ ไม่ต้องมีคนค้ำ* อนุมัติเร็วภายใน 1 ชม. รับรถเก๋ง กระบะ และรถบรรทุกทุกยี่ห้อ">
    <meta name="keywords"
        content="จำนำทะเบียนรถ, เงินด่วนเล่มรถ, สินเชื่อรถแลกเงิน">

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
    <link rel="stylesheet" href="assets/css/content-pages.css">
</head>

<body class="service-page">

    <!-- Header -->
    <?php $active_page = 'services'; include 'includes/nav.php'; ?>

    <!-- Hero Section -->
    <section class="service-hero">
        <div class="container">
            <h1 style="font-size: 3rem; margin-bottom: 10px; font-weight: 700;">สินเชื่อจำนำทะเบียนรถยนต์</h1>
            <p class="service-lead">
                ใช้เล่มทะเบียนรถช่วยเสริมสภาพคล่อง รับเงินก้อน รถยังมีขับ และไม่ต้องโอนเล่ม
            </p>
        </div>
    </section>

    <!-- Quick Summary Section -->
    <section class="section service-summary-section">
        <div class="container">
            <div class="section-title">
                <h2 style="font-size: 1.85rem;">มีเล่มทะเบียน อยากใช้เงิน ไมด้าช่วยดูให้ได้</h2>
                <p>มีรถอยู่แล้วและอยากได้เงินก้อนไปหมุนใช้ โดยยังอยากใช้รถได้ตามปกติ</p>
            </div>

            <div class="service-summary-grid">
                <div class="service-summary-card">
                    <span class="service-summary-icon"><i class="fa-solid fa-user-check"></i></span>
                    <h3>เหมาะกับใคร</h3>
                    <p>คนมีรถที่อยากใช้เล่มทะเบียนช่วยเรื่องเงิน ไม่ว่าจะหมุนธุรกิจหรือจัดการค่าใช้จ่ายจำเป็น</p>
                </div>
                <div class="service-summary-card">
                    <span class="service-summary-icon"><i class="fa-solid fa-star"></i></span>
                    <h3>จุดเด่น</h3>
                    <ul>
                        <li>รถยังมีขับตามปกติ</li>
                        <li>ไม่ต้องโอนเล่ม</li>
                        <li>ช่วยดูวงเงินจากรถของคุณ</li>
                    </ul>
                </div>
                <div class="service-summary-card">
                    <span class="service-summary-icon"><i class="fa-solid fa-list-check"></i></span>
                    <h3>เริ่มยังไง</h3>
                    <p>เลือกประเภทรถ ฝากเบอร์ติดต่อไว้ แล้วเจ้าหน้าที่จะโทรกลับไปช่วยดูรายละเอียดรถให้</p>
                </div>
                <div class="service-summary-card">
                    <span class="service-summary-icon"><i class="fa-solid fa-file-lines"></i></span>
                    <h3>เตรียมอะไรไว้บ้าง</h3>
                    <p>เตรียมบัตรประชาชน ทะเบียนบ้าน เล่มทะเบียนรถ และเอกสารรายได้ไว้คร่าว ๆ ก่อนคุยได้เลย</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Why Choose Us -->
    <section class="section">
        <div class="container">
            <div class="section-title">
                <h2 style="font-size: 1.85rem;">จุดเด่นบริการ</h2>
                <p>ทำไมต้องเลือกจำนำทะเบียนกับไมด้า ลิสซิ่ง?</p>
            </div>

            <div class="feature-box-grid">
                <div class="feature-box">
                    <i class="fa-solid fa-book"></i>
                    <h4>ไม่ต้องโอนเล่ม</h4>
                    <p class="text-secondary">กรรมสิทธิ์ยังเป็นของคุณ ไม่ต้องโอนให้ยุ่งยาก</p>
                </div>
                <div class="feature-box">
                    <i class="fa-solid fa-car-side"></i>
                    <h4>มีรถใช้เหมือนเดิม</h4>
                    <p class="text-secondary">ได้รับเงินก้อน แต่ยังสามารถใช้รถได้ตามปกติ</p>
                </div>
                <div class="feature-box">
                    <i class="fa-solid fa-money-bill-wave"></i>
                    <h4>รับเงินเต็มจำนวน</h4>
                    <p class="text-secondary">ไม่หักค่าธรรมเนียมซับซ้อน รับเงินเข้าบัญชีเต็มๆ</p>
                </div>
                <div class="feature-box">
                    <i class="fa-solid fa-bolt"></i>
                    <h4>อนุมัติไว</h4>
                    <p class="text-secondary">เอกสารครบ รู้ผลและรับเงินได้อย่างรวดเร็ว</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section class="section" style="background-color: #f4f6f9;">
        <div class="container">
            <div class="section-title">
                <h2>ประเภทรถที่รับจำนำ</h2>
                <p>เลือกประเภทสินเชื่อให้ตรงกับรถและความต้องการใช้เงินของคุณ</p>
            </div>

            <div class="features-grid">

                <!-- Sedan -->
                <div class="service-card" id="sedan">
                    <div class="service-img">
                        <i class="fa-solid fa-car-side"></i>
                    </div>
                    <div class="service-content">
                        <h3>รถเก๋ง</h3>
                        <ul class="service-card-points">
                            <li>รถยังมีขับ ใช้งานได้ตามปกติ</li>
                            <li>ประเมินวงเงินตามสภาพรถจริง</li>
                            <li>เจ้าหน้าที่ช่วยแนะนำเอกสาร</li>
                        </ul>
                        <a href="register_title_loan.php?type=sedan" class="btn btn-primary"
                            style="width: 100%; text-align: center;">สมัครจำนำทะเบียนรถเก๋ง</a>
                    </div>
                </div>

                <!-- Pickup -->
                <div class="service-card" id="pickup">
                    <div class="service-img">
                        <i class="fa-solid fa-truck-pickup"></i>
                    </div>
                    <div class="service-content">
                        <h3>รถกระบะ</h3>
                        <ul class="service-card-points">
                            <li>เหมาะกับใช้หมุนธุรกิจหรือค่าใช้จ่ายจำเป็น</li>
                            <li>รองรับตอนเดียว แคป และ 4 ประตู</li>
                            <li>รู้ผลไวเมื่อเอกสารครบ</li>
                        </ul>
                        <a href="register_title_loan.php?type=pickup" class="btn btn-primary"
                            style="width: 100%; text-align: center;">สมัครจำนำทะเบียนรถกระบะ</a>
                    </div>
                </div>

                <!-- Truck -->
                <div class="service-card" id="truck">
                    <div class="service-img">
                        <i class="fa-solid fa-truck"></i>
                    </div>
                    <div class="service-content">
                        <h3>รถบรรทุก</h3>
                        <ul class="service-card-points">
                            <li>สำหรับธุรกิจขนส่งและพาณิชย์</li>
                            <li>รองรับรถ 6 ล้อ 10 ล้อ และหัวลาก</li>
                            <li>ช่วยวางแผนวงเงินให้เหมาะกับธุรกิจ</li>
                        </ul>
                        <a href="register_title_loan.php?type=truck" class="btn btn-primary"
                            style="width: 100%; text-align: center;">สมัครจำนำทะเบียนรถบรรทุก</a>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- Documents Section -->
    <section class="section doc-section">
        <div class="container">
            <div class="section-title">
                <h2>เอกสารที่ควรเตรียม</h2>
                <p>เตรียมข้อมูลพื้นฐานไว้ล่วงหน้า เพื่อให้เจ้าหน้าที่ประเมินและติดต่อกลับได้เร็วขึ้น</p>
            </div>

            <div class="doc-grid">
                <div class="doc-card">
                    <h3><i class="fa-solid fa-user"></i> บุคคลธรรมดา</h3>
                    <ul class="doc-list">
                        <li>สำเนาบัตรประชาชน</li>
                        <li>สำเนาทะเบียนบ้าน</li>
                        <li>เล่มทะเบียนรถตัวจริง (ไม่ต้องโอน)</li>
                        <li>เอกสารแสดงรายได้ (สลิปเงินเดือน / Statement)</li>
                        <li>สมุดบัญชีธนาคารสำหรับรับเงินโอน</li>
                    </ul>
                </div>

                <div class="doc-card">
                    <h3><i class="fa-solid fa-briefcase"></i> นิติบุคคล</h3>
                    <ul class="doc-list">
                        <li>หนังสือรับรองบริษัท</li>
                        <li>สำเนาบัตรประชาชนกรรมการ</li>
                        <li>สำเนาทะเบียนบ้านกรรมการ</li>
                        <li>เล่มทะเบียนรถตัวจริง</li>
                        <li>งบการเงิน / Statement ย้อนหลัง</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="section" style="background-color: #f8fbff;">
        <div class="container">
            <div class="section-title">
                <h2>คำถามที่พบบ่อย</h2>
                <p>ข้อสงสัยที่ลูกค้ามักถามก่อนใช้เล่มทะเบียนขอสินเชื่อ</p>
            </div>

            <div class="faq-grid">
                <div class="faq-card">
                    <h3>รถยังใช้งานได้ไหม?</h3>
                    <p>ยังใช้งานได้ตามปกติ เหมาะกับคนที่ต้องใช้รถทุกวัน ทั้งใช้ส่วนตัวและใช้ทำงาน</p>
                </div>
                <div class="faq-card">
                    <h3>ต้องโอนเล่มหรือไม่?</h3>
                    <p>ไม่ต้องโอนเล่ม กรรมสิทธิ์ยังเป็นของลูกค้า รายละเอียดเจ้าหน้าที่จะอธิบายให้ชัดเจนก่อนทำเรื่อง</p>
                </div>
                <div class="faq-card">
                    <h3>รถติดไฟแนนซ์อยู่สมัครได้ไหม?</h3>
                    <p>ฝากข้อมูลให้เจ้าหน้าที่ช่วยดูเบื้องต้นได้ครับ โดยจะดูจากรถ ภาระเดิม และรายละเอียดของลูกค้า</p>
                </div>
                <div class="faq-card">
                    <h3>ฝากข้อมูลแล้วผูกมัดทันทีไหม?</h3>
                    <p>ยังไม่ผูกมัดครับ เป็นเพียงการให้เจ้าหน้าที่โทรกลับไปคุยเรื่องวงเงินและรายละเอียดก่อน</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="section cta-section">
        <div class="container">
            <h2 style="margin-bottom: 16px; color: white;">ต้องการใช้เล่มทะเบียนช่วยเสริมสภาพคล่อง?</h2>
            <p style="margin-bottom: 28px; font-size: 1.05rem;">
                ฝากเบอร์ติดต่อไว้ แล้วให้เจ้าหน้าที่ไมด้าโทรกลับไปช่วยดูวงเงินเบื้องต้นให้</p>
            <div class="cta-actions">
                <a href="register_title_loan.php" class="btn"
                    style="background: var(--accent-gold); color: white; min-width: 220px;">สมัครสินเชื่อ</a>
                <a href="contact_us.php" class="btn btn-secondary-light" style="min-width: 220px;">คุยกับเจ้าหน้าที่</a>
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
                            style="margin-right: 10px;"></i><?php echo htmlspecialchars($settings['site_phone']); ?></p>
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
