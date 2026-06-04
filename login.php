<?php require_once 'config.php'; ?>
<?php
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $pass = $_POST['password'] ?? '';

    $stmt = $pdo->prepare('SELECT * FROM reserve_users WHERE username = ?');
    $stmt->execute([$username]);
    $userData = $stmt->fetch();

    if ($userData && password_verify($pass, $userData['password_hash'])) {
        $_SESSION['user_id'] = $userData['id'];
        $_SESSION['username'] = $userData['username'];
        header('Location: index.php');
        exit;
    } else {
        $message = '<p class="error">ログイン失敗。入力内容を確認してください。</p>';
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>ログイン</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header>
    <h1>予約サイト</h1>
</header>

<div class="container">
    <h2>ログイン</h2>
    <?= $message ?>

    <form method="post">
        <label>ユーザー名</label>
        <input type="text" name="username">

        <label>パスワード</label>
        <input type="password" name="password">

        <button type="submit">ログイン</button>
    </form>

    <a href="register.php">新規登録はこちら</a>
</div>
</body>
</html>