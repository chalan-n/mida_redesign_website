<?php
session_start();
require_once 'config/db.php';
require_once 'includes/header.php';

$database = new Database();
$db = $database->getConnection();

// Handle Delete
if (isset($_GET['delete_id'])) {
    $stmt = $db->prepare("DELETE FROM announcements WHERE id = :id");
    $stmt->bindParam(':id', $_GET['delete_id']);
    if ($stmt->execute()) {
        echo "<script>alert('ลบข้อมูลเรียบร้อยแล้ว'); window.location.href='announcements.php';</script>";
    }
}

// Handle Toggle Active
if (isset($_GET['toggle_active'])) {
    $id = $_GET['toggle_active'];
    $stmt = $db->prepare("UPDATE announcements SET is_active = NOT is_active WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    echo "<script>window.location.href='announcements.php';</script>";
}

// Handle Toggle Popup
if (isset($_GET['toggle_popup'])) {
    $id = $_GET['toggle_popup'];
    // Optional: If you want only ONE popup active at a time, reset others here
    // $db->query("UPDATE announcements SET is_popup = 0"); 

    $stmt = $db->prepare("UPDATE announcements SET is_popup = NOT is_popup WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    echo "<script>window.location.href='announcements.php';</script>";
}

// Fetch Items
$stmt = $db->query("SELECT * FROM announcements ORDER BY created_at DESC");
$announcements = $stmt->fetchAll();
?>

<div class="page-header">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <h1 class="page-title">จัดการข่าวประชาสัมพันธ์ (Announcements)</h1>
        <a href="announcement_form.php" class="btn btn-primary">
            <i class="fa-solid fa-plus-circle"></i> เพิ่มข่าวใหม่
        </a>
    </div>
</div>

<div class="card">
    <table style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="background: #f8f9fa; border-bottom: 2px solid #dee2e6;">
                <th style="padding: 15px; text-align: left; width: 80px;">รูปภาพ</th>
                <th style="padding: 15px; text-align: left;">หัวข้อ</th>
                <th style="padding: 15px; text-align: center;">หมวดหมู่</th>
                <th style="padding: 15px; text-align: center;">วันที่</th>
                <th style="padding: 15px; text-align: center;">สถานะ</th>
                <th style="padding: 15px; text-align: center;">Popup</th>
                <th style="padding: 15px; text-align: center;">จัดการ</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($announcements) > 0): ?>
                <?php foreach ($announcements as $item): ?>
                    <tr style="border-bottom: 1px solid #dee2e6;">
                        <td style="padding: 15px;">
                            <?php if (!empty($item['cover_image'])): ?>
                                <img src="../<?php echo $item['cover_image']; ?>" alt="img"
                                    style="width: 60px; height: 40px; object-fit: cover; border-radius: 4px;">
                            <?php else: ?>
                                <span style="color: #ccc;"><i class="fa-solid fa-image"></i></span>
                            <?php endif; ?>
                        </td>
                        <td style="padding: 15px;">
                            <strong>
                                <?php echo $item['title']; ?>
                            </strong>
                        </td>
                        <td style="padding: 15px; text-align: center;">
                            <span
                                style="background: #e3f2fd; color: #1565c0; padding: 2px 8px; border-radius: 4px; font-size: 0.85em;">
                                <?php echo $item['category']; ?>
                            </span>
                        </td>
                        <td style="padding: 15px; text-align: center; font-size: 0.9em; color: #666;">
                            <?php
                            echo ($item['start_date']) ? $item['start_date'] : '-';
                            echo " ถึง ";
                            echo ($item['end_date']) ? $item['end_date'] : '-';
                            ?>
                        </td>
                        <td style="padding: 15px; text-align: center;">
                            <a href="announcements.php?toggle_active=<?php echo $item['id']; ?>"
                                style="text-decoration: none; color: <?php echo $item['is_active'] ? '#2e7d32' : '#c62828'; ?>;">
                                <i class="fa-solid <?php echo $item['is_active'] ? 'fa-toggle-on' : 'fa-toggle-off'; ?>"
                                    style="font-size: 1.2em;"></i>
                                <br><span style="font-size: 0.8em;">
                                    <?php echo $item['is_active'] ? 'แสดง' : 'ซ่อน'; ?>
                                </span>
                            </a>
                        </td>
                        <td style="padding: 15px; text-align: center;">
                            <a href="announcements.php?toggle_popup=<?php echo $item['id']; ?>"
                                style="text-decoration: none; color: <?php echo $item['is_popup'] ? '#f57f17' : '#bdbdbd'; ?>;">
                                <i class="fa-solid <?php echo $item['is_popup'] ? 'fa-star' : 'fa-star'; ?>"
                                    style="font-size: 1.2em;"></i>
                            </a>
                        </td>
                        <td style="padding: 15px; text-align: center;">
                            <a href="announcement_form.php?id=<?php echo $item['id']; ?>"
                                style="color: #1976d2; margin-right: 10px;" title="แก้ไข">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </a>
                            <a href="announcements.php?delete_id=<?php echo $item['id']; ?>" style="color: #d32f2f;" title="ลบ"
                                onclick="return confirm('ยืนยันการลบ?');">
                                <i class="fa-solid fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" style="padding: 30px; text-align: center; color: #888;">
                        ยังไม่มีข้อมูลข่าวสาร
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once 'includes/footer.php'; ?>