<?php
// 1. START SESSION & SECURITY CHECKS
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Security Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit;
}

require_once 'header.php';
require_once 'config.php';

$active = 'user_statistics'; // Updated active tab name
$uid = $_SESSION['user_id'];

// Fetch totals
$totals = $conn->query("SELECT 
    COUNT(*) AS total,
    SUM(status='approved') AS approved,
    SUM(status='pending') AS pending
    FROM assets
    WHERE user_id=$uid")->fetch_assoc();

// Fetch type counts
$types = ['PDF','JPG','PNG','EXCEL','DOCS'];
$counts = [];
foreach($types as $type){
    $counts[$type] = $conn->query("SELECT COUNT(*) AS c FROM assets WHERE user_id=$uid AND type='$type'")->fetch_assoc()['c'];
}
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">

<style>
    /* --- TEAL THEME STYLES --- */
    
    html body { font-family: 'Inter', sans-serif; background-color: #f4f8f9 !important; color: #37474f; }
    
    /* Layout Fixes */
    main { display: flex !important; flex-direction: column !important; min-height: 85vh !important; }
    .dashboard-container { flex: 1; padding: 30px; width: 100%; max-width: 1200px; margin: 0 auto; }
    footer { margin-top: auto !important; width: 100% !important; }

    /* Stat Cards */
    .stat-card {
        border: none;
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.03);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        height: 100%;
        color: white; /* Text is white for these cards */
    }
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    }
    .stat-card h6 { font-weight: 600; opacity: 0.9; margin-bottom: 5px; }
    .stat-card h2 { font-weight: 800; margin-bottom: 0; font-size: 2rem; }

    /* Custom Backgrounds (Teal Palette) */
    .bg-teal-gradient { background: linear-gradient(135deg, #00695c 0%, #004d40 100%); }
    .bg-green-gradient { background: linear-gradient(135deg, #43a047 0%, #2e7d32 100%); }
    .bg-amber-gradient { background: linear-gradient(135deg, #ffb300 0%, #ff8f00 100%); }

    /* Chart Card */
    .chart-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.03);
        background: white;
        border-top: 4px solid #00695c; /* Teal Accent */
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
        height: 250px; 
        width: 100%;
    }

    /* List Group Customization */
    .list-group-item { border: none; border-bottom: 1px solid #f1f1f1; padding: 10px; font-size: 0.9rem; color: #546e7a; }
    .badge-pill { border-radius: 50px; padding: 6px 12px; font-weight: 600; font-size: 0.75rem; background-color: #00695c; color: white; }
</style>

<div class="dashboard-container">

    <h4 class="mb-4" style="color: #37474f;"><i class="bi bi-bar-chart-fill me-2" style="color: #00695c;"></i>My Statistics</h4>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="stat-card bg-teal-gradient">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6>Total Uploads</h6>
                        <h2><?= $totals['total'] ?></h2>
                    </div>
                    <i class="bi bi-cloud-upload-fill fs-1 opacity-25"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card bg-green-gradient">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6>Approved</h6>
                        <h2><?= $totals['approved'] ?></h2>
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
                        <h2><?= $totals['pending'] ?></h2>
                    </div>
                    <i class="bi bi-hourglass-split fs-1 opacity-25"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="chart-card">
        <div class="chart-header">
            File Type Breakdown
        </div>
        <div class="card-body">
            <div class="row align-items-center">
                
                <div class="col-lg-6 col-md-6 border-end">
                    <div class="chart-box">
                        <canvas id="typeChart"></canvas>
                    </div>
                </div>

                <div class="col-lg-6 col-md-6 ps-md-4">
                    <h6 class="text-muted small mb-3 text-uppercase fw-bold">Details</h6>
                    <div class="row g-2">
                        <?php foreach($counts as $type => $count): ?>
                        <div class="col-6"> 
                            <div class="d-flex justify-content-between align-items-center border rounded p-2 bg-light">
                                <small class="fw-bold text-secondary"><?= $type ?></small>
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

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Chart Colors (Teal Theme Palette)
const themeColors = ['#00695c', '#2e7d32', '#ff8f00', '#546e7a', '#d81b60'];

const ctx = document.getElementById('typeChart').getContext('2d');
new Chart(ctx, {
    type: 'doughnut',
    data: {
        labels: ['PDF','JPG','PNG','EXCEL','DOCS'],
        datasets: [{
            data: [
                <?= $counts['PDF'] ?>,
                <?= $counts['JPG'] ?>,
                <?= $counts['PNG'] ?>,
                <?= $counts['EXCEL'] ?>,
                <?= $counts['DOCS'] ?>
            ],
            backgroundColor: themeColors,
            borderColor: '#ffffff',
            borderWidth: 2,
            hoverOffset: 5
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false }, // Hide legend to save space
            tooltip: { enabled: true }
        },
        cutout: '70%' // Modern thinner ring
    }
});
</script>

<?php require_once 'footer.php'; ?>