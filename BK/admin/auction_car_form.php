<?php
session_start();
require_once 'config/db.php';
require_once 'includes/header.php';

$database = new Database();
$db = $database->getConnection();


$car = [
    'title' => '',
    'brand' => '',
    'car_type' => '',
    'grade' => '',
    'mileage' => '',
    'transmission' => '',
    'price' => '',
    'inspection_body' => '',
    'inspection_engine' => '',
    'inspection_suspension' => '',
    'inspection_interior' => '',
    'inspection_tires' => '',
    'image_path' => ''
];
$is_edit = false;

// Check if editing
if (isset($_GET['id'])) {
    $is_edit = true;
    $stmt = $db->prepare("SELECT * FROM auction_cars WHERE id = :id");
    $stmt->bindParam(':id', $_GET['id']);
    $stmt->execute();
    $car = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Initialize default values for new fields
$car['queue_number'] = isset($car['queue_number']) ? $car['queue_number'] : '';
$car['image_path_2'] = isset($car['image_path_2']) ? $car['image_path_2'] : '';
$car['image_path_3'] = isset($car['image_path_3']) ? $car['image_path_3'] : '';
$car['image_path_4'] = isset($car['image_path_4']) ? $car['image_path_4'] : '';
$car['image_path_5'] = isset($car['image_path_5']) ? $car['image_path_5'] : '';
$car['brand'] = isset($car['brand']) ? $car['brand'] : '';
$car['car_type'] = isset($car['car_type']) ? $car['car_type'] : '';
$car['inspection_body'] = isset($car['inspection_body']) ? $car['inspection_body'] : '';
$car['inspection_engine'] = isset($car['inspection_engine']) ? $car['inspection_engine'] : '';
$car['inspection_suspension'] = isset($car['inspection_suspension']) ? $car['inspection_suspension'] : '';
$car['inspection_interior'] = isset($car['inspection_interior']) ? $car['inspection_interior'] : '';
$car['inspection_tires'] = isset($car['inspection_tires']) ? $car['inspection_tires'] : '';
$car['car_color'] = isset($car['car_color']) ? $car['car_color'] : '';
$car['license_plate'] = isset($car['license_plate']) ? $car['license_plate'] : '';
$car['auction_price'] = isset($car['auction_price']) ? $car['auction_price'] : '';
$car['no_starting_price'] = isset($car['no_starting_price']) ? $car['no_starting_price'] : 0;
$car['cc'] = isset($car['cc']) ? $car['cc'] : '';
$car['car_year'] = isset($car['car_year']) ? $car['car_year'] : '';

$form_action = "";
if ($is_edit) {
    $form_action = "?id=" . htmlspecialchars($_GET['id']);
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $grade = $_POST['grade'];
    $mileage = $_POST['mileage'];
    $transmission = $_POST['transmission'];
    $price = $_POST['price'];
    $queue_number = $_POST['queue_number'];
    $brand = $_POST['brand'];
    $car_type = $_POST['car_type'];
    $inspection_body = $_POST['inspection_body'];
    $inspection_engine = $_POST['inspection_engine'];
    $inspection_suspension = $_POST['inspection_suspension'];
    $inspection_interior = $_POST['inspection_interior'];
    $inspection_tires = $_POST['inspection_tires'];
    $car_color = $_POST['car_color'];
    $license_plate = $_POST['license_plate'];
    $auction_price = $_POST['auction_price'];
    $no_starting_price = isset($_POST['no_starting_price']) ? 1 : 0;
    $cc = $_POST['cc'];
    $car_year = $_POST['car_year'];

    // Image Upload Function
    function uploadImage($file_key, $current_path)
    {
        if (isset($_FILES[$file_key]) && $_FILES[$file_key]['error'] == 0) {
            $target_dir = "../uploads/auction/";
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            $file_extension = pathinfo($_FILES[$file_key]["name"], PATHINFO_EXTENSION);
            $new_filename = uniqid() . '_' . $file_key . '.' . $file_extension;
            $target_db_path = "uploads/auction/" . $new_filename;
            $target_file = $target_dir . $new_filename;

            if (move_uploaded_file($_FILES[$file_key]["tmp_name"], $target_file)) {
                return $target_db_path;
            }
        }
        return $current_path;
    }

    $image_path = uploadImage('image', $car['image_path']);
    $image_path_2 = uploadImage('image_2', $car['image_path_2']);
    $image_path_3 = uploadImage('image_3', $car['image_path_3']);
    $image_path_4 = uploadImage('image_4', $car['image_path_4']);
    $image_path_5 = uploadImage('image_5', $car['image_path_5']);

    if ($is_edit) {
        $sql = "UPDATE auction_cars SET title = :title, car_year = :car_year, brand = :brand, car_type = :car_type, car_color = :car_color, license_plate = :license_plate, auction_price = :auction_price, grade = :grade, mileage = :mileage, cc = :cc, transmission = :transmission, price = :price, queue_number = :queue_number, image_path = :image_path, image_path_2 = :image_path_2, image_path_3 = :image_path_3, image_path_4 = :image_path_4, image_path_5 = :image_path_5, inspection_body = :inspection_body, inspection_engine = :inspection_engine, inspection_suspension = :inspection_suspension, inspection_interior = :inspection_interior, inspection_tires = :inspection_tires, no_starting_price = :no_starting_price WHERE id = :id";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':id', $_GET['id']);
    } else {
        $sql = "INSERT INTO auction_cars (title, car_year, brand, car_type, car_color, license_plate, auction_price, grade, mileage, cc, transmission, price, queue_number, image_path, image_path_2, image_path_3, image_path_4, image_path_5, inspection_body, inspection_engine, inspection_suspension, inspection_interior, inspection_tires, no_starting_price) VALUES (:title, :car_year, :brand, :car_type, :car_color, :license_plate, :auction_price, :grade, :mileage, :cc, :transmission, :price, :queue_number, :image_path, :image_path_2, :image_path_3, :image_path_4, :image_path_5, :inspection_body, :inspection_engine, :inspection_suspension, :inspection_interior, :inspection_tires, :no_starting_price)";
        $stmt = $db->prepare($sql);
    }

    $stmt->bindParam(':title', $title);
    $stmt->bindParam(':car_year', $car_year);
    $stmt->bindParam(':brand', $brand);
    $stmt->bindParam(':car_type', $car_type);
    $stmt->bindParam(':inspection_body', $inspection_body);
    $stmt->bindParam(':inspection_engine', $inspection_engine);
    $stmt->bindParam(':inspection_suspension', $inspection_suspension);
    $stmt->bindParam(':inspection_interior', $inspection_interior);
    $stmt->bindParam(':inspection_tires', $inspection_tires);
    $stmt->bindParam(':grade', $grade);
    $stmt->bindParam(':mileage', $mileage);
    $stmt->bindParam(':cc', $cc);
    $stmt->bindParam(':transmission', $transmission);
    $stmt->bindParam(':price', $price);
    $stmt->bindParam(':queue_number', $queue_number);
    $stmt->bindParam(':image_path', $image_path);
    $stmt->bindParam(':image_path_2', $image_path_2);
    $stmt->bindParam(':image_path_3', $image_path_3);
    $stmt->bindParam(':image_path_4', $image_path_4);
    $stmt->bindParam(':image_path_5', $image_path_5);
    $stmt->bindParam(':car_color', $car_color);
    $stmt->bindParam(':license_plate', $license_plate);
    $stmt->bindParam(':auction_price', $auction_price);
    $stmt->bindParam(':no_starting_price', $no_starting_price);

    if ($stmt->execute()) {
        echo "<script>alert('บันทึกข้อมูลเรียบร้อยแล้ว'); window.location.href='auction_cars.php';</script>";
        exit;
    } else {
        echo "<script>alert('เกิดข้อผิดพลาดในการบันทึกข้อมูล');</script>";
    }
}
?>

