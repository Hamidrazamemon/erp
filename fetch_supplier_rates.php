<?php
require 'includes/session.php';
require 'includes/db.php';

$supplier_id = (int)($_GET['supplier_id'] ?? 0);
$variety = trim($_GET['variety'] ?? '');

if(!$supplier_id || !$variety) exit(json_encode([]));

$stmt = $pdo->prepare("SELECT spherical,cylinder,rate FROM supplier_rates WHERE supplier_id=? AND variety_name=? ORDER BY spherical,cylinder");
$stmt->execute([$supplier_id,$variety]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($rows);
