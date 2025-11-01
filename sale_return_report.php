<?php
require 'includes/session.php';
require 'includes/db.php';
require_once __DIR__.'/vendor/autoload.php';

$return_id = (int)($_GET['id'] ?? 0);
if(!$return_id) die("Return ID missing");

// fetch return info
$stmt = $pdo->prepare("
    SELECT sr.*, s.sale_no, c.name AS customer_name
    FROM sale_return sr
    JOIN sales s ON sr.sale_id = s.id
    JOIN customers c ON s.customer_id = c.id
    WHERE sr.id=?
");
$stmt->execute([$return_id]);
$return = $stmt->fetch(PDO::FETCH_ASSOC);
if(!$return) die("Return not found");

// fetch items
$item_stmt = $pdo->prepare("
    SELECT si.item_name, si.item_type, si.spherical, si.cylinder, si.addition, si.axis, sri.quantity, sri.rate, sri.amount
    FROM sale_return_items sri
    JOIN sale_items si ON sri.sale_item_id = si.id
    WHERE sri.return_id=?
");
$item_stmt->execute([$return_id]);
$items = $item_stmt->fetchAll(PDO::FETCH_ASSOC);

// generate PDF using mPDF
$mpdf = new \Mpdf\Mpdf();
$html = '<h2>Sale Return</h2>';
$html .= '<strong>Return No:</strong> '.$return['return_no'].'<br>';
$html .= '<strong>Sale No:</strong> '.$return['sale_no'].'<br>';
$html .= '<strong>Customer:</strong> '.$return['customer_name'].'<br>';
$html .= '<strong>Date:</strong> '.$return['created_at'].'<br><br>';

$html .= '<table border="1" cellpadding="6" cellspacing="0" width="100%">';
$html .= '<tr style="background:#0078d7;color:#fff;"><th>Item</th><th>Type</th><th>Sph</th><th>Cyl</th><th>Add</th><th>Axis</th><th>Qty</th><th>Rate</th><th>Amount</th></tr>';

foreach($items as $it){
    $html .= '<tr>';
    $html .= '<td>'.$it['item_name'].'</td>';
    $html .= '<td>'.$it['item_type'].'</td>';
    $html .= '<td>'.$it['spherical'].'</td>';
    $html .= '<td>'.$it['cylinder'].'</td>';
    $html .= '<td>'.$it['addition'].'</td>';
    $html .= '<td>'.$it['axis'].'</td>';
    $html .= '<td>'.$it['quantity'].'</td>';
    $html .= '<td>'.$it['rate'].'</td>';
    $html .= '<td>'.$it['amount'].'</td>';
    $html .= '</tr>';
}

$html .= '<tr><td colspan="8" style="text-align:right;"><strong>Total</strong></td><td>'.$return['total_amount'].'</td></tr>';
$html .= '</table>';

$mpdf->WriteHTML($html);
$mpdf->Output('sale_return_'.$return['return_no'].'.pdf','I');
