<?php
require 'includes/session.php';
require 'includes/db.php';

// Fetch suppliers & varieties
$suppliers = $pdo->query("SELECT id,name FROM suppliers ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
$varieties = $pdo->query("SELECT DISTINCT variety_name FROM glass_powers ORDER BY variety_name")->fetchAll(PDO::FETCH_COLUMN);

// Generate -8.00 to +8.00 numbers
$numbers = [];
for($i=-8.00;$i<=8.00;$i+=0.25){
    $numbers[] = number_format($i,2,'.','');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Smart Supplier Rate List</title>
<style>
.topbar {
  background:#0078d7; color:#fff; padding:12px 20px;
  display:flex; justify-content:space-between; align-items:center;
}
.topbar nav a { color:#fff; text-decoration:none; margin-left:14px; font-weight:bold; }
.topbar nav a:hover { text-decoration:underline; }
body{font-family:Arial;background:#f4f6f8;margin:0;padding:0}
.container{max-width:1200px;margin:20px auto;padding:20px;background:#fff;border-radius:8px;box-shadow:0 0 8px rgba(0,0,0,.1)}
select,input{padding:6px;border:1px solid #ccc;border-radius:4px;width:120px}
table{width:100%;border-collapse:collapse;margin-top:15px}
th,td{border:1px solid #ddd;padding:8px;text-align:center}
th{background:#0078d7;color:#fff}
button{padding:6px 12px;background:#0078d7;color:#fff;border:none;border-radius:4px;cursor:pointer}
</style>
</head>
<body>
    <div class="topbar">
  <div><strong>AT Optical ERP</strong> | Rate List</div>
  <nav>
    <a href="dashboard.php" style="text-decoration:underline;">Dashboard</a>
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

<label>Supplier:</label>
<select id="supplierSelect">
<option value="">-- Select Supplier --</option>
<?php foreach($suppliers as $s): ?>
<option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option>
<?php endforeach; ?>
</select>

<label style="margin-left:15px">Variety:</label>
<select id="varietySelect">
<option value="">-- Select Variety --</option>
<?php foreach($varieties as $v): ?>
<option value="<?= $v ?>"><?= $v ?></option>
<?php endforeach; ?>
</select>

<button id="loadRates" style="margin-left:15px;">Load Rates</button>

<form id="rateForm" method="POST" action="rate_list_save.php">
<input type="hidden" name="supplier_id" id="supplier_id">
<input type="hidden" name="variety_name" id="variety_name">

<table id="rateTable">
<thead>
<tr>
<th>#</th>
<th>Spherical</th>
<th>Cylinder</th>
<th>Quantity</th>
<th>Action</th>
</tr>
</thead>
<tbody></tbody>
</table>

<button type="submit" style="margin-top:12px;">Save/Update Rates</button>
</form>
</div>

<script>
const numbers = <?= json_encode($numbers) ?>;

document.getElementById('loadRates').addEventListener('click', ()=>{
    const supplierId = document.getElementById('supplierSelect').value;
    const variety = document.getElementById('varietySelect').value;
    if(!supplierId || !variety){ alert('Select supplier & variety'); return; }

    document.getElementById('supplier_id').value = supplierId;
    document.getElementById('variety_name').value = variety;

    fetch(`fetch_supplier_rates.php?supplier_id=${supplierId}&variety=${encodeURIComponent(variety)}`)
    .then(res=>res.json())
    .then(data=>{
        const tbody = document.querySelector('#rateTable tbody');
        tbody.innerHTML = '';
        if(data.length===0){
            // add empty row
            addRow();
        } else {
            data.forEach((r,i)=>{
                addRow(r);
            });
        }
    })
});

function addRow(rate={}){
    const tbody = document.querySelector('#rateTable tbody');
    const tr = document.createElement('tr');
    tr.classList.add('rate-row');

    const index = tbody.children.length;

    tr.innerHTML = `
        <td>${index+1}</td>
        <td>
            <select name="rates[${index}][spherical]">
                ${numbers.map(n=>`<option value="${n}" ${rate.spherical==n?'selected':''}>${n}</option>`).join('')}
            </select>
        </td>
        <td>
            <select name="rates[${index}][cylinder]">
                ${numbers.map(n=>`<option value="${n}" ${rate.cylinder==n?'selected':''}>${n}</option>`).join('')}
            </select>
        </td>
        <td><input type="number" step="0.01" name="rates[${index}][rate]" value="${rate.rate||''}" required></td>
        <td>
            <button type="button" class="add-row">+</button>
            <button type="button" class="remove-row">-</button>
        </td>
    `;
    tbody.appendChild(tr);
}

// Add / Remove row buttons
document.addEventListener('click', e=>{
    if(e.target.classList.contains('add-row')) addRow();
    if(e.target.classList.contains('remove-row')){
        const rows = document.querySelectorAll('.rate-row');
        if(rows.length>1) e.target.closest('tr').remove();
        else alert('At least one row required.');
    }
});
</script>
</body>
</html>
