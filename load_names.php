<?php
require 'includes/db.php';

$type = $_GET['type'] ?? '';

if($type == 'customer'){
    $stmt = $pdo->query("SELECT id, name FROM customers ORDER BY name ASC");
} elseif($type == 'supplier'){
    $stmt = $pdo->query("SELECT id, name FROM suppliers ORDER BY name ASC");
} else {
    exit('<option value="">Select Name</option>');
}

echo '<option value="">Select Name</option>';
while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
    echo '<option value="'.$row['id'].'">'.htmlspecialchars($row['name']).'</option>';
}
