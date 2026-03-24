<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$conn = mysqli_connect("localhost", "root", "", "fitness_db");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

if (!function_exists('checkLogin')) {
    function checkLogin() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: index.php");
            exit();
        }
    }
}
?>