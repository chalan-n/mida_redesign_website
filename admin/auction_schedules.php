<?php
session_start();
require_once 'config/db.php';
require_once 'includes/header.php';

$database = new Database();
$db = $database->getConnection();

// Handle Edit/Add logic here or simpler update active schedule
// For simplicity, let's list schedules and edit them.

// Handle Delete
if (isset($_GET['delete_id'])) {
    $stmt = $db->prepare("DELETE FROM auction_schedules WHERE id = :id");
    $stmt->bindParam(':id', $_GET['delete_id']);
    if ($stmt->execute()) {
        echo "<script>alert('ลบข้อมูลเรียบร้อยแล้ว'); window.location.href='auction_schedules.php';</script>";
    }
}

// Fetch Schedules with actual car count from database
$stmt = $db->query("
    SELECT s.*, 
           (SELECT COUNT(*) FROM auction_cars c WHERE c.schedule_id = s.id) as actual_car_count
    FROM auction_schedules s 
    ORDER BY s.id DESC
");
$schedules = $stmt->fetchAll();
?>

<div class="page-header">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <h1 class="page-title">จัดการตารางประมูล (Auction Schedules)</h1>
        <!-- We can use a simple JS prompt or a separate page for adding. Let's make a separate simple form page later or inline modal? 
             Actually, let's reuse a simple form approach. -->
        <a href="auction_schedule_form.php" class="btn-add"
            style="background: var(--primary-blue); color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">
            <i class="fa-solid fa-plus"></i> เพิ่มตารางประมูล
        </a>
    </div>
</div>

<div class="card">
    <table style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="background: #f8f9fa; border-bottom: 2px solid #dee2e6;">
                <th style="padding: 15px; text-align: left;">สาขา / สถานที่</th>
                <th style="padding: 15px; text-align: left;">วันที่ประมูล</th>
                <th style="padding: 15px; text-align: left;">เวลาลงทะเบียน / เริ่ม</th>
                <th style="padding: 15px; text-align: center;">จำนวนรถ</th>
                <th style="padding: 15px; text-align: center;">สถานะ</th>
                <th style="padding: 15px; text-align: center;">จัดการ</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($schedules) > 0): ?>
                <?php foreach ($schedules as $sch): ?>
                    <tr style="border-bottom: 1px solid #dee2e6;">
                        <td style="padding: 15px;">
                            <strong>
                                <?php echo $sch['branch_name']; ?>
                            </strong>
                        </td>
                        <td style="padding: 15px; color: #002D62;">
                            <i class="fa-regular fa-calendar"></i>
                            <?php echo $sch['auction_date']; ?>
                        </td>
                        <td style="padding: 15px; font-size: 0.9em; color: #555;">
                            <div>ลงทะเบียน:
                                <?php echo $sch['time_register']; ?>
                            </div>
                            <div>เริ่มประมูล:
                                <?php echo $sch['time_start']; ?>
                            </div>
                        </td>
                        <td style="padding: 15px; text-align: center;">
                            <strong><?php echo $sch['actual_car_count']; ?></strong> คัน
                        </td>
                        <td style="padding: 15px; text-align: center;">
                            <?php if ($sch['is_active']): ?>
                                <span
                                    style="background: #d4edda; color: #155724; padding: 3px 8px; border-radius: 10px; font-size: 0.8em;">Active</span>
                            <?php else: ?>
                                <span
                                    style="background: #eee; color: #666; padding: 3px 8px; border-radius: 10px; font-size: 0.8em;">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td style="padding: 15px; text-align: center;">
                            <a href="auction_round_manager.php?schedule_id=<?php echo $sch['id']; ?>"
                                style="color: #28a745; margin-right: 10px;" title="จัดการรถ">
                                <i class="fa-solid fa-car-side"></i>
                            </a>
                            <a href="auction_schedule_form.php?id=<?php echo $sch['id']; ?>"
                                style="color: #002D62; margin-right: 10px;" title="แก้ไข">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </a>
                            <a href="auction_schedules.php?delete_id=<?php echo $sch['id']; ?>" style="color: #d32f2f;"
                                title="ลบ" onclick="return confirm('ยืนยันการลบตารางนี้?');">
                                <i class="fa-solid fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" style="padding: 30px; text-align: center; color: #888;">
                        ยังไม่มีข้อมูลตารางประมูล
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once 'includes/footer.php'; ?>