<?php require_once 'config.php'; ?>
<?php
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $pass = $_POST['password'] ?? '';

    if ($username === '' || $pass === '') {
        $message = '<p class="error">ユーザー名とパスワードを入力してください。</p>';
    } else {
        $hash = password_hash($pass, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('INSERT INTO reserve_users (username, password_hash) VALUES (?, ?)');

        if ($stmt->execute([$username, $hash])) {
            $message = '<p class="success">登録完了。ログインしてください。</p>';
        } else {
            $message = '<p class="error">そのユーザー名は既に使われています。</p>';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>ユーザー登録</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header>
    <h1>予約サイト</h1>
</header>

<div class="container">
    <h2>ユーザー登録</h2>
    <?= $message ?>

    <form method="post">
        <label>ユーザー名</label>
        <input type="text" name="username">

        <label>パスワード</label>
        <input type="password" name="password">

        <button type="submit">登録</button>
    </form>

    <a href="login.php">ログイン画面へ</a>
</div>
</body>
</html>