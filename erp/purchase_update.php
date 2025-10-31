<?php
require 'includes/session.php';
require 'includes/db.php';

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $id = (int)$_POST['id'];
    $supplier_id = (int)$_POST['supplier_id'];
    $item_type = $_POST['item_type'];
    $item_name = $_POST['item_name'];
    $spherical = $_POST['spherical'];
    $cylinder = $_POST['cylinder'];
    $quantity = (float)$_POST['quantity'];
    $rate = (float)$_POST['rate'];
    $amount = $quantity * $rate;
    $paid_amount = (float)$_POST['paid_amount'];
    $remaining = $amount - $paid_amount;
    $note = trim($_POST['note']);

    $stmt = $pdo->prepare("UPDATE purchases SET supplier_id=?, item_type=?, item_name=?, spherical=?, cylinder=?, quantity=?, rate=?, total_amount=?, paid_amount=?, remaining_amount=?, note=? WHERE id=?");
    $stmt->execute([$supplier_id, $item_type, $item_name, $spherical, $cylinder, $quantity, $rate, $amount, $paid_amount, $remaining, $note, $id]);

    header("Location: purchase_view.php?msg=updated");
    exit;
}
?>
