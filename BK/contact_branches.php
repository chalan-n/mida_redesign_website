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

$branches = [];
try {
    // Order by Region custom order using CASE
    // Order by Region custom order using CASE
    $search_q = isset($_GET['q']) ? trim($_GET['q']) : '';

    $where_sql = "";
    $params = [];

    if (!empty($search_q)) {
        $where_sql = "WHERE name LIKE :q OR address LIKE :q";
        $params[':q'] = "%$search_q%";
    }

    $sql = "SELECT * FROM branches $where_sql ORDER BY 
            CASE 
                WHEN name LIKE '%สำนักงานใหญ่%' THEN 0
                WHEN region = 'กลาง' THEN 1 
                WHEN region = 'เหนือ' THEN 2 
                WHEN region = 'อีสาน' THEN 3 
                WHEN region = 'ตะวันออกเฉียงเหนือ' THEN 3 
                WHEN region = 'ใต้' THEN 4 
                ELSE 5 
            END, name ASC";

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $branches = $stmt->fetchAll();

    // Set Default Map (Head Office)
    $default_map = "https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d15933092.48633394!2d100.0!3d13.0!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x304d8df747424db1%3A0x9ed72c880757e84f!2sThailand!5e0!3m2!1sen!2sth!4v1645500000000!5m2!1sen!2sth"; // Fallback
    foreach ($branches as $b) {
        // Only set as default if it's the Head Office AND it's an embeddable link
        if (strpos($b['name'], 'สำนักงานใหญ่') !== false && !empty($b['map_url']) && strpos($b['map_url'], 'maps/embed') !== false) {
            $default_map = $b['map_url'];
            break;
        }
    }
} catch (PDOException $e) {
    // Handle error
}

