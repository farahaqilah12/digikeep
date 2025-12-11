<?php
require_once 'config.php';
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }
$uid = $_SESSION['user_id'];
$id = intval($_GET['id'] ?? 0);

// Only user who uploaded or admin can delete
if ($_SESSION['role'] === 'admin') {
    $stmt = $conn->prepare("SELECT file_path FROM assets WHERE asset_id=?");
    $stmt->bind_param("i",$id); $stmt->execute(); $row = $stmt->get_result()->fetch_assoc();
    if ($row && !empty($row['file_path']) && file_exists($row['file_path'])) unlink($row['file_path']);
    $del = $conn->prepare("DELETE FROM assets WHERE asset_id=?"); $del->bind_param("i",$id); $del->execute();
    header("Location: admin_dashboard.php"); exit;
} else {
    $stmt = $conn->prepare("SELECT file_path FROM assets WHERE asset_id=? AND user_id=?");
    $stmt->bind_param("ii",$id,$uid); $stmt->execute(); $row = $stmt->get_result()->fetch_assoc();
    if ($row) {
        if (!empty($row['file_path']) && file_exists($row['file_path'])) unlink($row['file_path']);
        $del = $conn->prepare("DELETE FROM assets WHERE asset_id=? AND user_id=?"); $del->bind_param("ii",$id,$uid); $del->execute();
    }
    header("Location: user_dashboard.php"); exit;
}
