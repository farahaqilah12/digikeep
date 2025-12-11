<?php
include 'config.php';
session_start();

if(isset($_POST['submit'])) {
    $user_id = $_SESSION['id'];
    $title = $_POST['title'];
    $type = $_POST['type'];
    $category = $_POST['category'];
    $description = $_POST['description'];
    $file = $_FILES['file']['name'];
    $tmp_name = $_FILES['file']['tmp_name'];
    $folder = "uploads/".$file;

    if(move_uploaded_file($tmp_name, $folder)) {
        $stmt = $conn->prepare("INSERT INTO assets (user_id, title, type, category, description, file_name, status) VALUES (?, ?, ?, ?, ?, ?, 'Pending')");
        $stmt->bind_param("isssss", $user_id, $title, $type, $category, $description, $file);
        if($stmt->execute()) {
            echo "<script>alert('File uploaded successfully!'); window.location='user_dashboard.php';</script>";
        } else {
            echo "<script>alert('Database error'); window.location='user_dashboard.php';</script>";
        }
    } else {
        echo "<script>alert('File upload failed'); window.location='user_dashboard.php';</script>";
    }
}
?>
