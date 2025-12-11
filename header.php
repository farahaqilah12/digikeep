<?php
// --- FIX: Only set cookies and start session if one doesn't exist yet ---
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.gc_maxlifetime', 86400); // 24 hours
    session_set_cookie_params([
        'lifetime' => 86400,
        'path' => '/',
        'domain' => '',
        'secure' => isset($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

require_once 'config.php';
require_once 'auth.php';

$user = currentUser();
$role = $user['role'] ?? null;
$username = $user['username'] ?? 'Guest';
$active = $active ?? '';
$uid = $_SESSION['user_id'] ?? 0;

// Determine current profile image
$extensions = ['jpg','jpeg','png','gif'];
$profile_img = 'default.png';
foreach ($extensions as $ext) {
    if (file_exists("uploads/profile/profile_$uid.$ext")) {
        $profile_img = "profile_$uid.$ext";
        break;
    }
}

// Function to determine active link
function isActive($keyword) {
    $current = basename($_SERVER['PHP_SELF']); 
    if(isset($_GET['tab'])){
        return $_GET['tab'] === $keyword ? 'active' : '';
    }
    if(strpos($current, $keyword) !== false){
        return 'active';
    }
    return '';
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Company DAM</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">

<style>
    /* --- TEAL THEME & LAYOUT STYLES --- */
    
    body { 
        font-family: 'Inter', system-ui, -apple-system, sans-serif; 
        background: #f4f8f9; /* Teal/Gray Tint */
        color: #37474f; 
        overflow-x: hidden; 
    }

    /* --- 1. TOP NAVBAR (FIXED) --- */
    .top-navbar {
        height: 65px;
        background: #ffffff;
        border-bottom: 1px solid #cfd8dc;
        position: fixed;
        top: 0; right: 0; left: 0;
        z-index: 1040; /* Higher than sidebar */
        display: flex;
        align-items: center;
        padding: 0 20px;
        box-shadow: 0 4px 12px rgba(0, 77, 64, 0.05); /* Subtle teal shadow */
    }

    /* Brand Logo */
    .navbar-brand {
        color: #00695c !important; /* Teal Brand */
        letter-spacing: -0.5px;
        display: flex;         /* Added for logo alignment */
        align-items: center;   /* Added for logo alignment */
    }

    /* Toggle Button */
    #sidebarToggle {
        color: #00695c;
        border-color: #b2dfdb;
        background-color: transparent;
    }
    #sidebarToggle:hover {
        background-color: #e0f2f1;
        border-color: #00695c;
    }

    /* --- 2. SIDEBAR --- */
    #layoutSidenav_nav {
        width: 250px;
        height: 100vh;
        background-color: #ffffff; 
        border-right: 1px solid #cfd8dc;
        position: fixed;
        top: 65px; /* Below Navbar */
        left: 0;
        z-index: 1030;
        transition: transform 0.3s ease-in-out;
        padding-top: 20px;
        overflow-y: auto;
        box-shadow: 4px 0 20px rgba(0,0,0,0.02);
    }

    /* --- 3. MAIN CONTENT --- */
    #layoutSidenav_content {
        margin-left: 250px;
        margin-top: 65px;
        padding: 30px;
        transition: margin-left 0.3s ease-in-out;
        min-height: calc(100vh - 65px);
    }

    /* --- 4. DESKTOP TOGGLE LOGIC --- */
    /* When 'toggled' class is added on Desktop, hide sidebar to the left */
    @media (min-width: 769px) {
        body.sb-sidenav-toggled #layoutSidenav_nav {
            transform: translateX(-250px);
        }
        body.sb-sidenav-toggled #layoutSidenav_content {
            margin-left: 0;
        }
    }

    /* --- 5. MOBILE TOGGLE LOGIC (FIXED) --- */
    @media (max-width: 768px) {
        /* Default State on Mobile: HIDDEN */
        #layoutSidenav_nav {
            transform: translateX(-250px); /* Start off-screen */
            box-shadow: 4px 0 8px rgba(0,0,0,0.1); 
        }
        
        /* Content is always full width on mobile */
        #layoutSidenav_content {
            margin-left: 0;
            padding: 15px;
        }

        /* Active State on Mobile: VISIBLE */
        /* When 'toggled' class is added, bring it back to 0 */
        body.sb-sidenav-toggled #layoutSidenav_nav {
            transform: translateX(0);
        }
    }

    /* --- 6. NAVIGATION LINK STYLING (THEME) --- */
    .nav-link { 
        color: #546e7a; /* Blue-gray text */
        padding: 12px 20px; 
        font-weight: 500; 
        margin: 4px 12px; /* Spacing for rounded effect */
        border-radius: 8px; /* Rounded corners */
        transition: all 0.2s ease;
    }
    
    /* Hover State */
    .nav-link:hover { 
        color: #00695c; 
        background: #e0f2f1; /* Light teal hover bg */
        transform: translateX(3px);
    }

    /* Active State */
    .nav-link.active { 
        background: #e0f2f1; /* Teal tint background */
        color: #00695c !important; 
        border-left: 4px solid #00695c; /* Solid teal accent */
        box-shadow: 0 2px 5px rgba(0, 105, 92, 0.05);
    }

    .nav-link i { 
        margin-right: 10px; 
        width: 20px; 
        text-align: center; 
        color: #90a4ae; /* Muted icon */
        transition: color 0.2s;
    }
    
    .nav-link:hover i, .nav-link.active i {
        color: #00695c; /* Active icon color */
    }
    
    /* Logout Link Specifics */
    .nav-link.text-danger:hover {
        background-color: #ffebee;
        color: #c62828 !important;
    }
    .nav-link.text-danger:hover i {
        color: #c62828 !important;
    }

