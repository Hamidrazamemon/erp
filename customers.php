<?php
session_start();
require 'includes/db.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}

// ADD CUSTOMER
if (isset($_POST['add_customer'])) {
    $code = trim($_POST['code']);
    $name = trim($_POST['name']);
    $contact = trim($_POST['contact']);
    $address = trim($_POST['address']);

    if ($name !== '' && $code !== '') {
        $stmt = $pdo->prepare("INSERT INTO customers (code, name, phone, address, balance) VALUES (?, ?, ?, ?, 0)");
        $stmt->execute([$code, $name, $contact, $address]);
    }
    header("Location: customers.php");
    exit;
}

// UPDATE CUSTOMER
if (isset($_POST['update_customer'])) {
    $id = (int)$_POST['edit_id'];
    $code = trim($_POST['edit_code']);
    $name = trim($_POST['edit_name']);
    $contact = trim($_POST['edit_contact']);
    $address = trim($_POST['edit_address']);

    $stmt = $pdo->prepare("UPDATE customers SET code=?, name=?, phone=?, address=? WHERE id=?");
    $stmt->execute([$code, $name, $contact, $address, $id]);

    header("Location: customers.php");
    exit;
}

// DELETE CUSTOMER
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $pdo->prepare("DELETE FROM customers WHERE id=?")->execute([$id]);
    header("Location: customers.php");
    exit;
}

// FETCH CUSTOMERS WITH LIVE BALANCE (from sales)
$query = $pdo->query("
    SELECT c.*, 
           IFNULL(SUM(s.remaining_amount),0) AS live_balance
    FROM customers c
    LEFT JOIN sales s ON c.id = s.customer_id
    GROUP BY c.id
    ORDER BY c.id DESC
");
$customers = $query->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Customers | AT Optical ERP</title>
<style>
body { background:#f4f6f8; font-family:Arial,Helvetica,sans-serif; margin:0; }
.topbar {
  background:#0078d7; color:#fff; padding:12px 20px;
  display:flex; justify-content:space-between; align-items:center;
}
.topbar nav a { color:#fff; text-decoration:none; margin-left:14px; font-weight:bold; }
.topbar nav a:hover { text-decoration:underline; }
.container {
  max-width:1100px; margin:20px auto; background:#fff; padding:20px;
  border-radius:8px; box-shadow:0 0 8px rgba(0,0,0,0.1);
}
.container { padding: 20px; max-width: 1100px; margin: auto; }
form { background: white; padding: 15px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); margin-bottom: 20px; }
input[type=text], input[type=number] { padding: 8px; width: 200px; border: 1px solid #ccc; border-radius: 5px; }
button { padding: 8px 15px; background: #0078d7; color: white; border: none; border-radius: 5px; cursor: pointer; }
button:hover { background: #005fa3; }
table { width: 100%; border-collapse: collapse; background: white; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
th, td { border: 1px solid #ddd; padding: 8px; text-align: center; }
th { background: #0078d7; color: white; }
tr:nth-child(even) { background: #f9f9f9; }
.balance-pos { color: green; font-weight: bold; }
.balance-neg { color: red; font-weight: bold; }
#searchInput { margin-bottom: 15px; width: 250px; padding: 8px; border-radius: 5px; border: 1px solid #ccc; }
.modal {
    display: none; position: fixed; z-index: 100; left: 0; top: 0; width: 100%; height: 100%;
    background: rgba(0,0,0,0.5); justify-content: center; align-items: center;
}
.modal-content {
    background: white; padding: 20px; border-radius: 8px; width: 400px;
}
.modal input { width: 100%; margin-bottom: 10px; }

</style>
</head>
<body>
<div class="topbar">
    <div><strong>AT Optical ERP</strong> | Customers</div>
    <nav>
        <a href="dashboard.php">Dashboard</a>
        <a href="glass.php">Glasses</a>
        <a href="suppliers.php">Suppliers</a>
        <a href="customers.php" style="text-decoration:underline;">Customers</a>
        <a href="purchase.php">Purchase</a>
        <a href="sales.php">Sales</a>
        <a href="ledger.php">Ledger</a>
        <a href="stock.php">Stock</a>
        <a href="logout.php" style="color:#ffdddd;">Logout</a>
    </nav>
</div>

<div class="container">
    <h2>Add Customer</h2>
    <form method="POST">
        <input type="text" name="code" placeholder="Code" required>
        <input type="text" name="name" placeholder="Customer Name" required>
        <input type="text" name="contact" placeholder="Contact Number">
        <input type="text" name="address" placeholder="Address">
        <button type="submit" name="add_customer">Add Customer</button>
    </form>

    <h2>All Customers</h2>
    <input type="text" id="searchInput" placeholder="Search Customer by Name or Contact...">

    <table id="customerTable">
        <tr>
            <th>ID</th>
            <th>Code</th>
            <th>Name</th>
            <th>Contact</th>
            <th>Address</th>
            <th>Balance</th>
            <th>Action</th>
        </tr>
        <?php foreach ($customers as $c): ?>
        <tr>
            <td><?= $c['id'] ?></td>
            <td><?= htmlspecialchars($c['code']) ?></td>
            <td><?= htmlspecialchars($c['name']) ?></td>
            <td><?= htmlspecialchars($c['phone']) ?></td>
            <td><?= htmlspecialchars($c['address']) ?></td>
            <td class="<?= $c['live_balance'] >= 0 ? 'balance-pos' : 'balance-neg' ?>">
                <?= number_format($c['live_balance'], 2) ?>
            </td>
            <td>
                <button onclick="openEditModal(<?= $c['id'] ?>, '<?= htmlspecialchars($c['code']) ?>', '<?= htmlspecialchars($c['name']) ?>', '<?= htmlspecialchars($c['phone']) ?>', '<?= htmlspecialchars($c['address']) ?>')">Edit</button>
                <a href="?delete=<?= $c['id'] ?>" onclick="return confirm('Delete this customer?')" style="color:red;">Delete</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

<!-- Edit Modal -->
<div class="modal" id="editModal">
    <div class="modal-content">
        <h3>Edit Customer</h3>
        <form method="POST">
            <input type="hidden" name="edit_id" id="edit_id">
            <input type="text" name="edit_code" id="edit_code" placeholder="Code" required>
            <input type="text" name="edit_name" id="edit_name" placeholder="Name" required>
            <input type="text" name="edit_contact" id="edit_contact" placeholder="Contact">
            <input type="text" name="edit_address" id="edit_address" placeholder="Address">
            <button type="submit" name="update_customer">Update</button>
            <button type="button" onclick="closeModal()">Cancel</button>
        </form>
    </div>
</div>

<script>
document.getElementById('searchInput').addEventListener('keyup', function() {
    var filter = this.value.toLowerCase();
    var rows = document.querySelectorAll('#customerTable tr:not(:first-child)');
    rows.forEach(function(row) {
        var name = row.cells[2].textContent.toLowerCase();
        var contact = row.cells[3].textContent.toLowerCase();
        row.style.display = (name.includes(filter) || contact.includes(filter)) ? '' : 'none';
    });
});

function openEditModal(id, code, name, contact, address) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_code').value = code;
    document.getElementById('edit_name').value = name;
    document.getElementById('edit_contact').value = contact;
    document.getElementById('edit_address').value = address;
    document.getElementById('editModal').style.display = 'flex';
}

function closeModal() {
    document.getElementById('editModal').style.display = 'none';
}
</script>
</body>
</html>
