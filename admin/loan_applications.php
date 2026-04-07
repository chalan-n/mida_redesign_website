<?php
session_start();
require_once 'config/db.php';
require_once 'includes/header.php';

$database = new Database();
$db = $database->getConnection();

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
?>

<h1 class="mt-4">ผู้สมัครสินเชื่อ</h1>

<div class="card mb-4">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <div><i class="fas fa-table me-1"></i> รายการผู้สมัครสินเชื่อ</div>
            <div class="btn-group">
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
    <div class="card-body">
        <table class="table table-bordered table-striped table-hover">
            <thead>
                <tr>
                    <th width="5%">ID</th>
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
                    <?php foreach ($applications as $app): ?>
                        <tr>
                            <td>
                                <?php echo $app['id']; ?>
                            </td>
                            <td>
                                <?php echo date('d/m/Y H:i', strtotime($app['created_at'])); ?>
                            </td>
                            <td>
                                <?php echo getLoanTypeLabel($app['loan_type']); ?>
                            </td>
                            <td>
                                <strong>
                                    <?php echo htmlspecialchars($app['name']); ?>
                                </strong><br>
                                <small><i class="fas fa-phone"></i>
                                    <?php echo htmlspecialchars($app['phone']); ?>
                                </small>
                                <?php if ($app['line_id']): ?>
                                    <br><small><i class="fab fa-line text-success"></i>
                                        <?php echo htmlspecialchars($app['line_id']); ?>
                                    </small>
                                <?php endif; ?>
                            </td>
                            <td>
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
                            <td>
                                <?php echo $app['loan_amount'] ? number_format($app['loan_amount']) : '-'; ?>
                            </td>
                            <td>
                                <?php echo getStatusBadge($app['status']); ?>
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm btn-info text-white" data-bs-toggle="modal"
                                    data-bs-target="#viewModal<?php echo $app['id']; ?>">
                                    <i class="fas fa-eye"></i> ดู
                                </button>
                            </td>
                        </tr>

                        <!-- View Modal -->
                        <div class="modal fade" id="viewModal<?php echo $app['id']; ?>" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">รายละเอียดการสมัคร #
                                            <?php echo $app['id']; ?>
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
                                            <input type="hidden" name="id" value="<?php echo $app['id']; ?>">
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


<?php require_once 'includes/footer.php'; ?>