<?php
require 'includes/session.php';
require 'includes/db.php';

if (!isset($_GET['v']) || trim($_GET['v']) === '') {
    header("Location: glass_generate.php");
    exit;
}

$variety = trim($_GET['v']);

// Generate values from -8.00 to +8.00 step 0.25
$values = [];
for ($i = -8.00; $i <= 8.00; $i += 0.25) {
    $values[] = ($i >= 0 ? '+' : '') . number_format($i, 2);
}

// Delete previous records for this variety to avoid duplicates
$pdo->prepare("DELETE FROM glass_powers WHERE variety_name = ?")->execute([$variety]);

// Prepare insert query
$stmt = $pdo->prepare("INSERT INTO glass_powers (variety_name, spherical, cylinder) VALUES (?, ?, ?)");

// Insert spherical and cylinder same value
foreach ($values as $val) {
    $stmt->execute([$variety, $val,  $val]);
}

$message = "✅ Glass powers for '{$variety}' have been saved to the database.";

// Now display table
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
        <a href="glass_generate.php">Glass Generator</a>
        <a href="purchase.php">Purchase</a>
        <a href="sales.php">Sales</a>
        <a href="logout.php" style="color:#ffdddd;">Logout</a>
    </nav>
</div>

<div class="container">
<h2>Glass Variety: <?= htmlspecialchars($variety) ?></h2>
<p><strong><?= $message ?></strong></p>
<a href="glass_generate.php" class="btn">← Back</a>

<table>
<tr>
    <th>#</th>
    <th>Spherical</th>
    <th>Cylinder</th>
</tr>
<?php
$count = 1;
foreach ($values as $val) {
    echo "<tr>
            <td>{$count}</td>
            <td>{$val}</td>
            <td>cylinder {$val}</td>
          </tr>";
    $count++;
}
?>
</table>
</div>
</body>
</html>
