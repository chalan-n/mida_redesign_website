<?php
require_once 'admin/config/db.php';
$database = new Database();
$db = $database->getConnection();

// Track visitor
@include_once 'track_visitor.php';

// Pagination
$limit = 9;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$start = ($page - 1) * $limit;

// Category Filter
$category = isset($_GET['category']) ? $_GET['category'] : '';
$where_sql = "WHERE is_active = 1";
if ($category) {
    $where_sql .= " AND category = :category";
}
$where_sql .= " AND (is_popup = 0) AND (start_date IS NULL OR start_date <= CURDATE()) AND (end_date IS NULL OR end_date >= CURDATE())";

// Count Total
$sql_count = "SELECT COUNT(*) FROM announcements " . $where_sql;
$stmt_count = $db->prepare($sql_count);
if ($category)
    $stmt_count->bindParam(':category', $category);
$stmt_count->execute();
$total_rows = $stmt_count->fetchColumn();
$total_pages = ceil($total_rows / $limit);

// Fetch Items
$sql = "SELECT * FROM announcements " . $where_sql . " ORDER BY start_date DESC, created_at DESC LIMIT :start, :limit";
$stmt = $db->prepare($sql);
if ($category)
    $stmt->bindParam(':category', $category);
$stmt->bindParam(':start', $start, PDO::PARAM_INT);
$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
$stmt->execute();
$news_items = $stmt->fetchAll();

// Fetch Categories for Filter
$stmt_cat = $db->query("SELECT DISTINCT category FROM announcements WHERE is_active = 1");
$categories = $stmt_cat->fetchAll(PDO::FETCH_COLUMN);

// Fetch Settings for Footer
$stmt_settings = $db->query("SELECT * FROM settings WHERE id = 1");
$settings = $stmt_settings->fetch();

?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ข่าวสารและกิจกรรม - MIDA LEASING</title>

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
        .news-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 30px;
            margin-top: 30px;
        }

        .news-card {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s;
            display: flex;
            flex-direction: column;
            text-decoration: none;
            color: inherit;
        }

        .news-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .news-img {
            height: 220px;
            overflow: hidden;
        }

        .news-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s;
        }

        .news-card:hover .news-img img {
            transform: scale(1.05);
        }

        .news-content {
            padding: 25px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .news-meta {
            display: flex;
            justify-content: space-between;
            font-size: 0.85rem;
            color: #888;
            margin-bottom: 10px;
        }

        .news-category {
            color: var(--primary-blue);
            font-weight: 500;
        }

        .news-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 15px;
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .news-excerpt {
            color: #666;
            margin-bottom: 20px;
            font-size: 0.95rem;
            line-height: 1.6;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .read-more {
            margin-top: auto;
            color: var(--primary-blue);
            font-weight: 500;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
        }

        .read-more i {
            margin-left: 5px;
            transition: margin-left 0.2s;
        }

        .news-card:hover .read-more i {
            margin-left: 10px;
        }

        .pagination {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 50px;
        }

        .page-link {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: white;
            border: 1px solid #ddd;
            border-radius: 4px;
            color: #333;
            text-decoration: none;
            transition: 0.2s;
        }

        .page-link.active,
        .page-link:hover {
            background: var(--primary-blue);
            color: white;
            border-color: var(--primary-blue);
        }

        .filter-nav {
            display: flex;
            justify-content: center;
            gap: 15px;
            flex-wrap: wrap;
            margin-bottom: 30px;
        }

        .filter-btn {
            background: white;
            border: 1px solid #ddd;
            padding: 8px 20px;
            border-radius: 20px;
            color: #555;
            text-decoration: none;
            transition: 0.2s;
        }

        .filter-btn.active,
        .filter-btn:hover {
            background: var(--primary-blue);
            color: white;
            border-color: var(--primary-blue);
        }
    </style>
</head>

<body>

    <!-- Header -->
    <?php include 'includes/nav.php'; ?>

    <!-- Page Header -->
    <div
        style="background: var(--primary-blue); padding: 80px 0 40px 0; margin-top: 60px; color: white; text-align: center;">
        <div class="container">
            <h1 style="font-size: 2.5rem; margin-bottom: 10px; color: #FFC107;">ข่าวสารและกิจกรรม</h1>
            <p style="opacity: 0.8;">อัพเดทข่าวสาร โปรโมชั่น และกิจกรรมล่าสุดจากไมด้า ลิสซิ่ง</p>
        </div>
    </div>

    <!-- Content -->
    <section class="section" style="background-color: #f9f9f9; min-height: 600px;">
        <div class="container">

            <!-- Filter -->
            <div class="filter-nav">
                <a href="news.php" class="filter-btn <?php echo $category == '' ? 'active' : ''; ?>">ทั้งหมด</a>
                <?php foreach ($categories as $cat): ?>
                    <a href="news.php?category=<?php echo urlencode($cat); ?>"
                        class="filter-btn <?php echo $category == $cat ? 'active' : ''; ?>">
                        <?php echo $cat == 'News' ? 'ข่าวประชาสัมพันธ์' : ($cat == 'Activity' ? 'กิจกรรม' : ($cat == 'Promotion' ? 'โปรโมชั่น' : $cat)); ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <div class="news-grid">
                <?php if (count($news_items) > 0): ?>
                    <?php foreach ($news_items as $item): ?>
                        <a href="news_detail.php?id=<?php echo $item['id']; ?>" class="news-card">
                            <div class="news-img">
                                <?php if (!empty($item['cover_image'])): ?>
                                    <img src="<?php echo $item['cover_image']; ?>"
                                        alt="<?php echo htmlspecialchars($item['title']); ?>">
                                <?php else: ?>
                                    <img src="img/mida_logo_5.png" alt="No Image"
                                        style="object-fit: contain; padding: 20px; background: #f0f0f0;">
                                <?php endif; ?>
                            </div>
                            <div class="news-content">
                                <div class="news-meta">
                                    <span class="news-category">
                                        <?php echo $item['category'] == 'News' ? 'ข่าวประชาสัมพันธ์' : ($item['category'] == 'Activity' ? 'กิจกรรม' : $item['category']); ?>
                                    </span>
                                    <span><i class="fa-regular fa-calendar" style="margin-right: 5px;"></i>
                                        <?php echo date('d/m/Y', strtotime($item['start_date'])); ?>
                                    </span>
                                </div>
                                <h3 class="news-title">
                                    <?php echo htmlspecialchars($item['title']); ?>
                                </h3>
                                <div class="news-excerpt">
                                    <?php
                                    $plain_text = strip_tags($item['content']);
                                    echo mb_substr($plain_text, 0, 150, 'UTF-8') . '...';
                                    ?>
                                </div>
                                <div class="read-more">
                                    อ่านเพิ่มเติม <i class="fa-solid fa-arrow-right"></i>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="grid-column: 1/-1; text-align: center; padding: 50px; color: #888;">
                        <i class="fa-regular fa-newspaper" style="font-size: 3rem; margin-bottom: 15px;"></i>
                        <p>ยังไม่มีข่าวสารในขณะนี้</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?>&category=<?php echo urlencode($category); ?>"
                            class="page-link"><i class="fa-solid fa-chevron-left"></i></a>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?page=<?php echo $i; ?>&category=<?php echo urlencode($category); ?>"
                            class="page-link <?php echo $page == $i ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>

                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?php echo $page + 1; ?>&category=<?php echo urlencode($category); ?>"
                            class="page-link"><i class="fa-solid fa-chevron-right"></i></a>
                    <?php endif; ?>
                </div>
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