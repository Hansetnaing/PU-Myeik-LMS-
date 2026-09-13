<?php
require 'auth.php';
require_role('admin');
require 'dbConnect.php';

if(isset($_POST['create'])){
    verify_csrf();

    $name = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $email = $_POST['email'];
    $year = $_POST['year'];
    $major = $_POST['major'];
    mysqli_begin_transaction($con);
    $stmt = $con->prepare('INSERT INTO student (name, password, email, year, major) VALUES (?, ?, ?, ?, ?)');
    $stmt->bind_param('sssss', $name, $password, $email, $year, $major);
    $stuin = $stmt->execute();
    if ($stuin) {
        $student_id = $con->insert_id;
        // New students automatically join every course for their academic year.
        $enroll = $con->prepare('INSERT IGNORE INTO student_course (student_id, course_id) SELECT ?, course_id FROM course WHERE year = ?');
        $enroll->bind_param('is', $student_id, $year);
        $stuin = $enroll->execute();
    }
    if($stuin){
        mysqli_commit($con);
        $success = 'Create Successful!';
    }
    else{
        mysqli_rollback($con);
        $error = 'Something Wrong!';
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin | Add Student</title>
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
            <div class="form-container">
                <h2>Create Student Account</h2>
                <form action="" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token()); ?>">

                    <div id="message">
                        <?php if (isset($success)): ?>
                            <p style="color: green"><?php echo $success ?></p>
                        <?php elseif (isset($error)): ?>
                            <p style="color: red"><?php echo $error ?></p>
                        <?php endif; ?>
                    </div>


                    <div class="form-group">
                        <label for="username">Username:</label>
                        <input type="text" id="username" name="username" required>
                    </div>

                    <div class="form-group">
                        <label for="password">Password:</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" required>
                    </div>

                    <div class="form-group">
                        <label for="year">Year :</label>
                        <select id="year" name="year" required>
                            <option value="">Select Academic Year</option>
                            <option value="First Year">First Year</option>
                            <option value="Second Year">Second Year</option>
                            <option value="Third Year">Third Year</option>
                            <option value="Fourth Year">Fourth Year</option>
                            <option value="Fifth Year">Fifth Year</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="major">Major :</label>
                        <select id="major" name="major" required>
                            <option value="">Select Major</option>
                            <option value="CS">CST</option>
                            <option value="CS">CS</option>
                            <option value="CT">CT</option>
                        </select>
                    </div>

                    <p class="form-help">Students are automatically enrolled in all courses created for their selected academic year.</p>

                    <div class="form-group">
                        <button type="submit" name="create" class="create-button">Create Account</button>
                    </div>
                </form>
            </div>
        </main>

    </div>

    <script src="js/admin.js"></script>
</body>
</html>
