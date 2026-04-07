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

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$job = null;

if ($id > 0) {
    $stmt = $db->prepare("SELECT * FROM careers WHERE id = ? AND is_active = 1");
    $stmt->execute([$id]);
    $job = $stmt->fetch();
}

if (!$job) {
    header("Location: contact_career.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายละเอียดงาน - MIDA LEASING</title>
    <meta name="description" content="รายละเอียดตำแหน่งงาน เจ้าหน้าที่การตลาด บริษัท ไมด้าลิสซิ่ง จำกัด (มหาชน)">

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

        .job-detail-card {
            background: white;
            border-radius: 12px;
            padding: 40px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            border: 1px solid #eee;
        }

        .job-header {
            border-bottom: 1px solid #eee;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .job-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--primary-blue);
            margin-bottom: 10px;
        }

        .job-meta-list {
            display: flex;
            gap: 20px;
            color: #666;
            font-size: 1rem;
            flex-wrap: wrap;
        }

        .job-meta-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .section-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 15px;
            margin-top: 30px;
        }

        .job-content ul {
            padding-left: 20px;
            color: #555;
            line-height: 1.7;
        }

        .job-content li {
            margin-bottom: 8px;
        }

        .apply-box {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 30px;
            text-align: center;
            margin-top: 40px;
        }
    </style>
</head>

<body>

    <!-- Header -->
    <?php include 'includes/nav.php'; ?>

    <!-- Page Header (Mini) -->
    <div class="page-header" style="padding: 120px 0 40px;">
        <div class="container">
            <h1 style="font-size: 2rem; margin-bottom: 5px; color: #fec435;">ตำแหน่งงานว่าง</h1>
            <p style="opacity: 0.8; font-size: 1rem;">ร่วมเติบโตก้าวหน้าไปกับครอบครัวไมด้าลิสซิ่ง</p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="section" style="padding-top: 40px; background-color: #f8f9fa;">
        <div class="container">

            <div style="margin-bottom: 20px;">
                <a href="contact_career.php"
                    style="text-decoration: none; color: #666; display: inline-flex; align-items: center;">
                    <i class="fa-solid fa-arrow-left" style="margin-right: 8px;"></i> กลับหน้ารายการงาน
                </a>
            </div>

            <div class="job-detail-card">
                <div class="job-header">
                    <h2 class="job-title"><?php echo htmlspecialchars($job['position']); ?></h2>
                    <div class="job-meta-list">
                        <div class="job-meta-item"><i class="fa-solid fa-location-dot"
                                style="color: var(--accent-gold);"></i>
                            <?php echo htmlspecialchars($job['location']); ?></div>
                        <div class="job-meta-item"><i class="fa-solid fa-briefcase"
                                style="color: var(--accent-gold);"></i>
                            <?php echo htmlspecialchars($job['type'] ?? 'งานประจำ (Full-time)'); ?></div>
                        <div class="job-meta-item"><i class="fa-solid fa-money-bill-wave"
                                style="color: var(--accent-gold);"></i> <?php echo htmlspecialchars($job['salary']); ?>
                        </div>
                        <div class="job-meta-item"><i class="fa-solid fa-users" style="color: var(--accent-gold);"></i>
                            <?php echo htmlspecialchars($job['quantity']); ?> อัตรา</div>
                    </div>
                </div>

                <div class="job-content">
                    <h3 class="section-title" style="text-align: left;">รายละเอียดงาน</h3>
                    <div style="color: #555; line-height: 1.7; margin-bottom: 20px; text-align: left;">
                        <?php echo nl2br(htmlspecialchars($job['description'])); ?>
                    </div>

                    <h3 class="section-title" style="text-align: left;">สวัสดิการ</h3>
                    <?php if (!empty($job['benefits'])): ?>
                        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; margin-top: 10px;">
                            <?php
                            $benefits_list = explode(',', $job['benefits']);
                            foreach ($benefits_list as $benefit):
                                $benefit = trim($benefit);
                                if (empty($benefit))
                                    continue;
                                ?>
                                <div><i class="fa-solid fa-check" style="color: #10b981; margin-right: 8px;"></i>
                                    <?php echo htmlspecialchars($benefit); ?></div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="apply-box">
                <h3 style="margin-bottom: 15px; font-size: 1.5rem;">สนใจร่วมงานกับเรา?</h3>
                <p style="color: #666; margin-bottom: 25px;">ส่งประวัติ (Resume) ของคุณมาได้ที่อีเมลด้านล่าง
                    หรือโทรสอบถามข้อมูลเพิ่มเติม</p>

                <a href="mailto:hr@midaleasing.com" class="btn btn-primary"
                    style="padding: 12px 35px; font-size: 1.1rem; margin-right: 15px;">
                    <i class="fa-solid fa-envelope" style="margin-right: 8px;"></i> ส่งใบสมัครทางอีเมล
                </a>
                <a href="tel:025746901" class="btn btn-primary btn-outline"
                    style="padding: 12px 35px; font-size: 1.1rem;">
                    <i class="fa-solid fa-phone" style="margin-right: 8px;"></i> 02-574-6901
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