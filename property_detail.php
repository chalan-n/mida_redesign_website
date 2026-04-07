<?php
require_once 'admin/config/db.php';
$database = new Database();
$db = $database->getConnection();

// Track visitor
@include_once 'track_visitor.php';

// Fetch Settings (Optional, using defaults if fails)
$settings = [];
try {
    $stmt = $db->query("SELECT * FROM settings WHERE id = 1");
    $settings = $stmt->fetch();
} catch (PDOException $e) {
}


// Handle Lead Form Submission
$lead_success = false;
$lead_error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_lead'])) {
    $prop_id = $_POST['property_id'];
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $line_id = $_POST['line_id'];
    $message = $_POST['message'];

    if (!empty($name) && !empty($phone)) {
        try {
            $sql = "INSERT INTO property_leads (property_id, name, phone, line_id, message) VALUES (:pid, :name, :phone, :lid, :msg)";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':pid' => $prop_id,
                ':name' => $name,
                ':phone' => $phone,
                ':lid' => $line_id,
                ':msg' => $message
            ]);
            $lead_success = true;
        } catch (PDOException $e) {
            $lead_error = "เกิดข้อผิดพลาด: " . $e->getMessage();
        }
    } else {
        $lead_error = "กรุณากรอกชื่อและเบอร์โทรศัพท์";
    }
}

// Fetch Property Details
$property = null;
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    try {
        $stmt = $db->prepare("SELECT * FROM properties WHERE id = :id AND is_active = 1");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $property = $stmt->fetch();
    } catch (PDOException $e) {
    }
}

// Redirect if not found
if (!$property) {
    header("Location: properties.php");
    exit;
}

// Fetch Related Properties (Random 3)
$related_properties = [];
try {
    $stmt_related = $db->prepare("SELECT * FROM properties WHERE id != :id AND is_active = 1 ORDER BY RAND() LIMIT 3");
    $stmt_related->bindParam(':id', $id);
    $stmt_related->execute();
    $related_properties = $stmt_related->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
}

// Prepare Images
$images = [];
if (!empty($property['image_path']))
    $images[] = $property['image_path'];
if (!empty($property['image_path_2']))
    $images[] = $property['image_path_2'];
if (!empty($property['image_path_3']))
    $images[] = $property['image_path_3'];
if (!empty($property['image_path_4']))
    $images[] = $property['image_path_4'];
if (!empty($property['image_path_5']))
    $images[] = $property['image_path_5'];
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?php echo htmlspecialchars($property['title']); ?> - MIDA LEASING
    </title>
    <meta name="description" content="<?php echo htmlspecialchars(mb_substr($property['description'], 0, 150)); ?>">

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
            background: linear-gradient(135deg, #1c4587 0%, #004a99 100%);
            color: white;
            padding: 140px 0 60px;
            text-align: center;
        }

        .gallery-container {
            display: grid;
            gap: 10px;
            margin-bottom: 30px;
        }

        .main-image {
            width: 100%;
            height: 400px;
            background-color: #ddd;
            border-radius: 12px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 5rem;
            color: #999;
            position: relative;
        }

        .thumb-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
        }

        .thumb-image {
            height: 80px;
            background-color: #eee;
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ccc;
            transition: all 0.2s;
        }

        .thumb-image:hover {
            opacity: 0.8;
            border: 2px solid var(--primary-blue);
        }

        .prop-badge {
            display: inline-block;
            background: var(--accent-gold);
            color: #000;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .price-tag {
            font-size: 2.2rem;
            font-weight: 700;
            color: var(--primary-blue);
            margin: 15px 0;
        }

        .feature-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
        }

        .feature-item {
            text-align: center;
        }

        .feature-item i {
            display: block;
            font-size: 1.5rem;
            color: var(--primary-blue);
            margin-bottom: 5px;
        }

        .feature-item span {
            font-size: 0.9rem;
            color: #666;
        }

        .feature-item strong {
            display: block;
            font-size: 1.1rem;
            color: #333;
        }

        .contact-box {
            background: white;
            border: 1px solid #eee;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            position: sticky;
            top: 100px;
        }

        .detail-section {
            margin-top: 40px;
        }

        .detail-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #eee;
        }

        @media (max-width: 768px) {
            .main-image {
                height: 250px;
            }
        }
    </style>
</head>

