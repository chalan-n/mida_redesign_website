<?php
session_start();
require_once 'config/db.php';
require_once 'includes/header.php';

$database = new Database();
$db = $database->getConnection();

$c = [
    'position' => '',
    'location' => '',
    'salary' => '',
    'quantity' => '',
    'description' => '',
    'benefits' => '',
    'is_active' => 1
];
$is_edit = false;

if (isset($_GET['id'])) {
    $is_edit = true;
    $stmt = $db->prepare("SELECT * FROM careers WHERE id = :id");
    $stmt->bindParam(':id', $_GET['id']);
    $stmt->execute();
    $c = $stmt->fetch(PDO::FETCH_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $position = $_POST['position'];
    $location = $_POST['location'];
    $salary = $_POST['salary'];
    $quantity = $_POST['quantity'];
    $description = $_POST['description'];
    $benefits_arr = isset($_POST['benefits']) ? $_POST['benefits'] : [];
    $benefits = is_array($benefits_arr) ? implode(',', $benefits_arr) : $benefits_arr;
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    if ($is_edit) {
        $sql = "UPDATE careers SET position=:position, location=:location, salary=:salary, quantity=:quantity, 
                description=:description, benefits=:benefits, is_active=:is_active WHERE id=:id";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':id', $_GET['id']);
    } else {
        $sql = "INSERT INTO careers (position, location, salary, quantity, description, benefits, is_active) 
                VALUES (:position, :location, :salary, :quantity, :description, :benefits, :is_active)";
        $stmt = $db->prepare($sql);
    }

    $stmt->bindParam(':position', $position);
    $stmt->bindParam(':location', $location);
    $stmt->bindParam(':salary', $salary);
    $stmt->bindParam(':quantity', $quantity);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':benefits', $benefits);
    $stmt->bindParam(':is_active', $is_active);

    if ($stmt->execute()) {
        echo "<script>alert('บันทึกข้อมูลเรียบร้อยแล้ว'); window.location.href='careers.php';</script>";
    } else {
        echo "<script>alert('เกิดข้อผิดพลาดในการบันทึกข้อมูล');</script>";
    }
}
?>

<div class="page-header">
    <h1 class="page-title">
        <?php echo $is_edit ? 'แก้ไขตำแหน่งงาน' : 'เพิ่มตำแหน่งงานใหม่'; ?>
    </h1>
    <a href="careers.php" style="color: #666; text-decoration: none; margin-top: 10px; display: inline-block;">
        <i class="fa-solid fa-arrow-left"></i> ย้อนกลับไปหน้ารายการ
    </a>
</div>

<div class="card">
    <form method="POST" style="max-width: 800px;">
        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 5px; font-weight: 500;">ชื่อตำแหน่ง (Position)</label>
            <input type="text" name="position" value="<?php echo htmlspecialchars($c['position']); ?>" required
                placeholder="เช่น เจ้าหน้าที่การตลาด, Programmer"
                style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-family: 'Prompt';">
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 500;">สถานที่ปฏิบัติงาน</label>
                <input type="text" name="location" value="<?php echo htmlspecialchars($c['location']); ?>" required
                    placeholder="เช่น สำนักงานใหญ่ (บางใหญ่)"
                    style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-family: 'Prompt';">
            </div>
            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 500;">เงินเดือน</label>
                <input type="text" name="salary" value="<?php echo htmlspecialchars($c['salary']); ?>" required
                    placeholder="เช่น ตามโครงสร้างบริษัท, 20,000+"
                    style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-family: 'Prompt';">
            </div>
        </div>

        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 5px; font-weight: 500;">จำนวนอัตราที่รับ</label>
            <input type="text" name="quantity" value="<?php echo htmlspecialchars($c['quantity']); ?>" required
                placeholder="เช่น 2 อัตรา"
                style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-family: 'Prompt';">
        </div>

        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 5px; font-weight: 500;">รายละเอียดงาน / คุณสมบัติ
                (Description)</label>
            <textarea name="description" rows="8"
                style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-family: 'Prompt';"><?php echo htmlspecialchars($c['description']); ?></textarea>
            <div style="font-size: 0.8em; color: #888;">สามารถใช้ HTML tag พื้นฐานได้ เช่น &lt;br&gt;
                เพื่อขึ้นบรรทัดใหม่, &lt;li&gt; เพื่อทำรายการ</div>
        </div>

        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 10px; font-weight: 500;">สวัสดิการ (Benefits)</label>
            <div
                style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 10px; background: #f9f9f9; padding: 15px; border-radius: 4px; border: 1px solid #ddd;">
                <?php
                $benefit_options = [
                    'ประกันสังคม',
                    'โบนัสประจำปี',
                    'ประกันอุบัติเหตุ',
                    'ชุดยูนิฟอร์ม',
                    'กองทุนสำรองเลี้ยงชีพ',
                    'ตรวจสุขภาพประจำปี'
                ];
                $current_benefits = !empty($c['benefits']) ? explode(',', $c['benefits']) : [];
                ?>
                <?php foreach ($benefit_options as $option): ?>
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-weight: normal;">
                        <input type="checkbox" name="benefits[]" value="<?php echo $option; ?>" <?php echo in_array($option, $current_benefits) ? 'checked' : ''; ?>>
                        <span><?php echo $option; ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>

        <div style="margin-bottom: 30px;">
            <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                <input type="checkbox" name="is_active" value="1" <?php echo $c['is_active'] ? 'checked' : ''; ?>>
                <span>เปิดรับสมัคร (Active)</span>
            </label>
        </div>

        <button type="submit"
            style="background: var(--primary-blue); color: white; border: none; padding: 12px 30px; border-radius: 4px; font-size: 1rem; cursor: pointer; font-family: 'Prompt';">
            <i class="fa-solid fa-save"></i> บันทึกข้อมูล
        </button>
    </form>
</div>

<?php require_once 'includes/footer.php'; ?>