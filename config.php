<?php
$servername = "";
$username = "";
$password = "";
$dbname = "";

$connection = mysqli_connect($servername, $username, $password, $dbname);

if (!$connection) {
    die("Connection failed: " . mysqli_connect_error());
}

session_start();

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getUser() {
    global $connection;
    if (!isLoggedIn()) return null;
    $sql = "SELECT username, email FROM users WHERE id_users = " . $_SESSION['user_id'];
    return mysqli_query($connection, $sql);
}
?>