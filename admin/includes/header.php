<?php
// admin/includes/header.php
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . '/AdminPermission.php';
require_once __DIR__ . '/../config/db.php';

// โหลด permissions
$database = new Database();
$db = $database->getConnection();
$perm = new AdminPermission($db, $_SESSION['admin_id']);

// กำหนดโครงสร้างเมนู
$menuStructure = [
    'main' => [
        ['module' => 'dashboard', 'page' => 'index.php', 'icon' => 'fa-gauge', 'label' => 'ภาพรวม (Dashboard)'],
        ['module' => 'banners', 'page' => 'banners.php', 'form' => 'banner_form.php', 'icon' => 'fa-images', 'label' => 'จัดการแบนเนอร์'],
        ['module' => 'services', 'page' => 'services.php', 'form' => 'service_form.php', 'icon' => 'fa-layer-group', 'label' => 'จัดการบริการ'],
        ['module' => 'pages', 'page' => 'pages.php', 'form' => 'page_form.php', 'icon' => 'fa-file-contract', 'label' => 'จัดการเนื้อหา'],
        ['module' => 'announcements', 'page' => 'announcements.php', 'form' => 'announcement_form.php', 'icon' => 'fa-bullhorn', 'label' => 'จัดการข่าวสาร'],
    ],
    'data' => [
        ['module' => 'loan_applications', 'page' => 'loan_applications.php', 'icon' => 'fa-money-check-dollar', 'label' => 'ผู้สมัครสินเชื่อ'],
        ['module' => 'auction_cars', 'page' => 'auction_cars.php', 'form' => 'auction_car_form.php', 'icon' => 'fa-car', 'label' => 'รถประมูล'],
        ['module' => 'auction_schedules', 'page' => 'auction_schedules.php', 'form' => 'auction_schedule_form.php', 'icon' => 'fa-calendar-days', 'label' => 'ตารางประมูล'],
        ['module' => 'auction_schedules', 'page' => 'auction_round_manager.php', 'icon' => 'fa-car-side', 'label' => 'รถเข้ารอบประมูล'],
        ['module' => 'auction_schedules', 'page' => 'auction_featured.php', 'icon' => 'fa-star', 'label' => 'รถเด่นประจำรอบ'],
        ['module' => 'used_cars', 'page' => 'used_cars.php', 'form' => 'used_car_form.php', 'icon' => 'fa-car-rear', 'label' => 'รถสวยพร้อมขาย'],
        ['module' => 'properties', 'page' => 'properties.php', 'form' => 'property_form.php', 'icon' => 'fa-house-chimney', 'label' => 'บ้านคอนโดที่ดิน'],
        ['module' => 'property_leads', 'page' => 'property_leads.php', 'icon' => 'fa-address-book', 'label' => 'ผู้สนใจบ้านคอนโดที่ดิน'],
        ['module' => 'branches', 'page' => 'branches.php', 'form' => 'branch_form.php', 'icon' => 'fa-map-location-dot', 'label' => 'สาขาให้บริการ'],
        ['module' => 'careers', 'page' => 'careers.php', 'form' => 'career_form.php', 'icon' => 'fa-briefcase', 'label' => 'ร่วมงานกับเรา'],
    ],
    'investor' => [
        ['module' => 'financials', 'page' => 'financials.php', 'form' => 'financial_form.php', 'icon' => 'fa-file-invoice-dollar', 'label' => 'ข้อมูลทางการเงิน'],
        ['module' => 'publications', 'page' => 'publications.php', 'form' => 'publication_form.php', 'icon' => 'fa-file-arrow-down', 'label' => 'เอกสารดาวน์โหลด'],
    ],
    'system' => [
        ['module' => 'contact_messages', 'page' => 'contact_messages.php', 'icon' => 'fa-envelope', 'label' => 'ข้อความติดต่อ'],
        ['module' => 'settings', 'page' => 'settings.php', 'icon' => 'fa-cog', 'label' => 'ตั้งค่าเว็บไซต์'],
        ['module' => 'settings', 'page' => 'share_buttons.php', 'icon' => 'fa-share-nodes', 'label' => 'ปุ่มแชร์หน้านี้'],
        ['module' => 'settings', 'page' => 'visitor_stats.php', 'icon' => 'fa-chart-line', 'label' => 'สถิติผู้เข้าชม'],
    ],
    'admin' => [
        ['module' => 'users', 'page' => 'users.php', 'form' => 'user_form.php', 'icon' => 'fa-users', 'label' => 'จัดการผู้ใช้'],
        ['module' => 'roles', 'page' => 'roles.php', 'form' => 'role_form.php', 'icon' => 'fa-user-shield', 'label' => 'กลุ่มผู้ใช้'],
    ],
];

