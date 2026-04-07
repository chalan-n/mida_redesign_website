<?php
/**
 * API: ดึงข้อมูลอัตราเบี้ยประกัน
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once '../config/database.php';

$conn = getConnection();

if (!$conn) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit;
}

$company = $_GET['company'] ?? '';
$rateDate = $_GET['rate_date'] ?? '';
$sex = $_GET['sex'] ?? '';
$status = $_GET['status'] ?? 'A';

$sql = "SELECT * FROM loanprotectrate WHERE 1=1";
$params = [];

if ($company) {
    $sql .= " AND CmpInsuranceID = :company";
    $params['company'] = $company;
}

if ($rateDate) {
    $sql .= " AND Rate_date = :rate_date";
    $params['rate_date'] = $rateDate;
}

if ($sex !== '') {
    $sql .= " AND SexID = :sex";
    $params['sex'] = $sex;
}

if ($status) {
    $sql .= " AND Status = :status";
    $params['status'] = $status;
}

$sql .= " ORDER BY Rate_date DESC, CmpInsuranceID, SexID, age_from, Term_year";

try {
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $rates = $stmt->fetchAll();

    echo json_encode([
        'success' => true,
        'count' => count($rates),
        'data' => $rates
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
