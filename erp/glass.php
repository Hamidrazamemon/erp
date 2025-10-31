<?php
require 'includes/session.php';
require 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $variety = trim($_POST['variety_name']);
    if ($variety !== '') {

        // remove any previous records of same variety
        $pdo->prepare("DELETE FROM glass_powers WHERE variety_name = ?")->execute([$variety]);

        // generate values from 0.00 to 8.00 (step 0.25)
        $values = [];
        for ($i = 0; $i <= 8; $i += 0.25) {
            $values[] = number_format($i, 2);
        }

        // prepare insert query
        $stmt = $pdo->prepare("INSERT INTO glass_powers (variety_name, spherical, cylinder) VALUES (?, ?, ?)");

        // nested loop
        foreach ($values as $spherical) {
            foreach ($values as $cylinder) {
                $stmt->execute([$variety, $spherical, $cylinder]);
            }
        }

        $message = "✅ Variety '{$variety}' generated successfully with all combinations!";
    } else {
        $message = "❌ Please enter a variety name!";
    }
}

// fetch all generated varieties
$varieties = $pdo->query("SELECT DISTINCT variety_name FROM glass_powers ORDER BY variety_name")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Generate Glass Powers | AT Optical ERP</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f8; padding: 20px; }
        .topbar { background: #0078d7; color: white; padding: 10px 20px; display: flex; justify-content: space-between; align-items: center; }
        nav { display: flex; gap: 15px; }
        nav a { color: white; text-decoration: none; font-weight: bold; }
        nav a:hover { text-decoration: underline; }

        .container { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 0 8px rgba(0,0,0,0.1); margin-top: 20px; }
        input[type=text] { width: 250px; padding: 8px; border: 1px solid #ccc; border-radius: 5px; }
        button { padding: 8px 15px; background: #0078d7; color: white; border: none; border-radius: 5px; cursor: pointer; }
        button:hover { background: #005fa3; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background: #0078d7; color: white; }
    </style>
</head>
<body>
    <div class="topbar">
        <div><strong>AT Optical ERP</strong> | Glass Power Generator</div>
        <nav>
            <a href="dashboard.php">Dashboard</a>
            <a href="items.php">Items</a>
            <a href="glass_generate.php">Glass Generator</a>
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
            <tr><th>#</th><th>Variety Name</th><th>Total Combinations</th><th>Action</th></tr>
            <?php
            $i = 1;
            foreach ($varieties as $v):
                $count = $pdo->prepare("SELECT COUNT(*) FROM glass_powers WHERE variety_name = ?");
                $count->execute([$v['variety_name']]);
                $total = $count->fetchColumn();
            ?>
            <tr>
                <td><?= $i++ ?></td>
                <td><?= htmlspecialchars($v['variety_name']) ?></td>
                <td><?= $total ?></td>
                <td><a href="view_glass.php?v=<?= urlencode($v['variety_name']) ?>">View</a></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
</body>
</html>