// กรองเมนูตามสิทธิ์
function filterMenuByPermission($items, $perm)
{
    $filtered = [];
    foreach ($items as $item) {
        if ($perm->canView($item['module'])) {
            $filtered[] = $item;
        }
    }
    return $filtered;
}

$currentPage = basename($_SERVER['PHP_SELF']);

function isActive($item, $currentPage)
{
    if ($item['page'] === $currentPage)
        return true;
    if (isset($item['form']) && $item['form'] === $currentPage)
        return true;
    return false;
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mida Leasing Admin Panel</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600&display=swap" rel="stylesheet">

    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom Drag & Drop CSS -->
    <link href="css/drag-drop.css" rel="stylesheet">

    <!-- Bootstrap 5 JS Bundle (includes Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <style>
        :root {
            --primary-blue: #002D62;
            --accent-gold: #C5A059;
            --sidebar-width: 250px;
            --header-height: 60px;
        }

        body {
            font-family: 'Prompt', sans-serif;
            background-color: #f4f6f9;
            margin: 0;
            display: flex;
        }

        /* Sidebar */
        .sidebar {
            width: var(--sidebar-width);
            background: var(--primary-blue);
            color: white;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
        }

        .sidebar-header {
            height: var(--header-height);
            display: flex;
            align-items: center;
            padding: 0 20px;
            font-size: 1.2rem;
            font-weight: 600;
            background: rgba(0, 0, 0, 0.1);
            color: var(--accent-gold);
        }

        .nav-link {
            padding: 15px 20px;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            display: flex;
            align-items: center;
            transition: 0.3s;
            border-left: 4px solid transparent;
        }

        .nav-link:hover,
        .nav-link.active {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            border-left-color: var(--accent-gold);
        }

        .nav-link i {
            width: 30px;
        }

        .nav-section-title {
            padding: 0 20px 10px 20px;
            font-size: 0.8rem;
            color: var(--accent-gold);
            text-transform: uppercase;
            font-weight: bold;
        }

        .nav-divider {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            margin: 20px 0 10px 0;
        }

        /* Submenu */
        .nav-submenu {
            display: none;
            background: rgba(0, 0, 0, 0.1);
            padding-left: 20px;
        }

        .nav-submenu.show {
            display: block;
        }

        .nav-submenu .nav-link {
            font-size: 0.9em;
        }

        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            width: calc(100% - var(--sidebar-width));
            min-height: 100vh;
        }

        /* Top Header */
        .top-header {
            height: var(--header-height);
            background: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 30px;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .user-menu {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .user-name {
            font-weight: 500;
            color: #333;
        }

        .btn-logout {
            color: #d32f2f;
            text-decoration: none;
            font-size: 0.9rem;
            border: 1px solid #d32f2f;
            padding: 5px 15px;
            border-radius: 4px;
            transition: 0.3s;
        }

        .btn-logout:hover {
            background: #d32f2f;
            color: white;
        }

        /* Dashboard Content */
        .container-fluid {
            padding: 30px;
        }

        .page-header {
            margin-bottom: 30px;
        }

        .page-title {
            font-size: 1.8rem;
            color: #333;
            margin: 0;
        }

        /* Cards */
        .card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            padding: 20px;
            margin-bottom: 20px;
        }

        .stat-card {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: #e3f2fd;
            color: var(--primary-blue);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .stat-info h3 {
            margin: 0;
            font-size: 2rem;
            color: #333;
            text-align: center;
        }

        .stat-info p {
            margin: 0;
            color: #777;
        }
    </style>
</head>

<body>

    <!-- Sidebar -->
    <nav class="sidebar">
        <div class="sidebar-header">
            <i class="fa-solid fa-shield-halved" style="margin-right: 10px;"></i> Admin Panel
        </div>

        <?php
        // แสดงเมนูหลัก
        $mainMenu = filterMenuByPermission($menuStructure['main'], $perm);
        foreach ($mainMenu as $item):
            ?>
            <a href="<?php echo $item['page']; ?>"
                class="nav-link <?php echo isActive($item, $currentPage) ? 'active' : ''; ?>">
                <i class="fa-solid <?php echo $item['icon']; ?>"></i> <?php echo $item['label']; ?>
            </a>
        <?php endforeach; ?>

        <?php
        // แสดงเมนูจัดการข้อมูล
        $dataMenu = filterMenuByPermission($menuStructure['data'], $perm);
        if (!empty($dataMenu)):
            ?>
            <div class="nav-divider"></div>
            <div class="nav-section-title">จัดการข้อมูล</div>
            <?php foreach ($dataMenu as $item): ?>
                <a href="<?php echo $item['page']; ?>"
                    class="nav-link <?php echo isActive($item, $currentPage) ? 'active' : ''; ?>">
                    <i class="fa-solid <?php echo $item['icon']; ?>"></i> <?php echo $item['label']; ?>
                </a>
            <?php endforeach; ?>
        <?php endif; ?>

        <?php
        // แสดงเมนูนักลงทุนสัมพันธ์
        $irMenu = filterMenuByPermission($menuStructure['investor'], $perm);
        if (!empty($irMenu)):
            $irActive = false;
            foreach ($irMenu as $item) {
                if (isActive($item, $currentPage)) {
                    $irActive = true;
                    break;
                }
            }
            ?>
            <a href="#" class="nav-link" onclick="toggleSubmenu('ir-submenu'); return false;">
                <i class="fa-solid fa-chart-line"></i> นักลงทุนสัมพันธ์
                <i class="fa-solid fa-chevron-down" style="font-size: 0.8em; margin-left: auto; width: auto;"></i>
            </a>
            <div id="ir-submenu" class="nav-submenu <?php echo $irActive ? 'show' : ''; ?>">
                <?php foreach ($irMenu as $item): ?>
                    <a href="<?php echo $item['page']; ?>"
                        class="nav-link <?php echo isActive($item, $currentPage) ? 'active' : ''; ?>">
                        <i class="fa-solid <?php echo $item['icon']; ?>"></i> <?php echo $item['label']; ?>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php
        // แสดงเมนูระบบ
        $sysMenu = filterMenuByPermission($menuStructure['system'], $perm);
        foreach ($sysMenu as $item):
            ?>
            <a href="<?php echo $item['page']; ?>"
                class="nav-link <?php echo isActive($item, $currentPage) ? 'active' : ''; ?>">
                <i class="fa-solid <?php echo $item['icon']; ?>"></i> <?php echo $item['label']; ?>
            </a>
        <?php endforeach; ?>

        <?php
        // แสดงเมนูจัดการผู้ใช้
        $adminMenu = filterMenuByPermission($menuStructure['admin'], $perm);
        if (!empty($adminMenu)):
            ?>
            <div class="nav-divider"></div>
            <div class="nav-section-title">ผู้ดูแลระบบ</div>
            <?php foreach ($adminMenu as $item): ?>
                <a href="<?php echo $item['page']; ?>"
                    class="nav-link <?php echo isActive($item, $currentPage) ? 'active' : ''; ?>">
                    <i class="fa-solid <?php echo $item['icon']; ?>"></i> <?php echo $item['label']; ?>
                </a>
            <?php endforeach; ?>
        <?php endif; ?>

        <div style="margin-bottom: 20px;"></div>
        <a href="../index.php" target="_blank" class="nav-link" style="background: rgba(0,0,0,0.2);">
            <i class="fa-solid fa-external-link-alt"></i> ดูหน้าเว็บไซต์
        </a>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Header -->
        <header class="top-header">
            <div class="menu-toggle">
                <!-- Mobile toggle logic can be added here -->
            </div>
            <div class="user-menu">
                <span class="user-name">สวัสดี,
                    <?php echo isset($_SESSION['admin_name']) ? $_SESSION['admin_name'] : 'Admin'; ?></span>
                <a href="logout.php" class="btn-logout">ออกจากระบบ</a>
            </div>
        </header>

        <div class="container-fluid">

            <script>
                function toggleSubmenu(id) {
                    var el = document.getElementById(id);
                    if (el) {
                        el.classList.toggle('show');
                    }
                }
            </script>