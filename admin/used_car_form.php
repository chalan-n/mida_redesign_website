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

// Default values
$car = [
    'id' => '',
    'title' => '',
    'brand' => '',
    'model' => '',
    'car_year' => '',
    'car_color' => '',
    'license_plate' => '',
    'mileage' => '',
    'cc' => '',
    'transmission' => '',
    'price' => '',
    'price_original' => '',
    'description' => '',
    'car_type' => '',
    'image_path' => '',
    'image_path_2' => '',
    'image_path_3' => '',
    'image_path_4' => '',
    'image_path_5' => '',
    'inspection_body' => '',
    'inspection_engine' => '',
    'inspection_suspension' => '',
    'inspection_interior' => '',
    'inspection_tires' => '',
    'is_active' => 1,
    'is_featured' => 0,
];
$is_edit = false;

if (isset($_GET['id'])) {
    $is_edit = true;
    try {
        $stmt = $db->prepare("SELECT * FROM used_cars WHERE id = :id");
        $stmt->bindParam(':id', $_GET['id']);
        $stmt->execute();
        $car = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $brand = $_POST['brand'];
    $model = $_POST['model'];
    $car_year = $_POST['car_year'];
    $car_color = $_POST['car_color'];
    $license_plate = $_POST['license_plate'];
    $mileage = $_POST['mileage'];
    $cc = $_POST['cc'];
    $transmission = $_POST['transmission'];
    $price = $_POST['price'];
    $price_original = $_POST['price_original'];
    $description = $_POST['description'];
    $car_type = $_POST['car_type'];
    $inspection_body = $_POST['inspection_body'];
    $inspection_engine = $_POST['inspection_engine'];
    $inspection_suspension = $_POST['inspection_suspension'];
    $inspection_interior = $_POST['inspection_interior'];
    $inspection_tires = $_POST['inspection_tires'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;

    // Image Upload Function
    function uploadImage($file_key, $current_path)
    {
        if (isset($_FILES[$file_key]) && $_FILES[$file_key]['error'] == 0) {
            $upload_dir = 'uploads/used_cars/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            $ext = pathinfo($_FILES[$file_key]['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '.' . $ext;
            $target = $upload_dir . $filename;

            if (move_uploaded_file($_FILES[$file_key]['tmp_name'], $target)) {
                return $target;
            }
        }
        return $current_path;
    }

    $image_path = uploadImage('image', $car['image_path']);
    $image_path_2 = uploadImage('image_2', $car['image_path_2'] ?? '');
    $image_path_3 = uploadImage('image_3', $car['image_path_3'] ?? '');
    $image_path_4 = uploadImage('image_4', $car['image_path_4'] ?? '');
    $image_path_5 = uploadImage('image_5', $car['image_path_5'] ?? '');

    try {
        if ($is_edit) {
            $sql = "UPDATE used_cars SET title=:title, brand=:brand, model=:model, car_year=:car_year, 
                    car_color=:car_color, license_plate=:license_plate, mileage=:mileage, cc=:cc, 
                    transmission=:transmission, price=:price, price_original=:price_original, 
                    description=:description, car_type=:car_type, image_path=:image_path, 
                    image_path_2=:image_path_2, image_path_3=:image_path_3, image_path_4=:image_path_4, 
                    image_path_5=:image_path_5, inspection_body=:inspection_body, 
                    inspection_engine=:inspection_engine, inspection_suspension=:inspection_suspension, 
                    inspection_interior=:inspection_interior, inspection_tires=:inspection_tires, 
                    is_active=:is_active, is_featured=:is_featured WHERE id=:id";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':id', $_GET['id']);
        } else {
            $sql = "INSERT INTO used_cars (title, brand, model, car_year, car_color, license_plate, 
                    mileage, cc, transmission, price, price_original, description, car_type, 
                    image_path, image_path_2, image_path_3, image_path_4, image_path_5, 
                    inspection_body, inspection_engine, inspection_suspension, inspection_interior, 
                    inspection_tires, is_active, is_featured) 
                    VALUES (:title, :brand, :model, :car_year, :car_color, :license_plate, 
                    :mileage, :cc, :transmission, :price, :price_original, :description, :car_type, 
                    :image_path, :image_path_2, :image_path_3, :image_path_4, :image_path_5, 
                    :inspection_body, :inspection_engine, :inspection_suspension, :inspection_interior, 
                    :inspection_tires, :is_active, :is_featured)";
            $stmt = $db->prepare($sql);
        }

        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':brand', $brand);
        $stmt->bindParam(':model', $model);
        $stmt->bindParam(':car_year', $car_year);
        $stmt->bindParam(':car_color', $car_color);
        $stmt->bindParam(':license_plate', $license_plate);
        $stmt->bindParam(':mileage', $mileage);
        $stmt->bindParam(':cc', $cc);
        $stmt->bindParam(':transmission', $transmission);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':price_original', $price_original);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':car_type', $car_type);
        $stmt->bindParam(':image_path', $image_path);
        $stmt->bindParam(':image_path_2', $image_path_2);
        $stmt->bindParam(':image_path_3', $image_path_3);
        $stmt->bindParam(':image_path_4', $image_path_4);
        $stmt->bindParam(':image_path_5', $image_path_5);
        $stmt->bindParam(':inspection_body', $inspection_body);
        $stmt->bindParam(':inspection_engine', $inspection_engine);
        $stmt->bindParam(':inspection_suspension', $inspection_suspension);
        $stmt->bindParam(':inspection_interior', $inspection_interior);
        $stmt->bindParam(':inspection_tires', $inspection_tires);
        $stmt->bindParam(':is_active', $is_active);
        $stmt->bindParam(':is_featured', $is_featured);

        if ($stmt->execute()) {
            header("Location: used_cars.php");
            exit;
        }
    } catch (PDOException $e) {
        $error = "เกิดข้อผิดพลาด: " . $e->getMessage();
    }
}
?>

