<?php require_once 'config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minolovec</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="header">
        <?php if (isLoggedIn()) { ?>
            <a href="logout.php" class="button-link">LOG OUT</a>
            <?php if (isAdmin()) { ?>
                <a href="admin.php" class="button-link">ADMIN PANEL</a>
            <?php } ?>
        <?php } else { ?>
            <a href="login.php" class="button-link">LOG IN</a>
            <a href="register.php" class="button-link">REGISTER</a>
        <?php } ?>
    </div>

    <h1>MINOLOVEC</h1>

    <div class="container">
        <div class="box">
            <h3><b>LEADERBOARD</b></h3>
            <?php 
            $sql = "SELECT r.id_results, r.time, r.playing_date, u.username, c.width, c.height, c.mine_count,
                    (SELECT COUNT(*) FROM comments WHERE id_results = r.id_results) as comment_count
                    FROM results r 
                    JOIN users u ON r.id_users = u.id_users 
                    LEFT JOIN configurations c ON r.id_configurations = c.id_configurations
                    ORDER BY r.time ASC 
                    LIMIT 10";

            $result = mysqli_query($connection, $sql);

            if (mysqli_num_rows($result) == 0) {
                echo "<p>No games played yet.</p>";
            } else {
                $position = 1;
                while ($game = mysqli_fetch_row($result)) {
                    $time = number_format($game[1], 2) . 's';
                    $config = '';
                    if ($game[4] && $game[5] && $game[6]) {
                        $config = " ({$game[4]}x{$game[5]}, {$game[6]} mines)";
                    }

                    if (isLoggedIn()) {
                        echo "<a href='comments.php?id={$game[0]}' class='button-link' style='font-size: 12px; padding: 4px 8px;'>COMMENT</a> ";
                    } else {
                        echo "<span style='color: #7f8c8d;'>COMMENT</span> ";
                    }
                    echo "<strong>{$position}.</strong> {$game[3]} - <strong>{$time}</strong>{$config}<br><br>";
                    $position++;
                }
            }
            
            if (!isLoggedIn()) {
                echo "<p style='color: #7f8c8d; font-style: italic;'>LOG IN TO SEE COMMENTS</p>";
            }
            ?>
        </div>
                
        <div class="center-box">
            <?php if (isLoggedIn()) { ?>
                <br>
                <br>
                <br>
                <br>
                <br>
                <br>
                <br>
                <br>
                <br>
                <br>
                <br>
                <br>
                <a href="game.php" class="button-link" style="font-size: 18px; padding: 20px 40px;">CLICK TO PLAY</a>
            <?php } else { ?>
                <br>
                <br>
                <br>
                <br>
                <br>
                <br>
                <br>
                <br>
                <br>
                <br>
                <br>
                <br>
                <p>LOG IN TO PLAY</p>
            <?php } ?>
        </div>

        <div class="box">
            <h3><b>STATISTICS</b></h3>
            <?php if (isLoggedIn()) { 
                $userId = $_SESSION['user_id'];
                $sql = "SELECT * FROM statistics WHERE id_users = $userId";
                $result = mysqli_query($connection, $sql);
                $stats = mysqli_fetch_assoc($result);
                
                if ($stats) {
                    $winRate = $stats['game_count'] > 0 ? round(($stats['win_count'] / $stats['game_count']) * 100, 1) : 0;
                    $losses = $stats['game_count'] - $stats['win_count'];
                    echo "<p><strong>WIN RATE:</strong> {$winRate}%</p>";
                    echo "<p><strong>WINS:</strong> {$stats['win_count']}</p>";
                    echo "<p><strong>LOSSES:</strong> {$losses}</p>";
                    echo "<p><strong>GAMES PLAYED:</strong> {$stats['game_count']}</p>";
                    echo "<p><strong>BEST TIME:</strong> " . number_format($stats['best_time'], 2) . "s</p>";
                    echo "<p><strong>AVERAGE TIME:</strong> " . number_format($stats['average_time'], 2) . "s</p>";
                } else {
                    echo "<p><strong>WIN RATE:</strong> N/A</p>";
                    echo "<p><strong>WINS:</strong> N/A</p>";
                    echo "<p><strong>LOSSES:</strong> N/A</p>";
                    echo "<p><strong>GAMES PLAYED:</strong> N/A</p>";
                    echo "<p><strong>BEST TIME:</strong> N/A</p>";
                    echo "<p><strong>AVERAGE TIME:</strong> N/A</p>";
                }
            } else { ?>
                <p><strong>WIN RATE:</strong> N/A</p>
                <p><strong>WINS:</strong> N/A</p>
                <p><strong>LOSSES:</strong> N/A</p>
                <p><strong>GAMES PLAYED:</strong> N/A</p>
                <p><strong>BEST TIME:</strong> N/A</p>
                <p><strong>AVERAGE TIME:</strong> N/A</p>
                <p>LOG IN TO SEE STATISTICS</p>
            <?php } ?>
        </div>
    </div>
        <?php include 'footer.php'; ?>
</body>
</html>