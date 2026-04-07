<?php
session_start();
require_once 'config/db.php';
require_once 'includes/header.php';

$database = new Database();
$db = $database->getConnection();

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $category = $_POST['category'];
    $publish_date = $_POST['publish_date'];
    $link_url = $_POST['link_url'];

    // File Upload
    $file_path = "";
    if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
        $target_dir = "../uploads/publications/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $file_extension = pathinfo($_FILES["file"]["name"], PATHINFO_EXTENSION);
        $new_filename = uniqid() . '.' . $file_extension;
        $target_db_path = "uploads/publications/" . $new_filename;
        $target_file = $target_dir . $new_filename;

        if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)) {
            $file_path = $target_db_path;
        }
    }

    $sql = "INSERT INTO publications (title, category, publish_date, file_path, link_url) VALUES (:title, :category, :publish_date, :file_path, :link_url)";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':title', $title);
    $stmt->bindParam(':category', $category);
    $stmt->bindParam(':publish_date', $publish_date);
    $stmt->bindParam(':file_path', $file_path);
    $stmt->bindParam(':link_url', $link_url);

    if ($stmt->execute()) {
        echo "<script>alert('เพิ่มข้อมูลเรียบร้อยแล้ว'); window.location.href='publications.php';</script>";
    } else {
        echo "<script>alert('เกิดข้อผิดพลาดในการบันทึก');</script>";
    }
}

// Handle Delete
if (isset($_GET['delete_id'])) {
    $stmt = $db->prepare("DELETE FROM publications WHERE id = :id");
    $stmt->bindParam(':id', $_GET['delete_id']);
    if ($stmt->execute()) {
        echo "<script>alert('ลบข้อมูลเรียบร้อยแล้ว'); window.location.href='publications.php';</script>";
    }
}

// Fetch Items
$stmt = $db->query("SELECT * FROM publications ORDER BY id DESC");
$pubs = $stmt->fetchAll();
?>

<div class="page-header">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <h1 class="page-title">เอกสารดาวน์โหลด (Download Documents)</h1>
    </div>
</div>

<div class="card" style="margin-bottom: 30px; border-left: 5px solid var(--primary-blue);">
    <h3 style="margin-top: 0;">เพิ่มเอกสารใหม่</h3>
    <form method="POST" enctype="multipart/form-data"
        style="display: grid; grid-template-columns: 2fr 1fr 1fr 1fr 1fr auto; gap: 15px; align-items: end;">
        <div>
            <label style="display: block; font-size: 0.9em; margin-bottom: 5px;">หัวข้อข่าว</label>
            <input type="text" name="title" required placeholder="หัวข้อ..."
                style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-family: 'Prompt';">
        </div>
        <div>
            <label style="display: block; font-size: 0.9em; margin-bottom: 5px;">หมวดหมู่</label>
            <select name="category"
                style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-family: 'Prompt';">
                <option value="หนังสือเชิญประชุมสามัญผู้ถือหุ้น">หนังสือเชิญประชุมสามัญผู้ถือหุ้น</option>
                <option value="รายงานการประชุมสามัญผู้ถือหุ้นประจำปี">รายงานการประชุมสามัญผู้ถือหุ้นประจำปี</option>
                <option value="ข้อมูลใบสำคัญแสดงสิทธิ">ข้อมูลใบสำคัญแสดงสิทธิ</option>
            </select>
        </div>
        <div>
            <label style="display: block; font-size: 0.9em; margin-bottom: 5px;">วันที่</label>
            <input type="text" name="publish_date" placeholder="วว/ดด/ปป"
                style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-family: 'Prompt';">
        </div>
        <div>
            <label style="display: block; font-size: 0.9em; margin-bottom: 5px;">ไฟล์ (ถ้ามี)</label>
            <input type="file" name="file" style="font-size: 0.9em; width: 100%; font-family: 'Prompt';">
        </div>
        <div>
            <label style="display: block; font-size: 0.9em; margin-bottom: 5px;">URL (ถ้ามี)</label>
            <input type="text" name="link_url" placeholder="http://..."
                style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-family: 'Prompt';">
        </div>
        <div>
            <button type="submit"
                style="background: var(--primary-blue); color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; font-family: 'Prompt';">
                <i class="fa-solid fa-plus-circle"></i> เพิ่ม
            </button>
        </div>
    </form>
</div>

<div class="card">
    <table style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="background: #f8f9fa; border-bottom: 2px solid #dee2e6;">
                <th style="padding: 15px; text-align: left;">หัวข้อ</th>
                <th style="padding: 15px; text-align: left;">หมวดหมู่</th>
                <th style="padding: 15px; text-align: left;">วันที่</th>
                <th style="padding: 15px; text-align: center;">ลิงก์/ไฟล์</th>
                <th style="padding: 15px; text-align: center;">จัดการ</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($pubs) > 0): ?>
                <?php foreach ($pubs as $p): ?>
                    <tr style="border-bottom: 1px solid #dee2e6;">
                        <td style="padding: 15px;">
                            <strong>
                                <?php echo $p['title']; ?>
                            </strong>
                        </td>
                        <td style="padding: 15px;">
                            <span style="background: #eee; padding: 2px 8px; border-radius: 4px; font-size: 0.9em;">
                                <?php echo $p['category']; ?>
                            </span>
                        </td>
                        <td style="padding: 15px; color: #555;">
                            <?php echo $p['publish_date']; ?>
                        </td>
                        <td style="padding: 15px; text-align: center;">
                            <?php if (!empty($p['file_path'])): ?>
                                <a href="../<?php echo $p['file_path']; ?>" target="_blank"
                                    style="margin-right: 5px; color: #d32f2f;" title="View PDF"><i
                                        class="fa-solid fa-file-pdf"></i></a>
                            <?php endif; ?>
                            <?php if (!empty($p['link_url'])): ?>
                                <a href="<?php echo $p['link_url']; ?>" target="_blank" style="color: #0d47a1;"
                                    title="Go to Link"><i class="fa-solid fa-link"></i></a>
                            <?php endif; ?>
                        </td>
                        <td style="padding: 15px; text-align: center;">
                            <a href="publications.php?delete_id=<?php echo $p['id']; ?>" style="color: #d32f2f;" title="ลบ"
                                onclick="return confirm('ยืนยันใหม?');">
                                <i class="fa-solid fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" style="padding: 30px; text-align: center; color: #888;">
                        ยังไม่มีข้อมูล
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once 'includes/footer.php'; ?>