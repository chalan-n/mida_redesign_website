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

$careers = [];
try {
    $stmt = $db->query("SELECT * FROM careers WHERE is_active = 1 ORDER BY created_at DESC");
    $careers = $stmt->fetchAll();
} catch (PDOException $e) {
    // Handle error
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ร่วมงานกับเรา - MIDA LEASING</title>
    <meta name="description"
        content="ร่วมเป็นส่วนหนึ่งของครอบครัวไมด้าลิสซิ่ง องค์กรที่มั่นคง พร้อมโอกาสเติบโตในสายอาชีพ">

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

        .career-intro {
            text-align: center;
            max-width: 800px;
            margin: 0 auto 60px;
        }

        .benefit-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 60px;
        }

        .benefit-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            text-align: center;
            border: 1px solid #eee;
        }

        .benefit-icon {
            font-size: 2.5rem;
            color: var(--accent-gold);
            margin-bottom: 15px;
        }

        .job-card {
            background: white;
            border: 1px solid #eee;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.3s;
        }

        .job-card:hover {
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            border-color: var(--primary-blue);
            transform: translateY(-2px);
        }

        .job-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }

        .job-meta {
            color: #666;
            font-size: 0.95rem;
            display: flex;
            gap: 20px;
        }

        .job-meta span {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        @media (max-width: 768px) {
            .benefit-grid {
                grid-template-columns: 1fr 1fr;
            }

            .job-card {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }

            .job-card .btn {
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
                ร่วมงานกับเรา</h1>
            <p style="opacity: 0.8; font-size: 1.1rem;">โอกาสเติบโตในองค์กรชั้นนำ มั่นคง และก้าวหน้าไปพร้อมกัน</p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="section" style="padding-top: 60px; background-color: #f8f9fa;">
        <div class="container">

            <div class="career-intro">
                <h2 style="font-size: 2rem; margin-bottom: 20px; color: var(--primary-blue);">ทำไมต้อง ไมด้า ลิสซิ่ง?
                </h2>
                <p style="color: #555; font-size: 1.1rem; line-height: 1.7;">
                    เราคือผู้นำในธุรกิจสินเชื่อเช่าซื้อรถยนต์มือสอง ที่ดำเนินธุรกิจมาอย่างยาวนานและมั่นคง
                    เราให้ความสำคัญกับบุคลากรเสมือนสินทรัพย์ที่มีค่าที่สุด พร้อมมอบโอกาสในการเรียนรู้
                    พัฒนาศักยภาพ และสวัสดิการที่ดีเยี่ยม เพื่อคุณภาพชีวิตที่ดีของพนักงานทุกคน
                </p>
            </div>

            <!-- Benefits -->
            <div class="benefit-grid">
                <div class="benefit-card">
                    <div class="benefit-icon"><i class="fa-solid fa-hand-holding-dollar"></i></div>
                    <h4 style="font-weight: 600; margin-bottom: 10px;">รายได้/โบนัส</h4>
                    <p style="font-size: 0.9rem; color: #666;">เงินเดือนแข่งขันได้ โบนัสตามผลประกอบการ และเบี้ยขยัน</p>
                </div>
                <div class="benefit-card">
                    <div class="benefit-icon"><i class="fa-solid fa-user-doctor"></i></div>
                    <h4 style="font-weight: 600; margin-bottom: 10px;">ประกันสุขภาพ</h4>
                    <p style="font-size: 0.9rem; color: #666;">ประกันสังคม ประกันอุบัติเหตุ และตรวจสุขภาพประจำปี</p>
                </div>
                <div class="benefit-card">
                    <div class="benefit-icon"><i class="fa-solid fa-chart-line"></i></div>
                    <h4 style="font-weight: 600; margin-bottom: 10px;">ความก้าวหน้า</h4>
                    <p style="font-size: 0.9rem; color: #666;">เส้นทางการเติบโตในสายอาชีพและการฝึกอบรมพัฒนา</p>
                </div>
                <div class="benefit-card">
                    <div class="benefit-icon"><i class="fa-solid fa-umbrella-beach"></i></div>
                    <h4 style="font-weight: 600; margin-bottom: 10px;">วันหยุดพักร้อน</h4>
                    <p style="font-size: 0.9rem; color: #666;">วันหยุดพักผ่อนประจำปี วันหยุดตามประเพณี</p>
                </div>
            </div>

            <!-- Open Positions -->
            <div style="margin-bottom: 60px;">
                <h2
                    style="font-size: 1.8rem; margin-bottom: 30px; border-left: 5px solid var(--accent-gold); padding-left: 15px;">
                    ตำแหน่งงานที่เปิดรับสมัคร</h2>

                <?php if (count($careers) > 0): ?>
                    <?php foreach ($careers as $job): ?>
                        <div class="job-card">
                            <div>
                                <div class="job-title"><?php echo htmlspecialchars($job['position']); ?></div>
                                <div class="job-meta">
                                    <span><i class="fa-solid fa-location-dot"></i>
                                        <?php echo htmlspecialchars($job['location']); ?></span>
                                    <span><i class="fa-solid fa-sack-dollar"></i>
                                        <?php echo htmlspecialchars($job['salary']); ?></span>
                                    <span><i class="fa-solid fa-users"></i> <?php echo htmlspecialchars($job['quantity']); ?>
                                        อัตรา</span>
                                </div>
                            </div>
                            <a href="career_detail.php?id=<?php echo $job['id']; ?>" class="btn btn-primary"
                                style="min-width: 140px; text-align: center;">ดูรายละเอียด</a>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="color: #777; font-size: 1.1rem; background: white; padding: 20px; border-radius: 8px;">
                        ขณะนี้ยังไม่มีตำแหน่งงานว่างที่เปิดรับสมัคร</p>
                <?php endif; ?>
            </div>

            <!-- Contact HR -->
            <div
                style="background: white; border-radius: 12px; padding: 40px; text-align: center; border: 1px solid #ddd;">
                <h3 style="margin-bottom: 20px; font-size: 1.5rem;">หากคุณพร้อมที่จะเติบโตไปกับเรา</h3>
                <p style="color: #666; margin-bottom: 30px;">ส่งประวัติ (Resume) และผลงานของคุณมาที่ฝ่ายทรัพยากรบุคคล
                </p>
                <div style="display: flex; justify-content: center; gap: 30px; flex-wrap: wrap;">
                    <div style="display: flex; align-items: center; gap: 10px; font-size: 1.1rem;">
                        <i class="fa-solid fa-envelope" style="color: var(--primary-blue);"></i> hr@midaleasing.com
                    </div>
                    <div style="display: flex; align-items: center; gap: 10px; font-size: 1.1rem;">
                        <i class="fa-solid fa-phone" style="color: var(--primary-blue);"></i> 02-574-6901 ต่อ 888
                        (ฝ่ายบุคคล)
                    </div>
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