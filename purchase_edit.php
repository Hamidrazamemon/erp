<?php
session_start();
require 'includes/db.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: index.php");
    exit;
}
$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    header("Location: purchase_view.php");
    exit;
}

// fetch purchase
$stmt = $pdo->prepare("SELECT * FROM purchases WHERE id=?");
$stmt->execute([$id]);
$purchase = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$purchase) {
    header("Location: purchase_view.php");
    exit;
}

// fetch purchase items
$item_stmt = $pdo->prepare("SELECT * FROM purchase_items WHERE purchase_id=?");
$item_stmt->execute([$id]);
$items = $item_stmt->fetchAll(PDO::FETCH_ASSOC);

// fetch suppliers & varieties
$suppliers = $pdo->query("SELECT id,name FROM suppliers ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
$varieties = $pdo->query("SELECT DISTINCT variety_name FROM glass_powers ORDER BY variety_name")->fetchAll(PDO::FETCH_COLUMN);

// handle form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();

        $supplier_id = (int)$_POST['supplier_id'];
        $note = trim($_POST['note'] ?? '');
        $paid_amount = floatval($_POST['paid_amount'] ?? 0);
        $items_post = $_POST['items'] ?? [];

        $grand_total = 0;
        foreach ($items_post as $it) {
            $grand_total += floatval($it['quantity'] ?? 0) * floatval($it['rate'] ?? 0);
        }
        $remaining_amount = max($grand_total - $paid_amount, 0);

        // update purchase (removed updated_at)
        $stmt = $pdo->prepare("
            UPDATE purchases 
            SET supplier_id=?, total_amount=?, paid_amount=?, remaining_amount=?, note=?
            WHERE id=?
        ");
        $stmt->execute([$supplier_id, $grand_total, $paid_amount, $remaining_amount, $note, $id]);

        // delete old items
        $pdo->prepare("DELETE FROM purchase_items WHERE purchase_id=?")->execute([$id]);

        // insert updated items
        $item_stmt = $pdo->prepare("
            INSERT INTO purchase_items
            (purchase_id, item_type, item_name, spherical, cylinder, addition, axis, quantity, rate, amount)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        foreach ($items_post as $it) {
            $amount = floatval($it['quantity'] ?? 0) * floatval($it['rate'] ?? 0);
            $item_stmt->execute([
                $id,
                $it['type'] ?? '',
                trim($it['name'] ?? ''),
                $it['spherical'] ?? null,
                $it['cylinder'] ?? null,
                $it['addition'] ?? null,
                $it['axis'] ?? null,
                floatval($it['quantity'] ?? 0),
                floatval($it['rate'] ?? 0),
                $amount
            ]);
        }

        $pdo->commit();
        header("Location: purchase_view.php?msg=Purchase Updated Successfully");
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        die("Error: " . $e->getMessage());
    }
}
?>

<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Edit Purchase | AT Optical ERP</title>
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
.add-row{background:#28a745;color:#fff}
.remove-row{background:#dc3545;color:#fff}
.small{width:120px}
.hidden{display:none}

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
  <div><strong>AT Optical ERP</strong> — Edit Purchase</div>
  <nav>
  <a href="dashboard.php">Dashboard</a>
  <a href="glass.php">Glasses</a>
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
    <button class="dropbtn" style="text-decoration:underline;">Report ▼</button>
    <div class="dropdown-content">
      <a href="report.php">Main Report</a>
      <a href="rate_list.php">Rate List</a>
       <a href="ledger.php">Ledger</a>
  <a href="stock.php">Stock</a>
  <a href="balance.php" style="text-decoration:underline;">Balance</a>

    </div>
  </div>

  <a href="logout.php" style="color:#ffdddd;">Logout</a>
</nav>
</div>

<div class="container">
  <h2>Edit Purchase</h2>

  <form id="purchaseForm" method="POST">
    <label>Select Supplier</label>
    <select name="supplier_id" id="supplier_id" required>
      <option value="">-- Select Supplier --</option>
      <?php foreach($suppliers as $s): ?>
        <option value="<?= $s['id'] ?>" <?= $s['id']==$purchase['supplier_id']?'selected':'' ?>><?= htmlspecialchars($s['name']) ?></option>
      <?php endforeach; ?>
    </select>

    <table id="purchaseTable">
      <thead>
        <tr>
          <th>Type</th>
          <th>Item</th>
          <th>Spherical</th>
          <th>Cylinder</th>
          <th>Addition</th>
          <th>Axis</th>
          <th>Qty</th>
          <th>Rate</th>
          <th>Amount</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($items as $index => $it): ?>
        <tr class="item-row">
          <td>
            <select class="item_type" name="items[<?= $index ?>][type]">
              <option value="">Select</option>
              <option value="Glass" <?= $it['item_type']=='Glass'?'selected':'' ?>>Glass</option>
              <option value="Other" <?= $it['item_type']=='Other'?'selected':'' ?>>Other</option>
            </select>
          </td>
          <td class="item_name_cell">
            <?php if($it['item_type']=='Glass'): ?>
            <select name="items[<?= $index ?>][name]" class="item_name">
              <option value="">-- Select --</option>
              <?php foreach($varieties as $v): ?>
                <option value="<?= $v ?>" <?= $v==$it['item_name']?'selected':'' ?>><?= $v ?></option>
              <?php endforeach; ?>
            </select>
            <?php else: ?>
            <input type="text" name="items[<?= $index ?>][name]" value="<?= htmlspecialchars($it['item_name']) ?>">
            <?php endif; ?>
          </td>
          <td><select name="items[<?= $index ?>][spherical]" class="spherical"><option value="<?= $it['spherical'] ?>"><?= $it['spherical'] ?></option></select></td>
          <td><select name="items[<?= $index ?>][cylinder]" class="cylinder"><option value="<?= $it['cylinder'] ?>"><?= $it['cylinder'] ?></option></select></td>
          <td><select name="items[<?= $index ?>][addition]" class="addition"><option value="<?= $it['addition'] ?>"><?= $it['addition'] ?></option></select></td>
          <td><select name="items[<?= $index ?>][axis]" class="axis"><option value="<?= $it['axis'] ?>"><?= $it['axis'] ?></option></select></td>
          <td><input type="number" name="items[<?= $index ?>][quantity]" class="quantity" value="<?= $it['quantity'] ?>" min="1"></td>
          <td><input type="number" name="items[<?= $index ?>][rate]" class="rate" value="<?= $it['rate'] ?>" step="0.01"></td>
          <td><input type="text" name="items[<?= $index ?>][amount]" class="amount" value="<?= $it['amount'] ?>" readonly></td>
          <td>
            <button type="button" class="add-row">+</button>
            <button type="button" class="remove-row">-</button>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <div style="margin-top:12px;">
      <label>Paid Amount</label>
      <input type="number" name="paid_amount" id="paid_amount" step="0.01" class="small" value="<?= $purchase['paid_amount'] ?>">
      <label style="margin-left:12px">Remaining</label>
      <input type="text" id="remaining" readonly class="small" value="<?= $purchase['remaining_amount'] ?>">
    </div>

    <div style="margin-top:14px;">
      <label>Note</label>
      <input type="text" name="note" style="width:60%" value="<?= htmlspecialchars($purchase['note']) ?>">
    </div>

    <div style="margin-top:14px;">
      <button type="submit" style="background:#0078d7;color:#fff">Update Purchase</button>
    </div>
  </form>
</div>

<script>
const varieties = <?= json_encode($varieties) ?>;
let rowIndex = <?= count($items) ?>;

// add/remove row buttons
document.addEventListener('click', e=>{
  if(e.target.classList.contains('add-row')){
    const tbody = document.querySelector('#purchaseTable tbody');
    const clone = document.querySelector('.item-row').cloneNode(true);
    clone.querySelectorAll('input,select').forEach(el=>{
      if(el.name) el.name = el.name.replace(/\[\d+\]/,'['+rowIndex+']');
      if(el.tagName==='INPUT') el.value = el.classList.contains('quantity')?'1':'';
      if(el.tagName==='SELECT') el.selectedIndex = 0;
    });
    clone.querySelector('.item_name_cell').innerHTML = '';
    tbody.appendChild(clone);
    rowIndex++;
  }
  if(e.target.classList.contains('remove-row')){
    const rows = document.querySelectorAll('.item-row');
    if(rows.length>1) e.target.closest('tr').remove();
  }
});

// item type change
document.addEventListener('change', e=>{
  const tr = e.target.closest('tr');
  if(e.target.classList.contains('item_type')){
    const cell = tr.querySelector('.item_name_cell');
    cell.innerHTML = '';
    if(e.target.value==='Glass'){
      const sel = document.createElement('select');
      sel.name = 'items['+getIndex(tr)+'][name]';
      sel.className='item_name';
      sel.innerHTML='<option value="">-- Select --</option>';
      varieties.forEach(v=>{
        const opt = document.createElement('option');
        opt.value=opt.textContent=v;
        sel.appendChild(opt);
      });
      cell.appendChild(sel);
    } else if(e.target.value==='Other'){
      const input=document.createElement('input');
      input.type='text';
      input.name='items['+getIndex(tr)+'][name]';
      input.placeholder='Enter item name';
      cell.appendChild(input);
    }
    ['spherical','cylinder','addition','axis'].forEach(cls=>{
      tr.querySelector('.'+cls).innerHTML='<option>--</option>';
    });
  }

  if(e.target.classList.contains('item_name')){
    const tr = e.target.closest('tr');
    fetch('fetch_glass_data.php?v='+encodeURIComponent(e.target.value))
    .then(r=>r.json())
    .then(data=>{
      ['spherical','cylinder','addition','axis'].forEach(cls=>{
        const sel = tr.querySelector('.'+cls);
        sel.innerHTML='<option>--</option>';
        (data[cls]||[]).forEach(v=>{
          const opt = document.createElement('option');
          opt.value=opt.textContent=v;
          sel.appendChild(opt);
        });
      });
    });
  }

  if(e.target.classList.contains('quantity') || e.target.classList.contains('rate')){
    const q = parseFloat(tr.querySelector('.quantity').value||0);
    const r = parseFloat(tr.querySelector('.rate').value||0);
    tr.querySelector('.amount').value=(q*r).toFixed(2);
    calcRemaining();
  }

  if(e.target.id==='paid_amount') calcRemaining();
});

function getIndex(tr){
  const any = tr.querySelector('[name]');
  const m = any.name.match(/\[(\d+)\]/);
  return m?m[1]:0;
}

function calcRemaining(){
  let total=0;
  document.querySelectorAll('.amount').forEach(a=>{ total+=parseFloat(a.value||0); });
  const paid=parseFloat(document.getElementById('paid_amount').value||0);
  document.getElementById('remaining').value=(total-paid).toFixed(2);
}
</script>
</body>
</html>
