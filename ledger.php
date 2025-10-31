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
        $col = 'sale_no';
        $col_person = 'customer_id';
    } else {
        $tbl = 'purchases';
        $col = 'purchase_no';
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
        $query = $pdo->prepare("SELECT $col, remaining_amount, paid_amount FROM $tbl WHERE $col_person=? AND remaining_amount>0 ORDER BY created_at ASC");
        $query->execute([$person_id]);
        $bills = $query->fetchAll(PDO::FETCH_ASSOC);

        $remaining_payment = $amount;

        foreach ($bills as $b) {
            if ($remaining_payment <= 0) break;
            $apply = min($b['remaining_amount'], $remaining_payment);
            $new_remaining = $b['remaining_amount'] - $apply;
            $new_paid = $b['paid_amount'] + $apply;
            $update = $pdo->prepare("UPDATE $tbl SET remaining_amount=?, paid_amount=? WHERE $col=?");
            $update->execute([$new_remaining, $new_paid, $b[$col]]);
            $remaining_payment -= $apply;
        }

        $pdo->commit();
        $msg = "<p style='color:green;'>Payment of Rs. $amount successfully adjusted to outstanding bills.</p>";
    }
}

/* ------------------- FETCH DROPDOWN OPTIONS ------------------- */
$customers = $pdo->query("SELECT id, name FROM customers ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
$suppliers = $pdo->query("SELECT id, name FROM suppliers ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

/* ------------------- VIEW LEDGER ------------------- */
if (isset($_POST['view']) && $type && $person_id) {
    if ($type === 'customer') {
        $stmt = $pdo->prepare("SELECT name FROM customers WHERE id=?");
        $stmt->execute([$person_id]);
        $person_name = $stmt->fetchColumn();

        $query = $pdo->prepare("SELECT 'Sale' AS type, sale_no AS code, total_amount, paid_amount, remaining_amount, created_at 
                                FROM sales WHERE customer_id=? ORDER BY created_at ASC");
    } else {
        $stmt = $pdo->prepare("SELECT name FROM suppliers WHERE id=?");
        $stmt->execute([$person_id]);
        $person_name = $stmt->fetchColumn();

        $query = $pdo->prepare("SELECT 'Purchase' AS type, purchase_no AS code, total_amount, paid_amount, remaining_amount, created_at 
                                FROM purchases WHERE supplier_id=? ORDER BY created_at ASC");
    }

    $query->execute([$person_id]);
    $rows = $query->fetchAll(PDO::FETCH_ASSOC);
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

@media print {
  body { background: #fff; }
  .topbar, form, button[name="add_total_payment"], nav { display: none !important; }
  .print-btn { display: none !important; }
  .container { box-shadow: none; }
}
</style>
<script>
function showNames() {
    var type = document.getElementById('type').value;
    var cDiv = document.getElementById('customer_div');
    var sDiv = document.getElementById('supplier_div');
    var cSel = cDiv.querySelector('select');
    var sSel = sDiv.querySelector('select');
    if (type === 'customer') {
        cDiv.style.display='inline'; sDiv.style.display='none';
        cSel.disabled=false; sSel.disabled=true;
    } else if (type === 'supplier') {
        sDiv.style.display='inline'; cDiv.style.display='none';
        sSel.disabled=false; cSel.disabled=true;
    } else {
        sDiv.style.display='none'; cDiv.style.display='none';
        sSel.disabled=true; cSel.disabled=true;
    }
}
window.onload = showNames;
function printLedger(){
    window.print();
}
</script>
</head>
<body>
<div class="topbar">
  <div><strong>AT Optical ERP</strong> | Ledger</div>
  <nav>
    <a href="dashboard.php">Dashboard</a>
    <a href="items.php">Items</a>
    <a href="glass.php">Glasses</a>
    <a href="suppliers.php">Suppliers</a>
    <a href="customers.php">Customers</a>
    <a href="purchase.php">Purchase</a>
    <a href="sales.php">Sales</a>
    <a href="ledger.php" style="text-decoration:underline;">Ledger</a>
    <a href="stock.php">Stock</a>
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

    <div id="customer_div" style="display:inline;">
      <label><strong>Customer:</strong></label>
      <select name="person_id">
        <option value="">Select Customer</option>
        <?php foreach($customers as $c): ?>
          <option value="<?= $c['id'] ?>" <?= ($person_id==$c['id'])?'selected':'' ?>><?= htmlspecialchars($c['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div id="supplier_div" style="display:inline;">
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
  <button class="print-btn" onclick="printLedger()">🖨️ Print Report</button>
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
</body>
</html>
