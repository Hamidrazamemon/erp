<?php
require 'includes/session.php';
require 'includes/db.php';

// validate variety
if (!isset($_GET['v']) || trim($_GET['v']) === '') {
    header("Location: glass.php");
    exit;
}

$variety = trim($_GET['v']);

// generate values from -8.00 to +8.00 (step 0.25)
$rangeValues = [];
for ($i = -8.00; $i <= 8.00; $i += 0.25) {
    $formatted = ($i > 0) ? '+' . number_format($i, 2) : number_format($i, 2);
    if (abs($i) < 0.001) $formatted = '+0.00';
    $rangeValues[] = $formatted;
}

// axis values (0 to 180 step 5)
$axes = [];
for ($a = 0; $a <= 180; $a += 5) {
    $axes[] = $a;
}

// find max rows (largest set)
$totalRows = max(count($rangeValues), count($axes));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($variety) ?> | Glass Powers</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f8; margin: 0; padding: 0; }
        .topbar { background: #0078d7; color: white; padding: 10px 20px; display: flex; justify-content: space-between; align-items: center; }
        nav { display: flex; gap: 15px; }
        nav a { color: white; text-decoration: none; font-weight: bold; }
        nav a:hover { text-decoration: underline; }
        .container { padding: 20px; }
        h2 { color: #0078d7; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; background: white; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: center; }
        th { background: #0078d7; color: white; }
        a.btn { background: #0078d7; color: white; padding: 6px 12px; border-radius: 5px; text-decoration: none; }
        a.btn:hover { background: #005fa3; }
    </style>
</head>
<body>
    <div class="topbar">
        <div><strong>AT Optical ERP</strong> | Glass Variety Powers</div>
        <nav>
            <a href="dashboard.php">Dashboard</a>
            <a href="glass.php" style="text-decoration:underline;">Glasses</a>
            <a href="suppliers.php">Suppliers</a>
            <a href="customers.php">Customers</a>
            <a href="purchase.php">Purchase</a>
            <a href="sales.php">Sales</a>
            <a href="ledger.php">Ledger</a>
            <a href="stock.php">Stock</a>
            <a href="logout.php" style="color:#ffdddd;">Logout</a>
        </nav>
    </div>

    <div class="container">
        <h2>Glass Variety: <?= htmlspecialchars($variety) ?></h2>
        <a href="glass.php" class="btn">← Back</a>

        <table>
            <tr>
                <th>#</th>
                <th>Spherical</th>
                <th>Cylinder</th>
                <th>Addition</th>
                <th>Axis</th>
            </tr>
            <?php
            for ($i = 0; $i < $totalRows; $i++) {
                $spherical = $rangeValues[$i % count($rangeValues)] ?? '';
                $cylinder  = $rangeValues[$i % count($rangeValues)] ?? '';
                $addition  = $rangeValues[$i % count($rangeValues)] ?? '';
                $axis      = $axes[$i % count($axes)] ?? '';
                echo "<tr>
                        <td>" . ($i + 1) . "</td>
                        <td>{$spherical}</td>
                        <td>{$cylinder}</td>
                        <td>{$addition}</td>
                        <td>{$axis}</td>
                      </tr>";
            }
            ?>
        </table>
    </div>
</body>
</html>
