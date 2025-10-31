<?php
session_start();
require 'includes/db.php';
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}

// Fetch variety names
$varieties = $pdo->query("SELECT DISTINCT variety_name FROM glass_powers ORDER BY variety_name")->fetchAll(PDO::FETCH_COLUMN);

// Fetch unique SPH, CYL, ADDITION, AXIS values from database
$sph_vals = $pdo->query("SELECT DISTINCT spherical FROM glass_powers ORDER BY spherical ASC")->fetchAll(PDO::FETCH_COLUMN);
$cyl_vals = $pdo->query("SELECT DISTINCT cylinder FROM glass_powers ORDER BY cylinder ASC")->fetchAll(PDO::FETCH_COLUMN);
$add_vals = $pdo->query("SELECT DISTINCT addition FROM glass_powers ORDER BY addition ASC")->fetchAll(PDO::FETCH_COLUMN);
$axis_vals = $pdo->query("SELECT DISTINCT axis FROM glass_powers ORDER BY axis ASC")->fetchAll(PDO::FETCH_COLUMN);
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Stock Filter | AT Optical ERP</title>
<style>
body { background:#f4f6f8; font-family:Arial,Helvetica,sans-serif; margin:0; }
.topbar {
  background:#0078d7; color:#fff; padding:12px 20px;
  display:flex; justify-content:space-between; align-items:center;
}
.topbar nav a { color:#fff; text-decoration:none; margin-left:14px; font-weight:bold; }
.topbar nav a:hover { text-decoration:underline; }
.container{
  max-width:700px;
  margin:30px auto;
  background:#fff;
  padding:20px;
  border-radius:8px;
  box-shadow:0 0 8px rgba(0,0,0,.1)
}
h2{color:#0078d7}
label{display:block;margin-top:10px;font-weight:bold}
select{padding:6px;width:100%;border:1px solid #ccc;border-radius:4px}
button{
  margin-top:20px;
  padding:10px 16px;
  background:#0078d7;
  color:#fff;
  border:none;
  border-radius:4px;
  cursor:pointer;
  font-size:15px;
}
button:hover{background:#005bb5}
.form-row{display:flex;gap:10px;margin-top:10px}
.form-row div{flex:1}
.btn-group {
  display:flex;
  gap:10px;
  margin-top:25px;
}
.btn-secondary {
  background:#28a745;
}
.btn-secondary:hover {
  background:#1f7e33;
}
</style>
</head>
<body>
<div class="topbar">
  <div><strong>AT Optical ERP</strong> | Stock Filter</div>
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
  <h2>Stock Filter</h2>
  <form id="stockForm" method="POST">
    <label>Variety</label>
    <select name="variety" required>
      <option value="">-- Select Variety --</option>
      <?php foreach($varieties as $v): ?>
        <option value="<?= htmlspecialchars($v) ?>"><?= htmlspecialchars($v) ?></option>
      <?php endforeach; ?>
    </select>

    <!-- SPH Range -->
    <div class="form-row">
      <div>
        <label>From SPH</label>
        <select name="from_sph" required>
          <?php foreach($sph_vals as $v): ?>
            <option value="<?= $v ?>"><?= $v ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label>To SPH</label>
        <select name="to_sph" required>
          <?php foreach(array_reverse($sph_vals) as $v): ?>
            <option value="<?= $v ?>"><?= $v ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <!-- CYL Range -->
    <div class="form-row">
      <div>
        <label>From CYL</label>
        <select name="from_cyl" required>
          <?php foreach($cyl_vals as $v): ?>
            <option value="<?= $v ?>"><?= $v ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label>To CYL</label>
        <select name="to_cyl" required>
          <?php foreach(array_reverse($cyl_vals) as $v): ?>
            <option value="<?= $v ?>"><?= $v ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <!-- ADDITION Range -->
    <div class="form-row">
      <div>
        <label>From Addition</label>
        <select name="from_addition">
          <?php foreach($add_vals as $v): ?>
            <option value="<?= $v ?>"><?= $v ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label>To Addition</label>
        <select name="to_addition">
          <?php foreach(array_reverse($add_vals) as $v): ?>
            <option value="<?= $v ?>"><?= $v ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <!-- AXIS Range -->
    <div class="form-row">
      <div>
        <label>From Axis</label>
        <select name="from_axis">
          <?php foreach($axis_vals as $v): ?>
            <option value="<?= $v ?>"><?= $v ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label>To Axis</label>
        <select name="to_axis">
          <?php foreach(array_reverse($axis_vals) as $v): ?>
            <option value="<?= $v ?>"><?= $v ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <!-- Two Buttons -->
    <div class="btn-group">
      <button type="submit" formaction="stock_report.php">📊 View Stock Report</button>
      <button type="submit" class="btn-secondary" formaction="stock_addition.php">➕ View Addition Report</button>
    </div>

  </form>
</div>
</body>
</html>
