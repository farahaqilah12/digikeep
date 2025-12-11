<?php
session_start();
require_once 'config.php';

// SAFER — regenerate session ID to prevent session fixation
session_regenerate_id(true);

$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Prevent SQL scanning & timing attacks
    $stmt = $conn->prepare("SELECT user_id, username, password, role FROM users WHERE username=? LIMIT 1");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();

    if ($res && password_verify($password, $res['password'])) {

        // set all sessions ONLY ONCE
        $_SESSION['loggedin'] = true;
        $_SESSION['user_id'] = (int)$res['user_id'];
        $_SESSION['username'] = $res['username'];
        $_SESSION['role'] = $res['role'];

        // NEVER unset session accidentally elsewhere
        // NO header conflicts

        if ($res['role'] === 'admin') {
            header("Location: admin_dashboard.php");
        } else {
            header("Location: user_dashboard.php");
        }
        exit;
    } else {
        $msg = "Invalid username or password.";
    }
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Login</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">

<style>
/* --- PROFESSIONAL TEAL LOGIN THEME --- */

body {
    font-family: 'Inter', system-ui, -apple-system, sans-serif;
    /* Soft Teal Gradient Background */
    background: linear-gradient(135deg, #e0f2f1 0%, #b2dfdb 100%) !important;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Card Styling */
.card {
    border: none;
    border-radius: 16px;
    /* Deep Teal Top Border */
    border-top: 5px solid #00695c;
    /* Professional Shadow */
    box-shadow: 0 15px 35px rgba(0, 77, 64, 0.15);
    background: #ffffff;
}

/* Title Styling */
h4 {
    color: #00695c;
    font-weight: 800;
    text-align: center;
    margin-bottom: 25px;
    letter-spacing: -0.5px;
}

/* Input Fields */
.form-control {
    background-color: #f4f8f9;
    border: 1px solid #cfd8dc;
    padding: 12px 15px;
    border-radius: 8px;
    font-size: 14px;
    transition: all 0.3s ease;
}

.form-control:focus {
    background-color: #fff;
    border-color: #00897b;
    box-shadow: 0 0 0 4px rgba(0, 137, 123, 0.15);
}

/* Button Styling */
.btn-primary {
    background-color: #00695c;
    border-color: #00695c;
    padding: 12px;
    font-weight: 600;
    border-radius: 8px;
    margin-top: 10px;
    letter-spacing: 0.5px;
    transition: all 0.3s ease;
}

.btn-primary:hover {
    background-color: #004d40;
    border-color: #004d40;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 77, 64, 0.2);
}

/* --- ADDED: Secondary Home Button Style --- */
.btn-outline-teal {
    color: #00695c;
    border: 2px solid #e0f2f1; /* Very subtle border */
    background: #ffffff;
    padding: 12px;
    font-weight: 600;
    border-radius: 8px;
    margin-top: 10px;
    display: block;
    text-align: center;
    text-decoration: none;
    transition: all 0.3s ease;
}

.btn-outline-teal:hover {
    border-color: #00695c;
    background: #e0f2f1;
    color: #004d40;
}

/* Error Message */
.alert-danger {
    border: none;
    background-color: #ffebee;
    color: #c62828;
    font-size: 13px;
    border-radius: 8px;
    text-align: center;
    font-weight: 500;
    border-left: 4px solid #ef5350;
}

/* Placeholder text color adjustment */
::placeholder {
    color: #90a4ae !important;
    opacity: 1;
}

</style>
</head>
<body class="bg-light">
<div class="container py-5" style="max-width:420px;">
  <div class="card p-4">
    <h4><i class="bi bi-shield-lock-fill me-2"></i>Login</h4>
    <?php if($msg): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>
    <form method="post">
      <div class="mb-3"><input name="username" class="form-control" placeholder="Username" required></div>
      <div class="mb-3"><input type="password" name="password" class="form-control" placeholder="Password" required></div>
      
      <button class="btn btn-primary w-100">Sign In</button>
      
      <a href="index.php" class="btn btn-outline-teal w-100">
          <i class="bi bi-house-door me-2"></i>Home
      </a>
      
    </form>
    </div>
</div>
</body>
</html>