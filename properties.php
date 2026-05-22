<?php
require_once 'admin/config/db.php';
$database = new Database();
$db = $database->getConnection();

// Track visitor
@include_once 'track_visitor.php';

// Fetch Settings
$settings = array();
try {
    $stmt_settings = $db->query("SELECT * FROM settings WHERE id = 1");
    $settings = $stmt_settings->fetch();
} catch (PDOException $e) {
}

// Filter Logic
$where_clauses = array("is_active = 1");
$params = array();

// 1. Filter by Type
$selected_types = isset($_GET['type']) ? $_GET['type'] : array();
if (!empty($selected_types)) {
    $placeholders = array();
    foreach ($selected_types as $idx => $type) {
        $key = ":type_$idx";
        $placeholders[] = $key;
        $params[$key] = $type;
    }
    $where_clauses[] = "type IN (" . implode(', ', $placeholders) . ")";
}

// 2. Filter by Location
$selected_location = isset($_GET['location']) ? $_GET['location'] : '';
if (!empty($selected_location)) {
    $where_clauses[] = "location LIKE :location";
    $params[':location'] = "%$selected_location%";
}

// 3. Filter by Price Range (Basic Implementation assuming numeric or simple string)
// Note: This is tricky with VARCHAR price. We'll skip complex range for now or prepare for it.

$where_sql = implode(' AND ', $where_clauses);

// Pagination
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$limit = 9;
$start = ($page - 1) * $limit;

// Count Total
$count_sql = "SELECT COUNT(*) FROM properties WHERE $where_sql";
$stmt_count = $db->prepare($count_sql);
foreach ($params as $key => $val) {
    $stmt_count->bindValue($key, $val);
}
$stmt_count->execute();
$total_rows = $stmt_count->fetchColumn();
$total_pages = ceil($total_rows / $limit);

// Fetch Properties
$sql = "SELECT * FROM properties WHERE $where_sql ORDER BY id DESC LIMIT :start, :limit";
$stmt = $db->prepare($sql);
foreach ($params as $key => $val) {
    $stmt->bindValue($key, $val);
}
$stmt->bindValue(':start', $start, PDO::PARAM_INT);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->execute();
$properties = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch Filter Data (Distinct Types & Locations)
// Types with Count
$types_sql = "SELECT type, COUNT(*) as count FROM properties WHERE is_active = 1 GROUP BY type";
$stmt_types = $db->query($types_sql);
$prop_types = $stmt_types->fetchAll(PDO::FETCH_ASSOC);

// Locations (Distinct)
$loc_sql = "SELECT DISTINCT location FROM properties WHERE is_active = 1 ORDER BY location";
$stmt_loc = $db->query($loc_sql);
$locations = $stmt_loc->fetchAll(PDO::FETCH_COLUMN);

