<?php
session_start();
require_once 'config/db.php';
require_once 'includes/header.php';

$database = new Database();
$db = $database->getConnection();

// Handle Bulk Delete Request
if (isset($_POST['bulk_delete']) && isset($_POST['selected_ids'])) {
    $selectedIds = $_POST['selected_ids'];
    if (!empty($selectedIds)) {
        $placeholders = implode(',', array_fill(0, count($selectedIds), '?'));
        $stmt = $db->prepare("DELETE FROM auction_cars WHERE id IN ($placeholders)");
        if ($stmt->execute($selectedIds)) {
            $count = count($selectedIds);
            echo "<script>alert('ลบข้อมูล {$count} รายการเรียบร้อยแล้ว'); window.location.href='auction_cars.php';</script>";
        } else {
            echo "<script>alert('เกิดข้อผิดพลาดในการลบข้อมูล');</script>";
        }
    }
}

// Handle Single Delete Request
if (isset($_GET['delete_id'])) {
    $stmt = $db->prepare("DELETE FROM auction_cars WHERE id = :id");
    $stmt->bindParam(':id', $_GET['delete_id']);
    if ($stmt->execute()) {
        echo "<script>alert('ลบข้อมูลเรียบร้อยแล้ว'); window.location.href='auction_cars.php';</script>";
    } else {
        echo "<script>alert('เกิดข้อผิดพลาดในการลบข้อมูล');</script>";
    }
}

