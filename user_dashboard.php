<?php
// 1. START SESSION & SECURITY CHECKS FIRST
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

$uid = $_SESSION['user_id'];
$uRes = $conn->query("SELECT username FROM users WHERE user_id=$uid");
$userRow = $uRes->fetch_assoc();
$current_username = $userRow['username'] ?? 'User';

$departments = [
    'IT' => 'Information Technology (IT)',
    'Finance' => 'Finance',
    'Marketing' => 'Marketing',
    'HR' => 'Human Resources (HR)',
    'Operations' => 'Operations',
    'Sales' => 'Sales',
    'R&D' => 'Research & Development (R&D)',
    'Executive' => 'Executive'
];

$tab = isset($_GET['tab']) ? $_GET['tab'] : 'dashboard';
$msg = '';

// --- HANDLE DELETE ACTION ---
if (isset($_GET['delete_id'])) {
    $del_id = (int)$_GET['delete_id'];
    $check = $conn->query("SELECT file_path FROM assets WHERE asset_id=$del_id AND user_id=$uid");
    if ($check->num_rows > 0) {
        $row = $check->fetch_assoc();
        if (file_exists($row['file_path'])) unlink($row['file_path']);
        $conn->query("DELETE FROM assets WHERE asset_id=$del_id");
        $msg = "Asset deleted successfully.";
    }
    echo "<script>window.location.href='user_dashboard.php?tab=myfiles';</script>";
    exit;
}

// --- HANDLE FORM SUBMISSION (UPLOAD OR UPDATE) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Common Variables
    $name = trim($_POST['name']);
    $department = trim($_POST['department']);
    $title = trim($_POST['title']);
    $type = $_POST['type'];
    // Category removed
    $description = trim($_POST['description']);
    
    // 2. CHECK IF THIS IS AN UPDATE OR NEW UPLOAD
    if (isset($_POST['asset_id']) && !empty($_POST['asset_id'])) {
        // --- UPDATE LOGIC ---
        $edit_id = (int)$_POST['asset_id'];
        
        // Handle File Replacement (Optional on Edit)
        $file_update_sql = "";
        if (!empty($_FILES['file']['name'])) {
            $orig = basename($_FILES['file']['name']);
            $safe = time() . "_" . preg_replace('/[^A-Za-z0-9\-_\.]/','_', $orig);
            $targetDir = __DIR__ . '/uploads/';
            if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);
            $targetFile = $targetDir . $safe;
            
            if (move_uploaded_file($_FILES['file']['tmp_name'], $targetFile)) {
                $fp = 'uploads/' . $safe;
                $oldQ = $conn->query("SELECT file_path FROM assets WHERE asset_id=$edit_id");
                $oldR = $oldQ->fetch_assoc();
                if ($oldR && file_exists($oldR['file_path'])) unlink($oldR['file_path']);
                $file_update_sql = ", file_path='$fp', status='pending'";
            }
        }

        // Run Update
        $sql = "UPDATE assets SET name=?, department=?, title=?, type=?, description=? $file_update_sql WHERE asset_id=? AND user_id=?";
        $stmt = $conn->prepare($sql);
        
        // --- FIX IS HERE: Changed "sssssi" to "sssssii" ---
        $stmt->bind_param("sssssii", $name, $department, $title, $type, $description, $edit_id, $uid);
        
        if ($stmt->execute()) $msg = "Asset updated successfully!";
        else $msg = "Update failed: " . $stmt->error;
        
    } else {
        // --- NEW UPLOAD LOGIC ---
        if (!empty($_FILES['file']['name'])) {
            $orig = basename($_FILES['file']['name']);
            $safe = time() . "_" . preg_replace('/[^A-Za-z0-9\-_\.]/','_', $orig);
            $targetDir = __DIR__ . '/uploads/';
            if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);
            $targetFile = $targetDir . $safe;

            if (move_uploaded_file($_FILES['file']['tmp_name'], $targetFile)) {
                $fp = 'uploads/' . $safe;
                $status = 'pending';
                // SQL Insert
                $stmt = $conn->prepare("INSERT INTO assets (user_id,name,department,title,type,description,file_path,status) VALUES (?,?,?,?,?,?,?,?)");
                $stmt->bind_param("isssssss", $uid, $name, $department, $title, $type, $description, $fp, $status);
                if ($stmt->execute()) $msg = "File uploaded successfully! Status: Pending.";
                else $msg = "Database error.";
            } else $msg = "File upload failed.";
        } else $msg = "Please select a file.";
    }
}

