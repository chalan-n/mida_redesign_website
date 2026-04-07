<?php
session_start();
require_once 'config/db.php';
require_once 'includes/header.php';

$database = new Database();
$db = $database->getConnection();

$sch = [
    'branch_name' => '',
    'auction_date' => '',
    'time_register' => '',
    'time_start' => '',
    'car_count' => '',
    'is_active' => 1
];
$is_edit = false;

if (isset($_GET['id'])) {
    $is_edit = true;
    $stmt = $db->prepare("SELECT * FROM auction_schedules WHERE id = :id");
    $stmt->bindParam(':id', $_GET['id']);
    $stmt->execute();
    $sch = $stmt->fetch(PDO::FETCH_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $branch_name = $_POST['branch_name'];
    $auction_date = $_POST['auction_date'];
    $time_register = $_POST['time_register'];
    $time_start = $_POST['time_start'];
    $car_count = $_POST['car_count'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    if ($is_edit) {
        $sql = "UPDATE auction_schedules SET branch_name = :branch_name, auction_date = :auction_date, 
                time_register = :time_register, time_start = :time_start, car_count = :car_count, is_active = :is_active 
                WHERE id = :id";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':id', $_GET['id']);
    } else {
        $sql = "INSERT INTO auction_schedules (branch_name, auction_date, time_register, time_start, car_count, is_active) 
                VALUES (:branch_name, :auction_date, :time_register, :time_start, :car_count, :is_active)";
        $stmt = $db->prepare($sql);
    }

    $stmt->bindParam(':branch_name', $branch_name);
    $stmt->bindParam(':auction_date', $auction_date);
    $stmt->bindParam(':time_register', $time_register);
    $stmt->bindParam(':time_start', $time_start);
    $stmt->bindParam(':car_count', $car_count);
    $stmt->bindParam(':is_active', $is_active);

    if ($stmt->execute()) {
        echo "<script>alert('บันทึกข้อมูลเรียบร้อยแล้ว'); window.location.href='auction_schedules.php';</script>";
    } else {
        echo "<script>alert('เกิดข้อผิดพลาดในการบันทึกข้อมูล');</script>";
    }
}
?>

<div class="page-header">
    <h1 class="page-title">
        <?php echo $is_edit ? 'แก้ไขตารางประมูล' : 'เพิ่มตารางประมูลใหม่'; ?>
    </h1>
    <a href="auction_schedules.php"
        style="color: #666; text-decoration: none; margin-top: 10px; display: inline-block;">
        <i class="fa-solid fa-arrow-left"></i> ย้อนกลับไปหน้ารายการ
    </a>
</div>

<div class="card">
    <form method="POST" style="max-width: 800px;">
        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 5px; font-weight: 500;">สาขา / สถานที่จัดประมูล</label>
            <input type="text" name="branch_name" value="<?php echo htmlspecialchars($sch['branch_name']); ?>" required
                placeholder="เช่น สาขานครปฐม หรือ ลานประมูล..."
                style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-family: 'Prompt';">
        </div>

        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 5px; font-weight: 500;">วันที่ประมูล (ข้อความ)</label>
            <input type="text" name="auction_date" value="<?php echo htmlspecialchars($sch['auction_date']); ?>"
                required placeholder="เช่น เสาร์ที่ 13 ม.ค. 69"
                style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-family: 'Prompt';">
            <div style="font-size: 0.8em; color: #888;">ใส่เป็นข้อความภาษาไทยเพื่อความสวยงาม</div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 500;">เวลาลงทะเบียน</label>
                <input type="text" name="time_register" value="<?php echo htmlspecialchars($sch['time_register']); ?>"
                    placeholder="เช่น 08:30 - 10:00 น."
                    style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-family: 'Prompt';">
            </div>
            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 500;">เวลาเริ่มประมูล</label>
                <input type="text" name="time_start" value="<?php echo htmlspecialchars($sch['time_start']); ?>"
                    placeholder="เช่น 10:30 น. เป็นต้นไป"
                    style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-family: 'Prompt';">
            </div>
        </div>

        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 5px; font-weight: 500;">จำนวนรถเข้าประมูล</label>
            <input type="text" name="car_count" value="<?php echo htmlspecialchars($sch['car_count']); ?>"
                placeholder="เช่น 120 คัน"
                style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-family: 'Prompt';">
        </div>

        <div style="margin-bottom: 30px;">
            <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                <input type="checkbox" name="is_active" value="1" <?php echo $sch['is_active'] ? 'checked' : ''; ?>>
                <span>แสดงผลบนหน้าเว็บไซต์ (Active)</span>
            </label>
        </div>

        <button type="submit"
            style="background: var(--primary-blue); color: white; border: none; padding: 12px 30px; border-radius: 4px; font-size: 1rem; cursor: pointer; font-family: 'Prompt';">
            <i class="fa-solid fa-save"></i> บันทึกข้อมูล
        </button>
    </form>
</div>

<?php require_once 'includes/footer.php'; ?>