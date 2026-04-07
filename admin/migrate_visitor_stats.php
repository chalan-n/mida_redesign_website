<?php
/**
 * Migration: สร้างตารางสำหรับเก็บสถิติผู้เข้าชม
 */
require_once 'config/db.php';
$database = new Database();
$db = $database->getConnection();

echo "<h2>Migration: Create visitor statistics tables</h2>";

try {
    // Create page_views table - เก็บทุก page view
    $db->exec("
        CREATE TABLE IF NOT EXISTS page_views (
            id INT AUTO_INCREMENT PRIMARY KEY,
            page_url VARCHAR(500) NOT NULL,
            page_title VARCHAR(255) DEFAULT NULL,
            ip_address VARCHAR(45) NOT NULL,
            user_agent TEXT,
            referrer VARCHAR(500) DEFAULT NULL,
            session_id VARCHAR(100) DEFAULT NULL,
            visited_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_visited_at (visited_at),
            INDEX idx_page_url (page_url(191)),
            INDEX idx_session_id (session_id)
        )
    ");
    echo "<p style='color: green;'>✓ Created page_views table</p>";

    // Create daily_stats table - สรุปสถิติรายวัน
    $db->exec("
        CREATE TABLE IF NOT EXISTS daily_stats (
            id INT AUTO_INCREMENT PRIMARY KEY,
            stat_date DATE NOT NULL UNIQUE,
            total_views INT DEFAULT 0,
            unique_visitors INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_stat_date (stat_date)
        )
    ");
    echo "<p style='color: green;'>✓ Created daily_stats table</p>";

    echo "<hr><p><b>Migration completed successfully!</b></p>";
    echo "<p><a href='visitor_stats.php'>ไปหน้าดูสถิติผู้เข้าชม</a></p>";

} catch (PDOException $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>