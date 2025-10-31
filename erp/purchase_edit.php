<?php
require 'includes/session.php';
require 'includes/db.php';

$id = (int)($_GET['id'] ?? 0);
if(!$id){
  header('Location: purchase_view.php');
  exit;
}

// fetch purchase
$stmt = $pdo->prepare("SELECT * FROM purchases WHERE id=?");
$stmt->execute([$id]);
$purchase = $stmt->fetch(PDO::FETCH_ASSOC);
if(!$purchase){
  header('Location: purchase_view.php');
  exit;
}

// suppliers & varieties
$suppliers = $pdo->query("SELECT id,name FROM suppliers ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
$varieties = $pdo->query("SELECT DISTINCT variety_name FROM glass_powers ORDER BY variety_name")->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Edit Purchase | AT Optical ERP</title>
<style>
body{font-family:Arial;background:#f4f6f8;margin:0}
.topbar{background:#0078d7;color:#fff;padding:12px 20px;display:flex;justify-content:space-between;align-items:center}
.container{max-width:1200px;margin:20px auto;background:#fff;padding:18px;border-radius:8px;box-shadow:0 0 8px rgba(0,0,0,.08)}
table{width:100%;border-collapse:collapse;margin-top:10px}
th,td{border:1px solid #ddd;padding:8px;text-align:center}
th{background:#0078d7;color:#fff}
select,input{padding:6px;border:1px solid #ccc;border-radius:4px;width:100%}
button{padding:8px 14px;border:none;border-radius:4px;cursor:pointer}
button.save{background:#0078d7;color:#fff}
.hidden{display:none}
.small{width:120px}
</style>
</head>
<body>
<div class="topbar">
  <div><strong>AT Optical ERP</strong> — Edit Purchase</div>
  <nav>
    <a href="dashboard.php" style="color:#fff;text-decoration:none;margin-right:12px">Dashboard</a>
    <a href="purchase_view.php" style="color:#fff;text-decoration:none">Purchases</a>
  </nav>
</div>

<div class="container">
  <h2>Edit Purchase #<?= htmlspecialchars($purchase['purchase_no']) ?></h2>

  <form method="POST" action="purchase_update.php" id="purchaseForm">
    <input type="hidden" name="id" value="<?= $purchase['id'] ?>">

    <label><strong>Supplier</strong></label>
    <select name="supplier_id" required>
      <option value="">-- select supplier --</option>
      <?php foreach($suppliers as $s): ?>
        <option value="<?= $s['id'] ?>" <?= $s['id']==$purchase['supplier_id']?'selected':'' ?>>
          <?= htmlspecialchars($s['name']) ?>
        </option>
      <?php endforeach; ?>
    </select>

    <table>
      <thead>
        <tr>
          <th>Type</th>
          <th>Item</th>
          <th>Spherical</th>
          <th>Cylinder</th>
          <th>Qty</th>
          <th>Rate</th>
          <th>Total</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>
            <select name="item_type" id="itemType">
              <option value="Glass" <?= $purchase['item_type']=='Glass'?'selected':'' ?>>Glass</option>
              <option value="Other" <?= $purchase['item_type']=='Other'?'selected':'' ?>>Other</option>
            </select>
          </td>
          <td>
            <!-- Glass dropdown -->
            <select name="item_name" id="glassSelect" <?= $purchase['item_type']=='Other'?'class="hidden"':'' ?>>
              <option value="">-- Select --</option>
              <?php foreach($varieties as $v): ?>
                <option value="<?= htmlspecialchars($v['variety_name']) ?>" <?= $v['variety_name']==$purchase['item_name']?'selected':'' ?>>
                  <?= htmlspecialchars($v['variety_name']) ?>
                </option>
              <?php endforeach; ?>
            </select>

            <!-- Other input -->
            <input type="text" name="item_name_other" id="otherInput" value="<?= htmlspecialchars($purchase['item_name']) ?>" <?= $purchase['item_type']=='Glass'?'class="hidden"':'' ?>>
          </td>

          <td><input type="text" name="spherical" id="spherical" value="<?= htmlspecialchars($purchase['spherical']) ?>"></td>
          <td><input type="text" name="cylinder" id="cylinder" value="<?= htmlspecialchars($purchase['cylinder']) ?>"></td>
          <td><input type="number" name="quantity" id="quantity" value="<?= htmlspecialchars($purchase['quantity']) ?>"></td>
          <td><input type="number" name="rate" id="rate" step="0.01" value="<?= htmlspecialchars($purchase['rate']) ?>"></td>
          <td><input type="text" id="amount" readonly value="<?= number_format($purchase['total_amount'],2) ?>"></td>
        </tr>
      </tbody>
    </table>

    <div style="margin-top:12px;">
      <label>Paid Amount</label>
      <input type="number" name="paid_amount" id="paid" step="0.01" value="<?= htmlspecialchars($purchase['paid_amount']) ?>" class="small">
      <label style="margin-left:12px">Remaining</label>
      <input type="text" name="remaining_amount" id="remaining" value="<?= htmlspecialchars($purchase['remaining_amount']) ?>" readonly class="small">
    </div>

    <div style="margin-top:14px;">
      <label>Note</label>
      <input type="text" name="note" value="<?= htmlspecialchars($purchase['note']) ?>" style="width:60%">
    </div>

    <div style="margin-top:14px;">
      <button type="submit" class="save">Update Purchase</button>
    </div>
  </form>
</div>

<script>
const itemType = document.getElementById('itemType');
const glassSelect = document.getElementById('glassSelect');
const otherInput = document.getElementById('otherInput');
const rate = document.getElementById('rate');
const qty = document.getElementById('quantity');
const paid = document.getElementById('paid');
const amount = document.getElementById('amount');
const remaining = document.getElementById('remaining');
const form = document.getElementById('purchaseForm');

// switch Glass ↔ Other
itemType.addEventListener('change', () => {
  if(itemType.value === 'Glass'){
    glassSelect.classList.remove('hidden');
    otherInput.classList.add('hidden');
    otherInput.name = 'item_name_other';
    glassSelect.name = 'item_name';
  } else {
    glassSelect.classList.add('hidden');
    otherInput.classList.remove('hidden');
    glassSelect.name = 'item_name_hidden';
    otherInput.name = 'item_name';
  }
});

// auto calculate total & remaining
function recalc(){
  const q = parseFloat(qty.value) || 0;
  const r = parseFloat(rate.value) || 0;
  const total = q * r;
  amount.value = total.toFixed(2);

  const p = parseFloat(paid.value) || 0;
  remaining.value = (total - p).toFixed(2);
}

[qty, rate, paid].forEach(i => i.addEventListener('input', recalc));

// initial calculation on load
recalc();
</script>
</body>
</html>
