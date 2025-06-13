<?php
require_once 'config.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$user = getUser();
$resultId = $_GET['id'] ?? 0;

if ($resultId <= 0) {
    header('Location: index.php');
    exit();
}

$sql = "
    SELECT results.id_results, results.time, results.playing_date, users.username, configurations.width, configurations.height, configurations.mine_count
    FROM results
    INNER JOIN users ON results.id_users = users.id_users
    LEFT JOIN configurations ON results.id_configurations = configurations.id_configurations
    WHERE results.id_results = $resultId
";
$result = mysqli_query($connection, $sql);
$gameResult = mysqli_fetch_assoc($result);

if (!$gameResult) {
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['comment'])) {
    $comment = $_POST['comment'];
    if (strlen($comment) > 0) {
        $userId = $_SESSION['user_id'];
        $sql = "INSERT INTO comments (content, post_time, id_users, id_results) VALUES ('$comment', NOW(), $userId, $resultId)";
        mysqli_query($connection, $sql);
        $success = "Comment posted!";
    }
}

$sql = "
    SELECT comments.content, comments.post_time, users.username
    FROM comments
    INNER JOIN users ON comments.id_users = users.id_users
    WHERE comments.id_results = $resultId
    ORDER BY comments.post_time DESC
";
$result = mysqli_query($connection, $sql);
$comments = [];
while ($row = mysqli_fetch_assoc($result)) {
    $comments[] = $row;
}

$totalComments = count($comments);
$timeFormatted = number_format($gameResult['time'], 2);
$configText = '';
if ($gameResult['width'] && $gameResult['height'] && $gameResult['mine_count']) {
    $configText = " ({$gameResult['width']}x{$gameResult['height']}, {$gameResult['mine_count']} mines)";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Minolovec Comments</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>TIME: <?php echo $timeFormatted; ?>s<?php echo $configText; ?></h1>
    <p>BY: <?php echo $gameResult['username']; ?></p>
    <p>PLAYED: <?php echo date('M j, Y \a\t H:i', strtotime($gameResult['playing_date'])); ?></p>
    <div class="comment-box">
        <button onclick="location.href='index.php'">BACK TO LEADERBOARD</button>
        <?php if (isset($success)) { ?>
            <div class="success"><?php echo $success; ?></div>
        <?php } ?>
        <div class="comments-section">
            <h3>Comments (<?php echo $totalComments; ?>)</h3>
            <?php if (empty($comments)) { ?>
                <p>No comments yet. Be the first to comment!</p>
            <?php } else { ?>
                <?php foreach ($comments as $comment) { ?>
                    <div class="comment">
                        <p><?php echo $comment['content']; ?></p>
                        <small class="comment-meta">- <?php echo $comment['username']; ?>
                            (<?php echo date('M j, Y \a\t H:i', strtotime($comment['post_time'])); ?>)</small>
                    </div>
                <?php } ?>
            <?php } ?>
        </div>
        <div class="comment-form">
            <h3>Leave a Comment</h3>
            <form method="POST">
                <textarea name="comment" placeholder="WRITE YOUR COMMENT HERE..." required rows="4"></textarea><br>
                <button type="submit">SUBMIT COMMENT</button>
            </form>
        </div>
    </div>
        <?php include 'footer.php'; ?>
</body>
</html>