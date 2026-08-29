<?php
require 'auth.php';
require_role('admin');
require 'dbConnect.php';

if (isset($_GET['id'])) {
    $student_id = (int) $_GET['id'];

    $query = "delete from student where student_id = $student_id";
    if (mysqli_query($con, $query)) {
        header("Location: stuList.php"); 
    } else {
        echo "Error deleting teacher: " . mysqli_error($con);
    }
} 
?>
