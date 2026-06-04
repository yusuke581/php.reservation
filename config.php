<?php
// DB接続設定
$dsn = 'mysql:dbname=tb280034db;host=localhost;charset=utf8mb4';
$user = 'tb-280034';
$password = 'my passwordを入れる';

try {
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    exit('DB接続エラー：' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
}

session_start();

// HTML表示用の自作関数
function h($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

// ログイン確認用の自作関数
function require_login() {
    if (empty($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }
}

// テーブル作成
function create_tables($pdo) {

    // ユーザー登録用テーブル
    $pdo->exec("CREATE TABLE IF NOT EXISTS reserve_users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // 予約登録用テーブル
    $pdo->exec("CREATE TABLE IF NOT EXISTS reservations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        title VARCHAR(100) NOT NULL,
        reserve_date DATE NOT NULL,
        reserve_time TIME NOT NULL,
        memo TEXT,
        image_path VARCHAR(255),
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // パーソナルトレーナーの可能日登録用テーブル
    $pdo->exec("CREATE TABLE IF NOT EXISTS trainer_available (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        available_date DATE NOT NULL,
        start_time TIME NOT NULL,
        end_time TIME NOT NULL,
        memo TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}

// テーブルを作成
create_tables($pdo);
?>
