<?php
require 'includes/session.php';
require 'includes/db.php';

$returns = $pdo->query("
    SELECT sr.id, sr.return_no, s.sale_no, c.name AS customer_name, sr.total_amount, sr.created_at
    FROM sale_return sr
    JOIN sales s ON sr.sale_id=s.id
    JOIN customers c ON s.customer_id=c.id
    ORDER BY sr.created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Sale Returns | AT Optical ERP</title>
<style>
body { font-family:Arial,sans-serif; background:#f4f6f8; margin:0; }
.container{max-width:1200px;margin:20px auto;background:#fff;padding:18px;border-radius:8px;box-shadow:0 0 8px rgba(0,0,0,.08);}
h2{margin-top:0;}
table{width:100%;border-collapse:collapse;margin-top:12px;}
th,td{border:1px solid #ddd;padding:8px;text-align:center;}
th{background:#0078d7;color:#fff;}
a.button{padding:4px 10px;border-radius:4px;text-decoration:none;color:#fff;margin-right:4px;}
a.delete{background:#dc3545;}
a.pdf{background:#28a745;}
a.button:hover{opacity:0.9;}
</style>
</head>
<body>
<div class="container">
<h2>Sale Returns</h2>
<a href="sale_return.php" class="button" style="background:#0078d7;margin-bottom:10px;">+ Add Sale Return</a>

<table>
<thead>
<tr>
<th>Return No</th>
<th>Sale No</th>
<th>Customer</th>
<th>Total Amount</th>
<th>Date</th>
<th>Action</th>
</tr>
</thead>
<tbody>
<?php foreach($returns as $r): ?>
<tr>
<td><?= htmlspecialchars($r['return_no']) ?></td>
<td><?= htmlspecialchars($r['sale_no']) ?></td>
<td><?= htmlspecialchars($r['customer_name']) ?></td>
<td><?= number_format($r['total_amount'],2) ?></td>
<td><?= $r['created_at'] ?></td>
<td>
    <a href="sale_return_report.php?id=<?= $r['id'] ?>" target="_blank" class="button pdf">PDF</a>
    <a href="sale_return_delete.php?id=<?= $r['id'] ?>" onclick="return confirm('Are you sure you want to delete this return?');" class="button delete">Delete</a>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
</body>
</html>
