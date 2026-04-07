<?php
session_start();
require_once 'config/db.php';
require_once 'includes/header.php';

$database = new Database();
$db = $database->getConnection();

$success_msg = "";
$error_msg = "";

// Handle Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $site_phone = $_POST['site_phone'] ?? '';
    $site_email = $_POST['site_email'] ?? '';
    $site_facebook = $_POST['site_facebook'] ?? '';
    $site_line = $_POST['site_line'] ?? '';
    $site_address = $_POST['site_address'] ?? '';
    $site_work_hours = $_POST['site_work_hours'] ?? '';

    // Fetch current favicon and logo first
    $site_favicon = '';
    $site_logo = '';
    $stmt_current = $db->query("SELECT site_favicon, site_logo FROM settings WHERE id = 1");
    if ($current_row = $stmt_current->fetch()) {
        $site_favicon = $current_row['site_favicon'] ?? '';
        $site_logo = $current_row['site_logo'] ?? '';
    }

    // Handle Favicon Upload
    if (isset($_FILES['site_favicon']) && $_FILES['site_favicon']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'ico', 'svg'];
        $filename = $_FILES['site_favicon']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (in_array($ext, $allowed)) {
            $upload_path = "../uploads/settings/";
            if (!file_exists($upload_path)) {
                mkdir($upload_path, 0777, true);
            }

            $new_filename = "favicon_" . time() . "." . $ext;
            if (move_uploaded_file($_FILES['site_favicon']['tmp_name'], $upload_path . $new_filename)) {
                $site_favicon = "uploads/settings/" . $new_filename;
            }
        }
    }

    // Handle Logo Upload
    if (isset($_FILES['site_logo']) && $_FILES['site_logo']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'];
        $filename = $_FILES['site_logo']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (in_array($ext, $allowed)) {
            $upload_path = "../uploads/settings/";
            if (!file_exists($upload_path)) {
                mkdir($upload_path, 0777, true);
            }

            $new_filename = "logo_" . time() . "." . $ext;
            if (move_uploaded_file($_FILES['site_logo']['tmp_name'], $upload_path . $new_filename)) {
                $site_logo = "uploads/settings/" . $new_filename;
            }
        }
    }

    try {
        $sql = "UPDATE settings SET site_phone = ?, site_email = ?, site_facebook = ?, site_line = ?, site_address = ?, site_work_hours = ?, site_favicon = ?, site_logo = ? WHERE id = 1";
        $stmt = $db->prepare($sql);
        $stmt->execute([$site_phone, $site_email, $site_facebook, $site_line, $site_address, $site_work_hours, $site_favicon, $site_logo]);
        $success_msg = "บันทึกข้อมูลเรียบร้อยแล้ว";
    } catch (PDOException $e) {
        $error_msg = "เกิดข้อผิดพลาด: " . $e->getMessage();
    }
}

// Fetch Current Settings
$stmt = $db->query("SELECT * FROM settings WHERE id = 1");
$settings = $stmt->fetch();

if (!$settings) {
    // Should not happen if seeded correctly, but just in case
    $settings = [
        'site_phone' => '',
        'site_email' => '',
        'site_facebook' => '',
        'site_line' => '',
        'site_address' => '',
        'site_work_hours' => '',
        'site_favicon' => '',
        'site_logo' => ''
    ];
}
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">ตั้งค่าเว็บไซต์</h1>

    <?php if ($success_msg): ?>
        <div class="alert alert-success"><i class="fas fa-check-circle"></i>
            <?php echo $success_msg; ?>
        </div>
    <?php endif; ?>

    <?php if ($error_msg): ?>
        <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i>
            <?php echo $error_msg; ?>
        </div>
    <?php endif; ?>

    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-cogs me-1"></i> ข้อมูลการติดต่อ
        </div>
        <div class="card-body">
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">เบอร์โทรศัพท์</label>
                        <input type="text" name="site_phone" class="form-control"
                            value="<?php echo htmlspecialchars($settings['site_phone']); ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">อีเมล</label>
                        <input type="email" name="site_email" class="form-control"
                            value="<?php echo htmlspecialchars($settings['site_email']); ?>">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label"><i class="fab fa-facebook text-primary"></i> Facebook Link</label>
                        <input type="text" name="site_facebook" class="form-control"
                            value="<?php echo htmlspecialchars($settings['site_facebook']); ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label"><i class="fab fa-line text-success"></i> LINE Link</label>
                        <input type="text" name="site_line" class="form-control"
                            value="<?php echo htmlspecialchars($settings['site_line']); ?>">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">ที่อยู่บริษัท</label>
                    <textarea name="site_address" class="form-control"
                        rows="3"><?php echo htmlspecialchars($settings['site_address']); ?></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">เวลาทำการ</label>
                    <input type="text" name="site_work_hours" class="form-control"
                        value="<?php echo htmlspecialchars($settings['site_work_hours']); ?>">
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label"><i class="fas fa-image me-1"></i> โลโก้เว็บไซต์</label>
                        <?php if (!empty($settings['site_logo'])): ?>
                            <div class="mb-2">
                                <img src="../<?php echo $settings['site_logo']; ?>" alt="Logo"
                                    style="max-width: 200px; max-height: 80px; object-fit: contain; border: 1px solid #ddd; padding: 5px; background: #f8f9fa;">
                            </div>
                        <?php endif; ?>
                        <input type="file" name="site_logo" class="form-control" accept=".png,.jpg,.jpeg,.svg,.webp">
                        <small class="text-muted">รองรับไฟล์ .png, .jpg, .svg, .webp (แนะนำขนาด 200x80 พิกเซล)</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label"><i class="fas fa-icons me-1"></i> Favicon (ไอคอนเว็บไซต์)</label>
                        <?php if (!empty($settings['site_favicon'])): ?>
                            <div class="mb-2">
                                <img src="../<?php echo $settings['site_favicon']; ?>" alt="Favicon"
                                    style="width: 32px; height: 32px; object-fit: contain; border: 1px solid #ddd; padding: 2px;">
                            </div>
                        <?php endif; ?>
                        <input type="file" name="site_favicon" class="form-control" accept=".ico,.png,.jpg,.jpeg,.svg">
                        <small class="text-muted">รองรับไฟล์ .ico, .png, .jpg, .svg</small>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> บันทึกการเปลี่ยนแปลง</button>
            </form>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>