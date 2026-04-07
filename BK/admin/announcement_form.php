<?php
session_start();
require_once 'config/db.php';
require_once 'includes/header.php';

$database = new Database();
$db = $database->getConnection();

$id = isset($_GET['id']) ? $_GET['id'] : null;
$announcement = null;

if ($id) {
    $stmt = $db->prepare("SELECT * FROM announcements WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $announcement = $stmt->fetch();
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $category = $_POST['category'];
    $content = $_POST['content'];
    $start_date = !empty($_POST['start_date']) ? $_POST['start_date'] : null;
    $end_date = !empty($_POST['end_date']) ? $_POST['end_date'] : null;
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $is_popup = isset($_POST['is_popup']) ? 1 : 0;

    // File Upload
    $cover_image = ($announcement) ? $announcement['cover_image'] : "";
    if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] == 0) {
        $target_dir = "../uploads/announcements/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $file_extension = pathinfo($_FILES["cover_image"]["name"], PATHINFO_EXTENSION);
        $new_filename = uniqid() . '.' . $file_extension;
        $target_db_path = "uploads/announcements/" . $new_filename;
        $target_file = $target_dir . $new_filename;

        if (move_uploaded_file($_FILES["cover_image"]["tmp_name"], $target_file)) {
            $cover_image = $target_db_path;
        }
    }

    if ($id) {
        // Update
        $sql = "UPDATE announcements SET title = :title, category = :category, content = :content, cover_image = :cover_image, start_date = :start_date, end_date = :end_date, is_active = :is_active, is_popup = :is_popup WHERE id = :id";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':id', $id);
    } else {
        // Insert
        $sql = "INSERT INTO announcements (title, category, content, cover_image, start_date, end_date, is_active, is_popup) VALUES (:title, :category, :content, :cover_image, :start_date, :end_date, :is_active, :is_popup)";
        $stmt = $db->prepare($sql);
    }

    $stmt->bindParam(':title', $title);
    $stmt->bindParam(':category', $category);
    $stmt->bindParam(':content', $content);
    $stmt->bindParam(':cover_image', $cover_image);
    $stmt->bindParam(':start_date', $start_date);
    $stmt->bindParam(':end_date', $end_date);
    $stmt->bindParam(':is_active', $is_active);
    $stmt->bindParam(':is_popup', $is_popup);

    if ($stmt->execute()) {
        echo "<script>alert('บันทึกข้อมูลเรียบร้อยแล้ว'); window.location.href='announcements.php';</script>";
    } else {
        echo "<script>alert('เกิดข้อผิดพลาดในการบันทึก');</script>";
    }
}
?>

<div class="page-header">
    <h1 class="page-title">
        <?php echo $id ? 'แก้ไข' : 'เพิ่ม'; ?>ข่าวประชาสัมพันธ์
    </h1>
    <a href="announcements.php" class="btn" style="background: #ddd; color: #333;">ย้อนกลับ</a>
</div>

<div class="card" style="max-width: 800px; margin: 0 auto;">
    <form method="POST" enctype="multipart/form-data">
        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px; font-weight: bold;">หัวข้อข่าว *</label>
            <input type="text" name="title" required value="<?php echo $announcement ? $announcement['title'] : ''; ?>"
                style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-family: 'Prompt';">
        </div>

        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px; font-weight: bold;">หมวดหมู่</label>
            <select name="category"
                style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-family: 'Prompt';">
                <option value="News" <?php echo ($announcement && $announcement['category'] == 'News') ? 'selected' : ''; ?>>ข่าวประชาสัมพันธ์ (News)</option>
                <option value="Activity" <?php echo ($announcement && $announcement['category'] == 'Activity') ? 'selected' : ''; ?>>กิจกรรม (Activity)</option>
                <option value="Promotion" <?php echo ($announcement && $announcement['category'] == 'Promotion') ? 'selected' : ''; ?>>โปรโมชั่น (Promotion)</option>
            </select>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 15px;">
            <div>
                <label style="display: block; margin-bottom: 5px;">วันที่เริ่มต้นแสดง</label>
                <input type="date" name="start_date"
                    value="<?php echo $announcement ? $announcement['start_date'] : date('Y-m-d'); ?>"
                    style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-family: 'Prompt';">
            </div>
            <div>
                <label style="display: block; margin-bottom: 5px;">วันที่สิ้นสุด (ถ้ามี)</label>
                <input type="date" name="end_date" value="<?php echo $announcement ? $announcement['end_date'] : ''; ?>"
                    style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-family: 'Prompt';">
            </div>
        </div>

        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px; font-weight: bold;">รูปภาพหน้าปก</label>
            <?php if ($announcement && !empty($announcement['cover_image'])): ?>
                <img src="../<?php echo $announcement['cover_image']; ?>"
                    style="max-width: 200px; display: block; margin-bottom: 10px;">
            <?php endif; ?>
            <input type="file" name="cover_image" accept="image/*">
        </div>

        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 5px; font-weight: bold;">เนื้อหาข่าว</label>
            <textarea name="content" rows="10"
                style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-family: 'Prompt';"><?php echo $announcement ? $announcement['content'] : ''; ?></textarea>
        </div>

        <div style="margin-bottom: 20px; background: #f9f9f9; padding: 15px; border-radius: 4px;">
            <div style="margin-bottom: 10px;">
                <label style="display: inline-flex; align-items: center; cursor: pointer;">
                    <input type="checkbox" name="is_active" <?php echo (!$announcement || $announcement['is_active']) ? 'checked' : ''; ?> style="width: 18px; height: 18px; margin-right: 10px;">
                    แสดงผลบนเว็บไซต์ (Active)
                </label>
            </div>
            <div>
                <label style="display: inline-flex; align-items: center; cursor: pointer;">
                    <input type="checkbox" name="is_popup" <?php echo ($announcement && $announcement['is_popup']) ? 'checked' : ''; ?> style="width: 18px; height: 18px; margin-right: 10px;">
                    แสดงเป็น Popup หน้าแรก
                </label>
            </div>
        </div>

        <button type="submit" class="btn btn-primary" style="padding: 12px 30px; font-size: 1.1em;">
            <i class="fa-solid fa-save"></i> บันทึกข้อมูล
        </button>
    </form>
</div>

<?php require_once 'includes/footer.php'; ?>