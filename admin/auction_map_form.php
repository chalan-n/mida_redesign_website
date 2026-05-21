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

function normalizeMapEmbedUrl($value)
{
    $value = trim($value);
    if ($value === '') {
        return '';
    }
    if (preg_match('/src=["\']([^"\']+)["\']/i', $value, $matches)) {
        return html_entity_decode($matches[1], ENT_QUOTES, 'UTF-8');
    }
    return $value;
}

ensureAuctionMapsTable($db);

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$map = array(
    'title' => '',
    'venue_name' => '',
    'address' => '',
    'map_embed_url' => '',
    'map_link' => '',
    'contact_note' => '',
    'sort_order' => 0,
    'is_active' => 1
);

if ($id > 0) {
    $stmt = $db->prepare("SELECT * FROM auction_maps WHERE id = ?");
    $stmt->execute(array($id));
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($existing) {
        $map = $existing;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title']);
    $venue_name = trim($_POST['venue_name']);
    $address = trim($_POST['address']);
    $map_embed_url = normalizeMapEmbedUrl($_POST['map_embed_url']);
    $map_link = trim($_POST['map_link']);
    $contact_note = trim($_POST['contact_note']);
    $sort_order = (int) $_POST['sort_order'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    if ($id > 0) {
        $sql = "UPDATE auction_maps SET title=?, venue_name=?, address=?, map_embed_url=?, map_link=?, contact_note=?, sort_order=?, is_active=? WHERE id=?";
        $stmt = $db->prepare($sql);
        $stmt->execute(array($title, $venue_name, $address, $map_embed_url, $map_link, $contact_note, $sort_order, $is_active, $id));
        echo "<script>window.location.href='auction_maps.php';</script>";
        exit;
    } else {
        $sql = "INSERT INTO auction_maps (title, venue_name, address, map_embed_url, map_link, contact_note, sort_order, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $db->prepare($sql);
        $stmt->execute(array($title, $venue_name, $address, $map_embed_url, $map_link, $contact_note, $sort_order, $is_active));
        echo "<script>window.location.href='auction_maps.php';</script>";
        exit;
    }
}

require_once 'includes/header.php';
?>

<div class="page-header">
    <h1 class="page-title"><?php echo $id > 0 ? 'แก้ไขแผนที่งานประมูล' : 'เพิ่มแผนที่งานประมูล'; ?></h1>
    <a href="auction_maps.php" style="color: #666; text-decoration: none; margin-top: 10px; display: inline-block;">
        <i class="fa-solid fa-arrow-left"></i> กลับไปหน้ารายการ
    </a>
</div>

<div class="card" style="max-width: 920px; margin: 0 auto;">
    <form method="POST">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 500;">หัวข้อ</label>
                <input type="text" name="title" value="<?php echo htmlspecialchars($map['title']); ?>" required
                    class="form-control" placeholder="เช่น แผนที่งานประมูล สาขานครปฐม"
                    style="padding: 10px; width: 100%; border: 1px solid #ddd; border-radius: 5px;">
            </div>
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 500;">ชื่อสถานที่ / สาขา</label>
                <input type="text" name="venue_name" value="<?php echo htmlspecialchars($map['venue_name']); ?>"
                    class="form-control" placeholder="เช่น สาขานครปฐม"
                    style="padding: 10px; width: 100%; border: 1px solid #ddd; border-radius: 5px;">
            </div>
        </div>

        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 500;">ที่อยู่</label>
            <textarea name="address" rows="3" class="form-control"
                placeholder="ใส่ที่อยู่หรือจุดสังเกตสำหรับลูกค้าที่ต้องการเดินทางไปงานประมูล"
                style="padding: 10px; width: 100%; border: 1px solid #ddd; border-radius: 5px;"><?php echo htmlspecialchars($map['address']); ?></textarea>
        </div>

        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 500;">Google Maps Embed URL หรือโค้ด iframe</label>
            <textarea name="map_embed_url" rows="3" class="form-control"
                placeholder="วางลิงก์ Embed หรือโค้ด iframe จาก Google Maps"
                style="padding: 10px; width: 100%; border: 1px solid #ddd; border-radius: 5px;"><?php echo htmlspecialchars($map['map_embed_url']); ?></textarea>
            <small style="color: #666;">ระบบจะดึงค่า src จาก iframe ให้อัตโนมัติ หากวางโค้ดมาทั้งชุด</small>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 500;">ลิงก์เปิดแผนที่</label>
                <input type="text" name="map_link" value="<?php echo htmlspecialchars($map['map_link']); ?>"
                    class="form-control" placeholder="https://maps.google.com/..."
                    style="padding: 10px; width: 100%; border: 1px solid #ddd; border-radius: 5px;">
            </div>
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 500;">ลำดับการแสดง</label>
                <input type="number" name="sort_order" value="<?php echo htmlspecialchars($map['sort_order']); ?>"
                    class="form-control"
                    style="padding: 10px; width: 100%; border: 1px solid #ddd; border-radius: 5px;">
            </div>
        </div>

        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 500;">หมายเหตุ / ช่องทางติดต่อ</label>
            <textarea name="contact_note" rows="3" class="form-control"
                placeholder="เช่น แนะนำให้มาถึงก่อนเริ่มประมูล 30 นาที หรือติดต่อเจ้าหน้าที่ประจำสาขา"
                style="padding: 10px; width: 100%; border: 1px solid #ddd; border-radius: 5px;"><?php echo htmlspecialchars($map['contact_note']); ?></textarea>
        </div>

        <div style="margin-bottom: 24px;">
            <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                <input type="checkbox" name="is_active" <?php echo $map['is_active'] ? 'checked' : ''; ?>>
                แสดงผลบนหน้าเว็บ
            </label>
        </div>

        <div style="display: flex; gap: 15px;">
            <button type="submit"
                style="background: var(--primary-blue); color: white; padding: 12px 30px; border: none; border-radius: 5px; cursor: pointer; font-size: 1rem;">
                <i class="fa-solid fa-save"></i> บันทึกข้อมูล
            </button>
            <a href="auction_maps.php"
                style="background: #eee; color: #333; padding: 12px 30px; border-radius: 5px; text-decoration: none; font-size: 1rem;">
                ยกเลิก
            </a>
        </div>
    </form>
</div>

<?php require_once 'includes/footer.php'; ?>
