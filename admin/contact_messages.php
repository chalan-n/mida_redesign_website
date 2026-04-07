<?php
session_start();
require_once 'config/db.php';
require_once 'includes/header.php';

$database = new Database();
$db = $database->getConnection();

// Handle Mark as Read
if (isset($_GET['read_id'])) {
    $stmt = $db->prepare("UPDATE contact_messages SET is_read = 1 WHERE id = :id");
    $stmt->bindParam(':id', $_GET['read_id']);
    $stmt->execute();
    echo "<script>window.location.href='contact_messages.php';</script>";
}

// Handle Delete
if (isset($_GET['delete_id'])) {
    $stmt = $db->prepare("DELETE FROM contact_messages WHERE id = :id");
    $stmt->bindParam(':id', $_GET['delete_id']);
    if ($stmt->execute()) {
        echo "<script>alert('ลบข้อความเรียบร้อยแล้ว'); window.location.href='contact_messages.php';</script>";
    }
}

// Fetch Messages
$stmt = $db->query("SELECT * FROM contact_messages ORDER BY created_at DESC");
$msgs = $stmt->fetchAll();
?>

<div class="page-header">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <h1 class="page-title">ข้อความจากผู้ติดต่อ (Contact Messages)</h1>
    </div>
</div>

<div class="card">
    <table style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="background: #f8f9fa; border-bottom: 2px solid #dee2e6;">
                <th style="padding: 15px; text-align: left; width: 200px;">ผู้ส่ง / เบอร์โทร</th>
                <th style="padding: 15px; text-align: left;">เรื่อง / ข้อความ</th>
                <th style="padding: 15px; text-align: center; width: 150px;">วันที่</th>
                <th style="padding: 15px; text-align: center; width: 100px;">จัดการ</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($msgs) > 0): ?>
                <?php foreach ($msgs as $m): ?>
                    <tr
                        style="border-bottom: 1px solid #dee2e6; <?php echo $m['is_read'] == 0 ? 'background-color: #f0f7ff;' : ''; ?>">
                        <td style="padding: 15px; vertical-align: top;">
                            <div style="font-weight: 600;">
                                <?php echo $m['name']; ?>
                            </div>
                            <div style="color: var(--primary-blue); font-size: 0.9em; margin-top: 5px;">
                                <i class="fa-solid fa-phone"></i>
                                <?php echo $m['phone']; ?>
                            </div>
                        </td>
                        <td style="padding: 15px; vertical-align: top;">
                            <div style="font-weight: 600; margin-bottom: 5px; color: #333;">
                                <?php echo $m['subject']; ?>
                            </div>
                            <div
                                style="color: #666; font-size: 0.95em; line-height: 1.5; white-space: pre-line; word-break: break-word;">
                                <?php echo htmlspecialchars($m['message']); ?>
                            </div>
                        </td>
                        <td style="padding: 15px; text-align: center; vertical-align: top; color: #888; font-size: 0.9em;">
                            <?php echo date('d/m/Y H:i', strtotime($m['created_at'])); ?> น.
                        </td>
                        <td style="padding: 15px; text-align: center; vertical-align: top;">
                            <?php if ($m['is_read'] == 0): ?>
                                <a href="contact_messages.php?read_id=<?php echo $m['id']; ?>"
                                    style="color: #28a745; margin-right: 10px;" title="ทำเครื่องหมายว่าอ่านแล้ว">
                                    <i class="fa-solid fa-check"></i>
                                </a>
                            <?php endif; ?>
                            <a href="contact_messages.php?delete_id=<?php echo $m['id']; ?>" style="color: #d32f2f;" title="ลบ"
                                onclick="return confirm('ยืนยันการลบ?');">
                                <i class="fa-solid fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" style="padding: 30px; text-align: center; color: #888;">
                        ไม่มีข้อความใหม่
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once 'includes/footer.php'; ?>