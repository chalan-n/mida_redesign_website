<?php
session_start();
require_once 'config/db.php';

$database = new Database();
$db = $database->getConnection();

// Handle AJAX request BEFORE including header (which outputs HTML)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    if ($_POST['action'] === 'assign') {
        $car_id = $_POST['car_id'];
        $schedule_id = $_POST['schedule_id'];
        
        $stmt = $db->prepare("UPDATE auction_cars SET schedule_id = :schedule_id WHERE id = :car_id");
        $stmt->bindParam(':schedule_id', $schedule_id, PDO::PARAM_INT);
        $stmt->bindParam(':car_id', $car_id, PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Database error']);
        }
        exit;
    }
    
    if ($_POST['action'] === 'remove') {
        $car_id = $_POST['car_id'];
        
        $stmt = $db->prepare("UPDATE auction_cars SET schedule_id = NULL WHERE id = :car_id");
        $stmt->bindParam(':car_id', $car_id, PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Database error']);
        }
        exit;
    }
    
    if ($_POST['action'] === 'bulk_assign') {
        $car_ids = json_decode($_POST['car_ids'], true);
        $schedule_id = $_POST['schedule_id'];
        
        if (!empty($car_ids) && is_array($car_ids)) {
            $placeholders = implode(',', array_fill(0, count($car_ids), '?'));
            $sql = "UPDATE auction_cars SET schedule_id = ? WHERE id IN ($placeholders)";
            $stmt = $db->prepare($sql);
            $params = array_merge([$schedule_id], $car_ids);
            
            if ($stmt->execute($params)) {
                echo json_encode(['success' => true, 'count' => count($car_ids)]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Database error']);
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'No cars selected']);
        }
        exit;
    }
    
    if ($_POST['action'] === 'bulk_remove') {
        $car_ids = json_decode($_POST['car_ids'], true);
        
        if (!empty($car_ids) && is_array($car_ids)) {
            $placeholders = implode(',', array_fill(0, count($car_ids), '?'));
            $sql = "UPDATE auction_cars SET schedule_id = NULL WHERE id IN ($placeholders)";
            $stmt = $db->prepare($sql);
            
            if ($stmt->execute($car_ids)) {
                echo json_encode(['success' => true, 'count' => count($car_ids)]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Database error']);
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'No cars selected']);
        }
        exit;
    }
}

// Now include header for normal page load
require_once 'includes/header.php';

// Get selected schedule
$selected_schedule_id = isset($_GET['schedule_id']) ? (int)$_GET['schedule_id'] : null;

// Fetch all schedules
$schedules = $db->query("SELECT * FROM auction_schedules ORDER BY id DESC")->fetchAll();

// Fetch cars not assigned to any schedule
$unassigned_cars = $db->query("SELECT * FROM auction_cars WHERE schedule_id IS NULL ORDER BY CAST(queue_number AS UNSIGNED) ASC")->fetchAll();

// Fetch cars in selected schedule
$assigned_cars = [];
$current_schedule = null;
if ($selected_schedule_id) {
    $stmt = $db->prepare("SELECT * FROM auction_schedules WHERE id = ?");
    $stmt->execute([$selected_schedule_id]);
    $current_schedule = $stmt->fetch();
    
    $stmt = $db->prepare("SELECT * FROM auction_cars WHERE schedule_id = ? ORDER BY CAST(queue_number AS UNSIGNED) ASC");
    $stmt->execute([$selected_schedule_id]);
    $assigned_cars = $stmt->fetchAll();
}
?>

<div class="page-header">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <h1 class="page-title">จัดการนำรถเข้ารอบประมูล</h1>
        <a href="auction_schedules.php" style="color: #666; text-decoration: none;">
            <i class="fa-solid fa-arrow-left"></i> กลับหน้าตารางประมูล
        </a>
    </div>
</div>

