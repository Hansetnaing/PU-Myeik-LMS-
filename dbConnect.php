<?php
$con = mysqli_connect('localhost', 'root', 'root', 'learnhub_v2');
if(!$con){
    die("Connected Error ".mysqli_error($con));
}
