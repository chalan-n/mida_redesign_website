<?php
require_once 'config/db.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    echo "<h1>Database Connection Info</h1>";
    // Using reflection or query to check connection attributes might divulge info, 
    // but mainly we want to see the table structure.
    
    echo "<h2>Describe Announcements</h2>";
    $stmt = $db->query("DESCRIBE announcements");
    $columns = $stmt->fetchAll();
    
    echo "<table border='1'><tr><th>Field</th><th>Type</th></tr>";
    foreach ($columns as $col) {
        echo "<tr><td>" . htmlspecialchars($col['Field']) . "</td><td>" . htmlspecialchars($col['Type']) . "</td></tr>";
    }
    echo "</table>";

    echo "<h2>First Row Data</h2>";
    $stmt = $db->query("SELECT * FROM announcements LIMIT 1");
    $row = $stmt->fetch();
    echo "<pre>";
    print_r($row);
    echo "</pre>";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
