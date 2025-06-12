<?php
require_once 'config.php';

if (isLoggedIn()) {
    header('Location: game.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirmPassword = trim($_POST['confirm_password']);
    
    if (empty($username) || empty($email) || empty($password)) {
        $error = "All fields are required";
    } elseif ($password !== $confirmPassword) {
        $error = "Passwords do not match";
    } else {
        $result = mysqli_query($connection, "SELECT id_users FROM users WHERE username = '$username'");
        if (mysqli_num_rows($result) > 0) {
            $error = "Username already exists";
        } else {
            $result = mysqli_query($connection, "SELECT id_users FROM users WHERE email = '$email'");
            if (mysqli_num_rows($result) > 0) {
                $error = "Email already registered";
            } else {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                if (mysqli_query($connection, "INSERT INTO users (username, password, email, registration_date, banned, admin) VALUES ('$username', '$hashedPassword', '$email', NOW(), 0, 0)")) {
                    $success = "Account created! You can now login.";
                } else {
                    $error = "Registration failed. Please try again.";
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minolovec - Register</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>MINOLOVEC - REGISTER</h1>
    <div class="login-container">
        <?php if (isset($error)) { ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php } ?>
        
        <?php if (isset($success)) { ?>
            <div class="success"><?php echo htmlspecialchars($success); ?></div>
            <a href="login.php" class="button-link">LOGIN NOW</a>
        <?php } else { ?>
            <form method="POST">
                <div>
                    <label for="username">Username:</label><br>
                    <input type="text" id="username" name="username" required>
                </div>
                <br>
                <div>
                    <label for="email">Email:</label><br>
                    <input type="email" id="email" name="email" required>
                </div>
                <br>
                <div>
                    <label for="password">Password:</label><br>
                    <input type="password" id="password" name="password" required>
                </div>
                <br>
                <div>
                    <label for="confirm_password">Confirm Password:</label><br>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                <br>
                <button type="submit">REGISTER</button>
            </form>
        <?php } ?>
        
        <br>
        <a href="login.php" class="button-link">ALREADY HAVE ACCOUNT?</a>
        <a href="index.php" class="button-link">BACK TO HOME</a>
    </div>
</body>
</html>