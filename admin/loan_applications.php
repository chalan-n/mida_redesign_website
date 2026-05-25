<?php
session_start();
require_once 'config/db.php';
require_once 'includes/header.php';

$database = new Database();
$db = $database->getConnection();

function repairZeroLoanApplicationId($db)
{
    try {
        $zeroCount = (int) $db->query("SELECT COUNT(*) FROM loan_applications WHERE id = 0")->fetchColumn();
        if ($zeroCount === 0) {
            return;
        }

        $db->beginTransaction();
        $nextId = (int) $db->query("SELECT COALESCE(MAX(id), 0) + 1 FROM loan_applications WHERE id <> 0")->fetchColumn();
        if ($nextId < 1) {
            $nextId = 1;
        }

        $stmt = $db->prepare("UPDATE loan_applications SET id = ? WHERE id = 0 LIMIT 1");
        $stmt->execute([$nextId]);
        $db->commit();
        $db->exec("ALTER TABLE loan_applications AUTO_INCREMENT = " . ($nextId + 1));
    } catch (PDOException $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        error_log('Repair loan application id=0 failed: ' . $e->getMessage());
    }
}

repairZeroLoanApplicationId($db);

// Handle Status Update
if (isset($_POST['update_status']) && isset($_POST['id'])) {
    $id = (int) $_POST['id'];
    $status = $_POST['status'];
    $stmt = $db->prepare("UPDATE loan_applications SET status = ? WHERE id = ?");
    $stmt->execute([$status, $id]);
    echo "<script>window.location.href = 'loan_applications.php?status=updated';</script>";
    exit;
}

// Helper function for badges
function getStatusBadge($status)
{
    switch ($status) {
        case 'contacted':
            return '<span class="badge bg-success">ติดต่อแล้ว</span>';
        case 'approved':
            return '<span class="badge bg-primary">อนุมัติ</span>';
        case 'rejected':
            return '<span class="badge bg-danger">ปฏิเสธ</span>';
        default:
            return '<span class="badge bg-warning text-dark">รอการติดต่อ</span>';
    }
}

function getLoanTypeLabel($type)
{
    switch ($type) {
        case 'hire_purchase':
            return 'เช่าซื้อรถยนต์';
        case 'title_loan':
            return 'จำนำทะเบียน';
        case 'personal_loan':
            return 'สินเชื่อส่วนบุคคล';
        default:
            return $type;
    }
}

// Fetch Data
$filter_type = isset($_GET['type']) ? $_GET['type'] : 'all';
$sql = "SELECT * FROM loan_applications";
$params = [];

if ($filter_type != 'all') {
    $sql .= " WHERE loan_type = ?";
    $params[] = $filter_type;
}
$sql .= " ORDER BY created_at DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$applications = $stmt->fetchAll();

$total_applications = count($applications);
$pending_applications = 0;
$today_applications = 0;
$today = date('Y-m-d');
foreach ($applications as $application) {
    $status = $application['status'] ?? '';
    if ($status === '' || $status === 'pending') {
        $pending_applications++;
    }
    if (!empty($application['created_at']) && date('Y-m-d', strtotime($application['created_at'])) === $today) {
        $today_applications++;
    }
}
?>

<h1 class="mt-4">ผู้สมัครสินเชื่อ</h1>

