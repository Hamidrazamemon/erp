<?php
require 'includes/session.php';
require 'includes/db.php';

// fetch sales
$sales = $pdo->query("SELECT id, sale_no FROM sales ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Add Sale Return | AT Optical ERP</title>
<style>
body{font-family:Arial,sans-serif;background:#f4f6f8;margin:0;}
.topbar {
  background:#0078d7; color:#fff; padding:12px 20px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap;
}
nav a, .dropbtn {
  color:#fff; text-decoration:none; font-weight:bold; padding:6px 10px; border:none; background:none; cursor:pointer;
}
nav a:hover, .dropbtn:hover { text-decoration:underline; }
.dropdown { position:relative; display:inline-block; }
.dropdown-content {
  display:none; position:absolute; background:#fff; min-width:180px; box-shadow:0 4px 8px rgba(0,0,0,0.2); z-index:1; border-radius:6px; overflow:hidden;
}
.dropdown-content a { color:#0078d7; padding:10px 14px; text-decoration:none; display:block; }
.dropdown-content a:hover { background:#f1f1f1; }
.dropdown:hover .dropdown-content { display:block; }

.container{max-width:1200px;margin:20px auto;background:#fff;padding:18px;border-radius:8px;box-shadow:0 0 8px rgba(0,0,0,.08)}
table{width:100%;border-collapse:collapse;margin-top:15px}
th,td{border:1px solid #ddd;padding:8px;text-align:center}
th{background:#0078d7;color:#fff}
select,input{padding:6px;border:1px solid #ccc;border-radius:4px;width:100%;}
button{padding:6px 10px;border:none;border-radius:4px;cursor:pointer;}
.add-row{background:#28a745;color:#fff;}
.remove-row{background:#dc3545;color:#fff;}
h2{color:#0078d7;margin-top:0;margin-bottom:15px;}
</style>
</head>
<body>

<div class="topbar">
  <div><strong>AT Optical ERP</strong></div>
  <nav>
    <a href="dashboard.php">Dashboard</a>
    <a href="glass.php">Glasses</a>
    <a href="view_glass.php">View Glass</a>
    <a href="suppliers.php">Suppliers</a>
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
<h2>Add Sale Return</h2>

<form id="returnForm" method="POST" action="sale_return_submit.php">
<label>Select Sale</label>
<select name="sale_id" id="sale_id" required>
    <option value="">-- Select Sale --</option>
    <?php foreach($sales as $s): ?>
    <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['sale_no']) ?></option>
    <?php endforeach; ?>
</select>

<table id="returnTable">
<thead>
<tr>
<th>Item</th>
<th>Type</th>
<th>Sph</th>
<th>Cyl</th>
<th>Add</th>
<th>Axis</th>
<th>Qty</th>
<th>Rate</th>
<th>Amount</th>
<th>Action</th>
</tr>
</thead>
<tbody></tbody>
</table>

<div style="margin-top:12px;">
<label>Note</label>
<input type="text" name="note" style="width:50%">
</div>

<div style="margin-top:12px;">
<button type="submit" style="background:#0078d7;color:#fff;">Submit Return</button>
</div>
</form>
</div>

<script>
// fetch sale items when sale is selected
document.getElementById('sale_id').addEventListener('change', function(){
    const saleId = this.value;
    const tbody = document.querySelector('#returnTable tbody');
    tbody.innerHTML = '';
    if(!saleId) return;
    fetch('fetch_sale_items.php?sale_id='+saleId)
    .then(r=>r.json())
    .then(data=>{
        data.forEach((item, index)=>{
            const tr = document.createElement('tr');
            tr.innerHTML = `
<td>${item.item_name}<input type="hidden" name="items[${index}][sale_item_id]" value="${item.id}"></td>
<td>${item.item_type}</td>
<td>${item.spherical}</td>
<td>${item.cylinder}</td>
<td>${item.addition}</td>
<td>${item.axis}</td>
<td><input type="number" name="items[${index}][quantity]" value="0" min="0" class="qty"></td>
<td><input type="number" name="items[${index}][rate]" value="${item.rate}" class="rate"></td>
<td><input type="text" name="items[${index}][amount]" value="0" readonly class="amount"></td>
<td><button type="button" class="remove-row">-</button></td>`;
            tbody.appendChild(tr);
        });
    });
});

// calculate amount when qty/rate changes
document.addEventListener('input', e=>{
    if(e.target.classList.contains('qty') || e.target.classList.contains('rate')){
        const tr = e.target.closest('tr');
        const qty = parseFloat(tr.querySelector('.qty').value||0);
        const rate = parseFloat(tr.querySelector('.rate').value||0);
        tr.querySelector('.amount').value = (qty*rate).toFixed(2);
    }
});

// remove row
document.addEventListener('click', e=>{
    if(e.target.classList.contains('remove-row')){
        e.target.closest('tr').remove();
    }
});
</script>

</body>
</html>
