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
            background:
                radial-gradient(circle at 12% 10%, rgba(255, 199, 44, 0.22), transparent 28%),
                radial-gradient(circle at 82% 18%, rgba(47, 107, 198, 0.18), transparent 30%),
                linear-gradient(135deg, #fff9ea 0%, #f8fbff 52%, #edf5ff 100%);
            color: var(--text-dark);
            padding: 150px 0 86px;
            position: relative;
            overflow: hidden;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                linear-gradient(120deg, rgba(23, 69, 143, 0.04) 0 1px, transparent 1px 120px),
                linear-gradient(102deg, transparent 0%, transparent 56%, rgba(255, 255, 255, 0.6) 56.2%, rgba(255, 255, 255, 0.12) 100%);
            pointer-events: none;
        }

        .investor-hero-inner {
            position: relative;
            z-index: 2;
            display: grid;
            grid-template-columns: minmax(0, 1.1fr) minmax(320px, 0.9fr);
            gap: clamp(28px, 5vw, 64px);
            align-items: center;
        }

        .investor-kicker {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 16px;
            color: var(--primary-blue);
            font-size: 0.88rem;
            font-weight: 800;
            letter-spacing: 0.14em;
        }

        .investor-kicker::before {
            content: '';
            width: 34px;
            height: 3px;
            border-radius: 999px;
            background: var(--accent-gold);
        }

        .investor-hero-title {
            max-width: 760px;
            margin: 0 0 20px;
            color: var(--primary-blue);
            font-size: clamp(2.1rem, 4.5vw, 4rem);
            line-height: 1.08;
            letter-spacing: -0.04em;
            font-weight: 800;
        }

        .investor-hero-copy {
            max-width: 720px;
            margin: 0;
            color: #536274;
            font-size: clamp(1rem, 1.8vw, 1.18rem);
            line-height: 1.85;
        }

        .investor-hero-panel {
            padding: 28px;
            border: 1px solid rgba(23, 69, 143, 0.08);
            border-radius: 28px;
            background: rgba(255, 255, 255, 0.86);
            box-shadow: 0 24px 58px rgba(23, 69, 143, 0.13);
            backdrop-filter: blur(12px);
        }

        .investor-panel-label {
            margin: 0 0 16px;
            color: var(--primary-blue);
            font-weight: 800;
        }

        .investor-panel-list {
            display: grid;
            gap: 14px;
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .investor-panel-list li {
            display: flex;
            gap: 12px;
            align-items: flex-start;
            color: #24364d;
            font-weight: 700;
            line-height: 1.55;
        }

        .investor-panel-list i {
            margin-top: 4px;
            color: var(--accent-gold);
        }

        .strategy-strip {
            position: relative;
            z-index: 3;
            margin-top: -42px;
        }

        .strategy-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 18px;
        }

        .strategy-item {
            padding: 24px;
            border: 1px solid rgba(23, 69, 143, 0.08);
            border-radius: 24px;
            background: rgba(255, 255, 255, 0.96);
            box-shadow: 0 18px 42px rgba(23, 69, 143, 0.09);
        }

        .strategy-item span {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 42px;
            height: 42px;
            margin-bottom: 14px;
            border-radius: 14px;
            background: linear-gradient(135deg, var(--accent-gold) 0%, #ffe07a 100%);
            color: #0f2d5c;
            font-weight: 900;
        }

        .strategy-item h3 {
            margin: 0 0 8px;
            color: var(--primary-blue);
            font-size: 1.1rem;
        }

        .strategy-item p {
            margin: 0;
            color: #536274;
            line-height: 1.7;
            font-size: 0.95rem;
        }

        .concept-card {
            background: white;
            border-radius: 26px;
            padding: 32px;
            box-shadow: 0 16px 38px rgba(23, 69, 143, 0.08);
            height: 100%;
            transition: transform 0.3s ease;
            border: 1px solid rgba(23, 69, 143, 0.08);
        }

        .concept-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 22px 48px rgba(23, 69, 143, 0.13);
        }

        .concept-icon-wrapper {
            width: 68px;
            height: 68px;
            background: linear-gradient(135deg, #fff7db 0%, #ffffff 100%);
            border: 1px solid rgba(255, 199, 44, 0.34);
            border-radius: 22px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 22px;
            color: var(--primary-blue);
            font-size: 1.55rem;
        }

        .concept-card h3 {
            margin-bottom: 12px;
            color: var(--primary-blue);
            font-size: clamp(1.25rem, 2vw, 1.55rem);
        }

        .section-header {
            text-align: center;
            margin-bottom: 46px;
        }

        .section-header h2 {
            font-size: 2.5rem;
            color: var(--primary-blue);
            margin-bottom: 20px;
            font-weight: 700;
        }

        .section-header p {
            color: #536274;
            font-size: 1.1rem;
            max-width: 800px;
            margin: 0 auto;
            line-height: 1.8;
        }

        .stat-box {
            background:
                radial-gradient(circle at 94% 0%, rgba(255, 199, 44, 0.18), transparent 30%),
                linear-gradient(135deg, #17458f 0%, #2f6bc6 100%);
            color: white;
            border-radius: 28px;
            padding: 38px;
            box-shadow: 0 22px 50px rgba(23, 69, 143, 0.18);
        }

        .trust-layout {
            display: grid;
            grid-template-columns: minmax(0, 1.08fr) minmax(320px, 0.92fr);
            gap: 42px;
            align-items: center;
        }

        .trust-copy h2 {
            font-size: clamp(2rem, 3.5vw, 2.8rem);
            color: var(--primary-blue);
            margin-bottom: 18px;
            line-height: 1.18;
        }

        .trust-copy p {
            color: #536274;
            font-size: 1.05rem;
            line-height: 1.85;
            margin-bottom: 26px;
        }

        .stat-box h3 {
            color: #ffffff;
            margin-bottom: 12px;
            font-size: 1.45rem;
        }

        .stat-box p {
            color: rgba(255, 255, 255, 0.86);
            line-height: 1.75;
            margin: 0;
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

        .investor-cta-row {
            display: flex;
            flex-wrap: wrap;
            gap: 14px;
            align-items: center;
        }

        .investor-secondary-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--primary-blue);
            font-weight: 800;
            text-decoration: none;
        }

        .investor-secondary-link:hover {
            color: var(--primary-light);
            text-decoration: underline;
        }

        @media (max-width: 900px) {
            .investor-hero-inner,
            .trust-layout,
            .strategy-grid {
                grid-template-columns: 1fr;
            }

            .hero-section {
                padding: 124px 0 58px;
            }

            .strategy-strip {
                margin-top: -22px;
            }
        }
    </style>
</head>

<body>

    <!-- Header -->
    <?php include 'includes/nav.php'; ?>

    <!-- Hero Section -->
    <div class="hero-section">
        <div class="container investor-hero-inner">
            <div>
                <p class="investor-kicker">INVESTOR RELATIONS</p>
                <h1 class="investor-hero-title">ภาพรวมธุรกิจและทิศทางการเติบโต</h1>
                <p class="investor-hero-copy">
                    บริษัท ไมด้าลิสซิ่ง จำกัด (มหาชน) ดำเนินธุรกิจสินเชื่อและบริการที่เกี่ยวข้องกับรถยนต์
                    โดยมุ่งเน้นการให้บริการที่เข้าถึงง่าย โปร่งใส และต่อยอดสู่ช่องทางดิจิทัล
                    เพื่อสร้างฐานรายได้ที่มั่นคงและรองรับการเติบโตในระยะยาว
                </p>
            </div>
            <div class="investor-hero-panel">
                <p class="investor-panel-label">จุดเด่นของธุรกิจ</p>
                <ul class="investor-panel-list">
                    <li><i class="fa-solid fa-circle-check"></i> ครอบคลุมสินเชื่อรถยนต์ จำนำทะเบียน และสินเชื่อส่วนบุคคล</li>
                    <li><i class="fa-solid fa-circle-check"></i> มีบริการต่อเนื่องด้านภาษี พ.ร.บ. และประกันภัยรถยนต์</li>
                    <li><i class="fa-solid fa-circle-check"></i> บริหารทรัพย์และรถประมูลเพื่อเพิ่มโอกาสทางธุรกิจ</li>
                </ul>
            </div>
        </div>
    </div>

    <section class="strategy-strip">
        <div class="container">
            <div class="strategy-grid">
                <article class="strategy-item">
                    <span>1</span>
                    <h3>ฐานธุรกิจชัดเจน</h3>
                    <p>มุ่งเน้นธุรกิจสินเชื่อที่เกี่ยวข้องกับรถยนต์ ซึ่งเป็นตลาดที่บริษัทมีความเชี่ยวชาญและประสบการณ์ต่อเนื่อง</p>
                </article>
                <article class="strategy-item">
                    <span>2</span>
                    <h3>บริการครบวงจร</h3>
                    <p>ต่อยอดจากสินเชื่อสู่บริการหลังการขาย ภาษี ประกันภัย และงานเอกสาร เพื่อเพิ่มความสะดวกให้ลูกค้า</p>
                </article>
                <article class="strategy-item">
                    <span>3</span>
                    <h3>ยกระดับด้วยดิจิทัล</h3>
                    <p>พัฒนาช่องทางออนไลน์และระบบงานภายใน เพื่อให้การให้บริการรวดเร็ว ตรวจสอบได้ และรองรับการเติบโต</p>
                </article>
            </div>
        </div>
    </section>

    <!-- Vision Section -->
    <section class="section">
        <div class="container">
            <div class="section-header">
                <h2>โครงสร้างธุรกิจของไมด้าลิสซิ่ง</h2>
                <p>
                    ธุรกิจของบริษัทถูกออกแบบให้เชื่อมโยงตั้งแต่การให้สินเชื่อ การดูแลลูกค้าหลังการขาย
                    ไปจนถึงการบริหารทรัพย์สิน เพื่อเพิ่มประสิทธิภาพการดำเนินงานและสร้างมูลค่าอย่างต่อเนื่อง
                </p>
            </div>

            <div class="features-grid">

                <!-- Pillar 1: Core Financial Services -->
                <div class="concept-card">
                    <div class="concept-icon-wrapper">
                        <i class="fa-solid fa-coins"></i>
                    </div>
                    <h3>ธุรกิจสินเชื่อหลัก</h3>
                    <p style="color: #666; margin-bottom: 20px;">ให้บริการสินเชื่อที่ตอบโจทย์ทั้งลูกค้ารายย่อยและผู้ประกอบการ</p>
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
                    <h3>บริการต่อเนื่องเกี่ยวกับรถ</h3>
                    <p style="color: #666; margin-bottom: 20px;">เพิ่มความสะดวกให้ลูกค้าและต่อยอดความสัมพันธ์ระยะยาว</p>
                    <ul class="feature-list">
                        <li><i class="fa-solid fa-check-circle"></i> ต่อภาษีและทะเบียนรถยนต์</li>
                        <li><i class="fa-solid fa-check-circle"></i> ประกันภัยรถยนต์ภาคสมัครใจ</li>
                        <li><i class="fa-solid fa-check-circle"></i> บริการ พ.ร.บ. และงานเอกสารที่เกี่ยวข้อง</li>
                    </ul>
                </div>

                <!-- Pillar 3: Asset Management -->
                <div class="concept-card">
                    <div class="concept-icon-wrapper">
                        <i class="fa-solid fa-building-columns"></i>
                    </div>
                    <h3>บริหารทรัพย์และรถประมูล</h3>
                    <p style="color: #666; margin-bottom: 20px;">สร้างช่องทางจำหน่ายทรัพย์สินที่โปร่งใสและตรวจสอบได้</p>
                    <ul class="feature-list">
                        <li><i class="fa-solid fa-check-circle"></i> ประมูลรถยนต์มือสอง (Auction)</li>
                        <li><i class="fa-solid fa-check-circle"></i> บริหารทรัพย์สินรอการขาย (NPA)</li>
                        <li><i class="fa-solid fa-check-circle"></i> บ้าน คอนโด และที่ดินราคาพิเศษ</li>
                    </ul>
                </div>

            </div>
        </div>
    </section>

    <!-- Trust Section -->
    <section class="section" style="background: linear-gradient(180deg, #f8fbff 0%, #ffffff 100%);">
        <div class="container">
            <div class="trust-layout">
                <div class="trust-copy">
                    <h2>เติบโตบนพื้นฐานของความโปร่งใสและความรับผิดชอบ</h2>
                    <p>
                        ในฐานะบริษัทมหาชน บริษัทให้ความสำคัญกับการกำกับดูแลกิจการที่ดี
                        การบริหารความเสี่ยงอย่างเหมาะสม และการพัฒนาช่องทางบริการให้สอดคล้องกับพฤติกรรมลูกค้ายุคใหม่
                        เพื่อสนับสนุนการเติบโตที่มั่นคงและยั่งยืน
                    </p>
                    <div class="investor-cta-row">
                        <a href="investor_financial.php" class="btn btn-primary">ดูข้อมูลทางการเงิน</a>
                        <a href="investor_publications.php" class="investor-secondary-link">
                            เอกสารเผยแพร่ <i class="fa-solid fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
                <div>
                    <div class="stat-box">
                        <i class="fa-solid fa-users-gear"
                            style="font-size: 3rem; margin-bottom: 20px; opacity: 0.8;"></i>
                        <h3>ทีมงานและเครือข่ายบริการ</h3>
                        <p>
                            บริษัทมีทีมงานที่เข้าใจตลาดสินเชื่อรถยนต์และลูกค้ารายย่อย
                            พร้อมพัฒนากระบวนการทำงานให้รวดเร็ว ตรวจสอบได้ และรองรับการขยายบริการในอนาคต
                        </p>
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
                        <li><a href="investor_company.php">ข้อมูลบริษัท</a></li>
                        <li><a href="investor_financial.php">ข้อมูลทางการเงิน</a></li>
                        <li><a href="investor_publications.php">เอกสารเผยแพร่</a></li>
                            <li><a href="investor_contact.php">ติดต่อนักลงทุนสัมพันธ์</a></li>
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
