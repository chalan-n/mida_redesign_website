<?php
/**
 * Migration: สร้างตาราง share_buttons สำหรับจัดการปุ่มแชร์
 */
require_once 'config/db.php';
$database = new Database();
$db = $database->getConnection();

echo "<h2>Migration: Create share_buttons table</h2>";

try {
    // Create share_buttons table
    $db->exec("
        CREATE TABLE IF NOT EXISTS share_buttons (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(50) NOT NULL,
            icon VARCHAR(100) NOT NULL,
            color VARCHAR(20) NOT NULL,
            url_pattern VARCHAR(255) NOT NULL,
            is_active TINYINT(1) DEFAULT 1,
            sort_order INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    echo "<p style='color: green;'>✓ Created share_buttons table</p>";

    // Check if data exists
    $count = $db->query("SELECT COUNT(*) FROM share_buttons")->fetchColumn();

    if ($count == 0) {
        // Insert default share buttons
        $db->exec("
            INSERT INTO share_buttons (name, icon, color, url_pattern, is_active, sort_order) VALUES
            ('Facebook', 'fa-brands fa-facebook', '#3b5998', 'https://www.facebook.com/sharer/sharer.php?u={URL}', 1, 1),
            ('Line', 'fa-brands fa-line', '#06c755', 'https://social-plugins.line.me/lineit/share?url={URL}', 1, 2),
            ('Twitter/X', 'fa-brands fa-x-twitter', '#000000', 'https://x.com/intent/tweet?url={URL}&text={TITLE}', 1, 3),
            ('คัดลอกลิงก์', 'fa-solid fa-link', '#666666', 'copy', 1, 4)
        ");
        echo "<p style='color: green;'>✓ Inserted default share buttons</p>";
    } else {
        echo "<p style='color: orange;'>Share buttons already exist. Skipping insert.</p>";
    }

    echo "<hr><p><b>Migration completed successfully!</b></p>";
    echo "<p><a href='share_buttons.php'>ไปหน้าจัดการปุ่มแชร์</a></p>";

} catch (PDOException $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>