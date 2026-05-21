<?php
session_start();
require_once 'config/db.php';
require_once 'includes/header.php';

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

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$brochure = array(
    'title' => '',
    'round_label' => '',
    'description' => '',
    'image_path' => '',
    'registration_link' => 'https://auction.mida-leasing.com',
    'line_link' => '',
    'sort_order' => 0,
    'is_active' => 1
);

if ($id > 0) {
    $stmt = $db->prepare("SELECT * FROM auction_brochures WHERE id = ?");
    $stmt->execute(array($id));
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($existing) {
        $brochure = $existing;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title']);
    $round_label = trim($_POST['round_label']);
    $description = trim($_POST['description']);
    $registration_link = trim($_POST['registration_link']);
    $line_link = trim($_POST['line_link']);
    $sort_order = (int) $_POST['sort_order'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $image_path = $brochure['image_path'];

    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $upload_dir = '../uploads/auction_brochures/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file_ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed_ext = array('jpg', 'jpeg', 'png', 'webp');
        if (in_array($file_ext, $allowed_ext)) {
            $new_filename = 'auction_brochure_' . uniqid() . '.' . $file_ext;
            $target_file = $upload_dir . $new_filename;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                if (!empty($image_path) && file_exists("../" . $image_path)) {
                    unlink("../" . $image_path);
                }
                $image_path = 'uploads/auction_brochures/' . $new_filename;
            }
        }
    }

    if (empty($image_path)) {
        echo "<script>alert('กรุณาอัปโหลดรูปโบรชัวร์');</script>";
    } elseif ($id > 0) {
        $sql = "UPDATE auction_brochures SET title=?, round_label=?, description=?, image_path=?, registration_link=?, line_link=?, sort_order=?, is_active=? WHERE id=?";
        $stmt = $db->prepare($sql);
        $stmt->execute(array($title, $round_label, $description, $image_path, $registration_link, $line_link, $sort_order, $is_active, $id));
        echo "<script>window.location.href='auction_brochures.php';</script>";
        exit;
    } else {
        $sql = "INSERT INTO auction_brochures (title, round_label, description, image_path, registration_link, line_link, sort_order, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $db->prepare($sql);
        $stmt->execute(array($title, $round_label, $description, $image_path, $registration_link, $line_link, $sort_order, $is_active));
        echo "<script>window.location.href='auction_brochures.php';</script>";
        exit;
    }
}
?>

<div class="page-header">
    <h1 class="page-title"><?php echo $id > 0 ? 'แก้ไขโบรชัวร์ประมูล' : 'เพิ่มโบรชัวร์ประมูล'; ?></h1>
    <a href="auction_brochures.php" style="color: #666; text-decoration: none; margin-top: 10px; display: inline-block;">
        <i class="fa-solid fa-arrow-left"></i> กลับไปหน้ารายการ
    </a>
</div>

<div class="card" style="max-width: 900px; margin: 0 auto;">
    <form method="POST" enctype="multipart/form-data">
        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 500;">รูปโบรชัวร์ประมูล</label>
            <?php if (!empty($brochure['image_path'])): ?>
                <div style="margin-bottom: 10px;">
                    <img src="../<?php echo htmlspecialchars($brochure['image_path']); ?>"
                        style="max-width: 320px; width: 100%; height: auto; border-radius: 10px; border: 1px solid #ddd;">
                </div>
            <?php endif; ?>
            <input type="file" name="image" accept="image/*" class="form-control" <?php echo $id > 0 ? '' : 'required'; ?>
                style="padding: 10px; width: 100%; border: 1px solid #ddd; border-radius: 5px;">
            <small style="color: #666;">แนะนำเป็นรูปแนวตั้งแบบโบรชัวร์ เช่น JPG/PNG/WebP</small>
        </div>

        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 500;">หัวข้อ</label>
            <input type="text" name="title" value="<?php echo htmlspecialchars($brochure['title']); ?>" required
                class="form-control" placeholder="เช่น ประมูลรถสวยกับไมด้าลิสซิ่ง"
                style="padding: 10px; width: 100%; border: 1px solid #ddd; border-radius: 5px;">
        </div>

        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 500;">รอบประมูล / สาขา</label>
            <input type="text" name="round_label" value="<?php echo htmlspecialchars($brochure['round_label']); ?>"
                class="form-control" placeholder="เช่น สาขานครปฐม วันที่ 20 พฤษภาคม 2569"
                style="padding: 10px; width: 100%; border: 1px solid #ddd; border-radius: 5px;">
        </div>

        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 500;">คำอธิบายสั้น</label>
            <textarea name="description" rows="3" class="form-control"
                placeholder="ข้อความสั้น ๆ สำหรับแสดงข้างโบรชัวร์บนหน้าเว็บ"
                style="padding: 10px; width: 100%; border: 1px solid #ddd; border-radius: 5px;"><?php echo htmlspecialchars($brochure['description']); ?></textarea>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 500;">ลิงก์ลงทะเบียน</label>
                <input type="text" name="registration_link" value="<?php echo htmlspecialchars($brochure['registration_link']); ?>"
                    class="form-control" placeholder="https://auction.mida-leasing.com"
                    style="padding: 10px; width: 100%; border: 1px solid #ddd; border-radius: 5px;">
            </div>
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 500;">ลิงก์ LINE</label>
                <input type="text" name="line_link" value="<?php echo htmlspecialchars($brochure['line_link']); ?>"
                    class="form-control" placeholder="https://line.me/R/ti/p/@midaleasing"
                    style="padding: 10px; width: 100%; border: 1px solid #ddd; border-radius: 5px;">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 500;">ลำดับการแสดง</label>
                <input type="number" name="sort_order" value="<?php echo htmlspecialchars($brochure['sort_order']); ?>"
                    class="form-control"
                    style="padding: 10px; width: 100%; border: 1px solid #ddd; border-radius: 5px;">
            </div>
            <div style="margin-bottom: 20px; display: flex; align-items: end;">
                <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; padding-bottom: 10px;">
                    <input type="checkbox" name="is_active" <?php echo $brochure['is_active'] ? 'checked' : ''; ?>>
                    แสดงผลบนหน้าเว็บ
                </label>
            </div>
        </div>

        <div style="display: flex; gap: 15px;">
            <button type="submit"
                style="background: var(--primary-blue); color: white; padding: 12px 30px; border: none; border-radius: 5px; cursor: pointer; font-size: 1rem;">
                <i class="fa-solid fa-save"></i> บันทึกข้อมูล
            </button>
            <a href="auction_brochures.php"
                style="background: #eee; color: #333; padding: 12px 30px; border-radius: 5px; text-decoration: none; font-size: 1rem;">
                ยกเลิก
            </a>
        </div>
    </form>
</div>

<?php require_once 'includes/footer.php'; ?>
