<?php
require_once 'admin/config/db.php';
$database = new Database();
$db = $database->getConnection();

try {
    $sql = "ALTER TABLE careers ADD COLUMN benefits TEXT DEFAULT NULL AFTER description";
    $db->exec($sql);
    echo "Column 'benefits' added successfully.";
} catch (PDOException $e) {
    if ($e->getCode() == '42S21') { // Duplicate column name
        echo "Column 'benefits' already exists.";
    } else {
        echo "Error: " . $e->getMessage();
    }
}
?>