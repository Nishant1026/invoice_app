<?php
// ✅ Database Connection
$conn = new mysqli("localhost", "root", "", "invoice_app");

if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// ✅ Handle Form Submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $logo = $_POST['logo'];
  $businessName = $_POST['businessName'];
  $address = $_POST['address'];
  $extraInfo = $_POST['extraInfo'];
  $website = $_POST['website'];

  // Check if a record already exists
  $check = $conn->query("SELECT id FROM business_settings LIMIT 1");
  if ($check->num_rows > 0) {
    $conn->query("UPDATE business_settings 
      SET logo_url='$logo', business_name='$businessName', address='$address', extra_info='$extraInfo', website='$website' 
      WHERE id=1");
  } else {
    $conn->query("INSERT INTO business_settings (logo_url, business_name, address, extra_info, website)
      VALUES ('$logo', '$businessName', '$address', '$extraInfo', '$website')");
  }
}

// ✅ Fetch latest saved data
$result = $conn->query("SELECT * FROM business_settings LIMIT 1");
$data = $result->fetch_assoc();
$logo = $data['logo_url'] ?? 'https://ultrakeyit.com/wp-content/uploads/2024';
$businessName = $data['business_name'] ?? 'Ultrakey IT Solutions Private Limited';
$address = $data['address'] ?? "Flat No. 204, 2nd Floor, Cyber Residency,\nIndira Nagar, Gachibowli,\nHyderabad, Telangana, India-500032\nsupport@ultrakeyit.com";
$extraInfo = $data['extra_info'] ?? '<b>GST No:</b> 36AADCU506A1ZO';
$website = $data['website'] ?? 'https://ultrakeyit.com';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Business Settings</title>
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
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
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
    input[type="text"], textarea {
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
    .logo-preview {
      margin-top: 10px;
      display: flex;
      align-items: center;
      gap: 15px;
    }
    .logo-preview img {
      max-width: 180px;
      border: 1px solid #ddd;
      border-radius: 8px;
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
  </style>
</head>
<body>
  <div class="container-tab">
    <h2>Business Settings</h2>

    <!-- Tabs -->
    <ul class="nav nav-tabs mb-4" id="settingsTabs" role="tablist">
      <li class="nav-item"><a class="nav-link" href="index.php">General</a></li>
      <li class="nav-item"><button class="nav-link active" id="business-tab" data-bs-toggle="tab" data-bs-target="#business" type="button">Business</button></li>
      <li class="nav-item"><a class="nav-link" href="index.php#quotes">Quotes</a></li>
      <li class="nav-item"><a class="nav-link" href="index.php#invoices">Invoices</a></li>
      <li class="nav-item"><a class="nav-link" href="index.php#payments">Payments</a></li>
      <li class="nav-item"><a class="nav-link" href="index.php#tax">Tax</a></li>
      <li class="nav-item"><a class="nav-link" href="index.php#emails">Emails</a></li>
      <!-- <li class="nav-item"><a class="nav-link" href="index.php#pdf">PDF</a></li>
      <li class="nav-item"><a class="nav-link" href="index.php#translate">Translate</a></li> -->
    </ul>

    <!-- Business Tab -->
    <div class="tab-content">
      <div class="tab-pane fade show active" id="business">
        <form method="POST">
          <label for="logo">Logo URL</label>
          <input type="text" id="logo" name="logo" value="<?= htmlspecialchars($logo) ?>">

          <div class="logo-preview">
          <img src="/ultrakey/assets/images/logo.png" alt="Logo Preview" id="filePreview">
            <input type="file" id="fileInput" style="display:none;">
            <a href="#" class="button" onclick="document.getElementById('fileInput').click(); return false;">Add or Upload File</a>
          </div>

          <label for="businessName">Business Name</label>
          <input type="text" id="businessName" name="businessName" value="<?= htmlspecialchars($businessName) ?>">

          <label for="address">Address</label>
          <textarea id="address" name="address"><?= htmlspecialchars($address) ?></textarea>

          <label for="extraInfo">Extra Business Info</label>
          <textarea id="extraInfo" name="extraInfo"><?= htmlspecialchars($extraInfo) ?></textarea>

          <label for="website">Website</label>
          <input type="text" id="website" name="website" value="<?= htmlspecialchars($website) ?>">

          <button class="button" type="submit">Save Settings</button>
          <br>
          <a class="button" href="index.php">Back to General</a>
        </form>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
  // ✅ Live Logo Preview
  document.getElementById('fileInput').addEventListener('change', function (event) {
    const file = event.target.files[0];
    if (file) {
      const reader = new FileReader();
      reader.onload = function (e) {
        document.getElementById('filePreview').src = e.target.result;
      };
      reader.readAsDataURL(file);
    }
  });
  </script>
</body>
</html>
