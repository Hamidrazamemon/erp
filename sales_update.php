<?php
require 'includes/session.php';
require 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sale_id = (int)($_POST['id'] ?? 0);
    if(!$sale_id){
        die("Invalid Sale ID");
    }

    try {
        $pdo->beginTransaction();

        $customer_id = (int)($_POST['customer_id']);
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

        // update sale table (without updated_at)
        $stmt = $pdo->prepare("
            UPDATE sales SET 
            customer_id=?, total_amount=?, paid_amount=?, remaining_amount=?, note=?
            WHERE id=?
        ");
        $stmt->execute([$customer_id, $grand_total, $paid_amount, $remaining_amount, $note, $sale_id]);

        // delete old items
        $pdo->prepare("DELETE FROM sale_items WHERE sale_id=?")->execute([$sale_id]);

        // insert updated items
        $item_stmt = $pdo->prepare("
            INSERT INTO sale_items 
            (sale_id, item_type, item_name, spherical, cylinder, addition, axis, quantity, rate, amount)
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
                $sale_id, $type, $name, $spherical, $cylinder, $addition, $axis, $qty, $rate, $amount
            ]);
        }

        $pdo->commit();
        header("Location: sale_view.php?msg=Sale Updated Successfully");
        exit;

    } catch (Exception $e){
        $pdo->rollBack();
        die("Error: " . $e->getMessage());
    }
}
?>
