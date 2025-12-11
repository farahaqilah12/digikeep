<?php
// 1. START SESSION & SECURITY CHECKS FIRST
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'auth.php';
checkLogin('admin');

require_once 'header.php';
require_once 'config.php';

$active = 'admin_statistics'; // Updated active tab name

// ----------------------------
// DATA FETCHING
// ----------------------------
$total_user_assets = $conn->query("SELECT COUNT(*) AS c FROM assets a JOIN users u ON a.user_id=u.user_id WHERE u.role='user'")->fetch_assoc()['c'] ?? 0;
$approved_users    = $conn->query("SELECT COUNT(*) AS c FROM assets a JOIN users u ON a.user_id=u.user_id WHERE u.role='user' AND a.status='approved'")->fetch_assoc()['c'] ?? 0;
$pending_users     = $conn->query("SELECT COUNT(*) AS c FROM assets a JOIN users u ON a.user_id=u.user_id WHERE u.role='user' AND a.status='pending'")->fetch_assoc()['c'] ?? 0;

$types = ['PDF','JPG','PNG','EXCEL','DOCS','OTHER'];
$user_counts = [];
foreach($types as $type){
    $user_counts[$type] = $conn->query("SELECT COUNT(*) AS c FROM assets a JOIN users u ON a.user_id=u.user_id WHERE u.role='user' AND a.type='$type'")->fetch_assoc()['c'] ?? 0;
}

$admin_counts = [];
foreach($types as $type){
    $admin_counts[$type] = $conn->query("SELECT COUNT(*) AS c FROM assets a JOIN users u ON a.user_id=u.user_id WHERE u.role='admin' AND a.type='$type'")->fetch_assoc()['c'] ?? 0;
}
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">

<style>
    /* --- TEAL ADMIN THEME STYLES --- */
    
    html body { font-family: 'Inter', sans-serif; background-color: #f4f8f9 !important; color: #37474f; }
    .content-wrapper { padding: 40px; background-color: #f4f8f9; min-height: 100vh; }

    /* Dashboard Layout Fixes */
    .dashboard-container {
        padding: 20px;
        max-width: 1400px;
        margin: 0 auto;
    }
    
    /* Stat Cards */
    .stat-card {
        border: none;
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.03);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        height: 100%;
        color: white; /* Default text color for these cards */
    }
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    }
    .stat-card h6 { font-weight: 600; opacity: 0.9; margin-bottom: 5px; }
    .stat-card h2 { font-weight: 800; margin-bottom: 0; font-size: 2rem; }
    
    /* Custom Backgrounds for Stats */
    .bg-teal-gradient { background: linear-gradient(135deg, #00695c 0%, #004d40 100%); }
    .bg-green-gradient { background: linear-gradient(135deg, #43a047 0%, #2e7d32 100%); }
    .bg-amber-gradient { background: linear-gradient(135deg, #ffb300 0%, #ff8f00 100%); }

    /* Charts Section */
    .chart-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.03);
        background: white;
        height: 100%;
        border-top: 4px solid #00695c;
    }
    .chart-header {
        background-color: white;
        border-bottom: 1px solid #f1f1f1;
        padding: 20px;
        font-weight: 700;
        color: #37474f;
    }
    .chart-box {
        position: relative; 
        height: 280px; /* Fixed height for consistency */
        width: 100%;
    }

    /* List Group Customization */
    .list-group-item { border: none; border-bottom: 1px solid #f1f1f1; padding: 12px 10px; font-size: 0.9rem; color: #546e7a; }
    .list-group-item:last-child { border-bottom: none; }
    .badge-pill { border-radius: 50px; padding: 6px 12px; font-weight: 600; font-size: 0.75rem; background-color: #00695c; }
    
    .list-group-item small { font-weight: 600; color: #37474f; }
</style>

<div class="content-wrapper">
    <div class="dashboard-container">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold" style="color: #37474f;">System Statistics</h3>
                <p class="text-muted small mb-0">Overview of asset distribution and status</p>
            </div>
        </div>

        <div class="row g-4 mb-5">
            <div class="col-md-4">
                <div class="stat-card bg-teal-gradient">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6>Total User Assets</h6>
                            <h2><?= $total_user_assets ?></h2>
                        </div>
                        <i class="bi bi-folder-fill fs-1 opacity-25"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card bg-green-gradient">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6>Approved</h6>
                            <h2><?= $approved_users ?></h2>
                        </div>
                        <i class="bi bi-check-circle-fill fs-1 opacity-25"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card bg-amber-gradient">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6>Pending</h6>
                            <h2><?= $pending_users ?></h2>
                        </div>
                        <i class="bi bi-hourglass-split fs-1 opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            
            <div class="col-lg-7">
                <div class="chart-card">
                    <div class="chart-header">User Uploads Breakdown</div>
                    <div class="card-body p-4">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <div class="chart-box">
                                    <canvas id="usersChart"></canvas>
                                </div>
                            </div>
                            <div class="col-md-6 ps-md-4">
                                <h6 class="text-muted small text-uppercase fw-bold mb-3">File Type Distribution</h6>
                                <div class="row g-2">
                                    <?php foreach($user_counts as $type => $count): ?>
                                    <div class="col-6"> 
                                        <div class="d-flex justify-content-between align-items-center border rounded p-2 bg-light">
                                            <small><?= $type ?></small>
                                            <span class="badge badge-pill"><?= $count ?></span>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="chart-card">
                    <div class="chart-header">Admin Uploads</div>
                    <div class="card-body p-4 d-flex justify-content-center align-items-center">
                        <div class="chart-box">
                            <canvas id="adminChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Chart Colors (Teal Theme Palette)
const themeColors = ['#00695c', '#2e7d32', '#ff8f00', '#546e7a', '#d81b60', '#8d6e63'];

// Shared Config
const legendOptions = { position: 'bottom', labels: { boxWidth: 12, padding: 15, font: {size: 11}, usePointStyle: true } };

// Users Chart
new Chart(document.getElementById('usersChart'), {
    type: 'doughnut',
    data: {
        labels: <?= json_encode($types) ?>,
        datasets: [{
            data: <?= json_encode(array_values($user_counts)) ?>,
            backgroundColor: themeColors,
            borderWidth: 2,
            borderColor: '#ffffff',
            hoverOffset: 5
        }]
    },
    options: { 
        responsive: true, 
        maintainAspectRatio: false, 
        plugins:{ legend: {display: false} }, // Hide legend here to save space
        cutout: '70%' 
    } 
});

// Admin Chart
new Chart(document.getElementById('adminChart'), {
    type: 'doughnut',
    data: {
        labels: <?= json_encode($types) ?>,
        datasets: [{
            data: <?= json_encode(array_values($admin_counts)) ?>,
            backgroundColor: themeColors,
            borderWidth: 2,
            borderColor: '#ffffff',
            hoverOffset: 5
        }]
    },
    options: { 
        responsive: true, 
        maintainAspectRatio: false, 
        plugins:{ legend: legendOptions },
        cutout: '70%'
    }
});
</script>

<?php require_once 'footer.php'; ?>