</style>
</head>
<body>

<nav class="top-navbar">
    <button class="btn btn-light btn-sm me-3 border shadow-sm" id="sidebarToggle">
        <i class="bi bi-list fs-5"></i>
    </button>
    
    <a class="navbar-brand fw-bold" href="#">
        <img src="uploads/logodigikeep.png" alt="Logo" width="35" height="35" class="me-2">
        DigiKeep 
    </a>
    
    <div class="ms-auto d-flex align-items-center">
        <span class="small text-muted me-2 d-none d-md-block">Hello, <?= htmlspecialchars($username) ?></span>
        <img src="uploads/profile/<?= $profile_img ?>" class="rounded-circle border" width="35" height="35" style="object-fit:cover; border-color: #b2dfdb !important;">
    </div>
</nav>

<div id="layoutSidenav">
    
    <div id="layoutSidenav_nav">
        <nav class="nav flex-column">
            
            <div class="text-center mb-4 px-3 d-md-none">
                <small class="text-muted fw-bold">MENU</small>
            </div>

            <?php if($role === 'user'): ?>
                <a class="nav-link <?= isActive('dashboard') ?>" href="user_dashboard.php?tab=dashboard"><i class="bi bi-speedometer2"></i> Dashboard</a>
                <a class="nav-link <?= isActive('upload') ?>" href="user_dashboard.php?tab=upload"><i class="bi bi-cloud-upload"></i> Upload</a>
                <a class="nav-link <?= isActive('myfiles') ?>" href="user_dashboard.php?tab=myfiles"><i class="bi bi-folder2-open"></i> My Files</a>
                <a class="nav-link <?= isActive('user_statistics') ?>" href="user_statistics.php"><i class="bi bi-bar-chart"></i> Statistics</a>
                <a class="nav-link <?= isActive('edit_profile') ?>" href="edit_profile.php"><i class="bi bi-person-gear"></i> Profile</a>
            <?php elseif($role === 'admin'): ?>
                <a class="nav-link <?= isActive('dashboard') ?>" href="admin_dashboard.php?tab=dashboard"><i class="bi bi-speedometer2"></i> Dashboard</a>
                <a class="nav-link <?= isActive('manage_users') ?>" href="manage_users.php?tab=manage_users"><i class="bi bi-people"></i> Manage Users</a>
                <a class="nav-link <?= isActive('upload') ?>" href="admin_dashboard.php?tab=upload"><i class="bi bi-cloud-arrow-up"></i> Admin Upload</a>
                <a class="nav-link <?= isActive('myfiles') ?>" href="admin_dashboard.php?tab=myfiles"><i class="bi bi-folder2-open"></i> My Files</a>
                <a class="nav-link <?= isActive('admin_statistics') ?>" href="admin_statistics.php"><i class="bi bi-graph-up"></i> Statistics</a>
                <a class="nav-link <?= isActive('edit_profile') ?>" href="edit_profile.php"><i class="bi bi-person-gear"></i> Profile</a>
            <?php endif; ?>

            <div class="mt-4 border-top pt-3 mx-3">
                <a class="nav-link text-danger" href="logout.php"><i class="bi bi-box-arrow-left"></i> Logout</a>
            </div>
        </nav>
    </div>

    <div id="layoutSidenav_content">
        <main>

<script>
window.addEventListener('DOMContentLoaded', event => {
    const sidebarToggle = document.body.querySelector('#sidebarToggle');
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', event => {
            event.preventDefault();
            document.body.classList.toggle('sb-sidenav-toggled');
        });
    }
});
</script>