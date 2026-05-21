<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

$database = new Database();
$db = $database->getConnection();

function ensureAuctionMapsTable($db)
{
    $db->exec("
        CREATE TABLE IF NOT EXISTS auction_maps (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            venue_name VARCHAR(255) DEFAULT NULL,
            address TEXT DEFAULT NULL,
            map_embed_url TEXT DEFAULT NULL,
            map_link VARCHAR(500) DEFAULT NULL,
            contact_note TEXT DEFAULT NULL,
            sort_order INT DEFAULT 0,
            is_active TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
}

ensureAuctionMapsTable($db);

if (isset($_GET['delete_id'])) {
    $id = (int) $_GET['delete_id'];
    $stmt = $db->prepare("DELETE FROM auction_maps WHERE id = ?");
    $stmt->execute(array($id));
    header("Location: auction_maps.php?msg=deleted");
    exit;
}

require_once 'includes/header.php';

$stmt = $db->query("SELECT * FROM auction_maps ORDER BY sort_order ASC, id DESC");
$maps = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="page-header">
    <div style="display: flex; justify-content: space-between; align-items: center; gap: 20px;">
        <div>
            <h1 class="page-title">จัดการแผนที่งานประมูล</h1>
            <p style="color: #666; margin: 8px 0 0;">เพิ่มจุดจัดงานประมูล ลิงก์นำทาง และแผนที่สำหรับแสดงบนหน้าเว็บประมูล</p>
        </div>
        <a href="auction_map_form.php"
            style="background: var(--primary-blue); color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; white-space: nowrap;">
            <i class="fa-solid fa-plus"></i> เพิ่มแผนที่
        </a>
    </div>
</div>

<?php if (isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
    <div style="background: #d4edda; color: #155724; padding: 15px; margin-bottom: 20px; border-radius: 5px;">
        ลบแผนที่งานประมูลเรียบร้อยแล้ว
    </div>
<?php endif; ?>

<div class="card">
    <table style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="background: #f8f9fa; border-bottom: 2px solid #dee2e6;">
                <th style="padding: 15px; text-align: left;">สถานที่</th>
                <th style="padding: 15px; text-align: left;">ที่อยู่ / หมายเหตุ</th>
                <th style="padding: 15px; text-align: center;">ลำดับ</th>
                <th style="padding: 15px; text-align: center;">สถานะ</th>
                <th style="padding: 15px; text-align: center;">จัดการ</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($maps) > 0): ?>
                <?php foreach ($maps as $map): ?>
                    <tr style="border-bottom: 1px solid #dee2e6;">
                        <td style="padding: 15px;">
                            <strong><?php echo htmlspecialchars($map['title']); ?></strong>
                            <?php if (!empty($map['venue_name'])): ?>
                                <div style="color: #002D62; font-size: 0.92rem; margin-top: 4px;">
                                    <?php echo htmlspecialchars($map['venue_name']); ?>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($map['map_link'])): ?>
                                <a href="<?php echo htmlspecialchars($map['map_link']); ?>" target="_blank" rel="noopener"
                                    style="display: inline-flex; align-items: center; gap: 6px; margin-top: 8px; color: #0d6efd; text-decoration: none;">
                                    <i class="fa-solid fa-up-right-from-square"></i> เปิดแผนที่
                                </a>
                            <?php endif; ?>
                        </td>
                        <td style="padding: 15px; color: #666;">
                            <?php if (!empty($map['address'])): ?>
                                <div><?php echo nl2br(htmlspecialchars($map['address'])); ?></div>
                            <?php endif; ?>
                            <?php if (!empty($map['contact_note'])): ?>
                                <div style="margin-top: 6px; color: #7a5b08;"><?php echo nl2br(htmlspecialchars($map['contact_note'])); ?></div>
                            <?php endif; ?>
                        </td>
                        <td style="padding: 15px; text-align: center;"><?php echo (int) $map['sort_order']; ?></td>
                        <td style="padding: 15px; text-align: center;">
                            <?php if ($map['is_active']): ?>
                                <span style="background: #d4edda; color: #155724; padding: 5px 10px; border-radius: 20px; font-size: 0.8em;">แสดงผล</span>
                            <?php else: ?>
                                <span style="background: #f8d7da; color: #721c24; padding: 5px 10px; border-radius: 20px; font-size: 0.8em;">ซ่อน</span>
                            <?php endif; ?>
                        </td>
                        <td style="padding: 15px; text-align: center;">
                            <a href="auction_map_form.php?id=<?php echo (int) $map['id']; ?>"
                                style="color: #002D62; margin-right: 10px;" title="แก้ไข">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </a>
                            <a href="auction_maps.php?delete_id=<?php echo (int) $map['id']; ?>" style="color: #d32f2f;"
                                title="ลบ" onclick="return confirm('ยืนยันการลบแผนที่นี้?');">
                                <i class="fa-solid fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" style="padding: 30px; text-align: center; color: #888;">
                        ยังไม่มีข้อมูลแผนที่งานประมูล
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once 'includes/footer.php'; ?>
