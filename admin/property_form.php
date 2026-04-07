<?php
session_start();
require_once 'config/db.php';
require_once 'includes/header.php';

$database = new Database();
$db = $database->getConnection();

// Self-Healing: Check and Add Missing Columns
try {
    $columns_to_check = [
        'property_code' => "VARCHAR(50) AFTER id",
        'land_size' => "VARCHAR(50) AFTER area",
        'usage_area' => "VARCHAR(50) AFTER land_size",
        'parking' => "VARCHAR(50) AFTER usage_area",
        'direction' => "VARCHAR(50) AFTER parking",
        'price_appraised' => "VARCHAR(50) AFTER price",
        'description' => "TEXT AFTER location",
        'map_url' => "TEXT AFTER description",
        'image_path_2' => "VARCHAR(255) AFTER image_path",
        'image_path_3' => "VARCHAR(255) AFTER image_path_2",
        'image_path_4' => "VARCHAR(255) AFTER image_path_3",
        'image_path_5' => "VARCHAR(255) AFTER image_path_4",
        'created_at' => "DATETIME DEFAULT CURRENT_TIMESTAMP"
    ];

    foreach ($columns_to_check as $col => $def) {
        $check = $db->query("SHOW COLUMNS FROM properties LIKE '$col'");
        if ($check->rowCount() == 0) {
            $db->exec("ALTER TABLE properties ADD COLUMN $col $def");
        }
    }
} catch (Exception $e) {
    // Continue
}

// 2. Self-Healing: Create property_leads table if not exists
try {
    $sql_leads = "CREATE TABLE IF NOT EXISTS property_leads (
        id INT AUTO_INCREMENT PRIMARY KEY,
        property_id INT NOT NULL,
        name VARCHAR(255) NOT NULL,
        phone VARCHAR(50) NOT NULL,
        line_id VARCHAR(50) NULL,
        message TEXT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        status ENUM('new', 'contacted', 'closed') DEFAULT 'new',
        FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    $db->exec($sql_leads);
} catch (PDOException $e) {
}

$p = [
    'title' => '',
    'type' => '',
    'location' => '',
    'price' => '',
    'bed' => 0,
    'bath' => 0,
    'area' => '',
    'land_size' => '',
    'usage_area' => '',
    'parking' => '',
    'direction' => '',
    'property_code' => '',
    'price_appraised' => '',
    'description' => '',
    'map_url' => '',
    'image_path' => '',
    'image_path_2' => '',
    'image_path_3' => '',
    'image_path_4' => '',
    'image_path_5' => '',
    'is_active' => 1
];
$is_edit = false;

