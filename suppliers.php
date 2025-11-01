<?php
session_start();
require 'includes/db.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: index.php");
    exit;
}

// Fetch totals
$total_purchase = $pdo->query("SELECT IFNULL(SUM(total_amount),0) AS total FROM purchases")->fetch()['total'];
$total_sales = $pdo->query("SELECT IFNULL(SUM(total_amount),0) AS total FROM sales")->fetch()['total'];
$total_suppliers = $pdo->query("SELECT COUNT(*) AS total FROM suppliers")->fetch()['total'];
$total_customers = $pdo->query("SELECT COUNT(*) AS total FROM customers")->fetch()['total'];


// ADD SUPPLIER
if (isset($_POST['add_supplier'])) {
    $code = trim($_POST['code']);
    $name = trim($_POST['name']);
    $contact = trim($_POST['contact']);
    $address = trim($_POST['address']);

    if ($name !== '' && $code !== '') {
        $stmt = $pdo->prepare("INSERT INTO suppliers (code, name, phone, address, balance) VALUES (?, ?, ?, ?, 0)");
        $stmt->execute([$code, $name, $contact, $address]);
    }
    header("Location: suppliers.php");
    exit;
}

// UPDATE SUPPLIER
if (isset($_POST['update_supplier'])) {
    $id = (int)$_POST['edit_id'];
    $code = trim($_POST['edit_code']);
    $name = trim($_POST['edit_name']);
    $contact = trim($_POST['edit_contact']);
    $address = trim($_POST['edit_address']);

    $stmt = $pdo->prepare("UPDATE suppliers SET code=?, name=?, phone=?, address=? WHERE id=?");
    $stmt->execute([$code, $name, $contact, $address, $id]);

    header("Location: suppliers.php");
    exit;
}

// DELETE SUPPLIER
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $pdo->prepare("DELETE FROM suppliers WHERE id=?")->execute([$id]);
    header("Location: suppliers.php");
    exit;
}

// ADD PAYMENT
if (isset($_POST['add_payment'])) {
    $id = (int)$_POST['supplier_id'];
    $amount = floatval($_POST['amount']);

    // Reduce remaining amounts from purchases (latest first)
    $pdo->beginTransaction();

    $remaining = $amount;
    $purchases = $pdo->prepare("SELECT id, remaining_amount FROM purchases WHERE supplier_id=? AND remaining_amount>0 ORDER BY id ASC");
    $purchases->execute([$id]);

    while ($row = $purchases->fetch(PDO::FETCH_ASSOC)) {
        if ($remaining <= 0) break;
        $deduct = min($row['remaining_amount'], $remaining);
        $pdo->prepare("UPDATE purchases SET remaining_amount = remaining_amount - ? WHERE id=?")
            ->execute([$deduct, $row['id']]);
        $remaining -= $deduct;
    }

    // Log payment
    $pdo->prepare("INSERT INTO supplier_ledger (supplier_id, type, amount, note) VALUES (?, 'Payment', ?, 'Manual Payment')")
        ->execute([$id, $amount]);

    $pdo->commit();

    header("Location: suppliers.php");
    exit;
}

// FETCH SUPPLIERS + BALANCE (live calculation)
$query = $pdo->query("
    SELECT s.*, 
           IFNULL(SUM(p.remaining_amount),0) AS live_balance
    FROM suppliers s
    LEFT JOIN purchases p ON s.id = p.supplier_id
    GROUP BY s.id
    ORDER BY s.id DESC
");
$suppliers = $query->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Suppliers | AT Optical ERP</title>
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
.modal { display: none; position: fixed; z-index: 100; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); justify-content: center; align-items: center; }
.modal-content { background: white; padding: 20px; border-radius: 8px; width: 400px; }
.modal input { width: 100%; margin-bottom: 10px; }

nav {
  display:flex;
  gap:12px;
  align-items:center;
  background:#0078d7;
  padding:12px 20px;
  flex-wrap:wrap;
}
nav a, .dropbtn {
  color:#fff;
  text-decoration:none;
  font-weight:bold;
  padding:6px 10px;
  border:none;
  background:none;
  cursor:pointer;
}
nav a:hover, .dropbtn:hover { text-decoration:underline; }

.dropdown {
  position:relative;
  display:inline-block;
}
.dropdown-content {
  display:none;
  position:absolute;
  background:#0078d7;
  min-width:180px;
  box-shadow:0 4px 8px rgba(0,0,0,0.2);
  z-index:1;
  border-radius:6px;
  overflow:hidden;
}
.dropdown-content a { 
  padding:10px 14px;
  text-decoration:none;
  display:block;
}
.dropdown-content a:hover { background:black; }

