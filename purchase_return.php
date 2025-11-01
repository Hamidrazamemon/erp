<?php
require 'includes/session.php';
require 'includes/db.php';

// fetch purchases
$purchases = $pdo->query("SELECT id,purchase_no FROM purchases ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Purchase Return | AT Optical ERP</title>
<style>
body { font-family:Arial,sans-serif; background:#f4f6f8; margin:0; }
.topbar { background:#0078d7; color:#fff; padding:12px 20px; display:flex; justify-content:space-between; align-items:center; }
.topbar nav a { color:#fff; text-decoration:none; margin-left:14px; font-weight:bold; }
.topbar nav a:hover { text-decoration:underline; }
.container{max-width:1200px;margin:20px auto;background:#fff;padding:18px;border-radius:8px;box-shadow:0 0 8px rgba(0,0,0,.08)}
table{width:100%;border-collapse:collapse}
th,td{border:1px solid #ddd;padding:8px;text-align:center}
th{background:#0078d7;color:#fff}
select,input{padding:6px;border:1px solid #ccc;border-radius:4px;width:100%}
button{padding:7px 12px;border:none;border-radius:4px;cursor:pointer}
.small{width:120px}
.hidden{display:none}
</style>
</head>
<body>
<div class="topbar">
  <div><strong>AT Optical ERP</strong> — Purchase Return</div>
  <nav>
    <a href="dashboard.php">Dashboard</a>
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

<div class="container">
  <h2>Purchase Return</h2>

  <form id="returnForm" method="POST" action="purchase_return_submit.php">
    <label>Select Purchase</label>
    <select name="purchase_id" id="purchase_id" required>
      <option value="">-- Select Purchase --</option>
      <?php foreach($purchases as $p): ?>
        <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['purchase_no']) ?></option>
      <?php endforeach; ?>
    </select>

    <table id="returnTable">
      <thead>
        <tr>
          <th>Item</th>
          <th>Type</th>
          <th>Spherical</th>
          <th>Cylinder</th>
          <th>Addition</th>
          <th>Axis</th>
          <th>Purchased Qty</th>
          <th>Return Qty</th>
          <th>Rate</th>
          <th>Amount</th>
        </tr>
      </thead>
      <tbody>
        <!-- Items will be loaded via JS -->
      </tbody>
    </table>

    <div style="margin-top:12px;">
      <label>Refund Amount</label>
      <input type="text" id="refund_amount" readonly class="small">
    </div>

    <div style="margin-top:14px;">
      <label>Note</label>
      <input type="text" name="note" style="width:60%">
    </div>

    <div style="margin-top:14px;">
      <button type="submit" style="background:#0078d7;color:#fff">Submit Return</button>
    </div>
  </form>
</div>

<script>
document.getElementById('purchase_id').addEventListener('change', function(){
    const purchaseId = this.value;
    const tbody = document.querySelector('#returnTable tbody');
    tbody.innerHTML = '';
    document.getElementById('refund_amount').value = '';

    if(!purchaseId) return;

    fetch('fetch_purchase_items.php?id='+purchaseId)
    .then(r=>r.json())
    .then(items=>{
        items.forEach((it,index)=>{
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${it.item_name}</td>
                <td>${it.item_type}</td>
                <td>${it.spherical??''}</td>
                <td>${it.cylinder??''}</td>
                <td>${it.addition??''}</td>
                <td>${it.axis??''}</td>
                <td>${it.quantity}</td>
                <td><input type="number" name="items[${index}][return_qty]" value="0" max="${it.quantity}" class="return_qty"></td>
                <td><input type="number" name="items[${index}][rate]" value="${it.rate}" step="0.01" class="rate"></td>
                <td><input type="text" name="items[${index}][amount]" class="amount" readonly></td>
                <input type="hidden" name="items[${index}][item_id]" value="${it.id}">
            `;
            tbody.appendChild(tr);
        });
    });
});

// calculate amount on qty/rate change
document.addEventListener('input', e=>{
    if(e.target.classList.contains('return_qty') || e.target.classList.contains('rate')){
        const tr = e.target.closest('tr');
        const qty = parseFloat(tr.querySelector('.return_qty').value||0);
        const rate = parseFloat(tr.querySelector('.rate').value||0);
        tr.querySelector('.amount').value = (qty*rate).toFixed(2);
        calcRefund();
    }
});

function calcRefund(){
    let total=0;
    document.querySelectorAll('.amount').forEach(a=>{ total += parseFloat(a.value||0); });
    document.getElementById('refund_amount').value = total.toFixed(2);
}
</script>
</body>
</html>
