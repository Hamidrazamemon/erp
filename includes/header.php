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
<style>
body {
  background:#f4f6f8;
  font-family:Arial,Helvetica,sans-serif;
  margin:0;
}
.topbar {
  background:#0078d7;
  color:#fff;
  padding:12px 20px;
  display:flex;
  justify-content:space-between;
  align-items:center;
}
.topbar nav a {
  color:#fff;
  text-decoration:none;
  margin-left:14px;
  font-weight:bold;
}
.topbar nav a:hover { text-decoration:underline; }

.container {
  max-width:1200px;
  margin:20px auto;
  background:#fff;
  padding:20px;
  border-radius:8px;
  box-shadow:0 0 8px rgba(0,0,0,0.1);
}
h2 {
  color:#0078d7;
  margin-top:0;
}
.cards {
  display:grid;
  grid-template-columns:repeat(auto-fit, minmax(250px, 1fr));
  gap:20px;
  margin-top:20px;
}
.card {
  background:#fff;
  border:1px solid #ddd;
  border-radius:10px;
  box-shadow:0 0 8px rgba(0,0,0,0.05);
  text-align:center;
  padding:20px;
  transition:transform .2s ease;
}
.card:hover {
  transform:translateY(-4px);
  box-shadow:0 4px 10px rgba(0,0,0,0.1);
}
.card h3 {
  margin:0;
  color:#0078d7;
  font-size:18px;
}
.card p {
  font-size:26px;
  font-weight:bold;
  color:#333;
  margin:10px 0 0;
}
</style>
</head>

<body>

<div class="topbar">
  <div><strong>AT Optical ERP</strong> | Dashboard</div>
  <nav>
    <a href="dashboard.php" style="text-decoration:underline;">Dashboard</a>
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
