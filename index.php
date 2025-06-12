<?php require_once 'config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Minolovec</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<?php if (isLoggedIn()) { ?>
<a href="logout.php" class="button-link">LOG OUT</a>
<?php } else { ?>
<a href="login.php" class="button-link">LOG IN</a><br>
<a href="register.php" class="button-link">REGISTER</a>
<?php } ?>

<h1>MINOLOVEC</h1>

<div class="container">
<div class="box">
<p><strong>LEADERBOARD:</strong></p>
<p>
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
    echo "No games played yet.<br>";
} else {
    $position = 1;
    while ($game = mysqli_fetch_row($result)) {

        $time = number_format($game[1], 2) . 's';
        $config = '';
        if ($game[4] && $game[5] && $game[6]) {
            $config = " ({$game[4]}x{$game[5]}, {$game[6]} mines)";
        }

        if (isLoggedIn()) {
            echo "<a href='comments.php?id={$game[0]}'>COMMENT</a> ";
        } else {
            echo "COMMENT ";
        }
        echo "{$position}. {$game[3]} - {$time}{$config}<br>";
        $position++;
    }
}
?>
<br>
<?php if (!isLoggedIn()) { ?>
 LOG IN TO SEE COMMENTS
<?php } ?>
</p>
</div>

<div class="center-box">
<?php if (isLoggedIn()) { ?>
<br><br><br><a href="game.php" class="button-link">CLICK TO PLAY</a>
<?php } else { ?>
<br><br><br>LOG IN TO PLAY
<?php } ?>
</div>

<div class="box">
<p><b>STATISTICS:</b><br>
<?php if (isLoggedIn()) { 
    $userId = $_SESSION['user_id'];
    $sql = "SELECT * FROM statistics WHERE id_users = $userId";
    $result = mysqli_query($connection, $sql);
    $stats = mysqli_fetch_assoc($result);
    
    if ($stats) {
        $winRate = $stats['game_count'] > 0 ? round(($stats['win_count'] / $stats['game_count']) * 100, 1) : 0;
        $losses = $stats['game_count'] - $stats['win_count'];
        echo "WIN RATE: {$winRate}%<br>";
        echo "WINS: {$stats['win_count']}<br>";
        echo "LOSSES: {$losses}<br>";
        echo "GAMES PLAYED: {$stats['game_count']}<br>";
        echo "BEST TIME: " . number_format($stats['best_time'], 2) . "s<br>";
        echo "AVERAGE TIME: " . number_format($stats['average_time'], 2) . "s";
    } else {
        echo "WIN RATE: N/A<br>";
        echo "WINS: N/A<br>";
        echo "LOSSES: N/A<br>";
        echo "GAMES PLAYED: N/A<br>";
        echo "BEST TIME: N/A<br>";
        echo "AVERAGE TIME: N/A";
    }
} else { ?>
 WIN RATE: N/A<br>
 WINS: N/A<br>
 LOSSES: N/A<br>
 GAMES PLAYED: N/A<br>
 BEST TIME: N/A<br>
 AVERAGE TIME: N/A<br>
<br>
 LOG IN TO SEE STATISTICS
<?php } ?>
</p>
</div>
</div>

</body>
</html>