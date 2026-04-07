<?php
session_start();
require_once 'config/db.php';
require_once 'includes/header.php';

$database = new Database();
$db = $database->getConnection();

// Handle Form Submission (Add New)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $report_type = $_POST['report_type'];
    $publish_date = $_POST['publish_date']; // Text format or Date? Text as per other forms logic simply.

    // File Upload
    $file_path = "";
    if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
        $target_dir = "../uploads/financials/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $file_extension = pathinfo($_FILES["file"]["name"], PATHINFO_EXTENSION);
        $new_filename = uniqid() . '.' . $file_extension;
        $target_db_path = "uploads/financials/" . $new_filename;
        $target_file = $target_dir . $new_filename;

        if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)) {
            $file_path = $target_db_path;

            $sql = "INSERT INTO financial_reports (title, report_type, publish_date, file_path) VALUES (:title, :report_type, :publish_date, :file_path)";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':report_type', $report_type);
            $stmt->bindParam(':publish_date', $publish_date);
            $stmt->bindParam(':file_path', $file_path);

            if ($stmt->execute()) {
                echo "<script>alert('เพิ่มรายงานเรียบร้อยแล้ว'); window.location.href='financials.php';</script>";
            }
        } else {
            echo "<script>alert('เกิดข้อผิดพลาดในการอัปโหลดไฟล์');</script>";
        }
    } else {
        echo "<script>alert('กรุณาเลือกไฟล์เอกสาร (PDF)');</script>";
    }
}

// Handle Delete
if (isset($_GET['delete_id'])) {
    $stmt = $db->prepare("DELETE FROM financial_reports WHERE id = :id");
    $stmt->bindParam(':id', $_GET['delete_id']);
    if ($stmt->execute()) {
        echo "<script>alert('ลบข้อมูลเรียบร้อยแล้ว'); window.location.href='financials.php';</script>";
    }
}

// Fetch Reports
$stmt = $db->query("SELECT * FROM financial_reports ORDER BY id DESC");
$reports = $stmt->fetchAll();
?>

<div class="page-header">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <h1 class="page-title">ข้อมูลทางการเงิน (Financial Reports)</h1>
    </div>
</div>

<div class="card" style="margin-bottom: 30px; border-left: 5px solid var(--accent-gold);">
    <h3 style="margin-top: 0; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 1px solid #eee;">เพิ่มรายงานใหม่</h3>
    <form method="POST" enctype="multipart/form-data" style="max-width: 100%;">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
            <div style="grid-column: span 2;">
                <label style="display: block; font-weight: 500; margin-bottom: 8px;">ชื่อรายงาน</label>
                <input type="text" name="title" required placeholder="เช่น รายงานประจำปี 2568"
                    style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-family: 'Prompt';">
            </div>
            
            <div>
                <label style="display: block; font-weight: 500; margin-bottom: 8px;">ประเภท</label>
                <select name="report_type"
                    style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-family: 'Prompt';">
                    <option value="งบการเงิน">งบการเงิน</option>
                    <option value="รายการข้อมูลประจำปี ONE REPORT">รายการข้อมูลประจำปี ONE REPORT</option>
                    <option value="รายงานประจำปี (แบบ 56-2)">รายงานประจำปี (แบบ 56-2)</option>
                </select>
            </div>
            
            <div>
                <label style="display: block; font-weight: 500; margin-bottom: 8px;">วันที่เผยแพร่</label>
                <input type="text" name="publish_date" placeholder="วว/ดด/ปปปป"
                    style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-family: 'Prompt';">
            </div>
            
            <div style="grid-column: span 2;">
                <label style="display: block; font-weight: 500; margin-bottom: 8px;">ไฟล์ PDF</label>
                <div style="border: 2px dashed #ddd; padding: 20px; text-align: center; border-radius: 6px; background: #f9f9f9;">
                    <input type="file" name="file" required accept=".pdf" 
                        style="width: 100%; max-width: 300px; font-size: 0.9em; font-family: 'Prompt';">
                    <div style="margin-top: 5px; color: #888; font-size: 0.85em;">รองรับไฟล์ .PDF เท่านั้น</div>
                </div>
            </div>
        </div>

        <div style="text-align: right;">
            <button type="submit"
                style="background: var(--primary-blue); color: white; border: none; padding: 12px 30px; border-radius: 4px; cursor: pointer; font-family: 'Prompt'; font-size: 1rem; display: inline-flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-cloud-upload"></i> บันทึกและอัปโหลด
            </button>
        </div>
    </form>
</div>

<div class="card" style="margin-top: 30px;">
    <table style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="background: #f8f9fa; border-bottom: 2px solid #dee2e6;">
                <th style="padding: 15px; text-align: left;">ชื่อรายงาน</th>
                <th style="padding: 15px; text-align: left;">ประเภท</th>
                <th style="padding: 15px; text-align: left;">วันที่</th>
                <th style="padding: 15px; text-align: center;">ไฟล์</th>
                <th style="padding: 15px; text-align: center;">จัดการ</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($reports) > 0): ?>
                <?php foreach ($reports as $r): ?>
                    <tr style="border-bottom: 1px solid #dee2e6;">
                        <td style="padding: 15px;">
                            <strong>
                                <?php echo $r['title']; ?>
                            </strong>
                        </td>
                        <td style="padding: 15px;">
                            <span
                                style="background: #e3f2fd; color: #0d47a1; padding: 2px 8px; border-radius: 4px; font-size: 0.9em;">
                                <?php echo $r['report_type']; ?>
                            </span>
                        </td>
                        <td style="padding: 15px; color: #555;">
                            <?php echo $r['publish_date']; ?>
                        </td>
                        <td style="padding: 15px; text-align: center;">
                            <a href="../<?php echo $r['file_path']; ?>" target="_blank"
                                style="color: #d32f2f; text-decoration: none;">
                                <i class="fa-solid fa-file-pdf fa-lg"></i> PDF
                            </a>
                        </td>
                        <td style="padding: 15px; text-align: center;">
                            <a href="financials.php?delete_id=<?php echo $r['id']; ?>" style="color: #d32f2f;" title="ลบ"
                                onclick="return confirm('ยืนยันลบรายงานนี้?');">
                                <i class="fa-solid fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" style="padding: 30px; text-align: center; color: #888;">
                        ยังไม่มีรายงานการเงิน
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once 'includes/footer.php'; ?>