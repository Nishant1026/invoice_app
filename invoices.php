<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "invoice_app";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize variables
$prefix = $suffix = $autoIncrement = $nextNumber = $dueDate = $hideAdjust = $terms = $footer = $customCSS = "";

// Fetch existing settings
$result = $conn->query("SELECT * FROM invoice_settings WHERE id=1 LIMIT 1");
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $prefix = $row['prefix'];
    $suffix = $row['suffix'];
    $autoIncrement = $row['auto_increment'];
    $nextNumber = $row['next_number'];
    $dueDate = $row['due_date'];
    $hideAdjust = $row['hide_adjust'];
    $terms = $row['terms'];
    $footer = $row['footer'];
    $customCSS = $row['custom_css'];
}

// Save settings
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $prefix = $_POST['prefix'];
    $suffix = $_POST['suffix'];
    $autoIncrement = isset($_POST['autoIncrement']) ? 1 : 0;
    $nextNumber = $_POST['nextNumber'];
    $dueDate = $_POST['dueDate'];
    $hideAdjust = isset($_POST['hideAdjust']) ? 1 : 0;
    $terms = $_POST['terms'];
    $footer = $_POST['footer'];
    $customCSS = $_POST['customCSS'];

    $stmt = $conn->prepare("UPDATE invoice_settings SET prefix=?, suffix=?, auto_increment=?, next_number=?, due_date=?, hide_adjust=?, terms=?, footer=?, custom_css=? WHERE id=1");
    $stmt->bind_param("ssiiissss", $prefix, $suffix, $autoIncrement, $nextNumber, $dueDate, $hideAdjust, $terms, $footer, $customCSS);
    $stmt->execute();
    $stmt->close();

    $message = "Settings saved successfully!";
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Invoices Settings</title>

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
    textarea {
      width: 100%;
      padding: 10px;
      margin-top: 5px;
      border: 1px solid #ccc;
      border-radius: 5px;
      font-size: 14px;
    }

    textarea {
      height: 100px;
      resize: vertical;
    }

    .button {
      display: inline-block;
      margin-top: 10px;
      padding: 8px 15px;
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

    .message {
      padding: 10px 15px;
      background-color: #d4edda;
      color: #155724;
      border: 1px solid #c3e6cb;
      border-radius: 5px;
      margin-bottom: 15px;
    }
  </style>
</head>

<body>
  <div class="container-tab">
    <h2>Invoices Settings</h2>

    <!-- Show success message -->
    <?php if (isset($message)) { echo "<div class='message'>{$message}</div>"; } ?>

    <!-- Tabs -->
    <ul class="nav nav-tabs mb-4" id="settingsTabs" role="tablist">
      <li class="nav-item" role="presentation">
        <a class="nav-link" href="index.php" role="tab">General</a>
      </li>
      <li class="nav-item" role="presentation">
        <a class="nav-link" href="business.php" role="tab">Business</a>
      </li>
      <li class="nav-item" role="presentation">
        <a class="nav-link" href="quotes.php" role="tab">Quotes</a>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link active" id="invoices-tab" data-bs-toggle="tab" data-bs-target="#invoices" type="button"
          role="tab" aria-controls="invoices" aria-selected="true">Invoices</button>
      </li>
      <li class="nav-item" role="presentation">
        <a class="nav-link" href="index.php#payments" role="tab">Payments</a>
      </li>
      <li class="nav-item" role="presentation">
        <a class="nav-link" href="index.php#tax" role="tab">Tax</a>
      </li>
      <li class="nav-item" role="presentation">
        <a class="nav-link" href="index.php#emails" role="tab">Emails</a>
      </li>
      <!-- <li class="nav-item" role="presentation">
        <a class="nav-link" href="index.php#pdf" role="tab">PDF</a>
      </li>
      <li class="nav-item" role="presentation">
        <a class="nav-link" href="index.php#translate" role="tab">Translate</a>
      </li> -->
    </ul>

    <!-- Invoices Tab Content -->
    <div class="tab-content">
      <div class="tab-pane fade show active" id="invoices" role="tabpanel" aria-labelledby="invoices-tab">
        <form method="POST">
          <label for="prefix">Prefix</label>
          <input type="text" id="prefix" name="prefix" placeholder="Enter prefix (e.g. AKEY-)" value="<?php echo htmlspecialchars($prefix); ?>">

          <label for="suffix">Suffix</label>
          <input type="text" id="suffix" name="suffix" placeholder="Enter suffix (optional)" value="<?php echo htmlspecialchars($suffix); ?>">

          <div class="form-check">
            <input type="checkbox" class="form-check-input" id="autoIncrement" name="autoIncrement" <?php if($autoIncrement) echo 'checked'; ?>>
            <label class="form-check-label" for="autoIncrement">Yes, increment Invoice numbers by one. Recommended.</label>
          </div>

          <label for="nextNumber">Next Number</label>
          <input type="text" id="nextNumber" name="nextNumber" value="<?php echo htmlspecialchars($nextNumber); ?>">

          <label for="dueDate">Due Date (in days)</label>
          <input type="number" id="dueDate" name="dueDate" value="<?php echo htmlspecialchars($dueDate); ?>">

          <div class="form-check">
            <input type="checkbox" class="form-check-input" id="hideAdjust" name="hideAdjust" <?php if($hideAdjust) echo 'checked'; ?>>
            <label class="form-check-label" for="hideAdjust">Yes, hide the Adjust field on line items.</label>
          </div>

          <label for="terms">Terms & Conditions</label>
          <textarea id="terms" name="terms"><?php echo htmlspecialchars($terms); ?></textarea>

          <label for="footer">Footer</label>
          <textarea id="footer" name="footer"><?php echo htmlspecialchars($footer); ?></textarea>

          <label for="customCSS">Custom CSS</label>
          <textarea id="customCSS" name="customCSS"><?php echo htmlspecialchars($customCSS); ?></textarea>

          <button class="button" type="submit">💾 Save Settings</button>
          <br><br>
          <a class="button" href="index.php">Back to General</a>
        </form>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
