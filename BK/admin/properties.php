<?php
session_start();
require_once 'config/db.php';
require_once 'includes/header.php';

$database = new Database();
$db = $database->getConnection();

// Handle Delete
if (isset($_GET['delete_id'])) {
    $stmt = $db->prepare("DELETE FROM properties WHERE id = :id");
    $stmt->bindParam(':id', $_GET['delete_id']);
    if ($stmt->execute()) {
        echo "<script>alert('ลบข้อมูลเรียบร้อยแล้ว'); window.location.href='properties.php';</script>";
    }
}

// Fetch Properties
$stmt = $db->query("SELECT * FROM properties ORDER BY id DESC");
$props = $stmt->fetchAll();
?>

<div class="page-header">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <h1 class="page-title">จัดการบ้าน คอนโด ที่ดิน (Properties)</h1>
        <a href="property_form.php" class="btn-add"
            style="background: var(--primary-blue); color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">
            <i class="fa-solid fa-plus"></i> เพิ่มบ้าน คอนโด ที่ดิน
        </a>
    </div>
</div>

<div class="card">
    <table style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="background: #f8f9fa; border-bottom: 2px solid #dee2e6;">
                <th style="padding: 15px; text-align: left;">รหัสทรัพย์</th>
                <th style="padding: 15px; text-align: left; width: 100px;">รูปภาพ</th>
                <th style="padding: 15px; text-align: left;">ชื่อทรัพย์สิน / ทำเล</th>
                <th style="padding: 15px; text-align: center;">ประเภท</th>
                <th style="padding: 15px; text-align: center;">ราคา</th>
                <th style="padding: 15px; text-align: center;">สถานะ</th>
                <th style="padding: 15px; text-align: center;">จัดการ</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($props) > 0): ?>
                <?php foreach ($props as $p): ?>
                    <tr style="border-bottom: 1px solid #dee2e6;">
                        <td style="padding: 15px;">
                            <span style="font-weight: 500; color: #555;">
                                <?php echo !empty($p['property_code']) ? htmlspecialchars($p['property_code']) : '-'; ?>
                            </span>
                        </td>
                        <td style="padding: 15px;">
                            <?php if (!empty($p['image_path'])): ?>
                                <img src="../<?php echo $p['image_path']; ?>"
                                    style="width: 80px; height: 60px; object-fit: cover; border-radius: 4px;">
                            <?php else: ?>
                                <div
                                    style="width: 80px; height: 60px; background: #eee; display: flex; align-items: center; justify-content: center; border-radius: 4px; color: #aaa;">
                                    <i class="fa-solid fa-image"></i>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td style="padding: 15px;">
                            <div style="font-weight: 600; color: #333;">
                                <?php echo $p['title']; ?>
                            </div>
                            <div style="font-size: 0.9em; color: #666;">
                                <i class="fa-solid fa-location-dot"></i>
                                <?php echo $p['location']; ?>
                            </div>
                            <div style="font-size: 0.85em; color: #888; margin-top: 3px;">
                                <?php if ($p['type'] !== 'ที่ดินเปล่า'): ?>
                                    <?php if ($p['bed']): ?><i class="fa-solid fa-bed"></i>
                                        <?php echo $p['bed']; ?>
                                    <?php endif; ?>
                                    <?php if ($p['bath']): ?><i class="fa-solid fa-bath"></i>
                                        <?php echo $p['bath']; ?>
                                    <?php endif; ?>
                                <?php endif; ?>
                                <?php if ($p['area'] || !empty($p['land_size'])): ?>
                                    <i
                                        class="fa-solid fa-<?php echo $p['type'] === 'ที่ดินเปล่า' ? 'mountain-sun' : 'maximize'; ?>"></i>
                                    <?php echo !empty($p['land_size']) ? $p['land_size'] : $p['area']; ?>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td style="padding: 15px; text-align: center;">
                            <span
                                style="background: #e3f2fd; color: #0d47a1; padding: 2px 8px; border-radius: 4px; font-size: 0.9em;">
                                <?php echo $p['type']; ?>
                            </span>
                        </td>
                        <td style="padding: 15px; text-align: center; color: var(--primary-blue); font-weight: 600;">
                            <?php echo $p['price']; ?>
                        </td>
                        <td style="padding: 15px; text-align: center;">
                            <?php if ($p['is_active']): ?>
                                <span
                                    style="background: #d4edda; color: #155724; padding: 3px 8px; border-radius: 10px; font-size: 0.8em;">Active</span>
                            <?php else: ?>
                                <span
                                    style="background: #eee; color: #666; padding: 3px 8px; border-radius: 10px; font-size: 0.8em;">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td style="padding: 15px; text-align: center;">
                            <a href="property_form.php?id=<?php echo $p['id']; ?>" style="color: #002D62; margin-right: 10px;"
                                title="แก้ไข">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </a>
                            <a href="properties.php?delete_id=<?php echo $p['id']; ?>" style="color: #d32f2f;" title="ลบ"
                                onclick="return confirm('ยืนยันการลบข้อมูลนี้?');">
                                <i class="fa-solid fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" style="padding: 30px; text-align: center; color: #888;">
                        ยังไม่มีข้อมูลทรัพย์สิน
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once 'includes/footer.php'; ?>