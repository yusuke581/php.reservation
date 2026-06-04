<?php require_once 'config.php'; require_login(); ?>
<?php
$message = '';

/* パーソナル可能日を登録 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['mode'] ?? '') === 'available') {
    $available_date = $_POST['available_date'] ?? '';
    $start_time = $_POST['start_time'] ?? '';
    $end_time = $_POST['end_time'] ?? '';
    $memo = trim($_POST['available_memo'] ?? '');

    if ($available_date !== '' && $start_time !== '' && $end_time !== '') {
        $stmt = $pdo->prepare('INSERT INTO trainer_available (user_id, available_date, start_time, end_time, memo) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([$_SESSION['user_id'], $available_date, $start_time, $end_time, $memo]);
        $message = '<p class="success">パーソナル可能日を登録しました。</p>';
    } else {
        $message = '<p class="error">可能日・開始時間・終了時間は必須です。</p>';
    }
}

/* 予約登録：可能日の中から選ぶ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['mode'] ?? '') === 'reserve') {
    $title = trim($_POST['title'] ?? '');
    $available_id = $_POST['available_id'] ?? '';
    $memo = trim($_POST['memo'] ?? '');
    $imagePath = null;

    if ($title !== '' && $available_id !== '') {
        // 選ばれた可能日を取得
        $stmt = $pdo->prepare('SELECT * FROM trainer_available WHERE id = ? AND user_id = ?');
        $stmt->execute([$available_id, $_SESSION['user_id']]);
        $available = $stmt->fetch();

        if ($available) {
            $date = $available['available_date'];
            $time = $available['start_time'];

            if (!empty($_FILES['image']['name'])) {
                $uploadDir = 'uploads/';

                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                $allowed = ['jpg', 'jpeg', 'png', 'gif'];

                if (in_array($ext, $allowed, true)) {
                    $fileName = uniqid('img_', true) . '.' . $ext;
                    $savePath = $uploadDir . $fileName;

                    if (move_uploaded_file($_FILES['image']['tmp_name'], $savePath)) {
                        $imagePath = $savePath;
                    }
                }
            }

            $stmt = $pdo->prepare('INSERT INTO reservations (user_id, title, reserve_date, reserve_time, memo, image_path) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->execute([$_SESSION['user_id'], $title, $date, $time, $memo, $imagePath]);

            $message = '<p class="success">選択した可能日の中から予約を登録しました。</p>';
        } else {
            $message = '<p class="error">選択された可能日が見つかりません。</p>';
        }
    } else {
        $message = '<p class="error">予約名と予約可能日を選択してください。</p>';
    }
}

/* 予約一覧 */
$stmt = $pdo->prepare('SELECT * FROM reservations WHERE user_id = ? ORDER BY reserve_date, reserve_time');
$stmt->execute([$_SESSION['user_id']]);
$reservations = $stmt->fetchAll();

/* パーソナル可能日一覧 */
$stmt = $pdo->prepare('SELECT * FROM trainer_available WHERE user_id = ? ORDER BY available_date, start_time');
$stmt->execute([$_SESSION['user_id']]);
$available_days = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>パーソナル予約サイト</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f8;
            margin: 0;
            color: #222;
        }

        header {
            background: #203864;
            color: white;
            padding: 18px;
            text-align: center;
        }

        .container {
            max-width: 900px;
            margin: 25px auto;
            background: white;
            padding: 24px;
            border-radius: 12px;
            box-shadow: 0 3px 12px rgba(0,0,0,.12);
        }

        input, textarea, button, select {
            width: 100%;
            box-sizing: border-box;
            padding: 10px;
            margin: 8px 0 16px;
            border: 1px solid #ccc;
            border-radius: 8px;
        }

        textarea {
            height: 100px;
        }

        button, .btn {
            background: #203864;
            color: white;
            border: none;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
            display: inline-block;
        }

        button:hover, .btn:hover {
            opacity: .85;
        }

        .card {
            border: 1px solid #ddd;
            padding: 14px;
            border-radius: 10px;
            margin-bottom: 14px;
            background: #fafafa;
        }

        .card img {
            max-width: 220px;
            display: block;
            margin-top: 8px;
            border-radius: 8px;
        }

        .nav {
            display: flex;
            gap: 10px;
            margin-bottom: 16px;
        }

        .small {
            color: #666;
            font-size: 13px;
        }

        .error {
            color: #c00;
            font-weight: bold;
        }

        .success {
            color: #087b25;
            font-weight: bold;
        }

        .section {
            border-top: 2px solid #eee;
            padding-top: 20px;
            margin-top: 25px;
        }
    </style>

    <script>
        function checkAvailableForm() {
            const date = document.getElementById('available_date').value;
            const start = document.getElementById('start_time').value;
            const end = document.getElementById('end_time').value;

            if (date === '' || start === '' || end === '') {
                alert('可能日・開始時間・終了時間を入力してください');
                return false;
            }

            return confirm('この日程をパーソナル可能日として登録しますか？');
        }

        function checkReserveForm() {
            const title = document.getElementById('title').value.trim();
            const available = document.getElementById('available_id').value;

            if (title === '' || available === '') {
                alert('予約名と予約可能日を選択してください');
                return false;
            }

            return confirm('選択した可能日の中から予約しますか？');
        }
    </script>
