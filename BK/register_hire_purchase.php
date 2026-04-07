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

$success_msg = "";
$error_msg = "";

$selected_type = isset($_GET['type']) ? $_GET['type'] : 'sedan'; // Default to sedan
$type_map = [
    'sedan' => 'รถเก๋ง',
    'pickup' => 'รถกระบะ',
    'truck' => 'รถบรรทุก'
];
$default_checked_value = isset($type_map[$selected_type]) ? $type_map[$selected_type] : 'รถเก๋ง';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['fullname'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $line_id = $_POST['line_id'] ?? '';
    $car_type = $_POST['car_type'] ?? '';
    $car_brand = $_POST['car_brand'] ?? '';
    $car_model_year = $_POST['car_model_year'] ?? '';
    $loan_amount = $_POST['loan_amount'] ?? 0;

    if ($name && $phone && $car_type) {
        try {
            $sql = "INSERT INTO loan_applications (loan_type, name, phone, line_id, car_type, car_brand, car_model_year, loan_amount, created_at) VALUES ('hire_purchase', ?, ?, ?, ?, ?, ?, ?, NOW())";
            $stmt = $db->prepare($sql);
            $stmt->execute([$name, $phone, $line_id, $car_type, $car_brand, $car_model_year, $loan_amount]);
            $success_msg = "ส่งข้อมูลสมัครสินเชื่อเรียบร้อยแล้ว เจ้าหน้าที่จะติดต่อกลับภายใน 1 วันทำการ";
        } catch (PDOException $e) {
            $error_msg = "เกิดข้อผิดพลาดในการส่งข้อมูล กรุณาลองใหม่อีกครั้ง";
        }
    } else {
        $error_msg = "กรุณากรอกข้อมูลที่จำเป็นให้ครบถ้วน";
    }
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สมัครสินเชื่อเช่าซื้อรถยนต์ - MIDA LEASING</title>
    <meta name="description" content="สมัครสินเชื่อเช่าซื้อรถยนต์ออนไลน์ อนุมัติไว รู้ผลเร็ว ให้วงเงินสูง">

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
        .register-header {
            background: linear-gradient(135deg, var(--primary-blue) 0%, #004a99 100%);
            padding: 100px 0 60px;
            text-align: center;
            color: white;
            clip-path: polygon(0 0, 100% 0, 100% 90%, 0 100%);
        }

        .register-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            padding: 40px;
            margin-top: -40px;
            margin-bottom: 60px;
            position: relative;
            z-index: 2;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-label {
            font-weight: 500;
            color: #333;
            margin-bottom: 8px;
            display: block;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-family: 'Prompt', sans-serif;
            font-size: 1rem;
            transition: all 0.3s;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 3px rgba(28, 69, 135, 0.1);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .radio-group {
            display: flex;
            gap: 20px;
        }

        .radio-option {
            flex: 1;
        }

        .radio-input {
            display: none;
        }

        .radio-label {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 15px;
            border: 2px solid #eee;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s;
            height: 100%;
            text-align: center;
        }

        .radio-input:checked+.radio-label {
            border-color: var(--primary-blue);
            background-color: #f0f7ff;
            color: var(--primary-blue);
        }

        .radio-label i {
            font-size: 1.5rem;
            margin-bottom: 10px;
            color: #ccc;
        }

        .radio-input:checked+.radio-label i {
            color: var(--primary-blue);
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }

            .register-card {
                padding: 25px;
            }
        }
    </style>
</head>

<body>

    <!-- Header -->
    <?php $active_page = 'services'; include 'includes/nav.php'; ?>

    <!-- Page Title -->
    <div class="register-header">
        <div class="container">
            <h1 style="margin-bottom: 10px; color: var(--accent-gold);">สมัครสินเชื่อเช่าซื้อรถยนต์</h1>
            <p style="opacity: 0.9;">กรอกข้อมูลเพื่อให้เจ้าหน้าที่ติดต่อกลับ (ประเมินวงเงินฟรี)</p>
        </div>
    </div>

    <!-- Registration Form -->
    <div class="container" style="max-width: 800px;">
        <div class="register-card">
            <a href="service_hire_purchase.php"
                style="display: inline-flex; align-items: center; text-decoration: none; color: #666; margin-bottom: 20px; font-size: 0.9rem; transition: color 0.3s;">
                <i class="fa-solid fa-arrow-left" style="margin-right: 8px;"></i> กลับหน้าหลัก
            </a>
            <form action="" method="POST">
                <?php if ($success_msg): ?>
                    <div
                        style="background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                        <i class="fa-solid fa-check-circle" style="margin-right: 5px;"></i> <?php echo $success_msg; ?>
                    </div>
                <?php endif; ?>

                <?php if ($error_msg): ?>
                    <div
                        style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                        <i class="fa-solid fa-exclamation-circle" style="margin-right: 5px;"></i> <?php echo $error_msg; ?>
                    </div>
                <?php endif; ?>
                <h3
                    style="color: var(--primary-blue); margin-bottom: 25px; border-bottom: 2px solid #f0f0f0; padding-bottom: 15px;">
                    <i class="fa-solid fa-user-pen"></i> ข้อมูลผู้สมัคร
                </h3>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">ชื่อ - นามสกุล <span style="color: red;">*</span></label>
                        <input type="text" name="fullname" class="form-control" placeholder="ระบุชื่อและนามสกุล"
                            required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">เบอร์โทรศัพท์มือถือ <span style="color: red;">*</span></label>
                        <input type="tel" name="phone" class="form-control" placeholder="08x-xxx-xxxx" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">LINE ID (ถ้ามี)</label>
                    <input type="text" name="line_id" class="form-control" placeholder="ไอดีไลน์">
                </div>

                <h3
                    style="color: var(--primary-blue); margin: 35px 0 25px; border-bottom: 2px solid #f0f0f0; padding-bottom: 15px;">
                    <i class="fa-solid fa-car"></i> ข้อมูลรถที่ต้องการกู้
                </h3>

                <div class="form-group">
                    <label class="form-label">ประเภทรถ <span style="color: red;">*</span></label>
                    <div class="radio-group">
                        <div class="radio-option">
                            <input type="radio" name="car_type" id="car_sedan" value="รถเก๋ง" class="radio-input" <?php echo $default_checked_value == 'รถเก๋ง' ? 'checked' : ''; ?>>
                            <label for="car_sedan" class="radio-label">
                                <i class="fa-solid fa-car-side"></i>
                                <span>รถเก๋ง</span>
                            </label>
                        </div>
                        <div class="radio-option">
                            <input type="radio" name="car_type" id="car_pickup" value="รถกระบะ" class="radio-input"
                                <?php echo $default_checked_value == 'รถกระบะ' ? 'checked' : ''; ?>>
                            <label for="car_pickup" class="radio-label">
                                <i class="fa-solid fa-truck-pickup"></i>
                                <span>รถกระบะ</span>
                            </label>
                        </div>
                        <div class="radio-option">
                            <input type="radio" name="car_type" id="car_truck" value="รถบรรทุก" class="radio-input"
                                <?php echo $default_checked_value == 'รถบรรทุก' ? 'checked' : ''; ?>>
                            <label for="car_truck" class="radio-label">
                                <i class="fa-solid fa-truck"></i>
                                <span>รถบรรทุก</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">ยี่ห้อรถ</label>
                        <input type="text" name="car_brand" class="form-control" placeholder="เช่น Toyota, Isuzu">
                    </div>
                    <div class="form-group">
                        <label class="form-label">รุ่นรถ / ปี</label>
                        <input type="text" name="car_model_year" class="form-control" placeholder="เช่น Vigo ปี 2018">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">วงเงินที่ต้องการ (บาท)</label>
                    <input type="number" name="loan_amount" class="form-control" placeholder="ระบุจำนวนเงินที่ต้องการ">
                </div>

                <div style="margin: 30px 0;">
                    <label style="display: flex; align-items: start; gap: 10px; cursor: pointer;">
                        <input type="checkbox" style="margin-top: 5px;" required>
                        <span style="font-size: 0.9rem; color: #666;">
                            ข้าพเจ้ายินยอมให้บริษัทเก็บรวบรวม ใช้ และเปิดเผยข้อมูลส่วนบุคคล
                            เพื่อวัตถุประสงค์ในการติดต่อกลับและนำเสนอผลิตภัณฑ์สินเชื่อ ตามนโยบายความเป็นส่วนตัว
                        </span>
                    </label>
                </div>

                <button type="submit" class="btn btn-primary"
                    style="width: 100%; padding: 15px; font-size: 1.1rem; box-shadow: 0 4px 15px rgba(28, 69, 135, 0.3);">
                    ส่งข้อมูลสมัครสินเชื่อ
                </button>
                <p style="text-align: center; margin-top: 15px; font-size: 0.9rem; color: #888;">
                    * เจ้าหน้าที่จะติดต่อกลับภายใน 1 วันทำการ
                </p>
                <p style="text-align: center; margin-top: 25px; font-size: 0.9rem; color: #888;">
                    *กู้เท่าที่จำเป็นและชำระคืนไหว อัตราดอกเบี้ย 11.99% - 23.99% ต่อปี เงื่อนไขเป็นไปตามที่บริษัทฯกำหนด
                </p>
            </form>
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