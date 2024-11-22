<?php

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "kelenne_car_wash";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
