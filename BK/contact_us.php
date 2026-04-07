<?php
require_once 'admin/config/db.php';
$database = new Database();
$db = $database->getConnection();

// Track visitor
@include_once 'track_visitor.php';

$success_msg = "";
$error_msg = "";

// Fetch Settings
$settings = [];
try {
    $stmt = $db->query("SELECT * FROM settings WHERE id = 1");
    $settings = $stmt->fetch();
} catch (PDOException $e) {
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $email = $_POST['email'] ?? '';
    $subject = $_POST['subject'] ?? '';
    $message = $_POST['message'] ?? '';

    // Thai phone validation function
    $isValidThaiPhone = preg_match('/^0[0-9]{8,9}$/', $phone);

    if (!empty($_POST['website_url'])) {
        // Honeypot caught a bot
        $error_msg = "ระบบตรวจพบความผิดปกติ (Spam Detected)";
    } elseif (!$isValidThaiPhone) {
        $error_msg = "กรุณากรอกเบอร์โทรศัพท์ไทยที่ถูกต้อง (เช่น 0812345678)";
    } elseif (!isset($_POST['captcha_answer']) || !isset($_SESSION['captcha_result']) || (int) $_POST['captcha_answer'] !== (int) $_SESSION['captcha_result']) {
        $error_msg = "คำตอบยืนยันตัวตนไม่ถูกต้อง กรุณาลองใหม่อีกครั้ง";
    } elseif ($name && $phone && $message) {
        try {
            $sql = "INSERT INTO contact_messages (name, email, phone, subject, message, created_at) VALUES (?, ?, ?, ?, ?, NOW())";
            $stmt = $db->prepare($sql);
            $stmt->execute([$name, $email, $phone, $subject, $message]);
            $success_msg = "ส่งข้อความเรียบร้อยแล้ว เจ้าหน้าที่จะติดต่อกลับโดยเร็วที่สุด";

            // Clear input
            $name = $phone = $email = $subject = $message = '';
        } catch (PDOException $e) {
            $error_msg = "เกิดข้อผิดพลาดในการส่งข้อความ กรุณาลองใหม่อีกครั้ง";
            // error_log($e->getMessage());
        }
    } else {
        $error_msg = "กรุณากรอกข้อมูลให้ครบถ้วน";
    }
}

// Thai number words array
$thaiNumbers = ['ศูนย์', 'หนึ่ง', 'สอง', 'สาม', 'สี่', 'ห้า', 'หก', 'เจ็ด', 'แปด', 'เก้า'];

// Generate new captcha for next attempt
$math_n1 = rand(1, 9);
$math_n2 = rand(1, 9);
$_SESSION['captcha_result'] = $math_n1 + $math_n2;
$thai_n1 = $thaiNumbers[$math_n1];
$thai_n2 = $thaiNumbers[$math_n2];
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ติดต่อเรา - MIDA LEASING</title>
    <meta name="description" content="ติดต่อไมด้าลิสซิ่ง สอบถามข้อมูลสินเชื่อ ประมูลรถ หรือติตด่อเรื่องอื่นๆ">

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

        .contact-card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            border: 1px solid #eee;
            height: 100%;
        }

        .contact-icon-box {
            width: 50px;
            height: 50px;
            background: #f0f7ff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-blue);
            font-size: 1.5rem;
            margin-bottom: 20px;
        }

        .contact-form label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #333;
        }

        .contact-form input,
        .contact-form textarea,
        .contact-form select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            margin-bottom: 20px;
            font-family: 'Prompt', sans-serif;
        }

        .contact-form input:focus,
        .contact-form textarea:focus {
            outline: none;
            border-color: var(--primary-blue);
        }

        .social-link {
            display: inline-flex;
            width: 40px;
            height: 40px;
            background: #eee;
            border-radius: 50%;
            align-items: center;
            justify-content: center;
            color: #555;
            text-decoration: none;
            margin-right: 10px;
            transition: all 0.2s;
        }

        .social-link:hover {
            background: var(--primary-blue);
            color: white;
            transform: translateY(-3px);
        }
    </style>
</head>

