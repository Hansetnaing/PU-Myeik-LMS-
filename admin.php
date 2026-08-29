<?php
require 'auth.php';
require_role('admin');
?>
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
                <form action="logout.php" method="post"><input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token()); ?>"><button type="submit" aria-label="Log out"><i class="fa-solid fa-right-from-bracket"></i></button></form>
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
            <h2>Welcome Admin</h2>
            <p>Assign and manage accounts for teachers and students to access their respective pages.</p>
            
            <!-- Quick Stats -->
            <div class="quick-stats">
                <div class="stat-card">
                    <h3>Total Teachers</h3>
<?php
                    require 'dbConnect.php';
                    $count = 'select * from teacher';
                    $res =mysqli_query($con,$count);
                    $num = mysqli_num_rows($res);
                    ?>
                    <p><?php echo $num ?></p>
                </div>
                <div class="stat-card">
                    <h3>Total Students</h3>
                    <?php
                    require 'dbConnect.php';
                    $countS = 'select * from student';
                    $resS =mysqli_query($con,$countS);
                    $numS = mysqli_num_rows($resS);
                    ?>
                    <p><?php echo $numS ?></p>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="quick-links">
                <a href="addtchel.php" class="link-card">
                    <h3>Add Teacher</h3>
                    <p>Add a new teacher to the system.</p>
                </a>
                <a href="addstu.php" class="link-card">
                    <h3>Add Student</h3>
                    <p>Add a new student to the system.</p>
                </a>
                <a href="tList.php" class="link-card">
                    <h3>View Teacher List</h3>
                    <p>View and manage all teachers.</p>
                </a>
                <a href="stuList.php" class="link-card">
                    <h3>View Student List</h3>
                    <p>View and manage all students.</p>
                </a>
            </div>

            <!-- Recent Activities -->
            <!-- <div class="recent-activities">
                <h3>Recent Activities</h3>
                <ul>
                    <li>Teacher John Doe added.</li>
                    <li>Student Jane Smith updated.</li>
                    <li>New course "Mathematics 101" created.</li>
                </ul>
            </div> -->
        </main>
    </div>

    <script src="js/admin.js"></script>
</body>
</html>
