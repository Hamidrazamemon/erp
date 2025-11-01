<?php
require 'includes/session.php';
require 'includes/db.php';

$type = $_POST['type'] ?? $_GET['type'] ?? ''; 
$message = '';

// Handle payment update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['receive'])) {
    foreach ($_POST['receive'] as $key => $amount) {
        $amount = floatval($amount);
        if ($amount <= 0) continue;

        if ($type === 'customer') {
            $sales = $pdo->prepare("SELECT sale_no, remaining_amount, paid_amount FROM sales WHERE customer_id=? AND remaining_amount>0 ORDER BY id ASC");
            $sales->execute([$key]);
            $rows = $sales->fetchAll(PDO::FETCH_ASSOC);

            $total_remaining = array_sum(array_column($rows, 'remaining_amount'));
            if ($amount > $total_remaining) continue; // ❌ skip if more than remaining

            $remaining = $amount;
            foreach ($rows as $r) {
                if ($remaining <= 0) break;
                $pay = min($remaining, $r['remaining_amount']);
                $remaining -= $pay;

                $update = $pdo->prepare("UPDATE sales SET paid_amount=paid_amount+?, remaining_amount=remaining_amount-? WHERE sale_no=?");
                $update->execute([$pay, $pay, $r['sale_no']]);
            }
        }

        if ($type === 'supplier') {
            $purchases = $pdo->prepare("SELECT purchase_no, remaining_amount, paid_amount FROM purchases WHERE supplier_id=? AND remaining_amount>0 ORDER BY id ASC");
            $purchases->execute([$key]);
            $rows = $purchases->fetchAll(PDO::FETCH_ASSOC);

            $total_remaining = array_sum(array_column($rows, 'remaining_amount'));
            if ($amount > $total_remaining) continue; // ❌ skip if more than remaining

            $remaining = $amount;
            foreach ($rows as $r) {
                if ($remaining <= 0) break;
                $pay = min($remaining, $r['remaining_amount']);
                $remaining -= $pay;

                $update = $pdo->prepare("UPDATE purchases SET paid_amount=paid_amount+?, remaining_amount=remaining_amount-? WHERE purchase_no=?");
                $update->execute([$pay, $pay, $r['purchase_no']]);
            }
        }
    }
    $message = "<p style='color:green;font-weight:bold;text-align:center;'>✅ Payments updated successfully!</p>";
}

