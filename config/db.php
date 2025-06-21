<?php
    // Database connection
$mysqli = new mysqli('localhost', 'root', '1234', 'Hopebehindebt');

// Check connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}
?>