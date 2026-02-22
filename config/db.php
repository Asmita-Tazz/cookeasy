<?php
$host     = "localhost";
$username = "root";
$password = "";
$dbname   = "cookeasy_db";

$conn = mysqli_connect($host, $username, $password, $dbname);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
// 1. Set PHP to Nepal Time
date_default_timezone_set('Asia/Kathmandu');

// 2. Set MySQL to Nepal Time (+05:45)
mysqli_query($conn, "SET time_zone = '+05:45'");

// DEFINING THE PROJECT ROOT URL
define('BASE_URL', 'http://localhost/cookeasy_db/'); 
?>