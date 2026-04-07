<?php
/**
 * Migration: เพิ่ม is_featured column สำหรับรถเด่นประจำรอบ
 */
require_once 'config/db.php';
$database = new Database();
$db = $database->getConnection();

echo "<h2>Migration: Add is_featured to auction_cars</h2>";

try {
    // Check if column exists
    $stmt = $db->query("SHOW COLUMNS FROM auction_cars LIKE 'is_featured'");
    $exists = $stmt->fetch();

    if ($exists) {
        echo "<p style='color: orange;'>Column 'is_featured' already exists. Skipping.</p>";
    } else {
        // Add is_featured column
        $db->exec("ALTER TABLE auction_cars ADD COLUMN is_featured TINYINT(1) DEFAULT 0");
        echo "<p style='color: green;'>✓ Added 'is_featured' column to auction_cars</p>";
    }

    echo "<hr><p><b>Migration completed successfully!</b></p>";
    echo "<p><a href='auction_featured.php'>ไปหน้าจัดการรถเด่น</a></p>";

} catch (PDOException $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>