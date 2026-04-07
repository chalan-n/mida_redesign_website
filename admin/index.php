<?php
session_start();
require_once 'config/db.php';

// Include Header
require_once 'includes/header.php';

// Fetch Basic Stats
$database = new Database();
$db = $database->getConnection();

// Count Pending Loan Applications (status = 'pending' or similar)
$pending_loans = 0;
try {
    $stmt = $db->query("SELECT COUNT(*) FROM loan_applications WHERE status = 'pending' OR status IS NULL OR status = ''");
    $pending_loans = $stmt->fetchColumn();
} catch (PDOException $e) {
}

// Count Pending Property Inquiries
$pending_property_inquiries = 0;
try {
    $stmt = $db->query("SELECT COUNT(*) FROM property_inquiries WHERE status = 'pending' OR status IS NULL OR status = ''");
    $pending_property_inquiries = $stmt->fetchColumn();
} catch (PDOException $e) {
}

// Count Properties for Sale
$properties_count = 0;
try {
    $stmt = $db->query("SELECT COUNT(*) FROM properties WHERE is_active = 1");
    $properties_count = $stmt->fetchColumn();
} catch (PDOException $e) {
}

// Count Auction Cars
$auction_cars_count = 0;
try {
    $stmt = $db->query("SELECT COUNT(*) FROM auction_cars");
    $auction_cars_count = $stmt->fetchColumn();
} catch (PDOException $e) {
}

// Count Unread Messages
$unread_messages = 0;
try {
    $stmt = $db->query("SELECT COUNT(*) FROM contact_messages WHERE is_read = 0");
    $unread_messages = $stmt->fetchColumn();
} catch (PDOException $e) {
}

// Today's Visitors
$today_visitors = 0;
try {
    $today = date('Y-m-d');
    $stmt = $db->prepare("SELECT unique_visitors FROM daily_stats WHERE stat_date = :today");
    $stmt->execute([':today' => $today]);
    $result = $stmt->fetch();
    $today_visitors = $result['unique_visitors'] ?? 0;
} catch (PDOException $e) {
}
?>

<div class="page-header">
    <h1 class="page-title">ภาพรวมระบบ (Dashboard)</h1>
    <p style="color: #666;">ยินดีต้อนรับเข้าสู่ระบบจัดการเว็บไซต์ Mida Leasing</p>
</div>

<!-- Stats Grid -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">

    <!-- ผู้สมัครสินเชื่อรอติดต่อ -->
    <a href="loan_applications.php" style="text-decoration: none;">
        <div class="card stat-card" style="cursor: pointer; transition: transform 0.2s;"
            onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='none'">
            <div class="stat-icon" style="background-color: #fff3e0; color: #f57c00;">
                <i class="fa-solid fa-file-signature"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo $pending_loans ?: 0; ?></h3>
                <p>ผู้สมัครสินเชื่อ (รอติดต่อ)</p>
            </div>
        </div>
    </a>

    <!-- ผู้สนใจทรัพย์สินรอติดต่อ -->
    <a href="property_inquiries.php" style="text-decoration: none;">
        <div class="card stat-card" style="cursor: pointer; transition: transform 0.2s;"
            onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='none'">
            <div class="stat-icon" style="background-color: #e8f5e9; color: #388e3c;">
                <i class="fa-solid fa-house-user"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo $pending_property_inquiries ?: 0; ?></h3>
                <p>สนใจทรัพย์สิน (รอติดต่อ)</p>
            </div>
        </div>
    </a>

    <!-- ทรัพย์สินรอขาย -->
    <a href="properties.php" style="text-decoration: none;">
        <div class="card stat-card" style="cursor: pointer; transition: transform 0.2s;"
            onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='none'">
            <div class="stat-icon" style="background-color: #e3f2fd; color: #1976d2;">
                <i class="fa-solid fa-building"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo $properties_count ?: 0; ?></h3>
                <p>ทรัพย์สินรอขาย</p>
            </div>
        </div>
    </a>

    <!-- รถประมูล -->
    <a href="auction_cars.php" style="text-decoration: none;">
        <div class="card stat-card" style="cursor: pointer; transition: transform 0.2s;"
            onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='none'">
            <div class="stat-icon" style="background-color: #fce4ec; color: #c2185b;">
                <i class="fa-solid fa-car"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo $auction_cars_count ?: 0; ?></h3>
                <p>รถประมูล</p>
            </div>
        </div>
    </a>

    <!-- ข้อความใหม่ -->
    <a href="contact_messages.php" style="text-decoration: none;">
        <div class="card stat-card" style="cursor: pointer; transition: transform 0.2s;"
            onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='none'">
            <div class="stat-icon" style="background-color: #ffebee; color: #d32f2f;">
                <i class="fa-solid fa-envelope"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo $unread_messages ?: 0; ?></h3>
                <p>ข้อความใหม่</p>
            </div>
        </div>
    </a>

    <!-- ผู้เข้าชมวันนี้ -->
    <a href="visitor_stats.php" style="text-decoration: none;">
        <div class="card stat-card" style="cursor: pointer; transition: transform 0.2s;"
            onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='none'">
            <div class="stat-icon" style="background-color: #f3e5f5; color: #7b1fa2;">
                <i class="fa-solid fa-eye"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo $today_visitors ?: 0; ?></h3>
                <p>ผู้เข้าชมวันนี้</p>
            </div>
        </div>
    </a>

</div>

<?php
// Include Footer
require_once 'includes/footer.php';
?>