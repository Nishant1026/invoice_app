<?php
// ✅ Database Connection
$conn = new mysqli("localhost", "root", "", "invoice_app");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize messages
$message = "";

// -------------------
// Handle General Settings
// -------------------
$year_start = $year_end = $line_items = "";

$generalResult = $conn->query("SELECT * FROM general_settings LIMIT 1");
if ($generalResult && $generalResult->num_rows > 0) {
    $generalRow = $generalResult->fetch_assoc();
    $year_start = $generalRow['year_start'];
    $year_end = $generalRow['year_end'];
    $line_items = $generalRow['line_items'];
}

// -------------------
// Handle Payment Settings
// -------------------
$currencySymbol = '₹';
$currencyPosition = 'left';
$thousandSeparator = ',';
$decimalSeparator = '.';
$numberOfDecimals = 2;
$paymentPage = 'payment';
$paymentPageFooter = 'Thanks for choosing <a href="https://ultrakeyit.com" target="_blank">Ultrakey IT Solutions Private Limited</a> | <a href="mailto:support@ultrakeyit.com">support@ultrakeyit.com</a>';
$bankDetails = '';
$genericPayment = 'Pay Invoice amount via one of the options mentioned below:<br><a href="https://pages.razorpay.com/ultrakeyitinvoices" target="_blank">1. Click here for Online Payment through Razorpay</a>';

$paymentResult = $conn->query("SELECT * FROM payment_settings WHERE id=1 LIMIT 1");
if ($paymentResult && $paymentResult->num_rows > 0) {
    $paymentRow = $paymentResult->fetch_assoc();
    $currencySymbol = $paymentRow['currency_symbol'];
    $currencyPosition = $paymentRow['currency_position'];
    $thousandSeparator = $paymentRow['thousand_separator'];
    $decimalSeparator = $paymentRow['decimal_separator'];
    $numberOfDecimals = $paymentRow['number_of_decimals'];
    $paymentPage = $paymentRow['payment_page'];
    $paymentPageFooter = $paymentRow['payment_page_footer'];
    $bankDetails = $paymentRow['bank_details'];
    $genericPayment = $paymentRow['generic_payment'];
}

