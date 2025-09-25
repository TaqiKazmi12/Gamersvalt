<?php
// Database Connection Starting
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gamersvalt";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// Database Connection Ending
?>