// Fetch data
if ($type === 'customer') {
    $records = $pdo->query("
        SELECT c.id, c.name,
               IFNULL(SUM(s.total_amount),0) AS total_amount,
               IFNULL(SUM(s.paid_amount),0) AS paid_amount,
               IFNULL(SUM(s.remaining_amount),0) AS remaining_amount
        FROM customers c
        LEFT JOIN sales s ON c.id = s.customer_id
        GROUP BY c.id
    ")->fetchAll(PDO::FETCH_ASSOC);
} elseif ($type === 'supplier') {
    $records = $pdo->query("
        SELECT sp.id, sp.name,
               IFNULL(SUM(p.total_amount),0) AS total_amount,
               IFNULL(SUM(p.paid_amount),0) AS paid_amount,
               IFNULL(SUM(p.remaining_amount),0) AS remaining_amount
        FROM suppliers sp
        LEFT JOIN purchases p ON sp.id = p.supplier_id
        GROUP BY sp.id
    ")->fetchAll(PDO::FETCH_ASSOC);
} else {
    $records = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Balance Management | AT Optical ERP</title>
<style>
body { background:#f4f6f8; font-family:Arial,Helvetica,sans-serif; margin:0; }
.topbar { background:#0078d7; color:#fff; padding:12px 20px; display:flex; justify-content:space-between; align-items:center; }
.topbar nav a { color:#fff; text-decoration:none; margin-left:14px; font-weight:bold; }
.topbar nav a:hover { text-decoration:underline; }

.container { max-width:1200px; margin:20px auto; background:#fff; padding:20px; border-radius:10px; box-shadow:0 0 8px rgba(0,0,0,0.1); }
h2 { color:#0078d7; text-align:center; }
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

select { padding:8px; border:1px solid #ccc; border-radius:5px; }
button {
  background:#0078d7; color:#fff; border:none; padding:8px 14px;
  border-radius:5px; cursor:pointer; font-weight:bold;
}
button:hover { background:#005fa3; }

table {
  width:100%; border-collapse:collapse; margin-top:20px;
}
th,td { border:1px solid #ddd; padding:8px; text-align:center; }
th { background:#0078d7; color:#fff; }
tfoot td { background:#f1f5f9; font-weight:bold; }

input[type=number]{width:120px; padding:6px; border:1px solid #ccc; border-radius:5px; text-align:right;}
.action-bar { display:flex; justify-content:space-between; margin-bottom:10px; align-items:center; }
.print-btn { background:#28a745; }
.print-btn:hover { background:#1e7e34; }

/* ✅ Print style */
@media print {
  body { background:#fff; margin:0; }
  .topbar, form, .action-bar button[type=submit] { display:none !important; }
  .container { box-shadow:none; border:none; width:100%; margin:0; padding:0; }
  table { width:100%; margin:0 auto; border:1px solid #000; }
  th, td { border:1px solid #000; font-size:13px; }
  @page { size: A4 portrait; margin:10mm; }
}
</style>

<script>
function printTable(){
  window.print();
}

function validateInput(el, maxValue) {
  const val = parseFloat(el.value || 0);
  if (val > maxValue) {
    alert("❌ Entered amount cannot exceed remaining balance: " + maxValue);
    el.value = maxValue.toFixed(2);
  }
}
</script>
</head>
<body>
<div class="topbar">
  <div><strong>AT Optical ERP</strong> | Balance Management</div>
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
  <h2>Customer / Supplier Balances</h2>
  <?= $message ?>

  <form method="GET" style="text-align:center; margin-bottom:20px;">
    <label><strong>Select Type:</strong></label>
    <select name="type" onchange="this.form.submit()">
      <option value="">-- Select --</option>
      <option value="customer" <?= $type=='customer'?'selected':'' ?>>Customer</option>
      <option value="supplier" <?= $type=='supplier'?'selected':'' ?>>Supplier</option>
    </select>
  </form>

  <?php if ($type): ?>
  <div class="action-bar">
    <form method="POST" style="width:100%;">
      <input type="hidden" name="type" value="<?= htmlspecialchars($type) ?>">
      <div id="printSection" style="width:100%;">
        <table>
          <thead>
            <tr>
              <th>Name</th>
              <th>Total Amount</th>
              <th><?= $type=='customer'?'Receive':'Pay' ?> Amount</th>
              <th>Remaining</th>
            </tr>
          </thead>
          <tbody>
          <?php 
          $total_due=0;
          foreach($records as $r): 
              $total_due += $r['remaining_amount'];
          ?>
            <tr>
              <td><?= htmlspecialchars($r['name']) ?></td>
              <td><?= number_format($r['total_amount'],2) ?></td>
              <td>
                <input type="number" step="0.01" 
                       name="receive[<?= $r['id'] ?>]" 
                       placeholder="0.00" 
                       oninput="validateInput(this, <?= $r['remaining_amount'] ?>)">
              </td>
              <td><?= number_format($r['remaining_amount'],2) ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
          <tfoot>
            <tr><td colspan="3">Total Remaining</td><td><?= number_format($total_due,2) ?></td></tr>
          </tfoot>
        </table>
      </div>
      <div style="margin-top:15px; text-align:right;">
        <button type="submit">Submit</button>
        <button type="button" class="print-btn" onclick="printTable()">Print</button>
      </div>
    </form>
  </div>
  <?php endif; ?>
</div>
</body>
</html>