// -------------------
// Handle Form Submission
// -------------------
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // General Settings
    if(isset($_POST['year_start'])) {
        $year_start = $_POST['year_start'];
        $year_end = $_POST['year_end'];
        $line_items = $_POST['line_items'];

        $check = $conn->query("SELECT * FROM general_settings LIMIT 1");
        if ($check && $check->num_rows > 0) {
            $conn->query("UPDATE general_settings SET year_start='$year_start', year_end='$year_end', line_items='$line_items'");
        } else {
            $conn->query("INSERT INTO general_settings (year_start, year_end, line_items) VALUES ('$year_start', '$year_end', '$line_items')");
        }
        $message .= "✅ General settings saved successfully!<br>";
    }

    // Payment Settings
    if(isset($_POST['currencySymbol'])) {
        $currencySymbol = $_POST['currencySymbol'];
        $currencyPosition = $_POST['currencyPosition'];
        $thousandSeparator = $_POST['thousandSeparator'];
        $decimalSeparator = $_POST['decimalSeparator'];
        $numberOfDecimals = (int)$_POST['numberOfDecimals'];
        $paymentPage = $_POST['paymentPage'];
        $paymentPageFooter = $_POST['paymentPageFooter'];
        $bankDetails = $_POST['bankDetails'];
        $genericPayment = $_POST['genericPayment'];

        $check = $conn->query("SELECT id FROM payment_settings LIMIT 1");
        if ($check && $check->num_rows > 0) {
            $stmt = $conn->prepare("UPDATE payment_settings SET 
                currency_symbol=?, currency_position=?, thousand_separator=?, decimal_separator=?, number_of_decimals=?, payment_page=?, payment_page_footer=?, bank_details=?, generic_payment=? WHERE id=1");
            $stmt->bind_param("ssssissss", $currencySymbol, $currencyPosition, $thousandSeparator, $decimalSeparator, $numberOfDecimals, $paymentPage, $paymentPageFooter, $bankDetails, $genericPayment);
            $stmt->execute();
            $stmt->close();
        } else {
            $stmt = $conn->prepare("INSERT INTO payment_settings 
                (currency_symbol, currency_position, thousand_separator, decimal_separator, number_of_decimals, payment_page, payment_page_footer, bank_details, generic_payment)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssissss", $currencySymbol, $currencyPosition, $thousandSeparator, $decimalSeparator, $numberOfDecimals, $paymentPage, $paymentPageFooter, $bankDetails, $genericPayment);
            $stmt->execute();
            $stmt->close();
        }
        $message .= "✅ Payment settings saved successfully!";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Payment Settings</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { font-family: Arial, sans-serif; background: #f8f9fa; margin: 0; padding: 20px; }
    .container-tab { max-width: 900px; margin: auto; background: #fff; padding: 20px 40px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-top: 20px; }
    h2 { text-align: center; margin-bottom: 30px; color: #0078d7; }
    label { font-weight: bold; margin-top: 15px; display: block; color: #333; }
    input[type="text"], input[type="number"], select, textarea { width: 100%; padding: 10px; margin-top: 5px; border: 1px solid #ccc; border-radius: 5px; font-size: 14px; }
    textarea { resize: vertical; height: 100px; }
    .button { display: inline-block; margin-top: 15px; padding: 10px 18px; background-color: #0078d7; color: white; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; }
    .button:hover { background-color: #005fa3; }
    .section-title { font-size: 18px; margin-top: 25px; border-bottom: 1px solid #ddd; padding-bottom: 5px; color: #444; }
    .nav-tabs .nav-link { cursor: pointer; }
    .message { padding: 10px 15px; background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; border-radius: 5px; margin-bottom: 15px; }
  </style>
</head>
<body>
  <div class="container-tab">
    <h2>Payment Settings</h2>

    <?php if(isset($message)) echo "<div class='message'>{$message}</div>"; ?>

    <!-- Tabs -->
    <ul class="nav nav-tabs mb-4" id="settingsTabs" role="tablist">
      <li class="nav-item"><a class="nav-link" href="index.php">General</a></li>
      <li class="nav-item"><a class="nav-link" href="business.php">Business</a></li>
      <li class="nav-item"><a class="nav-link" href="quotes.php">Quotes</a></li>
      <li class="nav-item"><a class="nav-link" href="invoice.php">Invoices</a></li>
      <li class="nav-item"><button class="nav-link active" id="payment-tab" type="button">Payments</button></li>
      <li class="nav-item"><a class="nav-link" href="tax.php">Tax</a></li>
      <li class="nav-item"><a class="nav-link" href="email.php">Emails</a></li>
      <!-- <li class="nav-item"><a class="nav-link" href="pdf.php">PDF</a></li>
      <li class="nav-item"><a class="nav-link" href="translate.php">Translate</a></li> -->
    </ul>

    <form method="POST">
      <label for="currencySymbol">Currency Symbol</label>
      <input type="text" id="currencySymbol" name="currencySymbol" value="<?php echo htmlspecialchars($currencySymbol); ?>">

      <label for="currencyPosition">Currency Position</label>
      <select id="currencyPosition" name="currencyPosition">
        <option value="left" <?php if($currencyPosition=='left') echo 'selected'; ?>>Left (₹100.00)</option>
        <option value="right" <?php if($currencyPosition=='right') echo 'selected'; ?>>Right (100.00₹)</option>
        <option value="left_space" <?php if($currencyPosition=='left_space') echo 'selected'; ?>>Left with space (₹ 100.00)</option>
        <option value="right_space" <?php if($currencyPosition=='right_space') echo 'selected'; ?>>Right with space (100.00 ₹)</option>
      </select>

      <label for="thousandSeparator">Thousand Separator</label>
      <input type="text" id="thousandSeparator" name="thousandSeparator" value="<?php echo htmlspecialchars($thousandSeparator); ?>">

      <label for="decimalSeparator">Decimal Separator</label>
      <input type="text" id="decimalSeparator" name="decimalSeparator" value="<?php echo htmlspecialchars($decimalSeparator); ?>">

      <label for="numberOfDecimals">Number of Decimals</label>
      <input type="number" id="numberOfDecimals" name="numberOfDecimals" value="<?php echo htmlspecialchars($numberOfDecimals); ?>" min="0" max="4">

      <label for="paymentPage">Payment Page</label>
      <input type="text" id="paymentPage" name="paymentPage" value="<?php echo htmlspecialchars($paymentPage); ?>">

      <label for="paymentPageFooter">Payment Page Footer</label>
      <textarea id="paymentPageFooter" name="paymentPageFooter"><?php echo htmlspecialchars($paymentPageFooter); ?></textarea>

      <div class="section-title">Payment Methods</div>

      <label for="bankDetails">Bank (Displayed on Invoice)</label>
      <textarea id="bankDetails" name="bankDetails"><?php echo htmlspecialchars($bankDetails); ?></textarea>

      <label for="genericPayment">Generic Payment (Displayed on Invoice)</label>
      <textarea id="genericPayment" name="genericPayment"><?php echo htmlspecialchars($genericPayment); ?></textarea>

      <button class="button" type="submit">💾 Save Settings</button>
      <a class="button" href="index.php">Back to General</a>
    </form>
  </div>
</body>
</html>
