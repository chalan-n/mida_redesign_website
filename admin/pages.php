<?php
session_start();
require_once 'config/db.php';
require_once 'includes/header.php';

$database = new Database();
$db = $database->getConnection();

// Fetch Pages
$stmt = $db->query("SELECT * FROM pages ORDER BY title ASC");
$pages = $stmt->fetchAll();
?>

<div class="page-header">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <h1 class="page-title">จัดการเนื้อหาเว็บไซต์ (Content Pages)</h1>
        <!-- No Add button for now, as we only manage specific system pages -->
    </div>
</div>

<div class="card">
    <table style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="background: #f8f9fa; border-bottom: 2px solid #dee2e6;">
                <th style="padding: 15px; text-align: left;">ชื่อหน้า (Title)</th>
                <th style="padding: 15px; text-align: left;">Slug</th>
                <th style="padding: 15px; text-align: left;">แก้ไขล่าสุด</th>
                <th style="padding: 15px; text-align: center;">จัดการ</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($pages) > 0): ?>
                <?php foreach ($pages as $page): ?>
                    <tr style="border-bottom: 1px solid #dee2e6;">
                        <td style="padding: 15px;">
                            <strong>
                                <?php echo $page['title']; ?>
                            </strong>
                        </td>
                        <td style="padding: 15px; color: #666;">
                            <?php echo $page['slug']; ?>
                        </td>
                        <td style="padding: 15px; color: #666;">
                            <?php echo $page['updated_at']; ?>
                        </td>
                        <td style="padding: 15px; text-align: center;">
                            <a href="page_form.php?id=<?php echo $page['id']; ?>" style="color: #002D62; margin-right: 10px;"
                                title="แก้ไขเนื้อหา">
                                <i class="fa-solid fa-pen-to-square"></i> แก้ไข
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" style="padding: 30px; text-align: center; color: #888;">
                        ยังไม่มีข้อมูลหน้าเพจ
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once 'includes/footer.php'; ?>