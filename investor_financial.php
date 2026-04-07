<?php
require_once 'admin/config/db.php';
$database = new Database();
$db = $database->getConnection();

// Track visitor
@include_once 'track_visitor.php';

// Fetch Settings
$settings = [];
try {
    $stmt_settings = $db->query("SELECT * FROM settings WHERE id = 1");
    $settings = $stmt_settings->fetch();
} catch (PDOException $e) {
}

$one_reports = [];
$reports_56_2 = [];
$financial_statements = [];

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
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ข้อมูลทางการเงิน - MIDA LEASING</title>
    <meta name="description" content="ข้อมูลทางการเงิน งบการเงิน และรายงานประจำปี บริษัท ไมด้าลิสซิ่ง จำกัด (มหาชน)">

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

        .document-list {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            border: 1px solid #eee;
        }

        .document-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 20px 30px;
            border-bottom: 1px solid #eee;
            transition: background-color 0.2s;
        }

        .document-item:last-child {
            border-bottom: none;
        }

        .document-item:hover {
            background-color: #f8f9fa;
        }

        .doc-icon {
            font-size: 2rem;
            color: #ff5252;
            /* PDF color */
            margin-right: 20px;
        }

        .doc-info {
            flex-grow: 1;
        }

        .doc-title {
            font-size: 1.1rem;
            font-weight: 500;
            color: #333;
            margin-bottom: 5px;
        }

        .doc-date {
            font-size: 0.9rem;
            color: #888;
        }

        .btn-download {
            background-color: #f0f7ff;
            color: var(--primary-blue);
            padding: 8px 15px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.9rem;
            transition: all 0.2s;
        }

        .btn-download:hover {
            background-color: var(--primary-blue);
            color: white;
        }

        @media (max-width: 768px) {
            .document-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }

            .btn-download {
                width: 100%;
                text-align: center;
            }
        }
    </style>
</head>

<body>

    <!-- Header -->
    <?php include 'includes/nav.php'; ?>

    <!-- Page Header -->
    <div class="page-header">
        <div class="container">
            <h1 style="font-size: 2.5rem; margin-bottom: 10px; font-weight: 700; color: var(--accent-gold);">
                ข้อมูลทางการเงิน</h1>
            <p style="opacity: 0.8; font-size: 1.1rem;">รายงานงบการเงินและผลการดำเนินงานของบริษัท</p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="section" style="padding-top: 60px; background-color: #f8f9fa; min-height: 80vh;">
        <div class="container">

            <div style="max-width: 900px; margin: 0 auto;">

                <h2
                    style="font-size: 1.5rem; margin-bottom: 20px; color: var(--primary-blue); border-left: 4px solid var(--accent-gold); padding-left: 15px;">
                    งบการเงิน</h2>
                <div class="document-list" style="margin-bottom: 40px;">
                    <?php if (count($financial_statements) > 0): ?>
                        <?php foreach ($financial_statements as $report): ?>
                            <div class="document-item">
                                <div style="display: flex; align-items: center;">
                                    <i class="fa-solid fa-file-lines doc-icon" style="color: #4CAF50;"></i>
                                    <div class="doc-info">
                                        <div class="doc-title"><?php echo htmlspecialchars($report['title']); ?></div>

                                    </div>
                                </div>
                                <a href="<?php echo htmlspecialchars($report['file_path']); ?>" target="_blank"
                                    class="btn-download"><i class="fa-solid fa-download"></i> ดาวน์โหลด</a>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="padding: 20px; text-align: center; color: #777;">ไม่พบงบการเงิน</p>
                    <?php endif; ?>
                </div>

                <h2
                    style="font-size: 1.5rem; margin-bottom: 20px; color: var(--primary-blue); border-left: 4px solid var(--accent-gold); padding-left: 15px;">
                    รายการข้อมูลประจำปี ONE REPORT</h2>
                <div class="document-list" style="margin-bottom: 40px;">
                    <?php if (count($one_reports) > 0): ?>
                        <?php foreach ($one_reports as $report): ?>
                            <div class="document-item">
                                <div style="display: flex; align-items: center;">
                                    <i class="fa-solid fa-file-pdf doc-icon"></i>
                                    <div class="doc-info">
                                        <div class="doc-title"><?php echo htmlspecialchars($report['title']); ?></div>

                                    </div>
                                </div>
                                <a href="<?php echo htmlspecialchars($report['file_path']); ?>" target="_blank"
                                    class="btn-download"><i class="fa-solid fa-download"></i> ดาวน์โหลด</a>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="padding: 20px; text-align: center; color: #777;">ไม่พบรายงาน</p>
                    <?php endif; ?>
                </div>

                <h2
                    style="font-size: 1.5rem; margin-bottom: 20px; color: var(--primary-blue); border-left: 4px solid var(--accent-gold); padding-left: 15px;">
                    รายงานประจำปี (แบบ 56-2)</h2>
                <div class="document-list">
                    <?php if (count($reports_56_2) > 0): ?>
                        <?php foreach ($reports_56_2 as $report): ?>
                            <div class="document-item">
                                <div style="display: flex; align-items: center;">
                                    <i class="fa-solid fa-file-contract doc-icon" style="color: #673AB7;"></i>
                                    <div class="doc-info">
                                        <div class="doc-title"><?php echo htmlspecialchars($report['title']); ?></div>

                                    </div>
                                </div>
                                <a href="<?php echo htmlspecialchars($report['file_path']); ?>" target="_blank"
                                    class="btn-download"><i class="fa-solid fa-download"></i> ดาวน์โหลด</a>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="padding: 20px; text-align: center; color: #777;">ไม่พบรายงาน</p>
                    <?php endif; ?>
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