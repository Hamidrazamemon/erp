<?php
session_start();
require 'includes/db.php';
require_once __DIR__ . '/vendor/autoload.php'; // for mpdf if needed

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}

$variety   = $_POST['variety'] ?? '';
$from_sph  = (float)($_POST['from_sph'] ?? 0);
$to_sph    = (float)($_POST['to_sph'] ?? 0);
$from_cyl  = (float)($_POST['from_cyl'] ?? 0);
$to_cyl    = (float)($_POST['to_cyl'] ?? 0);

// Generate SPH / CYL ranges
function generateRange($start, $end, $step = 0.25) {
    $vals = [];
    if ($start <= $end) {
        for ($i = $start; $i <= $end; $i += $step) $vals[] = number_format($i, 2);
    } else {
        for ($i = $start; $i >= $end; $i -= $step) $vals[] = number_format($i, 2);
    }
    return $vals;
}

$sph_vals = generateRange($from_sph, $to_sph);
$cyl_vals = generateRange($from_cyl, $to_cyl);

// Fetch all relevant stock data
$query = "
SELECT 
    pi.spherical,
    pi.cylinder,
    COALESCE(SUM(pi.quantity), 0)
    - COALESCE((
        SELECT SUM(si.quantity)
        FROM sale_items si
        WHERE si.item_name = pi.item_name
          AND si.spherical = pi.spherical
          AND si.cylinder = pi.cylinder
    ), 0)
    + COALESCE((
        SELECT SUM(sri.quantity)
        FROM sale_return_items sri
        JOIN sale_items si2 ON sri.sale_item_id = si2.id
        WHERE si2.item_name = pi.item_name
          AND si2.spherical = pi.spherical
          AND si2.cylinder = pi.cylinder
    ), 0)
    - COALESCE((
        SELECT SUM(pri.quantity)
        FROM purchase_return_items pri
        JOIN purchase_items pi2 ON pri.purchase_item_id = pi2.id
        WHERE pi2.item_name = pi.item_name
          AND pi2.spherical = pi.spherical
          AND pi2.cylinder = pi.cylinder
    ), 0) AS available_stock
FROM purchase_items pi
WHERE pi.item_name = :variety
  AND pi.spherical BETWEEN :from_sph AND :to_sph
  AND pi.cylinder BETWEEN :from_cyl AND :to_cyl
GROUP BY pi.spherical, pi.cylinder
ORDER BY pi.spherical, pi.cylinder
";

$stmt = $pdo->prepare($query);
$stmt->execute([
    ':variety'   => $variety,
    ':from_sph'  => $from_sph,
    ':to_sph'    => $to_sph,
    ':from_cyl'  => $from_cyl,
    ':to_cyl'    => $to_cyl
]);

$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Make lookup map
$stockMap = [];
foreach ($data as $d) {
    $stockMap[number_format($d['spherical'], 2) . '_' . number_format($d['cylinder'], 2)] = $d['available_stock'];
}

// Excel download
if (isset($_POST['download_excel'])) {
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=stock_report.xls");
    echo "SPH/CYL\t" . implode("\t", $cyl_vals) . "\n";
    foreach ($sph_vals as $s) {
        echo $s;
        foreach ($cyl_vals as $c) {
            $key = number_format($s, 2) . '_' . number_format($c, 2);
            $qty = $stockMap[$key] ?? 0;
            echo "\t$qty";
        }
        echo "\n";
    }
    exit;
}
?>

<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Stock Report | AT Optical ERP</title>
<style>
body{background:#eef2f5;font-family:"Segoe UI", Arial,sans-serif;margin:0;color:#222}
.topbar{background:#0078d7;color:#fff;padding:12px 20px;display:flex;justify-content:space-between;align-items:center;font-size:14px}
.topbar nav a{color:#fff;text-decoration:none;margin-left:14px;font-weight:bold}
.topbar nav a:hover{text-decoration:underline}
.container{max-width:100%;margin:25px auto;background:#fff;padding:25px 30px;border-radius:10px;box-shadow:0 4px 12px rgba(0,0,0,.1)}
h2{text-align:center;color:#0078d7;font-size:22px;margin-bottom:10px;font-weight:600;letter-spacing:0.5px}
.table-container{width:100%;overflow-x:auto}
table{width:100%;border-collapse:collapse;font-size:12px;text-align:center;table-layout:fixed;word-wrap:break-word}
th,td{border:1px solid blue;white-space:nowrap}
th{background:#0078d7;color:#fff;font-weight:600}
tr:nth-child(even){background:#f9fafc}
td.zero{color:#999}
.buttons{display:flex;justify-content:center;align-items:center;gap:12px;margin-bottom:25px;flex-wrap:wrap}
button,.btn{background:#0078d7;color:#fff;border:none;border-radius:6px;padding:10px 16px;cursor:pointer;font-weight:500;font-size:13px;transition:0.3s;text-decoration:none}
button:hover,.btn:hover{background:#005bb5}
.btn-green{background:#28a745}
.btn-green:hover{background:#1e7e34}

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


@media print{body{background:#fff;color:#000;font-size:10px}.topbar,.buttons{display:none}.container{box-shadow:none;margin:0;padding:0;width:100%}.table-container{overflow-x:visible}table{border-collapse:collapse;table-layout:auto;width:100%;font-size:10px}th,td{border:1px solid #000;padding:2px 6px;text-align:center;vertical-align:middle;white-space:normal}th{background-color:#0078d7 !important;color:#fff !important;font-weight:600}tr:nth-child(even){background-color:#f9fafc}h2{font-size:16px;margin-bottom:6px;text-align:center;color:#000}}
</style>
</head>
<body>
<div class="topbar">
  <div><strong>AT Optical ERP</strong> | Stock Report</div>
  <nav>
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
  <a href="stock.php" style="text-decoration:underline;">Stock</a>
  <a href="balance.php">Balance</a>

    </div>
  </div>

  <a href="logout.php" style="color:#ffdddd;">Logout</a>
</nav>

</div>

<div class="container">
<h2>Stock Report for <?= htmlspecialchars($variety) ?></h2>

<div class="buttons">
  <a href="stock.php" class="btn">← Back</a>
  <button onclick="window.print()">🖨️ Print</button>
  <form method="post" style="display:inline;">
    <?php foreach ($_POST as $k=>$v): ?>
      <input type="hidden" name="<?= htmlspecialchars($k) ?>" value="<?= htmlspecialchars($v) ?>">
    <?php endforeach; ?>
    <button type="submit" name="download_excel" class="btn-green">📊 Excel</button>
  </form>
</div>

<div class="table-container">
<table>
<thead>
<tr>
<th>SPH / CYL</th>
<?php foreach ($cyl_vals as $c): ?>
<th><?= htmlspecialchars($c) ?></th>
<?php endforeach; ?>
</tr>
</thead>
<tbody>
<?php foreach ($sph_vals as $s): ?>
<tr>
<th><?= htmlspecialchars($s) ?></th>
<?php foreach ($cyl_vals as $c):
    $key = number_format($s,2).'_'.number_format($c,2);
    $qty = $stockMap[$key] ?? 0;
?>
<td class="<?= $qty==0?'zero':'' ?>"><?= $qty ?></td>
<?php endforeach; ?>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
</div>
</body>
</html>
