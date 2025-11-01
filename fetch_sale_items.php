<?php
require 'includes/session.php';
require 'includes/db.php';

$sale_id = (int)($_GET['sale_id'] ?? 0);
if(!$sale_id) exit(json_encode([]));

$stmt = $pdo->prepare("SELECT * FROM sale_items WHERE sale_id=?");
$stmt->execute([$sale_id]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($items);
?>
