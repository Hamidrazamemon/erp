<?php
session_start();
require 'includes/db.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: index.php");
    exit;
}
require_once __DIR__ . '/vendor/autoload.php'; // path to autoload.php

$return_id = (int)($_GET['id'] ?? 0);
if(!$return_id) die("Return ID missing.");

// fetch return info
$stmt = $pdo->prepare("
    SELECT pr.*, p.purchase_no, s.name AS supplier_name
    FROM purchase_return pr
    JOIN purchases p ON pr.purchase_id = p.id
    JOIN suppliers s ON p.supplier_id = s.id
    WHERE pr.id=?
");
$stmt->execute([$return_id]);
$return = $stmt->fetch(PDO::FETCH_ASSOC);
if(!$return) die("Return not found.");

// fetch return items
$item_stmt = $pdo->prepare("
    SELECT pi.item_name, pi.item_type, pi.spherical, pi.cylinder, pi.addition, pi.axis, pri.quantity, pri.rate, pri.amount
    FROM purchase_return_items pri
    JOIN purchase_items pi ON pri.purchase_item_id = pi.id
    WHERE pri.return_id=?
");
$item_stmt->execute([$return_id]);
$items = $item_stmt->fetchAll(PDO::FETCH_ASSOC);

// Generate HTML for PDF
$html = '
<h2 style="text-align:center;">AT Optical ERP - Purchase Return</h2>
<p>
<strong>Return No:</strong> '.htmlspecialchars($return['return_no']).'<br>
<strong>Purchase No:</strong> '.htmlspecialchars($return['purchase_no']).'<br>
<strong>Supplier:</strong> '.htmlspecialchars($return['supplier_name']).'<br>
<strong>Total Amount:</strong> '.number_format($return['total_amount'],2).'<br>
<strong>Note:</strong> '.htmlspecialchars($return['note']).'<br>
<strong>Date:</strong> '.$return['created_at'].'
</p>

<table width="100%" border="1" cellpadding="6" cellspacing="0" style="border-collapse:collapse;">
<thead>
<tr style="background:#0078d7;color:#fff;">
<th>Item</th>
<th>Type</th>
<th>Sph</th>
<th>Cyl</th>
<th>Add</th>
<th>Axis</th>
<th>Qty</th>
<th>Rate</th>
<th>Amount</th>
</tr>
</thead>
<tbody>';

foreach($items as $i){
    $html .= '<tr>
    <td>'.htmlspecialchars($i['item_name']).'</td>
    <td>'.htmlspecialchars($i['item_type']).'</td>
    <td>'.$i['spherical'].'</td>
    <td>'.$i['cylinder'].'</td>
    <td>'.$i['addition'].'</td>
    <td>'.$i['axis'].'</td>
    <td>'.$i['quantity'].'</td>
    <td>'.number_format($i['rate'],2).'</td>
    <td>'.number_format($i['amount'],2).'</td>
    </tr>';
}

$html .= '</tbody></table>';

// Generate PDF
$mpdf = new \Mpdf\Mpdf();
$mpdf->SetTitle('Purchase Return '.$return['return_no']);
$mpdf->WriteHTML($html);
$mpdf->Output('Purchase_Return_'.$return['return_no'].'.pdf','I'); // I = Inline (browser)
