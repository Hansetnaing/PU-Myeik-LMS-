<?php require 'auth.php'; require_role('admin'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin | Home</title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="icon" href="images/footer.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body>
    <div class="container">
        <header id="header">
            <div class="header-content">
                <button id="toggle-sidebar">☰</button>
                <h1>Admin</h1>
            </div>
            <div class="log-out">
                <a href="login.php"><i class="fa-solid fa-right-from-bracket"></i></a>
            </div>
        </header>

        <aside id="sidebar">
            <nav>
                <ul>
                    <li><a href="admin.php">Home</a></li>
                    <li><a href="addtchel.php">Add Teacher</a></li>
                    <li><a href="tList.php">Teacher List</a></li>
                    <li><a href="addstu.php">Add Student</a></li>
                    <li><a href="stuList.php">Student List</a></li>
                </ul>
            </nav>
        </aside>

        <main id="content">
            <div class="main-header">
            <h2>Student List</h2>
            <form action="" method="post" class="box">
            <div class="form-group" >
                <select id="year" name="year" required>
                    <option value="all">Select Academic Year</option>
                    <option value="First Year">First Year</option>
                    <option value="Second Year">Second Year</option>
                    <option value="Third Year">Third Year</option>
                    <option value="Fourth Year">Fourth Year</option>
                    <option value="Fifth Year">Fifth Year</option>
                </select>
            </div>
            <div class="form-group">
                <input type="submit" name="submit" value="Search">
            </div>
            </form>
            <input type="text" id="searchBar" onkeyup="searchStudent()" placeholder="Search for students...">
            </div>

            <table id="studentTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Year</th>
                        <th>Major</th>
                        <th>Class</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                require 'dbConnect.php';
                if(isset($_POST['submit'])){
                    $year = $_POST['year'];

                if($year == 'all'){
                    $query = 'select * from student;';
                }
                else{
                    $query = "select * from student where year='$year';";
                }
                
                if($query){
                $result = mysqli_query($con, $query);
                $i=0;
                while($row = mysqli_fetch_array($result)) {
                $i++;
                echo "<tr>";
                echo "<td>".$i."</td>";
                echo "<td>".$row['name']."</td>";
                echo "<td>".$row['email']."</td>";
                echo "<td>".$row['year']."</td>";
                echo "<td>".$row['major']."</td>";
                echo "<td>".$row['class']."</td>";
                echo "<td>";
                echo "<a href='edit_student.php?id=" . $row['student_id'] . "' class='edit'><i class='fa-solid fa-pen-to-square'></i></a>";
                echo "<a href='delete_student.php?id=" . $row['student_id'] . "' class='delete' onclick='return confirm(\"Are you sure you want to delete this student?\");'><i class='fa-solid fa-trash'></i></a>";
                echo "</td>";                
                echo "</tr>";
                }
                }
                }
                ?>

                <?php
                require 'dbConnect.php';
                if(!isset($_POST['submit'])){
                $query = 'select * from student;';
                if($query){
                $result = mysqli_query($con, $query);
                $i=0;
                while($row = mysqli_fetch_array($result)) {
                $i++;
                echo "<tr>";
                echo "<td>".$i."</td>";
                echo "<td>".$row['name']."</td>";
                echo "<td>".$row['email']."</td>";
                echo "<td>".$row['year']."</td>";
                echo "<td>".$row['major']."</td>";
                echo "<td>".$row['class']."</td>";
                echo "<td>";
                echo "<a href='edit_student.php?id=" . $row['student_id'] . "' class='edit'><i class='fa-solid fa-pen-to-square'></i></a>";
                echo "<a href='delete_student.php?id=" . $row['student_id'] . "' class='delete' onclick='return confirm(\"Are you sure you want to delete this student?\");'><i class='fa-solid fa-trash'></i></a>";
                echo "</td>";                
                echo "</tr>";
                }
                }
            }
                ?>
                </tbody>
            </table>


        </main>

    </div>
</body>
<script src="js/admin.js"></script>
</html>
