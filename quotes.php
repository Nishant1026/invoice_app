<?php
// ✅ Database Connection
$conn = new mysqli("localhost", "root", "", "invoice_app");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ✅ Handle Form Submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $prefix = $_POST['prefix'];
    $suffix = $_POST['suffix'];
    $autoIncrement = isset($_POST['autoIncrement']) ? 1 : 0;
    $nextNumber = $_POST['nextNumber'];
    $validDays = $_POST['validDays'];
    $hideAdjust = isset($_POST['hideAdjust']) ? 1 : 0;
    $action = $_POST['action'];
    $acceptText = $_POST['acceptText'];
    $acceptedMessage = $_POST['acceptedMessage'];
    $declineRequired = isset($_POST['declineRequired']) ? 1 : 0;
    $declinedMessage = $_POST['declinedMessage'];

    // Check if record exists
    $check = $conn->query("SELECT id FROM quote_settings LIMIT 1");
    if ($check->num_rows > 0) {
        $conn->query("UPDATE quote_settings SET 
            prefix='$prefix',
            suffix='$suffix',
            auto_increment=$autoIncrement,
            next_number=$nextNumber,
            valid_days=$validDays,
            hide_adjust=$hideAdjust,
            action='$action',
            accept_text='$acceptText',
            accepted_message='$acceptedMessage',
            decline_required=$declineRequired,
            declined_message='$declinedMessage'
            WHERE id=1");
    } else {
        $conn->query("INSERT INTO quote_settings 
            (prefix, suffix, auto_increment, next_number, valid_days, hide_adjust, action, accept_text, accepted_message, decline_required, declined_message) 
            VALUES 
            ('$prefix', '$suffix', $autoIncrement, $nextNumber, $validDays, $hideAdjust, '$action', '$acceptText', '$acceptedMessage', $declineRequired, '$declinedMessage')");
    }

    $message = "Settings saved successfully!";
}

// ✅ Fetch latest saved data
$result = $conn->query("SELECT * FROM quote_settings LIMIT 1");
$data = $result->fetch_assoc();

$prefix = $data['prefix'] ?? 'AKEYQ-';
$suffix = $data['suffix'] ?? '';
$autoIncrement = $data['auto_increment'] ?? 1;
$nextNumber = $data['next_number'] ?? 1;
$validDays = $data['valid_days'] ?? 30;
$hideAdjust = $data['hide_adjust'] ?? 0;
$action = $data['action'] ?? 'Convert Quote to Invoice and send to client';
$acceptText = $data['accept_text'] ?? 'Thank you for accepting the quote.';
$acceptedMessage = $data['accepted_message'] ?? 'Quote has been accepted successfully.';
$declineRequired = $data['decline_required'] ?? 0;
$declinedMessage = $data['declined_message'] ?? 'Quote has been declined.';
?>


<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Quotes Settings</title>

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
    textarea,
    select {
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
    <h2>Quotes Settings</h2>

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
        <button class="nav-link active" id="quotes-tab" data-bs-toggle="tab" data-bs-target="#quotes" type="button"
          role="tab" aria-controls="quotes" aria-selected="true">Quotes</button>
      </li>
      <li class="nav-item" role="presentation">
        <a class="nav-link" href="index.php#invoices" role="tab">Invoices</a>
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

    <!-- Quotes Tab Content -->
    <div class="tab-content">
      <div class="tab-pane fade show active" id="quotes" role="tabpanel" aria-labelledby="quotes-tab">
        <form method="POST">
          <label for="prefix">Prefix</label>
          <input type="text" id="prefix" name="prefix" placeholder="Enter prefix (e.g. AKEYQ-)" value="<?php echo htmlspecialchars($prefix); ?>">

          <label for="suffix">Suffix</label>
          <input type="text" id="suffix" name="suffix" placeholder="Enter suffix (optional)" value="<?php echo htmlspecialchars($suffix); ?>">

          <div class="form-check">
            <input type="checkbox" class="form-check-input" id="autoIncrement" name="autoIncrement" <?php if($autoIncrement) echo 'checked'; ?>>
            <label class="form-check-label" for="autoIncrement">Yes, increment Quote numbers by one.</label>
          </div>

          <label for="nextNumber">Next Number</label>
          <input type="number" id="nextNumber" name="nextNumber" value="<?php echo htmlspecialchars($nextNumber); ?>">

          <label for="validDays">Quotes Valid For (days)</label>
          <input type="number" id="validDays" name="validDays" value="<?php echo htmlspecialchars($validDays); ?>">

          <div class="form-check">
            <input type="checkbox" class="form-check-input" id="hideAdjust" name="hideAdjust" <?php if($hideAdjust) echo 'checked'; ?>>
            <label class="form-check-label" for="hideAdjust">Yes, hide the Adjust field on line items.</label>
          </div>

          <h5 style="margin-top: 20px;">Accepted Quote Settings</h5>

          <label for="action">Accepted Quote Action</label>
          <select id="action" name="action">
            <option <?php if($action=="Convert Quote to Invoice and send to client") echo "selected"; ?>>Convert Quote to Invoice and send to client</option>
            <option <?php if($action=="Mark as Accepted Only") echo "selected"; ?>>Mark as Accepted Only</option>
            <option <?php if($action=="Send Confirmation Email") echo "selected"; ?>>Send Confirmation Email</option>
          </select>

          <label for="acceptText">Accept Quote Text</label>
          <textarea id="acceptText" name="acceptText"><?php echo htmlspecialchars($acceptText); ?></textarea>

          <label for="acceptedMessage">Accepted Quote Message</label>
          <textarea id="acceptedMessage" name="acceptedMessage"><?php echo htmlspecialchars($acceptedMessage); ?></textarea>

          <div class="form-check">
            <input type="checkbox" class="form-check-input" id="declineRequired" name="declineRequired" <?php if($declineRequired) echo 'checked'; ?>>
            <label class="form-check-label" for="declineRequired">Make "Reason for declining" field required.</label>
          </div>

          <label for="declinedMessage">Declined Quote Message</label>
          <textarea id="declinedMessage" name="declinedMessage"><?php echo htmlspecialchars($declinedMessage); ?></textarea>

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
