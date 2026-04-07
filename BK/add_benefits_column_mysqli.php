<?php
$host = 'localhost';
$port = '3307';
$db_name = 'db_mida_leasing';
$username = 'root';
$password = '';

// Try connecting with mysqli
$conn = new mysqli($host, $username, $password, $db_name, $port);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "ALTER TABLE careers ADD COLUMN benefits TEXT DEFAULT NULL AFTER description";

if ($conn->query($sql) === TRUE) {
    echo "Column 'benefits' added successfully";
} else {
    // Check for duplicate column error (1060)
    if ($conn->errno == 1060) {
        echo "Column 'benefits' already exists";
    } else {
        echo "Error adding column: " . $conn->error;
    }
}

$conn->close();
?>