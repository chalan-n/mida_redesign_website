<?php
session_start();
require_once 'config/db.php';

// Delete Logic
if (isset($_GET['delete_id'])) {
    $database = new Database();
    $db = $database->getConnection();

    $id = $_GET['delete_id'];

    // Get image path to delete file
    $stmt = $db->prepare("SELECT image_path FROM banners WHERE id = ?");
    $stmt->execute([$id]);
    $banner = $stmt->fetch();

    if ($banner) {
        $file_path = "../" . $banner['image_path'];
        if (file_exists($file_path)) {
            unlink($file_path);
        }

        $del_stmt = $db->prepare("DELETE FROM banners WHERE id = ?");
        $del_stmt->execute([$id]);

        header("Location: banners.php?msg=deleted");
        exit;
    }
}

require_once 'includes/header.php';

$database = new Database();
$db = $database->getConnection();

$stmt = $db->query("SELECT * FROM banners ORDER BY sort_order ASC");
$banners = $stmt->fetchAll();
?>

<div class="page-header">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <h1 class="page-title">จัดการแบนเนอร์ (Banners)</h1>
        <a href="banner_form.php" class="btn-primary"
            style="background: var(--primary-blue); color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">
            <i class="fa-solid fa-plus"></i> เพิ่มแบนเนอร์ใหม่
        </a>
    </div>
</div>

<?php if (isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
    <div style="background: #d4edda; color: #155724; padding: 15px; margin-bottom: 20px; border-radius: 5px;">
        ลบข้อมูลเรียบร้อยแล้ว
    </div>
<?php endif; ?>

<div class="card">
    <table style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="background: #f8f9fa; border-bottom: 2px solid #dee2e6;">
                <th style="padding: 15px; text-align: left;">รูปภาพ</th>
                <th style="padding: 15px; text-align: left;">หัวข้อ (Title)</th>
                <th style="padding: 15px; text-align: center;">ลำดับ</th>
                <th style="padding: 15px; text-align: center;">สถานะ</th>
                <th style="padding: 15px; text-align: center;">จัดการ</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($banners) > 0): ?>
                <?php foreach ($banners as $banner): ?>
                    <tr style="border-bottom: 1px solid #dee2e6;">
                        <td style="padding: 15px;">
                            <img src="../<?php echo $banner['image_path']; ?>" alt="Banner"
                                style="height: 60px; object-fit: cover; border-radius: 4px;">
                        </td>
                        <td style="padding: 15px;">
                            <strong>
                                <?php echo $banner['title']; ?>
                            </strong><br>
                            <span style="color: #888; font-size: 0.9em;">
                                <?php echo $banner['subtitle']; ?>
                            </span>
                        </td>
                        <td style="padding: 15px; text-align: center;">
                            <?php echo $banner['sort_order']; ?>
                        </td>
                        <td style="padding: 15px; text-align: center;">
                            <?php if ($banner['is_active']): ?>
                                <span
                                    style="background: #d4edda; color: #155724; padding: 5px 10px; border-radius: 20px; font-size: 0.8em;">แสดงผล</span>
                            <?php else: ?>
                                <span
                                    style="background: #f8d7da; color: #721c24; padding: 5px 10px; border-radius: 20px; font-size: 0.8em;">ซ่อน</span>
                            <?php endif; ?>
                        </td>
                        <td style="padding: 15px; text-align: center;">
                            <a href="banner_form.php?id=<?php echo $banner['id']; ?>"
                                style="color: #002D62; margin-right: 10px;" title="แก้ไข"><i
                                    class="fa-solid fa-pen-to-square"></i></a>
                            <a href="banners.php?delete_id=<?php echo $banner['id']; ?>" style="color: #d32f2f;" title="ลบ"
                                onclick="return confirm('ยืนยันการลบข้อมูลนี้?');"><i class="fa-solid fa-trash"></i></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" style="padding: 30px; text-align: center; color: #888;">
                        ยังไม่มีข้อมูลแบนเนอร์
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once 'includes/footer.php'; ?>