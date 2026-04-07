<?php
session_start();
require_once 'config/db.php';
require_once 'includes/header.php';

$database = new Database();
$db = $database->getConnection();

$b = [
    'name' => '',
    'region' => '',
    'address' => '',
    'phone' => '',
    'hours' => 'จันทร์ - เสาร์: 08.30 - 17.00 น.',
    'map_url' => ''
];
$is_edit = false;

if (isset($_GET['id'])) {
    $is_edit = true;
    $stmt = $db->prepare("SELECT * FROM branches WHERE id = :id");
    $stmt->bindParam(':id', $_GET['id']);
    $stmt->execute();
    $b = $stmt->fetch(PDO::FETCH_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $region = $_POST['region'];
    $address = $_POST['address'];
    $phone = $_POST['phone'];
    $hours = $_POST['hours'];
    $map_url = $_POST['map_url'];

    if ($is_edit) {
        $sql = "UPDATE branches SET name=:name, region=:region, address=:address, phone=:phone, hours=:hours, map_url=:map_url WHERE id=:id";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':id', $_GET['id']);
    } else {
        $sql = "INSERT INTO branches (name, region, address, phone, hours, map_url) VALUES (:name, :region, :address, :phone, :hours, :map_url)";
        $stmt = $db->prepare($sql);
    }

    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':region', $region);
    $stmt->bindParam(':address', $address);
    $stmt->bindParam(':phone', $phone);
    $stmt->bindParam(':hours', $hours);
    $stmt->bindParam(':map_url', $map_url);

    if ($stmt->execute()) {
        echo "<script>alert('บันทึกข้อมูลเรียบร้อยแล้ว'); window.location.href='branches.php';</script>";
    } else {
        echo "<script>alert('เกิดข้อผิดพลาดในการบันทึกข้อมูล');</script>";
    }
}
?>

<div class="page-header">
    <h1 class="page-title">
        <?php echo $is_edit ? 'แก้ไขสาขา' : 'เพิ่มสาขาใหม่'; ?>
    </h1>
    <a href="branches.php" style="color: #666; text-decoration: none; margin-top: 10px; display: inline-block;">
        <i class="fa-solid fa-arrow-left"></i> ย้อนกลับไปหน้ารายการ
    </a>
</div>

<div class="card">
    <form method="POST" style="max-width: 800px;">
        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 5px; font-weight: 500;">ชื่อสาขา</label>
            <input type="text" name="name" value="<?php echo htmlspecialchars($b['name']); ?>" required
                placeholder="เช่น สาขานครปฐม (สำนักงานใหญ่)"
                style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-family: 'Prompt';">
        </div>

        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 5px; font-weight: 500;">ภูมิภาค (Region)</label>
            <select name="region"
                style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-family: 'Prompt';">
                <option value="กลาง" <?php echo $b['region'] == 'กลาง' ? 'selected' : ''; ?>>ภาคกลาง (Central)</option>
                <option value="เหนือ" <?php echo $b['region'] == 'เหนือ' ? 'selected' : ''; ?>>ภาคเหนือ (North)</option>
                <option value="อีสาน" <?php echo $b['region'] == 'อีสาน' ? 'selected' : ''; ?>>ภาคอีสาน (Northeast)
                </option>
                <option value="ใต้" <?php echo $b['region'] == 'ใต้' ? 'selected' : ''; ?>>ภาคใต้ (South)</option>
                <option value="ตะวันออก" <?php echo $b['region'] == 'ตะวันออก' ? 'selected' : ''; ?>>ภาคตะวันออก (East)
                </option>
            </select>
        </div>

        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 5px; font-weight: 500;">ที่อยู่</label>
            <textarea name="address" required rows="3"
                style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-family: 'Prompt';"><?php echo htmlspecialchars($b['address']); ?></textarea>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 500;">เบอร์โทรศัพท์</label>
                <input type="text" name="phone" value="<?php echo htmlspecialchars($b['phone']); ?>" required
                    placeholder="เช่น 02-123-4567"
                    style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-family: 'Prompt';">
            </div>
            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 500;">เวลาทำการ</label>
                <input type="text" name="hours" value="<?php echo htmlspecialchars($b['hours']); ?>"
                    style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-family: 'Prompt';">
            </div>
        </div>

        <div style="margin-bottom: 30px;">
            <label style="display: block; margin-bottom: 5px; font-weight: 500;">Google Map Link (Embed URL or Normal
                URL)</label>
            <input type="text" name="map_url" value="<?php echo htmlspecialchars($b['map_url']); ?>"
                placeholder="https://maps.google.com/..."
                style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-family: 'Prompt';">
            <div style="font-size: 0.8em; color: #888;">ใส่ลิงก์แผนที่เพื่อให้ผู้ใช้คลิกนำทาง</div>
        </div>

        <button type="submit"
            style="background: var(--primary-blue); color: white; border: none; padding: 12px 30px; border-radius: 4px; font-size: 1rem; cursor: pointer; font-family: 'Prompt';">
            <i class="fa-solid fa-save"></i> บันทึกข้อมูล
        </button>
    </form>
</div>

<?php require_once 'includes/footer.php'; ?>