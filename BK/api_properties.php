<?php
/**
 * API สำหรับ AJAX Pagination - ทรัพย์สินรอการขาย
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
$where_clauses = ["is_active = 1"];
$params = [];

// Filter by Type
if (isset($_GET['type']) && !empty($_GET['type'])) {
    $types = is_array($_GET['type']) ? $_GET['type'] : explode(',', $_GET['type']);
    $type_placeholders = [];
    foreach ($types as $key => $type) {
        $placeholder = ":type_" . $key;
        $type_placeholders[] = $placeholder;
        $params[$placeholder] = $type;
    }
    if (!empty($type_placeholders)) {
        $where_clauses[] = "type IN (" . implode(', ', $type_placeholders) . ")";
    }
}

// Filter by Location
if (isset($_GET['location']) && !empty($_GET['location'])) {
    $where_clauses[] = "location LIKE :location";
    $params[':location'] = "%" . $_GET['location'] . "%";
}

$where_sql = implode(' AND ', $where_clauses);

try {
    // Count total properties
    $count_sql = "SELECT COUNT(*) FROM properties WHERE $where_sql";
    $stmt_count = $db->prepare($count_sql);
    foreach ($params as $key => $value) {
        $stmt_count->bindValue($key, $value);
    }
    $stmt_count->execute();
    $total_rows = $stmt_count->fetchColumn();
    $total_pages = ceil($total_rows / $limit);

    // Fetch properties for current page
    $sql = "SELECT * FROM properties WHERE $where_sql ORDER BY id DESC LIMIT :start, :limit";
    $stmt = $db->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':start', $start, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    $properties = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'data' => [
            'properties' => $properties,
            'total_rows' => (int) $total_rows,
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
