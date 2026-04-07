<?php
/**
 * AdminPermission Helper Class
 * ตรวจสอบและจัดการสิทธิ์ผู้ใช้งาน
 */

class AdminPermission
{
    private $db;
    private $userId;
    private $roleId;
    private $permissions = [];
    private $menuItems = [];

    public function __construct($db, $userId = null)
    {
        $this->db = $db;
        if ($userId) {
            $this->userId = $userId;
            $this->loadUserRole();
            $this->loadPermissions();
        }
    }

    /**
     * โหลด role ของ user
     */
    private function loadUserRole()
    {
        $stmt = $this->db->prepare("SELECT role_id FROM admins WHERE id = ?");
        $stmt->execute([$this->userId]);
        $this->roleId = $stmt->fetchColumn();
    }

    /**
     * โหลดสิทธิ์ทั้งหมดของ user ลง array
     */
    private function loadPermissions()
    {
        if (!$this->roleId)
            return;

        $sql = "SELECT p.module, p.action, p.label, p.menu_icon, p.menu_order
                FROM admin_permissions p
                INNER JOIN role_permissions rp ON p.id = rp.permission_id
                WHERE rp.role_id = ?
                ORDER BY p.menu_order";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$this->roleId]);

        while ($row = $stmt->fetch()) {
            $key = $row['module'] . '.' . $row['action'];
            $this->permissions[$key] = true;

            // เก็บ menu items (เฉพาะ action = view)
            if ($row['action'] === 'view' && $row['menu_icon']) {
                $this->menuItems[$row['module']] = [
                    'label' => $row['label'],
                    'icon' => $row['menu_icon'],
                    'order' => $row['menu_order']
                ];
            }
        }
    }

    /**
     * ตรวจสอบว่ามีสิทธิ์หรือไม่
     */
    public function hasPermission($module, $action = 'view')
    {
        $key = $module . '.' . $action;
        return isset($this->permissions[$key]);
    }

    /**
     * ตรวจสอบสิทธิ์ดู
     */
    public function canView($module)
    {
        return $this->hasPermission($module, 'view');
    }

    /**
     * ตรวจสอบสิทธิ์เพิ่ม
     */
    public function canCreate($module)
    {
        return $this->hasPermission($module, 'create');
    }

    /**
     * ตรวจสอบสิทธิ์แก้ไข
     */
    public function canUpdate($module)
    {
        return $this->hasPermission($module, 'update');
    }

    /**
     * ตรวจสอบสิทธิ์ลบ
     */
    public function canDelete($module)
    {
        return $this->hasPermission($module, 'delete');
    }

    /**
     * ดึงรายการเมนูที่ user มีสิทธิ์เห็น
     */
    public function getMenuItems()
    {
        return $this->menuItems;
    }

    /**
     * ตรวจสอบสิทธิ์และ redirect ถ้าไม่มี
     */
    public function requirePermission($module, $action = 'view')
    {
        if (!$this->hasPermission($module, $action)) {
            $_SESSION['error_message'] = 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้';
            header('Location: index.php');
            exit;
        }
    }

    /**
     * ดึง role ทั้งหมด
     */
    public function getAllRoles()
    {
        $stmt = $this->db->query("SELECT * FROM admin_roles ORDER BY id");
        return $stmt->fetchAll();
    }

    /**
     * ดึง permissions ทั้งหมด (สำหรับหน้า role form)
     */
    public function getAllPermissions()
    {
        $stmt = $this->db->query("SELECT * FROM admin_permissions ORDER BY menu_order");
        return $stmt->fetchAll();
    }

    /**
     * ดึง permissions ของ role (สำหรับหน้า role form)
     */
    public function getRolePermissions($roleId)
    {
        $stmt = $this->db->prepare("SELECT permission_id FROM role_permissions WHERE role_id = ?");
        $stmt->execute([$roleId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * จัดกลุ่ม permissions ตาม module
     */
    public function getPermissionsGroupedByModule()
    {
        $all = $this->getAllPermissions();
        $grouped = [];

        foreach ($all as $perm) {
            $module = $perm['module'];
            if (!isset($grouped[$module])) {
                $grouped[$module] = [
                    'label' => $this->getModuleLabel($module),
                    'permissions' => []
                ];
            }
            $grouped[$module]['permissions'][] = $perm;
        }

        return $grouped;
    }

    /**
     * ชื่อ module เป็นภาษาไทย
     */
    private function getModuleLabel($module)
    {
        $labels = [
            'dashboard' => 'ภาพรวม',
            'banners' => 'แบนเนอร์',
            'services' => 'บริการ',
            'pages' => 'เนื้อหา',
            'announcements' => 'ข่าวสาร',
            'loan_applications' => 'ผู้สมัครสินเชื่อ',
            'auction_cars' => 'รถประมูล',
            'auction_schedules' => 'ตารางประมูล',
            'used_cars' => 'รถสวยพร้อมขาย',
            'properties' => 'บ้านคอนโดที่ดิน',
            'property_leads' => 'ผู้สนใจบ้านคอนโดที่ดิน',
            'branches' => 'สาขา',
            'careers' => 'ตำแหน่งงาน',
            'financials' => 'ข้อมูลทางการเงิน',
            'publications' => 'เอกสารดาวน์โหลด',
            'contact_messages' => 'ข้อความติดต่อ',
            'settings' => 'ตั้งค่าเว็บไซต์',
            'users' => 'จัดการผู้ใช้',
            'roles' => 'กลุ่มผู้ใช้'
        ];
        return isset($labels[$module]) ? $labels[$module] : $module;
    }

    /**
     * ชื่อ action เป็นภาษาไทย
     */
    public static function getActionLabel($action)
    {
        $labels = [
            'view' => 'ดู',
            'create' => 'เพิ่ม',
            'update' => 'แก้ไข',
            'delete' => 'ลบ'
        ];
        return isset($labels[$action]) ? $labels[$action] : $action;
    }
}
?>