<?php
require 'includes/session.php';
require 'includes/db.php';

// fetch suppliers & varieties
$suppliers = $pdo->query("SELECT id,name FROM suppliers ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
$varieties = $pdo->query("SELECT DISTINCT variety_name FROM glass_powers ORDER BY variety_name")->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Add Purchase | AT Optical ERP</title>
<style>
/* minimal styling */
body{font-family:Arial;background:#f4f6f8;margin:0}
.topbar{background:#0078d7;color:#fff;padding:12px 20px;display:flex;justify-content:space-between;align-items:center}
.container{max-width:1200px;margin:20px auto;background:#fff;padding:18px;border-radius:8px;box-shadow:0 0 8px rgba(0,0,0,.08)}
table{width:100%;border-collapse:collapse}
th,td{border:1px solid #ddd;padding:8px;text-align:center}
th{background:#0078d7;color:#fff}
select,input{padding:6px;border:1px solid #ccc;border-radius:4px;width:100%}
button{padding:7px 12px;border:none;border-radius:4px;cursor:pointer}
.add-row{background:#28a745;color:#fff}
.remove-row{background:#dc3545;color:#fff}
.hidden{display:none}
.small{width:120px}
</style>
</head>
<body>
<div class="topbar">
  <div><strong>AT Optical ERP</strong> — Add Purchase</div>
  <nav>
    <a href="dashboard.php" style="color:#fff;text-decoration:none;margin-right:12px">Dashboard</a>
    <a href="purchase_view.php" style="color:#fff;text-decoration:none">Purchases</a>
    <a href="logout.php" style="color:#ffdddd;text-decoration:none;margin-left:12px">Logout</a>
  </nav>
</div>

<div class="container">
  <h2>Add Purchase</h2>

  <!-- step 1: supplier -->
  <div id="step1">
    <label><strong>Select Supplier</strong></label>
    <select id="supplierSelect">
      <option value="">-- select supplier --</option>
      <?php foreach($suppliers as $s): ?>
        <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option>
      <?php endforeach; ?>
    </select>
    <button id="nextBtn" style="margin-left:10px;background:#0078d7;color:#fff">Next</button>
  </div>

  <!-- step 2: form -->
  <form id="purchaseForm" class="hidden" method="POST" action="purchase_submit.php">
    <input type="hidden" name="supplier_id" id="supplier_id">

    <table id="purchaseTable">
      <thead>
        <tr>
          <th>Type</th>
          <th>Item (Glass / Other)</th>
          <th>Spherical</th>
          <th>Cylinder</th>
          <th>Qty</th>
          <th>Rate</th>
          <th>Amount</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <tr class="item-row">
          <td>
            <select name="items[0][type]" class="item_type">
              <option value="">Select</option>
              <option value="Glass">Glass</option>
              <option value="Other">Other</option>
            </select>
          </td>

          <td class="item_name_cell">
            <!-- JS will insert either select (Glass) or input (Other) here -->
          </td>

          <td><select name="items[0][spherical]" class="spherical"><option value="">--</option></select></td>
          <td><select name="items[0][cylinder]" class="cylinder"><option value="">--</option></select></td>

          <td><input type="number" name="items[0][quantity]" class="quantity" value="1" min="1"></td>
          <td><input type="number" name="items[0][rate]" class="rate" step="0.01"></td>
          <td><input type="text" name="items[0][amount]" class="amount" readonly></td>
          <td>
            <button type="button" class="add-row add-row-btn">+</button>
            <button type="button" class="remove-row remove-row-btn">-</button>
          </td>
        </tr>
      </tbody>
    </table>

    <div style="margin-top:12px;">
      <label>Paid Amount</label>
      <input type="number" name="paid_amount" id="paid_amount" step="0.01" class="small">
      <label style="margin-left:12px">Remaining</label>
      <input type="text" id="remaining" readonly class="small">
    </div>

    <div style="margin-top:14px;">
      <label>Note</label>
      <input type="text" name="note" style="width:60%">
    </div>

    <div style="margin-top:14px;">
      <button type="submit" style="background:#0078d7;color:#fff">Submit Purchase</button>
    </div>
  </form>
</div>
<script>
const varieties = <?php echo json_encode(array_column($varieties,'variety_name')); ?>;
let rowIndex = 1;

// Next button -> show form
document.getElementById('nextBtn').addEventListener('click', () => {
  const s = document.getElementById('supplierSelect').value;
  if(!s) { alert('Please select supplier'); return; }
  document.getElementById('supplier_id').value = s;
  document.getElementById('step1').classList.add('hidden');
  document.getElementById('purchaseForm').classList.remove('hidden');
});

// add/remove rows
document.addEventListener('click', (e) => {
  if(e.target.classList.contains('add-row-btn')){
    const tbody = document.querySelector('#purchaseTable tbody');
    const clone = document.querySelector('.item-row').cloneNode(true);
    clone.querySelectorAll('input,select').forEach(el=>{
      if(el.name) el.name = el.name.replace(/\[\d+\]/, '['+rowIndex+']');
      if(el.tagName==='INPUT') el.value = (el.classList.contains('quantity')? '1' : '');
      if(el.tagName==='SELECT') el.selectedIndex = 0;
    });
    clone.querySelector('.item_name_cell').innerHTML = '';
    tbody.appendChild(clone);
    rowIndex++;
  }

  if(e.target.classList.contains('remove-row-btn')){
    const rows = document.querySelectorAll('.item-row');
    if(rows.length>1){
      e.target.closest('tr').remove();
      calcRemaining();
    } else alert('At least one row required.');
  }
});

// handle type change (Glass/Other) & item_name selection
document.addEventListener('change', (e) => {
  const tr = e.target.closest('tr');

  // Type change
  if(e.target.classList.contains('item_type')){
    const cell = tr.querySelector('.item_name_cell');
    cell.innerHTML = '';
    if(e.target.value==='Glass'){
      const sel = document.createElement('select');
      sel.name = 'items['+getIndex(tr)+'][name]';
      sel.className = 'item_name';
      sel.innerHTML = '<option value="">-- select --</option>';
      varieties.forEach(v=>{
        const opt = document.createElement('option');
        opt.value = v;
        opt.textContent = v;
        sel.appendChild(opt);
      });
      cell.appendChild(sel);
      tr.querySelector('.spherical').innerHTML = '<option value="">--</option>';
      tr.querySelector('.cylinder').innerHTML = '<option value="">--</option>';
    } else if(e.target.value==='Other'){
      const input = document.createElement('input');
      input.type = 'text';
      input.name = 'items['+getIndex(tr)+'][name]';
      input.placeholder = 'Enter item name';
      input.className = 'item_name_text';
      cell.appendChild(input);
      tr.querySelector('.spherical').innerHTML = '<option value="">--</option>';
      tr.querySelector('.cylinder').innerHTML = '<option value="">--</option>';
    }
  }

  // Glass variety selected -> fetch ± values
  if(e.target.classList.contains('item_name')){
    const sph = tr.querySelector('.spherical');
    const cyl = tr.querySelector('.cylinder');
    const v = e.target.value;
    sph.innerHTML = '<option>Loading...</option>';
    cyl.innerHTML = '<option>Loading...</option>';
    fetch('fetch_glass_data.php?v=' + encodeURIComponent(v))
      .then(r=>r.json())
      .then(data=>{
        sph.innerHTML = '<option value="">--</option>';
        cyl.innerHTML = '<option value="">--</option>';
        (data.spherical||[]).forEach(val=>{
          const opt = document.createElement('option');
          opt.value = val; // keep + / - sign
          opt.textContent = val;
          sph.appendChild(opt);
        });
        (data.cylinder||[]).forEach(val=>{
          const opt = document.createElement('option');
          opt.value = val;
          opt.textContent = val;
          cyl.appendChild(opt);
        });
      }).catch(err=>{
        sph.innerHTML = '<option value="">--</option>';
        cyl.innerHTML = '<option value="">--</option>';
        console.error(err);
      });
  }

  // Qty/Rate change -> recalc amount
  if(e.target.classList.contains('quantity') || e.target.classList.contains('rate')){
    const q = parseFloat(tr.querySelector('.quantity').value||0);
    const r = parseFloat(tr.querySelector('.rate').value||0);
    tr.querySelector('.amount').value = (q*r).toFixed(2);
    calcRemaining();
  }

  // Paid amount change -> recalc remaining
  if(e.target.id==='paid_amount') calcRemaining();
});

// helper: get row index from name
function getIndex(tr){
  const any = tr.querySelector('[name]');
  if(any && any.name){
    const m = any.name.match(/\[(\d+)\]/);
    if(m) return m[1];
  }
  return 0;
}

// calc total remaining
function calcRemaining(){
  let total = 0;
  document.querySelectorAll('.amount').forEach(a=>{
    total += parseFloat(a.value||0);
  });
  const paid = parseFloat(document.getElementById('paid_amount').value||0);
  document.getElementById('remaining').value = (total-paid).toFixed(2);
}
</script>

</body>
</html>
