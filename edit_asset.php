<?php
require_once 'header.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role']!=='user') { header("Location: login.php"); exit; }
$uid = $_SESSION['user_id'];
$id = intval($_GET['id'] ?? 0);
$msg='';

$stmt = $conn->prepare("SELECT * FROM assets WHERE asset_id=? AND user_id=?");
$stmt->bind_param("ii",$id,$uid); $stmt->execute(); $res = $stmt->get_result()->fetch_assoc();
if (!$res) { echo "<div class='alert alert-danger'>Not found or no permission.</div>"; require 'footer.php'; exit; }

if ($_SERVER['REQUEST_METHOD']==='POST') {
    $name = trim($_POST['name']);
    $title = trim($_POST['title']);
    $category = trim($_POST['category']);
    $description = trim($_POST['description']);
    $stmt = $conn->prepare("UPDATE assets SET name=?, title=?, category=?, description=? WHERE asset_id=? AND user_id=?");
    $stmt->bind_param("ssssii",$name,$title,$category,$description,$id,$uid);
    $stmt->execute();
    $msg = "Updated.";
    header("Location: user_dashboard.php"); exit;
}
?>
<div class="container-fluid">
  <h4>Edit Asset</h4>
  <?php if($msg) echo "<div class='alert alert-info'>$msg</div>"; ?>
  <form method="post" class="row g-2">
    <div class="col-md-4"><input name="name" value="<?= htmlspecialchars($res['name']) ?>" class="form-control" required></div>
    <div class="col-md-4"><input name="title" value="<?= htmlspecialchars($res['title']) ?>" class="form-control" required></div>
    <div class="col-md-4"><input name="category" value="<?= htmlspecialchars($res['category']) ?>" class="form-control"></div>
    <div class="col-12"><textarea name="description" class="form-control"><?= htmlspecialchars($res['description']) ?></textarea></div>
    <div class="col-12 text-end"><button class="btn btn-primary">Save</button></div>
  </form>
</div>
<?php require_once 'footer.php'; ?>
