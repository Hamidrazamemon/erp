<?php
require 'includes/session.php';
require 'includes/db.php';

// fetch purchases
$purchases = $pdo->query("SELECT p.*, s.name AS supplier_name
                          FROM purchases p
                          LEFT JOIN suppliers s ON p.supplier_id = s.id
                          ORDER BY p.id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Purchases | AT Optical ERP</title>
<style>
body{font-family:Arial;background:#f4f6f8;margin:0}
.topbar{background:#0078d7;color:#fff;padding:12px 20px;display:flex;justify-content:space-between}
.container{max-width:1200px;margin:20px auto;background:#fff;padding:18px;border-radius:8px}
table{width:100%;border-collapse:collapse}
th,td{border:1px solid #ddd;padding:8px;text-align:center}
th{background:#0078d7;color:#fff}
a.button{padding:6px 10px;border-radius:4px;text-decoration:none;color:#fff}
.edit{background:#ffc107}
.report{background:#17a2b8}
.del{background:#dc3545}
.add{background:blue}
</style>
</head>
<body>
<div class="topbar">
  <div><strong>AT Optical ERP</strong> | Purchases</div>
  <nav>
    <a href="purchase_add.php" style="color:#fff;text-decoration:none">Add Purchase</a>
    <a href="dashboard.php" style="color:#fff;text-decoration:none;margin-left:10px">Dashboard</a>
  </nav>
</div>

<div class="container">
  <h2>Purchase List</h2>
  <a class="button add" href="purchase.php">Add</a>
  <?php if(isset($_GET['msg'])): ?>
    <p style="color:green"> <?= htmlspecialchars($_GET['msg']) ?> </p>
  <?php endif; ?>

  <table>
    <tr>
      <th>ID</th>
      <th>Purchase No</th>
      <th>Supplier</th>
      <th>Total</th>
      <th>Paid</th>
      <th>Remaining</th>
      <th>Date</th>
      <th>Action</th>
    </tr>
    <?php foreach($purchases as $p): ?>
      <tr>
        <td><?= $p['id'] ?></td>
        <td><?= htmlspecialchars($p['purchase_no']) ?></td>
        <td><?= htmlspecialchars($p['supplier_name']) ?></td>
        <td><?= number_format($p['total_amount'],2) ?></td>
        <td><?= number_format($p['paid_amount'],2) ?></td>
        <td><?= number_format($p['remaining_amount'],2) ?></td>
        <td><?= $p['created_at'] ?? '' ?></td>
        <td>
          <a class="button edit" href="purchase_edit.php?id=<?= $p['id'] ?>">Edit</a>
          <a class="button report" href="purchase_report.php?id=<?= $p['id'] ?>">Report</a>
          <a class="button del" href="purchase_delete.php?id=<?= $p['id'] ?>" onclick="return confirm('Delete this purchase?')">Delete</a>
        </td>
      </tr>
    <?php endforeach; ?>
  </table>
</div>
</body>
</html>