$grouped_branches = [];
foreach ($branches as $branch) {
    $region_name = $branch['region'];
    if ($region_name == 'อีสาน')
        $region_name = 'ตะวันออกเฉียงเหนือ'; // Display name
    $grouped_branches[$region_name][] = $branch;
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สาขาของเรา - MIDA LEASING</title>
    <meta name="description" content="ค้นหาสาขาไมด้าลิสซิ่งใกล้บ้านท่าน พร้อมเบอร์โทรและแผนที่เดินทาง">

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
            background: linear-gradient(135deg, #002D62 0%, #1c4587 50%, #2e5d9e 100%);
            color: white;
            padding: 140px 0 80px;
            text-align: center;
            position: relative;
        }

        .page-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="40" fill="none" stroke="rgba(255,255,255,0.05)" stroke-width="1"/></svg>') repeat;
            opacity: 0.3;
        }

        .branch-search-box {
            background: white;
            padding: 30px 40px;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.12);
            margin-top: -50px;
            position: relative;
            z-index: 10;
        }

        .stats-bar {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin: 30px 0;
        }

        .stat-item {
            text-align: center;
            padding: 20px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: #002D62;
        }

        .stat-label {
            color: #666;
            font-size: 0.9rem;
            margin-top: 5px;
        }

        .branch-card {
            background: white;
            border: none;
            border-radius: 16px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .branch-card::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 5px;
            background: linear-gradient(180deg, #C5A059, #e8c97b);
        }

        .branch-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.12);
        }

        .branch-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #002D62;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
        }

        .branch-badge {
            background: linear-gradient(135deg, #C5A059, #e8c97b);
            color: white;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            margin-left: 10px;
            font-weight: 500;
        }

        .branch-info {
            font-size: 0.95rem;
            color: #555;
            line-height: 1.8;
        }

        .branch-info p {
            display: flex;
            align-items: flex-start;
            margin-bottom: 8px;
        }

        .branch-info i {
            width: 25px;
            color: #C5A059;
            margin-right: 12px;
            margin-top: 3px;
        }

        .branch-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #f0f0f0;
        }

        .btn-branch {
            flex: 1;
            padding: 10px 15px;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 500;
            text-decoration: none;
            text-align: center;
            transition: all 0.2s;
        }

        .btn-branch-primary {
            background: #002D62;
            color: white;
        }

        .btn-branch-primary:hover {
            background: #1c4587;
        }

        .btn-branch-outline {
            background: white;
            color: #002D62;
            border: 2px solid #002D62;
        }

        .btn-branch-outline:hover {
            background: #002D62;
            color: white;
        }

        .region-title {
            background: linear-gradient(135deg, #002D62, #1c4587);
            color: white;
            padding: 15px 25px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1.1rem;
            margin: 30px 0 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .region-count {
            background: rgba(255, 255, 255, 0.2);
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
        }

        .branch-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
        }

        @media (max-width: 768px) {
            .stats-bar {
                grid-template-columns: repeat(2, 1fr);
            }

            .branch-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>

    <!-- Header -->
    <?php $active_page = 'branches'; include 'includes/nav.php'; ?>

    <!-- Page Header -->
    <div class="page-header">
        <div class="container" style="position: relative; z-index: 1;">
            <div
                style="display: inline-block; background: rgba(255,255,255,0.15); padding: 8px 20px; border-radius: 30px; margin-bottom: 15px;">
                <i class="fa-solid fa-location-dot"></i> ค้นหาสาขาใกล้บ้านคุณ
            </div>
            <h1 style="font-size: 2.8rem; margin-bottom: 15px; font-weight: 700; color: #C5A059;">สาขาของเรา</h1>
            <p style="opacity: 0.9; font-size: 1.15rem; max-width: 600px; margin: 0 auto;">
                ไมด้าลิสซิ่งพร้อมให้บริการทั่วประเทศ ด้วยทีมงานมืออาชีพ
            </p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="section" style="padding-top: 0; background: linear-gradient(180deg, #f8f9fa 0%, #ffffff 100%);">
        <div class="container">

            <!-- Search Box -->
            <div class="branch-search-box">
                <form method="GET" action="">
                    <div style="display: grid; grid-template-columns: 1fr auto; gap: 20px; align-items: end;">
                        <div>
                            <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #333;">
                                <i class="fa-solid fa-magnifying-glass" style="color: #C5A059; margin-right: 8px;"></i>
                                ค้นหาสาขา
                            </label>
                            <input type="text" name="q" placeholder="พิมพ์ชื่อสาขา, จังหวัด หรือเขต..."
                                value="<?php echo htmlspecialchars($search_q); ?>"
                                style="width: 100%; padding: 14px 18px; border: 2px solid #e8e8e8; border-radius: 10px; font-size: 1rem; transition: border 0.2s;"
                                onfocus="this.style.borderColor='#002D62'" onblur="this.style.borderColor='#e8e8e8'">
                        </div>
                        <button type="submit" class="btn btn-primary"
                            style="height: 52px; padding: 0 35px; font-size: 1rem;">
                            <i class="fa-solid fa-search"></i> ค้นหา
                        </button>
                    </div>
                </form>
            </div>

            <!-- Branch List -->
            <div>
                <?php if (count($grouped_branches) > 0): ?>
                    <?php foreach ($grouped_branches as $region => $region_branches): ?>
                        <div class="region-title">
                            <span><i class="fa-solid fa-map-marker-alt" style="margin-right: 10px;"></i>
                                ภาค<?php echo htmlspecialchars($region); ?></span>
                            <span class="region-count"><?php echo count($region_branches); ?> สาขา</span>
                        </div>
                        <div class="branch-grid">
                            <?php foreach ($region_branches as $branch): ?>
                                <div class="branch-card">
                                    <div class="branch-title">
                                        <i class="fa-solid fa-building" style="color: #C5A059; margin-right: 12px;"></i>
                                        <?php echo htmlspecialchars($branch['name']); ?>
                                        <?php if (strpos($branch['name'], 'สำนักงานใหญ่') !== false): ?>
                                            <span class="branch-badge">สำนักงานใหญ่</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="branch-info">
                                        <p><i class="fa-solid fa-map-pin"></i>
                                            <span><?php echo htmlspecialchars($branch['address']); ?></span>
                                        </p>
                                        <p><i class="fa-solid fa-phone"></i> <span
                                                style="font-weight: 600; color: #002D62;"><?php echo htmlspecialchars($branch['phone']); ?></span>
                                        </p>
                                        <p><i class="fa-solid fa-clock"></i>
                                            <span><?php echo htmlspecialchars($branch['hours']); ?></span>
                                        </p>
                                    </div>
                                    <div class="branch-actions">
                                        <a href="tel:<?php echo preg_replace('/[^0-9]/', '', $branch['phone']); ?>"
                                            class="btn-branch btn-branch-primary">
                                            <i class="fa-solid fa-phone"></i> โทร
                                        </a>
                                        <a href="<?php echo htmlspecialchars($branch['map_url']); ?>" target="_blank"
                                            class="btn-branch btn-branch-outline">
                                            <i class="fa-solid fa-location-arrow"></i> นำทาง
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div
                        style="text-align: center; padding: 60px; background: white; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.08);">
                        <i class="fa-solid fa-search" style="font-size: 3rem; color: #ddd; margin-bottom: 20px;"></i>
                        <p style="color: #888; font-size: 1.1rem;">ไม่พบสาขาที่ค้นหา</p>
                        <a href="contact_branches.php" class="btn btn-primary" style="margin-top: 15px;">ดูสาขาทั้งหมด</a>
                    </div>
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