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

function sqlPrepare($sql)
{
	global $objConnect;
	if(DB_TYPE == 'mssql'){
		return false;
	}
	if(!$objConnect){
		return false;
	}
	return mysqli_prepare($objConnect, $sql);
}

function sqlExecutePrepared($stmt, $types = '', $params = array())
{
	if(DB_TYPE == 'mssql' || !$stmt){
		return false;
	}

	if(!empty($types) && !empty($params)) {
		$bindParams = array($types);
		foreach ($params as $key => $value) {
			$bindParams[] = &$params[$key];
		}
		call_user_func_array('mysqli_stmt_bind_param', $bindParams);
	}

	if(!mysqli_stmt_execute($stmt)) {
		return false;
	}

	return mysqli_stmt_get_result($stmt);
}

function sqlFetchAllAssoc($sql, $types = '', $params = array())
{
	$stmt = sqlPrepare($sql);
	if(!$stmt) {
		return array();
	}

	$result = sqlExecutePrepared($stmt, $types, $params);
	$rows = array();

	if($result) {
		while($row = mysqli_fetch_assoc($result)) {
			$rows[] = $row;
		}
		mysqli_free_result($result);
	}

	mysqli_stmt_close($stmt);
	return $rows;
}

function sqlFetchOneAssoc($sql, $types = '', $params = array())
{
	$rows = sqlFetchAllAssoc($sql, $types, $params);
	return !empty($rows) ? $rows[0] : null;
}

function sqlFetchScalar($sql, $types = '', $params = array(), $default = null)
{
	$row = sqlFetchOneAssoc($sql, $types, $params);
	if(!$row) {
		return $default;
	}

	foreach($row as $value) {
		return $value;
	}

	return $default;
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
		$connection = ($objCon !== null) ? $objCon : $objConnect;
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