</head>
<body>
<header>
    <h1>パーソナル予約サイト</h1>
</header>

<div class="container">
    <div class="nav">
        <a class="btn" href="download_csv.php">CSVダウンロード</a>
        <a class="btn" href="logout.php">ログアウト</a>
    </div>

    <p>ログイン中：<?= h($_SESSION['username']) ?> さん</p>

    <?= $message ?>

    <div class="section">
        <h2>パーソナル可能日を登録</h2>

        <form method="post" onsubmit="return checkAvailableForm();">
            <input type="hidden" name="mode" value="available">

            <label>可能日</label>
            <input type="date" name="available_date" id="available_date">

            <label>開始時間</label>
            <input type="time" name="start_time" id="start_time">

            <label>終了時間</label>
            <input type="time" name="end_time" id="end_time">

            <label>メモ</label>
            <textarea name="available_memo" placeholder="例：初回体験OK、上半身トレーニング対応可能など"></textarea>

            <button type="submit">可能日を登録する</button>
        </form>
    </div>

    <div class="section">
        <h2>予約登録</h2>

        <form method="post" enctype="multipart/form-data" onsubmit="return checkReserveForm();">
            <input type="hidden" name="mode" value="reserve">

            <label>予約名</label>
            <input type="text" name="title" id="title" placeholder="例：山田太郎さん パーソナル予約">

            <label>予約可能日の中から選択</label>
            <select name="available_id" id="available_id">
                <option value="">選択してください</option>

                <?php foreach ($available_days as $a): ?>
                    <option value="<?= h($a['id']) ?>">
                        <?= h($a['available_date']) ?>
                        <?= h(substr($a['start_time'], 0, 5)) ?>
                        〜
                        <?= h(substr($a['end_time'], 0, 5)) ?>
                        <?php if (!empty($a['memo'])): ?>
                            ／<?= h($a['memo']) ?>
                        <?php endif; ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label>予約メモ</label>
            <textarea name="memo" placeholder="例：初回体験、肩トレ希望、食事相談あり"></textarea>

            <label>画像アップロード</label>
            <input type="file" name="image" accept="image/*">

            <button type="submit">予約する</button>
        </form>
    </div>

    <div class="section">
        <h2>パーソナル可能日一覧</h2>

        <?php if (count($available_days) === 0): ?>
            <p>まだ可能日が登録されていません。</p>
        <?php endif; ?>

        <?php foreach ($available_days as $a): ?>
            <div class="card">
                <h3><?= h($a['available_date']) ?></h3>
                <p>
                    <?= h(substr($a['start_time'], 0, 5)) ?>
                    〜
                    <?= h(substr($a['end_time'], 0, 5)) ?>
                </p>
                <p><?= nl2br(h($a['memo'])) ?></p>
                <p class="small">登録日時：<?= h($a['created_at']) ?></p>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="section">
        <h2>予約一覧</h2>

        <?php if (count($reservations) === 0): ?>
            <p>まだ予約はありません。</p>
        <?php endif; ?>

        <?php foreach ($reservations as $r): ?>
            <div class="card">
                <h3><?= h($r['title']) ?></h3>
                <p>
                    予約日時：
                    <?= h($r['reserve_date']) ?>
                    <?= h(substr($r['reserve_time'], 0, 5)) ?>
                </p>
                <p><?= nl2br(h($r['memo'])) ?></p>

                <?php if (!empty($r['image_path'])): ?>
                    <img src="<?= h($r['image_path']) ?>" alt="予約画像">
                <?php endif; ?>

                <p class="small">登録日時：<?= h($r['created_at']) ?></p>
            </div>
        <?php endforeach; ?>
    </div>
</div>
</body>
</html>