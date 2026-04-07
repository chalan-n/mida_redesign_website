<?php
/**
 * API: บันทึกข้อมูลอัตราเบี้ยประกัน (Insert/Update)
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once '../config/database.php';

$conn = getConnection();

if (!$conn) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit;
}

// รับข้อมูล JSON
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    $input = $_POST;
}

// Validate required fields
$required = ['rate_date', 'company', 'sex', 'age_from', 'age_to', 'term_year', 'rate'];
foreach ($required as $field) {
    if (!isset($input[$field]) || $input[$field] === '') {
        echo json_encode(['success' => false, 'error' => "Missing required field: $field"]);
        exit;
    }
}

$rateDate = str_replace('-', '', $input['rate_date']);
$company = $input['company'];
$sex = $input['sex'];
$ageFrom = intval($input['age_from']);
$ageTo = intval($input['age_to']);
$termYear = intval($input['term_year']);
$termMonth = intval($input['term_month'] ?? 0);
$rate = floatval($input['rate']);
$status = $input['status'] ?? 'A';
$perComm = floatval($input['per_comm'] ?? 0);

// ถ้ามี original_id แสดงว่าเป็นการแก้ไข
$isUpdate = isset($input['original_id']) && $input['original_id'];

try {
    if ($isUpdate) {
        // ลบข้อมูลเก่า
        $origParts = explode('|', $input['original_id']);
        if (count($origParts) >= 5) {
            $delSql = "DELETE FROM loanprotectrate 
                       WHERE Rate_date = :rate_date 
                       AND CmpInsuranceID = :company 
                       AND SexID = :sex 
                       AND age_from = :age_from 
                       AND Term_year = :term_year";
            $delStmt = $conn->prepare($delSql);
            $delStmt->execute([
                'rate_date' => $origParts[0],
                'company' => $origParts[1],
                'sex' => $origParts[2],
                'age_from' => $origParts[3],
                'term_year' => $origParts[4]
            ]);
        }
    }

    // เพิ่มข้อมูลใหม่
    $sql = "INSERT INTO loanprotectrate 
            (Rate_date, CmpInsuranceID, SexID, age_from, age_to, Term_year, Term_month, Rate, Status, PerComm) 
            VALUES (:rate_date, :company, :sex, :age_from, :age_to, :term_year, :term_month, :rate, :status, :per_comm)";

    $stmt = $conn->prepare($sql);
    $result = $stmt->execute([
        'rate_date' => $rateDate,
        'company' => $company,
        'sex' => $sex,
        'age_from' => $ageFrom,
        'age_to' => $ageTo,
        'term_year' => $termYear,
        'term_month' => $termMonth,
        'rate' => $rate,
        'status' => $status,
        'per_comm' => $perComm
    ]);

    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => $isUpdate ? 'Data updated successfully' : 'Data inserted successfully'
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to save data']);
    }

} catch (PDOException $e) {
    $errorMsg = $e->getMessage();
    if ($e->getCode() == 23000) {
        $errorMsg = 'Duplicate entry: Data already exists';
    }
    echo json_encode(['success' => false, 'error' => $errorMsg]);
}
