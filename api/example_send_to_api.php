<?php
/**
 * ตัวอย่าง PHP สำหรับส่งข้อมูลรถประมูลไป API
 * Example: Send Auction Car Data to API
 */

// ===== การตั้งค่า API =====
$apiUrl = 'http://localhost:8085/webmida_newdesign/api/api_auction_cars.php';
$apiKey = 'MIDA_API_2026_SECRET_KEY';

// ===== ข้อมูลรถที่ต้องการส่ง =====
$carData = [
    'title' => 'HONDA CIVIC 2021',
    'brand' => 'Honda',
    'car_type' => 'รถเก๋ง',
    'grade' => 'A',
    'mileage' => '35,000',
    'transmission' => 'Auto',
    'price' => '550,000.-',
    'queue_number' => '105',
    'car_color' => 'ขาว',
    'license_plate' => '2กก 5678',
    'auction_price' => '480,000.-',
    'inspection_body' => 'ปกติ',
    'inspection_engine' => 'สตาร์ทติดง่าย',
    'inspection_suspension' => 'ปกติ',
    'inspection_interior' => 'สะอาด',
    'inspection_tires' => 'ดี 4 เส้น'
];

// ===== ตัวอย่างการส่งพร้อมรูปภาพ (Base64) =====
// อ่านไฟล์รูปภาพแล้วแปลงเป็น Base64
$imagePath = 'path/to/your/image.jpg'; // เปลี่ยนเป็น path รูปจริง
if (file_exists($imagePath)) {
    $imageData = file_get_contents($imagePath);
    $base64Image = base64_encode($imageData);
    $mimeType = mime_content_type($imagePath);
    $carData['image'] = "data:{$mimeType};base64,{$base64Image}";
}

// ===== ฟังก์ชันส่งข้อมูลไป API =====
function sendToAuctionAPI($url, $apiKey, $data)
{
    $jsonData = json_encode($data, JSON_UNESCAPED_UNICODE);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json; charset=utf-8',
        'X-API-Key: ' . $apiKey
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    return [
        'success' => ($httpCode >= 200 && $httpCode < 300),
        'http_code' => $httpCode,
        'response' => json_decode($response, true),
        'error' => $error
    ];
}

// ===== ส่งข้อมูล =====
echo "กำลังส่งข้อมูลไป API...\n";
$result = sendToAuctionAPI($apiUrl, $apiKey, $carData);

// ===== แสดงผลลัพธ์ =====
if ($result['success']) {
    echo "✅ สำเร็จ!\n";
    echo "ID: " . $result['response']['data']['id'] . "\n";

    if (isset($result['response']['data']['images'])) {
        echo "รูปภาพที่บันทึก:\n";
        foreach ($result['response']['data']['images'] as $key => $path) {
            echo "  - {$key}: {$path}\n";
        }
    }
} else {
    echo "❌ ผิดพลาด!\n";
    echo "HTTP Code: " . $result['http_code'] . "\n";
    echo "Message: " . ($result['response']['message'] ?? $result['error']) . "\n";
}

// ===== ตัวอย่างการส่งหลายคันพร้อมกัน =====
/*
$cars = [
    ['title' => 'TOYOTA YARIS 2020', 'price' => '350,000.-', 'brand' => 'Toyota'],
    ['title' => 'MAZDA 2 2021', 'price' => '420,000.-', 'brand' => 'Mazda'],
    ['title' => 'NISSAN ALMERA 2022', 'price' => '380,000.-', 'brand' => 'Nissan'],
];

foreach ($cars as $index => $car) {
    echo "ส่งรถคันที่ " . ($index + 1) . "...\n";
    $result = sendToAuctionAPI($apiUrl, $apiKey, $car);
    
    if ($result['success']) {
        echo "  ✅ ID: " . $result['response']['data']['id'] . "\n";
    } else {
        echo "  ❌ Error: " . ($result['response']['message'] ?? 'Unknown') . "\n";
    }
    
    // หน่วงเวลาเล็กน้อยระหว่างแต่ละ request
    usleep(100000); // 0.1 วินาที
}
*/
