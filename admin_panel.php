<?php
session_start();

// Database connection
$host = "localhost";
$user = "root";
$password = "";
$db = "invoice_app";

$conn = new mysqli($host, $user, $password, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Admin login
if (isset($_POST['username']) && isset($_POST['password'])) {
    if ($_POST['username'] === 'admin' && $_POST['password'] === 'admin123') {
        $_SESSION['admin'] = true;
        header("Location: admin_panel.php");
        exit();
    } else {
        $error = "Invalid credentials!";
    }
}

// Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: admin_panel.php");
    exit();
}

// Define primary keys for each table
$key = [
    'general_settings' => 'id',
    'tax_settings' => 'id',
    'quotes' => 'id',
    'invoices' => 'id',
    'clients' => 'id'
];

// Delete record
if (isset($_GET['delete'], $_GET['table'], $_GET['id'])) {
    $id = (int)$_GET['id'];
    $table = preg_replace('/[^a-zA-Z0-9_]/', '', $_GET['table']);

    if (isset($key[$table])) {
        $column = $key[$table];
        $stmt = $conn->prepare("DELETE FROM `$table` WHERE `$column` = ?");
        if ($stmt) {
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
            header("Location: admin_panel.php#$table");
            exit();
        }
    }
}

// Update record
if (isset($_POST['update'], $_POST['table'], $_POST['id'])) {
    $table = preg_replace('/[^a-zA-Z0-9_]/', '', $_POST['table']);
    $id = (int)$_POST['id'];

    if (!isset($key[$table])) {
        echo "<p class='text-danger'>Invalid table or primary key!</p>";
        exit();
    }

    $idColumn = $key[$table];

    // Filter out update, table, id
    $columns = array_filter($_POST, function ($k) {
        return !in_array($k, ['update', 'table', 'id']);
    }, ARRAY_FILTER_USE_KEY);

    $updates = [];
    $params = [];
    $types = '';

    foreach ($columns as $col => $val) {
        $updates[] = "`$col` = ?";
        $params[] = $val;
        $types .= 's';
    }

    $params[] = $id;
    $types .= 'i';

    $sql = "UPDATE `$table` SET " . implode(', ', $updates) . " WHERE `$idColumn` = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param($types, ...$params);
        if ($stmt->execute()) {
            header("Location: admin_panel.php#$table");
            exit();
        } else {
            echo "<p class='text-danger'>Error: " . $stmt->error . "</p>";
        }
        $stmt->close();
    } else {
        echo "<p class='text-danger'>Prepare failed: " . $conn->error . "</p>";
    }
}

// Show login form if admin not logged in
if (!isset($_SESSION['admin'])):
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="d-flex justify-content-center align-items-center vh-100 bg-light">
    <form method="POST" class="border p-4 rounded shadow w-25 bg-white">
        <h2 class="mb-4 text-center">Admin Login</h2>
        <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
        <input type="text" name="username" placeholder="Username" class="form-control mb-3" required>
        <input type="password" name="password" placeholder="Password" class="form-control mb-3" required>
        <button type="submit" class="btn btn-primary w-100">Login</button>
    </form>
</body>
</html>
<?php
exit();
endif;

// Fetch data function
function fetchData($conn, $table) {
    $table = preg_replace('/[^a-zA-Z0-9_]/', '', $table);
    $result = $conn->query("SELECT * FROM `$table`");
    return $result ?: false;
}

$sections = [
    'general_settings' => ['business_name','prefix','suffix','auto_increment','valid_days'],
    'tax_settings' => ['tax_name','tax_percentage'],
    'quotes' => ['quote_number','client_name','total_amount','created_at'],
    'invoices' => ['invoice_number','client_name','total_amount','status','created_at'],
    'clients' => ['name','email','mobile','address']
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Invoice App</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; }
        .sidebar { height: 100vh; background-color: #343a40; color: white; }
        .sidebar a { color: white; text-decoration: none; display: block; padding: 10px; }
        .sidebar a:hover { background-color: #495057; }
        .content { padding: 20px; }
        .table thead { background-color: #dee2e6; }
        input.form-control-sm { font-size: 0.85rem; }
        hr { border-top: 2px solid #dee2e6; }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-2 sidebar p-3">
            <h4>Admin Panel</h4>
            <?php foreach ($sections as $table => $cols): ?>
                <a href="#<?= $table ?>"><?= ucwords(str_replace('_',' ', $table)) ?></a>
            <?php endforeach; ?>
            <a href="?logout=true">Logout</a>
        </div>
        <div class="col-md-10 content">
            <h2 class="mb-4">Welcome, Admin</h2>

            <?php foreach ($sections as $table => $columns): ?>
                <h4 id="<?= $table ?>" class="mt-4"><?= ucwords(str_replace('_',' ',$table)) ?></h4>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead>
                            <tr>
                                <?php foreach ($columns as $head): ?>
                                    <th><?= ucwords(str_replace('_',' ', $head)) ?></th>
                                <?php endforeach; ?>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        $data = fetchData($conn, $table);
                        if ($data && $data->num_rows > 0):
                            while ($row = $data->fetch_assoc()):
                                $idKey = $key[$table];
                                $id = $row[$idKey] ?? 0;
                        ?>
                        <tr>
                            <form method="POST">
                                <?php foreach ($columns as $col): 
                                    $val = htmlspecialchars($row[$col] ?? '');
                                ?>
                                <td><input type="text" name="<?= $col ?>" value="<?= $val ?>" class="form-control form-control-sm"></td>
                                <?php endforeach; ?>
                                <td class="d-flex gap-1 flex-wrap">
                                    <input type="hidden" name="table" value="<?= $table ?>">
                                    <input type="hidden" name="id" value="<?= $id ?>">
                                    <button type="submit" name="update" class="btn btn-warning btn-sm">Update</button>
                                    <a href="?delete=1&table=<?= $table ?>&id=<?= $id ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</a>
                                </td>
                            </form>
                        </tr>
                        <?php
                            endwhile;
                        else:
                        ?>
                        <tr><td colspan="<?= count($columns)+1 ?>">No records found.</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <hr>
            <?php endforeach; ?>
        </div>
    </div>
</div>
</body>
</html>
