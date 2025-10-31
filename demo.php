<?php
require 'includes/db.php';

$v = $_GET['v'] ?? '';
$v = trim($v);
echo "<h3>Testing Variety: '$v'</h3>";

$stmt = $pdo->prepare("SELECT COUNT(*) FROM glass_powers WHERE variety_name=?");
$stmt->execute([$v]);
$count = $stmt->fetchColumn();
echo "<p>Total rows found: <strong>$count</strong></p>";

$stmt = $pdo->prepare("SELECT DISTINCT spherical, cylinder FROM glass_powers WHERE variety_name=? ORDER BY CAST(spherical AS DECIMAL(5,2))");
$stmt->execute([$v]);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<pre>";
print_r($data);
echo "</pre>";
