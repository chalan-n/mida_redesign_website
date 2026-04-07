<?php
require_once 'admin/config/db.php';
$database = new Database();
$db = $database->getConnection();

// Track visitor
@include_once 'track_visitor.php';

// Fetch Banners
$banners = [];
try {
    $stmt = $db->query("SELECT * FROM banners WHERE is_active = 1 ORDER BY sort_order ASC");
    $banners = $stmt->fetchAll();
} catch (PDOException $e) {
    // Handle error or ignore
}

// Fetch Services
$services = [];
try {
    $stmt = $db->query("SELECT * FROM services WHERE is_active = 1 ORDER BY sort_order ASC");
    $services = $stmt->fetchAll();
} catch (PDOException $e) {
}

// Fetch Settings
$settings = [];
try {
    $stmt = $db->query("SELECT * FROM settings WHERE id = 1");
    $settings = $stmt->fetch();
} catch (PDOException $e) {
}

// Fetch Latest News
$news_items = [];
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

?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MIDA LEASING - รถอะไร..ก็ขอกู้เงินได้</title>
    <meta name="description"
        content="บริษัท ไมด้าลิสซิ่ง จำกัด (มหาชน) ให้บริการสินเชื่อรถยนต์มือสอง สินเชื่อจำนำทะเบียนรถยนต์ และสินเชื่อส่วนบุคคล อนุมัติไว ได้เงินจริง">

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
    <link rel="stylesheet" href="assets/css/index2.css">

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

