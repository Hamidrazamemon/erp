<?php
require 'includes/session.php';
require 'includes/db.php';

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
