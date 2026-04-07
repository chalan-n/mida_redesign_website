<?php
/**
 * Migration: เพิ่มคอลัมน์ cc (ขนาดเครื่องยนต์) สำหรับรถประมูล
 * รันไฟล์นี้ครั้งเดียว: http://localhost:8085/webmida_newdesign/admin/migrate_cc_field.php
 */

require_once 'config/db.php';

$database = new Database();
$db = $database->getConnection();

echo "<h2>🔧 Migration: เพิ่มคอลัมน์ cc (ขนาดเครื่องยนต์)</h2>";

try {
    // ตรวจสอบว่ามีคอลัมน์อยู่แล้วหรือไม่
    $check = $db->query("SHOW COLUMNS FROM auction_cars LIKE 'cc'");

    if ($check->rowCount() > 0) {
        echo "<p style='color: orange;'>⚠️ คอลัมน์ <strong>cc</strong> มีอยู่แล้ว ไม่ต้องทำอะไร</p>";
    } else {
        // สร้างคอลัมน์ใหม่
        $db->exec("ALTER TABLE auction_cars ADD COLUMN cc VARCHAR(50) AFTER mileage");
        echo "<p style='color: green;'>✅ สร้างคอลัมน์ <strong>cc</strong> สำเร็จ!</p>";
    }

    echo "<hr>";
    echo "<p>🎉 Migration เสร็จสมบูรณ์!</p>";
    echo "<p><a href='auction_car_form.php'>👉 ไปที่หน้าเพิ่ม/แก้ไขรถประมูล</a></p>";

} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ เกิดข้อผิดพลาด: " . $e->getMessage() . "</p>";
}
?>