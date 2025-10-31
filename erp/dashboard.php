<?php
session_start();
require 'includes/db.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}

// Fetch totals
$total_purchase = $pdo->query("SELECT IFNULL(SUM(total_amount),0) AS total FROM purchases")->fetch()['total'];
$total_sales = $pdo->query("SELECT IFNULL(SUM(total_amount),0) AS total FROM sales")->fetch()['total'];
$total_suppliers = $pdo->query("SELECT COUNT(*) AS total FROM suppliers")->fetch()['total'];
$total_customers = $pdo->query("SELECT COUNT(*) AS total FROM customers")->fetch()['total'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard | AT Optical ERP</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body { background: #f4f6f8; font-family: Arial, sans-serif; }
        .topbar { background: #0078d7; color: white; padding: 10px 20px; display: flex; justify-content: space-between; align-items: center; }
        .cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; padding: 20px; }
        .card { background: white; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); padding: 20px; text-align: center; }
        .card h3 { margin: 0; color: #0078d7; }
        .card p { font-size: 24px; font-weight: bold; margin-top: 10px; color: #333; }
        nav a { color: white; margin-right: 15px; text-decoration: none; font-weight: bold; }
        nav a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="topbar">
        <div><strong>AT Optical ERP</strong> | Dashboard</div>
        <nav>
            <a href="dashboard.php">Dashboard</a>
            <a href="items.php">Items</a>
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

    <div class="cards">
        <div class="card">
            <h3>Total Purchases</h3>
            <p>Rs <?= number_format($total_purchase, 2) ?></p>
        </div>
        <div class="card">
            <h3>Total Sales</h3>
            <p>Rs <?= number_format($total_sales, 2) ?></p>
        </div>
        <div class="card">
            <h3>Total Suppliers</h3>
            <p><?= $total_suppliers ?></p>
        </div>
        <div class="card">
            <h3>Total Customers</h3>
            <p><?= $total_customers ?></p>
        </div>
    </div>
</body>
</html>
