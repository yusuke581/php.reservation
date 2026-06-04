<?php require_once 'config.php'; require_login(); ?>
<?php
$stmt = $pdo->prepare('SELECT title, reserve_date, reserve_time, memo, created_at FROM reservations WHERE user_id = ? ORDER BY reserve_date, reserve_time');
$stmt->execute([$_SESSION['user_id']]);
$rows = $stmt->fetchAll();

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="reservations.csv"');

$out = fopen('php://output', 'w');

fwrite($out, "\xEF\xBB\xBF");

fputcsv($out, ['予約名', '予約日', '予約時間', 'メモ', '登録日時']);

foreach ($rows as $row) {
    fputcsv($out, $row);
}

fclose($out);
exit;
?>
