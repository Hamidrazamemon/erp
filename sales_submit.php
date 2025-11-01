<?php
session_start();
require 'includes/db.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: sales_view.php");
    exit;
}

$sale_id = (int)($_POST['sale_id'] ?? 0);
if (!$sale_id) die("Invalid Sale ID");

$customer_id = (int)($_POST['customer_id'] ?? 0);
$paid_amount = (float)($_POST['paid_amount'] ?? 0);
$note = trim($_POST['note'] ?? '');
$items = $_POST['items'] ?? [];

if (!$customer_id || empty($items)) die("Customer and items are required");

try {
    $pdo->beginTransaction();

    // Calculate total
    $total_amount = 0;
    foreach ($items as $it) {
        $qty = floatval($it['quantity'] ?? 0);
        $rate = floatval($it['rate'] ?? 0);
        $total_amount += ($qty * $rate);
    }

    $remaining_amount = $total_amount - $paid_amount;
    if ($remaining_amount < 0) $remaining_amount = 0;

    // Update main sale record
    $stmt = $pdo->prepare("
        UPDATE sales SET 
            customer_id=?, 
            total_amount=?, 
            paid_amount=?, 
            remaining_amount=?, 
            note=?
        WHERE id=?
    ");
    $stmt->execute([$customer_id, $total_amount, $paid_amount, $remaining_amount, $note, $sale_id]);

    // Delete old items
    $pdo->prepare("DELETE FROM sale_items WHERE sale_id=?")->execute([$sale_id]);

    // Insert updated items
    $stmt_item = $pdo->prepare("
        INSERT INTO sale_items 
        (sale_id, item_type, item_name, spherical, cylinder, addition, axis, quantity, rate, amount)
        VALUES (?,?,?,?,?,?,?,?,?,?)
    ");

    foreach ($items as $it) {
        $qty = floatval($it['quantity'] ?? 0);
        $rate = floatval($it['rate'] ?? 0);
        $stmt_item->execute([
            $sale_id,
            $it['type'] ?? '',
            $it['name'] ?? '',
            ($it['type']=='Glass') ? ($it['spherical'] ?? null) : null,
            ($it['type']=='Glass') ? ($it['cylinder'] ?? null) : null,
            ($it['type']=='Glass') ? ($it['addition'] ?? null) : null,
            ($it['type']=='Glass') ? ($it['axis'] ?? null) : null,
            $qty,
            $rate,
            $qty*$rate
        ]);
    }

    $pdo->commit();
    header("Location: sales_view.php?msg=Sale Updated Successfully");
    exit;

} catch (Exception $e) {
    $pdo->rollBack();
    die("Error updating sale: " . $e->getMessage());
}
?>
