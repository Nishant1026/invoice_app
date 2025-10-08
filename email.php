<?php
// ✅ Database Connection
$conn = new mysqli("localhost", "root", "", "invoice_app");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ✅ Create table if not exists
$conn->query("
CREATE TABLE IF NOT EXISTS email_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email_address VARCHAR(255),
    email_name VARCHAR(255),
    bcc_client_emails TINYINT(1),
    quote_subject TEXT,
    quote_content TEXT,
    quote_button_text VARCHAR(255),
    invoice_subject TEXT,
    invoice_content TEXT,
    invoice_button_text VARCHAR(255),
    payment_subject TEXT,
    payment_content TEXT,
    reminder_subject TEXT,
    reminder_content TEXT,
    footer_text TEXT
)
");

// ✅ Initialize default variables
$emailAddress = "support@ultrakeyit.com";
$emailName = "Ultrakey IT Solutions Private Limited";
$bccClientEmails = 1;
$quoteSubject = "New Quote %{number%} available";
$quoteContent = "Hi %{client_first_name%},\n\nYou have a new quote available (%{number%}) which can be viewed at %{url%}.";
$quoteButtonText = "View this quote online";
$invoiceSubject = "New Invoice %{number%} available";
$invoiceContent = "Hi %{client_first_name%},\n\nYou have a new invoice available (%{number%}) which can be viewed at %{url%}.";
$invoiceButtonText = "View this invoice online";
$paymentSubject = "Thanks for your payment!";
$paymentContent = "Thank you for your payment, %{client_first_name%}.\n\nYour recent payment for %{last_payment%} on invoice %{number%} has been successful.";
$reminderSubject = "A friendly reminder";
$reminderContent = "Hi %{client_first_name%},\n\nThis is a friendly reminder that your invoice %{number%} is now due. You can view it on %{url%}.";
$footerText = "© 2025-2026 Ultrakey IT Solutions Private Limited. All rights reserved.";

$message = "";

// ✅ Fetch existing settings
$result = $conn->query("SELECT * FROM email_settings WHERE id=1 LIMIT 1");
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $emailAddress = $row['email_address'];
    $emailName = $row['email_name'];
    $bccClientEmails = $row['bcc_client_emails'];
    $quoteSubject = $row['quote_subject'];
    $quoteContent = $row['quote_content'];
    $quoteButtonText = $row['quote_button_text'];
    $invoiceSubject = $row['invoice_subject'];
    $invoiceContent = $row['invoice_content'];
    $invoiceButtonText = $row['invoice_button_text'];
    $paymentSubject = $row['payment_subject'];
    $paymentContent = $row['payment_content'];
    $reminderSubject = $row['reminder_subject'];
    $reminderContent = $row['reminder_content'];
    $footerText = $row['footer_text'];
}

