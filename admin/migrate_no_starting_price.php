<?php
/**
 * Migration: เพิ่มคอลัมน์ no_starting_price สำหรับรถประมูล
 * รันไฟล์นี้ครั้งเดียว: http://localhost:8085/webmida_newdesign/admin/migrate_no_starting_price.php
 */

require_once 'config/db.php';

$database = new Database();
$db = $database->getConnection();

echo "<h2>🔧 Migration: เพิ่มคอลัมน์ no_starting_price</h2>";

try {
    // ตรวจสอบว่ามีคอลัมน์อยู่แล้วหรือไม่
    $check = $db->query("SHOW COLUMNS FROM auction_cars LIKE 'no_starting_price'");

    if ($check->rowCount() > 0) {
        echo "<p style='color: orange;'>⚠️ คอลัมน์ <strong>no_starting_price</strong> มีอยู่แล้ว ไม่ต้องทำอะไร</p>";
    } else {
        // สร้างคอลัมน์ใหม่
        $db->exec("ALTER TABLE auction_cars ADD COLUMN no_starting_price TINYINT(1) DEFAULT 0");
        echo "<p style='color: green;'>✅ สร้างคอลัมน์ <strong>no_starting_price</strong> สำเร็จ!</p>";
    }

    echo "<hr>";
    echo "<p>🎉 Migration เสร็จสมบูรณ์!</p>";
    echo "<p><a href='auction_car_form.php'>👉 ไปที่หน้าเพิ่ม/แก้ไขรถประมูล</a></p>";

} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ เกิดข้อผิดพลาด: " . $e->getMessage() . "</p>";
}
?>