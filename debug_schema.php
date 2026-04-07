<?php
require_once 'admin/config/db.php';
$database = new Database();
$db = $database->getConnection();

echo "<h1>DESCRIBE auction_cars</h1>";
$stmt = $db->query("DESCRIBE auction_cars");
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<table border='1'><tr><th>Field</th><th>Type</th></tr>";
foreach ($columns as $col) {
    echo "<tr><td>" . htmlspecialchars($col['Field']) . "</td><td>" . htmlspecialchars($col['Type']) . "</td></tr>";
}
echo "</table>";

echo "<h2>Distinct Brands</h2>";
$stmt = $db->query("SELECT DISTINCT brand FROM auction_cars");
$brands = $stmt->fetchAll(PDO::FETCH_COLUMN);
print_r($brands);

echo "<h2>Distinct Car Types</h2>";
// Note: Determining column name for car type might be tricky, looking for 'type' or 'car_type' or similar in the describe output first would be better, but I'll guess 'car_type' or check the describe output.
// Actually, I'll just rely on the describe output first.
?>