// --- EDIT DATA ---
$editData = [];
if ($tab === 'upload' && isset($_GET['edit_id'])) {
    $eId = (int)$_GET['edit_id'];
    $q = $conn->query("SELECT * FROM assets WHERE asset_id=$eId AND user_id=$uid");
    if ($q->num_rows > 0) {
        $editData = $q->fetch_assoc();
    }
}

// --- DASHBOARD DATA ---
if ($tab === 'dashboard') {
    $myStats = $conn->query("SELECT COUNT(*) as total, SUM(status='pending') as pending, SUM(status='approved') as approved FROM assets WHERE user_id=$uid")->fetch_assoc();
    
    $search = $_GET['search'] ?? '';
    $typeFilter = $_GET['type'] ?? '';
    $sql = "SELECT a.*, u.username FROM assets a JOIN users u ON a.user_id = u.user_id WHERE a.status = 'approved'";
    $params = []; $types = "";
    if (!empty($search)) {
        $sql .= " AND (a.title LIKE ? OR a.description LIKE ? OR u.username LIKE ?)";
        $searchTerm = "%$search%";
        $params[] = $searchTerm; $params[] = $searchTerm; $params[] = $searchTerm;
        $types .= "sss";
    }
    if (!empty($typeFilter)) { $sql .= " AND a.type = ?"; $params[] = $typeFilter; $types .= "s"; }
    $sql .= " ORDER BY a.uploaded_at DESC";
    $stmt = $conn->prepare($sql);
    if (!empty($params)) $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $galleryAssets = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// --- MY FILES ---
$myFiles = [];
if ($tab === 'myfiles') {
    $myFiles = $conn->query("SELECT * FROM assets WHERE user_id=$uid ORDER BY uploaded_at DESC");
}
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">

<style>
    /* TEAL THEME */
    html body { font-family: 'Inter', sans-serif; background-color: #f4f8f9 !important; color: #37474f; }
    html body .content { padding: 0 !important; display: flex !important; flex-direction: column !important; min-height: 100vh !important; }
    .dashboard-wrapper { padding: 40px; flex: 1; width: 100%; background-color: #f4f8f9; }
    html body footer { width: 100% !important; margin-top: auto !important; }
    .text-primary { color: #00695c !important; }
    .bg-primary { background-color: #00695c !important; }
    .btn-primary { background-color: #00695c; border-color: #00695c; transition: all 0.3s ease; }
    .btn-primary:hover { background-color: #004d40; border-color: #004d40; transform: translateY(-2px); }
    .btn-outline-secondary:hover { background-color: #00695c; border-color: #00695c; color: white; }
    .form-control, .form-select { border-radius: 8px; padding: 12px 15px; border: 1px solid #cfd8dc; background-color: #ffffff; }
    .form-control:focus, .form-select:focus { border-color: #00897b; box-shadow: 0 0 0 4px rgba(0, 137, 123, 0.15); }
    .compact-stat-card { background: white; border: none; border-radius: 12px; padding: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); display: flex; align-items: center; transition: transform 0.3s ease; border-left: 4px solid transparent; height: 100%; }
    .compact-stat-card:hover { transform: translateY(-4px); box-shadow: 0 10px 25px rgba(0,0,0,0.08); }
    .stat-icon-box { width: 50px; height: 50px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; margin-right: 15px; }
    .bg-light-primary { background: #e0f2f1; color: #00695c; }
    .bg-light-warning { background: #fff8e1; color: #f57f17; }
    .bg-light-success { background: #e8f5e9; color: #2e7d32; }
    .asset-card { transition: all 0.3s ease; border: none; border-radius: 12px; background: white; box-shadow: 0 4px 10px rgba(0,0,0,0.03); overflow: hidden; cursor: pointer; border-top: 3px solid #4db6ac; }
    .asset-card:hover { transform: translateY(-5px); box-shadow: 0 15px 30px rgba(0, 105, 92, 0.15); }
    .file-icon-zone { height: 140px; width: 100%; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #f5f7fa 0%, #e0f2f1 100%); font-size: 3.5rem; color: #78909c; }
    .file-thumb { height: 140px; width: 100%; object-fit: cover; }
    .desc-truncate { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; height: 38px; font-size: 0.85rem; color: #546e7a; margin-bottom: 10px; }
    .upload-card { border: none; border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.04); background: white; border-top: 5px solid #00695c; }
    .upload-zone { border: 2px dashed #b0bec5; border-radius: 12px; padding: 40px; text-align: center; background: #fafafa; position: relative; cursor: pointer; transition: all 0.3s ease; }
    .upload-zone:hover { border-color: #00695c; background: #e0f2f1; }
    .upload-zone i { color: #00695c !important; }
    .table-card { border: none; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.03); overflow: hidden; background: white; }
    .custom-table thead th { background-color: #eceff1; color: #455a64; font-weight: 700; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.6px; border-bottom: 2px solid #cfd8dc; padding: 18px 20px; }
    .custom-table tbody td { padding: 18px 20px; vertical-align: middle; border-bottom: 1px solid #f1f1f1; color: #37474f; }
    
    /* STATUS BADGE STYLING */
    .status-badge { padding: 6px 14px; border-radius: 30px; font-size: 0.75rem; font-weight: 700; letter-spacing: 0.3px; }
    .status-approved { background: #e0f2f1; color: #00695c; }
    .status-pending { background: #fff8e1; color: #f57f17; }
    .status-rejected { background: #ffebee; color: #c62828; }
</style>

<div class="dashboard-wrapper">

    <?php if ($tab === 'dashboard'): ?>
        <div class="row align-items-center mb-4 g-3">
            <div class="col-lg-4">
                <h4 class="fw-bold text-dark mb-1">Hello, <?= htmlspecialchars($current_username) ?>! 👋</h4>
                <p class="text-muted small mb-0">Welcome to your community asset dashboard.</p>
            </div>
            <div class="col-lg-8">
                <div class="row g-3">
                    <div class="col-md-4 col-sm-6"><div class="compact-stat-card"><div class="d-flex align-items-center"><div class="stat-icon-box bg-light-primary"><i class="bi bi-cloud-upload"></i></div><div><h5 class="mb-0 fw-bold"><?= $myStats['total'] ?? 0 ?></h5><small class="text-muted">My Uploads</small></div></div></div></div>
                    <div class="col-md-4 col-sm-6"><div class="compact-stat-card"><div class="d-flex align-items-center"><div class="stat-icon-box bg-light-warning"><i class="bi bi-hourglass-split"></i></div><div><h5 class="mb-0 fw-bold"><?= $myStats['pending'] ?? 0 ?></h5><small class="text-muted">Pending</small></div></div></div></div>
                    <div class="col-md-4 col-sm-6"><div class="compact-stat-card"><div class="d-flex align-items-center"><div class="stat-icon-box bg-light-success"><i class="bi bi-check-circle"></i></div><div><h5 class="mb-0 fw-bold"><?= $myStats['approved'] ?? 0 ?></h5><small class="text-muted">Approved</small></div></div></div></div>
                </div>
            </div>
        </div>
        
        <div class="d-flex justify-content-between align-items-center mb-3 px-1">
            <h5 class="fw-bold text-dark mb-0"><i class="bi bi-grid-fill me-2 text-primary"></i>Community Gallery</h5>
            <small class="text-muted">Showing all approved files</small>
        </div>

        <div class="card p-3 mb-4 border-0 shadow-sm bg-white rounded-3">
            <form method="GET" class="row g-2 align-items-center">
                <input type="hidden" name="tab" value="dashboard">
                <div class="col-md-5"><div class="input-group"><span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span><input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Search files..." value="<?= htmlspecialchars($_GET['search']??'') ?>"></div></div>
                <div class="col-md-3"><select name="type" class="form-select"><option value="">All Types</option><option value="PDF">PDF</option><option value="JPG">JPG</option><option value="PNG">PNG</option><option value="EXCEL">Excel</option><option value="DOCS">Docs</option></select></div>
                <div class="col-md-2"><button class="btn btn-primary w-100 fw-bold">Filter</button></div>
            </form>
        </div>

        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
            <?php if(count($galleryAssets) > 0): ?>
                <?php foreach($galleryAssets as $asset): 
                    $isImage = in_array(strtoupper($asset['type']), ['JPG','PNG','JPEG','GIF']);
                    $iconClass = 'bi-file-earmark';
                    if($asset['type'] == 'PDF') $iconClass = 'bi-file-earmark-pdf text-danger';
                    if($asset['type'] == 'EXCEL') $iconClass = 'bi-file-earmark-excel text-success';
                    $safeTitle = htmlspecialchars($asset['title']);
                    $safeDesc = htmlspecialchars($asset['description'] ?: 'No description available.');
                    $safeUser = htmlspecialchars($asset['username']);
                    $safeType = htmlspecialchars($asset['type']);
                    $safePath = htmlspecialchars($asset['file_path']);
                    $safeDate = date('M d, Y', strtotime($asset['uploaded_at']));
                ?>
                <div class="col">
                    <div class="card asset-card h-100" onclick="showAssetDetails('<?= $safeTitle ?>', '<?= $safeDesc ?>', '<?= $safeUser ?>', '<?= $safeType ?>', '<?= $safeDate ?>', '<?= $safePath ?>', '<?= $isImage ? 1 : 0 ?>')">
                        <?php if($isImage): ?><img src="<?= $safePath ?>" class="file-thumb"><?php else: ?><div class="file-icon-zone"><i class="bi <?= $iconClass ?>"></i></div><?php endif; ?>
                        <div class="card-body p-3">
                            <h6 class="card-title text-truncate fw-bold text-dark mb-1"><?= $safeTitle ?></h6>
                            <div class="d-flex justify-content-between align-items-center mb-2"><small class="text-muted" style="font-size: 0.75rem;">by <span class="fw-bold text-dark"><?= $safeUser ?></span></small><span class="badge bg-light text-secondary border" style="font-size: 0.65rem;"><?= $safeType ?></span></div>
                            <p class="desc-truncate"><?= $safeDesc ?></p>
                            <button class="btn btn-sm btn-light text-primary w-100 fw-bold border">View Details</button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5"><p class="text-muted">No files found.</p></div>
            <?php endif; ?>
        </div>

    <?php elseif ($tab === 'upload'): ?>
        <div class="row justify-content-center">
            <div class="col-lg-9">
                <div class="card upload-card p-4 p-md-5">
                    <div class="text-center mb-4">
                        <h4 class="fw-bold" style="color: #00695c;">
                            <?= !empty($editData) ? 'Edit Asset' : 'Upload New Asset' ?>
                        </h4>
                        <p class="text-muted">
                            <?= !empty($editData) ? 'Update your document details below' : 'Share your documents with the team' ?>
                        </p>
                    </div>
                    <?php if($msg): ?> <div class="alert alert-info text-center"><?= $msg ?></div> <?php endif; ?>
                    
                    <form method="post" enctype="multipart/form-data">
                        <?php if(!empty($editData)): ?>
                            <input type="hidden" name="asset_id" value="<?= $editData['asset_id'] ?>">
                        <?php endif; ?>

                        <div class="upload-zone mb-4">
                            <input type="file" name="file" <?= empty($editData) ? 'required' : '' ?>>
                            <i class="bi bi-cloud-arrow-up" style="font-size: 3rem;"></i>
                            <h5 class="mt-3 text-dark">
                                <?= !empty($editData) ? 'Click to Replace File (Optional)' : 'Click or Drag file here' ?>
                            </h5>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Uploader Name</label>
                                <input name="name" class="form-control" required placeholder="Full Name" value="<?= $editData['name'] ?? '' ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Department</label>
                                <select name="department" class="form-select" required>
                                    <option value="">Select Department</option>
                                    <?php foreach ($departments as $v => $l): ?>
                                        <option value="<?= $v ?>" <?= (isset($editData['department']) && $editData['department'] == $v) ? 'selected' : '' ?>><?= $l ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label small fw-bold text-muted">Title</label>
                                <input name="title" class="form-control" required placeholder="Document Title" value="<?= $editData['title'] ?? '' ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-muted">Type</label>
                                <select name="type" class="form-select" required>
                                    <?php $t = $editData['type'] ?? ''; ?>
                                    <option value="PDF" <?= $t=='PDF'?'selected':'' ?>>PDF</option>
                                    <option value="JPG" <?= $t=='JPG'?'selected':'' ?>>JPG</option>
                                    <option value="PNG" <?= $t=='PNG'?'selected':'' ?>>PNG</option>
                                    <option value="EXCEL" <?= $t=='EXCEL'?'selected':'' ?>>EXCEL</option>
                                    <option value="DOCS" <?= $t=='DOCS'?'selected':'' ?>>DOCS</option>
                                </select>
                            </div>
                            
                            <div class="col-12">
                                <label class="form-label small fw-bold text-muted">Description</label>
                                <textarea name="description" class="form-control" rows="3"><?= $editData['description'] ?? '' ?></textarea>
                            </div>
                            <div class="col-12 mt-4">
                                <button class="btn btn-primary w-100 py-3 fw-bold rounded-pill shadow-sm">
                                    <?= !empty($editData) ? 'Update Asset' : 'Submit Asset' ?>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    <?php elseif ($tab === 'myfiles'): ?>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div><h4 class="fw-bold mb-0">My Uploads</h4><p class="text-muted small mb-0">Track and manage your submissions</p></div>
            <a href="user_dashboard.php?tab=upload" class="btn btn-primary rounded-pill px-4 shadow-sm"><i class="bi bi-plus-lg me-1"></i> New Upload</a>
        </div>

        <div class="card table-card">
            <div class="table-responsive">
                <table class="table custom-table mb-0">
                    <thead>
                        <tr><th class="ps-4">File Details</th><th>Type</th><th>Date</th><th>Status</th><th class="text-end pe-4">Action</th></tr>
                    </thead>
                    <tbody>
                        <?php if($myFiles->num_rows > 0): ?>
                            <?php while($row = $myFiles->fetch_assoc()): ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($row['title']) ?></div>
                                    <div class="small text-muted"><?= htmlspecialchars($row['name']) ?></div>
                                </td>
                                <td><span class="badge bg-light text-dark border"><?= $row['type'] ?></span></td>
                                <td class="text-muted small"><?= date('M d, Y', strtotime($row['uploaded_at'])) ?></td>
                                
                                <td>
                                    <?php 
                                        $s = $row['status'];
                                        $c = ($s == 'approved') ? 'status-approved' : (($s == 'rejected') ? 'status-rejected' : 'status-pending');
                                    ?>
                                    <span class="status-badge <?= $c ?>"><?= ucfirst($s) ?></span>
                                </td>

                                <td class="text-end pe-4">
                                    <div class="btn-group">
                                        <a href="<?= htmlspecialchars($row['file_path']) ?>" download class="btn btn-sm btn-light border text-dark" title="Download"><i class="bi bi-download"></i></a>
                                        
                                        <a href="user_dashboard.php?tab=upload&edit_id=<?= $row['asset_id'] ?>" class="btn btn-sm btn-light border text-primary" title="Edit">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>

                                        <a href="user_dashboard.php?tab=myfiles&delete_id=<?= $row['asset_id'] ?>" class="btn btn-sm btn-light border text-danger" title="Delete" onclick="return confirm('Are you sure?');">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="text-center py-5 text-muted">You haven't uploaded any files yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

</div>

<div class="modal fade" id="viewModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4">
      <div class="modal-header border-0 pb-0"><h5 class="modal-title fw-bold">Asset Details</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body p-4">
          <div id="modalImgContainer" class="mb-3 text-center" style="display:none;"><img id="modalImg" src="" class="img-fluid rounded shadow-sm border" style="max-height: 300px;"></div>
          <div class="mb-3"><span class="badge bg-primary rounded-pill px-3" id="modalType">TYPE</span> <span class="text-muted small ms-2" id="modalDate">Date</span></div>
          <h3 id="modalFileTitle" class="fw-bold mb-2 text-dark">Title</h3>
          <p class="text-muted small mb-3">Uploaded by: <span class="fw-bold text-dark fs-6" id="modalUser">User</span></p>
          <div class="p-3 bg-light rounded border"><h6 class="fw-bold text-secondary small text-uppercase">Description</h6><p id="modalDesc" class="text-secondary mb-0 small">...</p></div>
      </div>
      <div class="modal-footer border-0 pt-0 px-4 pb-4"><button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Close</button><a href="#" id="modalDownloadBtn" download class="btn btn-primary fw-bold rounded-pill px-4 shadow-sm">Download</a></div>
    </div>
  </div>
</div>

<script>
function showAssetDetails(title, desc, user, type, date, path, isImage) {
    document.getElementById('modalTitle').innerText = title;
    document.getElementById('modalFileTitle').innerText = title;
    document.getElementById('modalDesc').innerText = desc;
    document.getElementById('modalUser').innerText = user;
    document.getElementById('modalType').innerText = type;
    document.getElementById('modalDate').innerText = date;
    document.getElementById('modalDownloadBtn').href = path;
    const imgC = document.getElementById('modalImgContainer');
    const imgE = document.getElementById('modalImg');
    if (isImage == 1) { imgE.src = path; imgC.style.display = 'block'; } else { imgC.style.display = 'none'; }
    var myModal = new bootstrap.Modal(document.getElementById('viewModal'));
    myModal.show();
}
</script>

<?php require_once 'footer.php'; ?>