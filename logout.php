<?php
// PHP Logout Starting
session_start();
$_SESSION = array();
session_destroy();
header("Location: userlogin.php"); 
exit();
// PHP Logout Ending
?>