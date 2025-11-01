<?php
require 'includes/session.php';
require 'includes/db.php';

$return_id = (int)($_GET['id'] ?? 0);
if(!$return_id) die("Return ID missing.");

try{
    $pdo->beginTransaction();

    // delete return items
    $stmt = $pdo->prepare("DELETE FROM purchase_return_items WHERE return_id=?");
    $stmt->execute([$return_id]);

    // delete main return
    $stmt = $pdo->prepare("DELETE FROM purchase_return WHERE id=?");
    $stmt->execute([$return_id]);

    $pdo->commit();
    header("Location: purchase_return_view.php?msg=Return deleted successfully");
    exit;

}catch(Exception $e){
    $pdo->rollBack();
    die("Error: ".$e->getMessage());
}
?>
