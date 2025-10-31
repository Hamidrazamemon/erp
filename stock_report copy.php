<?php
session_start();
require 'includes/db.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}

$variety   = $_POST['variety'] ?? '';
$from_sph  = floatval($_POST['from_sph'] ?? 0);
$to_sph    = floatval($_POST['to_sph'] ?? 0);
$from_cyl  = floatval($_POST['from_cyl'] ?? 0);
$to_cyl    = floatval($_POST['to_cyl'] ?? 0);

// Agar addition blank ho to default 0.00
if (empty($_POST['from_addition']) && empty($_POST['to_addition'])) {
    $from_add = 0.00;
    $to_add   = 0.00;
} else {
    $from_add = floatval($_POST['from_addition']);
    $to_add   = floatval($_POST['to_addition']);
}

// Fetch stock grouped by Addition, SPH, CYL
$query = "
SELECT 
    pi.addition,
    pi.spherical,
    pi.cylinder,
    COALESCE(SUM(pi.quantity), 0) -
    COALESCE((
        SELECT SUM(si.quantity)
        FROM sale_items si
        WHERE LOWER(si.item_name) = LOWER(pi.item_name)
          AND si.spherical = pi.spherical
          AND si.cylinder = pi.cylinder
          AND (si.addition = pi.addition OR (si.addition IS NULL AND pi.addition IS NULL))
    ), 0) AS available_stock
FROM purchase_items pi
WHERE LOWER(pi.item_name) = LOWER(:variety)
  AND pi.spherical BETWEEN :from_sph AND :to_sph
  AND pi.cylinder BETWEEN :from_cyl AND :to_cyl
  AND pi.addition BETWEEN :from_add AND :to_add
GROUP BY pi.addition, pi.spherical, pi.cylinder
ORDER BY pi.addition ASC, pi.cylinder ASC, pi.spherical ASC
";

$stmt = $pdo->prepare($query);
$stmt->execute([
    ':variety'  => $variety,
    ':from_sph' => $from_sph,
    ':to_sph'   => $to_sph,
    ':from_cyl' => $from_cyl,
    ':to_cyl'   => $to_cyl,
    ':from_add' => $from_add,
    ':to_add'   => $to_add
]);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Map DB results
$grouped = [];
foreach ($data as $row) {
    $add_key = number_format((float)$row['addition'], 2);
    $cyl_key = number_format((float)$row['cylinder'], 2);
    $sph_key = number_format((float)$row['spherical'], 2);
    $grouped[$add_key][$cyl_key][$sph_key] = (int)$row['available_stock'];
}

// Generate full ranges (0.25 step)
function rangeStep($start, $end, $step = 0.25) {
    $arr = [];
    if ($start <= $end) {
        for ($v = $start; $v <= $end + 0.0001; $v += $step) $arr[] = number_format((float)$v, 2);
    } else {
        for ($v = $start; $v >= $end - 0.0001; $v -= $step) $arr[] = number_format((float)$v, 2);
    }
    return $arr;
}

$sph_list = rangeStep($from_sph, $to_sph, 0.25);
$cyl_list = rangeStep($from_cyl, $to_cyl, 0.25);
$add_list = rangeStep($from_add, $to_add, 0.25);
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Stock Report | AT Optical ERP</title>
<style>
body { background:#eef2f5; font-family:Arial,Helvetica,sans-serif; margin:0; }
.topbar { background:#0078d7; color:#fff; padding:12px 20px; display:flex; justify-content:space-between; align-items:center; }
.topbar nav a { color:#fff; text-decoration:none; margin-left:14px; font-weight:bold; }
.topbar nav a:hover { text-decoration:underline; }
.container {
  max-width:1100px; margin:30px auto; background:#fff;
  padding:25px 30px; border-radius:10px; box-shadow:0 2px 10px rgba(0,0,0,.1);
}
h2 { color:#0078d7; margin-bottom:10px; }
.report-header {
  background:#f4f8ff; border:1px solid #d0e4ff; border-radius:6px;
  padding:10px 15px; margin-bottom:25px;
}
.report-header strong { color:#004a99; }
.addition-heading {
  background:#0078d7; color:#fff; padding:10px 15px;
  border-radius:6px; font-size:18px; font-weight:bold; margin-top:30px;
}
table {
  width:100%; border-collapse:collapse; margin-top:10px;
}
th, td {
  border:1px solid #ccc; padding:6px; text-align:center; font-size:13px;
}
th { background:#f1f6ff; color:#004a99; }
tr:nth-child(even){background:#f9f9f9}
td.zero { color:#bbb; }
.page-break { page-break-before: always; }
.btn-back, .btn-print {
  display:inline-block; background:#0078d7; color:#fff; text-decoration:none;
  padding:10px 18px; border-radius:6px; margin-top:25px; transition:0.3s;
}
.btn-back:hover, .btn-print:hover { background:#005bb5; }
.print-buttons {
  display:flex; justify-content:space-between; align-items:center;
  margin-bottom:20px;
}
@media print {
  .topbar, .print-buttons { display:none; }
  body { background:#fff; }
  .container { box-shadow:none; margin:0; padding:0; }
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

  <div class="print-buttons">
    <a href="stock.php" class="btn-back">← Back to Filter</a>
    <a href="#" onclick="window.print();" class="btn-print">🖨️ Print Report</a>
  </div>

  <div class="report-header">
    <?php if ($from_add == 0.00 && $to_add == 0.00): ?>
      <strong>Variety:</strong> <?= htmlspecialchars($variety) ?>
    <?php else: ?>
      <strong>Variety:</strong> <?= htmlspecialchars($variety) ?> &nbsp; | &nbsp;
      <strong>Addition Range:</strong> <?= htmlspecialchars(number_format($from_add,2)) ?> to <?= htmlspecialchars(number_format($to_add,2)) ?>
    <?php endif; ?>
    
  </div>

  <?php if (count($add_list) > 0): ?>
    <?php $first = true; foreach ($add_list as $add): ?>
      <div class="addition-section <?= $first ? '' : 'page-break' ?>">
        <?php if (!($from_add == 0.00 && $to_add == 0.00)): ?>
          <div class="addition-heading">Addition: <?= htmlspecialchars($add) ?></div>
        <?php endif; ?>
        <table>
          <thead>
            <tr>
              <th>CYL / SPH</th>
              <?php foreach ($sph_list as $sph): ?>
                <th><?= htmlspecialchars($sph) ?></th>
              <?php endforeach; ?>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($cyl_list as $cyl): ?>
              <tr>
                <th><?= htmlspecialchars($cyl) ?></th>
                <?php foreach ($sph_list as $sph):
                    $val = 0;
                    if (isset($grouped[$add]) && isset($grouped[$add][$cyl]) && isset($grouped[$add][$cyl][$sph])) {
                        $val = $grouped[$add][$cyl][$sph];
                    }
                ?>
                  <td class="<?= $val == 0 ? 'zero' : '' ?>"><?= htmlspecialchars($val) ?></td>
                <?php endforeach; ?>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php $first = false; endforeach; ?>
  <?php else: ?>
    <p style="color:red; text-align:center; font-weight:bold;">❌ Koi addition range nahi mili.</p>
  <?php endif; ?>

</div>
</body>
</html>