<!-- Schedule Selector -->
<div class="card" style="margin-bottom: 20px;">
    <form method="GET" style="display: flex; gap: 15px; align-items: center;">
        <label style="font-weight: 500;">เลือกรอบประมูล:</label>
        <select name="schedule_id" id="scheduleSelect" style="flex: 1; max-width: 400px; padding: 10px; border: 1px solid #ddd; border-radius: 5px; font-family: 'Prompt';">
            <option value="">-- เลือกรอบประมูล --</option>
            <?php foreach ($schedules as $sch): ?>
                <option value="<?php echo $sch['id']; ?>" <?php echo $selected_schedule_id == $sch['id'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($sch['branch_name']); ?> - <?php echo htmlspecialchars($sch['auction_date']); ?>
                    <?php echo $sch['is_active'] ? '(Active)' : ''; ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-primary" style="padding: 10px 20px;">
            <i class="fa-solid fa-search"></i> แสดง
        </button>
    </form>
</div>

<?php if ($selected_schedule_id && $current_schedule): ?>
<!-- Two Column Layout -->
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
    
    <!-- Left: Unassigned Cars -->
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid #eee;">
            <h3 style="margin: 0; color: #333;">
                <i class="fa-solid fa-car-side" style="color: #888;"></i> รถยังไม่มีรอบ
                <span style="font-size: 0.9rem; color: #666;">(<?php echo count($unassigned_cars); ?> คัน)</span>
            </h3>
            <button onclick="bulkAssign()" class="btn btn-sm" style="background: #28a745; color: white; padding: 5px 15px; border: none; border-radius: 4px; cursor: pointer; font-family: 'Prompt';">
                <i class="fa-solid fa-plus"></i> เพิ่มที่เลือกเข้ารอบ
            </button>
        </div>
        
        <?php if (count($unassigned_cars) > 0): ?>
            <div style="max-height: 500px; overflow-y: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #f8f9fa;">
                            <th style="padding: 10px; text-align: center; width: 40px;">
                                <input type="checkbox" id="selectAllUnassigned" onclick="toggleAll('unassigned')">
                            </th>
                            <th style="padding: 10px; text-align: center; width: 60px;">ลำดับ</th>
                            <th style="padding: 10px; text-align: left;">รถ</th>
                            <th style="padding: 10px; text-align: center; width: 60px;">เพิ่ม</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($unassigned_cars as $car): ?>
                            <tr class="car-row" data-car-id="<?php echo $car['id']; ?>">
                                <td style="padding: 8px; text-align: center;">
                                    <input type="checkbox" class="unassigned-checkbox" value="<?php echo $car['id']; ?>">
                                </td>
                                <td style="padding: 8px; text-align: center; font-weight: bold; color: #555;">
                                    <?php echo !empty($car['queue_number']) ? $car['queue_number'] : '-'; ?>
                                </td>
                                <td style="padding: 8px;">
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <?php if (!empty($car['image_path'])): ?>
                                            <img src="../<?php echo $car['image_path']; ?>" style="width: 50px; height: 35px; object-fit: cover; border-radius: 4px;">
                                        <?php else: ?>
                                            <div style="width: 50px; height: 35px; background: #eee; display: flex; align-items: center; justify-content: center; border-radius: 4px; color: #ccc;">
                                                <i class="fa-solid fa-car"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <div style="font-weight: 500; font-size: 0.9rem;"><?php echo htmlspecialchars($car['title']); ?></div>
                                            <div style="font-size: 0.8rem; color: #666;"><?php echo $car['grade']; ?> | <?php echo $car['price']; ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td style="padding: 8px; text-align: center;">
                                    <button onclick="assignCar(<?php echo $car['id']; ?>)" style="background: #28a745; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer;">
                                        <i class="fa-solid fa-plus"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p style="text-align: center; color: #888; padding: 30px;">ไม่มีรถที่ยังไม่ได้จัดรอบ</p>
        <?php endif; ?>
    </div>
    
    <!-- Right: Assigned Cars -->
    <div class="card" style="background: #e8f5e9; border: 2px solid #4caf50;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid #c8e6c9;">
            <h3 style="margin: 0; color: #2e7d32;">
                <i class="fa-solid fa-gavel"></i> รถในรอบ: <?php echo htmlspecialchars($current_schedule['branch_name']); ?>
                <span style="font-size: 0.9rem; color: #388e3c;">(<?php echo count($assigned_cars); ?> คัน)</span>
            </h3>
            <button onclick="bulkRemove()" class="btn btn-sm" style="background: #dc3545; color: white; padding: 5px 15px; border: none; border-radius: 4px; cursor: pointer; font-family: 'Prompt';">
                <i class="fa-solid fa-minus"></i> นำออกที่เลือก
            </button>
        </div>
        
        <div style="background: white; padding: 10px; border-radius: 5px; margin-bottom: 15px;">
            <div style="display: flex; gap: 20px; font-size: 0.9rem;">
                <div><strong>วันที่:</strong> <?php echo htmlspecialchars($current_schedule['auction_date']); ?></div>
                <div><strong>เวลา:</strong> <?php echo htmlspecialchars($current_schedule['time_start']); ?></div>
            </div>
        </div>
        
        <?php if (count($assigned_cars) > 0): ?>
            <div style="max-height: 400px; overflow-y: auto; background: white; border-radius: 5px;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #f8f9fa;">
                            <th style="padding: 10px; text-align: center; width: 40px;">
                                <input type="checkbox" id="selectAllAssigned" onclick="toggleAll('assigned')">
                            </th>
                            <th style="padding: 10px; text-align: center; width: 60px;">ลำดับ</th>
                            <th style="padding: 10px; text-align: left;">รถ</th>
                            <th style="padding: 10px; text-align: center; width: 60px;">นำออก</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($assigned_cars as $car): ?>
                            <tr class="car-row" data-car-id="<?php echo $car['id']; ?>">
                                <td style="padding: 8px; text-align: center;">
                                    <input type="checkbox" class="assigned-checkbox" value="<?php echo $car['id']; ?>">
                                </td>
                                <td style="padding: 8px; text-align: center; font-weight: bold; color: #2e7d32;">
                                    <?php echo !empty($car['queue_number']) ? $car['queue_number'] : '-'; ?>
                                </td>
                                <td style="padding: 8px;">
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <?php if (!empty($car['image_path'])): ?>
                                            <img src="../<?php echo $car['image_path']; ?>" style="width: 50px; height: 35px; object-fit: cover; border-radius: 4px;">
                                        <?php else: ?>
                                            <div style="width: 50px; height: 35px; background: #eee; display: flex; align-items: center; justify-content: center; border-radius: 4px; color: #ccc;">
                                                <i class="fa-solid fa-car"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <div style="font-weight: 500; font-size: 0.9rem;"><?php echo htmlspecialchars($car['title']); ?></div>
                                            <div style="font-size: 0.8rem; color: #666;"><?php echo $car['grade']; ?> | <?php echo $car['price']; ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td style="padding: 8px; text-align: center;">
                                    <button onclick="removeCar(<?php echo $car['id']; ?>)" style="background: #dc3545; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer;">
                                        <i class="fa-solid fa-minus"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div style="background: white; padding: 30px; border-radius: 5px; text-align: center; color: #888;">
                <i class="fa-solid fa-inbox" style="font-size: 2rem; margin-bottom: 10px;"></i>
                <p>ยังไม่มีรถในรอบนี้ กรุณาเพิ่มรถจากรายการด้านซ้าย</p>
            </div>
        <?php endif; ?>
    </div>
    
</div>

<script>
const scheduleId = <?php echo $selected_schedule_id; ?>;

function assignCar(carId) {
    fetch('auction_round_manager.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=assign&car_id=${carId}&schedule_id=${scheduleId}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('เกิดข้อผิดพลาด: ' + data.error);
        }
    });
}

