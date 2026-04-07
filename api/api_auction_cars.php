<?php
/**
 * Auction Cars API Endpoint
 * รับข้อมูลรถประมูลจากระบบภายนอก
 * 
 * Method: POST
 * Header: X-API-Key
 * Content-Type: application/json
 * 
 * รองรับรูปภาพแบบ Base64 (สูงสุด 5 รูป)
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-API-Key');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once 'api_config.php';

// Helper function to send JSON response
function sendResponse($success, $message, $data = null, $code = 200)
{
    http_response_code($code);
    $response = [
        'success' => $success,
        'message' => $message
    ];
    if ($data !== null) {
        $response['data'] = $data;
    }
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Save Base64 image to uploads folder
 * @param string $base64String Base64 encoded image (with or without data URI prefix)
 * @param string $prefix Filename prefix
 * @return string|null Relative path to saved image or null on failure
 */
function saveBase64Image($base64String, $prefix = 'img')
{
    if (empty($base64String)) {
        return '';
    }

    // Remove data URI scheme if present
    if (preg_match('/^data:image\/(\w+);base64,/', $base64String, $matches)) {
        $extension = $matches[1];
        $base64String = substr($base64String, strpos($base64String, ',') + 1);
    } else {
        // Try to detect image type from base64 content
        $extension = 'jpg'; // Default extension
    }

    // Decode base64
    $imageData = base64_decode($base64String);
    if ($imageData === false) {
        return '';
    }

    // Validate that it's actually an image
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->buffer($imageData);

    if (strpos($mimeType, 'image/') !== 0) {
        return ''; // Not an image
    }

    // Get extension from mime type
    $mimeToExt = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp'
    ];
    $extension = isset($mimeToExt[$mimeType]) ? $mimeToExt[$mimeType] : 'jpg';

    // Create upload directory if not exists
    $uploadDir = __DIR__ . '/../uploads/auction/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Generate unique filename
    $filename = uniqid($prefix . '_') . '.' . $extension;
    $filePath = $uploadDir . $filename;

    // Save file
    if (file_put_contents($filePath, $imageData) !== false) {
        return 'uploads/auction/' . $filename;
    }

    return '';
}

// Check request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, 'Method Not Allowed. ใช้ POST เท่านั้น', null, 405);
}

// Check API Key
$headers = getallheaders();
$apiKey = isset($headers['X-API-Key']) ? $headers['X-API-Key'] : (isset($headers['X-Api-Key']) ? $headers['X-Api-Key'] : null);

if (!$apiKey || $apiKey !== API_KEY) {
    sendResponse(false, 'API Key ไม่ถูกต้อง', null, 401);
}

// Get JSON input
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    sendResponse(false, 'Invalid JSON format', null, 400);
}

// Validate required fields
if (empty($data['title'])) {
    sendResponse(false, 'กรุณาระบุ title (ชื่อรุ่น/ปีรถ)', null, 400);
}
if (empty($data['price'])) {
    sendResponse(false, 'กรุณาระบุ price (ราคาเปิดประมูล)', null, 400);
}

// Connect to database
$database = new ApiDatabase();
$db = $database->getConnection();

if (!$db) {
    sendResponse(false, 'Database connection failed', null, 500);
}

// Prepare text data
$title = trim($data['title']);
$brand = isset($data['brand']) ? trim($data['brand']) : '';
$car_type = isset($data['car_type']) ? trim($data['car_type']) : '';
$grade = isset($data['grade']) ? trim($data['grade']) : '';
$mileage = isset($data['mileage']) ? trim($data['mileage']) : '';
$transmission = isset($data['transmission']) ? trim($data['transmission']) : '';
$price = trim($data['price']);
$queue_number = isset($data['queue_number']) ? trim($data['queue_number']) : '';
$car_color = isset($data['car_color']) ? trim($data['car_color']) : '';
$license_plate = isset($data['license_plate']) ? trim($data['license_plate']) : '';
$auction_price = isset($data['auction_price']) ? trim($data['auction_price']) : '';
$inspection_body = isset($data['inspection_body']) ? trim($data['inspection_body']) : '';
$inspection_engine = isset($data['inspection_engine']) ? trim($data['inspection_engine']) : '';
$inspection_suspension = isset($data['inspection_suspension']) ? trim($data['inspection_suspension']) : '';
$inspection_interior = isset($data['inspection_interior']) ? trim($data['inspection_interior']) : '';
$inspection_tires = isset($data['inspection_tires']) ? trim($data['inspection_tires']) : '';
$no_starting_price = isset($data['no_starting_price']) ? trim($data['no_starting_price']) : '0';
$cc = isset($data['cc']) ? trim($data['cc']) : '';
$car_year = isset($data['car_year']) ? trim($data['car_year']) : '';

