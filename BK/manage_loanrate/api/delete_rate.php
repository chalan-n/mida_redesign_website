<?php
/**
 * API: ลบข้อมูลอัตราเบี้ยประกัน
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, DELETE');
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

// รับข้อมูล
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    $input = $_POST;
}

// ต้องมี id
if (!isset($input['id']) || !$input['id']) {
    echo json_encode(['success' => false, 'error' => 'Missing id parameter']);
    exit;
}

$parts = explode('|', $input['id']);

if (count($parts) < 5) {
    echo json_encode(['success' => false, 'error' => 'Invalid id format']);
    exit;
}

try {
    $sql = "DELETE FROM loanprotectrate 
            WHERE Rate_date = :rate_date 
            AND CmpInsuranceID = :company 
            AND SexID = :sex 
            AND age_from = :age_from 
            AND Term_year = :term_year";

    $stmt = $conn->prepare($sql);
    $result = $stmt->execute([
        'rate_date' => $parts[0],
        'company' => $parts[1],
        'sex' => $parts[2],
        'age_from' => $parts[3],
        'term_year' => $parts[4]
    ]);

    if ($result && $stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Data deleted successfully',
            'affected_rows' => $stmt->rowCount()
        ]);
    } elseif ($stmt->rowCount() === 0) {
        echo json_encode(['success' => false, 'error' => 'No data found to delete']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to delete data']);
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
