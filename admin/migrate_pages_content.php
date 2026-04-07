<?php
/**
 * Migration: เปลี่ยน column content ในตาราง pages เป็น LONGTEXT
 * เพื่อรองรับเนื้อหาขนาดใหญ่ (สูงสุด 4GB)
 * 
 * วิธีใช้: เปิดหน้านี้ผ่าน browser 1 ครั้ง
 */

require_once 'config/db.php';

$database = new Database();
$db = $database->getConnection();

$success = false;
$message = '';

try {
    // เปลี่ยน column content เป็น LONGTEXT
    $sql = "ALTER TABLE pages MODIFY COLUMN content LONGTEXT";
    $db->exec($sql);

    $success = true;
    $message = 'อัปเดตตาราง pages สำเร็จ! column "content" เปลี่ยนเป็น LONGTEXT แล้ว';

} catch (PDOException $e) {
    $message = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>Migration: Update Pages Table</title>
    <style>
        body {
            font-family: 'Prompt', sans-serif;
            padding: 50px;
            background: #f4f6f9;
        }

        .box {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .success {
            color: #2e7d32;
            background: #e8f5e9;
            padding: 15px;
            border-radius: 5px;
        }

        .error {
            color: #c62828;
            background: #ffebee;
            padding: 15px;
            border-radius: 5px;
        }

        h1 {
            color: #333;
        }

        a {
            color: #002D62;
        }
    </style>
</head>

<body>
    <div class="box">
        <h1>📦 Migration: Update Pages Table</h1>

        <?php if ($success): ?>
            <div class="success">
                ✅
                <?php echo $message; ?>
            </div>
            <p style="margin-top: 20px;">
                <strong>สิ่งที่เปลี่ยนแปลง:</strong><br>
                - Column <code>content</code> เปลี่ยนจาก TEXT เป็น LONGTEXT<br>
                - รองรับข้อมูลได้สูงสุด 4GB
            </p>
        <?php else: ?>
            <div class="error">
                ❌
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <p style="margin-top: 30px;">
            <a href="pages.php">← กลับไปหน้าจัดการเนื้อหา</a>
        </p>
    </div>
</body>

</html>