<style>
    h1.mt-4 {
        display: none;
    }

    .loan-page-title {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        margin: 24px 0 18px;
    }

    .loan-page-title h1 {
        margin: 0;
        color: #173b73;
        font-weight: 700;
    }

    .loan-page-subtitle {
        margin: 6px 0 0;
        color: #667085;
    }

    .loan-summary-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 16px;
        margin-bottom: 18px;
    }

    .loan-summary-card {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 18px;
        border: 1px solid #dce6f4;
        border-radius: 18px;
        background: #fff;
        box-shadow: 0 12px 30px rgba(23, 69, 143, 0.08);
    }

    .loan-summary-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 48px;
        height: 48px;
        border-radius: 15px;
        color: #173b73;
        background: #fff3c4;
        font-size: 1.25rem;
    }

    .loan-summary-card strong {
        display: block;
        color: #173b73;
        font-size: 1.45rem;
        line-height: 1.1;
    }

    .loan-summary-card span {
        color: #667085;
        font-size: 0.92rem;
    }

    .loan-admin-card {
        border: 0;
        border-radius: 20px;
        box-shadow: 0 16px 40px rgba(15, 45, 92, 0.08);
        overflow: hidden;
    }

    .loan-admin-card .card-header {
        padding: 18px 22px;
        border-bottom: 1px solid #e7edf5;
        background: #fff;
    }

    .loan-filter-group {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .loan-filter-group .btn {
        border-radius: 999px;
        font-weight: 600;
    }

    .loan-table-wrap {
        overflow-x: auto;
    }

    .loan-table {
        min-width: 1120px;
        margin: 0;
        border-color: #edf1f6;
        vertical-align: middle;
    }

    .loan-table thead th {
        border-bottom: 0;
        background: #f7faff;
        color: #344054;
        font-size: 0.92rem;
        white-space: nowrap;
    }

    .loan-table tbody td {
        padding-top: 16px;
        padding-bottom: 16px;
    }

    .loan-id-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 70px;
        padding: 6px 10px;
        border-radius: 999px;
        color: #173b73;
        background: #eef5ff;
        font-weight: 700;
    }

    .loan-customer-name {
        color: #172033;
        font-weight: 700;
    }

    .loan-contact-line {
        display: flex;
        align-items: center;
        gap: 6px;
        color: #475467;
        font-size: 0.9rem;
    }

    .loan-detail-list small {
        margin-bottom: 3px;
    }

    .loan-amount {
        color: #173b73;
        font-weight: 700;
    }

    .loan-empty-state {
        padding: 48px 16px;
        color: #667085;
    }

    @media (max-width: 992px) {
        .loan-summary-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="loan-page-title">
    <div>
        <h1>ผู้สมัครสินเชื่อ</h1>
        <p class="loan-page-subtitle">ติดตามรายการที่ลูกค้าฝากข้อมูลไว้ และอัปเดตสถานะการติดต่อกลับ</p>
    </div>
</div>

<div class="loan-summary-grid">
    <div class="loan-summary-card">
        <span class="loan-summary-icon"><i class="fas fa-list-check"></i></span>
        <div>
            <strong><?php echo number_format($total_applications); ?></strong>
            <span>รายการทั้งหมด</span>
        </div>
    </div>
    <div class="loan-summary-card">
        <span class="loan-summary-icon"><i class="fas fa-phone-volume"></i></span>
        <div>
            <strong><?php echo number_format($pending_applications); ?></strong>
            <span>รอการติดต่อ</span>
        </div>
    </div>
    <div class="loan-summary-card">
        <span class="loan-summary-icon"><i class="fas fa-calendar-day"></i></span>
        <div>
            <strong><?php echo number_format($today_applications); ?></strong>
            <span>สมัครวันนี้</span>
        </div>
    </div>
</div>

<div class="card mb-4 loan-admin-card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <div><i class="fas fa-table me-1"></i> รายการผู้สมัครสินเชื่อ</div>
            <div class="loan-filter-group">
                <a href="loan_applications.php?type=all"
                    class="btn btn-sm btn-outline-primary <?php echo $filter_type == 'all' ? 'active' : ''; ?>">ทั้งหมด</a>
                <a href="loan_applications.php?type=hire_purchase"
                    class="btn btn-sm btn-outline-primary <?php echo $filter_type == 'hire_purchase' ? 'active' : ''; ?>">เช่าซื้อ</a>
                <a href="loan_applications.php?type=title_loan"
                    class="btn btn-sm btn-outline-primary <?php echo $filter_type == 'title_loan' ? 'active' : ''; ?>">จำนำทะเบียน</a>
                <a href="loan_applications.php?type=personal_loan"
                    class="btn btn-sm btn-outline-primary <?php echo $filter_type == 'personal_loan' ? 'active' : ''; ?>">สินเชื่อบุคคล</a>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="loan-table-wrap">
        <table class="table table-bordered table-hover loan-table">
            <thead>
                <tr>
                    <th width="7%">เลขที่</th>
                    <th width="15%">วันที่สมัคร</th>
                    <th width="10%">ประเภท</th>
                    <th width="15%">ผู้สมัคร</th>
                    <th width="20%">รายละเอียดย่อย</th>
                    <th width="10%">วงเงิน</th>
                    <th width="10%">สถานะ</th>
                    <th width="15%">จัดการ</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($applications) > 0): ?>
                    <?php foreach ($applications as $index => $app): ?>
                        <?php
                        $app_id = (int) ($app['id'] ?? 0);
                        $modal_id = $app_id > 0 ? (string) $app_id : 'row' . $index;
                        $display_id = $app_id > 0 ? '#' . str_pad((string) $app_id, 5, '0', STR_PAD_LEFT) : 'รอเลข';
                        ?>
                        <tr>
                            <td>
                                <span class="loan-id-pill"><?php echo htmlspecialchars($display_id); ?></span>
                            </td>
                            <td>
                                <?php echo date('d/m/Y H:i', strtotime($app['created_at'])); ?>
                            </td>
                            <td>
                                <?php echo getLoanTypeLabel($app['loan_type']); ?>
                            </td>
                            <td>
                                <strong class="loan-customer-name">
                                    <?php echo htmlspecialchars($app['name']); ?>
                                </strong><br>
                                <small class="loan-contact-line"><i class="fas fa-phone"></i>
                                    <?php echo htmlspecialchars($app['phone']); ?>
                                </small>
                                <?php if ($app['line_id']): ?>
                                    <small class="loan-contact-line"><i class="fab fa-line text-success"></i>
                                        <?php echo htmlspecialchars($app['line_id']); ?>
                                    </small>
                                <?php endif; ?>
                            </td>
                            <td class="loan-detail-list">
                                <?php if ($app['loan_type'] == 'hire_purchase' || $app['loan_type'] == 'title_loan'): ?>
                                    <small class="d-block text-muted">ประเภทรถ:
                                        <?php echo htmlspecialchars($app['car_type']); ?>
                                    </small>
                                    <small class="d-block text-muted">ยี่ห้อ:
                                        <?php echo htmlspecialchars($app['car_brand']); ?>
                                    </small>
                                    <small class="d-block text-muted">รุ่น/ปี:
                                        <?php echo htmlspecialchars($app['car_model_year']); ?>
                                    </small>
                                    <?php if ($app['debt_status']): ?>
                                        <span class="badge bg-info text-dark">หนี้:
                                            <?php echo $app['debt_status'] == 'finance' ? 'ติดไฟแนนซ์' : 'ปลอดภาระ'; ?>
                                        </span>
                                    <?php endif; ?>
                                <?php elseif ($app['loan_type'] == 'personal_loan'): ?>
                                    <small class="d-block text-muted">อาชีพ:
                                        <?php echo htmlspecialchars($app['occupation']); ?>
                                    </small>
                                    <small class="d-block text-muted">รายได้:
                                        <?php echo number_format($app['salary']); ?>
                                    </small>
                                <?php endif; ?>
                            </td>
                            <td class="loan-amount">
                                <?php echo $app['loan_amount'] ? number_format($app['loan_amount']) : '-'; ?>
                            </td>
                            <td>
                                <?php echo getStatusBadge($app['status']); ?>
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal"
                                    data-bs-target="#viewModal<?php echo htmlspecialchars($modal_id); ?>">
                                    <i class="fas fa-eye"></i> ดู
                                </button>
                            </td>
                        </tr>

                        <!-- View Modal -->
                        <div class="modal fade" id="viewModal<?php echo htmlspecialchars($modal_id); ?>" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">รายละเอียดการสมัคร #
                                            <?php echo htmlspecialchars($display_id); ?>
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p><strong>วันที่:</strong>
                                            <?php echo date('d/m/Y H:i', strtotime($app['created_at'])); ?>
                                        </p>
                                        <p><strong>ผู้สมัคร:</strong>
                                            <?php echo htmlspecialchars($app['name']); ?>
                                        </p>
                                        <p><strong>เบอร์โทร:</strong>
                                            <?php echo htmlspecialchars($app['phone']); ?>
                                        </p>
                                        <p><strong>Line ID:</strong>
                                            <?php echo htmlspecialchars($app['line_id']); ?>
                                        </p>
                                        <hr>
                                        <p><strong>ประเภทสินเชื่อ:</strong>
                                            <?php echo getLoanTypeLabel($app['loan_type']); ?>
                                        </p>
                                        <p><strong>วงเงินที่ต้องการ:</strong>
                                            <?php echo $app['loan_amount'] ? number_format($app['loan_amount']) . ' บาท' : '-'; ?>
                                        </p>

                                        <?php if ($app['loan_type'] != 'personal_loan'): ?>
                                            <p><strong>ประเภทรถ:</strong>
                                                <?php echo htmlspecialchars($app['car_type']); ?>
                                            </p>
                                            <p><strong>ยี่ห้อรถ:</strong>
                                                <?php echo htmlspecialchars($app['car_brand']); ?>
                                            </p>
                                            <p><strong>รุ่น/ปี:</strong>
                                                <?php echo htmlspecialchars($app['car_model_year']); ?>
                                            </p>
                                            <?php if ($app['debt_status']): ?>
                                                <p><strong>สถานะหนี้:</strong>
                                                    <?php echo $app['debt_status'] == 'finance' ? 'ติดไฟแนนซ์' : 'ปลอดภาระ'; ?>
                                                </p>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <p><strong>อาชีพ:</strong>
                                                <?php echo htmlspecialchars($app['occupation']); ?>
                                            </p>
                                            <p><strong>รายได้ต่อเดือน:</strong>
                                                <?php echo number_format($app['salary']); ?> บาท
                                            </p>
                                        <?php endif; ?>

                                        <hr>
                                        <form method="POST" action="">
                                            <input type="hidden" name="id" value="<?php echo $app_id; ?>">
                                            <div class="mb-3">
                                                <label class="form-label">อัปเดตสถานะ:</label>
                                                <select name="status" class="form-select">
                                                    <option value="pending" <?php echo $app['status'] == 'pending' ? 'selected' : ''; ?>>รอการติดต่อ</option>
                                                    <option value="contacted" <?php echo $app['status'] == 'contacted' ? 'selected' : ''; ?>>ติดต่อแล้ว</option>
                                                    <option value="approved" <?php echo $app['status'] == 'approved' ? 'selected' : ''; ?>>อนุมัติเบื้องต้น</option>
                                                    <option value="rejected" <?php echo $app['status'] == 'rejected' ? 'selected' : ''; ?>>ปฏิเสธ/ยกเลิก</option>
                                                </select>
                                            </div>
                                            <button type="submit" name="update_status"
                                                class="btn btn-primary w-100">บันทึกสถานะ</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="text-center py-4">ไม่พบข้อมูลการสมัครสินเชื่อ</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    </div>
</div>


<?php require_once 'includes/footer.php'; ?>
