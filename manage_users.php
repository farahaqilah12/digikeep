<?php
// 1. START SESSION & SECURITY CHECKS FIRST
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'auth.php';
checkLogin('admin');

require_once 'header.php';
require_once 'config.php';

$active = 'manage_users';

// 1. Handle Search & Filter Inputs
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$deptFilter = isset($_GET['department']) ? trim($_GET['department']) : '';

// 2. Fetch unique departments for the dropdown list
$deptQuery = $conn->query("SELECT DISTINCT department FROM users WHERE role='user' AND department IS NOT NULL AND department != '' ORDER BY department ASC");
$departments = $deptQuery->fetch_all(MYSQLI_ASSOC);

// 3. Build the Dynamic SQL Query for Users
$sql = "SELECT * FROM users WHERE role='user'";
$params = [];
$types = "";

if (!empty($search)) {
    $sql .= " AND username LIKE ?";
    $params[] = "%" . $search . "%";
    $types .= "s";
}

if (!empty($deptFilter)) {
    $sql .= " AND department = ?";
    $params[] = $deptFilter;
    $types .= "s";
}

$sql .= " ORDER BY username ASC";

// 4. Execute the Query safely
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$users = $result->fetch_all(MYSQLI_ASSOC);

$chartData = [];
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">

<style>
    /* --- TEAL ADMIN THEME STYLES (MATCHING DASHBOARD) --- */
    
    html body { font-family: 'Inter', sans-serif; background-color: #f4f8f9 !important; color: #37474f; }
    .content-wrapper { padding: 40px; background-color: #f4f8f9; min-height: 100vh; }

    /* Buttons */
    .btn-primary { background-color: #00695c; border-color: #00695c; transition: all 0.3s ease; font-weight: 600; }
    .btn-primary:hover { background-color: #004d40; border-color: #004d40; transform: translateY(-2px); }
    .btn-secondary { background-color: #546e7a; border-color: #546e7a; color: white; font-weight: 600; }
    .btn-secondary:hover { background-color: #37474f; border-color: #37474f; }

    /* Inputs */
    .form-control, .form-select { border-radius: 8px; padding: 10px 15px; border: 1px solid #cfd8dc; background-color: #ffffff; }
    .form-control:focus, .form-select:focus { border-color: #00897b; box-shadow: 0 0 0 4px rgba(0, 137, 123, 0.15); }
    .input-group-text { border-color: #cfd8dc; background-color: #fff; }

    /* Cards */
    .search-card { background: white; border: none; border-radius: 12px; padding: 25px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); margin-bottom: 30px; border-left: 5px solid #00695c; }
    
    .user-card { 
        background: white; border: none; border-radius: 12px; 
        box-shadow: 0 4px 15px rgba(0,0,0,0.03); 
        transition: transform 0.3s ease, box-shadow 0.3s ease; 
        height: 100%; overflow: hidden;
        border-top: 4px solid #4db6ac; 
    }
    .user-card:hover { transform: translateY(-5px); box-shadow: 0 12px 30px rgba(0, 105, 92, 0.15); }

    /* Stats Boxes inside User Card */
    .stat-box { border-radius: 8px; padding: 15px; text-align: center; height: 100%; transition: all 0.2s; }
    .stat-box h4 { font-weight: 800; margin-bottom: 0; font-size: 1.5rem; }
    .stat-box small { font-size: 0.75rem; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px; opacity: 0.8; }
    
    /* Custom Colors for Stats */
    .bg-teal-light { background: #e0f2f1; color: #00695c; }
    .bg-blue-light { background: #e3f2fd; color: #1565c0; }
    .bg-amber-light { background: #fff8e1; color: #f57f17; }

    /* List Group */
    .list-group-item { border: none; border-bottom: 1px solid #f1f1f1; padding: 10px 0; font-size: 0.9rem; color: #546e7a; }
    .list-group-item:last-child { border-bottom: none; }
    .badge-pill { border-radius: 50px; padding: 5px 10px; font-weight: 600; font-size: 0.75rem; }
</style>

<div class="content-wrapper">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold" style="color: #37474f;">User Statistics</h3>
            <p class="text-muted small mb-0">Monitor upload activity per user</p>
        </div>
    </div>

    <div class="search-card">
        <form method="GET" action="" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label small fw-bold text-muted">Search User</label>
                <div class="input-group">
                    <span class="input-group-text border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Enter username..." value="<?= htmlspecialchars($search) ?>">
                </div>
            </div>
            
            <div class="col-md-4">
                <label class="form-label small fw-bold text-muted">Filter by Department</label>
                <select name="department" class="form-select">
                    <option value="">All Departments</option>
                    <?php foreach ($departments as $dept): ?>
                        <option value="<?= htmlspecialchars($dept['department']) ?>" <?= ($deptFilter === $dept['department']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($dept['department']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1"><i class="bi bi-funnel"></i> Filter</button>
                <a href="manage_users.php" class="btn btn-secondary"><i class="bi bi-arrow-counterclockwise"></i> Reset</a>
            </div>
        </form>
    </div>

    <div class="row g-4">
    <?php if (count($users) > 0): ?>
        <?php foreach ($users as $user): 
            $uid = $user['user_id'];
            
            // Logic to fetch stats per user
            $total    = $conn->query("SELECT COUNT(*) AS c FROM assets WHERE user_id=$uid")->fetch_assoc()['c'];
            $approved = $conn->query("SELECT COUNT(*) AS c FROM assets WHERE user_id=$uid AND status='approved'")->fetch_assoc()['c'];
            $pending  = $conn->query("SELECT COUNT(*) AS c FROM assets WHERE user_id=$uid AND status='pending'")->fetch_assoc()['c'];

            $pdf    = $conn->query("SELECT COUNT(*) AS c FROM assets WHERE user_id=$uid AND type='PDF'")->fetch_assoc()['c'];
            $jpg    = $conn->query("SELECT COUNT(*) AS c FROM assets WHERE user_id=$uid AND type='JPG'")->fetch_assoc()['c'];
            $png    = $conn->query("SELECT COUNT(*) AS c FROM assets WHERE user_id=$uid AND type='PNG'")->fetch_assoc()['c'];
            $excel  = $conn->query("SELECT COUNT(*) AS c FROM assets WHERE user_id=$uid AND type='EXCEL'")->fetch_assoc()['c'];
            $docs   = $conn->query("SELECT COUNT(*) AS c FROM assets WHERE user_id=$uid AND type='DOCS'")->fetch_assoc()['c'];

            // Creating unique chart ID for JS
            $chartData[] = "createChart('chartUser$uid', [$pdf,$jpg,$png,$excel,$docs]);";
        ?>

        <div class="col-lg-6">
            <div class="card user-card p-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0 text-dark"><?= htmlspecialchars($user['username']) ?></h5>
                    <span class="badge bg-light text-secondary border px-3 py-2"><?= htmlspecialchars($user['department']) ?></span>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-4">
                        <div class="stat-box bg-teal-light">
                            <small>Total</small><h4><?= $total ?></h4>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="stat-box bg-blue-light">
                            <small>Approved</small><h4><?= $approved ?></h4>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="stat-box bg-amber-light">
                            <small>Pending</small><h4><?= $pending ?></h4>
                        </div>
                    </div>
                </div>

                <div class="row align-items-center">
                    <div class="col-md-5" style="height:200px; position: relative;">
                        <canvas id="chartUser<?= $uid ?>"></canvas>
                    </div>
                    <div class="col-md-7 ps-md-4">
                        <h6 class="fw-bold text-muted small text-uppercase mb-3">File Type Breakdown</h6>
                        <ul class="list-group">
                            <li class="list-group-item d-flex justify-content-between"><span>PDF</span> <span class="badge badge-pill bg-danger bg-opacity-75"><?= $pdf ?></span></li>
                            <li class="list-group-item d-flex justify-content-between"><span>JPG</span> <span class="badge badge-pill bg-success bg-opacity-75"><?= $jpg ?></span></li>
                            <li class="list-group-item d-flex justify-content-between"><span>PNG</span> <span class="badge badge-pill bg-warning text-dark bg-opacity-75"><?= $png ?></span></li>
                            <li class="list-group-item d-flex justify-content-between"><span>Excel</span> <span class="badge badge-pill bg-success"><?= $excel ?></span></li>
                            <li class="list-group-item d-flex justify-content-between"><span>Docs</span> <span class="badge badge-pill bg-primary bg-opacity-75"><?= $docs ?></span></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>

    <?php else: ?>
        <div class="col-12 text-center py-5">
            <div class="p-5 bg-white rounded-3 shadow-sm d-inline-block">
                <i class="bi bi-person-x fs-1 text-muted opacity-50"></i>
                <p class="text-muted mt-2 mb-0">No users found matching your search.</p>
            </div>
        </div>
    <?php endif; ?>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function createChart(id, dataArr){
    // If all data is 0, show a gray 'Empty' segment to make the chart visible but blank
    const isEmpty = dataArr.every(item => item === 0);
    const chartData = isEmpty ? [1] : dataArr;
    const chartColors = isEmpty ? ['#e0e0e0'] : ['#ef5350','#66bb6a','#ffca28','#2e7d32','#42a5f5']; // Matches badge colors
    
    new Chart(document.getElementById(id), {
        type: 'doughnut',
        data: {
            labels: isEmpty ? ['No Data'] : ['PDF','JPG','PNG','Excel','Docs'],
            datasets: [{
                data: chartData,
                backgroundColor: chartColors,
                borderColor: '#ffffff',
                borderWidth: 2,
                hoverOffset: 4
            }]
        },
        options: { 
            responsive: true, 
            maintainAspectRatio: false, 
            plugins: {
                legend: { display: false }, // Hide legend to save space, list is on the right
                tooltip: { enabled: !isEmpty }
            },
            cutout: '75%' // Thinner ring for modern look
        }
    });
}

<?php foreach($chartData as $js) echo $js."\n"; ?>
</script>

<?php require_once 'footer.php'; ?>