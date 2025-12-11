<?php
// 1. START SESSION & SECURITY CHECKS FIRST
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'auth.php';
checkLogin('admin'); // Redirect if not admin

require_once 'header.php';
require_once 'config.php';

$id = intval($_GET['id'] ?? 0);

// Basic Validation
if (!$id) { 
    echo "<div class='container py-5'><div class='alert alert-danger'>Invalid Asset ID.</div></div>"; 
    require 'footer.php'; 
    exit; 
}

// 2. HANDLE FORM SUBMISSION
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reason = trim($_POST['reason']);
    
    // Update status and save the reason (Make sure you ran the SQL command above!)
    $stmt = $conn->prepare("UPDATE assets SET status='rejected', reject_reason=? WHERE asset_id=?");
    $stmt->bind_param("si", $reason, $id);
    
    if ($stmt->execute()) {
        echo "<script>window.location.href='admin_dashboard.php';</script>";
        exit;
    } else {
        $error = "Database error: " . $stmt->error;
    }
}

// Fetch asset details for display (Optional context)
$asset = $conn->query("SELECT title, name FROM assets WHERE asset_id=$id")->fetch_assoc();
$title = $asset['title'] ?? "Unknown File";
$uploader = $asset['name'] ?? "Unknown User";
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">

<style>
    /* TEAL THEME */
    html body { font-family: 'Inter', sans-serif; background-color: #f4f8f9 !important; color: #37474f; }
    .content-wrapper { display: flex; flex-direction: column; justify-content: center; min-height: 80vh; padding: 20px; }
    
    .card {
        border: none;
        border-radius: 16px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        background: white;
        border-top: 5px solid #c62828; /* Red for Reject */
        max-width: 500px;
        margin: 0 auto;
    }
    
    h4 { color: #c62828; font-weight: 800; letter-spacing: -0.5px; }
    .btn-danger { background-color: #c62828; border-color: #c62828; font-weight: 600; transition: all 0.3s; }
    .btn-danger:hover { background-color: #b71c1c; transform: translateY(-2px); }
    .btn-secondary { background-color: #cfd8dc; color: #455a64; border: none; font-weight: 600; }
    .btn-secondary:hover { background-color: #b0bec5; color: #37474f; }
    .form-control:focus { border-color: #c62828; box-shadow: 0 0 0 4px rgba(198, 40, 40, 0.1); }
</style>

<div class="content-wrapper">
    <div class="card p-4 p-md-5">
        <div class="text-center mb-4">
            <i class="bi bi-x-circle-fill text-danger" style="font-size: 3rem;"></i>
            <h4 class="mt-3">Reject Asset</h4>
            <p class="text-muted small">
                You are rejecting <strong>"<?= htmlspecialchars($title) ?>"</strong> by <?= htmlspecialchars($uploader) ?>.
            </p>
        </div>

        <form method="post">
            <div class="mb-4">
                <label class="form-label fw-bold text-muted small">Reason for Rejection</label>
                <textarea name="reason" class="form-control" rows="4" placeholder="Please provide a reason so the user knows what to fix..." required></textarea>
            </div>
            
            <div class="d-grid gap-2">
                <button class="btn btn-danger py-2 rounded-pill shadow-sm">Confirm Rejection</button>
                <a class="btn btn-secondary py-2 rounded-pill" href="admin_dashboard.php">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php require_once 'footer.php'; ?>