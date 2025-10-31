<?php
require 'includes/session.php';
require 'includes/db.php';

// fetch suppliers & varieties
$suppliers = $pdo->query("SELECT id,name FROM suppliers ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
$varieties = $pdo->query("SELECT DISTINCT variety_name FROM glass_powers ORDER BY variety_name")->fetchAll(PDO::FETCH_ASSOC);

// edit mode
$id = (int)($_GET['id'] ?? 0);
$sale = null;
$sale_items = [];
if($id){
    $stmt = $pdo->prepare("SELECT * FROM sales WHERE id=?");
    $stmt->execute([$id]);
    $sale = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("SELECT * FROM sale_items WHERE sale_id=?");
    $stmt->execute([$id]);
    $sale_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title><?= $id?'Edit':'Add' ?> Sale | AT Optical ERP</title>
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
.add-row{background:#28a745;color:#fff}
.remove-row{background:#dc3545;color:#fff}
</style>
</head>
<body>
<div class="topbar">
  <div><strong>AT Optical ERP</strong> — <?= $id?'Edit':'Add' ?> Sale</div>
  <nav>
    <a href="dashboard.php" style="color:#fff;text-decoration:none;margin-right:12px">Dashboard</a>
    <a href="sale_view.php" style="color:#fff;text-decoration:none">Sales</a>
  </nav>
</div>

<div class="container">
  <h2><?= $id?'Edit':'Add' ?> Sale</h2>

  <form method="POST" action="sale_submit.php" id="saleForm">
    <input type="hidden" name="id" value="<?= $sale['id']??'' ?>">

    <label><strong>Supplier</strong></label>
    <select name="supplier_id" required>
      <option value="">-- select supplier --</option>
      <?php foreach($suppliers as $s): ?>
        <option value="<?= $s['id'] ?>" <?= ($sale && $s['id']==$sale['supplier_id'])?'selected':'' ?>>
          <?= htmlspecialchars($s['name']) ?>
        </option>
      <?php endforeach; ?>
    </select>

    <table id="saleTable">
      <thead>
        <tr>
          <th>Type</th>
          <th>Item (Glass/Other)</th>
          <th>Spherical</th>
          <th>Cylinder</th>
          <th>Qty</th>
          <th>Rate</th>
          <th>Amount</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php
        if($sale_items){
            foreach($sale_items as $i => $item){
                ?>
                <tr class="item-row">
                  <td>
                    <select name="items[<?= $i ?>][type]" class="item_type">
                      <option value="">Select</option>
                      <option value="Glass" <?= $item['item_type']=='Glass'?'selected':'' ?>>Glass</option>
                      <option value="Other" <?= $item['item_type']=='Other'?'selected':'' ?>>Other</option>
                    </select>
                  </td>
                  <td class="item_name_cell">
                    <?php if($item['item_type']=='Glass'){ ?>
                      <select name="items[<?= $i ?>][name]" class="item_name">
                        <option value="">-- select --</option>
                        <?php foreach($varieties as $v){ ?>
                          <option value="<?= $v['variety_name'] ?>" <?= $v['variety_name']==$item['item_name']?'selected':'' ?>><?= $v['variety_name'] ?></option>
                        <?php } ?>
                      </select>
                    <?php } else { ?>
                      <input type="text" name="items[<?= $i ?>][name]" value="<?= htmlspecialchars($item['item_name']) ?>" class="item_name_text">
                    <?php } ?>
                  </td>
                  <td><input type="text" name="items[<?= $i ?>][spherical]" class="spherical" value="<?= $item['spherical'] ?>"></td>
                  <td><input type="text" name="items[<?= $i ?>][cylinder]" class="cylinder" value="<?= $item['cylinder'] ?>"></td>
                  <td><input type="number" name="items[<?= $i ?>][quantity]" class="quantity" value="<?= $item['quantity'] ?>"></td>
                  <td><input type="number" name="items[<?= $i ?>][rate]" class="rate" step="0.01" value="<?= $item['rate'] ?>"></td>
                  <td><input type="text" name="items[<?= $i ?>][amount]" class="amount" readonly value="<?= $item['amount'] ?>"></td>
                  <td>
                    <button type="button" class="add-row">+</button>
                    <button type="button" class="remove-row">-</button>
                  </td>
                </tr>
                <?php
            }
        } else {
            // default empty row
            ?>
            <tr class="item-row">
              <td>
                <select name="items[0][type]" class="item_type">
                  <option value="">Select</option>
                  <option value="Glass">Glass</option>
                  <option value="Other">Other</option>
                </select>
              </td>
              <td class="item_name_cell"></td>
              <td><select name="items[0][spherical]" class="spherical"><option value="">--</option></select></td>
              <td><select name="items[0][cylinder]" class="cylinder"><option value="">--</option></select></td>
              <td><input type="number" name="items[0][quantity]" class="quantity" value="1"></td>
              <td><input type="number" name="items[0][rate]" class="rate" step="0.01"></td>
              <td><input type="text" name="items[0][amount]" class="amount" readonly></td>
              <td>
                <button type="button" class="add-row">+</button>
                <button type="button" class="remove-row">-</button>
              </td>
            </tr>
        <?php } ?>
      </tbody>
    </table>

    <div style="margin-top:12px;">
      <label>Paid Amount</label>
      <input type="number" name="paid_amount" id="paid_amount" step="0.01" class="small" value="<?= $sale['paid_amount']??0 ?>">
      <label style="margin-left:12px">Remaining</label>
      <input type="text" name="remaining_amount" id="remaining" readonly class="small" value="<?= $sale['remaining_amount']??0 ?>">
    </div>

    <div style="margin-top:14px;">
      <label>Note</label>
      <input type="text" name="note" value="<?= htmlspecialchars($sale['note']??'') ?>" style="width:60%">
    </div>

    <div style="margin-top:14px;">
      <button type="submit" class="save"><?= $id?'Update':'Add' ?> Sale</button>
    </div>
  </form>
</div>

<script>
const varieties = <?php echo json_encode(array_column($varieties,'variety_name')); ?>;
let rowIndex = <?= count($sale_items)??1 ?>;

// add/remove row & type switch
document.addEventListener('click', e=>{
  if(e.target.classList.contains('add-row')){
    const tbody = document.querySelector('#saleTable tbody');
    const clone = document.querySelector('.item-row').cloneNode(true);
    clone.querySelectorAll('input,select').forEach(el=>{
      if(el.name) el.name = el.name.replace(/\[\d+\]/,'['+rowIndex+']');
      if(el.tagName==='INPUT') el.value = (el.classList.contains('quantity')?'1':'');
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

document.addEventListener('change', e=>{
  if(e.target.classList.contains('item_type')){
    const tr = e.target.closest('tr');
    const cell = tr.querySelector('.item_name_cell');
    cell.innerHTML = '';
    if(e.target.value==='Glass'){
      const sel = document.createElement('select');
      sel.name = tr.querySelector('input,select').name; // keep same
      sel.className='item_name';
      sel.innerHTML='<option value="">-- select --</option>';
      varieties.forEach(v=>{
        const opt = document.createElement('option'); opt.value=v; opt.textContent=v; sel.appendChild(opt);
      });
      cell.appendChild(sel);
      tr.querySelector('.spherical').innerHTML='<option value="">--</option>';
      tr.querySelector('.cylinder').innerHTML='<option value="">--</option>';
    } else if(e.target.value==='Other'){
      const input = document.createElement('input');
      input.type='text'; input.name=tr.querySelector('input,select').name; input.placeholder='Enter item name'; input.className='item_name_text';
      cell.appendChild(input);
      tr.querySelector('.spherical').innerHTML='<option value="">--</option>';
      tr.querySelector('.cylinder').innerHTML='<option value="">--</option>';
    }
  }

  if(e.target.classList.contains('item_name')){
    const tr = e.target.closest('tr');
    const sph = tr.querySelector('.spherical');
    const cyl = tr.querySelector('.cylinder');
    const v = e.target.value;
    sph.innerHTML='<option value="">Loading...</option>';
    cyl.innerHTML='<option value="">Loading...</option>';
    fetch('fetch_glass_data.php?v='+encodeURIComponent(v))
      .then(r=>r.json())
      .then(data=>{
        sph.innerHTML='<option value="">--</option>';
        cyl.innerHTML='<option value="">--</option>';
        (data.spherical||[]).forEach(val=>{
          const opt=document.createElement('option');
          opt.value=parseFloat(val).toFixed(2); opt.textContent=parseFloat(val).toFixed(2);
          sph.appendChild(opt);
        });
        (data.cylinder||[]).forEach(val=>{
          const opt=document.createElement('option');
          opt.value=parseFloat(val).toFixed(2); opt.textContent=parseFloat(val).toFixed(2);
          cyl.appendChild(opt);
        });
      });
  }

  // recalc per row
  if(e.target.classList.contains('quantity') || e.target.classList.contains('rate')){
    const tr=e.target.closest('tr');
    const q=parseFloat(tr.querySelector('.quantity').value||0);
    const r=parseFloat(tr.querySelector('.rate').value||0);
    tr.querySelector('.amount').value=(q*r).toFixed(2);
    calcRemaining();
  }
});

document.getElementById('paid_amount').addEventListener('input', calcRemaining);

function calcRemaining(){
  let total=0;
  document.querySelectorAll('.amount').forEach(a=>total+=parseFloat(a.value||0));
  const paid=parseFloat(document.getElementById('paid_amount').value||0);
  document.getElementById('remaining').value=(total-paid).toFixed(2);
}
</script>
</body>
</html>
