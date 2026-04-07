<?php
define("DB_TYPE", "mysql"); //  'mssql' , 'mysql'

//define("DB_SERVER", "146.88.56.198");
//define("DB_USERNAME", "mida-leasing_insu");
//define("DB_PASSWORD", "mida1856");
define("DB_SERVER", getenv('DB_HOST') ? getenv('DB_HOST') : "localhost");
define("DB_PORT", (int) (getenv('DB_PORT') ? getenv('DB_PORT') : 3307));
define("DB_USERNAME", getenv('DB_USER') ? getenv('DB_USER') : "root");
$dbPassword = getenv('DB_PASSWORD');
define("DB_PASSWORD", $dbPassword !== false ? $dbPassword : "");

define("DB_DATABASE_NAME", getenv('DB_NAME') ? getenv('DB_NAME') : "db_mida_leasing");

$objConnect = false;

// Use mysqli for PHP 7+
$objConnect = @mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_DATABASE_NAME, DB_PORT);
if ($objConnect) {
	if (!mysqli_set_charset($objConnect, "utf8mb4")) {
		error_log("Failed to set mysqli charset: " . mysqli_error($objConnect));
	}
} else {
	error_log("Database connection failed: " . mysqli_connect_error());
}


include("function.php");
