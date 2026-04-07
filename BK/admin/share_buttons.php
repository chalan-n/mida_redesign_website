<?php
session_start();
require_once 'config/db.php';

$database = new Database();
$db = $database->getConnection();

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');

    if ($_POST['action'] === 'toggle') {
        $id = (int) $_POST['id'];
        $is_active = (int) $_POST['is_active'];

        $stmt = $db->prepare("UPDATE share_buttons SET is_active = ? WHERE id = ?");
        if ($stmt->execute([$is_active, $id])) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false]);
        }
        exit;
    }

    if ($_POST['action'] === 'update') {
        $id = (int) $_POST['id'];
        $name = $_POST['name'];
        $icon = $_POST['icon'];
        $color = $_POST['color'];

        $stmt = $db->prepare("UPDATE share_buttons SET name = ?, icon = ?, color = ? WHERE id = ?");
        if ($stmt->execute([$name, $icon, $color, $id])) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false]);
        }
        exit;
    }

    if ($_POST['action'] === 'reorder') {
        $orders = json_decode($_POST['orders'], true);
        foreach ($orders as $order) {
            $stmt = $db->prepare("UPDATE share_buttons SET sort_order = ? WHERE id = ?");
            $stmt->execute([$order['sort'], $order['id']]);
        }
        echo json_encode(['success' => true]);
        exit;
    }
}

require_once 'includes/header.php';

// Fetch all share buttons
$buttons = $db->query("SELECT * FROM share_buttons ORDER BY sort_order ASC")->fetchAll();
?>

<div class="page-header">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <h1 class="page-title">จัดการปุ่มแชร์หน้านี้</h1>
    </div>
</div>

<div class="card">
    <div
        style="margin-bottom: 20px; padding: 15px; background: #e3f2fd; border-radius: 8px; border-left: 4px solid #2196f3;">
        <i class="fa-solid fa-info-circle" style="color: #2196f3;"></i>
        <strong>คำอธิบาย:</strong> ปุ่มแชร์จะแสดงในหน้ารายละเอียดรถประมูลและทรัพย์สิน คลิกสวิตช์เพื่อเปิด/ปิดการแสดงปุ่ม
    </div>

    <table style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="background: #f8f9fa; border-bottom: 2px solid #dee2e6;">
                <th style="padding: 15px; text-align: center; width: 60px;">ลำดับ</th>
                <th style="padding: 15px; text-align: center; width: 80px;">ไอคอน</th>
                <th style="padding: 15px; text-align: left;">ชื่อ</th>
                <th style="padding: 15px; text-align: center; width: 80px;">สี</th>
                <th style="padding: 15px; text-align: center; width: 120px;">สถานะ</th>
                <th style="padding: 15px; text-align: center; width: 100px;">แก้ไข</th>
            </tr>
        </thead>
        <tbody id="buttonList">
            <?php foreach ($buttons as $btn): ?>
                <tr data-id="<?php echo $btn['id']; ?>" style="border-bottom: 1px solid #eee;">
                    <td style="padding: 15px; text-align: center; cursor: move;">
                        <i class="fa-solid fa-grip-vertical" style="color: #ccc;"></i>
                        <span style="margin-left: 5px;">
                            <?php echo $btn['sort_order']; ?>
                        </span>
                    </td>
                    <td style="padding: 15px; text-align: center;">
                        <i class="<?php echo htmlspecialchars($btn['icon']); ?>"
                            style="font-size: 1.8rem; color: <?php echo htmlspecialchars($btn['color']); ?>;"></i>
                    </td>
                    <td style="padding: 15px;">
                        <span class="btn-name">
                            <?php echo htmlspecialchars($btn['name']); ?>
                        </span>
                    </td>
                    <td style="padding: 15px; text-align: center;">
                        <input type="color" value="<?php echo htmlspecialchars($btn['color']); ?>"
                            onchange="updateColor(<?php echo $btn['id']; ?>, this.value)"
                            style="width: 40px; height: 30px; border: none; cursor: pointer;">
                    </td>
                    <td style="padding: 15px; text-align: center;">
                        <label class="switch">
                            <input type="checkbox" <?php echo $btn['is_active'] ? 'checked' : ''; ?>
                            onchange="toggleButton(
                        <?php echo $btn['id']; ?>, this.checked ? 1 : 0)">
                            <span class="slider round"></span>
                        </label>
                    </td>
                    <td style="padding: 15px; text-align: center;">
                        <button onclick="editButton(<?php echo $btn['id']; ?>)"
                            style="background: var(--primary-blue); color: white; border: none; padding: 5px 15px; border-radius: 5px; cursor: pointer;">
                            <i class="fa-solid fa-pen"></i>
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- ตัวอย่างการแสดงผล -->
<div class="card" style="margin-top: 20px;">
    <h3 style="margin-bottom: 15px;">ตัวอย่างการแสดงผล</h3>
    <div style="background: #f8f9fa; padding: 20px; border-radius: 10px; text-align: center;">
        <h4 style="font-size: 1rem; margin-bottom: 15px;">แชร์หน้านี้</h4>
        <div style="display: flex; justify-content: center; gap: 15px;">
            <?php foreach ($buttons as $btn): ?>
                <?php if ($btn['is_active']): ?>
                    <a href="#" style="color: <?php echo htmlspecialchars($btn['color']); ?>; font-size: 1.5rem;"
                        title="<?php echo htmlspecialchars($btn['name']); ?>">
                        <i class="<?php echo htmlspecialchars($btn['icon']); ?>"></i>
                    </a>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<style>
    /* Switch Toggle */
    .switch {
        position: relative;
        display: inline-block;
        width: 50px;
        height: 28px;
    }

    .switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        transition: .3s;
    }

    .slider:before {
        position: absolute;
        content: "";
        height: 20px;
        width: 20px;
        left: 4px;
        bottom: 4px;
        background-color: white;
        transition: .3s;
    }

    input:checked+.slider {
        background-color: #28a745;
    }

    input:checked+.slider:before {
        transform: translateX(22px);
    }

    .slider.round {
        border-radius: 28px;
    }

    .slider.round:before {
        border-radius: 50%;
    }
</style>

<script>
    function toggleButton(id, isActive) {
        fetch('share_buttons.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `action=toggle&id=${id}&is_active=${isActive}`
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                }
            });
    }

    function updateColor(id, color) {
        const row = document.querySelector(`tr[data-id="${id}"]`);
        const name = row.querySelector('.btn-name').textContent;
        const icon = row.querySelector('td:nth-child(2) i').className;

        fetch('share_buttons.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `action=update&id=${id}&name=${encodeURIComponent(name)}&icon=${encodeURIComponent(icon)}&color=${encodeURIComponent(color)}`
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    row.querySelector('td:nth-child(2) i').style.color = color;
                }
            });
    }

    function editButton(id) {
        const row = document.querySelector(`tr[data-id="${id}"]`);
        const name = row.querySelector('.btn-name').textContent;
        const newName = prompt('แก้ไขชื่อปุ่ม:', name);

        if (newName && newName !== name) {
            const icon = row.querySelector('td:nth-child(2) i').className;
            const color = row.querySelector('input[type="color"]').value;

            fetch('share_buttons.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=update&id=${id}&name=${encodeURIComponent(newName)}&icon=${encodeURIComponent(icon)}&color=${encodeURIComponent(color)}`
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        row.querySelector('.btn-name').textContent = newName;
                    }
                });
        }
    }
</script>

<?php require_once 'includes/footer.php'; ?>