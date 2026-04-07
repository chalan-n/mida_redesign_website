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

// Pagination Logic
$limit = 9;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
if ($page < 1)
    $page = 1;
$start = ($page - 1) * $limit;

// Fetch Filter Options
$brands = [];
$car_types = [];
$grades = [];

try {
    // Brands
    $sql_brands = "SELECT DISTINCT brand, COUNT(*) as count FROM auction_cars WHERE brand != '' GROUP BY brand ORDER BY brand";
    $stmt_brands = $db->query($sql_brands);
    $brands = $stmt_brands->fetchAll();

    // Car Types
    $sql_types = "SELECT DISTINCT car_type, COUNT(*) as count FROM auction_cars WHERE car_type != '' GROUP BY car_type ORDER BY car_type";
    $stmt_types = $db->query($sql_types);
    $car_types = $stmt_types->fetchAll();

    // Grades
    $sql_grades = "SELECT DISTINCT grade, COUNT(*) as count FROM auction_cars WHERE grade != '' GROUP BY grade ORDER BY grade";
    $stmt_grades = $db->query($sql_grades);
    $grades = $stmt_grades->fetchAll();

} catch (PDOException $e) {
}

// Fetch Cars
$cars = [];
$total_cars = 0;
$total_pages = 0;

// Build Filter Query
$where_clauses = ["1=1"];
$params = [];

if (isset($_GET['brands']) && is_array($_GET['brands'])) {
    $brand_placeholders = [];
    foreach ($_GET['brands'] as $key => $brand) {
        $placeholder = ":brand_" . $key;
        $brand_placeholders[] = $placeholder;
        $params[$placeholder] = $brand;
    }
    if (!empty($brand_placeholders)) {
        $where_clauses[] = "brand IN (" . implode(', ', $brand_placeholders) . ")";
    }
}

if (isset($_GET['types']) && is_array($_GET['types'])) {
    $type_placeholders = [];
    foreach ($_GET['types'] as $key => $type) {
        $placeholder = ":type_" . $key;
        $type_placeholders[] = $placeholder;
        $params[$placeholder] = $type;
    }
    if (!empty($type_placeholders)) {
        $where_clauses[] = "car_type IN (" . implode(', ', $type_placeholders) . ")";
    }
}

if (isset($_GET['grades']) && is_array($_GET['grades'])) {
    $grade_placeholders = [];
    foreach ($_GET['grades'] as $key => $grade) {
        $placeholder = ":grade_" . $key;
        $grade_placeholders[] = $placeholder;
        $params[$placeholder] = $grade;
    }
    if (!empty($grade_placeholders)) {
        $where_clauses[] = "grade IN (" . implode(', ', $grade_placeholders) . ")";
    }
}

$where_sql = implode(' AND ', $where_clauses);

try {
    // Count total cars
    $count_sql = "SELECT COUNT(*) FROM auction_cars WHERE $where_sql";
    $stmt_count = $db->prepare($count_sql);
    foreach ($params as $key => $value) {
        $stmt_count->bindValue($key, $value);
    }
    $stmt_count->execute();
    $total_cars = $stmt_count->fetchColumn();
    $total_pages = ceil($total_cars / $limit);

    // Fetch cars for current page
    $sql = "SELECT * FROM auction_cars WHERE $where_sql ORDER BY created_at DESC LIMIT :start, :limit";
    $stmt = $db->prepare($sql);
    $stmt = $db->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':start', $start, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    $cars = $stmt->fetchAll();
} catch (PDOException $e) {
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายการรถประมูล - MIDA LEASING</title>
    <meta name="description" content="ค้นหารถประมูล รถมือสองสภาพดี หลากหลายรุ่น ยี่ห้อ ราคาคุ้มค่า">

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
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            color: white;
            padding: 140px 0 60px;
            text-align: center;
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
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            border: 1px solid #eee;
            height: fit-content;
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

        .car-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 20px;
        }

        .car-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
            border: 1px solid #eee;
            transition: all 0.3s;
        }

        .car-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }

        .car-img {
            height: 180px;
            background-color: #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #94a3b8;
            font-size: 3rem;
            position: relative;
        }

        .car-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: var(--accent-gold);
            color: #000;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .car-info {
            padding: 15px;
        }

        .car-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 5px;
            color: #333;
        }

        .car-details {
            font-size: 0.9rem;
            color: #666;
            margin-bottom: 15px;
            display: flex;
            gap: 15px;
        }

        .car-price {
            color: var(--primary-blue);
            font-size: 1.2rem;
            font-weight: 700;
        }

        @media (max-width: 992px) {
            .layout-grid {
                grid-template-columns: 1fr;
            }

            .filter-sidebar {
                display: none;
                /* Hide sidebar on mobile for now (or make it a modal) */
            }
        }
    </style>
