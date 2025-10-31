<?php
session_start();
require 'includes/db.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}

// --- Totals ---
$total_purchase = $pdo->query("SELECT IFNULL(SUM(total_amount),0) AS total FROM purchases")->fetch()['total'];
$total_sales = $pdo->query("SELECT IFNULL(SUM(total_amount),0) AS total FROM sales")->fetch()['total'];
$total_suppliers = $pdo->query("SELECT COUNT(*) AS total FROM suppliers")->fetch()['total'];
$total_customers = $pdo->query("SELECT COUNT(*) AS total FROM customers")->fetch()['total'];



// --- Monthly Graph Data ---
function lastMonths($n = 6) {
    $months = [];
    for ($i = $n - 1; $i >= 0; $i--) {
        $ts = strtotime("-$i months");
        $months[] = [
            'label' => date('M', $ts),
            'start' => date('Y-m-01 00:00:00', $ts),
            'end'   => date('Y-m-t 23:59:59', $ts)
        ];
    }
    return $months;
}

$months = lastMonths();
$labels = [];
$purchases = [];
$sales = [];

$stmtP = $pdo->prepare("SELECT IFNULL(SUM(total_amount),0) FROM purchases WHERE created_at BETWEEN ? AND ?");
$stmtS = $pdo->prepare("SELECT IFNULL(SUM(total_amount),0) FROM sales WHERE created_at BETWEEN ? AND ?");

foreach ($months as $m) {
    $labels[] = $m['label'];
    $stmtP->execute([$m['start'], $m['end']]);
    $stmtS->execute([$m['start'], $m['end']]);
    $purchases[] = (float)$stmtP->fetchColumn();
    $sales[] = (float)$stmtS->fetchColumn();
}

