<?php
require 'auth.php';
require_role('admin');
require 'dbConnect.php';

if (isset($_GET['id'])) {
    $teacher_id = (int) $_GET['id'];

    $query = "DELETE FROM teacher WHERE teacher_id = $teacher_id";
    if (mysqli_query($con, $query)) {
        header("Location: tList.php"); 
    } else {
        echo "Error deleting teacher: " . mysqli_error($con);
    }
} 
?>