<body class="index2-page">

    <!-- Header -->
    <?php $active_page = 'home';
    include 'includes/nav.php'; ?>


    <!-- Hero Section - Tidlor Style -->
    <section class="hero-tidlor" id="home" style="margin-top: var(--header-height);">
        <div class="container">
            <div class="hero-tidlor-content">
                <!-- Left Side: Text Content -->
                <div class="hero-tidlor-text">
                    <div class="hero-highlight">
                        💰 อนุมัติไว ได้เงินจริง
                    </div>
                    <h1>รถอะไร...<br>ก็ขอกู้เงินได้</h1>
                    <p class="hero-subtitle">
                        บริการสินเชื่อเช่าซื้อรถยนต์ สินเชื่อจำนำทะเบียนรถยนต์ และสินเชื่อส่วนบุคคล
                        ให้วงเงินสูง ไม่ยุ่งยาก
                    </p>

                    <!-- CTA Buttons -->
                    <div class="hero-tidlor-buttons">
                        <a href="register_hire_purchase.php" class="btn-tidlor-primary">
                            <i class="fa-solid fa-file-signature"></i>
                            <span>สมัครสินเชื่อออนไลน์</span>
                        </a>
                        <a href="<?php echo htmlspecialchars($settings['site_line']); ?>" target="_blank"
                            class="btn-tidlor-secondary">
                            <i class="fa-brands fa-line"></i>
                            <span>ปรึกษาฟรี</span>
                        </a>
                    </div>

                    <!-- Trust Indicators -->
                    <div class="hero-trust-indicators">
                        <div class="trust-item">
                            <i class="fa-solid fa-shield-check"></i>
                            <span>ถูกกฎหมาย 100%</span>
                        </div>
                        <div class="trust-item">
                            <i class="fa-solid fa-clock"></i>
                            <span>อนุมัติใน 1 ชม.</span>
                        </div>
                        <div class="trust-item">
                            <i class="fa-solid fa-hand-holding-dollar"></i>
                            <span>ดอกเบี้ยเป็นธรรม</span>
                        </div>
                    </div>
                </div>

                <!-- Right Side: Image Placeholder -->
                <div class="hero-tidlor-image">
                    <div class="hero-image-placeholder">
                        <span>รูปภาพ Lifestyle / บุคคล</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Loan Selector - Simplified Tidlor Style -->
        <div class="container">
            <div class="loan-selector-tidlor">
                <h3 class="selector-title-tidlor">เลือกสินเชื่อที่เหมาะกับคุณ</h3>
                <div class="selector-grid-tidlor">
                    <a href="service_hire_purchase.php#sedan" class="selector-item-tidlor">
                        <i class="fa-solid fa-car-side"></i>
                        <span>รถเก๋ง</span>
                    </a>
                    <a href="service_hire_purchase.php#pickup" class="selector-item-tidlor">
                        <i class="fa-solid fa-truck-pickup"></i>
                        <span>รถกระบะ</span>
                    </a>
                    <a href="service_hire_purchase.php#truck" class="selector-item-tidlor">
                        <i class="fa-solid fa-truck"></i>
                        <span>รถบรรทุก</span>
                    </a>
                    <a href="service_title_loan.php" class="selector-item-tidlor">
                        <i class="fa-solid fa-passport"></i>
                        <span>จำนำทะเบียนรถ</span>
                    </a>
                    <a href="service_personal_loan.php" class="selector-item-tidlor">
                        <i class="fa-solid fa-user-tag"></i>
                        <span>สินเชื่อบุคคล</span>
                    </a>
                    <a href="contact_us.php" class="selector-item-tidlor">
                        <i class="fa-solid fa-headset"></i>
                        <span>สอบถามเพิ่มเติม</span>
                    </a>
                </div>
            </div>
        </div>
    </section>


    <!-- Services / Products - Tidlor Style -->
    <section class="services-tidlor" id="products">
        <div class="container">
            <div class="section-title">
                <h2>บริการสินเชื่อของเรา</h2>
                <p>ตอบโจทย์ทุกความต้องการทางการเงินของคุณ</p>
            </div>

            <div class="services-tidlor-grid">
                <?php if (count($services) > 0): ?>
                    <?php foreach ($services as $service): ?>
                        <div class="service-card-tidlor">
                            <div class="service-icon-tidlor">
                                <i class="<?php echo htmlspecialchars($service['icon_class']); ?>"></i>
                            </div>
                            <h3><?php echo htmlspecialchars($service['title']); ?></h3>
                            <p><?php echo nl2br(htmlspecialchars($service['description'])); ?></p>
                            <a href="<?php echo htmlspecialchars($service['link']); ?>" class="service-link-tidlor">
                                อ่านเพิ่มเติม <i class="fa-solid fa-arrow-right"></i>
                            </a>
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
                <h2>บ้าน-ที่ดินราคาพิเศษ</h2>
                <p>โอกาสเป็นเจ้าของรถยนต์ บ้าน คอนโด และที่ดินในราคาสุดคุ้ม</p>
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
                        <h3>บ้าน คอนโด และที่ดิน</h3>
                        <p>ทรัพย์สินพร้อมขาย บ้านเดี่ยว อาคารพาณิชย์ คอนโด และที่ดินเปล่า ทำเลศักยภาพทั่วประเทศ
                            ราคาพิเศษสำหรับคุณ</p>
                        <a href="properties.php" class="btn btn-outline-white">ดูรายการทรัพย์</a>
                    </div>
                </div>
            </div>
        </div>


        <!-- Why Choose Us / Features - Tidlor Style -->
        <section class="features-tidlor">
            <div class="container">
                <div class="section-title">
                    <h2>ทำไมต้องเลือกไมด้า ลิสซิ่ง?</h2>
                    <p>มั่นใจได้ในมาตรฐาน บริษัทจดทะเบียนในตลาดหลักทรัพย์</p>
                </div>

                <div class="features-tidlor-grid">
                    <!-- Feature 1 -->
                    <div class="feature-card-tidlor">
                        <div class="feature-icon-tidlor">
                            <i class="fa-solid fa-building-columns"></i>
                        </div>
                        <h4>บริษัทมหาชน</h4>
                        <p>จดทะเบียนในตลาดหลักทรัพย์ มีความมั่นคงสูง</p>
                    </div>

                    <!-- Feature 2 -->
                    <div class="feature-card-tidlor">
                        <div class="feature-icon-tidlor">
                            <i class="fa-solid fa-clock-rotate-left"></i>
                        </div>
                        <h4>ประสบการณ์ 20+ ปี</h4>
                        <p>เชี่ยวชาญด้านสินเชื่อรถยนต์ ให้บริการมายาวนาน</p>
                    </div>

                    <!-- Feature 3 -->
                    <div class="feature-card-tidlor">
                        <div class="feature-icon-tidlor">
                            <i class="fa-solid fa-map-location-dot"></i>
                        </div>
                        <h4>สาขาทั่วประเทศ</h4>
                        <p>มีสาขาให้บริการครอบคลุม พร้อมดูแลคุณใกล้บ้าน</p>
                    </div>

                    <!-- Feature 4 -->
                    <div class="feature-card-tidlor">
                        <div class="feature-icon-tidlor">
                            <i class="fa-solid fa-hand-holding-dollar"></i>
                        </div>
                        <h4>ดอกเบี้ยยุติธรรม</h4>
                        <p>อัตราดอกเบี้ยมาตรฐาน ถูกต้องตามกฎหมาย</p>
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
                            <span>เช็กวงเงินประเมินฟรี</span>
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

                    <a href="news_detail.php?id=<?php echo $popup_news['id']; ?>"
                        style="text-decoration: none; display: block;">
                        <?php if (!empty($popup_news['cover_image'])): ?>
                            <img src="<?php echo $popup_news['cover_image']; ?>" style="width: 100%; display: block;">
                        <?php endif; ?>
                        <div style="padding: 20px; padding-bottom: 5px; text-align: center;">
                            <h3 style="margin: 0 0 10px 0; color: #333; font-size: 1.5rem;">
                                <?php echo htmlspecialchars($popup_news['title']); ?>
                            </h3>
                            <?php
                            $plain_content = strip_tags($popup_news['content']);
                            if (!empty(trim($plain_content))):
                                ?>
                                <p style="color: #666; margin: 0; font-size: 1rem;">
                                    <?php echo mb_substr($plain_content, 0, 100, 'UTF-8') . '...'; ?>
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
                    const popupId = '<?php echo $popup_news['id']; ?>';
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
                        const popupId = '<?php echo $popup_news['id']; ?>';
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
</body>

</html>