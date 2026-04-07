<?php
session_start();
require_once 'config/db.php';
require_once 'includes/header.php';

$database = new Database();
$db = $database->getConnection();

// Self-Healing: Create used_cars table if not exists
try {
    $sql_create = "CREATE TABLE IF NOT EXISTS used_cars (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        brand VARCHAR(100) NOT NULL DEFAULT '',
        model VARCHAR(100) NOT NULL DEFAULT '',
        car_year VARCHAR(10) DEFAULT NULL,
        car_color VARCHAR(50) DEFAULT '',
        license_plate VARCHAR(50) DEFAULT '',
        mileage VARCHAR(50) DEFAULT '',
        cc VARCHAR(20) DEFAULT '',
        transmission VARCHAR(50) DEFAULT '',
        price VARCHAR(100) NOT NULL DEFAULT '',
        price_original VARCHAR(100) DEFAULT '',
        description TEXT NULL,
        car_type VARCHAR(50) DEFAULT '',
        image_path VARCHAR(255) NULL,
        image_path_2 VARCHAR(255) NULL,
        image_path_3 VARCHAR(255) NULL,
        image_path_4 VARCHAR(255) NULL,
        image_path_5 VARCHAR(255) NULL,
        inspection_body VARCHAR(255) DEFAULT '',
        inspection_engine VARCHAR(255) DEFAULT '',
        inspection_suspension VARCHAR(255) DEFAULT '',
        inspection_interior VARCHAR(255) DEFAULT '',
        inspection_tires VARCHAR(255) DEFAULT '',
        is_active TINYINT(1) DEFAULT 1,
        is_featured TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    $db->exec($sql_create);
} catch (Exception $e) {
    // Continue
}

// Fetch Used Cars
$cars = [];
try {
    $stmt = $db->query("SELECT * FROM used_cars ORDER BY created_at DESC");
    $cars = $stmt->fetchAll();
} catch (PDOException $e) {
}

// Handle Delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    try {
        $stmt = $db->prepare("DELETE FROM used_cars WHERE id = :id");
        $stmt->bindParam(':id', $_GET['delete']);
        $stmt->execute();
        header("Location: used_cars.php?deleted=1");
        exit;
    } catch (PDOException $e) {
    }
}

// Handle Toggle Active
if (isset($_GET['toggle']) && is_numeric($_GET['toggle'])) {
    try {
        $stmt = $db->prepare("UPDATE used_cars SET is_active = NOT is_active WHERE id = :id");
        $stmt->bindParam(':id', $_GET['toggle']);
        $stmt->execute();
        header("Location: used_cars.php");
        exit;
    } catch (PDOException $e) {
    }
}
?>

<div class="page-header">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <h1 class="page-title">จัดการรถสวยพร้อมขาย (Used Cars)</h1>
        <a href="used_car_form.php" class="btn-add"
            style="background: var(--primary-blue); color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">
            <i class="fa-solid fa-plus"></i> เพิ่มรถใหม่
        </a>
    </div>
</div>

<?php if (isset($_GET['deleted'])): ?>
    <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
        <i class="fa-solid fa-check-circle"></i> ลบข้อมูลเรียบร้อยแล้ว
    </div>
<?php endif; ?>

<div class="card">
    <?php if (count($cars) > 0): ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 80px;">รูปภาพ</th>
                    <th>ชื่อรถ</th>
                    <th style="text-align: center;">ยี่ห้อ</th>
                    <th style="text-align: center;">ปี</th>
                    <th style="text-align: center;">ราคา</th>
                    <th style="text-align: center;">สถานะ</th>
                    <th style="text-align: center;">เด่น</th>
                    <th style="text-align: center; width: 120px;">จัดการ</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cars as $car): ?>
                    <tr>
                        <td>
                            <?php if (!empty($car['image_path'])): ?>
                                <img src="<?php echo htmlspecialchars($car['image_path']); ?>"
                                    style="width: 70px; height: 50px; object-fit: cover; border-radius: 4px;">
                            <?php else: ?>
                                <div
                                    style="width: 70px; height: 50px; background: #eee; border-radius: 4px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fa-solid fa-car" style="color: #999;"></i>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong><?php echo htmlspecialchars($car['title']); ?></strong>
                            <?php if (!empty($car['mileage'])): ?>
                                <br><small style="color: #888;">ไมล์: <?php echo htmlspecialchars($car['mileage']); ?></small>
                            <?php endif; ?>
                        </td>
                        <td style="text-align: center;"><?php echo htmlspecialchars($car['brand']); ?></td>
                        <td style="text-align: center;"><?php echo htmlspecialchars($car['car_year']); ?></td>
                        <td style="text-align: center; color: var(--primary-blue); font-weight: 600;">
                            <?php echo htmlspecialchars($car['price']); ?>
                        </td>
                        <td style="text-align: center;">
                            <a href="?toggle=<?php echo $car['id']; ?>"
                                style="text-decoration: none; padding: 3px 10px; border-radius: 20px; font-size: 0.8rem;
                                    <?php echo $car['is_active'] ? 'background: #d4edda; color: #155724;' : 'background: #f8d7da; color: #721c24;'; ?>">
                                <?php echo $car['is_active'] ? 'แสดง' : 'ซ่อน'; ?>
                            </a>
                        </td>
                        <td style="text-align: center;">
                            <?php if ($car['is_featured']): ?>
                                <i class="fa-solid fa-star" style="color: #ffc107;"></i>
                            <?php else: ?>
                                <i class="fa-regular fa-star" style="color: #ccc;"></i>
                            <?php endif; ?>
                        </td>
                        <td style="text-align: center;">
                            <a href="used_car_form.php?id=<?php echo $car['id']; ?>"
                                style="color: var(--primary-blue); margin-right: 10px;">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </a>
                            <a href="?delete=<?php echo $car['id']; ?>" style="color: #dc3545;"
                                onclick="return confirm('ยืนยันการลบรถมือสองนี้?')">
                                <i class="fa-solid fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div style="text-align: center; padding: 40px; color: #888;">
            <i class="fa-solid fa-car-side" style="font-size: 3rem; margin-bottom: 15px;"></i>
            <p>ยังไม่มีรายการรถมือสอง</p>
            <a href="used_car_form.php" class="btn btn-primary" style="margin-top: 15px;">
                <i class="fa-solid fa-plus"></i> เพิ่มรถมือสองใหม่
            </a>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>