<?php
require 'includes/session.php';
require 'includes/db.php';

if($_SERVER['REQUEST_METHOD']==='POST'){
    try{
        $pdo->beginTransaction();

        $purchase_id = (int)($_POST['purchase_id'] ?? 0);
        $note = trim($_POST['note'] ?? '');
        $items = $_POST['items'] ?? [];

        if(!$purchase_id || empty($items)) throw new Exception("Select purchase and return at least one item.");

        $grand_total = 0;
        foreach($items as $it){
            $qty = floatval($it['return_qty'] ?? 0);
            $rate = floatval($it['rate'] ?? 0);
            $grand_total += $qty * $rate;
        }

        // Insert into purchase_return
        $return_no = 'PRR'.date('YmdHis').rand(10,99);
        $stmt = $pdo->prepare("INSERT INTO purchase_return (purchase_id, return_no, total_amount, note, created_at) VALUES (?,?,?,?,NOW())");
        $stmt->execute([$purchase_id,$return_no,$grand_total,$note]);
        $return_id = $pdo->lastInsertId();

        // Insert items
        $item_stmt = $pdo->prepare("INSERT INTO purchase_return_items (return_id, purchase_item_id, quantity, rate, amount) VALUES (?,?,?,?,?)");
        foreach($items as $it){
            $qty = floatval($it['return_qty'] ?? 0);
            if($qty<=0) continue;
            $rate = floatval($it['rate'] ?? 0);
            $amount = $qty*$rate;
            $item_stmt->execute([$return_id, $it['item_id'], $qty, $rate, $amount]);
        }

        $pdo->commit();
        header("Location: purchase_return_view.php?msg=Return processed successfully");
        exit;

    }catch(Exception $e){
        $pdo->rollBack();
        die("Error: ".$e->getMessage());
    }
}
?>
