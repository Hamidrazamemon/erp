<?php
require 'includes/session.php';
require 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $variety = trim($_POST['variety_name']);
    if ($variety !== '') {
        // remove any previous records
        $pdo->prepare("DELETE FROM glass_powers WHERE variety_name = ?")->execute([$variety]);

        // spherical / cylinder / addition values (65 total)
        $values = [];
        for ($i = -8.00; $i <= 8.00; $i += 0.25) {
            $values[] = number_format($i, 2, '.', '');
        }

        // axis values (0–180, step 5)
        $axes = [];
        for ($a = 0; $a <= 180; $a += 5) {
            $axes[] = $a;
        }

        // prepare insert query
        $stmt = $pdo->prepare("
            INSERT INTO glass_powers (variety_name, spherical, cylinder, addition, axis)
            VALUES (?, ?, ?, ?, ?)
        ");

        // only 65 rows total (each index = one row, not nested)
        // only 65 rows total (each index = one row, not nested)
$total = min(count($values), 65);
for ($i = 0; $i < $total; $i++) {
    $spherical = $values[$i];     // -8.00 → +8.00 tak
    $cylinder  = $values[$i];     // same pattern
    $addition  = $values[$i];     // same pattern
    $axis      = $axes[array_rand($axes)];
    $stmt->execute([$variety, $spherical, $cylinder, $addition, $axis]);
}


        $message = "✅ Variety '{$variety}' generated successfully with {$total} independent powers!";
    } else {
        $message = "❌ Please enter a variety name!";
    }
}

// fetch all generated varieties
$varieties = $pdo->query("
    SELECT DISTINCT variety_name FROM glass_powers ORDER BY variety_name
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Generate Glass Powers | AT Optical ERP</title>
<style>
body { background:#f4f6f8; font-family:Arial,Helvetica,sans-serif; margin:0; }
.topbar { background:#0078d7; color:#fff; padding:12px 20px; display:flex; justify-content:space-between; align-items:center; }
.topbar nav a { color:#fff; text-decoration:none; margin-left:14px; font-weight:bold; }
.topbar nav a:hover { text-decoration:underline; }
.container { max-width:1100px; margin:20px auto; background:#fff; padding:20px; border-radius:8px; box-shadow:0 0 8px rgba(0,0,0,0.1); }
input[type=text] { width:250px; padding:8px; border:1px solid #ccc; border-radius:5px; }
button { padding:8px 15px; background:#0078d7; color:white; border:none; border-radius:5px; cursor:pointer; }
button:hover { background:#005fa3; }
table { width:100%; border-collapse:collapse; margin-top:20px; }
th, td { border:1px solid #ccc; padding:8px; text-align:left; }
th { background:#0078d7; color:white; }
</style>
</head>
<body>
<div class="topbar">
    <div><strong>AT Optical ERP</strong> | Glass</div>
    <nav>
        <a href="dashboard.php">Dashboard</a>
        <a href="glass.php">Glasses</a>
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
    <h2>Generate Glass Variety</h2>
    <form method="POST">
        <label>Variety Name:</label>
        <input type="text" name="variety_name" placeholder="e.g. Bluecut" required>
        <button type="submit">Generate</button>
    </form>

    <?php if (!empty($message)): ?>
        <p><strong><?= htmlspecialchars($message) ?></strong></p>
    <?php endif; ?>

    <h3>Existing Varieties</h3>
    <table>
        <tr><th>#</th><th>Variety Name</th><th>Action</th></tr>
        <?php
        $i = 1;
        foreach ($varieties as $v):
        ?>
        <tr>
            <td><?= $i++ ?></td>
            <td><?= htmlspecialchars($v['variety_name']) ?></td>
            <td><a href="view_glass.php?v=<?= urlencode($v['variety_name']) ?>">View</a></td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>
</body>
</html>
