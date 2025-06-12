<?php
require_once 'config.php';

if (isLoggedIn()) {
    header('Location: game.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    if (!empty($username) && !empty($password)) {
        $result = mysqli_query($connection, "SELECT id_users, username, password, banned FROM users WHERE username = '$username'");
        if (mysqli_num_rows($result) > 0) {
            $user = mysqli_fetch_assoc($result);
            if ($user['banned']) {
                $error = "Your account has been banned";
            } elseif (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id_users'];
                $_SESSION['username'] = $user['username'];
                header('Location: index.php');
                exit();
            } else {
                $error = "Invalid username or password";
            }
        } else {
            $error = "Invalid username or password";
        }
    } else {
        $error = "Please fill in all fields";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Minolovec - Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>MINOLOVEC - LOGIN</h1>
    <div class="login-container">
        <?php if (isset($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div>
                <label>Username:</label><br>
                <input type="text" name="username" required>
            </div>
            <br>
            <div>
                <label>Password:</label><br>
                <input type="password" name="password" required>
            </div>
            <br>
            <button type="submit">LOGIN</button>
        </form>
        
        <br>
        <a href="register.php" class="button-link">CREATE ACCOUNT</a>
        <a href="index.php" class="button-link">BACK TO HOME</a>
    </div>
</body>
</html>