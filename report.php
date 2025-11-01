<?php
session_start();
require 'includes/db.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Reports | AT Optical ERP</title>
<style>
body {
  background:#f4f6f8;
  font-family:Arial,Helvetica,sans-serif;
  margin:0;
}
.topbar {
  background:#0078d7;
  color:#fff;
  padding:12px 20px;
  display:flex;
  justify-content:space-between;
  align-items:center;
}
.topbar nav a {
  color:#fff;
  text-decoration:none;
  margin-left:14px;
  font-weight:bold;
}
.topbar nav a:hover { text-decoration:underline; }

.container {
  max-width:800px;
  margin:40px auto;
  background:#fff;
  padding:30px 25px;
  border-radius:10px;
  box-shadow:0 0 12px rgba(0,0,0,0.08);
  text-align:center;
}
h2 {
  color:#0078d7;
  margin-top:0;
  font-size:24px;
  text-align:center;
  border-bottom:2px solid #0078d7;
  padding-bottom:10px;
  margin-bottom:25px;
}

form {
  display:flex;
  flex-direction:column;
  align-items:center;
  gap:18px;
}
form label {
  font-weight:bold;
  color:#333;
  margin-bottom:3px;
}

.select-group {
  display:flex;
  gap:15px;
  flex-wrap:wrap;
  justify-content:center;
}

select, input[type="date"] {
  padding:10px 12px;
  border:1px solid #ccc;
  border-radius:5px;
  font-size:14px;
  min-width:200px;
  background:#fff;
}

button {
  background:#0078d7;
  color:#fff;
  border:none;
  padding:10px 25px;
  border-radius:6px;
  cursor:pointer;
  font-weight:bold;
  font-size:15px;
  margin-top:10px;
}
button:hover { background:#005fa3; }

.hidden { display:none; }

#custom_dates {
  display:flex;
  flex-wrap:wrap;
  gap:15px;
  justify-content:center;
  align-items:center;
  background:#f9f9f9;
  padding:10px 15px;
  border-radius:6px;
  border:1px solid #ddd;
}

.footer-note {
  margin-top:30px;
  font-size:13px;
  color:#777;
  text-align:center;
}
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
<script>
function toggleDates() {
  const type = document.getElementById('duration').value;
  const custom = document.getElementById('custom_dates');
  custom.style.display = (type === 'custom') ? 'flex' : 'none';
}
</script>
</head>
<body>

<div class="topbar">
  <div><strong>AT Optical ERP</strong> | Reports</div>
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
      <a href="report.php" style="text-decoration:underline;">Main Report</a>
      <a href="rate_list.php">Rate List</a>
       <a href="ledger.php">Ledger</a>
  <a href="stock.php">Stock</a>
  <a href="balance.php" >Balance</a>

    </div>
  </div>

  <a href="logout.php" style="color:#ffdddd;">Logout</a>
</nav>
</div>

<div class="container">
  <h2>Generate Sales / Purchase Report</h2>

  <form method="POST" action="report_view.php">
    <div class="select-group">
      <div>
        <label>Report Type</label><br>
        <select name="report_type" id="report_type" required>
          <option value="">-- Select Type --</option>
          <option value="sales">Sales Report</option>
          <option value="purchase">Purchase Report</option>
        </select>
      </div>

      <div>
        <label>Duration</label><br>
        <select name="duration" id="duration" onchange="toggleDates()" required>
          <option value="">-- Select Duration --</option>
          <option value="daily">Daily</option>
          <option value="monthly">Monthly</option>
          <option value="annual">Annual</option>
          <option value="custom">Custom Range</option>
        </select>
      </div>
    </div>

    <div id="custom_dates" class="hidden">
      <div>
        <label>From:</label><br>
        <input type="date" name="from_date">
      </div>
      <div>
        <label>To:</label><br>
        <input type="date" name="to_date">
      </div>
    </div>

    <button type="submit">View Report</button>
  </form>

  <div class="footer-note">
    <p>Select report type and duration, or use a custom date range to generate detailed summaries.</p>
  </div>
</div>

</body>
</html>
