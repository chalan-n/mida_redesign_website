<?php
define("DB_TYPE", "mysql"); //  'mssql' , 'mysql'

//define("DB_SERVER", "146.88.56.198");
//define("DB_USERNAME", "mida-leasing_insu");
//define("DB_PASSWORD", "mida1856");
define("DB_SERVER", "localhost");
define("DB_USERNAME", "zp12865_zp12865");
define("DB_PASSWORD", "sfcLF7SfUYeNgPRwWb7W");

define("DB_DATABASE_NAME", "zp12865_db_mida_leasing");

$objConnect = false;

// Use mysqli for PHP 7+
$objConnect = @mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_DATABASE_NAME);
if ($objConnect) {
	mysqli_set_charset($objConnect, "utf8");
}


include("function.php");