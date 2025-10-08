<?php
// ---------- DB CONNECTION ----------
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "invoice_app";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// ---------- CREATE TABLE IF NOT EXISTS ----------
$createTable = "
CREATE TABLE IF NOT EXISTS tax_settings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tax_name VARCHAR(50) DEFAULT 'GST',
  tax_rate DECIMAL(5,2) DEFAULT 18.00,
  tax_type ENUM('exclusive', 'inclusive') DEFAULT 'exclusive',
  enable_tax TINYINT(1) DEFAULT 1,
  show_tax_number TINYINT(1) DEFAULT 1,
  gst_number VARCHAR(30) DEFAULT NULL,
  pan_number VARCHAR(20) DEFAULT NULL,
  tax_note TEXT DEFAULT NULL,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";
$conn->query($createTable);

// ---------- HANDLE FORM SUBMISSION ----------
$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $taxName = $_POST['taxName'];
  $taxRate = $_POST['taxRate'];
  $taxType = $_POST['taxType'];
  $enableTax = isset($_POST['enableTax']) ? 1 : 0;
  $showTaxNumber = isset($_POST['showTaxNumber']) ? 1 : 0;
  $gstNumber = $_POST['gstNumber'];
  $panNumber = $_POST['panNumber'];
  $taxNote = $_POST['taxNote'];

  // Check if record exists
  $check = $conn->query("SELECT * FROM tax_settings LIMIT 1");

  if ($check->num_rows > 0) {
    // Update existing record
    $sql = "UPDATE tax_settings SET 
            tax_name='$taxName',
            tax_rate='$taxRate',
            tax_type='$taxType',
            enable_tax='$enableTax',
            show_tax_number='$showTaxNumber',
            gst_number='$gstNumber',
            pan_number='$panNumber',
            tax_note='$taxNote'
            WHERE id=1";
    $conn->query($sql);
    $message = " Tax settings updated successfully!";
  } else {
    // Insert new record
    $sql = "INSERT INTO tax_settings 
            (tax_name, tax_rate, tax_type, enable_tax, show_tax_number, gst_number, pan_number, tax_note)
            VALUES ('$taxName', '$taxRate', '$taxType', '$enableTax', '$showTaxNumber', '$gstNumber', '$panNumber', '$taxNote')";
    $conn->query($sql);
    $message = " Tax settings saved successfully!";
  }
}

// ---------- FETCH EXISTING SETTINGS ----------
$result = $conn->query("SELECT * FROM tax_settings LIMIT 1");
$settings = $result->fetch_assoc() ?? [
  'tax_name' => 'GST',
  'tax_rate' => '18',
  'tax_type' => 'exclusive',
  'enable_tax' => 1,
  'show_tax_number' => 1,
  'gst_number' => '',
  'pan_number' => '',
  'tax_note' => ''
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Tax Settings</title>

  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f8f9fa;
      margin: 0;
      padding: 20px;
    }
    .container-tab {
      max-width: 900px;
      margin: auto;
      background: #fff;
      padding: 20px 40px;
      border-radius: 10px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
      margin-top: 20px;
    }
    h2 {
      text-align: center;
      margin-bottom: 30px;
      color: #0078d7;
    }
    label {
      font-weight: bold;
      display: block;
      margin-top: 15px;
      color: #333;
    }
    input[type="text"],
    input[type="number"],
    select,
    textarea {
      width: 100%;
      padding: 10px;
      margin-top: 5px;
      border: 1px solid #ccc;
      border-radius: 5px;
      font-size: 14px;
    }
    textarea {
      height: 80px;
      resize: vertical;
    }
    .button {
      display: inline-block;
      margin-top: 15px;
      padding: 10px 18px;
      background-color: #0078d7;
      color: white;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      text-decoration: none;
    }
    .button:hover {
      background-color: #005fa3;
    }
    .nav-tabs .nav-link {
      cursor: pointer;
    }
    .form-check-label {
      margin-left: 5px;
    }
  </style>
</head>

<body>
  <div class="container-tab">
    <h2>Tax Settings</h2>

    <?php if ($message): ?>
      <div class="alert alert-success"><?= $message ?></div>
    <?php endif; ?>

    <!-- Tabs -->
    <ul class="nav nav-tabs mb-4" id="settingsTabs" role="tablist">
      <li class="nav-item"><a class="nav-link" href="index.php">General</a></li>
      <li class="nav-item"><a class="nav-link" href="business.php">Business</a></li>
      <li class="nav-item"><a class="nav-link" href="quotes.php">Quotes</a></li>
      <li class="nav-item"><a class="nav-link" href="invoice.php">Invoices</a></li>
      <li class="nav-item"><a class="nav-link" href="payment.php">Payments</a></li>
      <li class="nav-item"><button class="nav-link active" id="tax-tab" data-bs-toggle="tab" 
        data-bs-target="#tax" type="button" role="tab">Tax</button></li>
      <li class="nav-item"><a class="nav-link" href="email.php">Emails</a></li>
      <!-- <li class="nav-item"><a class="nav-link" href="pdf.php">PDF</a></li>
      <li class="nav-item"><a class="nav-link" href="translate.php">Translate</a></li> -->
    </ul>

    <!-- Tax Tab Content -->
    <div class="tab-content">
      <div class="tab-pane fade show active" id="tax" role="tabpanel" aria-labelledby="tax-tab">
        <form method="POST" action="">
          <label for="taxName">Tax Name</label>
          <input type="text" id="taxName" name="taxName" value="<?= $settings['tax_name'] ?>">

          <label for="taxRate">Default Tax Rate (%)</label>
          <input type="number" id="taxRate" name="taxRate" value="<?= $settings['tax_rate'] ?>" min="0" max="100">

          <label for="taxType">Tax Type</label>
          <select id="taxType" name="taxType">
            <option value="exclusive" <?= ($settings['tax_type'] == 'exclusive') ? 'selected' : '' ?>>Exclusive (added to price)</option>
            <option value="inclusive" <?= ($settings['tax_type'] == 'inclusive') ? 'selected' : '' ?>>Inclusive (included in price)</option>
          </select>

          <div class="form-check mt-3">
            <input type="checkbox" class="form-check-input" id="enableTax" name="enableTax" <?= ($settings['enable_tax']) ? 'checked' : '' ?>>
            <label class="form-check-label" for="enableTax">Enable Tax on Invoices</label>
          </div>

          <div class="form-check">
            <input type="checkbox" class="form-check-input" id="showTaxNumber" name="showTaxNumber" <?= ($settings['show_tax_number']) ? 'checked' : '' ?>>
            <label class="form-check-label" for="showTaxNumber">Show GSTIN/PAN on Invoice</label>
          </div>

          <label for="gstNumber">GSTIN</label>
          <input type="text" id="gstNumber" name="gstNumber" value="<?= $settings['gst_number'] ?>" placeholder="Enter your GST number">

          <label for="panNumber">PAN Number</label>
          <input type="text" id="panNumber" name="panNumber" value="<?= $settings['pan_number'] ?>" placeholder="Enter your PAN number">

          <label for="taxNote">Tax Note (Optional)</label>
          <textarea id="taxNote" name="taxNote" placeholder="Add any tax-related note"><?= $settings['tax_note'] ?></textarea>

          <button class="button" type="submit">💾 Save Settings</button>
          <a class="button" href="index.php">Back to General</a>
        </form>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
