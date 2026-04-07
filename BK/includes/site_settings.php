<?php
/**
 * Site Settings Include File
 * ใช้สำหรับดึงข้อมูลการตั้งค่าเว็บไซต์จากฐานข้อมูล
 * รวมถึงโลโก้, favicon, และข้อมูลติดต่อต่างๆ
 */

// ป้องกันการ include ซ้ำ
if (!isset($site_settings_loaded)) {
    $site_settings_loaded = true;

    // ถ้ายังไม่มี database connection ให้สร้างใหม่
    if (!isset($db)) {
        require_once __DIR__ . '/../admin/config/db.php';
        $database = new Database();
        $db = $database->getConnection();
    }

    // ดึงข้อมูล settings ถ้ายังไม่มี
    if (!isset($settings) || empty($settings)) {
        try {
            $stmt = $db->query("SELECT * FROM settings WHERE id = 1");
            $settings = $stmt->fetch();
        } catch (PDOException $e) {
            $settings = [];
        }
    }

    // กำหนดค่า default สำหรับโลโก้และ favicon
    $site_logo = !empty($settings['site_logo']) ? $settings['site_logo'] : 'img/mida_logo_5.png';
    $site_favicon = !empty($settings['site_favicon']) ? $settings['site_favicon'] : 'favicon.ico';
}
?>