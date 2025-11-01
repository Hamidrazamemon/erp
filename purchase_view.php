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
/* minimal styling */
body { background:#f4f6f8; font-family:Arial,Helvetica,sans-serif; margin:0; }
.topbar {
  background:#0078d7; color:#fff; padding:12px 20px;
  display:flex; justify-content:space-between; align-items:center;
}
.topbar nav a { color:#fff; text-decoration:none; margin-left:14px; font-weight:bold; }
.topbar nav a:hover { text-decoration:underline; }
.container{max-width:1200px;margin:20px auto;background:#fff;padding:18px;border-radius:8px}
table{width:100%;border-collapse:collapse}
th,td{border:1px solid #ddd;padding:8px;text-align:center}
th{background:#0078d7;color:#fff}
a.button{padding:6px 10px;border-radius:4px;text-decoration:none;color:#fff}
.edit{background:#ffc107}
.report{background:#17a2b8}
.del{background:#dc3545}
.add{background:blue}

nav {
  display:flex;
  gap:12px;
  align-items:center;
  background:#0078d7;
  padding:12px 20px;
  flex-wrap:wrap;
}
nav a, .dropbtn {
  color:#fff;
  text-decoration:none;
  font-weight:bold;
  padding:6px 10px;
  border:none;
  background:none;
  cursor:pointer;
}
nav a:hover, .dropbtn:hover { text-decoration:underline; }

.dropdown {
  position:relative;
  display:inline-block;
}
.dropdown-content {
  display:none;
  position:absolute;
  background:#0078d7;
  min-width:180px;
  box-shadow:0 4px 8px rgba(0,0,0,0.2);
  z-index:1;
  border-radius:6px;
  overflow:hidden;
}
.dropdown-content a { 
  padding:10px 14px;
  text-decoration:none;
  display:block;
}
.dropdown-content a:hover { background:black; }

.dropdown:hover .dropdown-content { display:block; }
h2 { color:#0078d7; margin-top:0; text-align:center; }


</style>
</head>
<body>
<div class="topbar">
  <div><strong>AT Optical ERP</strong> | Purchases</div>
    <nav>
  <a href="dashboard.php">Dashboard</a>
  <a href="glass.php">Glasses</a>
  <a href="suppliers.php">Suppliers</a>
  <a href="customers.php">Customers</a>

  <!-- Purchase Dropdown -->
  <div class="dropdown">
    <button class="dropbtn" style="text-decoration:underline;">Purchase ▼</button>
    <div class="dropdown-content">
      <a href="purchase.php">Add Purchase</a>
      <a href="purchase_view.php" style="text-decoration:underline;">View Purchases</a>
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
    <button class="dropbtn">Report ▼</button>
    <div class="dropdown-content">
      <a href="report.php">Main Report</a>
      <a href="rate_list.php">Rate List</a>
       <a href="ledger.php">Ledger</a>
  <a href="stock.php">Stock</a>
  <a href="balance.php">Balance</a>

    </div>
  </div>

  <a href="logout.php" style="color:#ffdddd;">Logout</a>
</nav>  
</div>

<div class="container">
  <h2>Purchase List</h2>
  <a class="button add" style="background-color:black ; padding : 10px ;" href="purchase.php">Add</a>
  <?php if(isset($_GET['msg'])): ?>
    
    <p style="color:green"> <?= htmlspecialchars($_GET['msg']) ?> </p>
  <?php endif; ?>
<br><br>
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
