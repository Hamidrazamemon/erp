<?php
require 'includes/session.php';
require 'includes/db.php';

$report_type = $_POST['report_type'] ?? '';
$duration = $_POST['duration'] ?? '';
$from_date = $_POST['from_date'] ?? '';
$to_date = $_POST['to_date'] ?? '';

if (!$report_type || !$duration) {
    header("Location: report.php");
    exit;
}

$title = ucfirst($report_type) . " Report";

// --- DATE RANGE HANDLING ---
$today = date('Y-m-d');
if ($duration === 'daily') {
    $start = $today . " 00:00:00";
    $end   = $today . " 23:59:59";
} elseif ($duration === 'monthly') {
    $start = date('Y-m-01 00:00:00');
    $end   = date('Y-m-t 23:59:59');
} elseif ($duration === 'annual') {
    $start = date('Y-01-01 00:00:00');
    $end   = date('Y-12-31 23:59:59');
} elseif ($duration === 'custom' && $from_date && $to_date) {
    $start = $from_date . " 00:00:00";
    $end   = $to_date . " 23:59:59";
} else {
    $start = "1970-01-01 00:00:00";
    $end   = date('Y-m-d H:i:s');
}

// --- FETCH DATA ---
if ($report_type === 'sales') {
    $query = $pdo->prepare("
        SELECT s.sale_no AS invoice_no, c.name AS party_name, s.total_amount, s.paid_amount, s.remaining_amount, s.created_at
        FROM sales s
        LEFT JOIN customers c ON s.customer_id = c.id
        WHERE s.created_at BETWEEN ? AND ?
        ORDER BY s.created_at DESC
    ");
} else {
    $query = $pdo->prepare("
        SELECT p.purchase_no AS invoice_no, sp.name AS party_name, p.total_amount, p.paid_amount, p.remaining_amount, p.created_at
        FROM purchases p
        LEFT JOIN suppliers sp ON p.supplier_id = sp.id
        WHERE p.created_at BETWEEN ? AND ?
        ORDER BY p.created_at DESC
    ");
}

$query->execute([$start, $end]);
$rows = $query->fetchAll(PDO::FETCH_ASSOC);

$total_amount = array_sum(array_column($rows, 'total_amount'));
$total_paid = array_sum(array_column($rows, 'paid_amount'));
$total_remaining = array_sum(array_column($rows, 'remaining_amount'));
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?= $title ?> | AT Optical ERP</title>
<style>
body { background:#f4f6f8; font-family:Arial,Helvetica,sans-serif; margin:0; }
.topbar {
  background:#0078d7; color:#fff; padding:12px 20px;
  display:flex; justify-content:space-between; align-items:center;
}
.topbar nav a {
  color:#fff; text-decoration:none; margin-left:14px; font-weight:bold;
}
.topbar nav a:hover { text-decoration:underline; }

.container {
  max-width:1100px; margin:25px auto; background:#fff; padding:25px;
  border-radius:10px; box-shadow:0 0 10px rgba(0,0,0,0.1);
}
h2 {
  color:#0078d7; text-align:center; margin-bottom:15px;
}
.summary {
  text-align:center; margin-bottom:20px;
  background:#f8f9fa; padding:10px; border-radius:6px; border:1px solid #ddd;
}
.summary strong { color:#0078d7; }

table {
  width:100%; border-collapse:collapse; margin-top:10px;
}
th, td {
  border:1px solid #ddd; padding:8px; text-align:center;
}
th {
  background:#0078d7; color:#fff;
  text-transform:uppercase; font-size:13px;
}
tr:nth-child(even) { background:#f9f9f9; }

tfoot td {
  font-weight:bold;
  background:#eef2f7;
}
.actions {
  text-align:right; margin-bottom:15px;
}
button {
  background:#0078d7; color:#fff; border:none;
  padding:8px 14px; border-radius:5px; cursor:pointer;
  font-weight:bold; margin-left:10px;
}
button:hover { background:#005fa3; }
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
  .topbar, .actions { display:none; }
  body { background:#fff; }
  .container { box-shadow:none; border:none; margin:0; padding:0; }
}
</style>
<script>
function exportTableToExcel(tableID, filename = ''){
    var downloadLink;
    var dataType = 'application/vnd.ms-excel';
    var tableSelect = document.getElementById(tableID);
    var tableHTML = tableSelect.outerHTML.replace(/ /g, '%20');
    filename = filename?filename+'.xls':'report.xls';
    downloadLink = document.createElement("a");
    document.body.appendChild(downloadLink);
    if(navigator.msSaveOrOpenBlob){
        var blob = new Blob(['\ufeff', tableHTML], { type: dataType });
        navigator.msSaveOrOpenBlob(blob, filename);
    } else {
        downloadLink.href = 'data:' + dataType + ', ' + tableHTML;
        downloadLink.download = filename;
        downloadLink.click();
    }
}
</script>
</head>
<body>

<div class="topbar">
  <div><strong>AT Optical ERP</strong> | <?= $title ?></div>
  <nav>
    <a href="report.php">Back</a>
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
      <a href="report.php" style="text-decoration:underline;">Main Report</a>
      <a href="rate_list.php">Rate List</a>
       <a href="ledger.php">Ledger</a>
  <a href="stock.php">Stock</a>
  <a href="balance.php" >Balance</a>

    </div>
  </div>

  <a href="logout.php" style="color:#ffdddd;">Logout</a>
</nav>
</div>

<div class="container">
  <h2><?= $title ?></h2>

  <div class="summary">
    <p>
      <strong>Duration:</strong> <?= ucfirst($duration) ?> |
      <strong>From:</strong> <?= date('d-M-Y', strtotime($start)) ?> |
      <strong>To:</strong> <?= date('d-M-Y', strtotime($end)) ?>
    </p>
  </div>

  <div class="actions">
    <button onclick="window.print()">🖨 Print</button>
    <button onclick="exportTableToExcel('reportTable', '<?= strtolower($report_type) ?>_report')">📊 Export Excel</button>
  </div>

  <?php if($rows): ?>
  <table id="reportTable">
    <thead>
      <tr>
        <th>#</th>
        <th>Invoice No</th>
        <th><?= $report_type==='sales' ? 'Customer' : 'Supplier' ?></th>
        <th>Total</th>
        <th>Paid</th>
        <th>Remaining</th>
        <th>Date</th>
      </tr>
    </thead>
    <tbody>
      <?php $i=1; foreach($rows as $r): ?>
      <tr>
        <td><?= $i++ ?></td>
        <td><?= htmlspecialchars($r['invoice_no']) ?></td>
        <td><?= htmlspecialchars($r['party_name']) ?></td>
        <td><?= number_format($r['total_amount'],2) ?></td>
        <td><?= number_format($r['paid_amount'],2) ?></td>
        <td><?= number_format($r['remaining_amount'],2) ?></td>
        <td><?= date('d-M-Y', strtotime($r['created_at'])) ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
    <tfoot>
      <tr>
        <td colspan="3" style="text-align:right;"><strong>Totals:</strong></td>
        <td><?= number_format($total_amount,2) ?></td>
        <td><?= number_format($total_paid,2) ?></td>
        <td><?= number_format($total_remaining,2) ?></td>
        <td></td>
      </tr>
    </tfoot>
  </table>
  <?php else: ?>
    <p style="color:red;text-align:center;">No records found for the selected range.</p>
  <?php endif; ?>
</div>
</body>
</html>