// Fetch Auction Cars with Schedule Info
$stmt = $db->query("
    SELECT c.*, s.branch_name as schedule_name, s.auction_date as schedule_date 
    FROM auction_cars c 
    LEFT JOIN auction_schedules s ON c.schedule_id = s.id 
    ORDER BY CAST(c.queue_number AS UNSIGNED) ASC, c.queue_number ASC
");
$cars = $stmt->fetchAll();
?>

<div class="page-header">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <h1 class="page-title">จัดการรถประมูล (Auction Cars)</h1>
        <a href="auction_car_form.php" class="btn-add"
            style="background: var(--primary-blue); color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">
            <i class="fa-solid fa-plus"></i> เพิ่มรถใหม่
        </a>
    </div>
</div>

<div class="card">
    <form method="POST" id="bulkDeleteForm">
        <!-- Bulk Actions Bar -->
        <div id="bulkActionsBar"
            style="display: none; background: #fff3cd; padding: 12px 15px; margin-bottom: 15px; border-radius: 5px; border: 1px solid #ffc107; align-items: center; justify-content: space-between;">
            <div>
                <span id="selectedCount" style="font-weight: 600; color: #856404;">0</span> รายการที่เลือก
            </div>
            <button type="submit" name="bulk_delete" class="btn-delete-bulk"
                style="background: #d32f2f; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer; font-family: 'Prompt';"
                onclick="return confirm('ยืนยันการลบ ' + document.getElementById('selectedCount').textContent + ' รายการ?');">
                <i class="fa-solid fa-trash"></i> ลบรายการที่เลือก
            </button>
        </div>

        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: #f8f9fa; border-bottom: 2px solid #dee2e6;">
                    <th style="padding: 15px; text-align: center; width: 50px;">
                        <input type="checkbox" id="selectAll" title="เลือกทั้งหมด"
                            style="cursor: pointer; width: 18px; height: 18px;">
                    </th>
                    <th style="padding: 15px; text-align: center; width: 80px;">ลำดับ</th>
                    <th style="padding: 15px; text-align: left; width: 100px;">รูปภาพ</th>
                    <th style="padding: 15px; text-align: left;">ชื่อรุ่น / รายละเอียด</th>
                    <th style="padding: 15px; text-align: left;">ยี่ห้อ / ประเภท</th>
                    <th style="padding: 15px; text-align: center;">เกรด</th>
                    <th style="padding: 15px; text-align: left;">รอบประมูล</th>
                    <th style="padding: 15px; text-align: right;">ราคาเปิด</th>
                    <th style="padding: 15px; text-align: center;">จัดการ</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($cars) > 0): ?>
                    <?php foreach ($cars as $car): ?>
                        <tr style="border-bottom: 1px solid #dee2e6;" class="car-row">
                            <td style="padding: 15px; text-align: center;">
                                <input type="checkbox" name="selected_ids[]" value="<?php echo $car['id']; ?>"
                                    class="row-checkbox" style="cursor: pointer; width: 18px; height: 18px;">
                            </td>
                            <td style="padding: 15px; text-align: center; font-weight: bold; color: #555;">
                                <?php echo !empty($car['queue_number']) ? htmlspecialchars($car['queue_number']) : '-'; ?>
                            </td>
                            <td style=" padding: 15px;">
                                <?php if (!empty($car['image_path'])): ?>
                                    <img src="../<?php echo $car['image_path']; ?>" alt="Car"
                                        style="width: 80px; height: 60px; object-fit: cover; border-radius: 4px;">
                                <?php else: ?>
                                    <div
                                        style="width: 80px; height: 60px; background: #eee; display: flex; align-items: center; justify-content: center; border-radius: 4px; color: #aaa;">
                                        <i class="fa-solid fa-image"></i>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 15px;">
                                <div style="font-weight: 600; color: #333;">
                                    <?php echo $car['title']; ?>
                                </div>
                                <div style="font-size: 0.9em; color: #666;">
                                    <i class="fa-solid fa-gauge-high"></i>
                                    <?php echo $car['mileage']; ?> |
                                    <i class="fa-solid fa-gears"></i>
                                    <?php echo $car['transmission']; ?>
                                </div>
                            </td>
                            <td style="padding: 15px;">
                                <div style="font-weight: 500; color: #333;">
                                    <?php echo !empty($car['brand']) ? $car['brand'] : '-'; ?>
                                </div>
                                <div style="font-size: 0.9em; color: #666;">
                                    <?php echo !empty($car['car_type']) ? $car['car_type'] : '-'; ?>
                                </div>
                            </td>
                            <td style="padding: 15px; text-align: center;">
                                <span
                                    style="background: var(--accent-gold); color: white; padding: 2px 8px; border-radius: 4px; font-size: 0.9em;">
                                    <?php echo $car['grade']; ?>
                                </span>
                            </td>
                            <td style="padding: 15px;">
                                <?php if (!empty($car['schedule_name'])): ?>
                                    <div style="font-size: 0.9em;">
                                        <i class="fa-solid fa-gavel" style="color: #28a745;"></i>
                                        <?php echo htmlspecialchars($car['schedule_name']); ?>
                                    </div>
                                    <div style="font-size: 0.8em; color: #666;">
                                        <?php echo htmlspecialchars($car['schedule_date']); ?>
                                    </div>
                                <?php else: ?>
                                    <span style="color: #aaa; font-size: 0.9em;">- ยังไม่มีรอบ -</span>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 15px; text-align: right; font-weight: 600; color: var(--primary-blue);">
                                <?php echo $car['price']; ?>
                            </td>
                            <td style="padding: 15px; text-align: center;">
                                <a href="auction_car_form.php?id=<?php echo $car['id']; ?>"
                                    style="color: #002D62; margin-right: 10px;" title="แก้ไข">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                                <a href="auction_cars.php?delete_id=<?php echo $car['id']; ?>" style="color: #d32f2f;"
                                    title="ลบ" onclick="return confirm('ยืนยันการลบข้อมูลนี้?');">
                                    <i class="fa-solid fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" style="padding: 30px; text-align: center; color: #888;">
                            ยังไม่มีข้อมูลรถประมูล
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const selectAllCheckbox = document.getElementById('selectAll');
        const rowCheckboxes = document.querySelectorAll('.row-checkbox');
        const bulkActionsBar = document.getElementById('bulkActionsBar');
        const selectedCountSpan = document.getElementById('selectedCount');

        // Update bulk actions bar visibility
        function updateBulkActions() {
            const checkedCount = document.querySelectorAll('.row-checkbox:checked').length;
            selectedCountSpan.textContent = checkedCount;

            if (checkedCount > 0) {
                bulkActionsBar.style.display = 'flex';
            } else {
                bulkActionsBar.style.display = 'none';
            }

            // Update select all checkbox state
            if (checkedCount === 0) {
                selectAllCheckbox.checked = false;
                selectAllCheckbox.indeterminate = false;
            } else if (checkedCount === rowCheckboxes.length) {
                selectAllCheckbox.checked = true;
                selectAllCheckbox.indeterminate = false;
            } else {
                selectAllCheckbox.checked = false;
                selectAllCheckbox.indeterminate = true;
            }
        }

        // Select All checkbox click
        selectAllCheckbox.addEventListener('change', function () {
            rowCheckboxes.forEach(function (checkbox) {
                checkbox.checked = selectAllCheckbox.checked;
            });
            updateBulkActions();
        });

        // Individual row checkbox click
        rowCheckboxes.forEach(function (checkbox) {
            checkbox.addEventListener('change', updateBulkActions);
        });

        // Highlight selected rows
        rowCheckboxes.forEach(function (checkbox) {
            checkbox.addEventListener('change', function () {
                const row = this.closest('tr');
                if (this.checked) {
                    row.style.backgroundColor = '#e3f2fd';
                } else {
                    row.style.backgroundColor = '';
                }
            });
        });
    });
</script>

<?php require_once 'includes/footer.php'; ?>