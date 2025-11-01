<?php
require 'includes/session.php';
require 'includes/db.php';

$return_id = (int)($_GET['id'] ?? 0);
if(!$return_id){
    die("Return ID missing.");
}

try {
    $pdo->beginTransaction();

    // first delete the items
    $stmt = $pdo->prepare("DELETE FROM sale_return_items WHERE return_id=?");
    $stmt->execute([$return_id]);

    // then delete the main return
    $stmt = $pdo->prepare("DELETE FROM sale_return WHERE id=?");
    $stmt->execute([$return_id]);

    $pdo->commit();

    header("Location: sale_return_view.php?msg=Deleted successfully");
    exit;
} catch (Exception $e) {
    $pdo->rollBack();
    die("Error deleting return: " . $e->getMessage());
}