function removeCar(carId) {
    fetch('auction_round_manager.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=remove&car_id=${carId}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('เกิดข้อผิดพลาด: ' + data.error);
        }
    });
}

function toggleAll(type) {
    const checkboxes = document.querySelectorAll(`.${type}-checkbox`);
    const selectAll = document.getElementById(type === 'unassigned' ? 'selectAllUnassigned' : 'selectAllAssigned');
    checkboxes.forEach(cb => cb.checked = selectAll.checked);
}

function bulkAssign() {
    const checked = document.querySelectorAll('.unassigned-checkbox:checked');
    if (checked.length === 0) {
        alert('กรุณาเลือกรถที่ต้องการเพิ่ม');
        return;
    }
    
    const carIds = Array.from(checked).map(cb => cb.value);
    
    fetch('auction_round_manager.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=bulk_assign&schedule_id=${scheduleId}&car_ids=${JSON.stringify(carIds)}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('เกิดข้อผิดพลาด');
        }
    });
}

function bulkRemove() {
    const checked = document.querySelectorAll('.assigned-checkbox:checked');
    if (checked.length === 0) {
        alert('กรุณาเลือกรถที่ต้องการนำออก');
        return;
    }
    
    const carIds = Array.from(checked).map(cb => cb.value);
    
    fetch('auction_round_manager.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=bulk_remove&car_ids=${JSON.stringify(carIds)}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('เกิดข้อผิดพลาด');
        }
    });
}
</script>

<?php else: ?>
<div class="card" style="text-align: center; padding: 60px;">
    <i class="fa-solid fa-calendar-plus" style="font-size: 4rem; color: #ddd; margin-bottom: 20px;"></i>
    <h3 style="color: #666;">กรุณาเลือกรอบประมูลจากด้านบน</h3>
    <p style="color: #888;">เลือกรอบประมูลที่ต้องการจัดการรถยนต์เข้ารอบ</p>
    
    <?php if (count($schedules) == 0): ?>
        <hr style="margin: 30px 0;">
        <p style="color: #888;">ยังไม่มีรอบประมูลในระบบ</p>
        <a href="auction_schedule_form.php" class="btn btn-primary" style="margin-top: 10px;">
            <i class="fa-solid fa-plus"></i> สร้างรอบประมูลใหม่
        </a>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
