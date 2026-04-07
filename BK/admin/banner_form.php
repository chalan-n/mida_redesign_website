<?php
session_start();
require_once 'config/db.php';
require_once 'includes/header.php';

$database = new Database();
$db = $database->getConnection();

$id = isset($_GET['id']) ? $_GET['id'] : null;
$banner = null;

// Fetch existing data if editing
if ($id) {
    $stmt = $db->prepare("SELECT * FROM banners WHERE id = ?");
    $stmt->execute([$id]);
    $banner = $stmt->fetch();
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $subtitle = $_POST['subtitle'];
    $link = $_POST['link'];
    $sort_order = $_POST['sort_order'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    // Image Upload
    $image_path = $banner ? $banner['image_path'] : ''; // Default to old image

    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $upload_dir = '../assets/img/banners/';

        // Create dir if not exists
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file_ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $new_filename = uniqid() . '.' . $file_ext;
        $target_file = $upload_dir . $new_filename;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            $image_path = 'assets/img/banners/' . $new_filename;
        }
    }

    if ($id) {
        // Update
        $sql = "UPDATE banners SET title=?, subtitle=?, link=?, sort_order=?, is_active=?, image_path=? WHERE id=?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$title, $subtitle, $link, $sort_order, $is_active, $image_path, $id]);
    } else {
        // Insert
        $sql = "INSERT INTO banners (title, subtitle, link, sort_order, is_active, image_path) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $db->prepare($sql);
        $stmt->execute([$title, $subtitle, $link, $sort_order, $is_active, $image_path]);
    }

    echo "<script>window.location.href='banners.php';</script>";
    exit;
}
?>

<div class="page-header">
    <h1 class="page-title">
        <?php echo $id ? 'แก้ไขแบนเนอร์' : 'เพิ่มแบนเนอร์ใหม่'; ?>
    </h1>
</div>

<div class="card" style="max-width: 800px; margin: 0 auto;">
    <form method="POST" enctype="multipart/form-data">

        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 500;">รูปภาพแบนเนอร์</label>
            <?php if ($banner && $banner['image_path']): ?>
                <div style="margin-bottom: 10px;">
                    <img src="../<?php echo $banner['image_path']; ?>"
                        style="max-width: 100%; height: auto; border-radius: 8px;">
                </div>
            <?php endif; ?>
            <input type="file" name="image" accept="image/*" class="form-control" <?php echo $id ? '' : 'required'; ?>
            style="padding: 10px; width: 100%; border: 1px solid #ddd; border-radius: 5px;">
            <small style="color: #666;">ขนาดแนะนำ: 1920x600 px</small>
        </div>

        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 500;">หัวข้อ (Title)</label>
            <input type="text" name="title" value="<?php echo $banner ? $banner['title'] : ''; ?>" class="form-control"
                style="padding: 10px; width: 100%; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box;">
        </div>

        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 500;">รายละเอียด (Subtitle)</label>
            <input type="text" name="subtitle" value="<?php echo $banner ? $banner['subtitle'] : ''; ?>"
                class="form-control"
                style="padding: 10px; width: 100%; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box;">
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 500;">ลิงก์ (URL)</label>
                <input type="text" name="link" value="<?php echo $banner ? $banner['link'] : '#'; ?>"
                    class="form-control"
                    style="padding: 10px; width: 100%; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box;">
            </div>
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 500;">ลำดับการแสดง</label>
                <input type="number" name="sort_order" value="<?php echo $banner ? $banner['sort_order'] : '0'; ?>"
                    class="form-control"
                    style="padding: 10px; width: 100%; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box;">
            </div>
        </div>

        <div style="margin-bottom: 30px;">
            <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                <input type="checkbox" name="is_active" <?php echo (!$banner || $banner['is_active']) ? 'checked' : ''; ?>>
                แสดงผลบนหน้าเว็บ
            </label>
        </div>

        <div style="display: flex; gap: 15px;">
            <button type="submit"
                style="background: var(--primary-blue); color: white; padding: 12px 30px; border: none; border-radius: 5px; cursor: pointer; font-size: 1rem;">บันทึกข้อมูล</button>
            <a href="banners.php"
                style="background: #eee; color: #333; padding: 12px 30px; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; font-size: 1rem;">ยกเลิก</a>
        </div>

    </form>
</div>

<?php require_once 'includes/footer.php'; ?>