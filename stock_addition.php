<?php
session_start();
require 'includes/db.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}

$variety   = $_POST['variety'] ?? '';
$from_sph  = (float)($_POST['from_sph'] ?? 0);
$to_sph    = (float)($_POST['to_sph'] ?? 0);
$from_cyl  = (float)($_POST['from_cyl'] ?? 0);
$to_cyl    = (float)($_POST['to_cyl'] ?? 0);
$from_add  = $_POST['from_addition'] ?? '';
$to_add    = $_POST['to_addition'] ?? '';

// SQL query
$query = "
SELECT 
    pi.item_name AS variety,
    pi.spherical AS sph,
    pi.cylinder AS cyl,
    pi.addition,
    pi.axis,
    COALESCE(SUM(pi.quantity), 0) -
    COALESCE((
        SELECT SUM(si.quantity)
        FROM sale_items si
        WHERE si.item_name COLLATE utf8mb4_unicode_ci = pi.item_name COLLATE utf8mb4_unicode_ci
        AND si.spherical = pi.spherical
        AND si.cylinder = pi.cylinder
    ), 0) AS available_stock
FROM purchase_items pi
WHERE pi.item_name = :variety
  AND pi.spherical BETWEEN :from_sph AND :to_sph
  AND pi.cylinder BETWEEN :from_cyl AND :to_cyl
GROUP BY pi.spherical, pi.cylinder
ORDER BY pi.spherical ASC, pi.cylinder ASC
";

$stmt = $pdo->prepare($query);
$stmt->execute([
    ':variety' => $variety,
    ':from_sph' => $from_sph,
    ':to_sph' => $to_sph,
    ':from_cyl' => $from_cyl,
    ':to_cyl' => $to_cyl
]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ✅ Excel Download (safe)
if (isset($_POST['download_excel'])) {
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=stock_report.xls");
    echo "<table border='1'>";
    echo "<tr>
            <th>SPH</th>
            <th>CYL</th>
            <th>Addition</th>
            <th>Axis</th>
            <th>Available Stock</th>
          </tr>";
    foreach ($rows as $r) {
        echo "<tr>";
        echo "<td>{$r['sph']}</td>";
        echo "<td>{$r['cyl']}</td>";
        echo "<td>" . ($r['addition'] ?? '-') . "</td>";
        echo "<td>" . ($r['axis'] ?? '-') . "</td>";
        echo "<td>{$r['available_stock']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    exit;
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Stock Report | AT Optical ERP</title>
<style>
body { background:#eef2f5; font-family:Arial,Helvetica,sans-serif; margin:0; }
.topbar {
  background:#0078d7; color:#fff; padding:12px 20px;
  display:flex; justify-content:space-between; align-items:center;
}
.topbar nav a { color:#fff; text-decoration:none; margin-left:14px; font-weight:bold; }
.topbar nav a:hover { text-decoration:underline; }
.container {
  max-width:1000px; margin:30px auto; background:#fff;
  padding:25px 30px; border-radius:10px; box-shadow:0 2px 10px rgba(0,0,0,.1);
}
h2 { color:#0078d7; margin-bottom:10px; }
.report-header {
  background:#f4f8ff; border:1px solid #d0e4ff; border-radius:6px;
  padding:10px 15px; margin-bottom:20px;
}
.report-header strong { color:#004a99; }
table { width:100%; border-collapse:collapse; margin-top:10px; }
th, td { border:1px solid #ddd; padding:8px; text-align:center; }
th { background:#0078d7; color:#fff; }
tr:nth-child(even){background:#f9f9f9}
td.zero { color:#999; }
.btn-back, .btn-action {
  display:inline-block; background:#0078d7; color:#fff; text-decoration:none;
  padding:10px 18px; border-radius:6px; margin-top:15px; transition:0.3s;
}
.btn-back:hover, .btn-action:hover { background:#005bb5; }

/* ✅ Print Styling (portrait) */
@media print {
  @page { size: A4 portrait; margin:10mm; }
  body { background:#fff; color:#000; font-size:10px; }
  .topbar, .btn-back, .btn-action { display:none; }
  .container { box-shadow:none; margin:0; padding:0; width:100%; }
  table { border-collapse:collapse; width:100%; font-size:10px; }
  th, td { border:1px solid #000; padding:4px 6px; text-align:center; vertical-align:middle; white-space:normal; }
  th { background-color:#0078d7 !important; color:#fff !important; }
  tr { page-break-inside:avoid; }
}
</style>
</head>
<body>
<div class="topbar">
  <div><strong>AT Optical ERP</strong> | Stock Report</div>
  <nav>
    <a href="dashboard.php">Dashboard</a>
    <a href="glass.php">Glasses</a>
    <a href="suppliers.php">Suppliers</a>
    <a href="customers.php">Customers</a>
    <a href="purchase.php">Purchase</a>
    <a href="sales.php">Sales</a>
    <a href="ledger.php">Ledger</a>
    <a href="stock.php" style="text-decoration:underline;">Stock</a>
    <a href="logout.php" style="color:#ffdddd;">Logout</a>
  </nav>
</div>

<div class="container">
  <h2>Stock Report</h2>
  <div class="report-header">
    <strong>Variety:</strong> <?= htmlspecialchars($variety) ?> &nbsp; | &nbsp;
    <strong>SPH:</strong> <?= htmlspecialchars($from_sph) ?> to <?= htmlspecialchars($to_sph) ?> &nbsp; | &nbsp;
    <strong>CYL:</strong> <?= htmlspecialchars($from_cyl) ?> to <?= htmlspecialchars($to_cyl) ?> 
    <?php if (!empty($from_add) && !empty($to_add)): ?>
      &nbsp; | &nbsp; <strong>Addition:</strong> <?= htmlspecialchars($from_add) ?> to <?= htmlspecialchars($to_add) ?>
    <?php endif; ?>
  </div>

  <div style="margin-bottom:15px;">
    <button onclick="window.print()" class="btn-action">🖨️ Print</button>
    <form method="post" style="display:inline;">
      <?php foreach ($_POST as $k => $v): ?>
        <input type="hidden" name="<?= htmlspecialchars($k) ?>" value="<?= htmlspecialchars($v) ?>">
      <?php endforeach; ?>
      <button type="submit" name="download_excel" class="btn-action">📊 Excel</button>
    </form>
    <a href="stock.php" class="btn-back">← Back to Filter</a>
  </div>

  <?php if (count($rows) > 0): ?>
  <table>
    <thead>
      <tr>
        <th>SPH</th>
        <th>CYL</th>
        <th>Addition</th>
        <th>Axis</th>
        <th>Available Stock</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($rows as $r): ?>
      <tr>
        <td><?= htmlspecialchars($r['sph']) ?></td>
        <td><?= htmlspecialchars($r['cyl']) ?></td>
        <td><?= htmlspecialchars($r['addition'] ?? '-') ?></td>
        <td><?= htmlspecialchars($r['axis'] ?? '-') ?></td>
        <td><strong><?= $r['available_stock'] ?></strong></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <?php else: ?>
    <div style="color:red;text-align:center;font-weight:bold;margin-top:20px;">
      ❌ Is range me koi record nahi mila.
    </div>
  <?php endif; ?>
</div>
</body>
</html>
