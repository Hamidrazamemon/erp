<?php
require 'includes/session.php';
require 'includes/db.php';

// fetch all purchase returns with supplier info
$returns = $pdo->query("
    SELECT pr.id, pr.return_no, p.purchase_no, s.name AS supplier_name, pr.total_amount, pr.created_at
    FROM purchase_return pr
    JOIN purchases p ON pr.purchase_id = p.id
    JOIN suppliers s ON p.supplier_id = s.id
    ORDER BY pr.created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Purchase Returns | AT Optical ERP</title>
<style>
body { font-family:Arial,sans-serif; background:#f4f6f8; margin:0; }
.container{max-width:1200px;margin:20px auto;background:#fff;padding:18px;border-radius:8px;box-shadow:0 0 8px rgba(0,0,0,.08)}
table{width:100%;border-collapse:collapse}
th,td{border:1px solid #ddd;padding:8px;text-align:center}
th{background:#0078d7;color:#fff}
a.button{padding:4px 10px;border-radius:4px;background:#dc3545;color:#fff;text-decoration:none;margin-right:4px;}
a.pdf-button{padding:4px 10px;border-radius:4px;background:#28a745;color:#fff;text-decoration:none;}
a.button:hover, a.pdf-button:hover{opacity:0.9;}
</style>
</head>
<body>
<div class="container">
<h2>Purchase Returns</h2>

<table>
<thead>
<tr>
<th>Return No</th>
<th>Purchase No</th>
<th>Supplier</th>
<th>Total Amount</th>
<th>Date</th>
<th>Action</th>
</tr>
</thead>
<tbody>
<?php foreach($returns as $r): ?>
<tr>
<td><?= htmlspecialchars($r['return_no']) ?></td>
<td><?= htmlspecialchars($r['purchase_no']) ?></td>
<td><?= htmlspecialchars($r['supplier_name']) ?></td>
<td><?= number_format($r['total_amount'],2) ?></td>
<td><?= $r['created_at'] ?></td>
<td>
    <a class="pdf-button" href="purchase_return_report.php?id=<?= $r['id'] ?>" target="_blank">PDF</a>
    <a class="button" href="purchase_return_delete.php?id=<?= $r['id'] ?>" onclick="return confirm('Are you sure you want to delete this return?');">Delete</a>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
</body>
</html>