if (isset($_GET['id'])) {
    $is_edit = true;
    $stmt = $db->prepare("SELECT * FROM properties WHERE id = :id");
    $stmt->bindParam(':id', $_GET['id']);
    $stmt->execute();
    $p = $stmt->fetch(PDO::FETCH_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $type = $_POST['type'];
    $location = $_POST['location'];
    $price = $_POST['price'];
    $bed = $_POST['bed'];
    $bath = $_POST['bath'];
    $area = $_POST['area'];
    $land_size = $_POST['land_size'];
    $usage_area = $_POST['usage_area'];
    $parking = $_POST['parking'];
    $direction = $_POST['direction'];
    $property_code = $_POST['property_code'];
    $price_appraised = $_POST['price_appraised'];
    $description = $_POST['description'];
    $map_url = $_POST['map_url'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    // Image Upload Function
    function uploadImage($file_key, $current_path)
    {
        if (isset($_FILES[$file_key]) && $_FILES[$file_key]['error'] == 0) {
            $target_dir = "../uploads/properties/";
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            $file_extension = pathinfo($_FILES[$file_key]["name"], PATHINFO_EXTENSION);
            $new_filename = uniqid() . '_' . $file_key . '.' . $file_extension;
            $target_db_path = "uploads/properties/" . $new_filename;
            $target_file = $target_dir . $new_filename;

            if (move_uploaded_file($_FILES[$file_key]["tmp_name"], $target_file)) {
                return $target_db_path;
            }
        }
        return $current_path;
    }

    $image_path = uploadImage('image', $p['image_path']);
    $image_path_2 = uploadImage('image_2', $p['image_path_2'] ?? ''); // Null check for existing records
    $image_path_3 = uploadImage('image_3', $p['image_path_3'] ?? '');
    $image_path_4 = uploadImage('image_4', $p['image_path_4'] ?? '');
    $image_path_5 = uploadImage('image_5', $p['image_path_5'] ?? '');

    if ($is_edit) {
        $sql = "UPDATE properties SET title=:title, type=:type, location=:location, price=:price, price_appraised=:price_appraised,
                bed=:bed, bath=:bath, area=:area, land_size=:land_size, usage_area=:usage_area, parking=:parking, direction=:direction, 
                property_code=:property_code, description=:description, map_url=:map_url,
                image_path=:image_path, image_path_2=:image_path_2, image_path_3=:image_path_3, image_path_4=:image_path_4, image_path_5=:image_path_5,
                is_active=:is_active WHERE id=:id";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':id', $_GET['id']);
    } else {
        $sql = "INSERT INTO properties (title, type, location, price, price_appraised, bed, bath, area, land_size, usage_area, parking, direction, property_code, description, map_url, image_path, image_path_2, image_path_3, image_path_4, image_path_5, is_active) 
                VALUES (:title, :type, :location, :price, :price_appraised, :bed, :bath, :area, :land_size, :usage_area, :parking, :direction, :property_code, :description, :map_url, :image_path, :image_path_2, :image_path_3, :image_path_4, :image_path_5, :is_active)";
        $stmt = $db->prepare($sql);
    }

    $stmt->bindParam(':title', $title);
    $stmt->bindParam(':type', $type);
    $stmt->bindParam(':location', $location);
    $stmt->bindParam(':price', $price);
    $stmt->bindParam(':price_appraised', $price_appraised);
    $stmt->bindParam(':bed', $bed);
    $stmt->bindParam(':bath', $bath);
    $stmt->bindParam(':area', $area);
    $stmt->bindParam(':land_size', $land_size);
    $stmt->bindParam(':usage_area', $usage_area);
    $stmt->bindParam(':parking', $parking);
    $stmt->bindParam(':direction', $direction);
    $stmt->bindParam(':property_code', $property_code);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':map_url', $map_url);
    $stmt->bindParam(':image_path', $image_path);
    $stmt->bindParam(':image_path_2', $image_path_2);
    $stmt->bindParam(':image_path_3', $image_path_3);
    $stmt->bindParam(':image_path_4', $image_path_4);
    $stmt->bindParam(':image_path_5', $image_path_5);
    $stmt->bindParam(':is_active', $is_active);

    if ($stmt->execute()) {
        echo "<script>alert('บันทึกข้อมูลเรียบร้อยแล้ว'); window.location.href='properties.php';</script>";
    } else {
        echo "<script>alert('เกิดข้อผิดพลาดในการบันทึกข้อมูล');</script>";
    }
}
?>

<div class="page-header">
    <h1 class="page-title">
        <?php echo $is_edit ? 'แก้ไขบ้าน คอนโด ที่ดิน' : 'เพิ่มบ้าน คอนโด ที่ดิน'; ?>
    </h1>
    <a href="properties.php" style="color: #666; text-decoration: none; margin-top: 10px; display: inline-block;">
        <i class="fa-solid fa-arrow-left"></i> ย้อนกลับไปหน้ารายการ
    </a>
</div>

<div class="card">
    <form method="POST" enctype="multipart/form-data" style="max-width: 800px;">
        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 5px; font-weight: 500;">ชื่อทรัพย์สิน (Headline)</label>
            <input type="text" name="title" value="<?php echo htmlspecialchars($p['title']); ?>" required
                placeholder="เช่น บ้านเดี่ยว 2 ชั้น หมู่บ้าน..."
                style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-family: 'Prompt';">
        </div>

        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 5px; font-weight: 500;">รหัสทรัพย์ (Property Code)</label>
            <input type="text" name="property_code" value="<?php echo htmlspecialchars($p['property_code'] ?? ''); ?>"
                placeholder="เช่น H-69001"
                style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-family: 'Prompt';">
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 500;">ประเภท (Type)</label>
                <select name="type"
                    style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-family: 'Prompt';">
                    <option value="บ้านเดี่ยว" <?php echo $p['type'] == 'บ้านเดี่ยว' ? 'selected' : ''; ?>>บ้านเดี่ยว
                    </option>
                    <option value="บ้านแฝด" <?php echo $p['type'] == 'บ้านแฝด' ? 'selected' : ''; ?>>บ้านแฝด</option>
                    <option value="ทาวน์โฮม" <?php echo $p['type'] == 'ทาวน์โฮม' ? 'selected' : ''; ?>>ทาวน์โฮม</option>
                    <option value="อาคารพาณิชย์" <?php echo $p['type'] == 'อาคารพาณิชย์' ? 'selected' : ''; ?>>
                        อาคารพาณิชย์</option>
                    <option value="คอนโด" <?php echo $p['type'] == 'คอนโด' ? 'selected' : ''; ?>>คอนโด</option>
                    <option value="ที่ดินเปล่า" <?php echo $p['type'] == 'ที่ดินเปล่า' ? 'selected' : ''; ?>>ที่ดินเปล่า
                    </option>
                </select>
            </div>
            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 500;">ราคา (Price)</label>
                <input type="text" name="price" value="<?php echo htmlspecialchars($p['price']); ?>" required
                    placeholder="เช่น 3.59 ลบ."
                    style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-family: 'Prompt';">
            </div>
            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 500;">ราคาประเมิน (Appraised
                    Price)</label>
                <input type="text" name="price_appraised"
                    value="<?php echo htmlspecialchars($p['price_appraised'] ?? ''); ?>" placeholder="เช่น 6.2 ล้านบาท"
                    style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-family: 'Prompt';">
            </div>
        </div>

        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 5px; font-weight: 500;">ทำเลที่ตั้ง (Location)</label>
            <input type="text" name="location" value="<?php echo htmlspecialchars($p['location']); ?>" required
                placeholder="เช่น อ.เมือง นครปฐม"
                style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-family: 'Prompt';">
        </div>

        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 5px; font-weight: 500;">รายละเอียดเพิ่มเติม
                (Description)</label>
            <textarea name="description" rows="6"
                style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-family: 'Prompt';"><?php echo htmlspecialchars($p['description'] ?? ''); ?></textarea>
        </div>

        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 5px; font-weight: 500;">Google Map Link (Embed URL)</label>
            <input type="text" name="map_url" value="<?php echo htmlspecialchars($p['map_url'] ?? ''); ?>"
                placeholder="URL จาก Google Maps Share > Embed a map"
                style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-family: 'Prompt';">
        </div>

        <div id="bed-bath-row"
            style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 20px;">
            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 500;">ห้องนอน</label>
                <input type="number" name="bed" value="<?php echo htmlspecialchars($p['bed']); ?>" min="0"
                    style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-family: 'Prompt';">
            </div>
            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 500;">ห้องน้ำ</label>
                <input type="number" name="bath" value="<?php echo htmlspecialchars($p['bath']); ?>" min="0"
                    style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-family: 'Prompt';">
            </div>
            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 500;">พื้นที่ (ไม่ระบุประเภท)</label>
                <input type="text" name="area" value="<?php echo htmlspecialchars($p['area']); ?>"
                    placeholder="เช่น 60 ตร.ว."
                    style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-family: 'Prompt';">
            </div>
        </div>

        <div id="additional-details-row"
            style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 20px;">
            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 500;" title="ขนาดที่ดิน">ขนาดที่ดิน
                    (Land)</label>
                <input type="text" name="land_size" value="<?php echo htmlspecialchars($p['land_size'] ?? ''); ?>"
                    placeholder="เช่น 60 ตร.ว."
                    style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-family: 'Prompt';">
            </div>
            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 500;" title="พื้นที่ใช้สอย">พื้นใช้สอย
                    (Usage)</label>
                <input type="text" name="usage_area" value="<?php echo htmlspecialchars($p['usage_area'] ?? ''); ?>"
                    placeholder="เช่น 180 ตร.ม."
                    style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-family: 'Prompt';">
            </div>
            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 500;">ที่จอดรถ</label>
                <input type="text" name="parking" value="<?php echo htmlspecialchars($p['parking'] ?? ''); ?>"
                    placeholder="เช่น 2 คัน"
                    style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-family: 'Prompt';">
            </div>
            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 500;">ทิศหน้าบ้าน</label>
                <input type="text" name="direction" value="<?php echo htmlspecialchars($p['direction'] ?? ''); ?>"
                    placeholder="เช่น เหนือ"
                    style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-family: 'Prompt';">
            </div>
        </div>

        <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 30px;">
            <h3 style="margin-top: 0; margin-bottom: 20px; font-size: 1.1em;">รูปภาพทรัพย์สิน (สูงสุด 5 รูป)</h3>

            <!-- Image 1 -->
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px; font-weight: 500;">รูปภาพหลัก (Main Image)</label>
                <?php if (!empty($p['image_path'])): ?>
                    <div style="margin-bottom: 5px;">
                        <img src="../<?php echo $p['image_path']; ?>" style="max-height: 100px; border: 1px solid #ddd;">
                    </div>
                <?php endif; ?>
                <input type="file" name="image" accept="image/*">
            </div>

            <!-- Image 2 -->
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px; font-weight: 500;">รูปภาพเพิ่มเติม 2</label>
                <?php if (!empty($p['image_path_2'])): ?>
                    <div style="margin-bottom: 5px;">
                        <img src="../<?php echo $p['image_path_2']; ?>" style="max-height: 100px; border: 1px solid #ddd;">
                    </div>
                <?php endif; ?>
                <input type="file" name="image_2" accept="image/*">
            </div>

            <!-- Image 3 -->
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px; font-weight: 500;">รูปภาพเพิ่มเติม 3</label>
                <?php if (!empty($p['image_path_3'])): ?>
                    <div style="margin-bottom: 5px;">
                        <img src="../<?php echo $p['image_path_3']; ?>" style="max-height: 100px; border: 1px solid #ddd;">
                    </div>
                <?php endif; ?>
                <input type="file" name="image_3" accept="image/*">
            </div>

            <!-- Image 4 -->
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px; font-weight: 500;">รูปภาพเพิ่มเติม 4</label>
                <?php if (!empty($p['image_path_4'])): ?>
                    <div style="margin-bottom: 5px;">
                        <img src="../<?php echo $p['image_path_4']; ?>" style="max-height: 100px; border: 1px solid #ddd;">
                    </div>
                <?php endif; ?>
                <input type="file" name="image_4" accept="image/*">
            </div>

            <!-- Image 5 -->
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px; font-weight: 500;">รูปภาพเพิ่มเติม 5</label>
                <?php if (!empty($p['image_path_5'])): ?>
                    <div style="margin-bottom: 5px;">
                        <img src="../<?php echo $p['image_path_5']; ?>" style="max-height: 100px; border: 1px solid #ddd;">
                    </div>
                <?php endif; ?>
                <input type="file" name="image_5" accept="image/*">
            </div>
        </div>

        <div style="margin-bottom: 30px;">
            <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                <input type="checkbox" name="is_active" value="1" <?php echo $p['is_active'] ? 'checked' : ''; ?>>
                <span>แสดงผลบนหน้าเว็บไซต์ (Active)</span>
            </label>
        </div>

        <button type="submit"
            style="background: var(--primary-blue); color: white; border: none; padding: 12px 30px; border-radius: 4px; font-size: 1rem; cursor: pointer; font-family: 'Prompt';">
            <i class="fa-solid fa-save"></i> บันทึกข้อมูล
        </button>
    </form>
