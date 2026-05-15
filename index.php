<?php
require_once 'admin/config/db.php';
$database = new Database();
$db = $database->getConnection();

function logPageDataError($section, PDOException $e)
{
    error_log(sprintf('Homepage data load failed [%s]: %s', $section, $e->getMessage()));
}

// Track visitor
@include_once 'track_visitor.php';

// Fetch Banners
$banners = array();
try {
    $stmt = $db->query("SELECT * FROM banners WHERE is_active = 1 ORDER BY sort_order ASC");
    $banners = $stmt->fetchAll();
} catch (PDOException $e) {
    logPageDataError('banners', $e);
}

// Fetch Services
$services = array();
try {
    $stmt = $db->query("SELECT * FROM services WHERE is_active = 1 ORDER BY sort_order ASC");
    $services = $stmt->fetchAll();
} catch (PDOException $e) {
    logPageDataError('services', $e);
}

// Fetch Settings
$settings = array();
try {
    $stmt = $db->query("SELECT * FROM settings WHERE id = 1");
    $settings = $stmt->fetch();
} catch (PDOException $e) {
    logPageDataError('settings', $e);
}

// Fetch Latest News
$news_items = array();
try {
    $stmt = $db->query("SELECT * FROM announcements WHERE is_active = 1 AND is_popup = 0 AND (start_date IS NULL OR start_date <= CURDATE()) AND (end_date IS NULL OR end_date >= CURDATE()) ORDER BY start_date DESC, created_at DESC LIMIT 3");
    $news_items = $stmt->fetchAll();
} catch (PDOException $e) {
    logPageDataError('news_items', $e);
}

// Fetch Popup
$popup_news = null;
try {
    $stmt = $db->query("SELECT * FROM announcements WHERE is_active = 1 AND is_popup = 1 AND (start_date IS NULL OR start_date <= CURDATE()) AND (end_date IS NULL OR end_date >= CURDATE()) ORDER BY created_at DESC LIMIT 1");
    $popup_news = $stmt->fetch();
} catch (PDOException $e) {
    logPageDataError('popup_news', $e);
}

$popup_news_id = $popup_news && isset($popup_news['id']) ? (int) $popup_news['id'] : 0;
$popup_cover_image = !empty($popup_news['cover_image']) ? htmlspecialchars($popup_news['cover_image'], ENT_QUOTES, 'UTF-8') : '';
$popup_title = $popup_news && isset($popup_news['title']) ? htmlspecialchars($popup_news['title'], ENT_QUOTES, 'UTF-8') : '';

?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MIDA LEASING - รถอะไร..ก็ขอกู้เงินได้</title>
    <meta name="description"
        content="ไมด้า ลิสซิ่ง บริการสินเชื่อรถยนต์มือสอง สินเชื่อจำนำทะเบียนรถ และสินเชื่อส่วนบุคคล สมัครออนไลน์ได้ง่าย มีเจ้าหน้าที่ดูแล และมีสาขาให้บริการ">
    <meta name="keywords"
        content="สินเชื่อรถยนต์, จำนำทะเบียนรถ, ไมด้า ลิสซิ่ง, กู้เงินด่วน">

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

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://www.midaleasing.com/">
    <meta property="og:title" content="MIDA LEASING - รถอะไร..ก็ขอกู้เงินได้">
    <meta property="og:description"
        content="บริษัท ไมด้าลิสซิ่ง จำกัด (มหาชน) ให้บริการสินเชื่อรถยนต์มือสอง สินเชื่อจำนำทะเบียนรถยนต์ และสินเชื่อส่วนบุคคล อนุมัติไว ได้เงินจริง">
    <meta property="og:image" content="https://www.midaleasing.com/img/mida_logo_5.png">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="https://www.midaleasing.com/">
    <meta property="twitter:title" content="MIDA LEASING - รถอะไร..ก็ขอกู้เงินได้">
    <meta property="twitter:description"
        content="บริษัท ไมด้าลิสซิ่ง จำกัด (มหาชน) ให้บริการสินเชื่อรถยนต์มือสอง สินเชื่อจำนำทะเบียนรถยนต์ และสินเชื่อส่วนบุคคล อนุมัติไว ได้เงินจริง">
    <meta property="twitter:image" content="https://www.midaleasing.com/img/mida_logo_5.png">

    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/homepage.css">

    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- Structured Data (JSON-LD) -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "FinancialService",
      "name": "Mida Leasing Public Company Limited",
      "alternateName": "MIDA LEASING",
      "url": "https://www.midaleasing.com/",
      "logo": "https://www.midaleasing.com/img/mida_logo_5.png",
      "contactPoint": {
        "@type": "ContactPoint",
        "telephone": "02-574-6901",
        "contactType": "customer service",
        "areaServed": "TH",
        "availableLanguage": "Thai"
      },
      "address": {
        "@type": "PostalAddress",
        "streetAddress": "48/1-5 ซอยแจ้งวัฒนะ 14 ถนนแจ้งวัฒนะ แขวงทุ่งสองห้อง เขตหลักสี่",
        "addressLocality": "Bangkok",
        "postalCode": "10210",
        "addressCountry": "TH"
      },
      "sameAs": [
        "https://www.facebook.com/midaleasing.th",
        "https://line.me/R/ti/p/@midaleasing"
      ]
    }
    </script>