// --- Top Customers ---
$top_customers = $pdo->query("
    SELECT c.name, SUM(s.total_amount) AS total
    FROM sales s
    JOIN customers c ON s.customer_id = c.id
    GROUP BY c.name ORDER BY total DESC LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

// --- Top Suppliers ---
$top_suppliers = $pdo->query("
    SELECT sp.name, SUM(p.total_amount) AS total
    FROM purchases p
    JOIN suppliers sp ON p.supplier_id = sp.id
    GROUP BY sp.name ORDER BY total DESC LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

// --- Recent Transactions ---
$recent_sales = $pdo->query("SELECT sale_no, total_amount, created_at FROM sales ORDER BY id DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
$recent_purchases = $pdo->query("SELECT purchase_no, total_amount, created_at FROM purchases ORDER BY id DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Dashboard | AT Optical ERP</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
body { background:#f4f6f8; font-family:Arial,Helvetica,sans-serif; margin:0; }
.topbar {
  background:#0078d7; color:#fff; padding:12px 20px;
  display:flex; justify-content:space-between; align-items:center;
}
.topbar nav a { color:#fff; text-decoration:none; margin-left:14px; font-weight:bold; }
.topbar nav a:hover { text-decoration:underline; }
.container {
  max-width:1100px; margin:20px auto; background:#fff; padding:20px;
  border-radius:8px; box-shadow:0 0 8px rgba(0,0,0,0.1);
}
h2 { color:#0078d7; margin-top:0; text-align:center; }

.cards {
  display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
  gap:20px; margin-top:20px;
}
.card {
  background:#fff; border:1px solid #ddd; border-radius:10px;
  box-shadow:0 2px 6px rgba(0,0,0,0.05);
  text-align:center; padding:20px; transition:transform .2s ease;
}
.card:hover { transform:translateY(-3px); }
.card h3 { margin:0; color:#0078d7; font-size:17px; }
.card p { font-size:22px; font-weight:bold; color:#333; margin:10px 0 0; }

.chartBox { margin-top:30px; text-align:center; }
canvas { max-width:2000px; height:500px !important; margin:auto; }



.extra-section {
  margin-top:40px; display:grid;
  grid-template-columns:1fr 1fr; gap:20px;
}
.extra-section div {
  background:#f9fafb; border:1px solid #ddd; border-radius:8px;
  padding:15px; box-shadow:0 2px 5px rgba(0,0,0,0.05);
}
.extra-section h4 {
  margin:0 0 10px; color:#0078d7; border-bottom:1px solid #ccc; padding-bottom:5px;
}
table { width:100%; border-collapse:collapse; font-size:14px; }
th,td { padding:6px; border-bottom:1px solid #eee; text-align:left; }
th { color:#0078d7; }
tr:hover { background:#f1f5f9; }
</style>
</head>
<body>
<div class="topbar">
  <div><strong>AT Optical ERP</strong> | Dashboard</div>
  <nav>
    <a href="dashboard.php" style="text-decoration:underline;">Dashboard</a>
    <a href="glass.php">Glasses</a>
    <a href="suppliers.php">Suppliers</a>
    <a href="customers.php">Customers</a>
    <a href="purchase.php">Purchase</a>
    <a href="sales.php">Sales</a>
    <a href="ledger.php">Ledger</a>
    <a href="stock.php">Stock</a>
    <a href="balace.php">Balance</a>
    <a href="report.php">Report</a>
    <a href="rate_list.php">Rate List</a>
    <a href="logout.php" style="color:#ffdddd;">Logout</a>
  </nav>
</div>

<div class="container">
  <h2>Welcome to Dashboard</h2>

  <div class="cards">
    <div class="card"><h3>Total Purchase</h3><p>Rs <?= number_format($total_purchase,2) ?></p></div>
    <div class="card"><h3>Total Sales</h3><p>Rs <?= number_format($total_sales,2) ?></p></div>
    <div class="card"><h3>Total Suppliers</h3><p><?= $total_suppliers ?></p></div>
    <div class="card"><h3>Total Customers</h3><p><?= $total_customers ?></p></div>
  </div>

  <div class="chartBox">
    <canvas id="monthlyChart"></canvas>
  </div>



  <div class="extra-section">
    <div>
      <h4>Top Customers</h4>
      <table>
        <tr><th>Name</th><th>Total Purchase</th></tr>
        <?php foreach($top_customers as $c): ?>
          <tr><td><?= htmlspecialchars($c['name']) ?></td><td>Rs <?= number_format($c['total'],2) ?></td></tr>
        <?php endforeach; ?>
      </table>
    </div>

    <div>
      <h4>Top Suppliers</h4>
      <table>
        <tr><th>Name</th><th>Total Supply</th></tr>
        <?php foreach($top_suppliers as $s): ?>
          <tr><td><?= htmlspecialchars($s['name']) ?></td><td>Rs <?= number_format($s['total'],2) ?></td></tr>
        <?php endforeach; ?>
      </table>
    </div>

    <div>
      <h4>Recent Sales</h4>
      <table>
        <tr><th>Invoice</th><th>Total</th><th>Date</th></tr>
        <?php foreach($recent_sales as $s): ?>
          <tr><td><?= htmlspecialchars($s['sale_no']) ?></td><td>Rs <?= number_format($s['total_amount'],2) ?></td><td><?= htmlspecialchars($s['created_at']) ?></td></tr>
        <?php endforeach; ?>
      </table>
    </div>

    <div>
      <h4>Recent Purchases</h4>
      <table>
        <tr><th>Invoice</th><th>Total</th><th>Date</th></tr>
        <?php foreach($recent_purchases as $p): ?>
          <tr><td><?= htmlspecialchars($p['purchase_no']) ?></td><td>Rs <?= number_format($p['total_amount'],2) ?></td><td><?= htmlspecialchars($p['created_at']) ?></td></tr>
        <?php endforeach; ?>
      </table>
    </div>
  </div>
</div>

<script>
const ctx = document.getElementById('monthlyChart').getContext('2d');
new Chart(ctx, {
  type: 'bar',
  data: {
    labels: <?= json_encode($labels) ?>,
    datasets: [
      { label: 'Sales', data: <?= json_encode($sales) ?>, backgroundColor: 'rgba(34,197,94,0.6)' },
      { label: 'Purchases', data: <?= json_encode($purchases) ?>, backgroundColor: 'rgba(59,130,246,0.6)' }
    ]
  },
  options: {
    responsive: true,
    plugins: { legend: { position: 'top' } },
    scales: { y: { beginAtZero:true } }
  }
});
</script>
</body>
</html>