<body>

    <!-- Header -->
    <?php $active_page = 'properties'; include 'includes/nav.php'; ?>

    <!-- Page Header (Mini) -->
    <div class="page-header" style="padding: 120px 0 40px;">
        <div class="container">
            <h1 style="font-size: 2rem; margin-bottom: 0; color: #fec435;">รายละเอียดทรัพย์สิน</h1>
            <p style="margin-top: 10px; opacity: 0.9;">
                <?php echo htmlspecialchars($property['title']); ?>
            </p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="section" style="padding-top: 40px; background-color: #fff; min-height: 80vh;">
        <div class="container">

            <div style="margin-bottom: 20px;">
                <a href="properties.php"
                    style="text-decoration: none; color: #666; display: inline-flex; align-items: center;">
                    <i class="fa-solid fa-arrow-left" style="margin-right: 8px;"></i> กลับหน้ารายการ
                </a>
            </div>

            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 40px;">

                <!-- Left Content: Images & Details -->
                <div>
                    <!-- Gallery -->
                    <!-- Gallery -->
                    <div class="gallery-container">
                        <div class="main-image">
                            <?php if (count($images) > 0): ?>
                                <img id="main-display-img" src="<?php echo htmlspecialchars($images[0]); ?>"
                                    alt="<?php echo htmlspecialchars($property['title']); ?>"
                                    style="width: 100%; height: 100%; object-fit: cover;">
                            <?php else: ?>
                                <i
                                    class="fa-solid <?php echo $property['type'] === 'ที่ดินเปล่า' ? 'fa-mountain-sun' : 'fa-house-chimney'; ?>"></i>
                            <?php endif; ?>

                            <?php if (!empty($property['property_code'])): ?>
                                <span
                                    style="position: absolute; top: 15px; left: 15px; background: rgba(0,0,0,0.6); color: white; padding: 5px 15px; border-radius: 20px; font-size: 1rem;">
                                    รหัสทรัพย์:
                                    <?php echo htmlspecialchars($property['property_code']); ?>
                                </span>
                            <?php endif; ?>

                            <span
                                style="position: absolute; bottom: 15px; right: 15px; background: rgba(0,0,0,0.6); color: white; padding: 5px 15px; border-radius: 20px; font-size: 0.9rem;">
                                <i class="fa-solid fa-camera"></i>
                                <?php echo count($images); ?> รูป
                            </span>
                        </div>
                        <div class="thumb-grid"
                            style="grid-template-columns: repeat(<?php echo count($images); ?>, 1fr);">
                            <?php foreach ($images as $idx => $img): ?>
                                <div class="thumb-image <?php echo $idx === 0 ? 'active' : ''; ?>"
                                    onclick="document.getElementById('main-display-img').src='<?php echo htmlspecialchars($img); ?>'; setActiveThumb(this);">
                                    <img src="<?php echo htmlspecialchars($img); ?>"
                                        style="width: 100%; height: 100%; object-fit: cover; border-radius: 8px;">
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <script>
                            function setActiveThumb(el) {
                                document.querySelectorAll('.thumb-image').forEach(t => t.classList.remove('active'));
                                el.classList.add('active');
                            }
                        </script>
                        <style>
                            .thumb-image.active {
                                border: 3px solid var(--primary-blue);
                            }
                        </style>
                    </div>

                    <div>
                        <span class="prop-badge">
                            <?php echo htmlspecialchars($property['type']); ?>
                        </span>
                        <h2 style="font-size: 1.8rem; margin-bottom: 10px; line-height: 1.3;">
                            <?php echo htmlspecialchars($property['title']); ?>
                        </h2>
                        <p style="color: #666; font-size: 1.1rem;"><i class="fa-solid fa-location-dot"
                                style="color: var(--primary-blue); margin-right: 5px;"></i>
                            <?php echo htmlspecialchars($property['location']); ?>
                        </p>

                        <div class="feature-grid" <?php if ($property['type'] === 'ที่ดินเปล่า')
                            echo 'style="grid-template-columns: 1fr;"'; ?>>
                            <?php if ($property['type'] !== 'ที่ดินเปล่า'): ?>
                                <div class="feature-item">
                                    <i class="fa-solid fa-bed"></i>
                                    <span>ห้องนอน</span>
                                    <strong>
                                        <?php echo number_format($property['bed']); ?> ห้อง
                                    </strong>
                                </div>
                                <div class="feature-item">
                                    <i class="fa-solid fa-bath"></i>
                                    <span>ห้องน้ำ</span>
                                    <strong>
                                        <?php echo number_format($property['bath']); ?> ห้อง
                                    </strong>
                                </div>
                                <div class="feature-item">
                                    <i class="fa-solid fa-ruler-combined"></i>
                                    <span>พื้นที่ใช้สอย</span>
                                    <strong>
                                        <?php echo !empty($property['usage_area']) ? htmlspecialchars($property['usage_area']) : '-'; ?>
                                    </strong>
                                </div>
                            <?php endif; ?>
                            <div class="feature-item">
                                <i
                                    class="fa-solid fa-<?php echo $property['type'] === 'ที่ดินเปล่า' ? 'mountain-sun' : 'maximize'; ?>"></i>
                                <span>ขนาดที่ดิน</span>
                                <strong>
                                    <?php echo !empty($property['land_size']) ? htmlspecialchars($property['land_size']) : $property['area']; ?>
                                </strong>
                            </div>
                            <?php if ($property['type'] !== 'ที่ดินเปล่า'): ?>
                                <div class="feature-item">
                                    <i class="fa-solid fa-car"></i>
                                    <span>ที่จอดรถ</span>
                                    <strong>
                                        <?php echo !empty($property['parking']) ? htmlspecialchars($property['parking']) : '-'; ?>
                                    </strong>
                                </div>
                                <div class="feature-item">
                                    <i class="fa-solid fa-compass"></i>
                                    <span>ทิศหน้าบ้าน</span>
                                    <strong>
                                        <?php echo !empty($property['direction']) ? htmlspecialchars($property['direction']) : '-'; ?>
                                    </strong>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="detail-section">
                            <h3 class="detail-title">รายละเอียดเพิ่มเติม</h3>
                            <div style="line-height: 1.6; color: #444;">
                                <?php echo nl2br(htmlspecialchars($property['description'])); ?>
                            </div>
                        </div>

                        <?php if (!empty($property['map_url'])): ?>
                            <div class="detail-section">
                                <h3 class="detail-title">แผนที่</h3>
                                <div style="background: #eee; height: 350px; border-radius: 10px; overflow: hidden;">
                                    <iframe src="<?php echo htmlspecialchars($property['map_url']); ?>" width="100%"
                                        height="100%" style="border:0;" allowfullscreen="" loading="lazy"
                                        referrerpolicy="no-referrer-when-downgrade"></iframe>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Right Content: Contact Box -->
                <div>
                    <div class="contact-box">
                        <div class="price-tag">
                            <?php echo htmlspecialchars($property['price']); ?>
                        </div>
                        <?php if (!empty($property['price_appraised'])): ?>
                            <p style="color: #666; margin-bottom: 20px;">ราคาประเมิน:
                                <?php echo htmlspecialchars($property['price_appraised']); ?>
                            </p>
                        <?php endif; ?>

                        <a href="https://line.me/R/ti/p/@midaleasing" target="_blank" class="btn btn-primary"
                            style="width: 100%; text-align: center; margin-bottom: 10px;">
                            <i class="fa-brands fa-line" style="margin-right: 5px;"></i> สนใจติดต่อทางไลน์
                        </a>
                        <a href="tel:025746901" class="btn btn-primary btn-outline"
                            style="width: 100%; text-align: center;">
                            <i class="fa-solid fa-phone" style="margin-right: 5px;"></i> 02-574-6901
                        </a>

                        <hr style="border: 0; border-top: 1px solid #eee; margin: 20px 0;">

                        <div style="text-align: center; margin-bottom: 25px;">
                            <h4 style="font-size: 1rem; margin-bottom: 15px;">แชร์หน้านี้</h4>
                            <?php
                            $current_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

                            // Fetch active share buttons
                            $share_buttons = [];
                            try {
                                $stmt_share = $db->query("SELECT * FROM share_buttons WHERE is_active = 1 ORDER BY sort_order ASC");
                                $share_buttons = $stmt_share->fetchAll();
                            } catch (PDOException $e) {
                                // Fallback if table doesn't exist
                            }
                            ?>
                            <div style="display: flex; justify-content: center; gap: 15px;">
                                <?php foreach ($share_buttons as $btn): ?>
                                    <?php if ($btn['url_pattern'] === 'copy'): ?>
                                        <a href="javascript:void(0)" onclick="copyToClipboard('<?php echo $current_url; ?>')"
                                            style="color: <?php echo htmlspecialchars($btn['color']); ?>; font-size: 1.5rem;"
                                            title="<?php echo htmlspecialchars($btn['name']); ?>">
                                            <i class="<?php echo htmlspecialchars($btn['icon']); ?>"></i>
                                        </a>
                                    <?php else: ?>
                                        <?php
                                        $share_url = str_replace(
                                            ['{URL}', '{TITLE}'],
                                            [urlencode($current_url), urlencode($property['title'])],
                                            $btn['url_pattern']
                                        );
                                        ?>
                                        <a href="<?php echo $share_url; ?>" target="_blank"
                                            style="color: <?php echo htmlspecialchars($btn['color']); ?>; font-size: 1.5rem;"
                                            title="<?php echo htmlspecialchars($btn['name']); ?>">
                                            <i class="<?php echo htmlspecialchars($btn['icon']); ?>"></i>
                                        </a>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                            <script>
                                function copyToClipboard(text) {
                                    navigator.clipboard.writeText(text).then(function () {
                                        alert('คัดลอกลิงก์เรียบร้อยแล้ว');
                                    }, function (err) {
                                        console.error('Could not copy text: ', err);
                                    });
                                }
                            </script>
                        </div>

                        <h4 style="margin-bottom: 15px;">ฝากเบอร์ติดต่อกลับ</h4>

                        <?php if ($lead_success): ?>
                            <div class="alert alert-success"
                                style="background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin-bottom: 15px;">
                                บันทึกข้อมูลเรียบร้อยแล้ว เจ้าหน้าที่จะติดต่อกลับโดยเร็วที่สุด
                            </div>
                        <?php endif; ?>

                        <?php if ($lead_error): ?>
                            <div class="alert alert-danger"
                                style="background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 15px;">
                                <?php echo $lead_error; ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST">
                            <input type="hidden" name="property_id" value="<?php echo $property['id']; ?>">
                            <div style="margin-bottom: 15px;">
                                <input type="text" name="name" placeholder="ชื่อ-นามสกุล *" required
                                    style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                            </div>
                            <div style="margin-bottom: 15px;">
                                <input type="tel" name="phone" placeholder="เบอร์โทรศัพท์ *" required
                                    style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                            </div>
                            <div style="margin-bottom: 15px;">
                                <input type="text" name="line_id" placeholder="ID Line (ถ้ามี)"
                                    style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                            </div>
                            <div style="margin-bottom: 15px;">
                                <textarea name="message" placeholder="ข้อความเพิ่มเติม (ถ้ามี)" rows="3"
                                    style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;"></textarea>
                            </div>
                            <button type="submit" name="submit_lead" class="btn btn-primary"
                                style="width: 100%;">ส่งข้อมูล</button>
                        </form>

                    </div>
                </div>

            </div>

            <!-- Related Properties -->
            <div style="margin-top: 60px;">
                <h3 style="margin-bottom: 30px;">ทรัพย์สินที่น่าสนใจอื่นๆ</h3>
                <div class="prop-grid"
                    style="grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); display: grid; gap: 20px;">
                    <?php if (count($related_properties) > 0): ?>
                        <?php foreach ($related_properties as $rel_prop): ?>
                            <div class="prop-card">
                                <div class="prop-img" style="height: 180px;">
                                    <?php if (!empty($rel_prop['image_path'])): ?>
                                        <img src="<?php echo htmlspecialchars($rel_prop['image_path']); ?>"
                                            alt="<?php echo htmlspecialchars($rel_prop['title']); ?>"
                                            style="width: 100%; height: 100%; object-fit: cover;">
                                    <?php else: ?>
                                        <i
                                            class="fa-solid <?php echo $rel_prop['type'] === 'ที่ดินเปล่า' ? 'fa-mountain-sun' : 'fa-house-chimney'; ?>"></i>
                                    <?php endif; ?>
                                    <span class="prop-badge"
                                        style="position: absolute; top: 10px; left: 10px; color: #000;"><?php echo htmlspecialchars($rel_prop['type']); ?></span>
                                </div>
                                <div class="prop-content" style="padding: 15px;">
                                    <h3 class="prop-title" style="font-size: 1rem;">
                                        <?php echo htmlspecialchars($rel_prop['title']); ?>
                                    </h3>
                                    <div class="prop-price" style="font-size: 1.2rem;">
                                        <?php echo htmlspecialchars($rel_prop['price']); ?>
                                    </div>
                                    <a href="property_detail.php?id=<?php echo $rel_prop['id']; ?>"
                                        class="btn btn-primary btn-sm"
                                        style="margin-top: 10px; display: block; text-align: center;">ดูรายละเอียด</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="color: #666;">ไม่มีทรัพย์สินที่น่าสนใจอื่นๆ</p>
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