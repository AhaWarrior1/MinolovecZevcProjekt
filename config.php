<?php
// config.php - Database configuration
$servername = "sql206.infinityfree.com";
$username = "if0_38997340";
$password = "VAPorFbY9ztjBH9";
$dbname = "if0_38997340_mine_sweeper";

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

function isAdmin() {
    global $connection;
    if (!isLoggedIn()) return false;
    
    $userId = $_SESSION['user_id'];
    $sql = "SELECT admin FROM users WHERE id_users = $userId";
    $result = mysqli_query($connection, $sql);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        return $user['admin'] >= 1;
    }
    return false;
}

function isSuperAdmin() {
    global $connection;
    if (!isLoggedIn()) return false;
    
    $userId = $_SESSION['user_id'];
    $sql = "SELECT admin FROM users WHERE id_users = $userId";
    $result = mysqli_query($connection, $sql);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        return $user['admin'] == 2;
    }
    return false;
}

function getUserAdminLevel($userId) {
    global $connection;
    $sql = "SELECT admin FROM users WHERE id_users = $userId";
    $result = mysqli_query($connection, $sql);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        return $user['admin'];
    }
    return 0;
}
?>