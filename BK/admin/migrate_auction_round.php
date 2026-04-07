<?php
/**
 * Migration: เพิ่ม schedule_id column สำหรับเชื่อมโยงรถกับรอบประมูล
 */
require_once 'config/db.php';
$database = new Database();
$db = $database->getConnection();

echo "<h2>Migration: Add schedule_id to auction_cars</h2>";

try {
    // Check if column exists
    $stmt = $db->query("SHOW COLUMNS FROM auction_cars LIKE 'schedule_id'");
    $exists = $stmt->fetch();

    if ($exists) {
        echo "<p style='color: orange;'>Column 'schedule_id' already exists. Skipping.</p>";
    } else {
        // Add schedule_id column
        $db->exec("ALTER TABLE auction_cars ADD COLUMN schedule_id INT NULL DEFAULT NULL");
        echo "<p style='color: green;'>✓ Added 'schedule_id' column to auction_cars</p>";
    }

    // Add index for faster queries
    try {
        $db->exec("ALTER TABLE auction_cars ADD INDEX idx_schedule_id (schedule_id)");
        echo "<p style='color: green;'>✓ Added index on schedule_id</p>";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate key name') !== false) {
            echo "<p style='color: orange;'>Index already exists. Skipping.</p>";
        } else {
            throw $e;
        }
    }

    echo "<hr><p><b>Migration completed successfully!</b></p>";
    echo "<p><a href='auction_round_manager.php'>ไปหน้าจัดการรอบประมูล</a></p>";

} catch (PDOException $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>