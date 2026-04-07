<?php
// Global connection variable
global $objConnect;

function sqlQuery($sql)
{
	global $objConnect;

	$result = mysqli_query($objConnect, $sql);
	// Return false if query failed
	if ($result === false) {
		error_log("SQL Error: " . mysqli_error($objConnect) . " | Query: " . $sql);
	}
	return $result;

}

function sqlPrepare($sql)
{
	global $objConnect;

	if (!$objConnect) {
		return false;
	}

	return mysqli_prepare($objConnect, $sql);
}

function sqlExecutePrepared($stmt, $types = '', $params = array())
{
	if (!$stmt) {
		return false;
	}

	if (!empty($types) && !empty($params)) {
		$bindParams = array($stmt, $types);
		foreach ($params as $key => $value) {
			$bindParams[] = &$params[$key];
		}
		call_user_func_array('mysqli_stmt_bind_param', $bindParams);
	}

	if (!mysqli_stmt_execute($stmt)) {
		error_log("Prepared SQL Error: " . mysqli_stmt_error($stmt));
		return false;
	}

	return mysqli_stmt_get_result($stmt);
}

function sqlFetchAllAssoc($sql, $types = '', $params = array())
{
	$stmt = sqlPrepare($sql);
	if (!$stmt) {
		return array();
	}

	$result = sqlExecutePrepared($stmt, $types, $params);
	$rows = array();

	if ($result) {
		while ($row = mysqli_fetch_assoc($result)) {
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
	if (!$row) {
		return $default;
	}

	foreach ($row as $value) {
		return $value;
	}

	return $default;
}

function sqlQueryUpdate($sql)
{
	global $objConnect;

	mysqli_query($objConnect, $sql);

}

function sqlNumRows($result)
{
	// Check if result is valid before calling mysqli_num_rows
	if ($result === false || $result === null) {
		return 0;
	}

	return @mysqli_num_rows($result);

}

function sqlFetch($result)
{
	// Check if result is valid before calling mysqli_fetch_array
	if ($result === false || $result === null) {
		return null;
	}

	return mysqli_fetch_array($result);

}

function sqlClose($objCon)
{
	if ($objCon) {
		mysqli_close($objCon);
	}
}

function sqlEscape($str)
{
	global $objConnect;
	if ($objConnect) {
		return mysqli_real_escape_string($objConnect, $str);
	}
	return addslashes($str);
}
