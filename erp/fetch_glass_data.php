<?php
require 'includes/db.php';

$v = $_GET['v'] ?? '';
$data = ['spherical'=>[], 'cylinder'=>[]];

if($v!=''){
    $stmt = $pdo->prepare("SELECT DISTINCT spherical, cylinder FROM glass_powers WHERE variety_name = ?");
    $stmt->execute([$v]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $data['spherical'] = array_values(array_unique(array_column($rows,'spherical')));
    $data['cylinder'] = array_values(array_unique(array_column($rows,'cylinder')));
}

header('Content-Type: application/json');
echo json_encode($data);
