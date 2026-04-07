<?php
require_once 'admin/config/db.php';
$database = new Database();
$db = $database->getConnection();

// Track visitor
@include_once 'track_visitor.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

// Fetch Item
$stmt = $db->prepare("SELECT * FROM announcements WHERE id = :id AND is_active = 1");
$stmt->bindParam(':id', $id);
$stmt->execute();
$news = $stmt->fetch();

if (!$news) {
    header("Location: news.php");
    exit;
}

// Update View Count
$db->query("UPDATE announcements SET view_count = view_count + 1 WHERE id = $id");

// Fetch Related News (Same Category)
$stmt_related = $db->prepare("SELECT * FROM announcements WHERE category = :category AND id != :id AND is_active = 1 ORDER BY start_date DESC LIMIT 3");
$stmt_related->bindParam(':category', $news['category']);
$stmt_related->bindParam(':id', $id);
$stmt_related->execute();
$related_news = $stmt_related->fetchAll();

// Fetch Settings for Footer
$stmt_settings = $db->query("SELECT * FROM settings WHERE id = 1");
$settings = $stmt_settings->fetch();

?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?php echo htmlspecialchars($news['title']); ?> - MIDA LEASING
    </title>

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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        .news-header-img {
            width: 100%;
            height: 400px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 30px;
        }

        .news-meta-bar {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 20px;
            color: #666;
            font-size: 0.95rem;
            border-bottom: 1px solid #eee;
            padding-bottom: 20px;
        }

        .news-category-badge {
            background: var(--primary-blue);
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
        }

        .news-content-body {
            font-size: 1.1rem;
            line-height: 1.8;
            color: #333;
            margin-bottom: 50px;
        }

        .news-content-body p {
            margin-bottom: 20px;
        }

        .sidebar-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--accent-gold);
        }

        .related-item {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            text-decoration: none;
            color: inherit;
        }

        .related-img {
            width: 100px;
            height: 70px;
            object-fit: cover;
            border-radius: 4px;
        }

        .related-info h4 {
            font-size: 0.95rem;
            margin: 0 0 5px 0;
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .related-date {
            font-size: 0.8rem;
            color: #888;
        }
    </style>
</head>

<body>

    <!-- Header -->
    <?php include 'includes/nav.php'; ?>

    <!-- Page Header -->
    <div style="height: 80px; margin-top: 60px;"></div>

    <!-- Content -->
    <section class="section">
        <div class="container">
            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 40px;">

                <!-- Main Content -->
                <div>
                    <a href="news.php"
                        style="color: #888; text-decoration: none; display: inline-flex; align-items: center; margin-bottom: 20px;">
                        <i class="fa-solid fa-arrow-left" style="margin-right: 5px;"></i> ย้อนกลับไปหน้าข่าวสาร
                    </a>

                    <h1 style="font-size: 2rem; margin-bottom: 20px;">
                        <?php echo htmlspecialchars($news['title']); ?>
                    </h1>

                    <div class="news-meta-bar">
                        <span class="news-category-badge">
                            <?php echo $news['category']; ?>
                        </span>
                        <span>
                            <i class="fa-regular fa-calendar" style="margin-right: 5px;"></i>
                            <?php echo date('d M Y', strtotime($news['start_date'])); ?>
                        </span>
                        <span>
                            <i class="fa-regular fa-eye" style="margin-right: 5px;"></i>
                            <?php echo $news['view_count']; ?> วิว
                        </span>
                    </div>

                    <?php if (!empty($news['cover_image'])): ?>
                        <img src="<?php echo $news['cover_image']; ?>" alt="<?php echo htmlspecialchars($news['title']); ?>"
                            class="news-header-img">
                    <?php endif; ?>

                    <div class="news-content-body">
                        <?php echo nl2br($news['content']); ?>
                    </div>
                </div>

                <!-- Sidebar -->
                <div>
                    <div class="card"
                        style="padding: 20px; border: 1px solid #eee; border-radius: 8px; position: sticky; top: 100px;">
                        <h3 class="sidebar-title">ข่าวสารที่เกี่ยวข้อง</h3>

                        <?php if (count($related_news) > 0): ?>
                            <?php foreach ($related_news as $item): ?>
                                <a href="news_detail.php?id=<?php echo $item['id']; ?>" class="related-item">
                                    <?php if (!empty($item['cover_image'])): ?>
                                        <img src="<?php echo $item['cover_image']; ?>" class="related-img">
                                    <?php else: ?>
                                        <div style="width: 100px; height: 70px; background: #eee; border-radius: 4px;"></div>
                                    <?php endif; ?>
                                    <div class="related-info">
                                        <h4>
                                            <?php echo htmlspecialchars($item['title']); ?>
                                        </h4>
                                        <span class="related-date"><i class="fa-regular fa-calendar"></i>
                                            <?php echo date('d/m/Y', strtotime($item['start_date'])); ?>
                                        </span>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p style="color: #888;">ไม่มีข่าวสารที่เกี่ยวข้อง</p>
                        <?php endif; ?>

                        <div style="margin-top: 30px;">
                            <h3 class="sidebar-title">หมวดหมู่</h3>
                            <ul style="list-style: none; padding: 0;">
                                <li style="margin-bottom: 10px;"><a href="news.php?category=News"
                                        style="text-decoration: none; color: #555;">ข่าวประชาสัมพันธ์</a></li>
                                <li style="margin-bottom: 10px;"><a href="news.php?category=Activity"
                                        style="text-decoration: none; color: #555;">กิจกรรม</a></li>
                                <li style="margin-bottom: 10px;"><a href="news.php?category=Promotion"
                                        style="text-decoration: none; color: #555;">โปรโมชั่น</a></li>
                            </ul>
                        </div>
                    </div>
                </div>

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
                    <?php if ($settings): ?>
                        <p style="color: #ccc; margin-bottom: 10px; font-size: 1rem;">
                            <?php echo nl2br(htmlspecialchars($settings['site_address'])); ?>
                        </p>
                        <p style="color: #ccc; margin-bottom: 20px; font-size: 1rem;"><i class="fa-solid fa-phone"
                                style="margin-right: 10px;"></i>
                            <?php echo htmlspecialchars($settings['site_phone']); ?>
                        </p>
                        <div style="display: flex; gap: 15px;">
                            <a href="<?php echo htmlspecialchars($settings['site_facebook']); ?>" target="_blank"
                                style="text-decoration: none;">
                                <i class="fa-brands fa-facebook" style="font-size: 2rem; color: #1877F2;"></i>
                            </a>
                            <a href="<?php echo htmlspecialchars($settings['site_line']); ?>" target="_blank"
                                style="text-decoration: none;">
                                <i class="fa-brands fa-line" style="font-size: 2rem; color: #00B900;"></i>
                            </a>
                        </div>
                    <?php endif; ?>
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
            </div>
        </div>
    </footer>

    <script src="assets/js/main.js"></script>
</body>

</html>