.dropdown:hover .dropdown-content { display:block; }
h2 { color:#0078d7; margin-top:0; text-align:center; }


</style>
</head>
<body>
<div class="topbar">
    <div><strong>AT Optical ERP</strong> | Suppliers</div>
    <nav>
  <a href="dashboard.php">Dashboard</a>
  <a href="glass.php">Glasses</a>
  <a href="suppliers.php" style="text-decoration:underline;">Suppliers</a>
  <a href="customers.php">Customers</a>

  <!-- Purchase Dropdown -->
  <div class="dropdown">
    <button class="dropbtn">Purchase ▼</button>
    <div class="dropdown-content">
      <a href="purchase.php">Add Purchase</a>
      <a href="purchase_view.php">View Purchases</a>
      <a href="purchase_return.php">Add Purchase Return</a>
      <a href="purchase_return_view.php">View Purchase Returns</a>
    </div>
  </div>

  <!-- Sales Dropdown -->
  <div class="dropdown">
    <button class="dropbtn">Sales ▼</button>
    <div class="dropdown-content">
      <a href="sales.php">Add Sale</a>
      <a href="sale_view.php">View Sales</a>
      <a href="sale_return.php">Add Sale Return</a>
      <a href="sale_return_view.php">View Sale Returns</a>
    </div>
  </div>


  <!-- Report Dropdown -->
  <div class="dropdown">
    <button class="dropbtn">Report ▼</button>
    <div class="dropdown-content">
      <a href="report.php">Main Report</a>
      <a href="rate_list.php">Rate List</a>
       <a href="ledger.php">Ledger</a>
  <a href="stock.php">Stock</a>
  <a href="balance.php">Balance</a>

    </div>
  </div>

  <a href="logout.php" style="color:#ffdddd;">Logout</a>
</nav>

</div>

<div class="container">
    <h2>Add Supplier</h2>
    <form method="POST">
        <input type="text" name="code" placeholder="Code" required>
        <input type="text" name="name" placeholder="Supplier Name" required>
        <input type="text" name="contact" placeholder="Contact Number">
        <input type="text" name="address" placeholder="Address">
        <button type="submit" name="add_supplier">Add Supplier</button>
    </form>

    <h2>All Suppliers</h2>
    <input type="text" id="searchInput" placeholder="Search Supplier by Name or Contact...">

    <table id="supplierTable">
        <tr>
            <th>ID</th>
            <th>Code</th>
            <th>Name</th>
            <th>Contact</th>
            <th>Address</th>
            <th>Balance</th>
            <th>Action</th>
        </tr>
        <?php foreach ($suppliers as $s): ?>
        <tr>
            <td><?= $s['id'] ?></td>
            <td><?= htmlspecialchars($s['code']) ?></td>
            <td><?= htmlspecialchars($s['name']) ?></td>
            <td><?= htmlspecialchars($s['phone']) ?></td>
            <td><?= htmlspecialchars($s['address']) ?></td>
            <td class="<?= $s['live_balance'] >= 0 ? 'balance-pos' : 'balance-neg' ?>">
                <?= number_format($s['live_balance'], 2) ?>
            </td>
            <td>
                <button onclick="openEditModal(<?= $s['id'] ?>, '<?= htmlspecialchars($s['code']) ?>', '<?= htmlspecialchars($s['name']) ?>', '<?= htmlspecialchars($s['phone']) ?>', '<?= htmlspecialchars($s['address']) ?>')">Edit</button>
                <a href="?delete=<?= $s['id'] ?>" onclick="return confirm('Delete this supplier?')" style="color:red;">Delete</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

<!-- Edit Modal -->
<div class="modal" id="editModal">
    <div class="modal-content">
        <h3>Edit Supplier</h3>
        <form method="POST">
            <input type="hidden" name="edit_id" id="edit_id">
            <input type="text" name="edit_code" id="edit_code" placeholder="Code" required>
            <input type="text" name="edit_name" id="edit_name" placeholder="Name" required>
            <input type="text" name="edit_contact" id="edit_contact" placeholder="Contact">
            <input type="text" name="edit_address" id="edit_address" placeholder="Address">
            <button type="submit" name="update_supplier">Update</button>
            <button type="button" onclick="closeModal()">Cancel</button>
        </form>
    </div>
</div>

<script>
document.getElementById('searchInput').addEventListener('keyup', function() {
    var filter = this.value.toLowerCase();
    var rows = document.querySelectorAll('#supplierTable tr:not(:first-child)');
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
