<?php
require 'auth.php';
require_role('admin');
require 'dbConnect.php';

// Check if the teacher ID is provided in the URL


if (isset($_POST['update'])) {
    verify_csrf();
    $teacher_id = (int) $_POST['teacher_id'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $dept_name = $_POST['department'];

    $query = "UPDATE teacher SET name = '$name', email = '$email', dept_name = '$dept_name' WHERE teacher_id = $teacher_id";
    if (mysqli_query($con, $query)) {
        $success = "Teacher updated successfully!";
    } else {
        $error = "Error updating teacher: " . mysqli_error($con);
    }
}

if (isset($_GET['id'])) {
    $teacher_id = (int) $_GET['id'];

    $query = "SELECT * FROM teacher WHERE teacher_id = $teacher_id";
    $result = mysqli_query($con, $query);
    $row = mysqli_fetch_assoc($result);

    if ($row) {
        $dept_query = "select distinct(dept_name) from teacher";
        $dept_result = mysqli_query($con, $dept_query);
?>        
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin | Edit Teacher</title>
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
            <h1>Edit Teacher</h1>
            <form action="" method="post">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token()); ?>">
            <input type="hidden" name="teacher_id" value="<?php echo $row['teacher_id']; ?>">
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" id="name" name="name" value="<?php echo $row['name']; ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" value="<?php echo $row['email']; ?>" required>
                </div>

                <div class="form-group">
                        <label for="department">Department:</label>
                        <select id="department" name="department" required>
                            <?php
                            while ($dept_row = mysqli_fetch_assoc($dept_result)) {
                                $selected = ($dept_row['dept_name'] == $row['dept_name']) ? 'selected' : '';
                                echo "<option value='" . $dept_row['dept_name'] . "' $selected>" . $dept_row['dept_name'] . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                    
                    <?php if (isset($success)): ?>
                        <p style="color: green;"><?php echo $success; ?></p>
                    <?php endif; ?>

                    <div class="form-group">
                    <button type="submit" name="update" class="create-button">Edit Teacher</button>
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
