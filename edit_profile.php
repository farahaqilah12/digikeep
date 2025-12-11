<?php
// 1. START SESSION & SECURITY CHECKS
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'header.php';
require_once 'config.php';

// Security: Ensure user is logged in (Works for both Admin and User)
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$active = 'edit_profile';
$uid = $_SESSION['user_id'];
$msg = '';

// 2. CREATE UPLOAD FOLDERS IF THEY DON'T EXIST
$targetDir = "uploads/profile/";
if (!is_dir($targetDir)) {
    mkdir($targetDir, 0777, true);
}

// 3. DETERMINE CURRENT IMAGE
$extensions = ['jpg','jpeg','png','gif'];
$img = 'default.png'; // Make sure you have a default.png in uploads/profile/

// Check if user has an uploaded image
foreach ($extensions as $ext) {
    if (file_exists($targetDir . "profile_$uid.$ext")) {
        $img = "profile_$uid.$ext";
        break;
    }
}

// 4. HANDLE PHOTO UPLOAD
if (isset($_FILES['profile_img']) && $_FILES['profile_img']['error'] == 0) {
    $fileExt = strtolower(pathinfo($_FILES['profile_img']['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg','jpeg','png','gif'];

    if (in_array($fileExt, $allowed)) {
        // Delete old files to save space
        foreach ($extensions as $oldExt) {
            $oldFile = $targetDir . "profile_$uid.$oldExt";
            if (file_exists($oldFile)) unlink($oldFile);
        }

        // Save new file
        if(move_uploaded_file($_FILES['profile_img']['tmp_name'], $targetDir . "profile_$uid.$fileExt")) {
            $msg = "Profile photo updated!";
            $img = "profile_$uid.$fileExt"; // Update display immediately
        } else {
            $msg = "Failed to upload image. Check folder permissions.";
        }
    } else {
        $msg = "Invalid file type. Only JPG, PNG, GIF allowed.";
    }
}

// 5. HANDLE PASSWORD UPDATE
if (isset($_POST['current']) && isset($_POST['new'])) {
    $current = $_POST['current']; 
    $new = $_POST['new']; 
    $confirm = $_POST['confirm'];

    $stmt = $conn->prepare("SELECT password FROM users WHERE user_id=?"); 
    $stmt->bind_param("i",$uid); 
    $stmt->execute(); 
    $res = $stmt->get_result()->fetch_assoc();

    if (!$res || !password_verify($current, $res['password'])) {
        $msg = "Current password incorrect.";
    } elseif ($new !== $confirm) {
        $msg = "New passwords do not match.";
    } else {
        $hash = password_hash($new, PASSWORD_DEFAULT);
        $upd = $conn->prepare("UPDATE users SET password=? WHERE user_id=?"); 
        $upd->bind_param("si", $hash, $uid); 
        $upd->execute();
        $msg = "Password updated successfully!";
    }
}
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">

<style>
    /* --- TEAL THEME STYLES --- */
    html body { font-family: 'Inter', sans-serif; background-color: #f4f8f9 !important; color: #37474f; }
    
    /* Layout Fixes */
    main { display: flex !important; flex-direction: column !important; min-height: 100vh !important; width: 100% !important; }
    .content-wrapper { flex: 1; display: flex; flex-direction: column; justify-content: center; padding: 40px 20px; width: 100%; }
    .profile-container { width: 100%; max-width: 850px; margin: 0 auto; }

    /* Card Styling */
    .profile-card {
        background: white;
        border: none;
        border-radius: 16px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.04);
        padding: 40px;
        border-top: 5px solid #00695c; /* Teal Accent */
    }

    /* Buttons */
    .btn-primary { background-color: #00695c; border-color: #00695c; transition: all 0.3s ease; font-weight: 600; padding: 10px 25px; border-radius: 8px; }
    .btn-primary:hover { background-color: #004d40; border-color: #004d40; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0, 77, 64, 0.2); }
    
    .btn-outline-primary { color: #00695c; border-color: #00695c; font-weight: 600; }
    .btn-outline-primary:hover { background-color: #00695c; color: white; }

    /* Inputs */
    .form-control { border-radius: 8px; padding: 12px 15px; border: 1px solid #cfd8dc; background-color: #fdfdfd; }
    .form-control:focus { border-color: #00897b; box-shadow: 0 0 0 4px rgba(0, 137, 123, 0.15); background-color: #fff; }
    
    /* Typography */
    h4 { color: #00695c; font-weight: 800; letter-spacing: -0.5px; }
    h6 { font-weight: 700; color: #546e7a; text-transform: uppercase; letter-spacing: 0.5px; font-size: 0.85rem; }
    .text-muted-label { font-size: 0.85rem; font-weight: 600; color: #78909c; margin-bottom: 6px; display: block; }

    /* Profile Image */
    .profile-img-box {
        width: 160px; height: 160px;
        border-radius: 50%;
        object-fit: cover;
        border: 4px solid #e0f2f1; /* Light teal border */
        box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        margin-bottom: 20px;
    }
</style>

<div class="content-wrapper">
  <div class="profile-container">
      
      <h4 class="mb-4 text-center"><i class="bi bi-person-gear me-2"></i>Edit Profile</h4>

      <?php if($msg): ?> 
        <div class="alert alert-info text-center shadow-sm border-0" style="background-color: #e0f2f1; color: #00695c;">
            <i class="bi bi-info-circle-fill me-2"></i><?= htmlspecialchars($msg) ?>
        </div> 
      <?php endif; ?>

      <div class="profile-card">
          <div class="row align-items-start">
            
            <div class="col-md-4 text-center border-end pe-md-4">
                <img src="<?= file_exists("uploads/profile/$img") ? "uploads/profile/$img" : "https://via.placeholder.com/150?text=No+Image" ?>" class="profile-img-box">
                
                <h6 class="mb-3">Profile Picture</h6>
                <form method="post" enctype="multipart/form-data">
                  <input type="file" name="profile_img" accept="image/*" class="form-control form-control-sm mb-3" required>
                  <button class="btn btn-outline-primary btn-sm w-100"><i class="bi bi-upload me-2"></i>Update Photo</button>
                </form>
            </div>

            <div class="col-md-8 ps-md-5 mt-4 mt-md-0">
              <h6 class="mb-4 pb-2 border-bottom">Security Settings</h6>
              <form method="post">
                <div class="mb-3">
                    <label class="text-muted-label">Current Password</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white text-muted border-end-0"><i class="bi bi-lock"></i></span>
                        <input type="password" name="current" class="form-control border-start-0 ps-0" placeholder="Enter current password" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="text-muted-label">New Password</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white text-muted border-end-0"><i class="bi bi-key"></i></span>
                        <input type="password" name="new" class="form-control border-start-0 ps-0" placeholder="Enter new password" required>
                    </div>
                </div>
                <div class="mb-4">
                    <label class="text-muted-label">Confirm Password</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white text-muted border-end-0"><i class="bi bi-check-circle"></i></span>
                        <input type="password" name="confirm" class="form-control border-start-0 ps-0" placeholder="Re-enter new password" required>
                    </div>
                </div>
                <div class="text-end">
                    <button class="btn btn-primary shadow-sm"><i class="bi bi-shield-check me-2"></i>Update Password</button>
                </div>
              </form>
            </div>

          </div>
      </div>
  </div>
</div>

<?php require_once 'footer.php'; ?>