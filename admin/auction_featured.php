<?php
session_start();
require_once 'config/db.php';

$database = new Database();
$db = $database->getConnection();

// Handle AJAX request BEFORE including header
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');

    if ($_POST['action'] === 'toggle_featured') {
        $car_id = (int) $_POST['car_id'];
        $is_featured = (int) $_POST['is_featured'];

        $stmt = $db->prepare("UPDATE auction_cars SET is_featured = :is_featured WHERE id = :car_id");
        $stmt->bindParam(':is_featured', $is_featured, PDO::PARAM_INT);
        $stmt->bindParam(':car_id', $car_id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Database error']);
        }
        exit;
    }

    if ($_POST['action'] === 'clear_all') {
        $stmt = $db->exec("UPDATE auction_cars SET is_featured = 0");
        echo json_encode(['success' => true]);
        exit;
    }
}

require_once 'includes/header.php';

// Fetch all cars in active schedules
$cars_in_schedule = $db->query("
    SELECT c.*, s.branch_name, s.auction_date 
    FROM auction_cars c
    LEFT JOIN auction_schedules s ON c.schedule_id = s.id
    WHERE c.schedule_id IS NOT NULL
    ORDER BY c.is_featured DESC, CAST(c.queue_number AS UNSIGNED) ASC
")->fetchAll();

// Fetch featured cars count
$featured_count = $db->query("SELECT COUNT(*) FROM auction_cars WHERE is_featured = 1")->fetchColumn();
?>

<div class="page-header">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <h1 class="page-title">จัดการรถเด่นประจำรอบ</h1>
        <div style="display: flex; gap: 10px;">
            <span style="background: #ffc107; color: #000; padding: 8px 15px; border-radius: 5px; font-weight: 500;">
                <i class="fa-solid fa-star"></i> รถเด่น: <span id="featuredCount">
                    <?php echo $featured_count; ?>
                </span> คัน
            </span>
            <button onclick="clearAllFeatured()"
                style="background: #dc3545; color: white; border: none; padding: 8px 15px; border-radius: 5px; cursor: pointer; font-family: 'Prompt';">
                <i class="fa-solid fa-xmark"></i> ล้างทั้งหมด
            </button>
        </div>
    </div>
</div>

<div class="card">
    <div
        style="margin-bottom: 20px; padding: 15px; background: #e8f5e9; border-radius: 8px; border-left: 4px solid #4caf50;">
        <i class="fa-solid fa-info-circle" style="color: #4caf50;"></i>
        <strong>วิธีใช้:</strong> คลิกที่ไอคอนดาว <i class="fa-solid fa-star" style="color: #ffc107;"></i>
        เพื่อเลือก/ยกเลิกรถเด่น
        รถเด่นจะแสดงในหน้าแรกของประมูล (แนะนำ 4 คัน)
    </div>

    <?php if (count($cars_in_schedule) > 0): ?>
        <div style="max-height: 600px; overflow-y: auto;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead style="position: sticky; top: 0; background: white; z-index: 10;">
                    <tr style="background: #f8f9fa; border-bottom: 2px solid #dee2e6;">
                        <th style="padding: 12px; text-align: center; width: 60px;">ลำดับ</th>
                        <th style="padding: 12px; text-align: left; width: 80px;">รูป</th>
                        <th style="padding: 12px; text-align: left;">รถ</th>
                        <th style="padding: 12px; text-align: left;">รอบประมูล</th>
                        <th style="padding: 12px; text-align: right;">ราคา</th>
                        <th style="padding: 12px; text-align: center; width: 80px;">รถเด่น</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cars_in_schedule as $car): ?>
                        <tr style="border-bottom: 1px solid #eee; <?php echo $car['is_featured'] ? 'background: #fffde7;' : ''; ?>"
                            id="row-<?php echo $car['id']; ?>">
                            <td style="padding: 10px; text-align: center; font-weight: bold; color: #555;">
                                <?php echo !empty($car['queue_number']) ? $car['queue_number'] : '-'; ?>
                            </td>
                            <td style="padding: 10px;">
                                <?php if (!empty($car['image_path'])): ?>
                                    <img src="../<?php echo $car['image_path']; ?>"
                                        style="width: 60px; height: 45px; object-fit: cover; border-radius: 4px;">
                                <?php else: ?>
                                    <div
                                        style="width: 60px; height: 45px; background: #eee; display: flex; align-items: center; justify-content: center; border-radius: 4px; color: #ccc;">
                                        <i class="fa-solid fa-car"></i>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 10px;">
                                <div style="font-weight: 500;">
                                    <?php echo htmlspecialchars($car['title']); ?>
                                </div>
                                <div style="font-size: 0.85rem; color: #666;">
                                    <span
                                        style="background: var(--accent-gold); color: #000; padding: 1px 6px; border-radius: 3px; font-size: 0.8rem;">เกรด
                                        <?php echo $car['grade']; ?>
                                    </span>
                                    |
                                    <?php echo $car['mileage']; ?> |
                                    <?php echo $car['transmission']; ?>
                                </div>
                            </td>
                            <td style="padding: 10px;">
                                <div style="font-size: 0.9rem;">
                                    <i class="fa-solid fa-location-dot" style="color: var(--primary-blue);"></i>
                                    <?php echo htmlspecialchars($car['branch_name']); ?>
                                </div>
                                <div style="font-size: 0.85rem; color: #666;">
                                    <?php echo htmlspecialchars($car['auction_date']); ?>
                                </div>
                            </td>
                            <td style="padding: 10px; text-align: right; font-weight: 600; color: var(--primary-blue);">
                                <?php echo $car['price']; ?>
                            </td>
                            <td style="padding: 10px; text-align: center;">
                                <button
                                    onclick="toggleFeatured(<?php echo $car['id']; ?>, <?php echo $car['is_featured'] ? '0' : '1'; ?>)"
                                    class="btn-featured" id="btn-<?php echo $car['id']; ?>"
                                    style="background: none; border: none; cursor: pointer; font-size: 1.5rem; color: <?php echo $car['is_featured'] ? '#ffc107' : '#ddd'; ?>;">
                                    <i class="fa-solid fa-star"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div style="text-align: center; padding: 60px; color: #888;">
            <i class="fa-solid fa-inbox" style="font-size: 3rem; margin-bottom: 15px;"></i>
            <p>ยังไม่มีรถที่ถูกจัดเข้ารอบประมูล</p>
            <a href="auction_round_manager.php" class="btn btn-primary" style="margin-top: 15px;">
                <i class="fa-solid fa-plus"></i> ไปจัดการรอบประมูล
            </a>
        </div>
    <?php endif; ?>
</div>

<script>
    function toggleFeatured(carId, newValue) {
        fetch('auction_featured.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `action=toggle_featured&car_id=${carId}&is_featured=${newValue}`
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const btn = document.getElementById('btn-' + carId);
                    const row = document.getElementById('row-' + carId);
                    const countSpan = document.getElementById('featuredCount');
                    let count = parseInt(countSpan.textContent);

                    if (newValue == 1) {
                        btn.style.color = '#ffc107';
                        btn.setAttribute('onclick', `toggleFeatured(${carId}, 0)`);
                        row.style.background = '#fffde7';
                        countSpan.textContent = count + 1;
                    } else {
                        btn.style.color = '#ddd';
                        btn.setAttribute('onclick', `toggleFeatured(${carId}, 1)`);
                        row.style.background = '';
                        countSpan.textContent = count - 1;
                    }
                } else {
                    alert('เกิดข้อผิดพลาด');
                }
            });
    }

    function clearAllFeatured() {
        if (!confirm('ยืนยันการล้างรถเด่นทั้งหมด?')) return;

        fetch('auction_featured.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'action=clear_all'
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                }
            });
    }
</script>

<?php require_once 'includes/footer.php'; ?>