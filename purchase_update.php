<?php
require 'includes/session.php';
require 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $purchase_id = (int)($_POST['id'] ?? 0);
    if(!$purchase_id){
        die("Invalid Purchase ID");
    }

    try {
        $pdo->beginTransaction();

        $supplier_id = (int)($_POST['supplier_id']);
        $note = trim($_POST['note'] ?? '');
        $paid_amount = floatval($_POST['paid_amount'] ?? 0);
        $items = $_POST['items'] ?? [];

        // calculate grand total
        $grand_total = 0;
        foreach($items as $it){
            $q = floatval($it['quantity'] ?? 0);
            $r = floatval($it['rate'] ?? 0);
            $grand_total += $q * $r;
        }

        $remaining_amount = max(0, $grand_total - $paid_amount);

        // update purchase table
        $stmt = $pdo->prepare("
            UPDATE purchases SET 
            supplier_id=?, total_amount=?, paid_amount=?, remaining_amount=?, note=?, updated_at=NOW()
            WHERE id=?
        ");
        $stmt->execute([$supplier_id, $grand_total, $paid_amount, $remaining_amount, $note, $purchase_id]);

        // delete old items
        $pdo->prepare("DELETE FROM purchase_items WHERE purchase_id=?")->execute([$purchase_id]);

        // insert updated items
        $item_stmt = $pdo->prepare("
            INSERT INTO purchase_items 
            (purchase_id, item_type, item_name, spherical, cylinder, addition, axis, quantity, rate, amount)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        foreach($items as $it){
            $type = $it['type'] ?? '';
            $name = trim($it['name'] ?? '');
            $spherical = ($type==='Glass') ? ($it['spherical'] ?? null) : null;
            $cylinder = ($type==='Glass') ? ($it['cylinder'] ?? null) : null;
            $addition = ($type==='Glass') ? ($it['addition'] ?? null) : null;
            $axis = ($type==='Glass') ? ($it['axis'] ?? null) : null;
            $qty = floatval($it['quantity'] ?? 0);
            $rate = floatval($it['rate'] ?? 0);
            $amount = $qty * $rate;

            $item_stmt->execute([
                $purchase_id, $type, $name, $spherical, $cylinder, $addition, $axis, $qty, $rate, $amount
            ]);
        }

        $pdo->commit();
        header("Location: purchase_view.php?msg=Purchase Updated Successfully");
        exit;

    } catch (Exception $e){
        $pdo->rollBack();
        die("Error: " . $e->getMessage());
    }
}
?>