<body>

    <!-- Header -->
    <?php include 'includes/nav.php'; ?>

    <!-- Page Header -->
    <div class="page-header">
        <div class="container">
            <h1 style="font-size: 2.5rem; margin-bottom: 10px; font-weight: 700; color: var(--accent-gold);">ติดต่อเรา
            </h1>
            <p style="opacity: 0.8; font-size: 1.1rem;">
                เราพร้อมให้คำปรึกษาและบริการข้อมูลเกี่ยวกับผลิตภัณฑ์และบริการของเรา</p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="section" style="padding-top: 60px; background-color: #f8f9fa;">
        <div class="container">

            <div style="display: grid; grid-template-columns: 1fr 1.5fr; gap: 40px;">

                <!-- Contact Info -->
                <div>
                    <div class="contact-card">
                        <div class="contact-icon-box"><i class="fa-solid fa-location-dot"></i></div>
                        <h3 style="margin-bottom: 15px;">สำนักงานใหญ่</h3>
                        <p style="color: #666; line-height: 1.6; margin-bottom: 20px;">
                            <?php echo nl2br(htmlspecialchars($settings['site_address'])); ?>
                        </p>
                        <hr style="border: 0; border-top: 1px solid #eee; margin: 20px 0;">

                        <div style="margin-bottom: 15px;">
                            <i class="fa-solid fa-phone" style="color: var(--primary-blue); width: 25px;"></i>
                            <span
                                style="font-weight: 600;"><?php echo htmlspecialchars($settings['site_phone']); ?></span>
                        </div>
                        <div style="margin-bottom: 15px;">
                            <i class="fa-solid fa-fax" style="color: var(--primary-blue); width: 25px;"></i>
                            <span>02-574-6902</span>
                        </div>
                        <div style="margin-bottom: 15px;">
                            <i class="fa-solid fa-envelope" style="color: var(--primary-blue); width: 25px;"></i>
                            <span><?php echo htmlspecialchars($settings['site_email']); ?></span>
                        </div>
                        <div style="margin-bottom: 15px;">
                            <i class="fa-solid fa-clock" style="color: var(--primary-blue); width: 25px;"></i>
                            <span><?php echo htmlspecialchars($settings['site_work_hours']); ?></span>
                        </div>

                        <hr style="border: 0; border-top: 1px solid #eee; margin: 20px 0;">

                        <h4 style="margin-bottom: 15px;">ติดตามเรา</h4>
                        <div>
                            <a href="<?php echo htmlspecialchars($settings['site_facebook']); ?>" target="_blank"
                                class="social-link"><i class="fa-brands fa-facebook-f"></i></a>
                            <a href="<?php echo htmlspecialchars($settings['site_line']); ?>" target="_blank"
                                class="social-link"><i class="fa-brands fa-line"></i></a>
                        </div>
                    </div>
                </div>

                <!-- Contact Form -->
                <div>
                    <div class="contact-card">
                        <h3 style="margin-bottom: 30px;">ติดต่อเรา</h3>

                        <?php if ($success_msg): ?>
                            <div
                                style="background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                                <i class="fa-solid fa-check-circle" style="margin-right: 5px;"></i>
                                <?php echo $success_msg; ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($error_msg): ?>
                            <div
                                style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                                <i class="fa-solid fa-exclamation-circle" style="margin-right: 5px;"></i>
                                <?php echo $error_msg; ?>
                            </div>
                        <?php endif; ?>

                        <form class="contact-form" method="POST" action="">
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                                <div>
                                    <label>ชื่อ - นามสกุล *</label>
                                    <input type="text" name="name" placeholder="ระบุชื่อของคุณ" required>
                                </div>
                                <div>
                                    <label>เบอร์โทรศัพท์ *</label>
                                    <input type="tel" name="phone" placeholder="เช่น 0812345678" pattern="0[0-9]{8,9}"
                                        title="กรุณากรอกเบอร์โทรศัพท์ไทย 9-10 หลัก" required>
                                </div>
                            </div>

                            <label>อีเมล (ถ้ามี)</label>
                            <input type="email" name="email" placeholder="ระบุอีเมลของคุณ">

                            <label>หัวข้อติดต่อ *</label>
                            <select name="subject">
                                <option value="สอบถามข้อมูลทั่วไป">สอบถามข้อมูลทั่วไป</option>
                                <option value="สอบถามเรื่องสินเชื่อ">สอบถามเรื่องสินเชื่อ</option>
                                <option value="สอบถามเรื่องรถประมูล">สอบถามเรื่องรถประมูล</option>
                                <option value="เสนอแนะบริการ">เสนอแนะบริการ</option>
                                <option value="ร้องเรียนบริการ">ร้องเรียนบริการ</option>
                            </select>

                            <label>ข้อความ *</label>
                            <textarea name="message" rows="5" placeholder="ระบุรายละเอียดที่คุณต้องการสอบถาม"
                                required><?php echo htmlspecialchars($message ?? ''); ?></textarea>

                            <!-- Honeypot (Hidden) -->
                            <div style="display: none; visibility: hidden;">
                                <input type="text" name="website_url" value="" autocomplete="off">
                            </div>

                            <!-- Human Check -->
                            <label style="margin-top: 20px;">ยืนยันตัวตน *</label>
                            <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 20px;">
                                <div
                                    style="background: #eef2f7; padding: 10px 20px; border-radius: 6px; font-weight: bold; font-size: 1.1rem; color: var(--primary-blue);">
                                    <?php echo $thai_n1; ?> + <?php echo $thai_n2; ?> = ?
                                </div>
                                <input type="number" name="captcha_answer" placeholder="ใส่ผลบวกเป็นตัวเลข" required
                                    style="width: 150px; margin-bottom: 0;">
                            </div>

                            <button type="submit" class="btn btn-primary"
                                style="padding: 12px 30px;">ส่งข้อความ</button>
                        </form>
                    </div>
                </div>

            </div>

            <!-- Google Map Embed Head Office -->
            <div style="margin-top: 40px; height: 400px; background: #eee; border-radius: 12px; overflow: hidden;">
                <iframe src="https://maps.google.com/maps?q=13.8974661,100.5616628&hl=th&z=15&output=embed" width="100%"
                    height="100%" style="border:0;" allowfullscreen="" loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"></iframe>
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