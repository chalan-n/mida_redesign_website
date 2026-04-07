<?php
/**
 * Migration Script: Create User Management Tables
 * ระบบจัดการผู้ใช้พร้อม Role-Based Access Control
 * 
 * รันไฟล์นี้ครั้งเดียวเพื่อสร้างตารางและข้อมูลเริ่มต้น
 */

require_once 'config/db.php';

$database = new Database();
$db = $database->getConnection();

$messages = [];

try {
    // 1. สร้างตาราง admin_roles
    $sql = "CREATE TABLE IF NOT EXISTS admin_roles (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(50) NOT NULL UNIQUE,
        description VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    $db->exec($sql);
    $messages[] = "✅ สร้างตาราง admin_roles สำเร็จ";

    // 2. สร้างตาราง admin_permissions
    $sql = "CREATE TABLE IF NOT EXISTS admin_permissions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        module VARCHAR(50) NOT NULL,
        action VARCHAR(20) NOT NULL,
        label VARCHAR(100),
        menu_icon VARCHAR(50),
        menu_order INT DEFAULT 0,
        UNIQUE KEY unique_perm (module, action)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    $db->exec($sql);
    $messages[] = "✅ สร้างตาราง admin_permissions สำเร็จ";

    // 3. สร้างตาราง role_permissions (Many-to-Many)
    $sql = "CREATE TABLE IF NOT EXISTS role_permissions (
        role_id INT NOT NULL,
        permission_id INT NOT NULL,
        PRIMARY KEY (role_id, permission_id),
        FOREIGN KEY (role_id) REFERENCES admin_roles(id) ON DELETE CASCADE,
        FOREIGN KEY (permission_id) REFERENCES admin_permissions(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    $db->exec($sql);
    $messages[] = "✅ สร้างตาราง role_permissions สำเร็จ";

    // 4. เพิ่ม column role_id ในตาราง admins (ถ้ายังไม่มี)
    $checkColumn = $db->query("SHOW COLUMNS FROM admins LIKE 'role_id'");
    if ($checkColumn->rowCount() == 0) {
        $db->exec("ALTER TABLE admins ADD COLUMN role_id INT AFTER role");
        $messages[] = "✅ เพิ่ม column role_id ในตาราง admins สำเร็จ";
    } else {
        $messages[] = "ℹ️ column role_id มีอยู่แล้ว";
    }

    // 5. เพิ่ม column is_active ในตาราง admins (ถ้ายังไม่มี)
    $checkColumn = $db->query("SHOW COLUMNS FROM admins LIKE 'is_active'");
    if ($checkColumn->rowCount() == 0) {
        $db->exec("ALTER TABLE admins ADD COLUMN is_active TINYINT(1) DEFAULT 1");
        $messages[] = "✅ เพิ่ม column is_active ในตาราง admins สำเร็จ";
    } else {
        $messages[] = "ℹ️ column is_active มีอยู่แล้ว";
    }

    // 6. เพิ่ม Default Roles
    $roles = [
        ['Super Admin', 'สิทธิ์เต็มทุกฟีเจอร์'],
        ['Content Editor', 'จัดการเนื้อหาเว็บไซต์'],
        ['Viewer', 'ดูอย่างเดียว ไม่สามารถแก้ไขได้']
    ];

    $stmt = $db->prepare("INSERT IGNORE INTO admin_roles (name, description) VALUES (?, ?)");
    foreach ($roles as $role) {
        $stmt->execute($role);
    }
    $messages[] = "✅ เพิ่ม Default Roles สำเร็จ";

    // 7. เพิ่ม Default Permissions
    $permissions = [
        // Dashboard
        ['dashboard', 'view', 'ภาพรวม (Dashboard)', 'fa-gauge', 1],

        // Content Management
        ['banners', 'view', 'ดูแบนเนอร์', 'fa-images', 10],
        ['banners', 'create', 'เพิ่มแบนเนอร์', null, 11],
        ['banners', 'update', 'แก้ไขแบนเนอร์', null, 12],
        ['banners', 'delete', 'ลบแบนเนอร์', null, 13],

        ['services', 'view', 'ดูบริการ', 'fa-layer-group', 20],
        ['services', 'create', 'เพิ่มบริการ', null, 21],
        ['services', 'update', 'แก้ไขบริการ', null, 22],
        ['services', 'delete', 'ลบบริการ', null, 23],

        ['pages', 'view', 'ดูเนื้อหา', 'fa-file-contract', 30],
        ['pages', 'create', 'เพิ่มเนื้อหา', null, 31],
        ['pages', 'update', 'แก้ไขเนื้อหา', null, 32],
        ['pages', 'delete', 'ลบเนื้อหา', null, 33],

        ['announcements', 'view', 'ดูข่าวสาร', 'fa-bullhorn', 40],
        ['announcements', 'create', 'เพิ่มข่าวสาร', null, 41],
        ['announcements', 'update', 'แก้ไขข่าวสาร', null, 42],
        ['announcements', 'delete', 'ลบข่าวสาร', null, 43],

        // Data Management
        ['loan_applications', 'view', 'ดูผู้สมัครสินเชื่อ', 'fa-money-check-dollar', 50],
        ['loan_applications', 'update', 'จัดการผู้สมัครสินเชื่อ', null, 51],
        ['loan_applications', 'delete', 'ลบผู้สมัครสินเชื่อ', null, 52],

        ['auction_cars', 'view', 'ดูรถประมูล', 'fa-car', 60],
        ['auction_cars', 'create', 'เพิ่มรถประมูล', null, 61],
        ['auction_cars', 'update', 'แก้ไขรถประมูล', null, 62],
        ['auction_cars', 'delete', 'ลบรถประมูล', null, 63],

        ['auction_schedules', 'view', 'ดูตารางประมูล', 'fa-calendar-days', 70],
        ['auction_schedules', 'create', 'เพิ่มตารางประมูล', null, 71],
        ['auction_schedules', 'update', 'แก้ไขตารางประมูล', null, 72],
        ['auction_schedules', 'delete', 'ลบตารางประมูล', null, 73],

        ['properties', 'view', 'ดูทรัพย์สินรอขาย', 'fa-house-chimney', 80],
        ['properties', 'create', 'เพิ่มทรัพย์สิน', null, 81],
        ['properties', 'update', 'แก้ไขทรัพย์สิน', null, 82],
        ['properties', 'delete', 'ลบทรัพย์สิน', null, 83],

        ['property_leads', 'view', 'ดูผู้สนใจทรัพย์สิน', 'fa-address-book', 90],
        ['property_leads', 'update', 'จัดการผู้สนใจทรัพย์สิน', null, 91],
        ['property_leads', 'delete', 'ลบผู้สนใจทรัพย์สิน', null, 92],

        ['branches', 'view', 'ดูสาขา', 'fa-map-location-dot', 100],
        ['branches', 'create', 'เพิ่มสาขา', null, 101],
        ['branches', 'update', 'แก้ไขสาขา', null, 102],
        ['branches', 'delete', 'ลบสาขา', null, 103],

        ['careers', 'view', 'ดูตำแหน่งงาน', 'fa-briefcase', 110],
        ['careers', 'create', 'เพิ่มตำแหน่งงาน', null, 111],
        ['careers', 'update', 'แก้ไขตำแหน่งงาน', null, 112],
        ['careers', 'delete', 'ลบตำแหน่งงาน', null, 113],

        // Investor Relations
        ['financials', 'view', 'ดูข้อมูลทางการเงิน', 'fa-file-invoice-dollar', 120],
        ['financials', 'create', 'เพิ่มข้อมูลทางการเงิน', null, 121],
        ['financials', 'update', 'แก้ไขข้อมูลทางการเงิน', null, 122],
        ['financials', 'delete', 'ลบข้อมูลทางการเงิน', null, 123],

        ['publications', 'view', 'ดูเอกสารดาวน์โหลด', 'fa-file-arrow-down', 130],
        ['publications', 'create', 'เพิ่มเอกสาร', null, 131],
        ['publications', 'update', 'แก้ไขเอกสาร', null, 132],
        ['publications', 'delete', 'ลบเอกสาร', null, 133],

        // System
        ['contact_messages', 'view', 'ดูข้อความติดต่อ', 'fa-envelope', 140],
        ['contact_messages', 'delete', 'ลบข้อความติดต่อ', null, 141],

        ['settings', 'view', 'ดูตั้งค่าเว็บไซต์', 'fa-cog', 150],
        ['settings', 'update', 'แก้ไขตั้งค่าเว็บไซต์', null, 151],

        // User Management
        ['users', 'view', 'ดูรายชื่อผู้ใช้', 'fa-users', 200],
        ['users', 'create', 'เพิ่มผู้ใช้', null, 201],
        ['users', 'update', 'แก้ไขผู้ใช้', null, 202],
        ['users', 'delete', 'ลบผู้ใช้', null, 203],

        ['roles', 'view', 'ดูกลุ่มผู้ใช้', 'fa-user-shield', 210],
        ['roles', 'create', 'เพิ่มกลุ่มผู้ใช้', null, 211],
        ['roles', 'update', 'แก้ไขกลุ่มผู้ใช้', null, 212],
        ['roles', 'delete', 'ลบกลุ่มผู้ใช้', null, 213],
    ];

    $stmt = $db->prepare("INSERT IGNORE INTO admin_permissions (module, action, label, menu_icon, menu_order) VALUES (?, ?, ?, ?, ?)");
    foreach ($permissions as $perm) {
        $stmt->execute($perm);
    }
    $messages[] = "✅ เพิ่ม Default Permissions สำเร็จ";

    // 8. กำหนดสิทธิ์ทั้งหมดให้ Super Admin
    $superAdminId = $db->query("SELECT id FROM admin_roles WHERE name = 'Super Admin'")->fetchColumn();
    $allPermIds = $db->query("SELECT id FROM admin_permissions")->fetchAll(PDO::FETCH_COLUMN);

    $stmt = $db->prepare("INSERT IGNORE INTO role_permissions (role_id, permission_id) VALUES (?, ?)");
    foreach ($allPermIds as $permId) {
        $stmt->execute([$superAdminId, $permId]);
    }
    $messages[] = "✅ กำหนดสิทธิ์ทั้งหมดให้ Super Admin สำเร็จ";

    // 9. กำหนดสิทธิ์ view ทั้งหมดให้ Viewer
    $viewerId = $db->query("SELECT id FROM admin_roles WHERE name = 'Viewer'")->fetchColumn();
    $viewPermIds = $db->query("SELECT id FROM admin_permissions WHERE action = 'view'")->fetchAll(PDO::FETCH_COLUMN);

    foreach ($viewPermIds as $permId) {
        $stmt->execute([$viewerId, $permId]);
    }
    $messages[] = "✅ กำหนดสิทธิ์ view ให้ Viewer สำเร็จ";

    // 10. อัพเดท user เดิมให้ใช้ Super Admin role
    $db->exec("UPDATE admins SET role_id = $superAdminId WHERE role_id IS NULL");
    $messages[] = "✅ อัพเดท user เดิมให้ใช้ Super Admin role สำเร็จ";

    echo "<h2>🎉 Migration สำเร็จ!</h2>";
    foreach ($messages as $msg) {
        echo "<p>$msg</p>";
    }
    echo "<p><a href='index.php'>กลับหน้า Dashboard</a></p>";

} catch (PDOException $e) {
    echo "<h2>❌ เกิดข้อผิดพลาด</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
    foreach ($messages as $msg) {
        echo "<p>$msg</p>";
    }
}
?>