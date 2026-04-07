<?php
/**
 * แก้ไข icon Twitter/X ให้ใช้ fa-brands fa-twitter แทน fa-brands fa-x-twitter
 */
require_once 'config/db.php';
$database = new Database();
$db = $database->getConnection();

try {
    $stmt = $db->prepare("UPDATE share_buttons SET icon = 'fa-brands fa-twitter' WHERE icon = 'fa-brands fa-x-twitter'");
    $stmt->execute();

    $affected = $stmt->rowCount();
    echo "<h2>แก้ไขไอคอน Twitter สำเร็จ</h2>";
    echo "<p>แก้ไข $affected รายการ</p>";
    echo "<p><a href='share_buttons.php'>กลับหน้าจัดการปุ่มแชร์</a></p>";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>