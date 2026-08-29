<?php
require 'auth.php';
require_role('admin');
require 'dbConnect.php';

// Check if the form is submitted for updating student details
if (isset($_POST['update'])) {
    verify_csrf();
    $student_id = (int) $_POST['student_id'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $year = $_POST['year'];
    $major = $_POST['major'];

    $query = "update student set name = '$name', email = '$email', year = '$year', major = '$major' where student_id = $student_id";
    if (mysqli_query($con, $query)) {
        $success = "Student updated successfully!";
    } else {
        $error = "Error updating student: " . mysqli_error($con);
    }
}

if (isset($_GET['id'])) {
    $student_id = (int) $_GET['id'];

    $query = "SELECT * FROM student where student_id = $student_id";
    $result = mysqli_query($con, $query);
    $row = mysqli_fetch_assoc($result);

    if ($row) {
        $major_query = "SELECT DISTINCT(major) FROM student";
        $major_result = mysqli_query($con, $major_query);

        $year_query = "SELECT DISTINCT(year) FROM student";
        $year_result = mysqli_query($con, $year_query);
?>        
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin | Edit Student</title>
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
            <h1>Edit Student</h1>
                <form action="" method="post">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token()); ?>">
                    <input type="hidden" name="student_id" value="<?php echo $row['student_id']; ?>">
                    <div class="form-group">
                        <label for="name">Name:</label>
                        <input type="text" id="name" name="name" value="<?php echo $row['name']; ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" value="<?php echo $row['email']; ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="year">Year:</label>
                        <select id="year" name="year" required>
                            <?php
                            while ($year_row = mysqli_fetch_assoc($year_result)) {
                                $selected = ($year_row['year'] == $row['year']) ? 'selected' : '';
                                echo "<option value='" . $year_row['year'] . "' $selected>" . $year_row['year'] . "</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="major">Major:</label>
                        <select id="major" name="major" required>
                            <?php
                            while ($major_row = mysqli_fetch_assoc($major_result)) {
                                $selected = ($major_row['major'] == $row['major']) ? 'selected' : '';
                                echo "<option value='" . $major_row['major'] . "' $selected>" . $major_row['major'] . "</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <?php if (isset($success)): ?>
                        <p style="color: green;"><?php echo $success; ?></p>
                    <?php endif; ?>

                    <div class="form-group">
                        <button type="submit" name="update" class="create-button">Update Student</button>
                    </div>
            </form>
        </div>
        </main>
    </div>
    <script src="js/admin.js"></script>
</body>
</html>
<?php
    }
}    
?>
