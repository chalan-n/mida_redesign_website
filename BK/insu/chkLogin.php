<?php
session_start();
include("includes/config.php");

$arrCode = array(13, 7, 11, 5, 12, 10, 14, 6, 5, 13, 10, 7, 12, 6, 13, 11, 10, 14, 7, 2);

function deCrypPwd($Str, $arrCode)
{
	$c = "";
	for ($i = 0; $i < strlen($Str); $i++) {
		$c .= chr((ord($Str[$i]) + $arrCode[$i]));
	}
	return $c;
}

function enCrypPwd($Str, $arrCode)
{
	$c = "";
	for ($i = 0; $i < strlen($Str); $i++) {
		$c .= chr((ord($Str[$i]) - $arrCode[$i]));
	}
	return $c;
}

$username = isset($_POST['username']) ? strtolower($_POST['username']) : '';
$passwd = isset($_POST['passwd']) ? strtolower($_POST['passwd']) : '';

$arr_username = array('mida00', 'mida01', 'mida02', 'mida03', 'mida04', 'mida05', 'mida06', 'mida07', 'mida08', 'mida09', 'mida10', 'mida11', 'mida12', 'mida13', 'mida14', 'mida15', 'mida16', 'mida17', 'mida18');

// Check database connection first
if (!$objConnect) {
	echo "db_error#ไม่สามารถเชื่อมต่อฐานข้อมูลได้";
	exit;
}

// Login check
if (in_array($username, $arr_username)) {
	if ($passwd == "midaloan") {
		$_SESSION['sess_login'] = "insurance";
		$_SESSION['sess_CmpBranch'] = $username;
		echo "pass#" . $username;
	} else {
		echo "wrong#ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง";
	}
} else {
	echo "wrong#ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง";
}

if ($objConnect) {
	sqlClose($objConnect);
}
?>