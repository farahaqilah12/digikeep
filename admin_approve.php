<?php
require_once 'config.php';
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role']!=='admin') { header("Location: login.php"); exit; }
$id = intval($_GET['id'] ?? 0);
if ($id>0) {
    $stmt = $conn->prepare("UPDATE assets SET status='approved', reject_reason=NULL WHERE asset_id=?");
    $stmt->bind_param("i",$id); $stmt->execute();
}
header("Location: admin_dashboard.php");
exit;
