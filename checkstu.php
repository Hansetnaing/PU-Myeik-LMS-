<?php
require 'auth.php';
require_role('student');

require 'dbConnect.php';

if (isset($_SESSION['s_id'])) {
    header("Location: student.php");
    exit;
}
logout();
?>
