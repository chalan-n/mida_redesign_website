<?php
session_start();
require_once 'config/db.php';
require_once 'includes/AdminPermission.php';

$database = new Database();
$db = $database->getConnection();

// ตรวจสอบสิทธิ์
$perm = new AdminPermission($db, $_SESSION['admin_id'] ?? null);
$perm->requirePermission('users', 'view');

// ลบ user
if (isset($_GET['delete']) && $perm->canDelete('users')) {
    $id = (int) $_GET['delete'];
    // ห้ามลบตัวเอง
    if ($id == $_SESSION['admin_id']) {
        $error = "ไม่สามารถลบตัวเองได้";
    } else {
        $stmt = $db->prepare("DELETE FROM admins WHERE id = ?");
        $stmt->execute([$id]);
        header("Location: users.php?deleted=1");
        exit;
    }
}

// ดึงรายการ users
$sql = "SELECT a.*, r.name as role_name 
        FROM admins a 
        LEFT JOIN admin_roles r ON a.role_id = r.id 
        ORDER BY a.id";
$users = $db->query($sql)->fetchAll();

include 'includes/header.php';
?>

<div class="page-header d-flex justify-content-between align-items-center">
    <h1 class="page-title">จัดการผู้ใช้งาน</h1>
    <?php if ($perm->canCreate('users')): ?>
        <a href="user_form.php" class="btn btn-primary">
            <i class="fa-solid fa-plus"></i> เพิ่มผู้ใช้ใหม่
        </a>
    <?php endif; ?>
</div>

<?php if (isset($_GET['deleted'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fa-solid fa-check-circle"></i> ลบผู้ใช้สำเร็จ
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (isset($error)): ?>
    <div class="alert alert-danger">
        <i class="fa-solid fa-exclamation-circle"></i>
        <?php echo $error; ?>
    </div>
<?php endif; ?>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width: 60px;">ID</th>
                    <th>ชื่อผู้ใช้</th>
                    <th>ชื่อ-นามสกุล</th>
                    <th>กลุ่มผู้ใช้</th>
                    <th>สถานะ</th>
                    <th>เข้าสู่ระบบล่าสุด</th>
                    <th style="width: 150px;">จัดการ</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">
                            <i class="fa-solid fa-users fa-2x mb-2"></i><br>
                            ยังไม่มีผู้ใช้งาน
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td>
                                <?php echo $user['id']; ?>
                            </td>
                            <td>
                                <strong>
                                    <?php echo htmlspecialchars($user['username']); ?>
                                </strong>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($user['name']); ?>
                            </td>
                            <td>
                                <span class="badge bg-primary">
                                    <?php echo htmlspecialchars($user['role_name'] ?? 'ไม่ระบุ'); ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($user['is_active'] ?? 1): ?>
                                    <span class="badge bg-success">ใช้งาน</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">ปิดใช้งาน</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                if ($user['last_login']) {
                                    echo date('d/m/Y H:i', strtotime($user['last_login']));
                                } else {
                                    echo '<span class="text-muted">ยังไม่เคยเข้าสู่ระบบ</span>';
                                }
                                ?>
                            </td>
                            <td>
                                <?php if ($perm->canUpdate('users')): ?>
                                    <a href="user_form.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-outline-primary"
                                        title="แก้ไข">
                                        <i class="fa-solid fa-edit"></i>
                                    </a>
                                <?php endif; ?>

                                <?php if ($perm->canDelete('users') && $user['id'] != $_SESSION['admin_id']): ?>
                                    <a href="users.php?delete=<?php echo $user['id']; ?>" class="btn btn-sm btn-outline-danger"
                                        title="ลบ" onclick="return confirm('คุณแน่ใจหรือไม่ที่จะลบผู้ใช้นี้?');">
                                        <i class="fa-solid fa-trash"></i>
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>