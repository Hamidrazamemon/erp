<?php
require 'includes/session.php';
require 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();

        $supplier_id = (int)$_POST['supplier_id'];
        $note = trim($_POST['note'] ?? '');
        $paid_amount = floatval($_POST['paid_amount'] ?? 0);

        $items = $_POST['items'] ?? [];
        $grand_total = 0;

        foreach ($items as $it) {
            $item_type = $it['type'] ?? '';
            $item_name = trim($it['name'] ?? '');
            $spherical = ($item_type === 'Glass') ? ($it['spherical'] ?? null) : null;
            $cylinder = ($item_type === 'Glass') ? ($it['cylinder'] ?? null) : null;
            $quantity = floatval($it['quantity'] ?? 0);
            $rate = floatval($it['rate'] ?? 0);
            $amount = $quantity * $rate;

            $grand_total += $amount;

            // create unique purchase number
            $purchase_no = 'PUR' . date('YmdHis') . rand(10, 99);

            // calculate remaining based on paid amount per item
            $remaining_amount = $amount - $paid_amount;
            if ($remaining_amount < 0) $remaining_amount = 0;

            $stmt = $pdo->prepare("
                INSERT INTO purchases 
                (purchase_no, supplier_id, item_type, item_name, spherical, cylinder, quantity, rate, total_amount, payment, paid_amount, remaining_amount, note)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $purchase_no,
                $supplier_id,
                $item_type,
                $item_name,
                $spherical,
                $cylinder,
                $quantity,
                $rate,
                $amount,
                $paid_amount,
                $paid_amount,
                $remaining_amount,
                $note
            ]);
        }

        $pdo->commit();
        header("Location: purchase_view.php?msg=Purchase Added Successfully");
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        die("Error: " . $e->getMessage());
    }
}
?>
