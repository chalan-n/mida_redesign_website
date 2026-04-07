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
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>บริการต่อภาษีและประกันภัย - MIDA LEASING</title>
    <meta name="description"
        content="บริการครบวงจรจากไมด้าลิสซิ่ง รับต่อภาษีรถยนต์ พ.ร.บ. และประกันภัยรถยนต์ทุกประเภท สะดวก รวดเร็ว มั่นใจได้">

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
            background: linear-gradient(135deg, #1c4587 0%, #004a99 100%);
            color: white;
            padding: 140px 0 60px;
            text-align: center;
        }

        .service-card {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
            border: 1px solid #eee;
            transition: transform 0.3s;
        }

        .service-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .service-icon {
            font-size: 2.5rem;
            color: var(--primary-blue);
            margin-bottom: 20px;
        }

        .check-list {
            list-style: none;
            padding: 0;
            margin: 20px 0;
        }

        .check-list li {
            margin-bottom: 10px;
            position: relative;
            padding-left: 30px;
            color: #555;
        }

        .check-list li::before {
            content: '\f00c';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            left: 0;
            color: var(--accent-gold);
        }

        .contact-box {
            background: linear-gradient(135deg, #f0f7ff 0%, #e6f0fa 100%);
            padding: 40px;
            border-radius: 16px;
            text-align: center;
            border: 1px solid #d0e3ff;
            margin-top: 40px;
        }
    </style>
</head>

<body>

    <!-- Header -->
    <?php $active_page = 'services'; include 'includes/nav.php'; ?>

    <!-- Page Header -->
    <div class="page-header">
        <div class="container">
            <h1 style="font-size: 2.5rem; margin-bottom: 10px; font-weight: 700; color: #fec435;">One Stop Service</h1>
            <p style="opacity: 0.9; font-size: 1.1rem; max-width: 700px; margin: 0 auto;">
                บริการครบ จบในที่เดียว ทั้งต่อภาษี พ.ร.บ. และประกันภัยรถยนต์<br>
                ดูแลทุกขั้นตอนโดยทีมงานมืออาชีพ
            </p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="section" style="background-color: #f8f9fa;">
        <div class="container">

            <div class="section-title">
                <h2>บริการของเรา</h2>
                <p>อำนวยความสะดวกให้คุณ ไม่ต้องเสียเวลาเดินทางหลายที่</p>
            </div>

            <div class="features-grid">

                <!-- Service 1: Tax & Registration -->
                <div class="service-card" style="padding: 40px;">
                    <div class="service-icon">
                        <i class="fa-solid fa-file-invoice-dollar"></i>
                    </div>
                    <h3 style="font-size: 1.5rem; margin-bottom: 15px;">ต่อทะเบียนและภาษีรถยนต์</h3>
                    <p style="color: #666; margin-bottom: 20px;">
                        บริการรับต่อภาษีรถยนต์ประจำปี ดำเนินการรวดเร็ว ไม่ต้องไปขนส่งเอง เอกสารครบ
                        รับเล่ม/ป้ายวงกลมได้เลย
                    </p>
                    <ul class="check-list">
                        <li>รับต่อภาษีรถยนต์ทุกประเภท</li>
                        <li>บริการตรวจสภาพรถ (ตรอ.)</li>
                        <li>แจ้งย้าย โอนกรรมสิทธิ์ เปลี่ยนสี เปลี่ยนเครื่อง</li>
                        <li>คัดสำเนาเล่มทะเบียน</li>
                    </ul>
                </div>

                <!-- Service 2: Compulsory Insurance (Por Ror Bor) -->
                <div class="service-card" style="padding: 40px;">
                    <div class="service-icon">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>
                    <h3 style="font-size: 1.5rem; margin-bottom: 15px;">พ.ร.บ. รถยนต์</h3>
                    <p style="color: #666; margin-bottom: 20px;">
                        บริการทำ พ.ร.บ. รถยนต์ภาคบังคับ คุ้มครองผู้ประสบภัยจากรถ ถูกต้องตามกฎหมาย รับกรมธรรม์ทันที
                    </p>
                    <ul class="check-list">
                        <li>คุ้มครองทันทีที่ทำ</li>
                        <li>เบี้ยประกันราคามาตรฐาน</li>
                        <li>ใช้ต่อภาษีได้ทันที</li>
                        <li>รองรับรถยนต์และรถจักรยานยนต์ทุกประเภท</li>
                    </ul>
                </div>

                <!-- Service 3: Voluntary Insurance -->
                <div class="service-card" style="padding: 40px;">
                    <div class="service-icon">
                        <i class="fa-solid fa-car-burst"></i>
                    </div>
                    <h3 style="font-size: 1.5rem; margin-bottom: 15px;">ประกันภัยรถยนต์ภาคสมัครใจ</h3>
                    <p style="color: #666; margin-bottom: 20px;">
                        อุ่นใจทุกการเดินทางด้วยประกันภัยรถยนต์ชั้นนำ มีให้เลือกครบทุกระดับความคุ้มครอง
                        ตามความต้องการของคุณ
                    </p>
                    <ul class="check-list">
                        <li>ประกันชั้น 1: คุ้มครองครบ จบทุกกรณี</li>
                        <li>ประกันชั้น 2+: คุ้มครองรถชนรถ สูญหาย ไฟไหม้</li>
                        <li>ประกันชั้น 3+: คุ้มครองรถชนรถ ราคาประหยัด</li>
                        <li>มีบริษัทประกันชั้นนำให้เลือกหลากหลาย</li>
                    </ul>
                </div>

            </div>

            <!-- CTA -->
            <div class="contact-box">
                <i class="fa-solid fa-headset"
                    style="font-size: 3rem; color: var(--primary-blue); margin-bottom: 20px;"></i>
                <h2 style="margin-bottom: 15px; color: var(--primary-blue);">สนใจใช้บริการ หรือสอบถามข้อมูลเพิ่มเติม
                </h2>
                <p style="font-size: 1.1rem; color: #555; margin-bottom: 30px;">
                    ติดต่อเจ้าหน้าที่ของเราได้ทันที พร้อมให้บริการและคำแนะนำฟรี
                </p>
                <div style="display: flex; gap: 20px; justify-content: center; flex-wrap: wrap;">
                    <a href="tel:025746901" class="btn btn-primary">
                        <i class="fa-solid fa-phone" style="margin-right: 10px;"></i> 02-574-6901
                    </a>
                    <a href="https://line.me/R/ti/p/@midaleasing" target="_blank" class="btn btn-accent"
                        style="color: var(--primary-blue);">
                        <i class="fa-brands fa-line" style="margin-right: 10px; font-size: 1.2rem;"></i> @midaleasing
                    </a>
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
                    <p style="color: #ccc; margin-bottom: 10px; font-size: 1rem;">48/1-5 ซอยแจ้งวัฒนะ 14 ถนนแจ้งวัฒนะ
                        แขวงทุ่งสองห้อง
                        เขตหลักสี่ กรุงเทพฯ 10210</p>
                    <p style="color: #ccc; margin-bottom: 20px; font-size: 1rem;"><i class="fa-solid fa-phone"
                            style="margin-right: 10px;"></i>02-574-6901</p>
                    <div style="display: flex; gap: 15px;">
                        <a href="https://www.facebook.com/midaleasing.th" target="_blank"
                            style="text-decoration: none;">
                            <i class="fa-brands fa-facebook" style="font-size: 2rem; color: #1877F2;"></i>
                        </a>
                        <a href="https://line.me/R/ti/p/@midaleasing" target="_blank" style="text-decoration: none;">
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