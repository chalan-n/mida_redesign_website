<?php
session_start();
require_once 'config/db.php';
require_once 'includes/AdminPermission.php';

$database = new Database();
$db = $database->getConnection();

// ตรวจสอบสิทธิ์
$perm = new AdminPermission($db, $_SESSION['admin_id'] ?? null);
$perm->requirePermission('roles', 'view');

// ลบ role
if (isset($_GET['delete']) && $perm->canDelete('roles')) {
    $id = (int) $_GET['delete'];

    // ตรวจสอบว่ามี user ใช้ role นี้อยู่หรือไม่
    $checkStmt = $db->prepare("SELECT COUNT(*) FROM admins WHERE role_id = ?");
    $checkStmt->execute([$id]);
    $userCount = $checkStmt->fetchColumn();

    if ($userCount > 0) {
        $error = "ไม่สามารถลบได้ เนื่องจากมีผู้ใช้ $userCount คนใช้กลุ่มนี้อยู่";
    } else {
        $stmt = $db->prepare("DELETE FROM admin_roles WHERE id = ?");
        $stmt->execute([$id]);
        header("Location: roles.php?deleted=1");
        exit;
    }
}

// ดึงรายการ roles พร้อมจำนวน users
$sql = "SELECT r.*, COUNT(a.id) as user_count 
        FROM admin_roles r 
        LEFT JOIN admins a ON r.id = a.role_id 
        GROUP BY r.id 
        ORDER BY r.id";
$roles = $db->query($sql)->fetchAll();

include 'includes/header.php';
?>

<div class="page-header d-flex justify-content-between align-items-center">
    <h1 class="page-title">จัดการกลุ่มผู้ใช้งาน</h1>
    <?php if ($perm->canCreate('roles')): ?>
        <a href="role_form.php" class="btn btn-primary">
            <i class="fa-solid fa-plus"></i> เพิ่มกลุ่มใหม่
        </a>
    <?php endif; ?>
</div>

<?php if (isset($_GET['deleted'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fa-solid fa-check-circle"></i> ลบกลุ่มผู้ใช้สำเร็จ
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
                    <th>ชื่อกลุ่ม</th>
                    <th>คำอธิบาย</th>
                    <th style="width: 120px;">จำนวนผู้ใช้</th>
                    <th style="width: 150px;">จัดการ</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($roles)): ?>
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">
                            <i class="fa-solid fa-user-shield fa-2x mb-2"></i><br>
                            ยังไม่มีกลุ่มผู้ใช้
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($roles as $role): ?>
                        <tr>
                            <td>
                                <?php echo $role['id']; ?>
                            </td>
                            <td>
                                <strong>
                                    <?php echo htmlspecialchars($role['name']); ?>
                                </strong>
                            </td>
                            <td>
                                <span class="text-muted">
                                    <?php echo htmlspecialchars($role['description'] ?? '-'); ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-secondary">
                                    <?php echo $role['user_count']; ?> คน
                                </span>
                            </td>
                            <td>
                                <?php if ($perm->canUpdate('roles')): ?>
                                    <a href="role_form.php?id=<?php echo $role['id']; ?>" class="btn btn-sm btn-outline-primary"
                                        title="แก้ไข">
                                        <i class="fa-solid fa-edit"></i>
                                    </a>
                                <?php endif; ?>

                                <?php if ($perm->canDelete('roles') && $role['user_count'] == 0): ?>
                                    <a href="roles.php?delete=<?php echo $role['id']; ?>" class="btn btn-sm btn-outline-danger"
                                        title="ลบ" onclick="return confirm('คุณแน่ใจหรือไม่ที่จะลบกลุ่มนี้?');">
                                        <i class="fa-solid fa-trash"></i>
                                    </a>
                                <?php elseif ($perm->canDelete('roles')): ?>
                                    <button class="btn btn-sm btn-outline-secondary" disabled title="มีผู้ใช้ในกลุ่มนี้">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
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