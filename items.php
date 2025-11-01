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

// Handle Add Item
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_item'])) {
    $stmt = $pdo->prepare("INSERT INTO items (item_name, category, brand, cost_price, sale_price, stock)
                           VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $_POST['item_name'],
        $_POST['category'],
        $_POST['brand'],
        $_POST['cost_price'],
        $_POST['sale_price'],
        $_POST['stock']
    ]);
    header("Location: items.php");
    exit;
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $pdo->prepare("DELETE FROM items WHERE id = ?")->execute([$id]);
    header("Location: items.php");
    exit;
}

// Fetch items
$items = $pdo->query("SELECT * FROM items ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Items | AT Optical ERP</title>
    <style>
        body { font-family: Arial; background: #f4f6f8; }
        .topbar { background: #0078d7; color: white; padding: 10px 20px; display: flex; justify-content: space-between; }
        nav a { color: white; text-decoration: none; margin-right: 15px; font-weight: bold; }
        nav a:hover { text-decoration: underline; }
        .container { padding: 20px; }
        h2 { color: #0078d7; }
        form { background: #fff; padding: 15px; border-radius: 10px; margin-bottom: 20px; }
        input, select { padding: 8px; margin: 5px; border-radius: 5px; border: 1px solid #ccc; }
        table { width: 100%; border-collapse: collapse; background: white; border-radius: 10px; overflow: hidden; }
        th, td { padding: 10px; border-bottom: 1px solid #ddd; text-align: center; }
        th { background: #0078d7; color: white; }
        a.btn-del { background: #e74c3c; color: white; padding: 5px 10px; border-radius: 5px; text-decoration: none; }
        a.btn-del:hover { background: #c0392b; }
        button { background: #0078d7; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; }
        button:hover { background: #005fa3; }
    </style>
</head>
<body>
    <div class="topbar">
        <div><strong>AT Optical ERP</strong> | Items</div>
        <nav>
    <a href="dashboard.php">Dashboard</a>
    <a href="glass.php">Glasses</a>
    <a href="suppliers.php">Suppliers</a>
    <a href="customers.php">Customers</a>
    <a href="purchase.php">Purchase</a>
    <a href="sales.php">Sales</a>
    <a href="ledger.php">Ledger</a>
    <a href="stock.php">Stock</a>
    <a href="balace.php">Balance</a>
    <a href="report.php">Report</a>
    <a href="rate_list.php">Rate List</a>
    <a href="logout.php" style="color:#ffdddd;">Logout</a>
  </nav>
    </div>

    <div class="container">
        <h2>Add New Item</h2>
        <form method="POST">
            <input type="text" name="item_name" placeholder="Item Name" required>
            <select name="category" required>
                <option value="">Select Category</option>
                <option value="Glass">Glass</option>
                <option value="Lens">Lens</option>
                <option value="Frame">Frame</option>
                <option value="Accessory">Accessory</option>
            </select>
            <input type="text" name="brand" placeholder="Brand">
            <input type="number" step="0.01" name="cost_price" placeholder="Cost Price">
            <input type="number" step="0.01" name="sale_price" placeholder="Sale Price">
            <input type="number" name="stock" placeholder="Stock">
            <button type="submit" name="add_item">Add Item</button>
        </form>

        <h2>All Items</h2>
        <table>
            <tr>
                <th>ID</th>
                <th>Item</th>
                <th>Category</th>
                <th>Brand</th>
                <th>Cost</th>
                <th>Sale</th>
                <th>Stock</th>
                <th>Action</th>
            </tr>
            <?php foreach($items as $i): ?>
            <tr>
                <td><?= $i['id'] ?></td>
                <td><?= htmlspecialchars($i['item_name']) ?></td>
                <td><?= $i['category'] ?></td>
                <td><?= htmlspecialchars($i['brand']) ?></td>
                <td>Rs <?= number_format($i['cost_price'], 2) ?></td>
                <td>Rs <?= number_format($i['sale_price'], 2) ?></td>
                <td><?= $i['stock'] ?></td>
                <td><a class="btn-del" href="items.php?delete=<?= $i['id'] ?>" onclick="return confirm('Delete this item?')">Delete</a></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
</body>
</html>
