<?php
session_start();
require_once 'config/db.php';
require_once 'includes/header.php';

$database = new Database();
$db = $database->getConnection();

$id = isset($_GET['id']) ? $_GET['id'] : null;
$service = null;

// Fetch existing data if editing
if ($id) {
    $stmt = $db->prepare("SELECT * FROM services WHERE id = ?");
    $stmt->execute([$id]);
    $service = $stmt->fetch();
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $icon_class = $_POST['icon_class'];
    $link = $_POST['link'];
    $sort_order = $_POST['sort_order'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    if ($id) {
        // Update
        $sql = "UPDATE services SET title=?, description=?, icon_class=?, link=?, sort_order=?, is_active=? WHERE id=?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$title, $description, $icon_class, $link, $sort_order, $is_active, $id]);
    } else {
        // Insert
        $sql = "INSERT INTO services (title, description, icon_class, link, sort_order, is_active) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $db->prepare($sql);
        $stmt->execute([$title, $description, $icon_class, $link, $sort_order, $is_active]);
    }

    echo "<script>window.location.href='services.php';</script>";
    exit;
}
?>

<div class="page-header">
    <h1 class="page-title">
        <?php echo $id ? 'แก้ไขบริการ' : 'เพิ่มบริการใหม่'; ?>
    </h1>
</div>

<div class="card" style="max-width: 800px; margin: 0 auto;">
    <form method="POST">

        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 500;">ชื่อบริการ (Title)</label>
            <input type="text" name="title" value="<?php echo $service ? $service['title'] : ''; ?>"
                class="form-control" required
                style="padding: 10px; width: 100%; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box;">
        </div>

        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 500;">รายละเอียด (Description)</label>
            <textarea name="description" class="form-control" rows="4"
                style="padding: 10px; width: 100%; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; font-family: 'Prompt', sans-serif;"><?php echo $service ? $service['description'] : ''; ?></textarea>
        </div>

        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 500;">ไอคอน (Font Awesome Class)</label>
            <input type="text" name="icon_class"
                value="<?php echo $service ? $service['icon_class'] : 'fa-solid fa-car'; ?>" class="form-control"
                style="padding: 10px; width: 100%; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box;">
            <small style="color: #666;">ตัวอย่าง: fa-solid fa-car, fa-solid fa-motorcycle, fa-solid fa-home</small>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 500;">ลิงก์ (URL)</label>
                <input type="text" name="link" value="<?php echo $service ? $service['link'] : '#'; ?>"
                    class="form-control"
                    style="padding: 10px; width: 100%; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box;">
            </div>
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 500;">ลำดับการแสดง</label>
                <input type="number" name="sort_order" value="<?php echo $service ? $service['sort_order'] : '0'; ?>"
                    class="form-control"
                    style="padding: 10px; width: 100%; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box;">
            </div>
        </div>

        <div style="margin-bottom: 30px;">
            <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                <input type="checkbox" name="is_active" <?php echo (!$service || $service['is_active']) ? 'checked' : ''; ?>>
                แสดงผลบนหน้าเว็บ
            </label>
        </div>

        <div style="display: flex; gap: 15px;">
            <button type="submit"
                style="background: var(--primary-blue); color: white; padding: 12px 30px; border: none; border-radius: 5px; cursor: pointer; font-size: 1rem;">บันทึกข้อมูล</button>
            <a href="services.php"
                style="background: #eee; color: #333; padding: 12px 30px; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; font-size: 1rem;">ยกเลิก</a>
        </div>

    </form>
</div>

<?php require_once 'includes/footer.php'; ?>