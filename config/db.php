<?php
    // Database connection
$db = new mysqli('localhost', 'root', '1234', 'Hopebehindebt');

// Check connection
if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}
?>