<?php
session_start();
require 'includes/db.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: index.php");
    exit;
}
$id = (int)($_GET['id'] ?? 0);
if(!$id){
    header('Location: purchase_view.php');
    exit;
}

try {
    $pdo->beginTransaction();
    $pdo->prepare("DELETE FROM purchase_items WHERE purchase_id = ?")->execute([$id]);
    $pdo->prepare("DELETE FROM purchases WHERE id = ?")->execute([$id]);
    $pdo->commit();
    header('Location: purchase_view.php?msg=deleted');
    exit;
} catch (Exception $ex) {
    $pdo->rollBack();
    echo "Error deleting: " . $ex->getMessage();
}
