<?php
require 'auth.php';
require_role('teacher');

require 'dbConnect.php';

if (isset($_SESSION['t_id'])) {
    header("Location: teacher.php");
    exit;
}
logout();
?>