// ✅ Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $emailAddress = $_POST['emailAddress'];
    $emailName = $_POST['emailName'];
    $bccClientEmails = isset($_POST['bccClientEmails']) ? 1 : 0;
    $quoteSubject = $_POST['quoteSubject'];
    $quoteContent = $_POST['quoteContent'];
    $quoteButtonText = $_POST['quoteButtonText'];
    $invoiceSubject = $_POST['invoiceSubject'];
    $invoiceContent = $_POST['invoiceContent'];
    $invoiceButtonText = $_POST['invoiceButtonText'];
    $paymentSubject = $_POST['paymentSubject'];
    $paymentContent = $_POST['paymentContent'];
    $reminderSubject = $_POST['reminderSubject'];
    $reminderContent = $_POST['reminderContent'];
    $footerText = $_POST['footerText'];

    $check = $conn->query("SELECT id FROM email_settings WHERE id=1");
    if ($check && $check->num_rows > 0) {
        $stmt = $conn->prepare("UPDATE email_settings SET 
            email_address=?, email_name=?, bcc_client_emails=?, 
            quote_subject=?, quote_content=?, quote_button_text=?,
            invoice_subject=?, invoice_content=?, invoice_button_text=?,
            payment_subject=?, payment_content=?, 
            reminder_subject=?, reminder_content=?, footer_text=?
            WHERE id=1");
        $stmt->bind_param(
            "ssisssssssssss",
            $emailAddress, $emailName, $bccClientEmails,
            $quoteSubject, $quoteContent, $quoteButtonText,
            $invoiceSubject, $invoiceContent, $invoiceButtonText,
            $paymentSubject, $paymentContent,
            $reminderSubject, $reminderContent, $footerText
        );
        $stmt->execute();
        $stmt->close();
    } else {
        $stmt = $conn->prepare("INSERT INTO email_settings 
            (email_address, email_name, bcc_client_emails, 
            quote_subject, quote_content, quote_button_text, 
            invoice_subject, invoice_content, invoice_button_text, 
            payment_subject, payment_content, 
            reminder_subject, reminder_content, footer_text)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param(
            "ssisssssssssss",
            $emailAddress, $emailName, $bccClientEmails,
            $quoteSubject, $quoteContent, $quoteButtonText,
            $invoiceSubject, $invoiceContent, $invoiceButtonText,
            $paymentSubject, $paymentContent,
            $reminderSubject, $reminderContent, $footerText
        );
        $stmt->execute();
        $stmt->close();
    }

    $message = "✅ Email settings saved successfully!";
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Email Settings</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { font-family: Arial, sans-serif; background: #f8f9fa; margin: 0; padding: 20px; }
    .container-tab { max-width: 900px; margin: auto; background: #fff; padding: 20px 40px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-top: 20px; }
    h2 { text-align: center; margin-bottom: 30px; color: #0078d7; }
    label { font-weight: bold; display: block; margin-top: 15px; color: #333; }
    input[type="text"], input[type="email"], textarea { width: 100%; padding: 10px; margin-top: 5px; border: 1px solid #ccc; border-radius: 5px; font-size: 14px; }
    textarea { height: 100px; resize: vertical; }
    .button { display: inline-block; margin-top: 10px; padding: 8px 15px; background-color: #0078d7; color: white; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; }
    .button:hover { background-color: #005fa3; }
    .nav-tabs .nav-link { cursor: pointer; }
    .section-title { font-size: 18px; margin-top: 30px; margin-bottom: 10px; border-bottom: 1px solid #ccc; padding-bottom: 5px; color: #0078d7; }
    .message { padding: 10px 15px; background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; border-radius: 5px; margin-bottom: 15px; }
  </style>
</head>

<body>
  <div class="container-tab">
    <h2>Email Settings</h2>
    <?php if($message) echo "<div class='message'>$message</div>"; ?>

    <ul class="nav nav-tabs mb-4">
      <li class="nav-item"><a class="nav-link" href="index.php">General</a></li>
      <li class="nav-item"><a class="nav-link" href="business.php">Business</a></li>
      <li class="nav-item"><a class="nav-link" href="quotes.php">Quotes</a></li>
      <li class="nav-item"><a class="nav-link" href="invoice.php">Invoices</a></li>
      <li class="nav-item"><a class="nav-link" href="payment.php">Payments</a></li>
      <li class="nav-item"><a class="nav-link" href="tax.php">Tax</a></li>
      <li class="nav-item"><button class="nav-link active" type="button">Emails</button></li>
      <!-- <li class="nav-item"><a class="nav-link" href="pdf.php">PDF</a></li>
      <li class="nav-item"><a class="nav-link" href="translate.php">Translate</a></li> -->
    </ul>

    <form method="POST">
      <label>Email Address</label>
      <input type="email" name="emailAddress" value="<?php echo htmlspecialchars($emailAddress); ?>">

      <label>Email Name</label>
      <input type="text" name="emailName" value="<?php echo htmlspecialchars($emailName); ?>">

      <div class="form-check mt-3">
        <input type="checkbox" class="form-check-input" name="bccClientEmails" <?php if($bccClientEmails) echo 'checked'; ?>>
        <label class="form-check-label">Send myself a copy of all client emails (Bcc)</label>
      </div>

      <div class="section-title">Quote Available</div>
      <label>Subject</label>
      <input type="text" name="quoteSubject" value="<?php echo htmlspecialchars($quoteSubject); ?>">
      <label>Content</label>
      <textarea name="quoteContent"><?php echo htmlspecialchars($quoteContent); ?></textarea>
      <label>Button Text</label>
      <input type="text" name="quoteButtonText" value="<?php echo htmlspecialchars($quoteButtonText); ?>">

      <div class="section-title">Invoice Available</div>
      <label>Subject</label>
      <input type="text" name="invoiceSubject" value="<?php echo htmlspecialchars($invoiceSubject); ?>">
      <label>Content</label>
      <textarea name="invoiceContent"><?php echo htmlspecialchars($invoiceContent); ?></textarea>
      <label>Button Text</label>
      <input type="text" name="invoiceButtonText" value="<?php echo htmlspecialchars($invoiceButtonText); ?>">

      <div class="section-title">Payment Received</div>
      <label>Subject</label>
      <input type="text" name="paymentSubject" value="<?php echo htmlspecialchars($paymentSubject); ?>">
      <label>Content</label>
      <textarea name="paymentContent"><?php echo htmlspecialchars($paymentContent); ?></textarea>

      <div class="section-title">Payment Reminder</div>
      <label>Reminder Subject</label>
      <input type="text" name="reminderSubject" value="<?php echo htmlspecialchars($reminderSubject); ?>">
      <label>Reminder Content</label>
      <textarea name="reminderContent"><?php echo htmlspecialchars($reminderContent); ?></textarea>

      <div class="section-title">Footer Text</div>
      <textarea name="footerText"><?php echo htmlspecialchars($footerText); ?></textarea>

      <button class="button mt-3" type="submit">💾 Save Settings</button>
      <a class="button" href="index.php">Back to General</a>
    </form>
  </div>
</body>
</html>
