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