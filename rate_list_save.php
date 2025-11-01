<?php
session_start();
require 'includes/db.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: index.php");
    exit;
}

if($_SERVER['REQUEST_METHOD']=='POST'){
    $supplier_id = (int)$_POST['supplier_id'];
    $variety = trim($_POST['variety_name'] ?? '');
    $rates = $_POST['rates'] ?? [];

    foreach($rates as $r){
        $sph = floatval($r['spherical']);
        $cyl = floatval($r['cylinder']);
        $rate = floatval($r['rate']);

        if($rate!==''){
            $stmt = $pdo->prepare("INSERT INTO supplier_rates (supplier_id,variety_name,spherical,cylinder,rate)
                VALUES (?,?,?,?,?)
                ON DUPLICATE KEY UPDATE rate=?");
            $stmt->execute([$supplier_id,$variety,$sph,$cyl,$rate,$rate]);
        }
    }
    header("Location: rate_list.php?supplier_id=$supplier_id");
    exit;
}
