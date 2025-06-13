<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minolovec - Sources</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="header">
        <a href="index.php" class="button-link">BACK TO HOME</a>
        <?php if (isLoggedIn()) { ?>
            <a href="game.php" class="button-link">PLAY GAME</a>
        <?php } ?>
    </div>

    <h1>MINOLOVEC - SOURCES</h1>

    <div class="form-container">
        <h2>Resources Used in This Project</h2> 
        <div class="sources-list">
            <p><strong>MySQL learned in class
                        <p><strong>PHP Documentation:</strong> <a href="https://www.php.net/docs.php" target="_blank">php.net</a></p>
            <p><strong>JS w3schools:</strong> <a href="https://www.w3schools.com/js/" target="_blank">w3schools.com</a></p>
                        <p><strong>CSS w3schools:</strong> <a href="https://www.w3schools.com/css/" target="_blank">w3schools.com</a></p>
            <p><strong>Minesweeper Game Logic:</strong> Inspired by classic Minesweeper implementation</p>
        </div>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>