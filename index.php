<?php
require_once 'admin/config/db.php';
$database = new Database();
$db = $database->getConnection();

// Track visitor
@include_once 'track_visitor.php';

// Fetch Banners
$banners = array();
try {
    $stmt = $db->query("SELECT * FROM banners WHERE is_active = 1 ORDER BY sort_order ASC");
    $banners = $stmt->fetchAll();
} catch (PDOException $e) {
    // Handle error or ignore
}

// Fetch Services
$services = array();
try {
    $stmt = $db->query("SELECT * FROM services WHERE is_active = 1 ORDER BY sort_order ASC");
    $services = $stmt->fetchAll();
} catch (PDOException $e) {
}

// Fetch Settings
$settings = array();
try {
    $stmt = $db->query("SELECT * FROM settings WHERE id = 1");
    $settings = $stmt->fetch();
} catch (PDOException $e) {
}

// Fetch Latest News
$news_items = array();
try {
    $stmt = $db->query("SELECT * FROM announcements WHERE is_active = 1 AND is_popup = 0 AND (start_date IS NULL OR start_date <= CURDATE()) AND (end_date IS NULL OR end_date >= CURDATE()) ORDER BY start_date DESC, created_at DESC LIMIT 3");
    $news_items = $stmt->fetchAll();
} catch (PDOException $e) {
}