</div>

<script>
    // Auto hide/show fields based on property type
    document.addEventListener('DOMContentLoaded', function () {
        var typeSelect = document.querySelector('select[name="type"]');
        var bedBathRow = document.getElementById('bed-bath-row');
        var additionalRow = document.getElementById('additional-details-row');

        // Get individual field containers
        var usageWrapper = additionalRow ? additionalRow.children[1] : null;
        var parkingWrapper = additionalRow ? additionalRow.children[2] : null;
        var directionWrapper = additionalRow ? additionalRow.children[3] : null;

        function toggleLandFields() {
            if (!typeSelect) return;
            var isLand = typeSelect.value === 'ที่ดินเปล่า';

            // Hide bed/bath/area row for land
            if (bedBathRow) {
                bedBathRow.style.display = isLand ? 'none' : 'grid';
            }

            // Hide usage area, parking, direction for land (keep land_size visible)
            if (usageWrapper) usageWrapper.style.display = isLand ? 'none' : 'block';
            if (parkingWrapper) parkingWrapper.style.display = isLand ? 'none' : 'block';
            if (directionWrapper) directionWrapper.style.display = isLand ? 'none' : 'block';

            // Clear values when hiding
            if (isLand) {
                var bedInput = document.querySelector('input[name="bed"]');
                var bathInput = document.querySelector('input[name="bath"]');
                var usageInput = document.querySelector('input[name="usage_area"]');
                var parkingInput = document.querySelector('input[name="parking"]');
                var directionInput = document.querySelector('input[name="direction"]');

                if (bedInput) bedInput.value = 0;
                if (bathInput) bathInput.value = 0;
                if (usageInput) usageInput.value = '';
                if (parkingInput) parkingInput.value = '';
                if (directionInput) directionInput.value = '';
            }
        }

        if (typeSelect) {
            typeSelect.addEventListener('change', toggleLandFields);
            toggleLandFields(); // Initial check
        }
    });
</script>

<?php require_once 'includes/footer.php'; ?>