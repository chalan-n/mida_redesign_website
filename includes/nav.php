<?php
/**
 * Navigation Menu Component
 * Include this file in all pages: <?php include 'includes/nav.php'; ?>
 *
 * Available variables (optional):
 * - $active_page: string to mark active menu item (e.g., 'home', 'services', 'used_cars', 'properties', 'auction', 'branches')
 * - $settings: array containing site settings (logo, etc.)
 */

if (!isset($active_page)) {
    $active_page = '';
}

$nav_logo_src = !empty($settings['site_logo']) ? $settings['site_logo'] : 'img/mida_logo_5.png';
?>

<header class="header">
    <div class="container nav-container">
        <a href="index.php" class="logo">
            <img src="<?php echo htmlspecialchars($nav_logo_src); ?>" alt="MIDA LEASING" class="logo-img">
        </a>

        <ul class="nav-menu" id="site-navigation">
            <li><a href="index.php" class="nav-link <?php echo $active_page == 'home' ? 'active' : ''; ?>">หน้าแรก</a></li>
            <li class="nav-dropdown">
                <a href="service_hire_purchase.php"
                    class="nav-link nav-dropdown-toggle <?php echo $active_page == 'services' ? 'active' : ''; ?>"
                    aria-haspopup="true">
                    ขอสินเชื่อ <i class="fa-solid fa-chevron-down" aria-hidden="true"></i>
                </a>
                <ul class="nav-submenu" aria-label="เมนูบริการสินเชื่อ">
                    <li><a href="service_hire_purchase.php">สินเชื่อเช่าซื้อ</a></li>
                    <li><a href="service_title_loan.php">สินเชื่อจำนำทะเบียน</a></li>
                    <li><a href="service_personal_loan.php">สินเชื่อส่วนบุคคล</a></li>
                </ul>
            </li>
            <li><a href="used_cars.php" class="nav-link <?php echo $active_page == 'used_cars' ? 'active' : ''; ?>">รถสวยพร้อมขาย</a></li>
            <li><a href="properties.php" class="nav-link <?php echo $active_page == 'properties' ? 'active' : ''; ?>">บ้าน คอนโด ที่ดิน</a></li>
            <li><a href="auction.php" class="nav-link <?php echo $active_page == 'auction' ? 'active' : ''; ?>">ประมูลรถยนต์</a></li>
            <li><a href="contact_branches.php" class="nav-link <?php echo $active_page == 'branches' ? 'active' : ''; ?>">ค้นหาสาขา</a></li>
            <li><a href="contact_us.php" class="btn btn-primary" style="padding: 8px 20px; font-size: 0.9rem;">คุยกับเจ้าหน้าที่</a></li>
        </ul>

        <button type="button"
                class="hamburger"
                aria-label="Toggle navigation menu"
                aria-expanded="false"
                aria-controls="site-navigation">
            <i class="fa-solid fa-bars" aria-hidden="true"></i>
        </button>
    </div>
</header>
