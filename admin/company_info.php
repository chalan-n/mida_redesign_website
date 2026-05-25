<?php
session_start();
require_once 'config/db.php';
require_once 'includes/AdminPermission.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

$database = new Database();
$db = $database->getConnection();
$perm = new AdminPermission($db, $_SESSION['admin_id']);

function ensureCompanyInfoTables($db)
{
    $db->exec("
        CREATE TABLE IF NOT EXISTS company_info (
            id INT NOT NULL PRIMARY KEY,
            company_name VARCHAR(255) DEFAULT NULL,
            head_office TEXT,
            business_type TEXT,
            registration_no VARCHAR(100) DEFAULT NULL,
            phone VARCHAR(100) DEFAULT NULL,
            fax VARCHAR(150) DEFAULT NULL,
            website VARCHAR(255) DEFAULT NULL,
            registered_capital VARCHAR(100) DEFAULT NULL,
            paid_up_capital VARCHAR(100) DEFAULT NULL,
            shareholder_date VARCHAR(100) DEFAULT NULL,
            updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    $db->exec("
        CREATE TABLE IF NOT EXISTS shareholders (
            ID INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            shareholder_name VARCHAR(150) DEFAULT NULL,
            shareholder_price VARCHAR(150) DEFAULT NULL,
            shareholder_percent VARCHAR(10) DEFAULT NULL,
            shareholder_order INT(11) DEFAULT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    $stmt = $db->prepare("
        INSERT IGNORE INTO company_info
            (id, company_name, head_office, business_type, registration_no, phone, fax, website, registered_capital, paid_up_capital, shareholder_date)
        VALUES
            (1, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute(array(
        'บริษัท ไมด้าลิสซิ่ง จำกัด (มหาชน)',
        '48/1-5 ซอยแจ้งวัฒนะ 14 ถนนแจ้งวัฒนะ แขวงทุ่งสองห้อง เขตหลักสี่ กรุงเทพฯ 10210',
        'ให้บริการสินเชื่อเช่าซื้อรถยนต์ โดยเน้นรถยนต์มือสองและรถรับจ้าง รวมถึงสินเชื่อหมุนเวียนสำหรับผู้ประกอบการรถยนต์มือสอง พร้อมบริการหลังการขาย เช่น ต่อทะเบียนรถยนต์ พ.ร.บ. คุ้มครองผู้ประสบภัยจากรถยนต์ ประกันภัย และการบริหารสินทรัพย์ด้อยคุณภาพ',
        '0107547000532',
        '0-2574-6901',
        '0-2574-6902, 0-2574-6903',
        'www.mida-leasing.com',
        '665,498,289.00 บาท',
        '532,398,631.50 บาท',
        '21 มี.ค. 2565'
    ));
}

function redirectCompanyInfo($message = '')
{
    $suffix = $message !== '' ? '?message=' . urlencode($message) : '';
    header('Location: company_info.php' . $suffix);
    exit;
}

ensureCompanyInfoTables($db);

if (!$perm->canView('financials')) {
    $_SESSION['error_message'] = 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้';
    header('Location: index.php');
    exit;
}

$success_msg = isset($_GET['message']) ? $_GET['message'] : '';
$error_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'save_company') {
            if (!$perm->canUpdate('financials')) {
                throw new Exception('คุณไม่มีสิทธิ์แก้ไขข้อมูลบริษัท');
            }

            $stmt = $db->prepare("
                UPDATE company_info
                SET company_name = ?, head_office = ?, business_type = ?, registration_no = ?,
                    phone = ?, fax = ?, website = ?, registered_capital = ?, paid_up_capital = ?, shareholder_date = ?
                WHERE id = 1
            ");
            $stmt->execute(array(
                trim($_POST['company_name'] ?? ''),
                trim($_POST['head_office'] ?? ''),
                trim($_POST['business_type'] ?? ''),
                trim($_POST['registration_no'] ?? ''),
                trim($_POST['phone'] ?? ''),
                trim($_POST['fax'] ?? ''),
                trim($_POST['website'] ?? ''),
                trim($_POST['registered_capital'] ?? ''),
                trim($_POST['paid_up_capital'] ?? ''),
                trim($_POST['shareholder_date'] ?? '')
            ));

            redirectCompanyInfo('บันทึกข้อมูลบริษัทเรียบร้อยแล้ว');
        }

        if ($action === 'save_shareholders') {
            if (!$perm->canUpdate('financials')) {
                throw new Exception('คุณไม่มีสิทธิ์แก้ไขข้อมูลผู้ถือหุ้น');
            }

            $ids = $_POST['shareholder_id'] ?? array();
            $orders = $_POST['shareholder_order'] ?? array();
            $names = $_POST['shareholder_name'] ?? array();
            $prices = $_POST['shareholder_price'] ?? array();
            $percents = $_POST['shareholder_percent'] ?? array();

            $stmt_update = $db->prepare("
                UPDATE shareholders
                SET shareholder_order = ?, shareholder_name = ?, shareholder_price = ?, shareholder_percent = ?
                WHERE ID = ?
            ");
            $stmt_insert = $db->prepare("
                INSERT INTO shareholders (shareholder_order, shareholder_name, shareholder_price, shareholder_percent)
                VALUES (?, ?, ?, ?)
            ");

            foreach ($names as $index => $name) {
                $name = trim($name);
                $price = trim($prices[$index] ?? '');
                $percent = trim($percents[$index] ?? '');
                $order = (int) ($orders[$index] ?? ($index + 1));
                $id = (int) ($ids[$index] ?? 0);

                if ($name === '' && $price === '' && $percent === '') {
                    continue;
                }

                if ($id > 0) {
                    $stmt_update->execute(array($order, $name, $price, $percent, $id));
                } else {
                    $stmt_insert->execute(array($order, $name, $price, $percent));
                }
            }

            redirectCompanyInfo('บันทึกรายชื่อผู้ถือหุ้นเรียบร้อยแล้ว');
        }
    } catch (Exception $e) {
        $error_msg = $e->getMessage();
    }
}

if (isset($_GET['delete_shareholder'])) {
    try {
        if (!$perm->canDelete('financials')) {
            throw new Exception('คุณไม่มีสิทธิ์ลบข้อมูลผู้ถือหุ้น');
        }

        $stmt = $db->prepare("DELETE FROM shareholders WHERE ID = ?");
        $stmt->execute(array((int) $_GET['delete_shareholder']));
        redirectCompanyInfo('ลบข้อมูลผู้ถือหุ้นเรียบร้อยแล้ว');
    } catch (Exception $e) {
        $error_msg = $e->getMessage();
    }
}

$stmt_company = $db->query("SELECT * FROM company_info WHERE id = 1");
$company_info = $stmt_company->fetch(PDO::FETCH_ASSOC);

$stmt_shareholders = $db->query("SELECT * FROM shareholders ORDER BY shareholder_order ASC, ID ASC LIMIT 10");
$shareholders = $stmt_shareholders->fetchAll(PDO::FETCH_ASSOC);

$blank_rows = max(1, 10 - count($shareholders));

require_once 'includes/header.php';
?>

<div class="page-header">
    <div style="display: flex; justify-content: space-between; align-items: center; gap: 20px;">
        <div>
            <h1 class="page-title">จัดการข้อมูลบริษัท</h1>
            <p style="margin: 8px 0 0; color: #6c757d;">แก้ไขข้อมูลที่แสดงในหน้า investor_company.php และรายชื่อผู้ถือหุ้นรายใหญ่ 10 รายแรก</p>
        </div>
        <a href="../investor_company.php" target="_blank" class="btn btn-outline-primary">
            <i class="fa-solid fa-arrow-up-right-from-square"></i> ดูหน้าบ้าน
        </a>
    </div>
</div>

<?php if ($success_msg): ?>
    <div class="alert alert-success"><i class="fa-solid fa-check-circle"></i> <?php echo htmlspecialchars($success_msg); ?></div>
<?php endif; ?>

<?php if ($error_msg): ?>
    <div class="alert alert-danger"><i class="fa-solid fa-triangle-exclamation"></i> <?php echo htmlspecialchars($error_msg); ?></div>
<?php endif; ?>

<div class="card" style="border-left: 5px solid var(--accent-gold);">
    <h3 style="margin-top: 0; margin-bottom: 20px;">ข้อมูลบริษัท</h3>
    <form method="POST" action="">
        <input type="hidden" name="action" value="save_company">

        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">ชื่อบริษัท</label>
                <input type="text" name="company_name" class="form-control" value="<?php echo htmlspecialchars($company_info['company_name'] ?? ''); ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label">เลขทะเบียนบริษัท</label>
                <input type="text" name="registration_no" class="form-control" value="<?php echo htmlspecialchars($company_info['registration_no'] ?? ''); ?>">
            </div>
            <div class="col-md-12">
                <label class="form-label">สำนักงานใหญ่</label>
                <textarea name="head_office" class="form-control" rows="3"><?php echo htmlspecialchars($company_info['head_office'] ?? ''); ?></textarea>
            </div>
            <div class="col-md-12">
                <label class="form-label">ประเภทธุรกิจ</label>
                <textarea name="business_type" class="form-control" rows="5"><?php echo htmlspecialchars($company_info['business_type'] ?? ''); ?></textarea>
            </div>
            <div class="col-md-4">
                <label class="form-label">โทรศัพท์</label>
                <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($company_info['phone'] ?? ''); ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">โทรสาร</label>
                <input type="text" name="fax" class="form-control" value="<?php echo htmlspecialchars($company_info['fax'] ?? ''); ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">เว็บไซต์</label>
                <input type="text" name="website" class="form-control" value="<?php echo htmlspecialchars($company_info['website'] ?? ''); ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">ทุนจดทะเบียน</label>
                <input type="text" name="registered_capital" class="form-control" value="<?php echo htmlspecialchars($company_info['registered_capital'] ?? ''); ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">ทุนจดทะเบียนชำระแล้ว</label>
                <input type="text" name="paid_up_capital" class="form-control" value="<?php echo htmlspecialchars($company_info['paid_up_capital'] ?? ''); ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">วันที่ข้อมูลผู้ถือหุ้น</label>
                <input type="text" name="shareholder_date" class="form-control" value="<?php echo htmlspecialchars($company_info['shareholder_date'] ?? ''); ?>" placeholder="เช่น 21 มี.ค. 2565">
            </div>
        </div>

        <div class="mt-4 text-end">
            <button type="submit" class="btn btn-primary" <?php echo $perm->canUpdate('financials') ? '' : 'disabled'; ?>>
                <i class="fa-solid fa-save"></i> บันทึกข้อมูลบริษัท
            </button>
        </div>
    </form>
</div>

<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 20px; margin-bottom: 20px;">
        <div>
            <h3 style="margin: 0;">รายชื่อผู้ถือหุ้นรายใหญ่ 10 รายแรก</h3>
            <p style="margin: 8px 0 0; color: #6c757d;">ข้อมูลนี้จะแสดงในตารางผู้ถือหุ้นบนหน้าบ้าน เรียงตามลำดับที่กำหนด</p>
        </div>
    </div>

    <form method="POST" action="">
        <input type="hidden" name="action" value="save_shareholders">
        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead class="table-primary">
                    <tr>
                        <th style="width: 90px;">ลำดับ</th>
                        <th>ชื่อ-นามสกุล / ชื่อนิติบุคคล</th>
                        <th style="width: 220px;">จำนวนหุ้น</th>
                        <th style="width: 160px;">ร้อยละ</th>
                        <th style="width: 90px;" class="text-center">ลบ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($shareholders as $index => $shareholder): ?>
                        <tr>
                            <td>
                                <input type="hidden" name="shareholder_id[]" value="<?php echo (int) $shareholder['ID']; ?>">
                                <input type="number" name="shareholder_order[]" class="form-control" value="<?php echo htmlspecialchars($shareholder['shareholder_order'] ?? ($index + 1)); ?>">
                            </td>
                            <td>
                                <input type="text" name="shareholder_name[]" class="form-control" value="<?php echo htmlspecialchars($shareholder['shareholder_name'] ?? ''); ?>">
                            </td>
                            <td>
                                <input type="text" name="shareholder_price[]" class="form-control" value="<?php echo htmlspecialchars($shareholder['shareholder_price'] ?? ''); ?>">
                            </td>
                            <td>
                                <input type="text" name="shareholder_percent[]" class="form-control" value="<?php echo htmlspecialchars($shareholder['shareholder_percent'] ?? ''); ?>">
                            </td>
                            <td class="text-center">
                                <?php if ($perm->canDelete('financials')): ?>
                                    <a href="company_info.php?delete_shareholder=<?php echo (int) $shareholder['ID']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('ยืนยันลบผู้ถือหุ้นรายการนี้?');">
                                        <i class="fa-solid fa-trash"></i>
                                    </a>
                                <?php else: ?>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" disabled><i class="fa-solid fa-lock"></i></button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <?php for ($i = 0; $i < $blank_rows; $i++): ?>
                        <tr>
                            <td>
                                <input type="hidden" name="shareholder_id[]" value="">
                                <input type="number" name="shareholder_order[]" class="form-control" value="<?php echo count($shareholders) + $i + 1; ?>">
                            </td>
                            <td><input type="text" name="shareholder_name[]" class="form-control" placeholder="เพิ่มชื่อผู้ถือหุ้น"></td>
                            <td><input type="text" name="shareholder_price[]" class="form-control" placeholder="เช่น 500,214,000"></td>
                            <td><input type="text" name="shareholder_percent[]" class="form-control" placeholder="เช่น 46.98"></td>
                            <td class="text-center text-muted">ใหม่</td>
                        </tr>
                    <?php endfor; ?>
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-between align-items-center gap-3 mt-3">
            <small class="text-muted">ระบบจะแสดง 10 รายการแรกตามลำดับเท่านั้น หากต้องเพิ่มแถวใหม่ ให้บันทึกแถวว่างด้านล่างก่อน</small>
            <button type="submit" class="btn btn-primary" <?php echo $perm->canUpdate('financials') ? '' : 'disabled'; ?>>
                <i class="fa-solid fa-save"></i> บันทึกผู้ถือหุ้น
            </button>
        </div>
    </form>
</div>

<?php require_once 'includes/footer.php'; ?>
