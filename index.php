<?php
// Include DB connection
include 'db.php';

// Initialize message
$message = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $year_start = $_POST['year_start'];
  $year_end = $_POST['year_end'];
  $line_items = $_POST['line_items'];

  // Check if settings already exist
  $check = $conn->query("SELECT * FROM general_settings LIMIT 1");

  if ($check->num_rows > 0) {
    // Update existing settings
    $sql = "UPDATE general_settings SET year_start='$year_start', year_end='$year_end', line_items='$line_items'";
  } else {
    // Insert new settings
    $sql = "INSERT INTO general_settings (year_start, year_end, line_items) 
            VALUES ('$year_start', '$year_end', '$line_items')";
  }

  if ($conn->query($sql) === TRUE) {
    $message = " Settings saved successfully!";
  } else {
    $message = " Error: " . $conn->error;
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Settings - General Settings</title>

  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />

  <!-- AOS Animation (Optional) -->
  <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
</head>

<body>
  <div class="container settings-container" data-aos="fade-up">
    <h2>⚙️ Settings</h2>

    <!-- Show success/error message -->
    <?php if (!empty($message)) : ?>
      <div class="alert alert-info"><?= $message ?></div>
    <?php endif; ?>

    <!-- Tabs -->
    <ul class="nav nav-tabs mb-4" id="settingsTabs" role="tablist">
      <li class="nav-item">
        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#general" type="button">General</button>
      </li>
      <li class="nav-item"><a class="nav-link" href="bussines.php">Business</a></li>
      <li class="nav-item"><a class="nav-link" href="quotes.php">Quotes</a></li>
      <li class="nav-item"><a class="nav-link" href="invoices.php">Invoices</a></li>
      <li class="nav-item"><a class="nav-link" href="payment.php">Payments</a></li>
      <li class="nav-item"><a class="nav-link" href="tax.php">Tax</a></li>
      <li class="nav-item"><a class="nav-link" href="email.php">Mails</a></li>
      <!-- <li class="nav-item"><a class="nav-link" href="pdf.php">PDF</a></li>
      <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#translate" type="button">Translate</button>
      </li> -->
    </ul>

    <!-- Tab Content -->
    <div class="tab-content">
      <!-- General Settings -->
      <div class="tab-pane fade show active" id="general">
        <div class="card p-4">
          <h5 class="mb-3">General Settings</h5>
          <p class="text-muted">Just some general options.</p>

          <form method="POST">
            <div class="row mb-3">
              <div class="col-md-6">
                <label class="form-label">Year Start</label>
                <select name="year_start" class="form-select" required>
                  <option value="01 Apr">01 Apr</option>
                  <option value="01 Jan">01 Jan</option>
                  <option value="01 Jul">01 Jul</option>
                  <option value="01 Oct">01 Oct</option>
                </select>
                <small class="text-muted">The start date of the fiscal year</small>
              </div>

              <div class="col-md-6">
                <label class="form-label">Year End</label>
                <select name="year_end" class="form-select" required>
                  <option value="31 Mar">31 Mar</option>
                  <option value="31 Dec">31 Dec</option>
                  <option value="30 Jun">30 Jun</option>
                  <option value="30 Sep">30 Sep</option>
                </select>
                <small class="text-muted">The end date of the fiscal year</small>
              </div>
            </div>

            <div class="mb-3">
              <label class="form-label">Pre-Defined Line Items</label>
              <textarea name="line_items" class="form-control" rows="6" placeholder="1 | .com Domain Registration | 1600 | Domain per year"></textarea>
              <small class="text-muted">
                Add 1 line per item in this format:<br>
                <b>Qty | Title | Price | Description</b> (Each field separated with a "|" symbol).
              </small>
            </div>

            <button type="submit" class="btn btn-primary">💾 Save</button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap & AOS JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
  <script>AOS.init({ duration: 700, once: true });</script>
</body>

</html>
