<?php
session_start();
require_once 'config/db.php';
require_once 'includes/AdminPermission.php';

$database = new Database();
$db = $database->getConnection();

// ตรวจสอบสิทธิ์
$perm = new AdminPermission($db, $_SESSION['admin_id'] ?? null);

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$isEdit = $id > 0;

// ตรวจสอบสิทธิ์ตามโหมด
if ($isEdit) {
    $perm->requirePermission('roles', 'update');
} else {
    $perm->requirePermission('roles', 'create');
}

$role = [
    'name' => '',
    'description' => ''
];
$selectedPermissions = [];
$error = '';
$success = '';

// ถ้าเป็นโหมดแก้ไข ดึงข้อมูลเดิม
if ($isEdit) {
    $stmt = $db->prepare("SELECT * FROM admin_roles WHERE id = ?");
    $stmt->execute([$id]);
    $role = $stmt->fetch();
    if (!$role) {
        header("Location: roles.php");
        exit;
    }
    $selectedPermissions = $perm->getRolePermissions($id);
}

// ดึง permissions ทั้งหมด จัดกลุ่มตาม module
$groupedPermissions = $perm->getPermissionsGroupedByModule();

// บันทึกข้อมูล
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $permissions = isset($_POST['permissions']) ? $_POST['permissions'] : [];

    // Validation
    if (empty($name)) {
        $error = 'กรุณากรอกชื่อกลุ่ม';
    } else {
        // ตรวจสอบชื่อซ้ำ
        $checkSql = "SELECT id FROM admin_roles WHERE name = ? AND id != ?";
        $checkStmt = $db->prepare($checkSql);
        $checkStmt->execute([$name, $id]);
        if ($checkStmt->fetch()) {
            $error = 'ชื่อกลุ่มนี้มีอยู่แล้ว';
        } else {
            try {
                $db->beginTransaction();

                if ($isEdit) {
                    // Update role
                    $sql = "UPDATE admin_roles SET name = ?, description = ? WHERE id = ?";
                    $stmt = $db->prepare($sql);
                    $stmt->execute([$name, $description, $id]);

                    // ลบ permissions เดิม
                    $db->prepare("DELETE FROM role_permissions WHERE role_id = ?")->execute([$id]);

                    $roleId = $id;
                } else {
                    // Insert role
                    $sql = "INSERT INTO admin_roles (name, description) VALUES (?, ?)";
                    $stmt = $db->prepare($sql);
                    $stmt->execute([$name, $description]);
                    $roleId = $db->lastInsertId();
                }

                // เพิ่ม permissions ใหม่
                if (!empty($permissions)) {
                    $insertStmt = $db->prepare("INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)");
                    foreach ($permissions as $permId) {
                        $insertStmt->execute([$roleId, (int) $permId]);
                    }
                }

                $db->commit();

                if ($isEdit) {
                    $success = 'อัพเดทข้อมูลสำเร็จ';
                    // รีโหลดข้อมูล
                    $stmt = $db->prepare("SELECT * FROM admin_roles WHERE id = ?");
                    $stmt->execute([$id]);
                    $role = $stmt->fetch();
                    $selectedPermissions = $perm->getRolePermissions($id);
                } else {
                    header("Location: roles.php?added=1");
                    exit;
                }

            } catch (PDOException $e) {
                $db->rollBack();
                $error = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
            }
        }
    }

    // เก็บค่าที่กรอกไว้
    $role['name'] = $name;
    $role['description'] = $description;
    $selectedPermissions = $permissions;
}

include 'includes/header.php';
?>

<style>
    .permission-module {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 15px;
    }

    .permission-module h6 {
        color: #333;
        margin-bottom: 10px;
        padding-bottom: 8px;
        border-bottom: 1px solid #ddd;
    }

    .permission-checkbox {
        display: inline-flex;
        align-items: center;
        margin-right: 20px;
        margin-bottom: 5px;
    }

    .permission-checkbox input {
        margin-right: 5px;
    }

    .select-all-btn {
        font-size: 0.8rem;
        padding: 2px 8px;
    }
</style>

<div class="page-header d-flex justify-content-between align-items-center">
    <h1 class="page-title">
        <?php echo $isEdit ? 'แก้ไขกลุ่มผู้ใช้' : 'เพิ่มกลุ่มใหม่'; ?>
    </h1>
    <a href="roles.php" class="btn btn-outline-secondary">
        <i class="fa-solid fa-arrow-left"></i> กลับ
    </a>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger">
        <i class="fa-solid fa-exclamation-circle"></i>
        <?php echo $error; ?>
    </div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="alert alert-success">
        <i class="fa-solid fa-check-circle"></i>
        <?php echo $success; ?>
    </div>
<?php endif; ?>

<form method="POST">
    <div class="card mb-4">
        <div class="card-header">
            <strong>ข้อมูลกลุ่ม</strong>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="name" class="form-label">ชื่อกลุ่ม <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="name" name="name"
                        value="<?php echo htmlspecialchars($role['name']); ?>" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="description" class="form-label">คำอธิบาย</label>
                    <input type="text" class="form-control" id="description" name="description"
                        value="<?php echo htmlspecialchars($role['description'] ?? ''); ?>">
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <strong>กำหนดสิทธิ์การใช้งาน</strong>
            <div>
                <button type="button" class="btn btn-sm btn-outline-primary" onclick="selectAll()">
                    <i class="fa-solid fa-check-double"></i> เลือกทั้งหมด
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="deselectAll()">
                    <i class="fa-solid fa-times"></i> ยกเลิกทั้งหมด
                </button>
            </div>
        </div>
        <div class="card-body">
            <?php foreach ($groupedPermissions as $module => $data): ?>
                <div class="permission-module">
                    <h6>
                        <i class="fa-solid fa-folder"></i>
                        <?php echo htmlspecialchars($data['label']); ?>
                        <button type="button" class="btn btn-sm btn-outline-secondary select-all-btn ms-2"
                            onclick="toggleModule('<?php echo $module; ?>')">
                            เลือกทั้งหมด
                        </button>
                    </h6>
                    <div>
                        <?php foreach ($data['permissions'] as $permission): ?>
                            <label class="permission-checkbox">
                                <input type="checkbox" name="permissions[]" value="<?php echo $permission['id']; ?>"
                                    class="perm-checkbox module-<?php echo $module; ?>" <?php echo in_array($permission['id'], $selectedPermissions) ? 'checked' : ''; ?>>
                                <?php echo AdminPermission::getActionLabel($permission['action']); ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="d-flex gap-2 mb-4">
        <button type="submit" class="btn btn-primary">
            <i class="fa-solid fa-save"></i>
            <?php echo $isEdit ? 'บันทึกการแก้ไข' : 'เพิ่มกลุ่ม'; ?>
        </button>
        <a href="roles.php" class="btn btn-outline-secondary">ยกเลิก</a>
    </div>
</form>

<script>
    function selectAll() {
        document.querySelectorAll('.perm-checkbox').forEach(cb => cb.checked = true);
    }

    function deselectAll() {
        document.querySelectorAll('.perm-checkbox').forEach(cb => cb.checked = false);
    }

    function toggleModule(module) {
        const checkboxes = document.querySelectorAll('.module-' + module);
        const allChecked = Array.from(checkboxes).every(cb => cb.checked);
        checkboxes.forEach(cb => cb.checked = !allChecked);
    }
</script>

<?php include 'includes/footer.php'; ?>