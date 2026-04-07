<?php
session_start();
require_once 'config/db.php';
require_once 'includes/header.php';

$database = new Database();
$db = $database->getConnection();

// Handle Delete
if (isset($_GET['delete_id'])) {
    $stmt = $db->prepare("DELETE FROM branches WHERE id = :id");
    $stmt->bindParam(':id', $_GET['delete_id']);
    if ($stmt->execute()) {
        echo "<script>alert('ลบข้อมูลเรียบร้อยแล้ว'); window.location.href='branches.php';</script>";
    }
}

// Fetch Branches
$sql = "SELECT * FROM branches ORDER BY 
        CASE 
            WHEN name LIKE '%สำนักงานใหญ่%' THEN 0
            WHEN region = 'กลาง' THEN 1 
            WHEN region = 'เหนือ' THEN 2 
            WHEN region = 'อีสาน' THEN 3 
            WHEN region = 'ตะวันออกเฉียงเหนือ' THEN 3 
            WHEN region = 'ใต้' THEN 4 
            ELSE 5 
        END, name ASC";
$stmt = $db->query($sql);
$branches = $stmt->fetchAll();
?>

<div class="page-header">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <h1 class="page-title">จัดการสาขาให้บริการ (Branches)</h1>
        <a href="branch_form.php" class="btn-add"
            style="background: var(--primary-blue); color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">
            <i class="fa-solid fa-plus"></i> เพิ่มสาขาใหม่
        </a>
    </div>
</div>

<div class="card">
    <table style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="background: #f8f9fa; border-bottom: 2px solid #dee2e6;">
                <th style="padding: 15px; text-align: left;">ชื่อสาขา</th>
                <th style="padding: 15px; text-align: left;">ภูมิภาค</th>
                <th style="padding: 15px; text-align: left;">ที่อยู่ / เบอร์โทร</th>
                <th style="padding: 15px; text-align: center;">จัดการ</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($branches) > 0): ?>
                <?php foreach ($branches as $b): ?>
                    <tr style="border-bottom: 1px solid #dee2e6;">
                        <td style="padding: 15px;">
                            <strong>
                                <?php echo $b['name']; ?>
                            </strong>
                        </td>
                        <td style="padding: 15px;">
                            <span style="background: #f0f0f0; padding: 2px 8px; border-radius: 4px; font-size: 0.9em;">
                                <?php echo $b['region']; ?>
                            </span>
                        </td>
                        <td style="padding: 15px; font-size: 0.9em; color: #555;">
                            <div>
                                <?php echo $b['address']; ?>
                            </div>
                            <div style="color: var(--primary-blue); margin-top: 5px;"><i class="fa-solid fa-phone"></i>
                                <?php echo $b['phone']; ?>
                            </div>
                        </td>
                        <td style="padding: 15px; text-align: center;">
                            <a href="branch_form.php?id=<?php echo $b['id']; ?>" style="color: #002D62; margin-right: 10px;"
                                title="แก้ไข">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </a>
                            <a href="branches.php?delete_id=<?php echo $b['id']; ?>" style="color: #d32f2f;" title="ลบ"
                                onclick="return confirm('ยืนยันการลบสาขานี้?');">
                                <i class="fa-solid fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" style="padding: 30px; text-align: center; color: #888;">
                        ยังไม่มีข้อมูลสาขา
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once 'includes/footer.php'; ?>