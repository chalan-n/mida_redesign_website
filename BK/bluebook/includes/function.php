<?php
/**
 * Database helper functions - PHP 8 compatible
 * Uses mysqli instead of deprecated mysql_* functions
 */

function sqlQuery($sql){
	global $objConnect;
	if(DB_TYPE == 'mssql'){
		// MSSQL not supported in this version
		return false;
	}else{
		return mysqli_query($objConnect, $sql);
	}
}

function sqlQueryUpdate($sql){
	global $objConnect;
	if(DB_TYPE == 'mssql'){
		// MSSQL not supported in this version
		return false;
	}else{
		return mysqli_query($objConnect, $sql);
	}
}

function sqlNumRows($sql){
	if(DB_TYPE == 'mssql'){
		// MSSQL not supported in this version
		return 0;
	}else{
		if($sql === false || $sql === null) {
			return 0;
		}
		return mysqli_num_rows($sql);
	}
}

function sqlFetch($sql){
	if(DB_TYPE == 'mssql'){
		// MSSQL not supported in this version
		return false;
	}else{
		if($sql === false || $sql === null) {
			return false;
		}
		return mysqli_fetch_array($sql);
	}
}

function sqlClose($objCon = null){
	global $objConnect;
	if(DB_TYPE == 'mssql'){
		// MSSQL not supported in this version
		return false;
	}else{
		$connection = $objCon ?? $objConnect;
		if($connection) {
			return mysqli_close($connection);
		}
		return false;
	}
}

/**
 * Escape string for SQL injection prevention
 * @param string $str String to escape
 * @return string Escaped string
 */
function sqlEscape($str){
	global $objConnect;
	if($objConnect) {
		return mysqli_real_escape_string($objConnect, $str);
	}
	return addslashes($str);
}
?>