<?php
/**
 * API สำหรับ AJAX Pagination - รายการรถประมูล
 */
header('Content-Type: application/json; charset=utf-8');

require_once 'admin/config/db.php';
$database = new Database();
$db = $database->getConnection();

// Parameters
$limit = 9;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
if ($page < 1)
    $page = 1;
$start = ($page - 1) * $limit;

// Build Filter Query
$where_clauses = ["1=1"];
$params = [];

if (isset($_GET['brands']) && !empty($_GET['brands'])) {
    $brands = is_array($_GET['brands']) ? $_GET['brands'] : explode(',', $_GET['brands']);
    $brand_placeholders = [];
    foreach ($brands as $key => $brand) {
        $placeholder = ":brand_" . $key;
        $brand_placeholders[] = $placeholder;
        $params[$placeholder] = $brand;
    }
    if (!empty($brand_placeholders)) {
        $where_clauses[] = "brand IN (" . implode(', ', $brand_placeholders) . ")";
    }
}

if (isset($_GET['types']) && !empty($_GET['types'])) {
    $types = is_array($_GET['types']) ? $_GET['types'] : explode(',', $_GET['types']);
    $type_placeholders = [];
    foreach ($types as $key => $type) {
        $placeholder = ":type_" . $key;
        $type_placeholders[] = $placeholder;
        $params[$placeholder] = $type;
    }
    if (!empty($type_placeholders)) {
        $where_clauses[] = "car_type IN (" . implode(', ', $type_placeholders) . ")";
    }
}

if (isset($_GET['grades']) && !empty($_GET['grades'])) {
    $grades = is_array($_GET['grades']) ? $_GET['grades'] : explode(',', $_GET['grades']);
    $grade_placeholders = [];
    foreach ($grades as $key => $grade) {
        $placeholder = ":grade_" . $key;
        $grade_placeholders[] = $placeholder;
        $params[$placeholder] = $grade;
    }
    if (!empty($grade_placeholders)) {
        $where_clauses[] = "grade IN (" . implode(', ', $grade_placeholders) . ")";
    }
}

// Filter by schedule_id
if (isset($_GET['schedule_id']) && !empty($_GET['schedule_id'])) {
    $where_clauses[] = "schedule_id = :schedule_id";
    $params[':schedule_id'] = (int) $_GET['schedule_id'];
}

$where_sql = implode(' AND ', $where_clauses);

try {
    // Count total cars
    $count_sql = "SELECT COUNT(*) FROM auction_cars WHERE $where_sql";
    $stmt_count = $db->prepare($count_sql);
    foreach ($params as $key => $value) {
        $stmt_count->bindValue($key, $value);
    }
    $stmt_count->execute();
    $total_cars = $stmt_count->fetchColumn();
    $total_pages = ceil($total_cars / $limit);

    // Fetch cars for current page - เรียงตามคันที่
    $sql = "SELECT * FROM auction_cars WHERE $where_sql ORDER BY CAST(queue_number AS UNSIGNED) ASC, id ASC LIMIT :start, :limit";
    $stmt = $db->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':start', $start, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    $cars = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'data' => [
            'cars' => $cars,
            'total_cars' => (int) $total_cars,
            'total_pages' => (int) $total_pages,
            'current_page' => (int) $page,
            'limit' => (int) $limit
        ]
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
