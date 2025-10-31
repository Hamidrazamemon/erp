<?php
session_start();
require 'includes/db.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}

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
    $pdo->prepare("UPDATE suppliers SET balance = balance - ? WHERE id=?")->execute([$amount, $id]);
    $pdo->prepare("INSERT INTO supplier_ledger (supplier_id, type, amount, note) VALUES (?, 'Payment', ?, 'Manual Payment')")
        ->execute([$id, $amount]);
    header("Location: suppliers.php");
    exit;
}

// FETCH SUPPLIERS
$suppliers = $pdo->query("SELECT * FROM suppliers ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Suppliers | AT Optical ERP</title>
<style>
body { font-family: Arial, sans-serif; background: #f4f6f8; margin: 0; }
.topbar { background: #0078d7; color: white; padding: 10px 20px; display: flex; justify-content: space-between; align-items: center; }
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
    <div><strong>AT Optical ERP</strong> | Suppliers</div>
    <nav>
        <a href="dashboard.php" style="color:white;text-decoration:none;">Dashboard</a>
        <a href="logout.php" style="color:#ffdddd;text-decoration:none;">Logout</a>
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
            <th>Add Payment</th>
            <th>Action</th>
        </tr>
        <?php foreach ($suppliers as $s): ?>
        <tr>
            <td><?= $s['id'] ?></td>
            <td><?= htmlspecialchars($s['code']) ?></td>
            <td><?= htmlspecialchars($s['name']) ?></td>
            <td><?= htmlspecialchars($s['phone']) ?></td>
            <td><?= htmlspecialchars($s['address']) ?></td>
            <td class="<?= $s['balance'] >= 0 ? 'balance-pos' : 'balance-neg' ?>">
                <?= number_format($s['balance'], 2) ?>
            </td>
            <td>
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="supplier_id" value="<?= $s['id'] ?>">
                    <input type="number" step="0.01" name="amount" placeholder="Amount" required>
                    <button type="submit" name="add_payment">Pay</button>
                </form>
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
