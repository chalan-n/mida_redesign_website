<?php
/**
 * Migration: เพิ่ม column device_type และ province ในตาราง page_views
 */
require_once 'config/db.php';
$database = new Database();
$db = $database->getConnection();

echo "<h2>Migration: Add device_type and province columns</h2>";

try {
    // Add device_type column
    $db->exec("ALTER TABLE page_views ADD COLUMN IF NOT EXISTS device_type VARCHAR(20) DEFAULT 'unknown'");
    echo "<p style='color: green;'>✓ Added device_type column</p>";

    // Add province column
    $db->exec("ALTER TABLE page_views ADD COLUMN IF NOT EXISTS province VARCHAR(100) DEFAULT NULL");
    echo "<p style='color: green;'>✓ Added province column</p>";

    // Add city column
    $db->exec("ALTER TABLE page_views ADD COLUMN IF NOT EXISTS city VARCHAR(100) DEFAULT NULL");
    echo "<p style='color: green;'>✓ Added city column</p>";

    // Add index for device_type and province
    try {
        $db->exec("CREATE INDEX idx_device_type ON page_views (device_type)");
        echo "<p style='color: green;'>✓ Added index for device_type</p>";
    } catch (PDOException $e) {
        echo "<p style='color: orange;'>Index idx_device_type already exists</p>";
    }

    try {
        $db->exec("CREATE INDEX idx_province ON page_views (province)");
        echo "<p style='color: green;'>✓ Added index for province</p>";
    } catch (PDOException $e) {
        echo "<p style='color: orange;'>Index idx_province already exists</p>";
    }

    echo "<hr><p><b>Migration completed successfully!</b></p>";
    echo "<p><a href='visitor_stats.php'>ไปหน้าดูสถิติผู้เข้าชม</a></p>";

} catch (PDOException $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>