// Fetch Popup
$popup_news = null;
try {
    $stmt = $db->query("SELECT * FROM announcements WHERE is_active = 1 AND is_popup = 1 AND (start_date IS NULL OR start_date <= CURDATE()) AND (end_date IS NULL OR end_date >= CURDATE()) ORDER BY created_at DESC LIMIT 1");
    $popup_news = $stmt->fetch();
} catch (PDOException $e) {
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
        content="ไมด้า ลิสซิ่ง บริการสินเชื่อรถยนต์มือสอง สินเชื่อจำนำทะเบียนรถ และสินเชื่อส่วนบุคคล อนุมัติไว ให้วงเงินสูง ดอกเบี้ยเป็นธรรม รถอะไร..ก็ขอกู้เงินได้ เช็ควงเงินฟรี!">
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

    <!-- Banner Slider Section -->
    <section class="banner-section" style="margin-top: var(--header-height);">
        <div class="container">
            <div class="slider-container">
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

                <button class="slider-btn prev-btn"><i class="fa-solid fa-chevron-left"></i></button>
                <button class="slider-btn next-btn"><i class="fa-solid fa-chevron-right"></i></button>
                <div class="slider-dots"></div>
            </div>
        </div>
    </section>

    <!-- Hero Section -->
    <section class="hero" id="home" style="padding-top: 40px; padding-bottom: 20px; min-height: auto;">
        <div class="hero-bg-shape"></div>
        <div class="container hero-content">
            <!-- Left: Text -->
            <div class="hero-text">
                <h1 style="line-height: 1.1; margin-bottom: 20px;">รถอะไร..<br>ก็ขอกู้เงินได้</h1>
                <h2 style="font-size: 1.5rem; color: #555; font-weight: 400; margin-bottom: 30px;">
                    <span style="font-size: 1rem; color: #777;">บริการสินเชื่อเช่าซื้อรถยนต์
                        สินเชื่อจำนำทะเบียนรถยนต์
                        สินเชื่อส่วนบุคคล</span>
                </h2>
                <div style="display: flex; gap: 15px;">
                    <a href="#products" class="btn btn-primary">ดูบริการของเรา</a>
                    <a href="#steps" class="btn btn-primary"
                        style="background: white; color: var(--primary-blue);">ขั้นตอนการกู้</a>
                </div>

                <div style="margin-top: 40px; border-left: 4px solid var(--accent-gold); padding-left: 20px;">
                    <p style="margin-bottom: 0; font-style: italic; color: #555;">"อนุมัติไว ให้วงเงินสูง ไม่ยุ่งยาก"
                    </p>
                </div>
            </div>

            <!-- Right: Loan Selector (Tidlor Style) -->
            <div class="loan-selector-card" id="loanSelector">
                <h3 class="selector-title">เลือกสินเชื่อที่เหมาะกับคุณ</h3>

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
                        <span>จำนำทะเบียนรถยนต์</span>
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

                <a href="register_hire_purchase.php" class="btn btn-primary selector-apply-btn">สนใจสมัครสินเชื่อ</a>
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
                        <div class="feature-card">
                            <div class="feature-icon">
                                <i class="<?php echo htmlspecialchars($service['icon_class']); ?>"></i>
                            </div>
                            <h3><?php echo htmlspecialchars($service['title']); ?></h3>
                            <p style="margin: 15px 0; color: #666;">
                                <?php echo nl2br(htmlspecialchars($service['description'])); ?>
                            </p>
                            <a href="<?php echo htmlspecialchars($service['link']); ?>"
                                style="color: var(--primary-blue); font-weight: 600; text-decoration: none;">อ่านเพิ่มเติม <i
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
                <h2>รถยนต์ บ้าน คอนโด และที่ดินราคาพิเศษ</h2>
            </div>

            <div class="properties-grid">
                <!-- Auction Card -->
                <div class="property-card auction-card">
                    <div class="property-icon">
                        <i class="fa-solid fa-gavel"></i>
                    </div>
                    <div class="property-info">
                        <h3>ประมูลรถยนต์</h3>
                        <p>ศูนย์ประมูลรถยนต์มาตรฐาน รถมือสองสภาพดี หลากหลายรุ่น ยี่ห้อ ราคาเริ่มต้นต่ำกว่าท้องตลาด
                            ประมูลอย่างเปิดเผยและโปร่งใส</p>
                        <a href="auction.php" class="btn btn-outline-white">ดูรอบประมูล</a>
                    </div>
                </div>

                <!-- NPA Card -->
                <div class="property-card npa-card">
                    <div class="property-icon">
                        <i class="fa-solid fa-house-chimney"></i>
                    </div>
                    <div class="property-info">
                        <h3>รถยนต์ บ้าน คอนโด ที่ดินราคาพิเศษ</h3>
                        <a href="properties.php" class="btn btn-outline-white">ดูรายการทรัพย์</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Why Choose Us / Stat Section -->
        <section class="section" style="background-color: #f0f4f8; padding: 60px 0;">
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
        <section class="section" style="background-color: white; padding-top: 40px;">
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
                            <div class="reviewer-avatar" style="background-color: #e3f2fd; color: var(--primary-blue);">
                                <i class="fa-solid fa-user"></i>
                            </div>
                            <div>
                                <h4 style="margin: 0; font-size: 1rem;">คุณสมชาย</h4>
                                <span style="font-size: 0.85rem; color: #888;">เจ้าของธุรกิจส่วนตัว</span>
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
                            <div class="reviewer-avatar" style="background-color: #fff3e0; color: var(--accent-gold);">
                                <i class="fa-solid fa-user-tie"></i>
                            </div>
                            <div>
                                <h4 style="margin: 0; font-size: 1rem;">คุณนิตยา</h4>
                                <span style="font-size: 0.85rem; color: #888;">พนักงานบริษัท</span>
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
                            <div class="reviewer-avatar" style="background-color: #e8f5e9; color: #2e7d32;">
                                <i class="fa-solid fa-user-tag"></i>
                            </div>
                            <div>
                                <h4 style="margin: 0; font-size: 1rem;">คุณประเสริฐ</h4>
                                <span style="font-size: 0.85rem; color: #888;">อาชีพเกษตรกร</span>
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
        <section class="section" id="steps" style="background-color: var(--bg-white);">
            <div class="container">
                <div class="section-title">
                    <h2>4 ขั้นตอนง่ายๆ ขอสินเชื่อ</h2>
                    <p>สะดวกรวดเร็ว ไม่ยุ่งยาก</p>
                </div>

                <div
                    style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 30px; text-align: center;">
                    <!-- Step 1 -->
                    <div style="position: relative;">
                        <div
                            style="width: 60px; height: 60px; background: var(--primary-blue); color: white; border-radius: 50%; font-size: 1.5rem; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                            1</div>
                        <h4 style="margin-bottom: 15px;">สมัครง่ายๆได้ทุกที่</h4>
                        <p style="font-size: 0.95rem; color: #666;">กรอกฟอร์มออนไลน์ รอเจ้าหน้าที่ติดต่อกลับ</p>
                    </div>

                    <!-- Step 2 -->
                    <div style="position: relative;">
                        <div
                            style="width: 60px; height: 60px; background: var(--primary-blue); color: white; border-radius: 50%; font-size: 1.5rem; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                            2</div>
                        <h4 style="margin-bottom: 15px;">ส่งรูปถ่ายเอกสาร</h4>
                        <p style="font-size: 0.95rem; color: #666;">ส่งเอกสารประกอบการขอสินเชื่อ</p>
                    </div>

                    <!-- Step 3 -->
                    <div style="position: relative;">
                        <div
                            style="width: 60px; height: 60px; background: var(--primary-blue); color: white; border-radius: 50%; font-size: 1.5rem; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                            3</div>
                        <h4 style="margin-bottom: 15px;">รอแจ้งผลอนุมัติ</h4>
                        <p style="font-size: 0.95rem; color: #666;">แจ้งผลหลังจากได้รับเอกสารครบถ้วน</p>
                    </div>

                    <!-- Step 4 -->
                    <div style="position: relative;">
                        <div
                            style="width: 60px; height: 60px; background: var(--accent-gold); color: white; border-radius: 50%; font-size: 1.5rem; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                            4</div>
                        <h4 style="margin-bottom: 15px;">รอรับเงิน</h4>
                        <p style="font-size: 0.95rem; color: #666;">รับเงินโอนเมื่อดำเนินการเรียบร้อย</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Pre-Footer CTA -->
        <section class="cta-banner-modern">
            <!-- Background with gradient and car image -->
            <div class="cta-modern-bg"></div>

            <div class="container" style="position: relative; z-index: 2;">
                <div class="cta-modern-content">
                    <!-- Main Headline -->
                    <h2 class="cta-modern-headline">
                        มีรถ มีเงินใช้! อนุมัติไวใน 1 ชม.
                    </h2>

                    <!-- Features & Icons (3 columns) -->
                    <div class="cta-features-row">
                        <div class="cta-feature-item">
                            <div class="cta-feature-icon">
                                <i class="fa-solid fa-shield"></i>
                            </div>
                            <div class="cta-feature-text">
                                <strong>ถูกกฎหมาย</strong>
                                <span>มั่นใจ ตรวจสอบได้ทุกขั้นตอน</span>
                            </div>
                        </div>

                        <div class="cta-feature-item">
                            <div class="cta-feature-icon">
                                <i class="fa-solid fa-chart-line"></i>
                            </div>
                            <div class="cta-feature-text">
                                <strong>ดอกเบี้ยเป็นธรรม</strong>
                                <span>เริ่มต้นต่ำตามกฎหมาย</span>
                            </div>
                        </div>

                        <div class="cta-feature-item">
                            <div class="cta-feature-icon">
                                <i class="fa-solid fa-lock"></i>
                            </div>
                            <div class="cta-feature-text">
                                <strong>ปลอดภัย 100%</strong>
                                <span>ข้อมูลเป็นความลับสูงสุด</span>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons (CTA) -->
                    <div class="cta-modern-buttons">
                        <a href="<?php echo htmlspecialchars($settings['site_line']); ?>" target="_blank"
                            class="cta-btn cta-btn-line">
                            <i class="fa-brands fa-line"></i>
                            <span>ปรึกษาเรื่องยอดจัดฟรี</span>
                        </a>
                        <a href="register_hire_purchase.php" class="cta-btn cta-btn-form">
                            <i class="fa-solid fa-file-signature"></i>
                            <span>สมัครสินเชื่อออนไลน์</span>
                        </a>
                    </div>

                    <!-- Trust & Sub-text -->
                    <div class="cta-trust-text">
                        ไม่ต้องมีคนค้ำ | ประเมินฟรีไม่มีข้อผูกมัด | รับเงินทันทีหลังอนุมัติ
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
                        <p style="color: #ccc; margin-bottom: 10px; font-size: 1rem;">
                            <?php echo nl2br(htmlspecialchars($settings['site_address'])); ?>
                        </p>
                        <p style="color: #ccc; margin-bottom: 20px; font-size: 1rem;"><i class="fa-solid fa-phone"
                                style="margin-right: 10px;"></i><?php echo htmlspecialchars($settings['site_phone']); ?>
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
            <div class="floating-contact-options">
                <a href="<?php echo htmlspecialchars($settings['site_line']); ?>" target="_blank" class="floating-contact-option line">
                    <i class="fa-brands fa-line"></i>
                    <span>คุยกับเจ้าหน้าที่ทาง LINE</span>
                </a>
                <a href="tel:<?php echo htmlspecialchars($settings['site_phone']); ?>" class="floating-contact-option phone">
                    <i class="fa-solid fa-phone"></i>
                    <span>โทร: <?php echo htmlspecialchars($settings['site_phone']); ?></span>
                </a>
            </div>
            <div class="floating-contact-main" onclick="toggleFloatingContact()">
                <i class="fa-solid fa-headset"></i>
            </div>
        </div>

        <script>
            function toggleFloatingContact() {
                const contact = document.getElementById('floatingContact');
                contact.classList.toggle('active');
            }

            // Close floating contact when clicking outside
            document.addEventListener('click', function(event) {
                const contact = document.getElementById('floatingContact');
                const isClickInside = contact.contains(event.target);
                
                if (!isClickInside && contact.classList.contains('active')) {
                    contact.classList.remove('active');
                }
            });
        </script>

        <!-- Popup Modal -->
        <?php if ($popup_news): ?>
            <div id="newsPopup"
                style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 9999; display: none; align-items: center; justify-content: center;">
                <div
                    style="background: white; width: 90%; max-width: 600px; border-radius: 8px; overflow: hidden; position: relative; animation: popupFadeIn 0.3s ease-out;">
                    <button onclick="closePopup()"
                        style="position: absolute; top: 10px; right: 10px; background: rgba(0,0,0,0.5); color: white; border: none; width: 30px; height: 30px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; z-index: 10;">
                        <i class="fa-solid fa-times"></i>
                    </button>

                    <a href="news_detail.php?id=<?php echo $popup_news_id; ?>"
                        style="text-decoration: none; display: block;">
                        <?php if (!empty($popup_news['cover_image'])): ?>
                            <img src="<?php echo $popup_cover_image; ?>" alt="<?php echo $popup_title; ?>" style="width: 100%; display: block;">
                        <?php endif; ?>
                        <div style="padding: 20px; padding-bottom: 5px; text-align: center;">
                            <h3 style="margin: 0 0 10px 0; color: #333; font-size: 1.5rem;">
                                <?php echo $popup_title; ?>
                            </h3>
                            <?php
                            $plain_content = strip_tags($popup_news['content']);
                            $trimmed_content = trim($plain_content);
                            if (!empty($trimmed_content)):
                                ?>
                                <p style="color: #666; margin: 0; font-size: 1rem;">
                                    <?php echo htmlspecialchars(mb_substr($plain_content, 0, 100, 'UTF-8') . '...', ENT_QUOTES, 'UTF-8'); ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </a>

                    <div style="padding: 10px 20px 20px 20px; text-align: center; border-top: 1px solid #eee;">
                        <label
                            style="color: #666; font-size: 0.9em; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px;">
                            <input type="checkbox" id="dontShowPopup" style="width: 16px; height: 16px;">
                            ไม่ต้องแสดงอีกในวันนี้
                        </label>
                    </div>
                </div>
            </div>
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    // Check local storage for long-term suppression
                    const popupId = '<?php echo $popup_news_id; ?>';
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

                    if (shouldShow) {
                        document.getElementById('newsPopup').style.display = 'flex';
                    }
                });

                function closePopup() {
                    document.getElementById('newsPopup').style.display = 'none';
                    const dontShow = document.getElementById('dontShowPopup').checked;
                    if (dontShow) {
                        const popupId = '<?php echo $popup_news_id; ?>';
                        const now = new Date().getTime();
                        localStorage.setItem('hidePopup_' + popupId, now.toString());
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
                <button class="btn btn-accent cookie-btn" onclick="acceptCookies()">ยอมรับ</button>
            </div>
        </div>
        <script>
            (function() {
                if (!localStorage.getItem('cookieAccepted')) {
                    setTimeout(function() {
                        document.getElementById('cookieConsentBanner').classList.add('show');
                    }, 1000);
                }
            })();
            function acceptCookies() {
                localStorage.setItem('cookieAccepted', '1');
                document.getElementById('cookieConsentBanner').classList.remove('show');
            }
        </script>
</body>

</html>
