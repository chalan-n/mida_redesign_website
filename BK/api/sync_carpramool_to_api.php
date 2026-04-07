<?php
/**
 * ดึงข้อมูลจากตาราง PStock (MSSQL) แล้วส่งไป API
 * Sync data from PStock table to Auction Cars API
 * 
 * โครงสร้างข้อมูล:
 * CmpBranch, ContNo, PramoolSort, CarBandName, CarModelName, CarGear, 
 * CarYear, CarColor, CarCC, CarRegNo, CarProvName, RegNo2, Provname2, 
 * CarTaxExpDate, carPicture, carPicture1, carPicture2, carPicture3, carPicture4,
 * Pramool_Price, Pramool_FlgPrice, CommentLast, CommentPramool
 * 
 * Compatible with PHP 5.2+
 */

// ===== การตั้งค่า API =====
$apiUrl = 'http://localhost:8085/webmida_newdesign/api/api_auction_cars.php';
$apiKey = 'MIDA_API_2026_SECRET_KEY';

// ===== การเชื่อมต่อ MSSQL Database ต้นทาง =====
// ปรับค่าเหล่านี้ให้ตรงกับ database ต้นทาง
$mssqlServer = 'your_server_name';    // เช่น 192.168.1.100 หรือ localhost\\SQLEXPRESS
$mssqlUser = 'sa';
$mssqlPass = 'your_password';
$mssqlDb = 'your_database_name';

// เชื่อมต่อ MSSQL
$conn = mssql_connect($mssqlServer, $mssqlUser, $mssqlPass);
if (!$conn) {
    die("MSSQL connection failed!");
}
mssql_select_db($mssqlDb, $conn);

// ===== ฟังก์ชันส่งข้อมูลไป API =====
function sendToAuctionAPI($url, $apiKey, $data)
{
    // PHP 5.2 ไม่รองรับ JSON_UNESCAPED_UNICODE
    // ใช้ฟังก์ชัน custom สำหรับ encode ภาษาไทย
    $jsonData = json_encode_unicode($data);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json; charset=utf-8',
        'X-API-Key: ' . $apiKey
    ));

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    return array(
        'success' => ($httpCode >= 200 && $httpCode < 300),
        'http_code' => $httpCode,
        'response' => json_decode($response, true),
        'error' => $error
    );
}

// ===== ฟังก์ชัน json_encode รองรับภาษาไทย สำหรับ PHP 5.2 =====
function json_encode_unicode($data)
{
    // ใช้ json_encode ปกติ แล้วแปลง \uXXXX กลับเป็น UTF-8
    $json = json_encode($data);
    // แปลง Unicode escape sequences กลับเป็นตัวอักษร UTF-8
    $json = preg_replace_callback('/\\\\u([0-9a-fA-F]{4})/', 'unicode_decode_callback', $json);
    return $json;
}

function unicode_decode_callback($matches)
{
    $codepoint = hexdec($matches[1]);
    if ($codepoint < 0x80) {
        return chr($codepoint);
    } elseif ($codepoint < 0x800) {
        return chr(0xC0 | ($codepoint >> 6)) . chr(0x80 | ($codepoint & 0x3F));
    } else {
        return chr(0xE0 | ($codepoint >> 12)) . chr(0x80 | (($codepoint >> 6) & 0x3F)) . chr(0x80 | ($codepoint & 0x3F));
    }
}

// ===== ฟังก์ชันแปลงรูปภาพเป็น Base64 =====
function imageToBase64($imagePath)
{
    if (empty($imagePath) || !file_exists($imagePath)) {
        return '';
    }
    $imageData = file_get_contents($imagePath);
    $mimeType = getMimeType($imagePath);
    return "data:{$mimeType};base64," . base64_encode($imageData);
}

// ===== ฟังก์ชันหา MIME Type สำหรับ PHP 5.2 =====
function getMimeType($filename)
{
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    $mimeTypes = array(
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'bmp' => 'image/bmp',
        'webp' => 'image/webp'
    );
    return isset($mimeTypes[$ext]) ? $mimeTypes[$ext] : 'application/octet-stream';
}

// ===== ฟังก์ชัน helper สำหรับดึงค่า (แทน ??) =====
function getValue($value, $default = '')
{
    return isset($value) ? $value : $default;
}

// ===== ดึงข้อมูลจากตาราง PStock =====
$query = "SELECT				
            pt.CmpBranch,
            pt.ContNo,
            pt.PramoolSort,
            pt.CarBandName,
            pt.CarModelName,
            pt.CarGear,
            pt.CarYear,
            pt.CarColor,
            pt.CarCC,
            pt.CarRegNo,
            pt.CarProvName,
            pt.RegNo2,
            pt.Provname2,
            pt.CarTaxExpDate,
            ptcar.carPicture,
            ptcar.carPicture1,
            ptcar.carPicture2,
            ptcar.carPicture3,
            ptcar.carPicture4,
            pt.Pramool_Price,
            pt.Pramool_FlgPrice,						
            pt.CommentLast,
            pt.CommentPramool 						  
          FROM PStock pt
          LEFT JOIN PStockCar ptcar ON pt.ContNo = ptcar.ContNo
          ORDER BY pt.PramoolSort ASC";