</head>

<body>

    <!-- Header -->
    <?php $active_page = 'auction'; include 'includes/nav.php'; ?>

    <!-- Page Header -->
    <div class="page-header">
        <div class="container">
            <h1 style="font-size: 2.5rem; margin-bottom: 10px; font-weight: 700; color: #fec435;">รายการรถประมูล</h1>
            <p style="opacity: 0.8;">รอบประมูล: 13 ม.ค. 2569 - สาขานครปฐม</p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="section" style="padding-top: 0; background-color: #f8f9fa; min-height: 80vh;">
        <div class="container">

            <div style="padding: 20px 0;">
                <a href="auction.php"
                    style="text-decoration: none; color: #666; display: inline-flex; align-items: center;">
                    <i class="fa-solid fa-arrow-left" style="margin-right: 8px;"></i> ย้อนกลับ
                </a>
            </div>

            <div class="layout-grid">

                <!-- Sidebar Filter -->
                <aside class="filter-sidebar">
                    <form action="" method="GET" id="filterForm">
                        <h3 style="margin-bottom: 20px; font-size: 1.2rem;"><i class="fa-solid fa-filter"></i>
                            กรองข้อมูล
                        </h3>

                        <!-- Brands Filter -->
                        <div class="filter-group">
                            <label class="filter-title">ยี่ห้อรถ</label>
                            <?php if (count($brands) > 0): ?>
                                <?php foreach ($brands as $b): ?>
                                    <?php $checked = (isset($_GET['brands']) && in_array($b['brand'], $_GET['brands'])) ? 'checked' : ''; ?>
                                    <label class="filter-checkbox">
                                        <input type="checkbox" name="brands[]"
                                            value="<?php echo htmlspecialchars($b['brand']); ?>" <?php echo $checked; ?>>
                                        <?php echo htmlspecialchars($b['brand']); ?> (<?php echo $b['count']; ?>)
                                    </label>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p style="color: #999; font-size: 0.9rem;">ไม่มียี่ห้อรถ</p>
                            <?php endif; ?>
                        </div>

                        <!-- Car Types Filter -->
                        <div class="filter-group">
                            <label class="filter-title">ประเภทรถ</label>
                            <?php if (count($car_types) > 0): ?>
                                <?php foreach ($car_types as $t): ?>
                                    <?php $checked = (isset($_GET['types']) && in_array($t['car_type'], $_GET['types'])) ? 'checked' : ''; ?>
                                    <label class="filter-checkbox">
                                        <input type="checkbox" name="types[]"
                                            value="<?php echo htmlspecialchars($t['car_type']); ?>" <?php echo $checked; ?>>
                                        <?php echo htmlspecialchars($t['car_type']); ?> (<?php echo $t['count']; ?>)
                                    </label>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p style="color: #999; font-size: 0.9rem;">ไม่มีข้อมูลประเภทรถ</p>
                            <?php endif; ?>
                        </div>



                        <button type="button" id="btnSearch" class="btn btn-primary" style="width: 100%;">
                            <i class="fa-solid fa-search"></i> ค้นหา
                        </button>
                        <button type="button" id="btnClear" class="btn"
                            style="width: 100%; margin-top: 10px; display: block; text-align: center; border: 1px solid #eee; color: #666;">
                            <i class="fa-solid fa-times"></i> ล้างค่า
                        </button>
                    </form>
                </aside>

                <!-- Car Grid -->
                <div>
                    <div
                        style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                        <h2 style="font-size: 1.5rem; margin: 0;">รถทั้งหมด <span
                                style="font-size: 1rem; color: #666; font-weight: 400;">(<?php echo $total_cars; ?>
                                รายการ)</span></h2>
                    </div>

                    <div class="car-grid" id="carGrid">
                        <!-- Cars will be loaded via AJAX -->
                        <div style="grid-column: 1/-1; text-align: center; padding: 40px;">
                            <i class="fa-solid fa-spinner fa-spin fa-2x"></i>
                            <p style="color: #888; margin-top: 15px;">กำลังโหลดข้อมูล...</p>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div id="paginationContainer"
                        style="display: flex; justify-content: center; gap: 10px; margin-top: 40px;">
                        <!-- Pagination will be rendered via JavaScript -->
                    </div>

                    <!-- Hidden data for JavaScript -->
                    <input type="hidden" id="currentFilters" value="<?php
                    $filters = [];
                    if (isset($_GET['brands']))
                        $filters['brands'] = implode(',', $_GET['brands']);
                    if (isset($_GET['types']))
                        $filters['types'] = implode(',', $_GET['types']);
                    if (isset($_GET['grades']))
                        $filters['grades'] = implode(',', $_GET['grades']);
                    echo htmlspecialchars(json_encode($filters));
                    ?>">

                    <script>
                        (function () {
                            let currentPage = 1;
                            let totalPages = 1;
                            let isLoading = false;

                            // Get filters from checkboxes (dynamic)
                            function getFiltersFromForm() {
                                const filters = {};

                                // Get checked brands
                                const checkedBrands = [];
                                document.querySelectorAll('input[name="brands[]"]').forEach(cb => {
                                    if (cb.checked) checkedBrands.push(cb.value);
                                });
                                if (checkedBrands.length > 0) filters.brands = checkedBrands.join(',');

                                // Get checked types
                                const checkedTypes = [];
                                document.querySelectorAll('input[name="types[]"]').forEach(cb => {
                                    if (cb.checked) checkedTypes.push(cb.value);
                                });
                                if (checkedTypes.length > 0) filters.types = checkedTypes.join(',');

                                // Get checked grades
                                const checkedGrades = [];
                                document.querySelectorAll('input[name="grades[]"]').forEach(cb => {
                                    if (cb.checked) checkedGrades.push(cb.value);
                                });
                                if (checkedGrades.length > 0) filters.grades = checkedGrades.join(',');

                                // Get schedule_id from URL if present
                                const urlParams = new URLSearchParams(window.location.search);
                                if (urlParams.get('schedule_id')) {
                                    filters.schedule_id = urlParams.get('schedule_id');
                                }

                                return filters;
                            }

                            // Load cars via AJAX
                            async function loadCars(page, filters = null) {
                                if (isLoading) return;
                                isLoading = true;

                                // Use provided filters or get from form
                                const filtersData = filters || getFiltersFromForm();

                                const carGrid = document.getElementById('carGrid');
                                const paginationContainer = document.getElementById('paginationContainer');

                                // Show loading
                                carGrid.innerHTML = `
                                <div style="grid-column: 1/-1; text-align: center; padding: 40px;">
                                    <i class="fa-solid fa-spinner fa-spin fa-2x" style="color: #002D62;"></i>
                                    <p style="color: #888; margin-top: 15px;">กำลังโหลดข้อมูล...</p>
                                </div>
                            `;

                                // Build query params
                                let params = new URLSearchParams();
                                params.append('page', page);
                                if (filtersData.brands) params.append('brands', filtersData.brands);
                                if (filtersData.types) params.append('types', filtersData.types);
                                if (filtersData.grades) params.append('grades', filtersData.grades);
                                if (filtersData.schedule_id) params.append('schedule_id', filtersData.schedule_id);

                                try {
                                    const response = await fetch('api_auction_list.php?' + params.toString());
                                    const result = await response.json();

                                    if (result.success) {
                                        currentPage = result.data.current_page;
                                        totalPages = result.data.total_pages;

                                        // Update total cars count
                                        const totalCarsEl = document.querySelector('h2 span');
                                        if (totalCarsEl) {
                                            totalCarsEl.textContent = `(${result.data.total_cars} รายการ)`;
                                        }

                                        // Render cars
                                        renderCars(result.data.cars);

                                        // Render pagination
                                        renderPagination(currentPage, totalPages);

                                        // Scroll to top of grid smoothly
                                        carGrid.scrollIntoView({ behavior: 'smooth', block: 'start' });
                                    } else {
                                        carGrid.innerHTML = `
                                        <div style="grid-column: 1/-1; text-align: center; padding: 40px; background: #fff3cd; border-radius: 10px;">
                                            <p style="color: #856404;">เกิดข้อผิดพลาด: ${result.error || 'ไม่สามารถโหลดข้อมูลได้'}</p>
                                        </div>
                                    `;
                                    }
                                } catch (error) {
                                    carGrid.innerHTML = `
                                    <div style="grid-column: 1/-1; text-align: center; padding: 40px; background: #f8d7da; border-radius: 10px;">
                                        <p style="color: #721c24;">เกิดข้อผิดพลาดในการเชื่อมต่อ</p>
                                    </div>
                                `;
                                }

                                isLoading = false;
                            }

                            // Render car cards
                            function renderCars(cars) {
                                const carGrid = document.getElementById('carGrid');

                                if (cars.length === 0) {
                                    carGrid.innerHTML = `
                                    <div style="grid-column: 1/-1; text-align: center; padding: 40px; background: white; border-radius: 10px;">
                                        <p style="color: #888;">ไม่พบรายการรถในขณะนี้</p>
                                    </div>
                                `;
                                    return;
                                }

                                let html = '';
                                cars.forEach(car => {
                                    const imageHtml = car.image_path
                                        ? `<img src="${escapeHtml(car.image_path)}" alt="${escapeHtml(car.title)}" style="width: 100%; height: 100%; object-fit: cover;">`
                                        : `<i class="fa-solid fa-car-side"></i>`;

                                    const queueBadge = car.queue_number
                                        ? `<span style="position: absolute; top: 10px; left: 10px; background: rgba(0,0,0,0.7); color: white; padding: 2px 8px; border-radius: 4px; font-size: 0.8rem;">คันที่: ${escapeHtml(car.queue_number)}</span>`
                                        : '';

                                    html += `
                                    <div class="car-card" style="opacity: 0; animation: fadeInUp 0.4s ease forwards;">
                                        <div class="car-img">
                                            ${imageHtml}
                                            ${queueBadge}
                                        </div>
                                        <div class="car-info">
                                            <h3 class="car-title">${escapeHtml(car.title)}</h3>
                                            <div class="car-details">
                                                <span><i class="fa-solid fa-gauge"></i> ${escapeHtml(car.mileage || '-')}</span>
                                                <span><i class="fa-solid fa-gear"></i> ${escapeHtml(car.transmission || '-')}</span>
                                            </div>
                                            <div style="display: flex; justify-content: space-between; align-items: end;">
                                                <div>
                                                    <div style="font-size: 0.8rem; color: #888;">ราคาเปิดประมูล</div>
                                                    <div class="car-price" ${car.no_starting_price == 1 ? 'style="color: #e74c3c;"' : ''}>${car.no_starting_price == 1 ? 'ไม่มีราคาเริ่มต้น' : escapeHtml(car.price || '-')}</div>
                                                </div>
                                                <a href="auction_detail.php?id=${car.id}" class="btn btn-accent" style="padding: 5px 15px; font-size: 0.9rem;">ดูรายละเอียด</a>
                                            </div>
                                        </div>
                                    </div>
                                `;
                                });

                                carGrid.innerHTML = html;
                            }

                            // Render pagination buttons
                            function renderPagination(current, total) {
                                const container = document.getElementById('paginationContainer');

                                if (total <= 1) {
                                    container.innerHTML = '';
                                    return;
                                }

                                let html = '';

                                // Previous button
                                if (current > 1) {
                                    html += `<button onclick="window.loadAuctionPage(${current - 1})" class="btn" style="background: white; border: 1px solid #ddd; padding: 8px 15px; cursor: pointer;">
                                    <i class="fa-solid fa-chevron-left"></i>
                                </button>`;
                                }

                                // Page numbers (show max 5 pages around current)
                                let startPage = Math.max(1, current - 2);
                                let endPage = Math.min(total, current + 2);

                                if (startPage > 1) {
                                    html += `<button onclick="window.loadAuctionPage(1)" class="btn" style="background: white; border: 1px solid #ddd; padding: 8px 15px; cursor: pointer;">1</button>`;
                                    if (startPage > 2) {
                                        html += `<span style="padding: 8px; color: #999;">...</span>`;
                                    }
                                }

                                for (let i = startPage; i <= endPage; i++) {
                                    const isActive = i === current;
                                    const activeStyle = isActive
                                        ? 'background: #002D62; color: white; border: 1px solid #002D62;'
                                        : 'background: white; border: 1px solid #ddd; color: #333;';
                                    html += `<button onclick="window.loadAuctionPage(${i})" class="btn" style="${activeStyle} padding: 8px 15px; cursor: pointer;">${i}</button>`;
                                }

                                if (endPage < total) {
                                    if (endPage < total - 1) {
                                        html += `<span style="padding: 8px; color: #999;">...</span>`;
                                    }
                                    html += `<button onclick="window.loadAuctionPage(${total})" class="btn" style="background: white; border: 1px solid #ddd; padding: 8px 15px; cursor: pointer;">${total}</button>`;
                                }

                                // Next button
                                if (current < total) {
                                    html += `<button onclick="window.loadAuctionPage(${current + 1})" class="btn" style="background: white; border: 1px solid #ddd; padding: 8px 15px; cursor: pointer;">
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

                            // Expose loadCars to window for pagination buttons
                            window.loadAuctionPage = function (page) {
                                loadCars(page);
                                // Update URL without refresh
                                updateUrlWithFilters(page);
                            };

                            // Update URL with current filters
                            function updateUrlWithFilters(page) {
                                const filters = getFiltersFromForm();
                                const url = new URL(window.location);

                                // Clear existing filter params
                                url.searchParams.delete('brands[]');
                                url.searchParams.delete('types[]');
                                url.searchParams.delete('grades[]');
                                url.searchParams.delete('page');

                                // Set new params
                                url.searchParams.set('page', page);
                                if (filters.brands) {
                                    filters.brands.split(',').forEach(b => url.searchParams.append('brands[]', b));
                                }
                                if (filters.types) {
                                    filters.types.split(',').forEach(t => url.searchParams.append('types[]', t));
                                }
                                if (filters.grades) {
                                    filters.grades.split(',').forEach(g => url.searchParams.append('grades[]', g));
                                }

                                window.history.pushState({ page: page, filters: filters }, '', url);
                            }

                            // Handle browser back/forward buttons
                            window.addEventListener('popstate', function (e) {
                                const page = e.state?.page || 1;
                                // Restore checkbox states from state if available
                                if (e.state?.filters) {
                                    restoreFiltersToForm(e.state.filters);
                                }
                                loadCars(page);
                            });

                            // Restore filters to checkboxes
                            function restoreFiltersToForm(filters) {
                                // Clear all checkboxes first
                                document.querySelectorAll('#filterForm input[type="checkbox"]').forEach(cb => cb.checked = false);

                                if (filters.brands) {
                                    filters.brands.split(',').forEach(brand => {
                                        const cb = document.querySelector(`input[name="brands[]"][value="${brand}"]`);
                                        if (cb) cb.checked = true;
                                    });
                                }
                                if (filters.types) {
                                    filters.types.split(',').forEach(type => {
                                        const cb = document.querySelector(`input[name="types[]"][value="${type}"]`);
                                        if (cb) cb.checked = true;
                                    });
                                }
                                if (filters.grades) {
                                    filters.grades.split(',').forEach(grade => {
                                        const cb = document.querySelector(`input[name="grades[]"][value="${grade}"]`);
                                        if (cb) cb.checked = true;
                                    });
                                }
                            }

                            // Search button handler
                            document.getElementById('btnSearch').addEventListener('click', function () {
                                loadCars(1); // Always start from page 1 when filtering
                                updateUrlWithFilters(1);
                            });

                            // Clear button handler
                            document.getElementById('btnClear').addEventListener('click', function () {
                                // Uncheck all checkboxes
                                document.querySelectorAll('#filterForm input[type="checkbox"]').forEach(cb => cb.checked = false);
                                // Load all cars
                                loadCars(1);
                                // Update URL
                                window.history.pushState({ page: 1, filters: {} }, '', 'auction_list.php');
                            });

                            // Initial load
                            const urlParams = new URLSearchParams(window.location.search);
                            const initialPage = parseInt(urlParams.get('page')) || 1;
                            loadCars(initialPage);
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

                        .car-card:nth-child(1) {
                            animation-delay: 0.05s;
                        }

                        .car-card:nth-child(2) {
                            animation-delay: 0.1s;
                        }

                        .car-card:nth-child(3) {
                            animation-delay: 0.15s;
                        }

                        .car-card:nth-child(4) {
                            animation-delay: 0.2s;
                        }

                        .car-card:nth-child(5) {
                            animation-delay: 0.25s;
                        }

                        .car-card:nth-child(6) {
                            animation-delay: 0.3s;
                        }

                        .car-card:nth-child(7) {
                            animation-delay: 0.35s;
                        }

                        .car-card:nth-child(8) {
                            animation-delay: 0.4s;
                        }

                        .car-card:nth-child(9) {
                            animation-delay: 0.45s;
                        }

                        #paginationContainer button {
                            transition: all 0.2s ease;
                        }

                        #paginationContainer button:hover {
                            transform: translateY(-2px);
                            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
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