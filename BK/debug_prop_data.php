<?php
require_once 'admin/config/db.php';
$database = new Database();
$db = $database->getConnection();

$stmt = $db->query("SELECT id, title, type, location, price, created_at FROM properties LIMIT 5");
$props = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<pre>";
print_r($props);
echo "</pre>";

$stmt_types = $db->query("SELECT DISTINCT type FROM properties");
print_r($stmt_types->fetchAll(PDO::FETCH_COLUMN));

$stmt_locs = $db->query("SELECT DISTINCT location FROM properties");
print_r($stmt_locs->fetchAll(PDO::FETCH_COLUMN));
?>