?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>บ้าน คอนโด ที่ดิน ราคาพิเศษ - MIDA LEASING</title>
    <meta name="description"
        content="รวมประกาศขายบ้าน คอนโด ที่ดิน ราคาพิเศษ ทำเลสวย จากไมด้าลิสซิ่ง">

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
    <meta property="og:url" content="https://www.midaleasing.com/properties.php">
    <meta property="og:title" content="บ้าน คอนโด ที่ดิน - MIDA LEASING">
    <meta property="og:description" content="บ้าน คอนโด ที่ดิน ราคาพิเศษ ทำเลสวย จากไมด้าลิสซิ่ง">
    <meta property="og:image" content="https://www.midaleasing.com/img/mida_logo_5.png">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="https://www.midaleasing.com/properties.php">
    <meta property="twitter:title" content="บ้าน คอนโด ที่ดิน - MIDA LEASING">
    <meta property="twitter:description" content="บ้าน คอนโด ที่ดิน ราคาพิเศษ ทำเลสวย จากไมด้าลิสซิ่ง">
    <meta property="twitter:image" content="https://www.midaleasing.com/img/mida_logo_5.png">

    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/style.css">

    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        .properties-hero {
            position: relative;
            overflow: hidden;
            background:
                radial-gradient(circle at 18% 12%, rgba(255, 199, 44, 0.28), transparent 28%),
                linear-gradient(108deg, #fffaf0 0%, #eef5ff 58%, #1f5fb8 58%, #17488f 100%);
            color: white;
            padding: 132px 0 70px;
        }

        .properties-hero::after {
            content: "";
            position: absolute;
            inset: auto -8% -42% 44%;
            height: 58%;
            background: rgba(255, 255, 255, 0.12);
            transform: skewX(-12deg);
            pointer-events: none;
        }

        .properties-hero-grid {
            position: relative;
            z-index: 1;
            display: grid;
            grid-template-columns: minmax(0, 0.92fr) minmax(360px, 0.78fr);
            gap: 42px;
            align-items: center;
        }

        .properties-hero-copy {
            max-width: 570px;
        }

        .properties-kicker {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 18px;
            color: var(--primary-blue);
            font-size: 0.88rem;
            font-weight: 900;
            letter-spacing: 0.14em;
            text-transform: uppercase;
        }

        .properties-kicker::before {
            content: "";
            width: 34px;
            height: 3px;
            border-radius: 999px;
            background: var(--accent-gold);
        }

        .properties-hero h1 {
            max-width: 560px;
            margin: 0 0 18px;
            color: var(--primary-blue);
            font-size: clamp(2.25rem, 5vw, 4.35rem);
            line-height: 1.02;
            letter-spacing: -0.05em;
        }

        .properties-hero p {
            max-width: 520px;
            margin: 0;
            color: #56677d;
            font-size: 1.08rem;
            line-height: 1.8;
        }

        .properties-hero-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 13px;
            margin-top: 28px;
        }

        .properties-hero-actions a,
        .property-guide-note {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: fit-content;
            min-height: 44px;
            padding: 0 18px;
            border-radius: 999px;
            background: var(--primary-blue);
            color: #fff;
            font-weight: 800;
            text-decoration: none;
            transition: transform 0.18s ease, box-shadow 0.18s ease;
        }

        .properties-hero-actions a:hover {
            transform: translateY(-2px);
        }

        .properties-hero-actions .is-gold {
            background: linear-gradient(135deg, var(--accent-gold) 0%, #ffe07a 100%);
            color: #0f2d5c;
            box-shadow: 0 12px 26px rgba(255, 199, 44, 0.24);
        }

        .property-guide-note {
            width: fit-content;
            min-height: 34px;
            margin-top: auto;
            padding: 0 12px;
            background: #f8fbff;
            color: var(--primary-blue);
            font-size: 0.86rem;
            box-shadow: inset 0 0 0 1px rgba(23, 69, 143, 0.08);
        }

        .properties-hero-panel {
            position: relative;
            z-index: 1;
            padding: 30px;
            border-radius: 30px;
            background: rgba(255, 255, 255, 0.92);
            border: 1px solid rgba(255, 255, 255, 0.45);
            box-shadow: 0 28px 70px rgba(16, 48, 95, 0.24);
            backdrop-filter: blur(14px);
        }

        .properties-hero-panel h2 {
            margin: 0 0 18px;
            color: var(--primary-blue);
            font-size: 1.45rem;
        }

        .property-type-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
        }

        .property-type-item {
            padding: 18px 14px;
            border: 1px solid #dfe8f3;
            border-radius: 20px;
            background: #fff;
            color: #183153;
            text-align: center;
            font-weight: 800;
        }

        .property-type-item i {
            display: block;
            margin-bottom: 9px;
            color: var(--primary-blue);
            font-size: 1.55rem;
        }

        .property-guide-section {
            padding: 34px 0 0;
            background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
        }

        .property-guide-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
        }

        .property-guide-card {
            display: flex;
            flex-direction: column;
            gap: 12px;
            min-height: 190px;
            padding: 22px;
            border-radius: 24px;
            background: #ffffff;
            border: 1px solid rgba(23, 69, 143, 0.1);
            box-shadow: 0 16px 36px rgba(18, 72, 148, 0.08);
        }

        .property-guide-card i {
            width: 46px;
            height: 46px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 16px;
            background: #fff4cf;
            color: #b98600;
            font-size: 1.25rem;
        }

        .property-guide-card h3 {
            margin: 0;
            color: #0f2d5c;
            font-size: 1.08rem;
        }

        .property-guide-card p {
            margin: 0;
            color: #617187;
            line-height: 1.65;
            font-size: 0.95rem;
        }

        .layout-grid {
            display: grid;
            grid-template-columns: 280px 1fr;
            gap: 30px;
            margin-top: 40px;
        }

        .filter-sidebar {
            background: white;
            padding: 25px;
            border-radius: 24px;
            box-shadow: 0 18px 42px rgba(23, 69, 143, 0.08);
            border: 1px solid rgba(23, 69, 143, 0.09);
            height: fit-content;
            position: sticky;
            top: 96px;
        }

        .filter-group {
            margin-bottom: 25px;
            border-bottom: 1px solid #eee;
            padding-bottom: 15px;
        }

        .filter-group:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }

        .filter-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 15px;
            display: block;
        }

        .filter-checkbox {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
            cursor: pointer;
            color: #666;
        }

        .filter-checkbox input {
            margin-right: 10px;
            width: 16px;
            height: 16px;
        }

        .prop-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
        }

        .prop-card {
            background: white;
            border-radius: 22px;
            overflow: hidden;
            box-shadow: 0 16px 36px rgba(23, 69, 143, 0.08);
            border: 1px solid rgba(23, 69, 143, 0.09);
            transition: all 0.3s;
            display: flex;
            flex-direction: column;
        }

        .prop-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 22px 44px rgba(23, 69, 143, 0.14);
        }

        .prop-img {
            height: 220px;
            background-color: #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            color: #94a3b8;
            font-size: 3rem;
            overflow: hidden;
        }

        .prop-type-badge {
            position: absolute;
            top: 15px;
            left: 15px;
            background: linear-gradient(135deg, var(--accent-gold) 0%, #ffe07a 100%);
            color: #0f2d5c;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            backdrop-filter: blur(4px);
        }

        .prop-content {
            padding: 20px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .prop-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 5px;
            color: #333;
            line-height: 1.4;
        }

        .prop-location {
            color: #666;
            font-size: 0.95rem;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .prop-facility {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px dashed #eee;
            color: #666;
            font-size: 0.9rem;
        }

        .prop-facility div {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .prop-price {
            color: var(--primary-blue);
            font-size: 1.4rem;
            font-weight: 700;
        }

        @media (max-width: 992px) {
            .properties-hero {
                padding: 108px 0 46px;
                background:
                    radial-gradient(circle at 18% 12%, rgba(255, 199, 44, 0.28), transparent 34%),
                    linear-gradient(160deg, #fffaf0 0%, #eef5ff 66%, #e5f0ff 100%);
            }

            .properties-hero-grid,
            .property-guide-grid {
                grid-template-columns: 1fr;
            }

            .property-type-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }

            .layout-grid {
                grid-template-columns: 1fr;
            }

            .filter-sidebar {
                position: static;
            }
        }

        @media (max-width: 640px) {
            .properties-hero h1 {
                font-size: 2.55rem;
            }

            .property-type-grid {
                grid-template-columns: 1fr;
            }

            .properties-hero-panel {
                padding: 22px;
            }
        }
    </style>
</head>

<body>

    <!-- Header -->
    <?php $active_page = 'properties'; include 'includes/nav.php'; ?>

    <!-- Page Header -->
    <section class="properties-hero">
        <div class="container">
            <div class="properties-hero-grid">
                <div class="properties-hero-copy">
                    <span class="properties-kicker">MIDA PROPERTY</span>
                    <h1>บ้าน คอนโด ที่ดิน ราคาพิเศษจากไมด้า</h1>
                    <p>รวมทรัพย์พร้อมขายจากบริษัท เลือกดูทำเล รายละเอียด ราคา และช่องทางสอบถามเจ้าหน้าที่ได้ในที่เดียว</p>
                    <div class="properties-hero-actions">
                        <a href="#property-listing" class="is-gold"><i class="fa-solid fa-magnifying-glass"></i> ดูรายการทรัพย์</a>
                    </div>
                </div>

                <aside class="properties-hero-panel">
                    <h2>เลือกทรัพย์ที่สนใจ</h2>
                    <div class="property-type-grid">
                        <div class="property-type-item">
                            <i class="fa-solid fa-house-chimney"></i>
                            บ้าน
                        </div>
                        <div class="property-type-item">
                            <i class="fa-solid fa-building"></i>
                            คอนโด
                        </div>
                        <div class="property-type-item">
                            <i class="fa-solid fa-map-location-dot"></i>
                            ที่ดิน
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </section>

    <section class="property-guide-section" id="property-guide">
        <div class="container">
            <div class="property-guide-grid">
                <div class="property-guide-card">
                    <i class="fa-solid fa-location-dot"></i>
                    <h3>เลือกทำเลที่ต้องการ</h3>
                    <p>ค้นหาทรัพย์ตามประเภทและพื้นที่ เพื่อดูรายการที่ตรงกับความสนใจของคุณ</p>
                    <span class="property-guide-note">ใช้ตัวกรองด้านล่าง</span>
                </div>
                <div class="property-guide-card">
                    <i class="fa-solid fa-file-lines"></i>
                    <h3>ดูรายละเอียดก่อนตัดสินใจ</h3>
                    <p>ดูรูป ราคา ขนาดพื้นที่ และข้อมูลทรัพย์ เพื่อเปรียบเทียบก่อนสอบถามเพิ่มเติม</p>
                    <span class="property-guide-note">กดดูในรายการทรัพย์</span>
                </div>
                <div class="property-guide-card">
                    <i class="fa-solid fa-headset"></i>
                    <h3>ให้เจ้าหน้าที่ติดต่อกลับ</h3>
                    <p>สนใจทรัพย์รายการไหน ฝากข้อมูลไว้ได้ เจ้าหน้าที่จะติดต่อกลับเพื่อให้รายละเอียด</p>
                    <span class="property-guide-note">สอบถามจากหน้ารายละเอียดทรัพย์</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <div class="section" id="property-listing" style="padding-top: 0; background-color: #f8f9fa; min-height: 80vh;">
        <div class="container">

            <div style="padding: 20px 0;">
                <a href="index.php"
                    style="text-decoration: none; color: #666; display: inline-flex; align-items: center;">
                    <i class="fa-solid fa-arrow-left" style="margin-right: 8px;"></i> กลับหน้าหลัก
                </a>
            </div>

            <div class="layout-grid">

                <!-- Sidebar Filter -->
                <aside class="filter-sidebar">
                    <form action="properties.php" method="GET">
                        <h3 style="margin-bottom: 20px; font-size: 1.2rem; color: #0f2d5c;"><i class="fa-solid fa-filter"></i> ค้นหาทรัพย์ที่สนใจ
                        </h3>

                        <div class="filter-group">
                            <label class="filter-title">ประเภททรัพย์</label>
                            <?php foreach ($prop_types as $pt): ?>
                                <label class="filter-checkbox">
                                    <input type="checkbox" name="type[]" value="<?php echo htmlspecialchars($pt['type']); ?>"
                                        <?php echo (in_array($pt['type'], $selected_types)) ? 'checked' : ''; ?>>
                                    <?php echo htmlspecialchars($pt['type']); ?> (<?php echo $pt['count']; ?>)
                                </label>
                            <?php endforeach; ?>
                        </div>

                        <!-- Price Range (Static for now, hard to filter text) -->
                        <!-- 
                        <div class="filter-group">
                            <label class="filter-title">ช่วงราคา</label>
                            <label class="filter-checkbox"><input type="checkbox"> ไม่เกิน 1 ล้านบาท</label>
                            ...
                        </div>
                        -->

                        <div class="filter-group">
                            <label class="filter-title">ทำเล / พื้นที่</label>
                            <select name="location" style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #ddd;">
                                <option value="">ทุกทำเล</option>
                                <?php foreach ($locations as $loc): ?>
                                    <option value="<?php echo htmlspecialchars($loc); ?>" 
                                        <?php echo ($selected_location == $loc) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($loc); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary" style="width: 100%;">ค้นหาทรัพย์</button>
                    </form>
                </aside>

                <!-- Property Grid -->
                <div>
                    <div
                        style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                        <h2 style="font-size: 1.5rem; margin: 0; color: #0f2d5c;">รายการบ้าน คอนโด ที่ดิน <span
                                style="font-size: 1rem; color: #666; font-weight: 400;">(
                                <?php echo $total_rows; ?> รายการ)
                            </span></h2>
                    </div>

                    <div class="prop-grid" id="propGrid">
                        <!-- Properties will be loaded via AJAX -->
                        <div style="grid-column: 1/-1; text-align: center; padding: 40px;">
                            <i class="fa-solid fa-spinner fa-spin fa-2x"></i>
                            <p style="color: #888; margin-top: 15px;">กำลังโหลดข้อมูล...</p>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div id="paginationContainer" style="display: flex; justify-content: center; gap: 10px; margin-top: 40px;">
                        <!-- Pagination will be rendered via JavaScript -->
                    </div>

                    <script>
                    (function() {
                        let currentPage = 1;
                        let totalPages = 1;
                        let isLoading = false;
                        
                        // Get filters from form
                        function getFiltersFromForm() {
                            const filters = {};
                            
                            // Get checked types
                            const checkedTypes = [];
                            document.querySelectorAll('input[name="type[]"]').forEach(cb => {
                                if (cb.checked) checkedTypes.push(cb.value);
                            });
                            if (checkedTypes.length > 0) filters.type = checkedTypes.join(',');
                            
                            // Get selected location
                            const locationSelect = document.querySelector('select[name="location"]');
                            if (locationSelect && locationSelect.value) {
                                filters.location = locationSelect.value;
                            }
                            
                            return filters;
                        }

                        // Load properties via AJAX
                        async function loadProperties(page, filters = null, shouldScroll = false) {
                            if (isLoading) return;
                            isLoading = true;

                            const filtersData = filters || getFiltersFromForm();

                            const propGrid = document.getElementById('propGrid');
                            const paginationContainer = document.getElementById('paginationContainer');

                            // Show loading
                            propGrid.innerHTML = `
                                <div style="grid-column: 1/-1; text-align: center; padding: 40px;">
                                    <i class="fa-solid fa-spinner fa-spin fa-2x" style="color: #1c4587;"></i>
                                    <p style="color: #888; margin-top: 15px;">กำลังโหลดข้อมูล...</p>
                                </div>
                            `;

                            // Build query params
                            let params = new URLSearchParams();
                            params.append('page', page);
                            if (filtersData.type) params.append('type', filtersData.type);
                            if (filtersData.location) params.append('location', filtersData.location);

                            try {
                                const response = await fetch('api_properties.php?' + params.toString());
                                const result = await response.json();

                                if (result.success) {
                                    currentPage = result.data.current_page;
                                    totalPages = result.data.total_pages;

                                    // Update total count
                                    const totalEl = document.querySelector('h2 span');
                                    if (totalEl) {
                                        totalEl.textContent = `(${result.data.total_rows} รายการ)`;
                                    }

                                    // Render properties
                                    renderProperties(result.data.properties);

                                    // Render pagination
                                    renderPagination(currentPage, totalPages);

                                    if (shouldScroll) {
                                        propGrid.scrollIntoView({ behavior: 'smooth', block: 'start' });
                                    }
                                } else {
                                    propGrid.innerHTML = `
                                        <div style="grid-column: 1/-1; text-align: center; padding: 40px; background: #fff3cd; border-radius: 10px;">
                                            <p style="color: #856404;">เกิดข้อผิดพลาด: ${result.error || 'ไม่สามารถโหลดข้อมูลได้'}</p>
                                        </div>
                                    `;
                                }
                            } catch (error) {
                                propGrid.innerHTML = `
                                    <div style="grid-column: 1/-1; text-align: center; padding: 40px; background: #f8d7da; border-radius: 10px;">
                                        <p style="color: #721c24;">เกิดข้อผิดพลาดในการเชื่อมต่อ</p>
                                    </div>
                                `;
                            }

                            isLoading = false;
                        }

                        // Render property cards
                        function renderProperties(properties) {
                            const propGrid = document.getElementById('propGrid');

                            if (properties.length === 0) {
                                propGrid.innerHTML = `
                                    <div style="grid-column: 1/-1; text-align: center; padding: 50px;">
                                        <i class="fa-solid fa-search" style="font-size: 3rem; color: #eee; margin-bottom: 20px;"></i>
                                        <p style="color: #666;">ไม่พบข้อมูลทรัพย์สิน</p>
                                    </div>
                                `;
                                return;
                            }

                            let html = '';
                            properties.forEach(prop => {
                                // ใช้ไอคอนต่างกันตามประเภท
                                const isLand = prop.type === 'ที่ดินเปล่า';
                                const placeholderIcon = isLand ? 'fa-solid fa-map-location-dot' : 'fa-solid fa-house-chimney';
                                
                                const imageHtml = prop.image_path
                                    ? `<img src="${escapeHtml(prop.image_path)}" alt="${escapeHtml(prop.title)}" style="width: 100%; height: 100%; object-fit: cover;">`
                                    : `<i class="${placeholderIcon}"></i>`;

                                let facilityHtml = '';
                                // ไม่แสดง bed/bath สำหรับที่ดินเปล่า
                                if (!isLand) {
                                    if (prop.bed > 0) {
                                        facilityHtml += `<div><i class="fa-solid fa-bed"></i> ${prop.bed} นอน</div>`;
                                    }
                                    if (prop.bath > 0) {
                                        facilityHtml += `<div><i class="fa-solid fa-bath"></i> ${prop.bath} น้ำ</div>`;
                                    }
                                }
                                // แสดงพื้นที่ที่ดินสำหรับทุกประเภท
                                const areaValue = prop.land_size || prop.area;
                                if (areaValue) {
                                    const areaIcon = isLand ? 'fa-solid fa-map-location-dot' : 'fa-solid fa-ruler-combined';
                                    facilityHtml += `<div><i class="${areaIcon}"></i> ${escapeHtml(areaValue)}</div>`;
                                }

                                html += `
                                    <div class="prop-card" style="opacity: 0; animation: fadeInUp 0.4s ease forwards;">
                                        <div class="prop-img">
                                            ${imageHtml}
                                            <span class="prop-type-badge">${escapeHtml(prop.type)}</span>
                                        </div>
                                        <div class="prop-content">
                                            <h3 class="prop-title">${escapeHtml(prop.title)}</h3>
                                            <div class="prop-location">
                                                <i class="fa-solid fa-location-dot" style="color: var(--accent-gold);"></i>
                                                ${escapeHtml(prop.location)}
                                            </div>
                                            <div class="prop-facility">
                                                ${facilityHtml}
                                            </div>
                                            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: auto;">
                                                <div class="prop-price">${escapeHtml(prop.price)}</div>
                                                <a href="property_detail.php?id=${prop.id}" class="btn btn-primary" style="padding: 8px 20px; font-size: 0.9rem;">ดูรายละเอียด</a>
                                            </div>
                                        </div>
                                    </div>
                                `;
                            });

                            propGrid.innerHTML = html;
                        }

                        // Render pagination
                        function renderPagination(current, total) {
                            const container = document.getElementById('paginationContainer');

                            if (total <= 1) {
                                container.innerHTML = '';
                                return;
                            }

                            let html = '';

                            // Previous button
                            if (current > 1) {
                                html += `<button onclick="window.loadPropertiesPage(${current - 1})" class="btn" style="background: white; border: 1px solid #ddd; padding: 8px 15px; cursor: pointer;">
                                    <i class="fa-solid fa-chevron-left"></i>
                                </button>`;
                            }

                            // Page numbers
                            let startPage = Math.max(1, current - 2);
                            let endPage = Math.min(total, current + 2);

                            if (startPage > 1) {
                                html += `<button onclick="window.loadPropertiesPage(1)" class="btn" style="background: white; border: 1px solid #ddd; padding: 8px 15px; cursor: pointer;">1</button>`;
                                if (startPage > 2) {
                                    html += `<span style="padding: 8px; color: #999;">...</span>`;
                                }
                            }

                            for (let i = startPage; i <= endPage; i++) {
                                const isActive = i === current;
                                const activeStyle = isActive
                                    ? 'background: #1c4587; color: white; border: 1px solid #1c4587;'
                                    : 'background: white; border: 1px solid #ddd; color: #333;';
                                html += `<button onclick="window.loadPropertiesPage(${i})" class="btn" style="${activeStyle} padding: 8px 15px; cursor: pointer;">${i}</button>`;
                            }

                            if (endPage < total) {
                                if (endPage < total - 1) {
                                    html += `<span style="padding: 8px; color: #999;">...</span>`;
                                }
                                html += `<button onclick="window.loadPropertiesPage(${total})" class="btn" style="background: white; border: 1px solid #ddd; padding: 8px 15px; cursor: pointer;">${total}</button>`;
                            }

                            // Next button
                            if (current < total) {
                                html += `<button onclick="window.loadPropertiesPage(${current + 1})" class="btn" style="background: white; border: 1px solid #ddd; padding: 8px 15px; cursor: pointer;">
                                    <i class="fa-solid fa-chevron-right"></i>
                                </button>`;
                            }

                            container.innerHTML = html;
                        }

                        // Helper: escape HTML
                        function escapeHtml(text) {
                            if (!text) return '';
                            const div = document.createElement('div');
                            div.textContent = text;
                            return div.innerHTML;
                        }

                        // Expose function to window
                        window.loadPropertiesPage = function(page) {
                            loadProperties(page, null, true);
                            updateUrlWithFilters(page);
                        };

                        // Update URL with filters
                        function updateUrlWithFilters(page) {
                            const filters = getFiltersFromForm();
                            const url = new URL(window.location);

                            url.searchParams.delete('type[]');
                            url.searchParams.delete('location');
                            url.searchParams.delete('page');

                            url.searchParams.set('page', page);
                            if (filters.type) {
                                filters.type.split(',').forEach(t => url.searchParams.append('type[]', t));
                            }
                            if (filters.location) {
                                url.searchParams.set('location', filters.location);
                            }

                            window.history.pushState({ page: page, filters: filters }, '', url);
                        }

                        // Handle browser back/forward
                        window.addEventListener('popstate', function(e) {
                            const page = e.state?.page || 1;
                            if (e.state?.filters) {
                                restoreFiltersToForm(e.state.filters);
                            }
                            loadProperties(page, null, true);
                        });

                        // Restore filters to form
                        function restoreFiltersToForm(filters) {
                            document.querySelectorAll('input[name="type[]"]').forEach(cb => cb.checked = false);
                            const locationSelect = document.querySelector('select[name="location"]');
                            if (locationSelect) locationSelect.value = '';

                            if (filters.type) {
                                filters.type.split(',').forEach(type => {
                                    const cb = document.querySelector(`input[name="type[]"][value="${type}"]`);
                                    if (cb) cb.checked = true;
                                });
                            }
                            if (filters.location && locationSelect) {
                                locationSelect.value = filters.location;
                            }
                        }

                        // Search button handler
                        const searchBtn = document.querySelector('button[type="submit"]');
                        if (searchBtn) {
                            searchBtn.type = 'button';
                            searchBtn.innerHTML = '<i class="fa-solid fa-search"></i> ค้นหา';
                            searchBtn.addEventListener('click', function() {
                                loadProperties(1, null, true);
                                updateUrlWithFilters(1);
                            });
                        }

                        // Initial load
                        const urlParams = new URLSearchParams(window.location.search);
                        const initialPage = parseInt(urlParams.get('page')) || 1;
                        loadProperties(initialPage, null, false);
                    })();
                    </script>

                    <style>
                    @keyframes fadeInUp {
                        from {
                            opacity: 0;
                            transform: translateY(20px);
                        }
                        to {
                            opacity: 1;
                            transform: translateY(0);
                        }
                    }
                    .prop-card:nth-child(1) { animation-delay: 0.05s; }
                    .prop-card:nth-child(2) { animation-delay: 0.1s; }
                    .prop-card:nth-child(3) { animation-delay: 0.15s; }
                    .prop-card:nth-child(4) { animation-delay: 0.2s; }
                    .prop-card:nth-child(5) { animation-delay: 0.25s; }
                    .prop-card:nth-child(6) { animation-delay: 0.3s; }
                    .prop-card:nth-child(7) { animation-delay: 0.35s; }
                    .prop-card:nth-child(8) { animation-delay: 0.4s; }
                    .prop-card:nth-child(9) { animation-delay: 0.45s; }

                    #paginationContainer button {
                        transition: all 0.2s ease;
                    }
                    #paginationContainer button:hover {
                        transform: translateY(-2px);
                        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
                    }
                    </style>

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
