<?php
require_once 'config.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

if (!isAdmin()) {
    header('Location: index.php');
    exit();
}

$message = '';
$messageType = '';
$currentUserAdminLevel = getUserAdminLevel($_SESSION['user_id']);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        
        switch ($action) {
            case 'ban_user':
                $userId = intval($_POST['user_id']);
                if ($userId > 0) {
                    $sql = "UPDATE users SET banned = 1 WHERE id_users = $userId AND admin = 0";
                    if (mysqli_query($connection, $sql)) {
                        $message = "User banned successfully";
                        $messageType = "success";
                    } else {
                        $message = "Error banning user";
                        $messageType = "error";
                    }
                }
                break;
                
            case 'unban_user':
                $userId = intval($_POST['user_id']);
                if ($userId > 0) {
                    $sql = "UPDATE users SET banned = 0 WHERE id_users = $userId";
                    if (mysqli_query($connection, $sql)) {
                        $message = "User unbanned successfully";
                        $messageType = "success";
                    } else {
                        $message = "Error unbanning user";
                        $messageType = "error";
                    }
                }
                break;
                
            case 'delete_comment':
                $commentId = intval($_POST['comment_id']);
                if ($commentId > 0) {
                    $sql = "DELETE FROM comments WHERE id_comments = $commentId";
                    if (mysqli_query($connection, $sql)) {
                        $message = "Comment deleted successfully";
                        $messageType = "success";
                    } else {
                        $message = "Error deleting comment";
                        $messageType = "error";
                    }
                }
                break;
                
            case 'delete_result':
                $resultId = intval($_POST['result_id']);
                if ($resultId > 0) {
                    //prvo zbriše komentarje
                    mysqli_query($connection, "DELETE FROM comments WHERE id_results = $resultId");
                    $sql = "DELETE FROM results WHERE id_results = $resultId";
                    if (mysqli_query($connection, $sql)) {
                        $message = "Result deleted successfully";
                        $messageType = "success";
                    } else {
                        $message = "Error deleting result";
                        $messageType = "error";
                    }
                }
                break;
                
            case 'make_admin':
                $userId = intval($_POST['user_id']);
                if ($userId > 0) {
                    $sql = "UPDATE users SET admin = 1 WHERE id_users = $userId";
                    if (mysqli_query($connection, $sql)) {
                        $message = "User promoted to admin successfully";
                        $messageType = "success";
                    } else {
                        $message = "Error promoting user";
                        $messageType = "error";
                    }
                }
                break;
                
            case 'remove_admin':
                if (isSuperAdmin()) {
                    $userId = intval($_POST['user_id']);
                    $targetUserAdminLevel = getUserAdminLevel($userId);
                    
                    if ($userId > 0 && $targetUserAdminLevel < 2 && $userId != $_SESSION['user_id']) {
                        $sql = "UPDATE users SET admin = 0 WHERE id_users = $userId";
                        if (mysqli_query($connection, $sql)) {
                            $message = "Admin status removed successfully";
                            $messageType = "success";
                        } else {
                            $message = "Error removing admin status";
                            $messageType = "error";
                        }
                    } else {
                        $message = "Cannot remove super admin status or demote yourself";
                        $messageType = "error";
                    }
                } else {
                    $message = "Only super admins can remove admin status";
                    $messageType = "error";
                }
                break;
        }
    }
}

// Get statistics
$totalUsers = mysqli_fetch_assoc(mysqli_query($connection, "SELECT COUNT(*) as count FROM users"))['count'];
$totalGames = mysqli_fetch_assoc(mysqli_query($connection, "SELECT COUNT(*) as count FROM results"))['count'];
$totalComments = mysqli_fetch_assoc(mysqli_query($connection, "SELECT COUNT(*) as count FROM comments"))['count'];
$bannedUsers = mysqli_fetch_assoc(mysqli_query($connection, "SELECT COUNT(*) as count FROM users WHERE banned = 1"))['count'];

// Get recent users
$recentUsersQuery = "SELECT id_users, username, email, registration_date, banned, admin FROM users ORDER BY registration_date DESC LIMIT 10";
$recentUsers = mysqli_query($connection, $recentUsersQuery);

// Get recent comments
$recentCommentsQuery = "
    SELECT c.id_comments, c.content, c.post_time, u.username, r.time as game_time
    FROM comments c
    JOIN users u ON c.id_users = u.id_users
    JOIN results r ON c.id_results = r.id_results
    ORDER BY c.post_time DESC
    LIMIT 10