// Process images (Base64)
$image_path = '';
$image_path_2 = '';
$image_path_3 = '';
$image_path_4 = '';
$image_path_5 = '';

if (isset($data['image']) && !empty($data['image'])) {
    $image_path = saveBase64Image($data['image'], 'car1');
}
if (isset($data['image_2']) && !empty($data['image_2'])) {
    $image_path_2 = saveBase64Image($data['image_2'], 'car2');
}
if (isset($data['image_3']) && !empty($data['image_3'])) {
    $image_path_3 = saveBase64Image($data['image_3'], 'car3');
}
if (isset($data['image_4']) && !empty($data['image_4'])) {
    $image_path_4 = saveBase64Image($data['image_4'], 'car4');
}
if (isset($data['image_5']) && !empty($data['image_5'])) {
    $image_path_5 = saveBase64Image($data['image_5'], 'car5');
}

try {
    // Check and add new columns if they don't exist
    $columns_to_check = [
        'car_color' => "VARCHAR(100)",
        'license_plate' => "VARCHAR(50)",
        'auction_price' => "VARCHAR(100)"
    ];

    foreach ($columns_to_check as $col => $def) {
        $check = $db->query("SHOW COLUMNS FROM auction_cars LIKE '$col'");
        if ($check->rowCount() == 0) {
            $db->exec("ALTER TABLE auction_cars ADD COLUMN $col $def");
        }
    }

    // Insert data
    $sql = "INSERT INTO auction_cars (
        title, brand, car_type, grade, mileage, transmission, price, queue_number,
        car_color, license_plate, auction_price,
        inspection_body, inspection_engine, inspection_suspension, inspection_interior, inspection_tires,
        image_path, image_path_2, image_path_3, image_path_4, image_path_5, no_starting_price, cc, car_year
    ) VALUES (
        :title, :brand, :car_type, :grade, :mileage, :transmission, :price, :queue_number,
        :car_color, :license_plate, :auction_price,
        :inspection_body, :inspection_engine, :inspection_suspension, :inspection_interior, :inspection_tires,
        :image_path, :image_path_2, :image_path_3, :image_path_4, :image_path_5, :no_starting_price, :cc, :car_year
    )";

    $stmt = $db->prepare($sql);
    $stmt->bindParam(':title', $title);
    $stmt->bindParam(':brand', $brand);
    $stmt->bindParam(':car_type', $car_type);
    $stmt->bindParam(':grade', $grade);
    $stmt->bindParam(':mileage', $mileage);
    $stmt->bindParam(':transmission', $transmission);
    $stmt->bindParam(':price', $price);
    $stmt->bindParam(':queue_number', $queue_number);
    $stmt->bindParam(':car_color', $car_color);
    $stmt->bindParam(':license_plate', $license_plate);
    $stmt->bindParam(':auction_price', $auction_price);
    $stmt->bindParam(':inspection_body', $inspection_body);
    $stmt->bindParam(':inspection_engine', $inspection_engine);
    $stmt->bindParam(':inspection_suspension', $inspection_suspension);
    $stmt->bindParam(':inspection_interior', $inspection_interior);
    $stmt->bindParam(':inspection_tires', $inspection_tires);
    $stmt->bindParam(':image_path', $image_path);
    $stmt->bindParam(':image_path_2', $image_path_2);
    $stmt->bindParam(':image_path_3', $image_path_3);
    $stmt->bindParam(':image_path_4', $image_path_4);
    $stmt->bindParam(':image_path_5', $image_path_5);
	$stmt->bindParam(':no_starting_price', $no_starting_price);
	$stmt->bindParam(':cc', $cc);
	$stmt->bindParam(':car_year', $car_year);

    if ($stmt->execute()) {
        $insertedId = $db->lastInsertId();

        // Build response data
        $responseData = ['id' => (int) $insertedId];

        // Include saved image paths if any
        $savedImages = [];
        if (!empty($image_path))
            $savedImages['image'] = $image_path;
        if (!empty($image_path_2))
            $savedImages['image_2'] = $image_path_2;
        if (!empty($image_path_3))
            $savedImages['image_3'] = $image_path_3;
        if (!empty($image_path_4))
            $savedImages['image_4'] = $image_path_4;
        if (!empty($image_path_5))
            $savedImages['image_5'] = $image_path_5;

        if (!empty($savedImages)) {
            $responseData['images'] = $savedImages;
        }

        sendResponse(true, 'เพิ่มข้อมูลรถประมูลสำเร็จ', $responseData, 201);
    } else {
        sendResponse(false, 'เกิดข้อผิดพลาดในการบันทึกข้อมูล', null, 500);
    }

} catch (PDOException $e) {
    sendResponse(false, 'Database Error: ' . $e->getMessage(), null, 500);
}
