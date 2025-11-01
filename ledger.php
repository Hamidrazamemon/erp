<?php
require 'includes/session.php';
require 'includes/db.php';

$type = $_POST['type'] ?? '';
$person_id = $_POST['person_id'] ?? 0;
$rows = [];
$person_name = '';
$msg = '';

/* ------------------- ADD BULK PAYMENT ------------------- */
if (isset($_POST['add_total_payment'])) {
    $type = $_POST['type'];
    $person_id = $_POST['person_id'];
    $amount = floatval($_POST['total_payment']);

    if ($type === 'customer') {
        $tbl = 'sales';
        $col_person = 'customer_id';
    } else {
        $tbl = 'purchases';
        $col_person = 'supplier_id';
    }

    $check = $pdo->prepare("SELECT SUM(remaining_amount) FROM $tbl WHERE $col_person=?");
    $check->execute([$person_id]);
    $total_remaining = floatval($check->fetchColumn());

    if ($amount <= 0) {
        $msg = "<p style='color:red;'>Enter a valid payment amount!</p>";
    } elseif ($amount > $total_remaining) {
        $msg = "<p style='color:red;'>Payment exceeds total remaining amount (Rs. " . number_format($total_remaining,2) . ").</p>";
    } else {
        $pdo->beginTransaction();
        $query = $pdo->prepare("SELECT id, remaining_amount, paid_amount FROM $tbl WHERE $col_person=? AND remaining_amount>0 ORDER BY created_at ASC");
        $query->execute([$person_id]);
        $bills = $query->fetchAll(PDO::FETCH_ASSOC);

        $remaining_payment = $amount;

        foreach ($bills as $b) {
            if ($remaining_payment <= 0) break;
            $apply = min($b['remaining_amount'], $remaining_payment);
            $new_remaining = $b['remaining_amount'] - $apply;
            $new_paid = $b['paid_amount'] + $apply;
            $update = $pdo->prepare("UPDATE $tbl SET remaining_amount=?, paid_amount=? WHERE id=?");
            $update->execute([$new_remaining, $new_paid, $b['id']]);
            $remaining_payment -= $apply;
        }

        // **Insert into payments table**
        $stmt = $pdo->prepare("INSERT INTO payments (person_id, type, amount, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$person_id, $type, $amount]);

        $pdo->commit();
        $msg = "<p style='color:green;'>Payment of Rs. $amount successfully added and recorded in payment history.</p>";
    }
}


/* ------------------- FETCH DROPDOWN OPTIONS ------------------- */
$customers = $pdo->query("SELECT id, name FROM customers ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
$suppliers = $pdo->query("SELECT id, name FROM suppliers ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);


/* ------------------- VIEW LEDGER ------------------- */
$payment_rows = [];
if (isset($_POST['view_payments']) && $type && $person_id) {
    $stmt = $pdo->prepare("SELECT id, type, amount, created_at 
                           FROM payments WHERE person_id=? ORDER BY created_at ASC");
    $stmt->execute([$person_id]);
    $payment_rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Person name fetch karo
    if($type==='customer'){
        $person_name = $pdo->prepare("SELECT name FROM customers WHERE id=?");
    } else {
        $person_name = $pdo->prepare("SELECT name FROM suppliers WHERE id=?");
    }
    $person_name->execute([$person_id]);
    $person_name = $person_name->fetchColumn();
}

if (isset($_POST['view']) && $type && $person_id) {
    $rows = [];
    if ($type === 'customer') {
        $stmt = $pdo->prepare("SELECT name FROM customers WHERE id=?");
        $stmt->execute([$person_id]);
        $person_name = $stmt->fetchColumn();

        // Sales
        $query1 = $pdo->prepare("SELECT 'Sale' AS type, sale_no AS code, total_amount, paid_amount, remaining_amount, created_at 
                                 FROM sales WHERE customer_id=?");
        $query1->execute([$person_id]);
        $sales = $query1->fetchAll(PDO::FETCH_ASSOC);

        // Sale Returns
        $query2 = $pdo->prepare("
    SELECT 
        'Sale Return' AS type, 
        sr.return_no AS code, 
        -sr.total_amount AS total_amount, 
        0 AS paid_amount, 
        -sr.total_amount AS remaining_amount, 
        sr.created_at
    FROM sale_return sr
    JOIN sales s ON sr.sale_id = s.id
    WHERE s.customer_id=?
");

        $query2->execute([$person_id]);
        $returns = $query2->fetchAll(PDO::FETCH_ASSOC);

        $rows = array_merge($sales, $returns);

    } else {
        $stmt = $pdo->prepare("SELECT name FROM suppliers WHERE id=?");
        $stmt->execute([$person_id]);
        $person_name = $stmt->fetchColumn();

        // Purchases
        $query1 = $pdo->prepare("SELECT 'Purchase' AS type, purchase_no AS code, total_amount, paid_amount, remaining_amount, created_at 
                                 FROM purchases WHERE supplier_id=?");
        $query1->execute([$person_id]);
        $purchases = $query1->fetchAll(PDO::FETCH_ASSOC);

        // Purchase Returns
        $query2 = $pdo->prepare("
    SELECT 
        'Purchase Return' AS type, 
        pr.return_no AS code, 
        -pr.total_amount AS total_amount, 
        0 AS paid_amount, 
        -pr.total_amount AS remaining_amount, 
        pr.created_at
    FROM purchase_return pr
    JOIN purchases p ON pr.purchase_id = p.id
    WHERE p.supplier_id=?
");

        $query2->execute([$person_id]);
        $returns = $query2->fetchAll(PDO::FETCH_ASSOC);

        $rows = array_merge($purchases, $returns);
    }

    // Sort by date
    usort($rows, function($a,$b){ return strtotime($a['created_at']) - strtotime($b['created_at']); });
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Ledger | AT Optical ERP</title>
<style>
body{font-family:Arial,Helvetica,sans-serif;background:#f4f6f8;margin:0}
.topbar{background:#0078d7;color:#fff;padding:12px 20px;display:flex;justify-content:space-between;align-items:center}
.container{max-width:1000px;margin:20px auto;background:#fff;padding:20px;border-radius:8px}
h2{color:#0078d7;margin-top:0}
form{margin-bottom:20px}
select,input{padding:6px;border:1px solid #ccc;border-radius:4px}
button{padding:6px 12px;background:#0078d7;color:#fff;border:none;border-radius:4px;cursor:pointer}
button:hover{background:#005bb5}
table{width:100%;border-collapse:collapse;margin-top:20px}
th,td{border:1px solid #ddd;padding:8px;text-align:center}
th{background:#0078d7;color:#fff}
.total-box{margin-top:20px;background:#eef4ff;padding:15px;border-radius:6px;text-align:right}
.total-box strong{color:#0078d7}
nav a { color: white; text-decoration: none; margin-right: 15px; font-weight: bold; }
nav a:hover { text-decoration: underline; }
.print-btn{margin-bottom:10px;}

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


@media print {
  body { background: #fff; }
  .topbar, form, button[name="add_total_payment"], nav, .print-btn { display: none !important; }
  .container { box-shadow: none; }
}
</style>
<script>
function showNames() {
    var type = document.getElementById('type').value;
    var cDiv = document.getElementById('customer_div');
    var sDiv = document.getElementById('supplier_div');
    cDiv.style.display = sDiv.style.display = 'none';
    cDiv.querySelector('select').disabled = true;
    sDiv.querySelector('select').disabled = true;
    if(type==='customer') { cDiv.style.display='inline'; cDiv.querySelector('select').disabled=false; }
    if(type==='supplier') { sDiv.style.display='inline'; sDiv.querySelector('select').disabled=false; }
}
window.onload = showNames;
function printLedger(){ window.print(); }
</script>
</head>
<body>
<div class="topbar">
  <div><strong>AT Optical ERP</strong> | Ledger</div>
    <nav>
  <a href="dashboard.php">Dashboard</a>
  <a href="glass.php" style="text-decoration:underline;">Glasses</a>
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

<div class="container" id="ledger_area">
  <h2>Ledger Report</h2>
  <?= $msg ?>

  <form method="POST">
    <label><strong>Type:</strong></label>
    <select name="type" id="type" onchange="showNames()" required>
      <option value="">Select Type</option>
      <option value="customer" <?= ($type==='customer')?'selected':'' ?>>Customer</option>
      <option value="supplier" <?= ($type==='supplier')?'selected':'' ?>>Supplier</option>
    </select>

    <div id="customer_div" style="display:none;">
      <label><strong>Customer:</strong></label>
      <select name="person_id">
        <option value="">Select Customer</option>
        <?php foreach($customers as $c): ?>
          <option value="<?= $c['id'] ?>" <?= ($person_id==$c['id'])?'selected':'' ?>><?= htmlspecialchars($c['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div id="supplier_div" style="display:none;">
      <label><strong>Supplier:</strong></label>
      <select name="person_id">
        <option value="">Select Supplier</option>
        <?php foreach($suppliers as $s): ?>
          <option value="<?= $s['id'] ?>" <?= ($person_id==$s['id'])?'selected':'' ?>><?= htmlspecialchars($s['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <button type="submit" name="view">View Report</button>
  </form>

  <?php if($rows): ?>
  <h3><?= ucfirst($type) ?>: <?= htmlspecialchars($person_name) ?></h3>
  <div style="display:flex; gap:10px; margin-bottom:10px;">
  <button class="print-btn" onclick="printLedger()">🖨️ Print Report</button>

  <form method="POST" style="display:inline;">
    <input type="hidden" name="type" value="<?= htmlspecialchars($type) ?>">
    <input type="hidden" name="person_id" value="<?= htmlspecialchars($person_id) ?>">
    <button type="submit" name="view_payments" style="background:#28a745;">💳 Payment History</button>
  </form>
</div>

  <table>
    <tr>
      <th>#</th>
      <th>Type</th>
      <th>Invoice No</th>
      <th>Total</th>
      <th>Paid</th>
      <th>Remaining</th>
      <th>Date</th>
    </tr>
    <?php
    $count = 1;
    $sum_total = $sum_paid = $sum_remain = 0;
    foreach ($rows as $r):
      $sum_total += $r['total_amount'];
      $sum_paid += $r['paid_amount'];
      $sum_remain += $r['remaining_amount'];
    ?>
    <tr>
      <td><?= $count++ ?></td>
      <td><?= htmlspecialchars($r['type']) ?></td>
      <td><?= htmlspecialchars($r['code']) ?></td>
      <td><?= number_format($r['total_amount'],2) ?></td>
      <td><?= number_format($r['paid_amount'],2) ?></td>
      <td><?= number_format($r['remaining_amount'],2) ?></td>
      <td><?= htmlspecialchars($r['created_at']) ?></td>
    </tr>
    <?php endforeach; ?>
  </table>

  <div class="total-box">
    <p><strong>Total:</strong> Rs. <?= number_format($sum_total,2) ?></p>
    <p><strong>Paid:</strong> Rs. <?= number_format($sum_paid,2) ?></p>
    <p><strong>Remaining:</strong> Rs. <?= number_format($sum_remain,2) ?></p>

    <form method="POST">
      <input type="hidden" name="type" value="<?= htmlspecialchars($type) ?>">
      <input type="hidden" name="person_id" value="<?= htmlspecialchars($person_id) ?>">
      <label><strong>Add Payment:</strong></label>
      <input type="number" step="0.01" name="total_payment" placeholder="Enter Amount" required>
      <button type="submit" name="add_total_payment">Pay</button>
    </form>
  </div>
  <?php elseif(isset($_POST['view'])): ?>
    <p style="color:red;">No transactions found for this <?= htmlspecialchars($type) ?>.</p>
  <?php endif; ?>
</div>
<?php if($payment_rows): ?>
<h3>Payment History for <?= htmlspecialchars($person_name) ?></h3>
<table>
  <tr>
    <th>#</th>
    <th>Type</th>
    <th>Amount</th>
    <th>Date</th>
  </tr>
  <?php 
  $count = 1; 
  foreach($payment_rows as $p): ?>
  <tr>
    <td><?= $count++ ?></td>
    <td><?= htmlspecialchars($p['type']) ?></td>
    <td><?= number_format($p['amount'],2) ?></td>
    <td><?= htmlspecialchars($p['created_at']) ?></td>
  </tr>
  <?php endforeach; ?>
</table>
<?php elseif(isset($_POST['view_payments'])): ?>
  <p style="color:red;">No payments found for this <?= htmlspecialchars($type) ?>.</p>
<?php endif; ?>


</body>
</html>