";
$recentComments = mysqli_query($connection, $recentCommentsQuery);

// Get top results
$topResultsQuery = "
    SELECT r.id_results, r.time, r.playing_date, u.username, c.width, c.height, c.mine_count
    FROM results r
    JOIN users u ON r.id_users = u.id_users
    LEFT JOIN configurations c ON r.id_configurations = c.id_configurations
    ORDER BY r.time ASC
    LIMIT 10
";
$topResults = mysqli_query($connection, $topResultsQuery);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minolovec - Admin Panel</title>
    <link rel="stylesheet" href="style.css">
</head>
<style>
        .admin-container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
        }
        
        .admin-section {
            margin-bottom: 30px;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background-color: #f9f9f9;
        }
        
        .admin-section h2 {
            margin-top: 0;
            color: #333;
            border-bottom: 2px solid #007BFF;
            padding-bottom: 10px;
        }
        
        .stats-grid {
            display: grid;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .stat-card {
            background-color: #007BFF;
            color: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }
        
        .stat-number {
            font-size: 2em;
            font-weight: bold;
            display: block;
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        
        .data-table th,
        .data-table td {
            padding: 8px 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        .data-table th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        
        .data-table tr:hover {
            background-color: #f9f9f9;
        }
        
        .action-btn {
            padding: 4px 8px;
            margin: 2px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
        }
        
        .btn-danger {
            background-color: #dc3545;
            color: white;
        }
        
        .btn-warning {
            background-color: #ffc107;
            color: black;
        }
        
        .btn-success {
            background-color: #28a745;
            color: white;
        }
        
        .btn-info {
            background-color: #17a2b8;
            color: white;
        }
        
        .btn-purple {
            background-color: #6f42c1;
            color: white;
        }
        
        .status-banned {
            color: red;
            font-weight: bold;
        }
        
        .status-admin {
            color: green;
            font-weight: bold;
        }
        
        .status-super-admin {
            color: purple;
            font-weight: bold;
        }
        
        .comment-preview {
            max-width: 300px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        .admin-level-badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: bold;
            margin-left: 5px;
        }
        
        .super-admin-badge {
            background-color: #6f42c1;
            color: white;
        }
</style>
<body>
    <div class="header">
        <a href="logout.php" class="button-link">LOG OUT</a>
        <a href="index.php" class="button-link">BACK TO HOME</a>
        <a href="game.php" class="button-link">PLAY GAME</a>
        <?php if (isSuperAdmin()) { ?>
            <span class="admin-level-badge super-admin-badge">SUPER ADMIN</span>
        <?php } ?>
    </div>

    <h1>MINOLOVEC - ADMIN PANEL</h1>

    <div class="admin-container">
        <?php if ($message) { ?>
            <div class="<?php echo $messageType; ?>" style="margin-bottom: 20px;">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php } ?>

        <!-- Statistics Section -->
        <div class="admin-section">
            <h2>System Statistics</h2>
            <div class="stats-grid">
                <div class="stat-card">
                    <span class="stat-number"><?php echo $totalUsers; ?></span>
                    Total Users
                </div>
                <div class="stat-card">
                    <span class="stat-number"><?php echo $totalGames; ?></span>
                    Total Games
                </div>
                <div class="stat-card">
                    <span class="stat-number"><?php echo $totalComments; ?></span>
                    Total Comments
                </div>
                <div class="stat-card">
                    <span class="stat-number"><?php echo $bannedUsers; ?></span>
                    Banned Users
                </div>
            </div>
        </div>

        <!-- User Management Section -->
        <div class="admin-section">
            <h2>Recent Users</h2>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Registration Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($user = mysqli_fetch_assoc($recentUsers)) { ?>
                        <tr>
                            <td><?php echo $user['id_users']; ?></td>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo date('M j, Y', strtotime($user['registration_date'])); ?></td>
                            <td>
                                <?php if ($user['admin'] == 2) { ?>
                                    <span class="status-super-admin">SUPER ADMIN</span>
                                <?php } elseif ($user['admin'] == 1) { ?>
                                    <span class="status-admin">ADMIN</span>
                                <?php } elseif ($user['banned']) { ?>
                                    <span class="status-banned">BANNED</span>
                                <?php } else { ?>
                                    Active
                                <?php } ?>
                            </td>
                            <td>
                                <?php if ($user['id_users'] != $_SESSION['user_id']) { // Don't show actions for current user ?>
                                    <?php if ($user['admin'] == 0) { // Regular user ?>
                                        <?php if ($user['banned']) { ?>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="action" value="unban_user">
                                                <input type="hidden" name="user_id" value="<?php echo $user['id_users']; ?>">
                                                <button type="submit" class="action-btn btn-success">UNBAN</button>
                                            </form>
                                        <?php } else { ?>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="action" value="ban_user">
                                                <input type="hidden" name="user_id" value="<?php echo $user['id_users']; ?>">
                                                <button type="submit" class="action-btn btn-danger" onclick="return confirm('Are you sure you want to ban this user?')">BAN</button>
                                            </form>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="action" value="make_admin">
                                                <input type="hidden" name="user_id" value="<?php echo $user['id_users']; ?>">
                                                <button type="submit" class="action-btn btn-info" onclick="return confirm('Are you sure you want to make this user an admin?')">MAKE ADMIN</button>
                                            </form>
                                            <?php if (isSuperAdmin()) { ?>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="action" value="make_super_admin">
                                                    <input type="hidden" name="user_id" value="<?php echo $user['id_users']; ?>">
                                                </form>
                                            <?php } ?>
                                        <?php } ?>
                                    <?php } elseif ($user['admin'] == 1 && isSuperAdmin()) { ?>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="remove_admin">
                                            <input type="hidden" name="user_id" value="<?php echo $user['id_users']; ?>">
                                            <button type="submit" class="action-btn btn-warning" onclick="return confirm('Are you sure you want to remove admin status from this user?')">REMOVE ADMIN</button>
                                        </form>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="make_super_admin">
                                            <input type="hidden" name="user_id" value="<?php echo $user['id_users']; ?>">
                                        </form>
                                    <?php } elseif ($user['admin'] == 2) { ?>
                                        <em>Super Admin - No actions available</em>
                                    <?php } ?>
                                <?php } else { ?>
                                    <em>Current User</em>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>

        <!-- Comments Management Section -->
        <div class="admin-section">
            <h2>Recent Comments</h2>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Comment</th>
                        <th>Game Time</th>
                        <th>Posted</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($comment = mysqli_fetch_assoc($recentComments)) { ?>
                        <tr>
                            <td><?php echo $comment['id_comments']; ?></td>
                            <td><?php echo htmlspecialchars($comment['username']); ?></td>
                            <td class="comment-preview"><?php echo htmlspecialchars($comment['content']); ?></td>
                            <td><?php echo number_format($comment['game_time'], 2); ?>s</td>
                            <td><?php echo date('M j, Y H:i', strtotime($comment['post_time'])); ?></td>
                            <td>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="delete_comment">
                                    <input type="hidden" name="comment_id" value="<?php echo $comment['id_comments']; ?>">
                                    <button type="submit" class="action-btn btn-danger" onclick="return confirm('Are you sure you want to delete this comment?')">DELETE</button>
                                </form>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>

        <!-- Results Management Section -->
        <div class="admin-section">
            <h2>Top Results</h2>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Time</th>
                        <th>Configuration</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($result = mysqli_fetch_assoc($topResults)) { ?>
                        <tr>
                            <td><?php echo $result['id_results']; ?></td>
                            <td><?php echo htmlspecialchars($result['username']); ?></td>
                            <td><?php echo number_format($result['time'], 2); ?>s</td>
                            <td>
                                <?php if ($result['width'] && $result['height'] && $result['mine_count']) { ?>
                                    <?php echo $result['width']; ?>x<?php echo $result['height']; ?>, <?php echo $result['mine_count']; ?> mines
                                <?php } else { ?>
                                    N/A
                                <?php } ?>
                            </td>
                            <td><?php echo date('M j, Y', strtotime($result['playing_date'])); ?></td>
                            <td>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="delete_result">
                                    <input type="hidden" name="result_id" value="<?php echo $result['id_results']; ?>">
                                    <button type="submit" class="action-btn btn-danger" onclick="return confirm('Are you sure you want to delete this result and all associated comments?')">DELETE</button>
                                </form>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
        <?php include 'footer.php'; ?>
</body>
</html>