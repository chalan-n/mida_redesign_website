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
        <div class="container policy-content">
            <div style="text-align: center; margin-bottom: 40px;">
                <h2 style="color: var(--primary-blue); margin-bottom: 10px;">นโยบายความเป็นส่วนตัว (Privacy Policy)</h2>
                <h3 style="color: #333; margin-bottom: 5px;">บริษัท ไมด้า ลิสซิ่ง จำกัด (มหาชน)</h3>
                <p style="color: #666; font-size: 0.9rem;">ปรับปรุงล่าสุดเมื่อ: 19 มีนาคม 2566</p>
            </div>

            <p style="font-size: 1.1rem; line-height: 1.7; color: #333; margin-bottom: 30px;">
                บริษัท ไมด้า ลิสซิ่ง จำกัด (มหาชน) ("บริษัท") ให้ความสำคัญอย่างยิ่งต่อการคุ้มครองข้อมูลส่วนบุคคลของท่าน นโยบายนี้ฉบับนี้จัดทำขึ้นเพื่อชี้แจงรายละเอียดเกี่ยวกับการเก็บรวบรวม ใช้ และเปิดเผยข้อมูลส่วนบุคคล ตามพระราชบัญญัติคุ้มครองข้อมูลส่วนบุคคล พ.ศ. 2562 (PDPA)
            </p>

            <h3>1. ข้อมูลส่วนบุคคลที่เราเก็บรวบรวม</h3>
            <p>เราอาจเก็บรวบรวมข้อมูลส่วนบุคคลของท่านผ่านทางเว็บไซต์ ฟอร์มสมัครออนไลน์ หรือช่องทางติดต่ออื่นๆ ดังนี้:</p>
            
            <ul>
                <li><strong>ข้อมูลส่วนตัว:</strong> ชื่อ-นามสกุล, เลขประจำตัวประชาชน, วันเดือนปีเกิด</li>
                <li><strong>ข้อมูลการติดต่อ:</strong> เบอร์โทรศัพท์, ที่อยู่, LINE ID, อีเมล</li>
                <li><strong>ข้อมูลยานพาหนะ:</strong> ยี่ห้อ, รุ่น, ปีจดทะเบียน, เลขทะเบียนรถ (สำหรับการประเมินสินเชื่อ)</li>
                <li><strong>ข้อมูลทางเทคนิค:</strong> หมายเลข IP Address, คุกกี้ (Cookies), ประวัติการเข้าชมเว็บไซต์</li>
            </ul>

            <h3>2. วัตถุประสงค์ในการประมวลผลข้อมูล</h3>
            <p>บริษัทจะใช้ข้อมูลของท่านเพื่อวัตถุประสงค์ดังต่อไปนี้:</p>
            
            <ul>
                <li>เพื่อใช้ในการติดต่อกลับและนำเสนอรายละเอียดผลิตภัณฑ์สินเชื่อตามที่ท่านร้องขอ</li>
                <li>เพื่อใช้ประกอบการประเมินวงเงินสินเชื่อเบื้องต้นและการพิจารณาอนุมัติสินเชื่อ</li>
                <li>เพื่อการตรวจสอบตัวตนและป้องกันการทุจริต</li>
                <li>เพื่อพัฒนาและปรับปรุงประสิทธิภาพของเว็บไซต์และบริการของบริษัท</li>
                <li>เพื่อการปฏิบัติหน้าที่ตามกฎหมายที่เกี่ยวข้องกับธุรกิจสถาบันการเงินและลิสซิ่ง</li>
            </ul>

            <h3>3. การเปิดเผยข้อมูลส่วนบุคคล</h3>
            <p>บริษัทอาจเปิดเผยข้อมูลของท่านให้กับบุคคลหรือหน่วยงานภายนอกภายใต้ขอบเขตที่กฎหมายกำหนด ได้แก่:</p>
            
            <ul>
                <li>บริษัทในเครือไมด้า เพื่อการบริหารจัดการภายใน</li>
                <li>หน่วยงานราชการหรือหน่วยงานกำกับดูแล (เช่น ธนาคารแห่งประเทศไทย, กรมการขนส่งทางบก)</li>
                <li>บริษัทข้อมูลเครดิต (Credit Bureau) ในกรณีที่มีการดำเนินธุรกรรมสินเชื่อ</li>
                <li>พันธมิตรทางธุรกิจหรือผู้ให้บริการภายนอก (เช่น ผู้ให้บริการระบบ IT, บริษัทประกันภัย)</li>
            </ul>

            <h3>4. ระยะเวลาในการเก็บรักษาข้อมูล</h3>
            <p>บริษัทจะเก็บรักษาข้อมูลส่วนบุคคลของท่านไว้ตราบเท่าที่จำเป็นตามวัตถุประสงค์ที่ระบุไว้ข้างต้น หรือตามระยะเวลาที่กฎหมายกำหนด (โดยปกติจะเก็บรักษาไว้ 10 ปี หลังจากสิ้นสุดนิติสัมพันธ์กับบริษัท)</p>

            <h3>5. สิทธิของเจ้าของข้อมูลส่วนบุคคล</h3>
            <p>ท่านมีสิทธิตามกฎหมาย PDPA ดังนี้:</p>
            
            <ul>
                <li>สิทธิในการถอนความยินยอม</li>
                <li>สิทธิในการเข้าถึงและขอรับสำเนาข้อมูลส่วนบุคคล</li>
                <li>สิทธิในการขอแก้ไขข้อมูลให้ถูกต้องเป็นปัจจุบัน</li>
                <li>สิทธิในการขอให้ลบหรือทำลายข้อมูลส่วนบุคคล</li>
                <li>สิทธิในการคัดค้านการเก็บรวบรวม ใช้ หรือเปิดเผยข้อมูล</li>
            </ul>

            <h3>6. การรักษาความปลอดภัยของข้อมูล</h3>
            <p>บริษัทได้กำหนดมาตรการรักษาความมั่นคงปลอดภัยของข้อมูลที่เหมาะสม เพื่อป้องกันการสูญหาย การเข้าถึง การใช้ หรือการเปิดเผยข้อมูลส่วนบุคคลโดยปราศจากอำนาจหรือโดยมิชอบ</p>

            <h3>7. ข้อมูลคุกกี้ (Cookies)</h3>
            <p>เว็บไซต์ของเราใช้คุกกี้เพื่อเพิ่มประสบการณ์การใช้งานของท่าน ท่านสามารถตั้งค่าเบราว์เซอร์เพื่อปฏิเสธการทำงานของคุกกี้ได้ แต่อาจส่งผลกระทบต่อการใช้งานบางฟีเจอร์บนเว็บไซต์</p>

            <h3>8. ช่องทางการติดต่อ</h3>
            <p>หากท่านมีข้อสงสัยเกี่ยวกับนโยบายความเป็นส่วนตัวนี้ หรือต้องการใช้สิทธิตามกฎหมาย สามารถติดต่อได้ที่:</p>
            
            <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;">
                <p><strong>บริษัท ไมด้า ลิสซิ่ง จำกัด (มหาชน)</strong></p>
                <p><strong>ที่อยู่:</strong> 48/1-5 ซอยแจ้งวัฒนะ 14 ถนนแจ้งวัฒนะ แขวงทุ่งสองห้อง เขตหลักสี่ กรุงเทพฯ 10210</p>
                <p><strong>โทรศัพท์:</strong> 02-574-6901</p>
                <p><strong>เว็บไซต์:</strong> https://www.mida-leasing.com</p>
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