</head>

<body>

    <!-- Header -->
    <?php $active_page = 'home';
    include 'includes/nav.php'; ?>

    <!-- Hero Section -->
    <section class="hero section-compact-top" id="home">
        <div class="hero-bg-shape"></div>
        <div class="container hero-content">
            <!-- Left: Text -->
            <div class="hero-text">
                <p class="hero-kicker">MIDA LEASING</p>
                <h1 class="hero-title">สินเชื่อรถ ใช้เงินไว<br>ให้ไมด้าช่วยดูแล</h1>
                <h2 class="hero-subtitle">
                    <span class="hero-subtitle-text">เลือกสินเชื่อ สมัครออนไลน์ หรือค้นหาสาขาใกล้บ้าน</span>
                </h2>
                <div class="hero-actions">
                    <a href="register_hire_purchase.php" class="btn btn-accent hero-primary-cta">สมัครสินเชื่อออนไลน์</a>
                    <a href="contact_branches.php" class="btn btn-primary btn-secondary-light">ค้นหาสาขา</a>
                </div>

                <div class="hero-trust-row" aria-label="จุดเด่นบริการไมด้า ลิสซิ่ง">
                    <div class="hero-trust-item">
                        <i class="fa-solid fa-user-check" aria-hidden="true"></i>
                        <span>เจ้าหน้าที่ดูแล</span>
                    </div>
                    <div class="hero-trust-item">
                        <i class="fa-solid fa-lock" aria-hidden="true"></i>
                        <span>ข้อมูลปลอดภัย</span>
                    </div>
                    <div class="hero-trust-item">
                        <i class="fa-solid fa-location-dot" aria-hidden="true"></i>
                        <span>มีสาขาให้บริการ</span>
                    </div>
                </div>

            </div>

            <!-- Right: Loan Selector (Tidlor Style) -->
            <div class="loan-selector-card" id="loanSelector">
                <h3 class="selector-title">เลือกบริการ</h3>

                <div class="selector-grid">
                    <!-- Item 1: Sedan -->
                    <a href="service_hire_purchase.php#sedan" class="selector-item">
                        <i class="fa-solid fa-car-side"></i>
                        <span>รถเก๋ง</span>
                    </a>

                    <!-- Item 2: Pickup -->
                    <a href="service_hire_purchase.php#pickup" class="selector-item">
                        <i class="fa-solid fa-truck-pickup"></i>
                        <span>รถกระบะ</span>
                    </a>

                    <!-- Item 3: Truck -->
                    <a href="service_hire_purchase.php#truck" class="selector-item">
                        <i class="fa-solid fa-truck"></i>
                        <span>รถบรรทุก</span>
                    </a>

                    <!-- Item 4: Nano -->
                    <a href="service_title_loan.php" class="selector-item">
                        <i class="fa-solid fa-passport"></i>
                        <span>จำนำทะเบียนรถ</span>
                    </a>

                    <!-- Item 5: Personal -->
                    <a href="service_personal_loan.php" class="selector-item">
                        <i class="fa-solid fa-user-tag"></i>
                        <span>สินเชื่อบุคคล</span>
                    </a>

                    <!-- Item 6: Other/Contact -->
                    <a href="contact_us.php" class="selector-item">
                        <i class="fa-solid fa-headset"></i>
                        <span>สอบถามเพิ่มเติม</span>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <nav class="mobile-quick-cta" aria-label="ทางลัดสำหรับมือถือ">
        <a href="register_hire_purchase.php" class="mobile-quick-link mobile-quick-link-primary">
            <i class="fa-solid fa-file-signature" aria-hidden="true"></i>
            <span>สมัคร</span>
        </a>
        <a href="<?php echo htmlspecialchars($settings['site_line']); ?>" target="_blank" class="mobile-quick-link">
            <i class="fa-brands fa-line" aria-hidden="true"></i>
            <span>LINE</span>
        </a>
        <a href="contact_branches.php" class="mobile-quick-link">
            <i class="fa-solid fa-location-dot" aria-hidden="true"></i>
            <span>สาขา</span>
        </a>
    </nav>

    <!-- Campaign Strip -->
    <section class="banner-section campaign-strip" aria-label="โปรโมชันและข่าวสาร">
        <div class="container">
            <div class="campaign-strip-header">
                <span>โปรโมชันและข่าวสาร</span>
            </div>
            <div class="slider-container campaign-slider">
                <div class="slider-wrapper">
                    <?php if (count($banners) > 0): ?>
                        <?php foreach ($banners as $index => $banner): ?>
                            <div class="slide">
                                <a href="<?php echo htmlspecialchars($banner['link']); ?>">
                                    <picture>
                                        <?php
                                        $webp_path = preg_replace('/\.(jpg|jpeg|png)$/i', '.webp', $banner['image_path']);
                                        $original_path = $banner['image_path'];
                                        ?>
                                        <source
                                            srcset="<?php echo htmlspecialchars($webp_path); ?>"
                                            type="image/webp">
                                        <source
                                            srcset="<?php echo htmlspecialchars($original_path); ?>"
                                            type="image/<?php echo pathinfo($original_path, PATHINFO_EXTENSION) === 'jpg' ? 'jpeg' : pathinfo($original_path, PATHINFO_EXTENSION); ?>">
                                        <img
                                            src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 1200 375'%3E%3C/svg%3E"
                                            data-src="<?php echo htmlspecialchars($original_path); ?>"
                                            alt="<?php echo htmlspecialchars($banner['title']); ?>"
                                            loading="<?php echo $index === 0 ? 'eager' : 'lazy'; ?>"
                                            decoding="async"
                                            width="1200"
                                            height="375"
                                            class="slider-image">
                                    </picture>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="slide">
                            <picture>
                                <source
                                    srcset="img/hire_purchase.webp"
                                    type="image/webp">
                                <source
                                    srcset="img/hire_purchase.jpg"
                                    type="image/jpeg">
                                <img
                                    src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 1200 375'%3E%3C/svg%3E"
                                    data-src="img/hire_purchase.jpg"
                                    alt="Default Banner"
                                    loading="eager"
                                    decoding="async"
                                    width="1200"
                                    height="375"
                                    class="slider-image">
                            </picture>
                        </div>
                    <?php endif; ?>
                </div>

                <button class="slider-btn prev-btn" type="button" aria-label="Previous campaign slide"><i class="fa-solid fa-chevron-left" aria-hidden="true"></i></button>
                <button class="slider-btn next-btn" type="button" aria-label="Next campaign slide"><i class="fa-solid fa-chevron-right" aria-hidden="true"></i></button>
                <div class="slider-dots" role="tablist" aria-label="Campaign slides"></div>
            </div>
        </div>
    </section>

    <!-- Services / Products -->
    <section class="section" id="products">
        <div class="container">
            <div class="section-title">
                <h2>บริการสินเชื่อของเรา</h2>
                <p>ตอบโจทย์ทุกความต้องการทางการเงินของคุณ</p>
            </div>

            <div class="features-grid">
                <?php if (count($services) > 0): ?>
                    <?php foreach ($services as $service): ?>
                        <?php
                        $service_link = isset($service['link']) ? $service['link'] : '';
                        $service_copy = array(
                            'service_hire_purchase.php' => array(
                                'title' => 'สินเชื่อเช่าซื้อ',
                                'description' => 'ผ่อนรถมือสอง รถเก๋ง กระบะ และรถบรรทุก พร้อมเจ้าหน้าที่ดูแล',
                                'cta' => 'ดูรายละเอียด'
                            ),
                            'service_title_loan.php' => array(
                                'title' => 'สินเชื่อจำนำทะเบียน',
                                'description' => 'ใช้เล่มทะเบียนต่อยอดสภาพคล่อง รถยังมีขับตามปกติ',
                                'cta' => 'ดูเงื่อนไข'
                            ),
                            'service_personal_loan.php' => array(
                                'title' => 'สินเชื่อส่วนบุคคล',
                                'description' => 'เงินก้อนพร้อมใช้ ช่วยเสริมสภาพคล่องในชีวิตประจำวัน',
                                'cta' => 'ดูบริการ'
                            ),
                            'service_insurance.php' => array(
                                'title' => 'ต่อภาษีและประกันภัย',
                                'description' => 'บริการต่อภาษี พ.ร.บ. และประกันรถยนต์ ครบในที่เดียว',
                                'cta' => 'ดูบริการ'
                            ),
                        );
                        $service_display = isset($service_copy[$service_link]) ? $service_copy[$service_link] : array(
                            'title' => $service['title'],
                            'description' => $service['description'],
                            'cta' => 'อ่านเพิ่มเติม'
                        );
                        ?>
                        <div class="feature-card">
                            <div class="feature-icon">
                                <i class="<?php echo htmlspecialchars($service['icon_class']); ?>"></i>
                            </div>
                            <h3><?php echo htmlspecialchars($service_display['title']); ?></h3>
                            <p class="feature-description">
                                <?php echo nl2br(htmlspecialchars($service_display['description'])); ?>
                            </p>
                            <a href="<?php echo htmlspecialchars($service['link']); ?>"
                                class="feature-link"><?php echo htmlspecialchars($service_display['cta']); ?> <i
                                    class="fa-solid fa-arrow-right"></i></a>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Properties & Auction Section -->
    <section class="section" id="properties">
        <div class="container">
            <div class="section-title">
                <h2>ทรัพย์และรถราคาพิเศษจากไมด้า</h2>
                <p>รวมรายการประมูลและทรัพย์พร้อมขาย</p>
            </div>

            <div class="properties-grid">
                <!-- Auction Card -->
                <div class="property-card auction-card">
                    <div class="property-info">
                        <div class="property-heading">
                            <div class="property-icon">
                                <i class="fa-solid fa-gavel"></i>
                            </div>
                            <h3>ประมูลรถยนต์</h3>
                        </div>
                        <p>รถมือสองสภาพดีจากศูนย์ประมูลมาตรฐาน ราคาพิเศษ เปิดเผย โปร่งใส</p>
                        <ul class="property-points">
                            <li>รถมือสองสภาพดี</li>
                            <li>ราคาเริ่มต้นพิเศษ</li>
                            <li>ประมูลโปร่งใส</li>
                        </ul>
                        <div class="property-actions">
                            <a href="auction.php" class="btn btn-outline-white property-primary-link">ดูรอบประมูล</a>
                            <a href="contact_us.php" class="property-secondary-link">สอบถามรายละเอียด</a>
                        </div>
                    </div>
                </div>

                <!-- NPA Card -->
                <div class="property-card npa-card">
                    <div class="property-info">
                        <div class="property-heading">
                            <div class="property-icon">
                                <i class="fa-solid fa-house-chimney"></i>
                            </div>
                            <h3>ทรัพย์ราคาพิเศษ</h3>
                        </div>
                        <p>รวมบ้าน คอนโด และที่ดิน พร้อมรายละเอียดให้เลือกชม</p>
                        <ul class="property-points">
                            <li>บ้านและคอนโด</li>
                            <li>ที่ดินราคาพิเศษ</li>
                            <li>มีรายละเอียดให้เลือกชม</li>
                        </ul>
                        <div class="property-actions">
                            <a href="properties.php" class="btn btn-outline-white property-primary-link">ดูรายการทรัพย์</a>
                            <a href="contact_us.php" class="property-secondary-link">ติดต่อเจ้าหน้าที่</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Why Choose Us / Stat Section -->
    <section class="section section-soft">
        <div class="container">
            <div class="section-title">
                <h2>ทำไมต้องเลือกไมด้า ลิสซิ่ง?</h2>
                <p>มั่นใจได้ในมาตรฐาน บริษัทจดทะเบียนในตลาดหลักทรัพย์</p>
            </div>

            <div class="stats-grid">
                <!-- Stat 1 -->
                <div class="stat-item">
                    <div class="stat-icon">
                        <i class="fa-solid fa-building-columns"></i>
                    </div>
                    <div class="stat-text">
                        <h3>บริษัทมหาชน</h3>
                        <p>จดทะเบียนในตลาดหลักทรัพย์<br>มีความมั่นคงสูง</p>
                    </div>
                </div>

                <!-- Stat 2 -->
                <div class="stat-item">
                    <div class="stat-icon">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                    </div>
                    <div class="stat-text">
                        <h3>ประสบการณ์ 20+ ปี</h3>
                        <p>เชี่ยวชาญด้านสินเชื่อรถยนต์<br>ให้บริการมายาวนาน</p>
                    </div>
                </div>

                <!-- Stat 3 -->
                <div class="stat-item">
                    <div class="stat-icon">
                        <i class="fa-solid fa-map-location-dot"></i>
                    </div>
                    <div class="stat-text">
                        <h3>สาขาทั่วประเทศ</h3>
                        <p>มีสาขาให้บริการครอบคลุม<br>พร้อมดูแลคุณใกล้บ้าน</p>
                    </div>
                </div>

                <!-- Stat 4 -->
                <div class="stat-item">
                    <div class="stat-icon">
                        <i class="fa-solid fa-hand-holding-dollar"></i>
                    </div>
                    <div class="stat-text">
                        <h3>ดอกเบี้ยยุติธรรม</h3>
                        <p>อัตราดอกเบี้ยมาตรฐาน<br>ถูกต้องตามกฎหมาย</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="section testimonial-section">
            <div class="container">
                <div class="section-title">
                    <h2>เสียงจากลูกค้าของเรา</h2>
                    <p>ความประทับใจจริง จากลูกค้าที่ไว้วางใจไมด้า ลิสซิ่ง</p>
                </div>

                <div class="testimonials-grid">
                    <!-- Review 1 -->
                    <div class="testimonial-card">
                        <div class="quote-icon"><i class="fa-solid fa-quote-left"></i></div>
                        <p class="review-text">"ตอนแรกกังวลมากเพราะต้องการเงินด่วนแต่รถยังผ่อนไม่หมด
                            มาปรึกษาที่นี่เจ้าหน้าที่แนะนำดีมาก อนุมัติไว ได้เงินมาหมุนทันเวลาพอดีครับ"</p>
                        <div class="reviewer-info">
                            <div class="reviewer-avatar reviewer-avatar-blue">
                                <i class="fa-solid fa-user"></i>
                            </div>
                            <div class="reviewer-meta">
                                <h4>คุณสมชาย</h4>
                                <span class="reviewer-role">เจ้าของธุรกิจส่วนตัว</span>
                            </div>
                            <div class="stars">
                                <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i
                                    class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i
                                    class="fa-solid fa-star"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Review 2 -->
                    <div class="testimonial-card">
                        <div class="quote-icon"><i class="fa-solid fa-quote-left"></i></div>
                        <p class="review-text">"ประทับใจความรวดเร็วค่ะ ยื่นเอกสารตอนเช้า ตอนบ่ายรู้ผลเลย
                            ไม่ยุ่งยากอย่างที่คิด ดอกเบี้ยก็โอเครับได้ แนะนำเลยค่ะ"</p>
                        <div class="reviewer-info">
                            <div class="reviewer-avatar reviewer-avatar-gold">
                                <i class="fa-solid fa-user-tie"></i>
                            </div>
                            <div class="reviewer-meta">
                                <h4>คุณนิตยา</h4>
                                <span class="reviewer-role">พนักงานบริษัท</span>
                            </div>
                            <div class="stars">
                                <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i
                                    class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i
                                    class="fa-solid fa-star"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Review 3 -->
                    <div class="testimonial-card">
                        <div class="quote-icon"><i class="fa-solid fa-quote-left"></i></div>
                        <p class="review-text">"รถกระบะทำเงินครับ เอามาเข้าที่นี่ได้วงเงินสูงกว่าที่อื่น
                            เอาเงินไปต่อทุนค้าขายได้สบายเลย ขอบคุณไมด้าลิสซิ่งมากครับ"</p>
                        <div class="reviewer-info">
                            <div class="reviewer-avatar reviewer-avatar-green">
                                <i class="fa-solid fa-user-tag"></i>
                            </div>
                            <div class="reviewer-meta">
                                <h4>คุณประเสริฐ</h4>
                                <span class="reviewer-role">อาชีพเกษตรกร</span>
                            </div>
                            <div class="stars">
                                <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i
                                    class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i
                                    class="fa-solid fa-star"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Steps Section (New) -->
        <section class="section section-plain" id="steps">
            <div class="container">
                <div class="section-title">
                    <h2>3 ขั้นตอนง่ายๆ ขอสินเชื่อ</h2>
                    <p>เริ่มจากเลือกบริการ ฝากข้อมูล แล้วให้เจ้าหน้าที่ดูแลต่อ</p>
                </div>

                <div class="steps-grid">
                    <!-- Step 1 -->
                    <div class="step-card">
                        <div
                            class="step-number">
                            1</div>
                        <h4 class="step-title">เลือกบริการและกรอกข้อมูล</h4>
                        <p class="step-description">เลือกสินเชื่อที่สนใจ แล้วฝากข้อมูลติดต่อกลับ</p>
                    </div>

                    <!-- Step 2 -->
                    <div class="step-card">
                        <div
                            class="step-number">
                            2</div>
                        <h4 class="step-title">เจ้าหน้าที่ติดต่อกลับ</h4>
                        <p class="step-description">รับคำแนะนำบริการ เอกสาร และขั้นตอนที่เหมาะกับคุณ</p>
                    </div>

                    <!-- Step 3 -->
                    <div class="step-card">
                        <div
                            class="step-number">
                            3</div>
                        <h4 class="step-title">ยื่นเอกสารและรอผล</h4>
                        <p class="step-description">ดำเนินการตามขั้นตอน พร้อมรับบริการเมื่ออนุมัติเรียบร้อย</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Pre-Footer CTA -->
        <section class="cta-banner-modern">
            <!-- Background with gradient and car image -->
            <div class="cta-modern-bg"></div>

            <div class="container cta-container">
                <div class="cta-modern-content">
                    <!-- Main Headline -->
                    <h2 class="cta-modern-headline">
                        พร้อมเริ่มสมัครสินเชื่อกับไมด้า?
                    </h2>

                    <!-- Compact Trust Row -->
                    <div class="cta-features-row cta-trust-row-compact" aria-label="จุดเด่นก่อนสมัคร">
                        <span><i class="fa-solid fa-shield"></i> บริการถูกต้อง</span>
                        <span><i class="fa-solid fa-check-circle"></i> เงื่อนไขชัดเจน</span>
                        <span><i class="fa-solid fa-lock"></i> ข้อมูลปลอดภัย</span>
                    </div>

                    <!-- Action Buttons (CTA) -->
                    <div class="cta-modern-buttons">
                        <a href="<?php echo htmlspecialchars($settings['site_line']); ?>" target="_blank"
                            class="cta-btn cta-btn-line">
                            <i class="fa-brands fa-line"></i>
                            <span>คุยกับเจ้าหน้าที่ทาง LINE</span>
                        </a>
                        <a href="register_hire_purchase.php" class="cta-btn cta-btn-form">
                            <i class="fa-solid fa-file-signature"></i>
                            <span>ฝากข้อมูลสมัครสินเชื่อ</span>
                        </a>
                    </div>

                    <!-- Trust & Sub-text -->
                    <div class="cta-trust-text">
                        ข้อมูลเป็นความลับ | มีสาขาให้บริการ | เจ้าหน้าที่ดูแลทุกขั้นตอน
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
                        <p class="footer-company">บริษัท ไมด้าลิสซิ่ง จำกัด (มหาชน)</p>
                        <p class="footer-address">
                            <?php echo nl2br(htmlspecialchars($settings['site_address'])); ?>
                        </p>
                        <p class="footer-phone"><i class="fa-solid fa-phone"></i><?php echo htmlspecialchars($settings['site_phone']); ?>
                        </p>
                        <div class="footer-socials">
                            <a href="<?php echo htmlspecialchars($settings['site_facebook']); ?>" target="_blank" class="footer-social-link footer-social-link-facebook" aria-label="Facebook MIDA Leasing">
                                <i class="fa-brands fa-facebook footer-social-icon footer-social-icon-facebook"></i>
                            </a>
                            <a href="<?php echo htmlspecialchars($settings['site_line']); ?>" target="_blank" class="footer-social-link footer-social-link-line" aria-label="LINE MIDA Leasing">
                                <i class="fa-brands fa-line footer-social-icon footer-social-icon-line"></i>
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
                            <li><a href="news.php">ข่าวสารและกิจกรรม</a></li>
                            <li><a href="contact_branches.php">แผนที่สาขา</a></li>
                            <li><a href="contact_career.php">ร่วมงานกับเรา</a></li>
                            <li><a href="contact_us.php">ติดต่อสอบถาม</a></li>
                        </ul>
                    </div>
                </div>

                <div class="copyright">
                    &copy; 2026 Mida Leasing Public Company Limited. All Rights Reserved.
                    <br>
                    <div class="footer-policies">
                        <a href="privacy_policy.php"
                            class="footer-policy-link">นโยบายความเป็นส่วนตัว</a> |
                        <a href="cookie_policy.php"
                            class="footer-policy-link">นโยบายเกี่ยวกับ
                            cookie</a>
                    </div>
                </div>
            </div>
        </footer>

        <!-- JS -->
        <script src="assets/js/main.js"></script>
        <script src="assets/js/slider.js"></script>

        <!-- Lazy Loading Script for Images -->
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Lazy loading for images with data-src
            if ('IntersectionObserver' in window) {
                const imageObserver = new IntersectionObserver(function(entries, observer) {
                    entries.forEach(function(entry) {
                        if (entry.isIntersecting) {
                            const img = entry.target;
                            const src = img.getAttribute('data-src');
                            
                            if (src) {
                                img.src = src;
                                img.removeAttribute('data-src');
                                img.classList.add('loaded');
                                observer.unobserve(img);
                            }
                        }
                    });
                }, {
                    rootMargin: '50px 0px',
                    threshold: 0.01
                });

                // Observe all images with data-src
                const lazyImages = document.querySelectorAll('img[data-src]');
                lazyImages.forEach(function(img) {
                    imageObserver.observe(img);
                });
            } else {
                // Fallback for browsers that don't support IntersectionObserver
                const lazyImages = document.querySelectorAll('img[data-src]');
                lazyImages.forEach(function(img) {
                    const src = img.getAttribute('data-src');
                    if (src) {
                        img.src = src;
                        img.removeAttribute('data-src');
                        img.classList.add('loaded');
                    }
                });
            }

            // Preload critical images
            const criticalImages = document.querySelectorAll('img[loading="eager"]');
            criticalImages.forEach(function(img) {
                if (img.src && img.src.includes('data:image/svg+xml')) {
                    const dataSrc = img.getAttribute('data-src');
                    if (dataSrc) {
                        img.src = dataSrc;
                        img.removeAttribute('data-src');
                        img.classList.add('loaded');
                    }
                }
            });
        });
        </script>

        <!-- Floating Contact Button -->
        <div class="floating-contact" id="floatingContact">
            <div class="floating-contact-options" id="floatingContactOptions">
                <a href="<?php echo htmlspecialchars($settings['site_line']); ?>" target="_blank" class="floating-contact-option line">
                    <i class="fa-brands fa-line"></i>
                    <span>คุยกับเจ้าหน้าที่ทาง LINE</span>
                </a>
                <a href="tel:<?php echo htmlspecialchars($settings['site_phone']); ?>" class="floating-contact-option phone">
                    <i class="fa-solid fa-phone"></i>
                    <span>โทร: <?php echo htmlspecialchars($settings['site_phone']); ?></span>
                </a>
            </div>
            <button type="button" class="floating-contact-main" aria-label="Toggle contact options" aria-expanded="false" aria-controls="floatingContactOptions" onclick="toggleFloatingContact()">
                <i class="fa-solid fa-headset" aria-hidden="true"></i>
            </button>
        </div>

        <script>
            function toggleFloatingContact(forceState) {
                const contact = document.getElementById('floatingContact');
                const trigger = contact.querySelector('.floating-contact-main');
                const nextState = typeof forceState === 'boolean' ? forceState : !contact.classList.contains('active');
                contact.classList.toggle('active', nextState);
                trigger.setAttribute('aria-expanded', nextState ? 'true' : 'false');
            }

            // Close floating contact when clicking outside
            document.addEventListener('click', function(event) {
                const contact = document.getElementById('floatingContact');
                const isClickInside = contact.contains(event.target);
                
                if (!isClickInside && contact.classList.contains('active')) {
                    toggleFloatingContact(false);
                }
            });

            document.addEventListener('keydown', function(event) {
                const contact = document.getElementById('floatingContact');
                if (event.key === 'Escape' && contact.classList.contains('active')) {
                    toggleFloatingContact(false);
                    contact.querySelector('.floating-contact-main').focus();
                }
            });
        </script>

        <!-- Popup Modal -->
        <?php if ($popup_news): ?>
            <div id="newsPopup" class="popup-modal" role="dialog" aria-modal="true" aria-labelledby="newsPopupTitle" aria-describedby="newsPopupDescription">
                <div id="newsPopupPanel" class="popup-panel" tabindex="-1">
                    <button type="button" class="popup-close-btn" onclick="closePopup()" aria-label="Close popup">
                        <i class="fa-solid fa-times" aria-hidden="true"></i>
                    </button>

                    <a href="news_detail.php?id=<?php echo $popup_news_id; ?>" class="popup-link">
                        <?php if (!empty($popup_news['cover_image'])): ?>
                            <img src="<?php echo $popup_cover_image; ?>" alt="<?php echo $popup_title; ?>" class="popup-image">
                        <?php endif; ?>
                        <div class="popup-body">
                            <h3 id="newsPopupTitle" class="popup-title">
                                <?php echo $popup_title; ?>
                            </h3>
                            <?php
                            $plain_content = strip_tags($popup_news['content']);
                            $trimmed_content = trim($plain_content);
                            if (!empty($trimmed_content)):
                                ?>
                                <p id="newsPopupDescription" class="popup-description">
                                    <?php echo htmlspecialchars(mb_substr($plain_content, 0, 100, 'UTF-8') . '...', ENT_QUOTES, 'UTF-8'); ?>
                                </p>
                            <?php else: ?>
                                <p id="newsPopupDescription" class="popup-description popup-description-hidden">Popup announcement</p>
                            <?php endif; ?>
                        </div>
                    </a>

                    <div class="popup-footer">
                        <label class="popup-checkbox-label">
                            <input type="checkbox" id="dontShowPopup" class="popup-checkbox">
                            ไม่ต้องแสดงอีกในวันนี้
                        </label>
                    </div>
                </div>
            </div>
            <script>
                let lastFocusedElement = null;

                document.addEventListener('DOMContentLoaded', function () {
                    const popup = document.getElementById('newsPopup');
                    const popupPanel = document.getElementById('newsPopupPanel');
                    const popupCloseButton = popup ? popup.querySelector('button') : null;
                    const popupId = '<?php echo $popup_news_id; ?>';
                    const popupSessionKey = 'seenPopup_' + popupId;
                    const hasAnchorTarget = window.location.hash && window.location.hash !== '#home';
                    const popupDelayMs = 4500;
                    let popupTimer = null;

                    function openPopup() {
                        if (!popup) {
                            return;
                        }
                        if (window.location.hash && window.location.hash !== '#home') {
                            return;
                        }
                        sessionStorage.setItem(popupSessionKey, '1');
                        lastFocusedElement = document.activeElement;
                        popup.style.display = 'flex';
                        if (popupPanel) {
                            popupPanel.focus();
                        } else if (popupCloseButton) {
                            popupCloseButton.focus();
                        }
                    }

                    // Check local storage for long-term suppression
                    const hideTime = localStorage.getItem('hidePopup_' + popupId);

                    let shouldShow = true;
                    if (hideTime) {
                        const now = new Date().getTime();
                        // 24 hours = 86400000 ms
                        if (now - parseInt(hideTime) < 86400000) {
                            shouldShow = false;
                        } else {
                            // Expired, remove it
                            localStorage.removeItem('hidePopup_' + popupId);
                        }
                    }

                    if (sessionStorage.getItem(popupSessionKey) === '1' || hasAnchorTarget) {
                        shouldShow = false;
                    }

                    if (shouldShow) {
                        popupTimer = window.setTimeout(openPopup, popupDelayMs);
                    }

                    if (popup) {
                        popup.addEventListener('click', function (event) {
                            if (event.target === popup) {
                                closePopup();
                            }
                        });
                    }

                    document.addEventListener('keydown', function (event) {
                        if (event.key === 'Escape' && popup && popup.style.display === 'flex') {
                            closePopup();
                        }
                    });

                    window.cancelNewsPopupTimer = function () {
                        if (popupTimer) {
                            window.clearTimeout(popupTimer);
                            popupTimer = null;
                        }
                    };

                    window.addEventListener('hashchange', window.cancelNewsPopupTimer);
                });

                function closePopup() {
                    if (typeof window.cancelNewsPopupTimer === 'function') {
                        window.cancelNewsPopupTimer();
                    }
                    const popup = document.getElementById('newsPopup');
                    if (!popup) {
                        return;
                    }
                    popup.style.display = 'none';
                    const dontShowCheckbox = document.getElementById('dontShowPopup');
                    const dontShow = dontShowCheckbox ? dontShowCheckbox.checked : false;
                    if (dontShow) {
                        const popupId = '<?php echo $popup_news_id; ?>';
                        const now = new Date().getTime();
                        localStorage.setItem('hidePopup_' + popupId, now.toString());
                    }
                    if (typeof lastFocusedElement !== 'undefined' && lastFocusedElement && typeof lastFocusedElement.focus === 'function') {
                        lastFocusedElement.focus();
                    }
                }
            </script>
            <style>
                @keyframes popupFadeIn {
                    from {
                        opacity: 0;
                        transform: scale(0.9);
                    }

                    to {
                        opacity: 1;
                        transform: scale(1);
                    }
                }
            </style>
        <?php endif; ?>

        <!-- Cookie Consent Banner -->
        <div class="cookie-consent-banner" id="cookieConsentBanner">
            <div class="cookie-content">
                <p class="cookie-text">
                    เว็บไซต์นี้ใช้คุกกี้ (Cookies) เพื่อพัฒนาประสบการณ์การใช้งานของคุณ
                    อ่านเพิ่มเติมได้ที่ <a href="cookie_policy.php">นโยบายคุกกี้</a>
                    และ <a href="privacy_policy.php">นโยบายความเป็นส่วนตัว</a>
                </p>
                <button class="btn btn-accent cookie-btn" id="acceptCookie" type="button">ยอมรับ</button>
            </div>
        </div>
        
</body>

</html>

