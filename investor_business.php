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
    <title>วิสัยทัศน์และพันธกิจ - MIDA LEASING</title>
    <meta name="description"
        content="วิสัยทัศน์และพันธกิจของไมด้าลีสซิ่ง มุ่งสู่การเป็นผู้นำสินเชื่อเช่าซื้อดิจิทัล Neo-Fintech ที่ทันสมัยและครบวงจร">

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
        .hero-section {
            background: linear-gradient(135deg, #002D62 0%, #004a99 100%);
            color: white;
            padding: 160px 0 100px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('img/pattern-bg.png');
            /* Fallback/Optional pattern */
            opacity: 0.1;
        }

        .concept-card {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            height: 100%;
            transition: transform 0.3s ease;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .concept-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.12);
        }

        .concept-icon-wrapper {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #f0f7ff 0%, #e6f0fa 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 25px;
            color: var(--primary-blue);
            font-size: 2rem;
        }

        .section-header {
            text-align: center;
            margin-bottom: 60px;
        }

        .section-header h2 {
            font-size: 2.5rem;
            color: var(--primary-blue);
            margin-bottom: 20px;
            font-weight: 700;
        }

        .section-header p {
            color: #666;
            font-size: 1.1rem;
            max-width: 800px;
            margin: 0 auto;
        }

        .stat-box {
            background: var(--primary-blue);
            color: white;
            border-radius: 16px;
            padding: 40px;
            text-align: center;
        }

        .stat-number {
            font-size: 3rem;
            font-weight: 700;
            color: var(--accent-gold);
            margin-bottom: 10px;
        }

        .feature-list {
            list-style: none;
            padding: 0;
            text-align: left;
            margin-top: 20px;
        }

        .feature-list li {
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            color: #555;
        }

        .feature-list li i {
            color: var(--accent-gold);
            margin-right: 12px;
            font-size: 1.1rem;
        }
    </style>
</head>

<body>

    <!-- Header -->
    <?php include 'includes/nav.php'; ?>

    <!-- Hero Section -->
    <div class="hero-section">
        <div class="container" style="position: relative; z-index: 2;">
            <h1 style="font-size: 3.5rem; margin-bottom: 20px; font-weight: 800; color: var(--accent-gold);">
                Digital-First Financial Experience
            </h1>
            <p style="font-size: 1.3rem; opacity: 0.9; max-width: 800px; margin: 0 auto 40px;">
                มิติใหม่ของบริการทางการเงินจาก ไมด้า ลิสซิ่ง <br>
                ที่ผสานความเชี่ยวชาญกว่า 20 ปี เข้ากับนวัตกรรมดิจิทัลที่ทันสมัย
            </p>
        </div>
    </div>

    <!-- Vision Section -->
    <section class="section">
        <div class="container">
            <div class="section-header">
                <h2>Neo-Fintech Vision</h2>
                <p>
                    เรามุ่งมั่นยกระดับภาพลักษณ์องค์กรสู่ความเป็นผู้นำด้านสินเชื่อเช่าซื้อดิจิทัล
                    ด้วยการออกแบบบริการที่ทันสมัย โปร่งใส และเข้าถึงง่าย (Modern, Trustworthy & Accessible)
                    เพื่อตอบโจทย์ลูกค้าในยุคดิจิทัลอย่างแท้จริง
                </p>
            </div>

            <div class="features-grid">

                <!-- Pillar 1: Core Financial Services -->
                <div class="concept-card">
                    <div class="concept-icon-wrapper">
                        <i class="fa-solid fa-coins"></i>
                    </div>
                    <h3>Comprehensive Financial Solutions</h3>
                    <p style="color: #666; margin-bottom: 20px;">โซลูชันทางการเงินที่ครบวงจร</p>
                    <ul class="feature-list">
                        <li><i class="fa-solid fa-check-circle"></i> สินเชื่อเช่าซื้อ (Hire Purchase)</li>
                        <li><i class="fa-solid fa-check-circle"></i> สินเชื่อจำนำทะเบียน (Title Loan)</li>
                        <li><i class="fa-solid fa-check-circle"></i> สินเชื่อส่วนบุคคล (Personal Loan)</li>
                    </ul>
                </div>

                <!-- Pillar 2: One Stop Service -->
                <div class="concept-card">
                    <div class="concept-icon-wrapper">
                        <i class="fa-solid fa-file-invoice"></i> <!-- Changed to invoice as per previous request -->
                    </div>
                    <h3>One Stop Service Ecosystem</h3>
                    <p style="color: #666; margin-bottom: 20px;">ครบ จบ เรื่องรถ ในที่เดียว</p>
                    <ul class="feature-list">
                        <li><i class="fa-solid fa-check-circle"></i> ต่อภาษีและทะเบียนรถยนต์</li>
                        <li><i class="fa-solid fa-check-circle"></i> ประกันภัยรถยนต์ภาคสมัครใจ</li>
                        <li><i class="fa-solid fa-check-circle"></i> ประกันภัย พ.ร.บ.</li>
                    </ul>
                </div>

                <!-- Pillar 3: Asset Management -->
                <div class="concept-card">
                    <div class="concept-icon-wrapper">
                        <i class="fa-solid fa-building-columns"></i>
                    </div>
                    <h3>Asset Management</h3>
                    <p style="color: #666; margin-bottom: 20px;">บริหารและจำหน่ายทรัพย์สิน</p>
                    <ul class="feature-list">
                        <li><i class="fa-solid fa-check-circle"></i> ประมูลรถยนต์มือสอง (Auction)</li>
                        <li><i class="fa-solid fa-check-circle"></i> บริหารทรัพย์สินรอการขาย (NPA)</li>
                        <li><i class="fa-solid fa-check-circle"></i> อสังหาริมทรัพย์คุณภาพ</li>
                    </ul>
                </div>

            </div>
        </div>
    </section>

    <!-- Trust Section -->
    <section class="section" style="background-color: #f8f9fa;">
        <div class="container">
            <div class="row" style="display: flex; align-items: center; flex-wrap: wrap; gap: 40px;">
                <div style="flex: 1; min-width: 300px;">
                    <h2 style="font-size: 2.5rem; color: var(--primary-blue); margin-bottom: 20px;">Your Trusted
                        Financial Partner</h2>
                    <p style="font-size: 1.1rem; color: #555; line-height: 1.8; margin-bottom: 30px;">
                        ในฐานะบริษัทจดทะเบียนในตลาดหลักทรัพย์ (Public Company Limited)
                        เรายึดมั่นในหลักธรรมาภิบาลและความโปร่งใส
                        พร้อมนำเทคโนโลยีมาใช้เพื่อสร้างประสบการณ์ที่ดีที่สุดให้กับลูกค้า ตั้งแต่การสมัครสินเชื่อออนไลน์
                        ไปจนถึงการบริการหลังการขายที่รวดเร็ว
                    </p>
                    <a href="investor_financial.php" class="btn btn-primary">ข้อมูลนักลงทุนสัมพันธ์</a>
                </div>
                <div style="flex: 1; min-width: 300px;">
                    <div class="stat-box">
                        <i class="fa-solid fa-users-gear"
                            style="font-size: 3rem; margin-bottom: 20px; opacity: 0.8;"></i>
                        <h3>Professional Team</h3>
                        <p>ทีมงานมืออาชีพพร้อมให้บริการ</p>
                    </div>
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