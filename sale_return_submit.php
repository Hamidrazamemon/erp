<?php
require 'includes/session.php';
require 'includes/db.php';

if($_SERVER['REQUEST_METHOD']==='POST'){
    try{
        $pdo->beginTransaction();

        $sale_id = (int)($_POST['sale_id']??0);
        $note = trim($_POST['note']??'');
        $items = $_POST['items']??[];

        if(!$sale_id || empty($items)) throw new Exception("Select sale and items to return");

        $total_amount = 0;
        foreach($items as $it){
            $total_amount += floatval($it['quantity']??0) * floatval($it['rate']??0);
        }

        $return_no = 'SR'.date('YmdHis').rand(10,99);
        $stmt = $pdo->prepare("INSERT INTO sale_return (return_no, sale_id, total_amount, note, created_at) VALUES (?,?,?,?,NOW())");
        $stmt->execute([$return_no, $sale_id, $total_amount, $note]);
        $return_id = $pdo->lastInsertId();

        $item_stmt = $pdo->prepare("INSERT INTO sale_return_items (return_id, sale_item_id, quantity, rate, amount) VALUES (?,?,?,?,?)");
        foreach($items as $it){
            $amount = floatval($it['quantity'])*floatval($it['rate']);
            $item_stmt->execute([$return_id, $it['sale_item_id'], $it['quantity'], $it['rate'], $amount]);
        }

        $pdo->commit();
        header("Location: sale_return_view.php?msg=Sale Return Added Successfully");
        exit;
    }catch(Exception $e){
        $pdo->rollBack();
        die("Error: ".$e->getMessage());
    }
}
?>