<div class="page-header">
    <h1 class="page-title">
        <?php echo $is_edit ? 'แก้ไขข้อมูลรถประมูล' : 'เพิ่มรถประมูลใหม่'; ?>
    </h1>
    <a href="auction_cars.php" style="color: #666; text-decoration: none; margin-top: 10px; display: inline-block;">
        <i class="fa-solid fa-arrow-left"></i> ย้อนกลับไปหน้ารายการ
    </a>
</div>

<div class="card">
    <form method="POST" enctype="multipart/form-data" style="max-width: 800px;">

        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 5px; font-weight: 500;">ลำดับรถ / คันที่ (Queue Number)</label>
            <input type="text" name="queue_number" value="<?php echo htmlspecialchars($car['queue_number']); ?>"
                placeholder="เช่น 101, 1A"
                style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-family: 'Prompt';">
        </div>

        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 5px; font-weight: 500;">ชื่อรุ่น (Title)</label>
            <input type="text" name="title" value="<?php echo htmlspecialchars($car['title']); ?>" required
                style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-family: 'Prompt';">
        </div>

        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 5px; font-weight: 500;">ปีรถ (Car Year)</label>
            <input type="text" name="car_year" value="<?php echo htmlspecialchars($car['car_year']); ?>"
                placeholder="เช่น 2020, 2565"
                style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-family: 'Prompt';">
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 500;">ยี่ห้อ (Brand)</label>
                <!-- You might want to make this a datalist or select in the future -->
                <input type="text" name="brand" value="<?php echo htmlspecialchars($car['brand']); ?>"
                    placeholder="เช่น Toyota, Honda"
                    style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-family: 'Prompt';">
            </div>
            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 500;">ประเภทรถ (Car Type)</label>
                <select name="car_type"
                    style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-family: 'Prompt';">
                    <option value="">-- เลือกประเภท --</option>
                    <option value="รถเก๋ง" <?php echo $car['car_type'] == 'รถเก๋ง' ? 'selected' : ''; ?>>รถเก๋ง (Sedan)
                    </option>
                    <option value="รถกระบะ" <?php echo $car['car_type'] == 'รถกระบะ' ? 'selected' : ''; ?>>รถกระบะ
                        (Pickup)</option>
                    <option value="รถตู้" <?php echo $car['car_type'] == 'รถตู้' ? 'selected' : ''; ?>>รถตู้ (Van)
                    </option>
                    <option value="รถบรรทุก" <?php echo $car['car_type'] == 'รถบรรทุก' ? 'selected' : ''; ?>>รถบรรทุก
                        (Truck)</option>
                    <option value="รถเอสยูวี" <?php echo $car['car_type'] == 'รถเอสยูวี' ? 'selected' : ''; ?>>รถเอสยูวี
                        (SUV)</option>
                    <option value="อื่นๆ" <?php echo $car['car_type'] == 'อื่นๆ' ? 'selected' : ''; ?>>อื่นๆ (Other)
                    </option>
                </select>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 500;">เกรดรถ (Grade)</label>
                <select name="grade"
                    style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-family: 'Prompt';">
                    <option value="A" <?php echo $car['grade'] == 'A' ? 'selected' : ''; ?>>Grade A</option>
                    <option value="B" <?php echo $car['grade'] == 'B' ? 'selected' : ''; ?>>Grade B</option>
                    <option value="C" <?php echo $car['grade'] == 'C' ? 'selected' : ''; ?>>Grade C</option>
                    <option value="D" <?php echo $car['grade'] == 'D' ? 'selected' : ''; ?>>Grade D</option>
                </select>
            </div>
            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 500;">ระบบเกียร์</label>
                <select name="transmission"
                    style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-family: 'Prompt';">
                    <option value="Auto" <?php echo $car['transmission'] == 'Auto' ? 'selected' : ''; ?>>Auto (อัตโนมัติ)
                    </option>
                    <option value="Manual" <?php echo $car['transmission'] == 'Manual' ? 'selected' : ''; ?>>Manual
                        (ธรรมดา)</option>
                </select>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; margin-bottom: 20px;">
            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 500;">เลขไมล์ (กม.)</label>
                <input type="text" name="mileage" value="<?php echo htmlspecialchars($car['mileage']); ?>"
                    placeholder="เช่น 45,xxx"
                    style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-family: 'Prompt';">
            </div>
            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 500;">CC (ขนาดเครื่องยนต์)</label>
                <input type="text" name="cc" value="<?php echo htmlspecialchars($car['cc']); ?>"
                    placeholder="เช่น 1500, 2000"
                    style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-family: 'Prompt';">
            </div>
            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 500;">ราคาเปิดประมูล (บาท)</label>
                <input type="text" name="price" value="<?php echo htmlspecialchars($car['price']); ?>" required
                    placeholder="เช่น 359,000.-"
                    style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-family: 'Prompt';">
            </div>
        </div>

        <!-- Checkbox: ไม่มีราคาเริ่มต้น -->
        <div style="margin-bottom: 20px; padding: 15px; background: #fff8e1; border: 1px solid #ffc107; border-radius: 8px;">
            <label style="display: flex; align-items: center; cursor: pointer; font-weight: 500;">
                <input type="checkbox" name="no_starting_price" value="1" 
                    <?php echo $car['no_starting_price'] == 1 ? 'checked' : ''; ?>
                    style="width: 20px; height: 20px; margin-right: 10px; cursor: pointer;">
                <span style="color: #856404;">
                    <i class="fa-solid fa-tag" style="margin-right: 5px;"></i>
                    รถไม่มีราคาเริ่มต้น (แสดงข้อความ "ไม่มีราคาเริ่มต้น" แทนราคาบนหน้าเว็บ)
                </span>
            </label>
        </div>

        <!-- New Fields: Car Color, License Plate, Auction Price -->
        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; margin-bottom: 20px;">
            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 500;">สีรถ (Car Color)</label>
                <input type="text" name="car_color" value="<?php echo htmlspecialchars($car['car_color']); ?>"
                    placeholder="เช่น ขาว, ดำ, เงิน"
                    style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-family: 'Prompt';">
            </div>
            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 500;">ทะเบียนรถ (License Plate)</label>
                <input type="text" name="license_plate" value="<?php echo htmlspecialchars($car['license_plate']); ?>"
                    placeholder="เช่น 1กก 1234"
                    style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-family: 'Prompt';">
            </div>
            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 500;">ราคาประมูล (Auction Price)</label>
                <input type="text" name="auction_price" value="<?php echo htmlspecialchars($car['auction_price']); ?>"
                    placeholder="เช่น 320,000.-"
                    style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-family: 'Prompt';">
            </div>
        </div>

        <div style="background: white; padding: 20px; border: 1px solid #ddd; border-radius: 8px; margin-bottom: 30px;">
            <h3
                style="margin-top: 0; margin-bottom: 20px; font-size: 1.1em; border-bottom: 1px solid #eee; padding-bottom: 10px;">
                รายงานการตรวจสภาพ (Inspection Report)</h3>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <!-- Body -->
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: 500;">สภาพตัวถัง (Body)</label>
                    <div style="display: flex; gap: 10px;">
                        <input type="text" name="inspection_body"
                            value="<?php echo htmlspecialchars($car['inspection_body']); ?>"
                            placeholder="เช่น ปกติ / เดิมบาง"
                            style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-family: 'Prompt';">
                    </div>
                </div>

                <!-- Engine -->
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: 500;">สภาพเครื่องยนต์
                        (Engine)</label>
                    <input type="text" name="inspection_engine"
                        value="<?php echo htmlspecialchars($car['inspection_engine']); ?>"
                        placeholder="เช่น สตาร์ทติดง่าย / ปกติ"
                        style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-family: 'Prompt';">
                </div>

                <!-- Suspension -->
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: 500;">เกียร์ / ช่วงล่าง
                        (Trans/Susp)</label>
                    <input type="text" name="inspection_suspension"
                        value="<?php echo htmlspecialchars($car['inspection_suspension']); ?>"
                        placeholder="เช่น ปกติ / เข้าเกียร์นุ่ม"
                        style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-family: 'Prompt';">
                </div>

                <!-- Interior -->
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: 500;">สภาพภายใน (Interior)</label>
                    <input type="text" name="inspection_interior"
                        value="<?php echo htmlspecialchars($car['inspection_interior']); ?>"
                        placeholder="เช่น สะอาด / เบาะไม่ขาด"
                        style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-family: 'Prompt';">
                </div>

                <!-- Tires -->
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: 500;">ยางรถยนต์ (Tires)</label>
                    <input type="text" name="inspection_tires"
                        value="<?php echo htmlspecialchars($car['inspection_tires']); ?>"
                        placeholder="เช่น ดี 4 เส้น / ควรเปลี่ยน"
                        style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-family: 'Prompt';">
                </div>
            </div>
            <div style="margin-top: 10px; font-size: 0.85rem; color: #666;">
                * <strong>เคล็ดลับ</strong>: หากต้องการให้แสดงไอคอน "แจ้งเตือน (สีเหลือง)" ให้พิมพ์คำว่า
                <strong>"ควร"</strong> หรือ <strong>"ต้อง"</strong> หรือ <strong>"Warning"</strong> หรือ
                <strong>"Fail"</strong> ลงในช่องข้อความ ระบบจะจับคำอัตโนมัติ
            </div>
        </div>

        <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 30px;">
            <h3 style="margin-top: 0; margin-bottom: 20px; font-size: 1.1em;">รูปภาพรถยนต์ (สูงสุด 5 รูป)</h3>

            <!-- Image 1 -->
            <div style="margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px; font-weight: 500;">รูปหลัก (Cover Image)</label>
                <?php if (!empty($car['image_path'])): ?>
                    <div style="margin-bottom: 10px;">
                        <img src="../<?php echo $car['image_path']; ?>" style="max-width: 150px; border-radius: 4px;"
                            alt="Current Image">
                    </div>
                <?php endif; ?>
                <input type="file" name="image" accept="image/*" style="font-family: 'Prompt';">
            </div>

            <!-- Image 2 -->
            <div style="margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px; font-weight: 500;">รูปที่ 2</label>
                <?php if (!empty($car['image_path_2'])): ?>
                    <div style="margin-bottom: 10px;">
                        <img src="../<?php echo $car['image_path_2']; ?>" style="max-width: 150px; border-radius: 4px;"
                            alt="Current Image">
                    </div>
                <?php endif; ?>
                <input type="file" name="image_2" accept="image/*" style="font-family: 'Prompt';">
            </div>

            <!-- Image 3 -->
            <div style="margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px; font-weight: 500;">รูปที่ 3</label>
                <?php if (!empty($car['image_path_3'])): ?>
                    <div style="margin-bottom: 10px;">
                        <img src="../<?php echo $car['image_path_3']; ?>" style="max-width: 150px; border-radius: 4px;"
                            alt="Current Image">
                    </div>
                <?php endif; ?>
                <input type="file" name="image_3" accept="image/*" style="font-family: 'Prompt';">
            </div>

            <!-- Image 4 -->
            <div style="margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px; font-weight: 500;">รูปที่ 4</label>
                <?php if (!empty($car['image_path_4'])): ?>
                    <div style="margin-bottom: 10px;">
                        <img src="../<?php echo $car['image_path_4']; ?>" style="max-width: 150px; border-radius: 4px;"
                            alt="Current Image">
                    </div>
                <?php endif; ?>
                <input type="file" name="image_4" accept="image/*" style="font-family: 'Prompt';">
            </div>

            <!-- Image 5 -->
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 5px; font-weight: 500;">รูปที่ 5</label>
                <?php if (!empty($car['image_path_5'])): ?>
                    <div style="margin-bottom: 10px;">
                        <img src="../<?php echo $car['image_path_5']; ?>" style="max-width: 150px; border-radius: 4px;"
                            alt="Current Image">
                    </div>
                <?php endif; ?>
                <input type="file" name="image_5" accept="image/*" style="font-family: 'Prompt';">
            </div>

            <div style="font-size: 0.8em; color: #888;">รองรับไฟล์ JPG, PNG</div>
        </div>

        <button type="submit"
            style="background: var(--primary-blue); color: white; border: none; padding: 12px 30px; border-radius: 4px; font-size: 1rem; cursor: pointer; font-family: 'Prompt';">
            <i class="fa-solid fa-save"></i> บันทึกข้อมูล
        </button>
    </form>
</div><?php require_once 'includes/footer.php'; ?>