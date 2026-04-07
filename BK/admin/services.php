<?php
session_start();
require_once 'config/db.php';

// Delete Logic
if (isset($_GET['delete_id'])) {
    $database = new Database();
    $db = $database->getConnection();

    $id = $_GET['delete_id'];

    $del_stmt = $db->prepare("DELETE FROM services WHERE id = ?");
    $del_stmt->execute([$id]);

    header("Location: services.php?msg=deleted");
    exit;
}

require_once 'includes/header.php';

$database = new Database();
$db = $database->getConnection();

// Fetch Services
$stmt = $db->query("SELECT * FROM services ORDER BY sort_order ASC");
$services = $stmt->fetchAll();
?>

<div class="page-header">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <h1 class="page-title">จัดการบริการ (Services)</h1>
        <a href="service_form.php" class="btn-primary"
            style="background: var(--primary-blue); color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">
            <i class="fa-solid fa-plus"></i> เพิ่มบริการใหม่
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
                <th style="padding: 15px; text-align: left;">ไอคอน</th>
                <th style="padding: 15px; text-align: left;">ชื่อบริการ</th>
                <th style="padding: 15px; text-align: left;">รายละเอียด</th>
                <th style="padding: 15px; text-align: center;">ลำดับ</th>
                <th style="padding: 15px; text-align: center;">สถานะ</th>
                <th style="padding: 15px; text-align: center;">จัดการ</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($services) > 0): ?>
                <?php foreach ($services as $service): ?>
                    <tr style="border-bottom: 1px solid #dee2e6;">
                        <td style="padding: 15px; text-align: center; font-size: 1.5rem; color: var(--primary-blue);">
                            <i class="<?php echo $service['icon_class']; ?>"></i>
                        </td>
                        <td style="padding: 15px;">
                            <strong>
                                <?php echo $service['title']; ?>
                            </strong>
                        </td>
                        <td style="padding: 15px; color: #666;">
                            <?php echo mb_substr($service['description'], 0, 50) . '...'; ?>
                        </td>
                        <td style="padding: 15px; text-align: center;">
                            <?php echo $service['sort_order']; ?>
                        </td>
                        <td style="padding: 15px; text-align: center;">
                            <?php if ($service['is_active']): ?>
                                <span
                                    style="background: #d4edda; color: #155724; padding: 5px 10px; border-radius: 20px; font-size: 0.8em;">แสดงผล</span>
                            <?php else: ?>
                                <span
                                    style="background: #f8d7da; color: #721c24; padding: 5px 10px; border-radius: 20px; font-size: 0.8em;">ซ่อน</span>
                            <?php endif; ?>
                        </td>
                        <td style="padding: 15px; text-align: center;">
                            <a href="service_form.php?id=<?php echo $service['id']; ?>"
                                style="color: #002D62; margin-right: 10px;" title="แก้ไข"><i
                                    class="fa-solid fa-pen-to-square"></i></a>
                            <a href="services.php?delete_id=<?php echo $service['id']; ?>" style="color: #d32f2f;" title="ลบ"
                                onclick="return confirm('ยืนยันการลบข้อมูลนี้?');"><i class="fa-solid fa-trash"></i></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" style="padding: 30px; text-align: center; color: #888;">
                        ยังไม่มีข้อมูลบริการ
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once 'includes/footer.php'; ?>