<?php
session_start();
require 'includes/db.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: index.php");
    exit;
}

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
.topbar {
  background:#0078d7; color:#fff; padding:12px 20px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap;
}
nav a, .dropbtn {
  color:#fff; text-decoration:none; font-weight:bold; padding:6px 10px; border:none; background:none; cursor:pointer;
}
nav a:hover, .dropbtn:hover { text-decoration:underline; }
.dropdown { position:relative; display:inline-block; }
.dropdown-content {
  display:none; position:absolute; background:#fff; min-width:180px; box-shadow:0 4px 8px rgba(0,0,0,0.2); z-index:1; border-radius:6px; overflow:hidden;
}
.dropdown-content a { color:#0078d7; padding:10px 14px; text-decoration:none; display:block; }
.dropdown-content a:hover { background:#f1f1f1; }
.dropdown:hover .dropdown-content { display:block; }

.container{max-width:1200px;margin:20px auto;background:#fff;padding:18px;border-radius:8px;box-shadow:0 0 8px rgba(0,0,0,.08)}
table{width:100%;border-collapse:collapse;margin-top:15px}
th,td{border:1px solid #ddd;padding:8px;text-align:center}
th{background:#0078d7;color:#fff}
a.button{padding:4px 10px;border-radius:4px;background:#dc3545;color:#fff;text-decoration:none;margin-right:4px;}
a.pdf-button{padding:4px 10px;border-radius:4px;background:#28a745;color:#fff;text-decoration:none;}
a.button:hover, a.pdf-button:hover{opacity:0.9;}
h2{color:#0078d7;margin-top:0;}
</style>
</head>
<body>

<div class="topbar">
  <div><strong>AT Optical ERP</strong></div>
  <nav>
  <a href="dashboard.php">Dashboard</a>
  <a href="glass.php">Glasses</a>
  <a href="suppliers.php">Suppliers</a>
  <a href="customers.php">Customers</a>

  <!-- Purchase Dropdown -->
  <div class="dropdown">
    <button class="dropbtn">Purchase ▼</button>
    <div class="dropdown-content">
      <a href="purchase.php">Add Purchase</a>
      <a href="purchase_view.php">View Purchases</a>
      <a href="purchase_return.php">Add Purchase Return</a>
      <a href="purchase_return_view.php">View Purchase Returns</a>
    </div>
  </div>

  <!-- Sales Dropdown -->
  <div class="dropdown">
    <button class="dropbtn">Sales ▼</button>
    <div class="dropdown-content">
      <a href="sales.php">Add Sale</a>
      <a href="sale_view.php">View Sales</a>
      <a href="sale_return.php">Add Sale Return</a>
      <a href="sale_return_view.php">View Sale Returns</a>
    </div>
  </div>


  <!-- Report Dropdown -->
  <div class="dropdown">
    <button class="dropbtn" style="text-decoration:underline;">Report ▼</button>
    <div class="dropdown-content">
      <a href="report.php">Main Report</a>
      <a href="rate_list.php">Rate List</a>
       <a href="ledger.php">Ledger</a>
  <a href="stock.php">Stock</a>
  <a href="balance.php" style="text-decoration:underline;">Balance</a>

    </div>
  </div>

  <a href="logout.php" style="color:#ffdddd;">Logout</a>
</nav>
</div>

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
