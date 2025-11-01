<?php
session_start();
require 'includes/db.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();

        $supplier_id = (int)($_POST['supplier_id'] ?? 0);
        $note = trim($_POST['note'] ?? '');
        $paid_amount = floatval($_POST['paid_amount'] ?? 0);
        $items = $_POST['items'] ?? [];

        if (!$supplier_id || empty($items)) {
            throw new Exception("Please select supplier and add at least one item.");
        }

        // Calculate total amount
        $grand_total = 0;
        foreach ($items as $it) {
            $qty = floatval($it['quantity'] ?? 0);
            $rate = floatval($it['rate'] ?? 0);
            $grand_total += $qty * $rate;
        }

        $remaining_amount = $grand_total - $paid_amount;
        if ($remaining_amount < 0) $remaining_amount = 0;

        // Insert into purchases table
        $purchase_no = 'PUR'.date('YmdHis').rand(10,99);
        $stmt = $pdo->prepare("
            INSERT INTO purchases 
            (purchase_no, supplier_id, total_amount, paid_amount, remaining_amount, note, created_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $purchase_no,
            $supplier_id,
            $grand_total,
            $paid_amount,
            $remaining_amount,
            $note
        ]);

        $purchase_id = $pdo->lastInsertId();

        // Insert items
        $item_stmt = $pdo->prepare("
            INSERT INTO purchase_items 
            (purchase_id, item_type, item_name, spherical, cylinder, addition, axis, quantity, rate, amount)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        foreach ($items as $it) {
            $item_type = $it['type'] ?? '';
            $item_name = trim($it['name'] ?? '');
            $spherical = ($item_type === 'Glass') ? ($it['spherical'] ?? null) : null;
            $cylinder = ($item_type === 'Glass') ? ($it['cylinder'] ?? null) : null;
            $addition = ($item_type === 'Glass') ? ($it['addition'] ?? null) : null;
            $axis = ($item_type === 'Glass') ? ($it['axis'] ?? null) : null;
            $qty = floatval($it['quantity'] ?? 0);
            $rate = floatval($it['rate'] ?? 0);
            $amount = $qty * $rate;

            $item_stmt->execute([
                $purchase_id,
                $item_type,
                $item_name,
                $spherical,
                $cylinder,
                $addition,
                $axis,
                $qty,
                $rate,
                $amount
            ]);
        }

        $pdo->commit();
        header("Location: purchase_view.php?msg=Purchase added successfully");
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        die("Error: " . $e->getMessage());
    }
}
?>