$sql = mssql_query($query);
$totalRows = @mssql_num_rows($sql);

echo "===========================================\n";
echo "Sync PStock (MSSQL) to Auction Cars API\n";
echo "===========================================\n";
echo "พบข้อมูล: {$totalRows} รายการ\n\n";

$successCount = 0;
$failCount = 0;

if ($totalRows > 0) {
    $index = 0;
    while ($rss = mssql_fetch_array($sql)) {
        $index++;

        // ===== สร้างชื่อรุ่นรถ (รวม brand + model + year + cc) =====
        $carTitle = trim(getValue($rss['CarBandName']) . ' ' . getValue($rss['CarModelName']));
        if (!empty($rss['CarYear'])) {
            $carTitle .= ' ' . $rss['CarYear'];
        }
        if (!empty($rss['CarCC'])) {
            $carTitle .= ' ' . $rss['CarCC'] . ' cc';
        }

        // ===== สร้างทะเบียนรถ (รวม regno + provname) =====
        $licensePlate = trim(getValue($rss['CarRegNo']) . ' ' . getValue($rss['CarProvName']));

        // ===== Map ข้อมูลจาก PStock ไปยัง API fields =====
        $carData = array(
            'title' => $carTitle,                                   // ชื่อรุ่น (brand + model + year)
            'brand' => getValue($rss['CarBandName']),               // ยี่ห้อ
            'car_type' => '',                                       // ไม่มีใน PStock
            'grade' => '',                                          // ไม่มีใน PStock
            'mileage' => '',                                        // ไม่มีใน PStock
            'transmission' => getValue($rss['CarGear']),            // เกียร์
            'price' => getValue($rss['Pramool_Price']),             // ราคาประมูล
            'queue_number' => getValue($rss['PramoolSort']),        // ลำดับ
            'car_color' => getValue($rss['CarColor']),              // สีรถ
            'license_plate' => $licensePlate,                       // ทะเบียนรถ
            'auction_price' => getValue($rss['Pramool_FlgPrice']),  // ราคาประมูลปิด
            'inspection_body' => getValue($rss['CommentPramool']),  // หมายเหตุประมูล
            'inspection_engine' => getValue($rss['CommentLast']),   // หมายเหตุล่าสุด
            'inspection_suspension' => '',
            'inspection_interior' => '',
            'inspection_tires' => ''
        );

        // ===== ส่งรูปภาพ (ถ้ามี) =====
        // ปรับ path ให้ตรงกับที่เก็บรูปจริง
        $imageBasePath = '/path/to/images/'; // เปลี่ยนเป็น path จริง

        // carPicture = รูปหลัก
        if (!empty($rss['carPicture'])) {
            $imgPath = $imageBasePath . $rss['carPicture'];
            if (file_exists($imgPath)) {
                $carData['image'] = imageToBase64($imgPath);
            }
        }
        // carPicture1 = รูปที่ 2
        if (!empty($rss['carPicture1'])) {
            $imgPath = $imageBasePath . $rss['carPicture1'];
            if (file_exists($imgPath)) {
                $carData['image_2'] = imageToBase64($imgPath);
            }
        }
        // carPicture2 = รูปที่ 3
        if (!empty($rss['carPicture2'])) {
            $imgPath = $imageBasePath . $rss['carPicture2'];
            if (file_exists($imgPath)) {
                $carData['image_3'] = imageToBase64($imgPath);
            }
        }
        // carPicture3 = รูปที่ 4
        if (!empty($rss['carPicture3'])) {
            $imgPath = $imageBasePath . $rss['carPicture3'];
            if (file_exists($imgPath)) {
                $carData['image_4'] = imageToBase64($imgPath);
            }
        }
        // carPicture4 = รูปที่ 5
        if (!empty($rss['carPicture4'])) {
            $imgPath = $imageBasePath . $rss['carPicture4'];
            if (file_exists($imgPath)) {
                $carData['image_5'] = imageToBase64($imgPath);
            }
        }

        // ===== ส่งข้อมูลไป API =====
        echo "[{$index}/{$totalRows}] ส่ง: {$carData['title']}... ";

        $result = sendToAuctionAPI($apiUrl, $apiKey, $carData);

        if ($result['success']) {
            $newId = isset($result['response']['data']['id']) ? $result['response']['data']['id'] : 'N/A';
            echo "OK ID: {$newId}\n";
            $successCount++;
        } else {
            $errorMsg = isset($result['response']['message']) ? $result['response']['message'] : (isset($result['error']) ? $result['error'] : 'Unknown error');
            echo "FAIL {$errorMsg}\n";
            $failCount++;
        }

        // หน่วงเวลาเล็กน้อยเพื่อไม่ให้ server overload
        usleep(100000); // 0.1 วินาที
    }
}

mssql_close($conn);

echo "\n===========================================\n";
echo "สรุปผล:\n";
echo "  OK สำเร็จ: {$successCount} รายการ\n";
echo "  FAIL ผิดพลาด: {$failCount} รายการ\n";
echo "===========================================\n";
