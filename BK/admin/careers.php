<?php
session_start();
require_once 'config/db.php';
require_once 'includes/header.php';

$database = new Database();
$db = $database->getConnection();

// Handle Delete
if (isset($_GET['delete_id'])) {
    $stmt = $db->prepare("DELETE FROM careers WHERE id = :id");
    $stmt->bindParam(':id', $_GET['delete_id']);
    if ($stmt->execute()) {
        echo "<script>alert('ลบข้อมูลเรียบร้อยแล้ว'); window.location.href='careers.php';</script>";
    }
}

// Fetch Careers
$stmt = $db->query("SELECT * FROM careers ORDER BY created_at DESC");
$careers = $stmt->fetchAll();
?>

<div class="page-header">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <h1 class="page-title">ร่วมงานกับเรา (Careers)</h1>
        <a href="career_form.php" class="btn-add"
            style="background: var(--primary-blue); color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">
            <i class="fa-solid fa-plus"></i> เพิ่มตำแหน่งงาน
        </a>
    </div>
</div>

<div class="card">
    <table style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="background: #f8f9fa; border-bottom: 2px solid #dee2e6;">
                <th style="padding: 15px; text-align: left;">ตำแหน่งงาน</th>
                <th style="padding: 15px; text-align: left;">สถานที่ปฏิบัติงาน</th>
                <th style="padding: 15px; text-align: left;">เงินเดือน</th>
                <th style="padding: 15px; text-align: center;">จำนวนรับ</th>
                <th style="padding: 15px; text-align: center;">สถานะ</th>
                <th style="padding: 15px; text-align: center;">จัดการ</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($careers) > 0): ?>
                <?php foreach ($careers as $c): ?>
                    <tr style="border-bottom: 1px solid #dee2e6;">
                        <td style="padding: 15px;">
                            <strong>
                                <?php echo $c['position']; ?>
                            </strong>
                            <div style="font-size: 0.85em; color: #888; margin-top: 5px;">
                                <i class="fa-regular fa-calendar"></i>
                                <?php echo date('d/m/Y', strtotime($c['created_at'])); ?>
                            </div>
                        </td>
                        <td style="padding: 15px;">
                            <i class="fa-solid fa-map-pin" style="color: var(--accent-gold);"></i>
                            <?php echo $c['location']; ?>
                        </td>
                        <td style="padding: 15px; font-weight: 600; color: var(--primary-blue);">
                            <?php echo $c['salary']; ?>
                        </td>
                        <td style="padding: 15px; text-align: center;">
                            <?php echo $c['quantity']; ?>
                        </td>
                        <td style="padding: 15px; text-align: center;">
                            <?php if ($c['is_active']): ?>
                                <span
                                    style="background: #d4edda; color: #155724; padding: 3px 8px; border-radius: 10px; font-size: 0.8em;">เปิดรับ</span>
                            <?php else: ?>
                                <span
                                    style="background: #f8d7da; color: #721c24; padding: 3px 8px; border-radius: 10px; font-size: 0.8em;">ปิดรับ</span>
                            <?php endif; ?>
                        </td>
                        <td style="padding: 15px; text-align: center;">
                            <a href="career_form.php?id=<?php echo $c['id']; ?>" style="color: #002D62; margin-right: 10px;"
                                title="แก้ไข">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </a>
                            <a href="careers.php?delete_id=<?php echo $c['id']; ?>" style="color: #d32f2f;" title="ลบ"
                                onclick="return confirm('ยืนยันการลบตำแหน่งงานนี้?');">
                                <i class="fa-solid fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" style="padding: 30px; text-align: center; color: #888;">
                        ยังไม่มีตำแหน่งงานว่าง
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once 'includes/footer.php'; ?>