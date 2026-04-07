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
    <title>นโยบายความเป็นส่วนตัว - MIDA LEASING</title>
    <meta name="description" content="นโยบายความเป็นส่วนตัว บริษัท ไมด้าลิสซิ่ง จำกัด (มหาชน)">

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
            background: linear-gradient(135deg, var(--primary-blue) 0%, #004a99 100%);
            padding: 100px 0 50px;
            color: white;
            text-align: center;
        }

        .page-header h1 {
            color: white;
            margin-bottom: 10px;
        }

        .page-header p {
            color: rgba(255, 255, 255, 0.9);
        }

        .content-section {
            padding: 60px 0;
            background-color: #fff;
        }

        .policy-content h2 {
            color: var(--primary-blue);
            margin-top: 30px;
            margin-bottom: 20px;
            font-size: 1.5rem;
        }

        .policy-content h3 {
            color: #333;
            margin-top: 20px;
            margin-bottom: 15px;
            font-size: 1.2rem;
        }

        .policy-content p {
            margin-bottom: 15px;
            line-height: 1.6;
            color: #555;
        }

        .policy-content ul {
            margin-bottom: 15px;
            padding-left: 20px;
        }

        .policy-content li {
            margin-bottom: 8px;
            color: #555;
        }
    </style>
</head>

<body>

    <!-- Header -->
    <?php include 'includes/nav.php'; ?>

    <!-- Page Title -->
    <div class="page-header">
        <div class="container">
            <h1>นโยบายความเป็นส่วนตัว</h1>
            <p>Privacy Policy</p>
        </div>
    </div>

    <!-- Content -->
    <section class="content-section">
        <?php
        require_once 'admin/config/db.php';
        $content = "";
        $title = "นโยบายความเป็นส่วนตัว";

        try {
            $database = new Database();
            $db = $database->getConnection();

            // Track visitor
            @include_once 'track_visitor.php';
            $stmt = $db->prepare("SELECT title, content FROM pages WHERE slug = 'privacy_policy'");
            $stmt->execute();
            $page = $stmt->fetch();

            if ($page) {
                $title = $page['title'];
                $content = $page['content'];
            }
        } catch (Exception $e) {
            // Fallback or error handling
        }
        ?>

        <div class="container policy-content">
            <?php if ($content): ?>
                <?php echo $content; ?>
            <?php else: ?>
                <p>ขออภัย ไม่พบข้อมูลนโยบายความเป็นส่วนตัวในขณะนี้</p>
            <?php endif; ?>
        </div>
    </section>

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
                    <h4>นักลงทุนสัมพันธ์</h4>
                    <ul>
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