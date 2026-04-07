<?php
session_start();

$_SESSION['sess_login']="";
$_SESSION['sess_CmpBranch']="";

header("Location: login.php");
?>