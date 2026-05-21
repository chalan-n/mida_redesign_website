<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

$database = new Database();
$db = $database->getConnection();

function ensureAuctionBrochuresTable($db)
{
    $db->exec("
        CREATE TABLE IF NOT EXISTS auction_brochures (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            round_label VARCHAR(255) DEFAULT NULL,
            description TEXT DEFAULT NULL,
            image_path VARCHAR(255) NOT NULL,
            registration_link VARCHAR(255) DEFAULT NULL,
            line_link VARCHAR(255) DEFAULT NULL,
            sort_order INT DEFAULT 0,
            is_active TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
}

ensureAuctionBrochuresTable($db);

if (isset($_GET['delete_id'])) {
    $id = (int) $_GET['delete_id'];
    $stmt = $db->prepare("SELECT image_path FROM auction_brochures WHERE id = ?");
    $stmt->execute(array($id));
    $brochure = $stmt->fetch();

    if ($brochure) {
        $file_path = "../" . $brochure['image_path'];
        if (file_exists($file_path)) {
            unlink($file_path);
        }

        $del_stmt = $db->prepare("DELETE FROM auction_brochures WHERE id = ?");
        $del_stmt->execute(array($id));
        header("Location: auction_brochures.php?msg=deleted");
        exit;
    }
}

require_once 'includes/header.php';

$stmt = $db->query("SELECT * FROM auction_brochures ORDER BY sort_order ASC, id DESC");
$brochures = $stmt->fetchAll();
?>

<div class="page-header">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h1 class="page-title">จัดการโบรชัวร์ประมูล</h1>
            <p style="color: #666; margin: 8px 0 0;">รูปโบรชัวร์ที่แสดงบนหน้าเว็บประมูลรถยนต์</p>
        </div>
        <a href="auction_brochure_form.php" class="btn-add"
            style="background: var(--primary-blue); color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">
            <i class="fa-solid fa-plus"></i> เพิ่มโบรชัวร์
        </a>
    </div>
</div>

<?php if (isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
    <div style="background: #d4edda; color: #155724; padding: 15px; margin-bottom: 20px; border-radius: 5px;">
        ลบโบรชัวร์เรียบร้อยแล้ว
    </div>
<?php endif; ?>

<div class="card">
    <table style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="background: #f8f9fa; border-bottom: 2px solid #dee2e6;">
                <th style="padding: 15px; text-align: left;">รูปโบรชัวร์</th>
                <th style="padding: 15px; text-align: left;">ข้อมูลรอบประมูล</th>
                <th style="padding: 15px; text-align: center;">ลำดับ</th>
                <th style="padding: 15px; text-align: center;">สถานะ</th>
                <th style="padding: 15px; text-align: center;">จัดการ</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($brochures) > 0): ?>
                <?php foreach ($brochures as $brochure): ?>
                    <tr style="border-bottom: 1px solid #dee2e6;">
                        <td style="padding: 15px; width: 170px;">
                            <img src="../<?php echo htmlspecialchars($brochure['image_path']); ?>" alt="Auction brochure"
                                style="width: 130px; height: 90px; object-fit: cover; border-radius: 8px; border: 1px solid #e5e7eb;">
                        </td>
                        <td style="padding: 15px;">
                            <strong><?php echo htmlspecialchars($brochure['title']); ?></strong>
                            <?php if (!empty($brochure['round_label'])): ?>
                                <div style="color: #002D62; font-size: 0.92rem; margin-top: 4px;">
                                    <?php echo htmlspecialchars($brochure['round_label']); ?>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($brochure['description'])): ?>
                                <div style="color: #777; font-size: 0.9rem; margin-top: 4px;">
                                    <?php echo htmlspecialchars($brochure['description']); ?>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td style="padding: 15px; text-align: center;"><?php echo (int) $brochure['sort_order']; ?></td>
                        <td style="padding: 15px; text-align: center;">
                            <?php if ($brochure['is_active']): ?>
                                <span style="background: #d4edda; color: #155724; padding: 5px 10px; border-radius: 20px; font-size: 0.8em;">แสดงผล</span>
                            <?php else: ?>
                                <span style="background: #f8d7da; color: #721c24; padding: 5px 10px; border-radius: 20px; font-size: 0.8em;">ซ่อน</span>
                            <?php endif; ?>
                        </td>
                        <td style="padding: 15px; text-align: center;">
                            <a href="auction_brochure_form.php?id=<?php echo (int) $brochure['id']; ?>"
                                style="color: #002D62; margin-right: 10px;" title="แก้ไข">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </a>
                            <a href="auction_brochures.php?delete_id=<?php echo (int) $brochure['id']; ?>" style="color: #d32f2f;"
                                title="ลบ" onclick="return confirm('ยืนยันการลบโบรชัวร์นี้?');">
                                <i class="fa-solid fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" style="padding: 30px; text-align: center; color: #888;">
                        ยังไม่มีโบรชัวร์ประมูล
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once 'includes/footer.php'; ?>
