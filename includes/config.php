<?php
define("DB_TYPE", "mysql"); //  'mssql' , 'mysql'

/*define("DB_SERVER", "localhost");
define("DB_USERNAME", "mida-leasing_usr1");
define("DB_PASSWORD", "U2s@al34");*/
define("DB_SERVER", getenv('DB_HOST') ? getenv('DB_HOST') : "localhost");
define("DB_PORT", (int) (getenv('DB_PORT') ? getenv('DB_PORT') : 3307));
define("DB_USERNAME", getenv('DB_USER') ? getenv('DB_USER') : "root");
$dbPassword = getenv('DB_PASSWORD');
define("DB_PASSWORD", $dbPassword !== false ? $dbPassword : "");

define("DB_DATABASE_NAME", getenv('DB_NAME') ? getenv('DB_NAME') : "db_mida_leasing"); 
define("DB_TABLE_NAME", "redbook_II"); 

// Global connection variable for mysqli
$objConnect = null;

if(DB_TYPE == "mssql"){
	// MSSQL connection (deprecated in PHP 7+, use sqlsrv extension if needed)
	// $objConnect = mssql_connect(DB_SERVER,DB_USERNAME,DB_PASSWORD);
	// $conn = mssql_select_db(DB_DATABASE_NAME);
	die("MSSQL connection is not supported. Please use MySQL or update to sqlsrv extension.");
}else{
	// MySQL connection using mysqli (PHP 7/8 compatible)
	$objConnect = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_DATABASE_NAME, DB_PORT);
	
	if (!$objConnect) {
		error_log("Database connection failed: " . mysqli_connect_error());
		die("Database connection failed.");
	}
	
	// Set charset to UTF-8
	mysqli_set_charset($objConnect, "utf8");
}

$webname = "Mida Blue Book - II";
//$bluebook_year = "201906";
//$bluebook_year = "202001";
//$bluebook_year = "202004";
//$bluebook_year = "202010";
//$bluebook_year = "202201";
//$bluebook_year = "202206";
//$bluebook_year = "202212";
//$bluebook_year = "202307";
//$bluebook_year = "202310";
//$bluebook_year = "202312";
//$bluebook_year = "202405";
//$bluebook_year = "202410";
$bluebook_year = "202504";

$url_image = "https://media.roddonjai.com/Bluebook_photo_2";

include("function.php");
?>