<div class="page-header">
    <h1 class="page-title">
        <?php echo $is_edit ? 'แก้ไขรถ' : 'เพิ่มรถใหม่'; ?>
    </h1>
    <a href="used_cars.php" style="color: #666; text-decoration: none; margin-top: 10px; display: inline-block;">
        <i class="fa-solid fa-arrow-left"></i> ย้อนกลับไปหน้ารายการ
    </a>
</div>

<?php if (isset($error)): ?>
    <div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
        <i class="fa-solid fa-exclamation-circle"></i>
        <?php echo $error; ?>
    </div>
<?php endif; ?>

<div class="card">
    <form method="POST" enctype="multipart/form-data" style="max-width: 900px;">

        <!-- Basic Info -->
        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-bottom: 20px;">
            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 500;">ชื่อรถ (Title) *</label>
                <input type="text" name="title" value="<?php echo htmlspecialchars($car['title']); ?>" required
                    placeholder="เช่น Toyota Camry 2.5 G"
                    style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-family: 'Prompt';">
            </div>
            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 500;">ปีรถ (Year)</label>
                <input type="text" name="car_year" value="<?php echo htmlspecialchars($car['car_year']); ?>"
                    placeholder="เช่น 2021"
                    style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-family: 'Prompt';">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; margin-bottom: 20px;">
            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 500;">ยี่ห้อ (Brand)</label>
                <input type="text" name="brand" value="<?php echo htmlspecialchars($car['brand']); ?>"
                    placeholder="เช่น Toyota"
                    style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-family: 'Prompt';">
            </div>
            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 500;">รุ่น (Model)</label>
                <input type="text" name="model" value="<?php echo htmlspecialchars($car['model']); ?>"
                    placeholder="เช่น Camry"
                    style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-family: 'Prompt';">
            </div>
            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 500;">ประเภทรถ (Type)</label>
                <select name="car_type"
                    style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-family: 'Prompt';">
                    <option value="">-- เลือกประเภท --</option>
                    <option value="รถเก๋ง" <?php echo $car['car_type'] == 'รถเก๋ง' ? 'selected' : ''; ?>>รถเก๋ง</option>
                    <option value="รถกระบะ" <?php echo $car['car_type'] == 'รถกระบะ' ? 'selected' : ''; ?>>รถกระบะ
                    </option>
                    <option value="รถ SUV" <?php echo $car['car_type'] == 'รถ SUV' ? 'selected' : ''; ?>>รถ SUV</option>
                    <option value="รถตู้" <?php echo $car['car_type'] == 'รถตู้' ? 'selected' : ''; ?>>รถตู้</option>
                    <option value="รถสปอร์ต" <?php echo $car['car_type'] == 'รถสปอร์ต' ? 'selected' : ''; ?>>รถสปอร์ต
                    </option>
                    <option value="มอเตอร์ไซค์" <?php echo $car['car_type'] == 'มอเตอร์ไซค์' ? 'selected' : ''; ?>>
                        มอเตอร์ไซค์</option>
                </select>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 20px; margin-bottom: 20px;">
            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 500;">สี (Color)</label>
                <input type="text" name="car_color" value="<?php echo htmlspecialchars($car['car_color']); ?>"
                    placeholder="เช่น ดำ"
                    style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-family: 'Prompt';">
            </div>
            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 500;">เลขไมล์ (Mileage)</label>
                <input type="text" name="mileage" value="<?php echo htmlspecialchars($car['mileage']); ?>"
                    placeholder="เช่น 50,000 กม."
                    style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-family: 'Prompt';">
            </div>
            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 500;">CC.</label>
                <input type="text" name="cc" value="<?php echo htmlspecialchars($car['cc']); ?>"
                    placeholder="เช่น 2,500"
                    style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-family: 'Prompt';">
            </div>
            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 500;">เกียร์ (Transmission)</label>
                <select name="transmission"
                    style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-family: 'Prompt';">
                    <option value="">-- เลือก --</option>
                    <option value="ออโต้" <?php echo $car['transmission'] == 'ออโต้' ? 'selected' : ''; ?>>ออโต้</option>
                    <option value="ธรรมดา" <?php echo $car['transmission'] == 'ธรรมดา' ? 'selected' : ''; ?>>ธรรมดา
                    </option>
                </select>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; margin-bottom: 20px;">
            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 500;">ราคาขาย *</label>
                <input type="text" name="price" value="<?php echo htmlspecialchars($car['price']); ?>" required
                    placeholder="เช่น 599,000 บาท"
                    style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-family: 'Prompt';">
            </div>
            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 500;">ราคาเดิม (สำหรับแสดงส่วนลด)</label>
                <input type="text" name="price_original" value="<?php echo htmlspecialchars($car['price_original']); ?>"
                    placeholder="เช่น 699,000 บาท"
                    style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-family: 'Prompt';">
            </div>
            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 500;">ป้ายทะเบียน</label>
                <input type="text" name="license_plate" value="<?php echo htmlspecialchars($car['license_plate']); ?>"
                    placeholder="เช่น 1กข 1234"
                    style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-family: 'Prompt';">
            </div>
        </div>

        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 5px; font-weight: 500;">รายละเอียดเพิ่มเติม</label>
            <textarea name="description" rows="4" placeholder="รายละเอียดรถเพิ่มเติม..."
                style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-family: 'Prompt';"><?php echo htmlspecialchars($car['description']); ?></textarea>
        </div>

        <!-- Inspection Section -->
        <h3 style="margin: 30px 0 20px; padding-bottom: 10px; border-bottom: 2px solid #eee;">
            <i class="fa-solid fa-clipboard-check"></i> รายงานการตรวจสภาพ
        </h3>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 500;">สภาพตัวถัง (Body)</label>
                <input type="text" name="inspection_body"
                    value="<?php echo htmlspecialchars($car['inspection_body']); ?>"
                    placeholder="เช่น สภาพดี ไม่มีอุบัติเหตุ"
                    style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-family: 'Prompt';">
            </div>
            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 500;">สภาพเครื่องยนต์ (Engine)</label>
                <input type="text" name="inspection_engine"
                    value="<?php echo htmlspecialchars($car['inspection_engine']); ?>"
                    placeholder="เช่น เครื่องเดินเรียบ ไม่มีควัน"
                    style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-family: 'Prompt';">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; margin-bottom: 20px;">
            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 500;">ระบบช่วงล่าง (Suspension)</label>
                <input type="text" name="inspection_suspension"
                    value="<?php echo htmlspecialchars($car['inspection_suspension']); ?>"
                    placeholder="เช่น ไม่มีเสียงผิดปกติ"
                    style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-family: 'Prompt';">
            </div>
            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 500;">สภาพภายใน (Interior)</label>
                <input type="text" name="inspection_interior"
                    value="<?php echo htmlspecialchars($car['inspection_interior']); ?>"
                    placeholder="เช่น สะอาด ไม่มีกลิ่น"
                    style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-family: 'Prompt';">
            </div>
            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 500;">ยางรถ (Tires)</label>
                <input type="text" name="inspection_tires"
                    value="<?php echo htmlspecialchars($car['inspection_tires']); ?>" placeholder="เช่น ยางใหม่ 80%"
                    style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-family: 'Prompt';">
            </div>
        </div>

        <!-- Images Section -->
        <h3 style="margin: 30px 0 20px; padding-bottom: 10px; border-bottom: 2px solid #eee;">
            <i class="fa-solid fa-images"></i> รูปภาพ (สูงสุด 5 รูป)
        </h3>

        <div style="display: grid; grid-template-columns: repeat(5, 1fr); gap: 15px; margin-bottom: 20px;">
            <?php for ($i = 1; $i <= 5; $i++):
                $img_key = $i == 1 ? 'image_path' : 'image_path_' . $i;
                $input_name = $i == 1 ? 'image' : 'image_' . $i;
                ?>
                <div style="text-align: center;">
                    <label style="display: block; margin-bottom: 5px; font-weight: 500;">รูปที่
                        <?php echo $i; ?>
                    </label>
                    <?php if (!empty($car[$img_key])): ?>
                        <img src="<?php echo htmlspecialchars($car[$img_key]); ?>"
                            style="width: 100%; height: 80px; object-fit: cover; border-radius: 4px; margin-bottom: 5px;">
                    <?php else: ?>
                        <div
                            style="width: 100%; height: 80px; background: #f1f5f9; border-radius: 4px; margin-bottom: 5px; display: flex; align-items: center; justify-content: center;">
                            <i class="fa-solid fa-image" style="color: #ccc; font-size: 1.5rem;"></i>
                        </div>
                    <?php endif; ?>
                    <input type="file" name="<?php echo $input_name; ?>" accept="image/*"
                        style="width: 100%; font-size: 0.8rem;">
                </div>
            <?php endfor; ?>
        </div>

        <!-- Status -->
        <div style="display: flex; gap: 30px; margin: 30px 0;">
            <label style="display: flex; align-items: center; cursor: pointer;">
                <input type="checkbox" name="is_active" <?php echo $car['is_active'] ? 'checked' : ''; ?>
                    style="width: 20px; height: 20px; margin-right: 10px;">
                <span>แสดงหน้าเว็บไซต์</span>
            </label>
            <label style="display: flex; align-items: center; cursor: pointer;">
                <input type="checkbox" name="is_featured" <?php echo $car['is_featured'] ? 'checked' : ''; ?>
                    style="width: 20px; height: 20px; margin-right: 10px;">
                <span><i class="fa-solid fa-star" style="color: #ffc107;"></i> รถเด่น (Featured)</span>
            </label>
        </div>

        <button type="submit" class="btn btn-primary" style="padding: 12px 30px;">
            <i class="fa-solid fa-save"></i> บันทึกข้อมูล
        </button>
    </form>
</div>

<?php require_once 'includes/footer.php'; ?>