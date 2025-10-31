<?php
require 'includes/db.php';
$v = trim($_GET['v'] ?? '');
header('Content-Type: application/json');

if ($v === '') { echo json_encode([]); exit; }

$stmt = $pdo->prepare("SELECT DISTINCT spherical, cylinder, addition, axis FROM glass_powers WHERE variety_name=?");
$stmt->execute([$v]);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

$response = [
  'spherical' => [],
  'cylinder'  => [],
  'addition'  => [],
  'axis'      => []
];

foreach ($data as $row) {
  if ($row['spherical'] !== null) $response['spherical'][] = $row['spherical'];
  if ($row['cylinder']  !== null) $response['cylinder'][]  = $row['cylinder'];
  if ($row['addition']  !== null) $response['addition'][]  = $row['addition'];
  if ($row['axis']      !== null) $response['axis'][]      = $row['axis'];
}

echo json_encode($response);
