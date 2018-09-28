<?php
$servername = "db.db.1and1.com";
$username = "dbo";
$password = "myPassword";
$dbname = "mydbname";

$secret = "GoogleAuthSecret";

// Create connection
$conn = new mysqli($servername, $username, $password,$dbname);

// Check connection
if ($conn->connect_error) {
   die("Connection failed: " . $conn->connect_error);
}
//echo "Connected successfully";
?>
