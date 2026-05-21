<?php
require_once 'admin/config/db.php';
$database = new Database();
$db = $database->getConnection();

// Track visitor
@include_once 'track_visitor.php';

// Fetch Settings
$settings = array();
try {
    $stmt_settings = $db->query("SELECT * FROM settings WHERE id = 1");
    $settings = $stmt_settings->fetch();
} catch (PDOException $e) {
}

$one_reports = array();
$reports_56_2 = array();
$financial_statements = array();

try {
    $stmt = $db->query("SELECT * FROM financial_reports ORDER BY id DESC");
    $reports = $stmt->fetchAll();
    foreach ($reports as $report) {
        $type = $report['report_type'];
        if ($type == 'รายการข้อมูลประจำปี ONE REPORT' || $type == 'Annual') {
            $one_reports[] = $report;
        } elseif ($type == 'รายงานประจำปี (แบบ 56-2)') {
            $reports_56_2[] = $report;
        } elseif ($type == 'งบการเงิน' || $type == 'Quarterly') {
            $financial_statements[] = $report;
        } else {
            // Default fallback
            $financial_statements[] = $report;
        }
    }
} catch (PDOException $e) {
    // Handle error
}

function renderFinancialReportList($reports, $emptyText, $iconClass, $toneClass)
{
    if (count($reports) > 0) {
        foreach ($reports as $report) {
            ?>
            <article class="financial-doc-item">
                <div class="financial-doc-main">
                    <span class="financial-doc-icon <?php echo $toneClass; ?>">
                        <i class="<?php echo $iconClass; ?>"></i>
                    </span>
                    <div>
                        <h3><?php echo htmlspecialchars($report['title']); ?></h3>
                        <p>เอกสารประกอบข้อมูลทางการเงินของบริษัท</p>
                    </div>
                </div>
                <a href="<?php echo htmlspecialchars($report['file_path']); ?>" target="_blank" class="financial-download">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i>
                    ดูเอกสาร
                </a>
            </article>
            <?php
        }
    } else {
        ?>
        <div class="financial-empty">
            <i class="fa-regular fa-folder-open"></i>
            <p><?php echo $emptyText; ?></p>
        </div>
        <?php
    }
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ข้อมูลทางการเงินและรายงานสำหรับนักลงทุน - MIDA LEASING</title>
    <meta name="description" content="ข้อมูลทางการเงิน งบการเงิน ONE REPORT และรายงานประจำปีของบริษัท ไมด้าลิสซิ่ง จำกัด (มหาชน)">

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
        .investor-financial-page {
            background: #f6f8fb;
            color: #183153;
        }

        .ir-financial-hero {
            position: relative;
            overflow: hidden;
            padding: 132px 0 72px;
            background:
                radial-gradient(circle at 16% 20%, rgba(255, 199, 50, 0.24), transparent 28%),
                linear-gradient(108deg, #f9fbff 0%, #eef5ff 62%, #1f5fb8 62%, #17488f 100%);
        }

        .ir-financial-hero::after {
            content: "";
            position: absolute;
            inset: auto -8% -42% 44%;
            height: 58%;
            background: rgba(255, 255, 255, 0.12);
            transform: skewX(-12deg);
            pointer-events: none;
        }

        .ir-financial-grid {
            position: relative;
            z-index: 1;
            display: grid;
            grid-template-columns: minmax(0, 0.92fr) minmax(360px, 0.78fr);
            gap: 42px;
            align-items: center;
        }

        .ir-financial-grid > div:first-child {
            max-width: 560px;
        }

        .ir-kicker {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 18px;
            color: var(--primary-blue);
            font-size: 0.88rem;
            font-weight: 800;
            letter-spacing: 0.18em;
            text-transform: uppercase;
        }

        .ir-kicker::before {
            content: "";
            width: 34px;
            height: 3px;
            border-radius: 999px;
            background: var(--accent-gold);
        }

        .ir-financial-hero h1 {
            max-width: 560px;
            margin: 0 0 18px;
            color: var(--primary-blue);
            font-size: clamp(2.25rem, 5vw, 4.4rem);
            line-height: 0.98;
            letter-spacing: -0.05em;
        }

        .ir-financial-hero p {
            max-width: 520px;
            margin: 0;
            color: #56677d;
            font-size: 1.08rem;
            line-height: 1.8;
        }

        .ir-hero-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 14px;
            margin-top: 30px;
        }

        .ir-primary-btn,
        .ir-secondary-btn,
        .financial-download {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            min-height: 46px;
            border-radius: 999px;
            font-weight: 800;
            text-decoration: none;
            transition: transform 0.18s ease, box-shadow 0.18s ease, background 0.18s ease;
        }

        .ir-primary-btn {
            padding: 0 24px;
            background: linear-gradient(135deg, #ffc830, #ffd966);
            color: #0f356d;
            box-shadow: 0 14px 30px rgba(255, 199, 50, 0.28);
        }

        .ir-secondary-btn {
            padding: 0 22px;
            background: #fff;
            color: var(--primary-blue);
            border: 1px solid rgba(24, 49, 83, 0.12);
            box-shadow: 0 12px 26px rgba(21, 63, 117, 0.08);
        }

        .ir-primary-btn:hover,
        .ir-secondary-btn:hover,
        .financial-download:hover {
            transform: translateY(-2px);
        }

        .ir-financial-panel {
            position: relative;
            padding: 32px;
            border: 1px solid rgba(255, 255, 255, 0.45);
            border-radius: 30px;
            background: rgba(255, 255, 255, 0.9);
            box-shadow: 0 28px 70px rgba(16, 48, 95, 0.24);
            backdrop-filter: blur(14px);
        }

        .ir-financial-panel h2 {
            margin: 0 0 18px;
            color: var(--primary-blue);
            font-size: 1.45rem;
        }

        .ir-panel-list {
            display: grid;
            gap: 14px;
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .ir-panel-list li {
            display: grid;
            grid-template-columns: 42px 1fr;
            gap: 14px;
            align-items: start;
            color: #253b58;
            line-height: 1.55;
        }

        .ir-panel-list i {
            display: inline-flex;
            width: 42px;
            height: 42px;
            align-items: center;
            justify-content: center;
            border-radius: 14px;
            background: #fff4cf;
            color: #b98600;
        }

        .ir-financial-main {
            padding: 64px 0 78px;
            background:
                linear-gradient(180deg, #f6f8fb 0%, #fff 52%, #f8fbff 100%);
        }

        .financial-intro {
            display: grid;
            grid-template-columns: minmax(0, 0.95fr) minmax(320px, 1.05fr);
            gap: 34px;
            align-items: end;
            margin-bottom: 36px;
        }

        .section-eyebrow {
            display: inline-flex;
            margin-bottom: 10px;
            color: #bc8600;
            font-weight: 800;
            letter-spacing: 0.1em;
            text-transform: uppercase;
        }

        .financial-intro h2 {
            margin: 0 0 12px;
            color: var(--primary-blue);
            font-size: clamp(1.8rem, 3vw, 2.7rem);
            line-height: 1.15;
            letter-spacing: -0.03em;
        }

        .financial-intro p {
            margin: 0;
            color: #617187;
            line-height: 1.75;
        }

        .financial-summary-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 14px;
        }

        .financial-summary {
            padding: 20px;
            border: 1px solid #dfe8f3;
            border-radius: 22px;
            background: #fff;
            box-shadow: 0 16px 36px rgba(21, 63, 117, 0.07);
        }

        .financial-summary strong {
            display: block;
            color: var(--primary-blue);
            font-size: 2rem;
            line-height: 1;
        }

        .financial-summary span {
            display: block;
            margin-top: 8px;
            color: #5f6f83;
            font-weight: 700;
        }

        .financial-doc-section {
            margin-top: 24px;
            border: 1px solid #dfe8f3;
            border-radius: 28px;
            background: rgba(255, 255, 255, 0.88);
            box-shadow: 0 20px 52px rgba(21, 63, 117, 0.08);
            overflow: hidden;
        }

        .financial-doc-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            padding: 24px 28px;
            background: linear-gradient(135deg, #ffffff 0%, #f0f6ff 100%);
            border-bottom: 1px solid #dfe8f3;
        }

        .financial-doc-heading h2 {
            margin: 0;
            color: var(--primary-blue);
            font-size: 1.35rem;
        }

        .financial-doc-heading p {
            margin: 6px 0 0;
            color: #68788e;
        }

        .financial-doc-count {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 80px;
            padding: 10px 16px;
            border-radius: 999px;
            background: #fff4cf;
            color: #8a6400;
            font-weight: 800;
            white-space: nowrap;
        }

        .financial-doc-list {
            display: grid;
        }

        .financial-doc-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 22px;
            padding: 22px 28px;
            border-bottom: 1px solid #edf2f7;
            transition: background 0.18s ease, transform 0.18s ease;
        }

        .financial-doc-item:last-child {
            border-bottom: 0;
        }

        .financial-doc-item:hover {
            background: #fbfdff;
            transform: translateX(4px);
        }

        .financial-doc-main {
            display: flex;
            gap: 16px;
            align-items: center;
            min-width: 0;
        }

        .financial-doc-icon {
            display: inline-flex;
            width: 52px;
            height: 52px;
            flex: 0 0 52px;
            align-items: center;
            justify-content: center;
            border-radius: 18px;
            font-size: 1.25rem;
        }

        .tone-green {
            background: #e9f8ef;
            color: #119047;
        }

        .tone-red {
            background: #fff0ed;
            color: #d94b35;
        }

        .tone-blue {
            background: #edf5ff;
            color: var(--primary-blue);
        }

        .financial-doc-main h3 {
            margin: 0;
            color: #183153;
            font-size: 1.05rem;
            line-height: 1.45;
        }

        .financial-doc-main p {
            margin: 4px 0 0;
            color: #718096;
            font-size: 0.92rem;
        }

        .financial-download {
            flex: 0 0 auto;
            min-width: 128px;
            padding: 0 18px;
            background: #f3f7ff;
            color: var(--primary-blue);
        }

        .financial-download:hover {
            background: var(--primary-blue);
            color: #fff;
            box-shadow: 0 14px 28px rgba(25, 76, 150, 0.18);
        }

        .financial-empty {
            display: grid;
            place-items: center;
            gap: 10px;
            padding: 34px 20px;
            color: #718096;
            text-align: center;
        }

        .financial-empty i {
            color: #a9b7c8;
            font-size: 2rem;
        }

        .financial-cta {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 24px;
            align-items: center;
            margin-top: 36px;
            padding: 28px;
            border-radius: 28px;
            background: linear-gradient(135deg, #183f7c, #2668bf);
            color: #fff;
            box-shadow: 0 22px 48px rgba(22, 73, 146, 0.2);
        }

        .financial-cta h2 {
            margin: 0 0 8px;
            color: #fff;
            font-size: 1.55rem;
        }

        .financial-cta p {
            margin: 0;
            color: rgba(255, 255, 255, 0.82);
            line-height: 1.65;
        }

        @media (max-width: 992px) {
            .ir-financial-hero {
                padding: 110px 0 56px;
                background:
                    radial-gradient(circle at 18% 12%, rgba(255, 199, 50, 0.26), transparent 34%),
                    linear-gradient(160deg, #f9fbff 0%, #eef5ff 62%, #dfefff 100%);
            }

            .ir-financial-grid,
            .financial-intro,
            .financial-cta {
                grid-template-columns: 1fr;
            }

            .ir-financial-panel {
                box-shadow: 0 20px 46px rgba(16, 48, 95, 0.14);
            }
        }

        @media (max-width: 768px) {
            .ir-financial-hero {
                padding: 96px 0 42px;
            }

            .ir-financial-hero h1 {
                font-size: 2.55rem;
            }

            .financial-summary-grid {
                grid-template-columns: 1fr;
            }

            .financial-doc-heading,
            .financial-doc-item {
                align-items: flex-start;
                flex-direction: column;
            }

            .financial-download {
                width: 100%;
            }

            .financial-cta {
                padding: 24px;
            }
        }
    </style>
</head>

<body class="investor-financial-page">

    <!-- Header -->
    <?php include 'includes/nav.php'; ?>

    <!-- Page Header -->
    <section class="ir-financial-hero">
        <div class="container">
            <div class="ir-financial-grid">
                <div>
                    <span class="ir-kicker">MIDA LEASING INVESTOR RELATIONS</span>
                    <h1>ข้อมูลทางการเงิน</h1>
                    <p>
                        ศูนย์รวมข้อมูลทางการเงิน รายงานประจำปี และเอกสารสำคัญของบริษัท สำหรับผู้ถือหุ้น นักลงทุน และผู้สนใจใช้ประกอบการติดตามผลการดำเนินงานอย่างต่อเนื่อง
                    </p>
                    <div class="ir-hero-actions">
                        <a href="#financial-documents" class="ir-primary-btn">
                            <i class="fa-solid fa-file-arrow-down"></i>
                            ดูรายงานทางการเงิน
                        </a>
                        <a href="investor_business.php" class="ir-secondary-btn">
                            ข้อมูลธุรกิจ
                        </a>
                    </div>
                </div>

                <aside class="ir-financial-panel">
                    <h2>ข้อมูลสำหรับนักลงทุน</h2>
                    <ul class="ir-panel-list">
                        <li>
                            <i class="fa-solid fa-chart-line"></i>
                            <span><strong>งบการเงิน</strong><br>ข้อมูลฐานะการเงินและผลการดำเนินงานตามรอบการรายงาน</span>
                        </li>
                        <li>
                            <i class="fa-solid fa-book-open"></i>
                            <span><strong>ONE REPORT</strong><br>ภาพรวมธุรกิจ การกำกับดูแลกิจการ และประเด็นสำคัญประจำปี</span>
                        </li>
                        <li>
                            <i class="fa-solid fa-file-shield"></i>
                            <span><strong>รายงานประจำปี</strong><br>ข้อมูลบริษัท ผลการดำเนินงาน และสาระสำคัญสำหรับผู้ถือหุ้น</span>
                        </li>
                    </ul>
                </aside>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <main id="financial-documents" class="ir-financial-main">
        <div class="container">
            <div class="financial-intro">
                <div>
                    <span class="section-eyebrow">Financial Documents</span>
                    <h2>รายงานและเอกสารทางการเงิน</h2>
                    <p>
                        บริษัทเผยแพร่ข้อมูลทางการเงินและรายงานสำคัญ เพื่อสนับสนุนความโปร่งใสและช่วยให้ผู้ลงทุนติดตามพัฒนาการของธุรกิจได้อย่างต่อเนื่อง
                    </p>
                </div>

                <div class="financial-summary-grid">
                    <div class="financial-summary">
                        <strong><?php echo count($financial_statements); ?></strong>
                        <span>งบการเงิน</span>
                    </div>
                    <div class="financial-summary">
                        <strong><?php echo count($one_reports); ?></strong>
                        <span>ONE REPORT</span>
                    </div>
                    <div class="financial-summary">
                        <strong><?php echo count($reports_56_2); ?></strong>
                        <span>รายงานประจำปี</span>
                    </div>
                </div>
            </div>

            <section class="financial-doc-section">
                <div class="financial-doc-heading">
                    <div>
                        <h2>งบการเงิน</h2>
                        <p>รายงานฐานะการเงิน ผลการดำเนินงาน และข้อมูลที่เกี่ยวข้องตามรอบระยะเวลาบัญชี</p>
                    </div>
                    <span class="financial-doc-count"><?php echo count($financial_statements); ?> รายการ</span>
                </div>
                <div class="financial-doc-list">
                    <?php renderFinancialReportList($financial_statements, 'ไม่พบงบการเงินในขณะนี้', 'fa-solid fa-file-lines', 'tone-green'); ?>
                </div>
            </section>

            <section class="financial-doc-section">
                <div class="financial-doc-heading">
                    <div>
                        <h2>ONE REPORT</h2>
                        <p>รายงานประจำปีแบบบูรณาการ ครอบคลุมธุรกิจ การกำกับดูแลกิจการ และข้อมูลสำคัญของบริษัท</p>
                    </div>
                    <span class="financial-doc-count"><?php echo count($one_reports); ?> รายการ</span>
                </div>
                <div class="financial-doc-list">
                    <?php renderFinancialReportList($one_reports, 'ไม่พบ ONE REPORT ในขณะนี้', 'fa-solid fa-file-pdf', 'tone-red'); ?>
                </div>
            </section>

            <section class="financial-doc-section">
                <div class="financial-doc-heading">
                    <div>
                        <h2>รายงานประจำปี</h2>
                        <p>รายงานสรุปข้อมูลบริษัทและผลการดำเนินงาน เพื่อใช้ประกอบการติดตามข้อมูลสำหรับผู้ถือหุ้น</p>
                    </div>
                    <span class="financial-doc-count"><?php echo count($reports_56_2); ?> รายการ</span>
                </div>
                <div class="financial-doc-list">
                    <?php renderFinancialReportList($reports_56_2, 'ไม่พบรายงานประจำปีในขณะนี้', 'fa-solid fa-file-contract', 'tone-blue'); ?>
                </div>
            </section>

            <section class="financial-cta">
                <div>
                    <h2>ศึกษาข้อมูลบริษัทเพิ่มเติม</h2>
                    <p>ผู้ลงทุนสามารถศึกษาภาพรวมธุรกิจและเอกสารเผยแพร่อื่น ๆ เพื่อประกอบความเข้าใจเกี่ยวกับทิศทางและการดำเนินงานของไมด้าลิสซิ่ง</p>
                </div>
                <div class="ir-hero-actions" style="margin-top: 0;">
                    <a href="investor_business.php" class="ir-primary-btn">ข้อมูลธุรกิจ</a>
                    <a href="investor_publications.php" class="ir-secondary-btn">เอกสารเผยแพร่</a>
                </div>
            </section>
        </div>
    </main>


    <!-- Footer -->
    <footer id="footer">
        <div class="container">
            <div class="footer-content">
                <div>
                    <div class="footer-logo">MIDA LEASING</div>
                    <p style="color: #ccc; margin-bottom: 10px;">บริษัท ไมด้าลิสซิ่ง จำกัด (มหาชน)</p>
                    <p style="color: #ccc; margin-bottom: 10px; font-size: 1rem;">48/1-5 ซอยแจ้งวัฒนะ 14
                        ถนนแจ้งวัฒนะ
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
