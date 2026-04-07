<?php
session_start();
require_once 'config/db.php';
require_once 'includes/AdminPermission.php';

$database = new Database();
$db = $database->getConnection();

// ตรวจสอบสิทธิ์
$perm = new AdminPermission($db, $_SESSION['admin_id'] ?? null);

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$isEdit = $id > 0;

// ตรวจสอบสิทธิ์ตามโหมด
if ($isEdit) {
    $perm->requirePermission('users', 'update');
} else {
    $perm->requirePermission('users', 'create');
}

$user = [
    'username' => '',
    'name' => '',
    'role_id' => '',
    'is_active' => 1
];
$error = '';
$success = '';

// ถ้าเป็นโหมดแก้ไข ดึงข้อมูลเดิม
if ($isEdit) {
    $stmt = $db->prepare("SELECT * FROM admins WHERE id = ?");
    $stmt->execute([$id]);
    $user = $stmt->fetch();
    if (!$user) {
        header("Location: users.php");
        exit;
    }
}

// ดึงรายการ roles
$roles = $perm->getAllRoles();

// บันทึกข้อมูล
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $name = trim($_POST['name']);
    $password = $_POST['password'];
    $role_id = (int)$_POST['role_id'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    // Validation
    if (empty($username) || empty($name) || empty($role_id)) {
        $error = 'กรุณากรอกข้อมูลให้ครบถ้วน';
    } elseif (!$isEdit && empty($password)) {
        $error = 'กรุณากรอกรหัสผ่าน';
    } else {
        // ตรวจสอบ username ซ้ำ
        $checkSql = "SELECT id FROM admins WHERE username = ? AND id != ?";
        $checkStmt = $db->prepare($checkSql);
        $checkStmt->execute([$username, $id]);
        if ($checkStmt->fetch()) {
            $error = 'ชื่อผู้ใช้นี้มีอยู่แล้ว';
        } else {
            try {
                if ($isEdit) {
                    // Update
                    if (!empty($password)) {
                        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                        $sql = "UPDATE admins SET username = ?, name = ?, password = ?, role_id = ?, is_active = ? WHERE id = ?";
                        $stmt = $db->prepare($sql);
                        $stmt->execute([$username, $name, $hashedPassword, $role_id, $is_active, $id]);
                    } else {
                        $sql = "UPDATE admins SET username = ?, name = ?, role_id = ?, is_active = ? WHERE id = ?";
                        $stmt = $db->prepare($sql);
                        $stmt->execute([$username, $name, $role_id, $is_active, $id]);
                    }
                    $success = 'อัพเดทข้อมูลสำเร็จ';
                    
                    // รีโหลดข้อมูล
                    $stmt = $db->prepare("SELECT * FROM admins WHERE id = ?");
                    $stmt->execute([$id]);
                    $user = $stmt->fetch();
                } else {
                    // Insert
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    $sql = "INSERT INTO admins (username, name, password, role_id, is_active) VALUES (?, ?, ?, ?, ?)";
                    $stmt = $db->prepare($sql);
                    $stmt->execute([$username, $name, $hashedPassword, $role_id, $is_active]);
                    header("Location: users.php?added=1");
                    exit;
                }
            } catch (PDOException $e) {
                $error = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
            }
        }
    }
    
    // เก็บค่าที่กรอกไว้
    $user['username'] = $username;
    $user['name'] = $name;
    $user['role_id'] = $role_id;
    $user['is_active'] = $is_active;
}

include 'includes/header.php';
?>

<div class="page-header d-flex justify-content-between align-items-center">
    <h1 class="page-title">
        <?php echo $isEdit ? 'แก้ไขผู้ใช้งาน' : 'เพิ่มผู้ใช้ใหม่'; ?>
    </h1>
    <a href="users.php" class="btn btn-outline-secondary">
        <i class="fa-solid fa-arrow-left"></i> กลับ
    </a>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger">
        <i class="fa-solid fa-exclamation-circle"></i> <?php echo $error; ?>
    </div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="alert alert-success">
        <i class="fa-solid fa-check-circle"></i> <?php echo $success; ?>
    </div>
<?php endif; ?>

<div class="card">
    <form method="POST" class="p-4">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="username" class="form-label">ชื่อผู้ใช้ (Username) <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="username" name="username" 
                       value="<?php echo htmlspecialchars($user['username']); ?>" required>
            </div>
            
            <div class="col-md-6 mb-3">
                <label for="name" class="form-label">ชื่อ-นามสกุล <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="name" name="name" 
                       value="<?php echo htmlspecialchars($user['name']); ?>" required>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="password" class="form-label">
                    รหัสผ่าน 
                    <?php if (!$isEdit): ?><span class="text-danger">*</span><?php endif; ?>
                </label>
                <input type="password" class="form-control" id="password" name="password" 
                       <?php echo $isEdit ? '' : 'required'; ?>>
                <?php if ($isEdit): ?>
                    <small class="text-muted">เว้นว่างไว้หากไม่ต้องการเปลี่ยนรหัสผ่าน</small>
                <?php endif; ?>
            </div>
            
            <div class="col-md-6 mb-3">
                <label for="role_id" class="form-label">กลุ่มผู้ใช้ <span class="text-danger">*</span></label>
                <select class="form-select" id="role_id" name="role_id" required>
                    <option value="">-- เลือกกลุ่มผู้ใช้ --</option>
                    <?php foreach ($roles as $role): ?>
                        <option value="<?php echo $role['id']; ?>" 
                                <?php echo ($user['role_id'] == $role['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($role['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="mb-4">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                       <?php echo ($user['is_active'] ?? 1) ? 'checked' : ''; ?>>
                <label class="form-check-label" for="is_active">
                    เปิดใช้งาน
                </label>
            </div>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-save"></i> 
                <?php echo $isEdit ? 'บันทึกการแก้ไข' : 'เพิ่มผู้ใช้'; ?>
            </button>
            <a href="users.php" class="btn btn-outline-secondary">ยกเลิก</a>
        </div>
    </form>
</div>

<?php include 'includes/footer.php'; ?>
