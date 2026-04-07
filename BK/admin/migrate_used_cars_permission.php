<?php
/**
 * Migration: เพิ่มสิทธิ์ used_cars (รถสวยพร้อมขาย) ลงในระบบ
 * รันไฟล์นี้ครั้งเดียวเพื่อเพิ่มสิทธิ์
 */

session_start();
require_once 'config/db.php';

$database = new Database();
$db = $database->getConnection();

echo "<h2>Migration: เพิ่มสิทธิ์ used_cars (รถสวยพร้อมขาย)</h2>";

try {
    // ตรวจสอบว่ามี permissions สำหรับ used_cars หรือยัง
    $checkStmt = $db->prepare("SELECT id FROM admin_permissions WHERE module = 'used_cars' LIMIT 1");
    $checkStmt->execute();

    if ($checkStmt->fetch()) {
        echo "<p style='color: orange;'>⚠️ สิทธิ์ used_cars มีอยู่ในระบบแล้ว ไม่ต้องเพิ่มใหม่</p>";
    } else {
        // หา menu_order สูงสุดเพื่อเพิ่มต่อท้าย
        $maxOrderStmt = $db->query("SELECT MAX(menu_order) as max_order FROM admin_permissions");
        $maxOrder = $maxOrderStmt->fetch()['max_order'] ?? 0;
        $nextOrder = $maxOrder + 1;

        // เพิ่ม permissions สำหรับ used_cars (view, create, update, delete)
        $permissions = [
            ['used_cars', 'view', 'ดูรถสวยพร้อมขาย', 'fa-car-rear', $nextOrder],
            ['used_cars', 'create', 'เพิ่มรถสวยพร้อมขาย', null, $nextOrder + 1],
            ['used_cars', 'update', 'แก้ไขรถสวยพร้อมขาย', null, $nextOrder + 2],
            ['used_cars', 'delete', 'ลบรถสวยพร้อมขาย', null, $nextOrder + 3],
        ];

        $insertStmt = $db->prepare("INSERT INTO admin_permissions (module, action, label, menu_icon, menu_order) VALUES (?, ?, ?, ?, ?)");

        foreach ($permissions as $perm) {
            $insertStmt->execute($perm);
            echo "<p style='color: green;'>✅ เพิ่มสิทธิ์: {$perm[2]} ({$perm[0]}.{$perm[1]})</p>";
        }

        echo "<br><p style='color: blue; font-weight: bold;'>✅ Migration สำเร็จ! กรุณาไปที่หน้า 'กลุ่มผู้ใช้' เพื่อกำหนดสิทธิ์ให้กับ Role ที่ต้องการ</p>";
    }

    echo "<br><a href='roles.php' style='padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px;'>ไปหน้ากลุ่มผู้ใช้</a>";
    echo " <a href='index.php' style='padding: 10px 20px; background: #6c757d; color: white; text-decoration: none; border-radius: 5px;'>กลับหน้าหลัก</a>";

} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ เกิดข้อผิดพลาด: " . $e->getMessage() . "</p>";
}
?>