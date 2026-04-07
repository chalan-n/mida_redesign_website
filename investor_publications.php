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

$invitations = [];
$minutes = [];
$warrants = [];

try {
    $stmt = $db->query("SELECT * FROM publications ORDER BY id DESC");
    $publications = $stmt->fetchAll();

    foreach ($publications as $pub) {
        $cat = $pub['category'];
        if ($cat == 'หนังสือเชิญประชุมสามัญผู้ถือหุ้น') {
            $invitations[] = $pub;
        } elseif ($cat == 'รายงานการประชุมสามัญผู้ถือหุ้นประจำปี') {
            $minutes[] = $pub;
        } elseif ($cat == 'ข้อมูลใบสำคัญแสดงสิทธิ') {
            $warrants[] = $pub;
        } else {
            // Mapping old categories to new ones or default
            if ($cat == 'News')
                $invitations[] = $pub; // Fallback
            else
                $minutes[] = $pub; // Fallback
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
    <title>เอกสารเผยแพร่ - MIDA LEASING</title>
    <meta name="description" content="ข่าวสาร เอกสารเผยแพร่ และวารสารสัมพันธ์ บริษัท ไมด้าลิสซิ่ง จำกัด (มหาชน)">

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

        .document-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 25px;
        }

        .pub-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            border: 1px solid #eee;
            transition: all 0.3s;
        }

        .pub-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .pub-img {
            height: 200px;
            background-color: #ddd;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #888;
            font-size: 3rem;
        }

        .pub-content {
            padding: 20px;
        }

        .pub-tag {
            display: inline-block;
            background: #f0f7ff;
            color: var(--primary-blue);
            font-size: 0.8rem;
            padding: 4px 10px;
            border-radius: 20px;
            margin-bottom: 10px;
        }

        .pub-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 10px;
            color: #333;
            line-height: 1.5;
        }

        .pub-date {
            font-size: 0.9rem;
            color: #999;
            margin-bottom: 15px;
        }

        .btn-read {
            color: var(--primary-blue);
            text-decoration: none;
            font-weight: 600;
            font-size: 0.95rem;
            display: inline-flex;
            align-items: center;
        }

        .btn-read:hover {
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .document-grid {
                grid-template-columns: 1fr;
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
                เอกสารดาวน์โหลด</h1>
            <p style="opacity: 0.8; font-size: 1.1rem;">หนังสือเชิญประชุม รายงานการประชุม และข้อมูลสำคัญ</p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="section" style="padding-top: 60px; background-color: #f8f9fa; min-height: 80vh;">
        <div class="container">

            <!-- Section 1: Invitations -->
            <h2
                style="font-size: 1.5rem; margin-bottom: 20px; color: var(--primary-blue); border-left: 4px solid var(--accent-gold); padding-left: 15px;">
                หนังสือเชิญประชุมสามัญผู้ถือหุ้น</h2>
            <div class="document-grid" style="margin-bottom: 50px;">
                <?php if (count($invitations) > 0): ?>
                    <?php foreach ($invitations as $pub): ?>
                        <div class="pub-card">
                            <div class="pub-img">
                                <i class="fa-solid fa-envelope-open-text"></i>
                            </div>
                            <div class="pub-content">
                                <span class="pub-tag"><?php echo htmlspecialchars($pub['category']); ?></span>
                                <div class="pub-title"><?php echo htmlspecialchars($pub['title']); ?></div>
                                <div class="pub-date"><i class="fa-regular fa-calendar" style="margin-right: 5px;"></i>
                                    <?php echo htmlspecialchars($pub['publish_date']); ?></div>
                                <?php if (!empty($pub['link_url'])): ?>
                                    <a href="<?php echo htmlspecialchars($pub['link_url']); ?>" target="_blank" class="btn-read">
                                        อ่านเพิ่มเติม <i class="fa-solid fa-arrow-right" style="margin-left: 5px;"></i>
                                    </a>
                                <?php elseif (!empty($pub['file_path'])): ?>
                                    <a href="<?php echo htmlspecialchars($pub['file_path']); ?>" target="_blank" class="btn-read">
                                        ดาวน์โหลด PDF <i class="fa-solid fa-download" style="margin-left: 5px;"></i>
                                    </a>
                                <?php else: ?>
                                    <span style="color:#aaa;">(ไม่มีลิงก์)</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="grid-column: 1/-1; text-align: center; color: #777;">ไม่พบข้อมูล</p>
                <?php endif; ?>
            </div>

            <!-- Section 2: Minutes -->
            <h2
                style="font-size: 1.5rem; margin-bottom: 20px; color: var(--primary-blue); border-left: 4px solid var(--accent-gold); padding-left: 15px;">
                รายงานการประชุมสามัญผู้ถือหุ้นประจำปี</h2>
            <div class="document-grid" style="margin-bottom: 50px;">
                <?php if (count($minutes) > 0): ?>
                    <?php foreach ($minutes as $pub): ?>
                        <div class="pub-card">
                            <div class="pub-img">
                                <i class="fa-solid fa-file-signature"></i>
                            </div>
                            <div class="pub-content">
                                <span class="pub-tag"><?php echo htmlspecialchars($pub['category']); ?></span>
                                <div class="pub-title"><?php echo htmlspecialchars($pub['title']); ?></div>
                                <div class="pub-date"><i class="fa-regular fa-calendar" style="margin-right: 5px;"></i>
                                    <?php echo htmlspecialchars($pub['publish_date']); ?></div>
                                <?php if (!empty($pub['link_url'])): ?>
                                    <a href="<?php echo htmlspecialchars($pub['link_url']); ?>" target="_blank" class="btn-read">
                                        อ่านเพิ่มเติม <i class="fa-solid fa-arrow-right" style="margin-left: 5px;"></i>
                                    </a>
                                <?php elseif (!empty($pub['file_path'])): ?>
                                    <a href="<?php echo htmlspecialchars($pub['file_path']); ?>" target="_blank" class="btn-read">
                                        ดาวน์โหลด PDF <i class="fa-solid fa-download" style="margin-left: 5px;"></i>
                                    </a>
                                <?php else: ?>
                                    <span style="color:#aaa;">(ไม่มีลิงก์)</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="grid-column: 1/-1; text-align: center; color: #777;">ไม่พบข้อมูล</p>
                <?php endif; ?>
            </div>

            <!-- Section 3: Warrants -->
            <h2
                style="font-size: 1.5rem; margin-bottom: 20px; color: var(--primary-blue); border-left: 4px solid var(--accent-gold); padding-left: 15px;">
                ข้อมูลใบสำคัญแสดงสิทธิ</h2>
            <div class="document-grid">
                <?php if (count($warrants) > 0): ?>
                    <?php foreach ($warrants as $pub): ?>
                        <div class="pub-card">
                            <div class="pub-img">
                                <i class="fa-solid fa-certificate"></i>
                            </div>
                            <div class="pub-content">
                                <span class="pub-tag"><?php echo htmlspecialchars($pub['category']); ?></span>
                                <div class="pub-title"><?php echo htmlspecialchars($pub['title']); ?></div>
                                <div class="pub-date"><i class="fa-regular fa-calendar" style="margin-right: 5px;"></i>
                                    <?php echo htmlspecialchars($pub['publish_date']); ?></div>
                                <?php if (!empty($pub['link_url'])): ?>
                                    <a href="<?php echo htmlspecialchars($pub['link_url']); ?>" target="_blank" class="btn-read">
                                        อ่านเพิ่มเติม <i class="fa-solid fa-arrow-right" style="margin-left: 5px;"></i>
                                    </a>
                                <?php elseif (!empty($pub['file_path'])): ?>
                                    <a href="<?php echo htmlspecialchars($pub['file_path']); ?>" target="_blank" class="btn-read">
                                        ดาวน์โหลด PDF <i class="fa-solid fa-download" style="margin-left: 5px;"></i>
                                    </a>
                                <?php else: ?>
                                    <span style="color:#aaa;">(ไม่มีลิงก์)</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="grid-column: 1/-1; text-align: center; color: #777;">ไม่พบข้อมูล</p>
                <?php endif; ?>
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