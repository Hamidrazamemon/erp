<?php
require 'includes/session.php';
require 'includes/db.php';

$purchase_id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT id,item_type,item_name,spherical,cylinder,addition,axis,quantity,rate FROM purchase_items WHERE purchase_id=?");
$stmt->execute([$purchase_id]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($items);
