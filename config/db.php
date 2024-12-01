<?php
// db.php
$host = 'localhost';
$username = 'root';
$password = '';
$db_name = 'todo_db'; // Change this to your actual database name

$connected = mysqli_connect($host, $username, $password, $db_name);

if (!$connected) {
    die(json_encode(["message" => "Database connection failed: " . mysqli_connect_error()]));
}
?>
