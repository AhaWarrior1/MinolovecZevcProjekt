<?php
require_once 'config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    http_response_code(401);
    echo "Unauthorized";
    exit();
}

// Check if this is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo "Method not allowed";
    exit();
}

// Get the submitted data
if (isset($_POST['time'])) {
    $time = floatval($_POST['time']);
} else {
    $time = 0;
}

if (isset($_POST['width'])) {
    $width = intval($_POST['width']);
} else {
    $width = 0;
}

if (isset($_POST['height'])) {
    $height = intval($_POST['height']);
} else {
    $height = 0;
}

if (isset($_POST['mine_count'])) {
    $mine_count = intval($_POST['mine_count']);
} else {
    $mine_count = 0;
}

if (isset($_POST['won'])) {
    $won = intval($_POST['won']);
} else {
    $won = 0;
}

if ($time <= 0 || $width <= 0 || $height <= 0 || $mine_count <= 0) {
    echo "Invalid game data";
    exit();
}

$user_id = $_SESSION['user_id'];

$config_sql = "SELECT id_configurations FROM configurations WHERE width = $width AND height = $height AND mine_count = $mine_count";
$config_result = mysqli_query($connection, $config_sql);
$config_id = null;

if (mysqli_num_rows($config_result) > 0) {
    $config_row = mysqli_fetch_assoc($config_result);
    $config_id = $config_row['id_configurations'];
} else {
    $insert_config_sql = "INSERT INTO configurations (width, height, mine_count) VALUES ($width, $height, $mine_count)";
    if (mysqli_query($connection, $insert_config_sql)) {
        $config_id = mysqli_insert_id($connection);
    } else {
        http_response_code(500);
        echo "Error creating configuration: " . mysqli_error($connection);
        exit();
    }
}

if ($won) {
    $result_sql = "INSERT INTO results (time, playing_date, id_users, id_configurations) VALUES ($time, NOW(), $user_id, $config_id)";
    if (!mysqli_query($connection, $result_sql)) {
        http_response_code(500);
        echo "Error submitting score: " . mysqli_error($connection);
        exit();
    }
}

updateUserStatistics($user_id, $time, $won, $connection);

if ($won) {
    echo "Score submitted successfully";
} else {
    echo "Game result recorded";
}

function updateUserStatistics($user_id, $time, $won, $connection) {
    $stats_check_sql = "SELECT id_statistics, win_count, game_count, best_time, average_time FROM statistics WHERE id_users = $user_id";
    $stats_result = mysqli_query($connection, $stats_check_sql);
    
    if (mysqli_num_rows($stats_result) > 0) {
        $stats = mysqli_fetch_assoc($stats_result);

        if ($won) {
            $new_win_count = $stats['win_count'] + 1;
        } else {
            $new_win_count = $stats['win_count'];
        }

        $new_game_count = $stats['game_count'] + 1;

        if ($won) {
            if ($stats['best_time'] == 0 || $time < $stats['best_time']) {
                $new_best_time = $time;
            } else {
                $new_best_time = $stats['best_time'];
            }

            if ($stats['win_count'] > 0) {
                $total_time = ($stats['average_time'] * $stats['win_count']) + $time;
                $new_average_time = $total_time / $new_win_count;
            } else {
                $new_average_time = $time;
            }
        } else {
            $new_best_time = $stats['best_time'];
            $new_average_time = $stats['average_time'];
        }

        $update_stats_sql = "UPDATE statistics SET
            win_count = $new_win_count,
            game_count = $new_game_count,
            best_time = $new_best_time,
            average_time = $new_average_time
            WHERE id_users = $user_id";

        mysqli_query($connection, $update_stats_sql);
    } else {
        if ($won) {
            $insert_stats_sql = "INSERT INTO statistics (win_count, game_count, best_time, average_time, id_users)
                VALUES (1, 1, $time, $time, $user_id)";
        } else {
            $insert_stats_sql = "INSERT INTO statistics (win_count, game_count, best_time, average_time, id_users)
                VALUES (0, 1, 0, 0, $user_id)";
        }
        mysqli_query($connection, $insert_stats_sql);
    }
}
?>
