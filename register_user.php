<?php
require_once 'config.php';
$msg = '';
$extensions = ['jpg','jpeg','png','gif']; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $department = trim($_POST['department']);

    if ($username && $password && $department) {
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE username=?");
        $stmt->bind_param("s",$username); $stmt->execute();
        
        if ($stmt->get_result()->num_rows>0) {
            $msg = "Username exists.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $role = 'user';
            
            $stmt = $conn->prepare("INSERT INTO users (username,password,role,department) VALUES (?,?,?,?)");
            $stmt->bind_param("ssss",$username,$hash,$role,$department);
            $stmt->execute();
            
            $new_user_id = $conn->insert_id; 
            if ($new_user_id) {
                foreach ($extensions as $ext) {
                    $oldFile = "uploads/profile/profile_$new_user_id.$ext";
                    if (file_exists($oldFile)) unlink($oldFile);
                }
            }
            
            header("Location: login.php");
            exit;
        }
    } else $msg = "Please fill all fields.";
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Register User</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">

<style>
/* --- PROFESSIONAL TEAL LOGIN THEME --- */
body {
    font-family: 'Inter', system-ui, -apple-system, sans-serif;
    background: linear-gradient(135deg, #e0f2f1 0%, #b2dfdb 100%) !important;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
}
.card {
    border: none;
    border-radius: 16px;
    border-top: 5px solid #00695c;
    box-shadow: 0 15px 35px rgba(0, 77, 64, 0.15);
    background: #ffffff;
}
h4 {
    color: #00695c;
    font-weight: 800;
    text-align: center;
    margin-bottom: 25px;
    letter-spacing: -0.5px;
}
.form-control, .form-select {
    background-color: #f4f8f9;
    border: 1px solid #cfd8dc;
    padding: 12px 15px;
    border-radius: 8px;
    font-size: 14px;
    transition: all 0.3s ease;
}
.form-control:focus, .form-select:focus {
    background-color: #fff;
    border-color: #00897b;
    box-shadow: 0 0 0 4px rgba(0, 137, 123, 0.15);
}
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
.btn-outline-teal {
    color: #00695c;
    border: 2px solid #e0f2f1;
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
.alert {
    border: none;
    font-size: 13px;
    border-radius: 8px;
    text-align: center;
    font-weight: 500;
}
.alert-warning { background-color: #ffebee; color: #c62828; border-left: 4px solid #ef5350; }
label {
    font-size: 0.85rem;
    font-weight: 600;
    color: #546e7a;
    margin-bottom: 5px;
    margin-left: 2px;
}
</style>
</head>
<body class="bg-light">
<div class="container py-5" style="max-width:480px;">
  <div class="card p-4">
    <h4><i class="bi bi-person-plus-fill me-2"></i>Register User</h4>
    <?php if($msg): ?><div class="alert alert-warning"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
    
    <form method="post">
      <div class="mb-3">
          <label>Username</label>
          <input name="username" class="form-control" placeholder="Choose a username" required>
      </div>
      <div class="mb-3">
          <label>Password</label>
          <input type="password" name="password" class="form-control" placeholder="Create a password" required>
      </div>
      
      <div class="mb-3">
        <label>Department</label>
        <select name="department" class="form-select" required>
            <option value="" disabled selected>Select Department</option>
            <option value="IT">Information Technology (IT)</option>
            <option value="Finance">Finance</option>
            <option value="Marketing">Marketing</option>
            <option value="HR">Human Resources (HR)</option>
            <option value="Operations">Operations</option>
            <option value="Sales">Sales</option>
            <option value="R&D">Research & Development (R&D)</option>
            <option value="Executive">Executive</option> </select>
      </div>
      
      <button class="btn btn-primary w-100">Register</button>
      <a href="login.php" class="btn btn-outline-teal w-100"><i class="bi bi-arrow-left me-2"></i>Back to Login</a>
    </form>
  </div>
</div>
</body>
</html>