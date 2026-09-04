<?php
require 'auth.php';
require_role('student');

require 'dbConnect.php';

$student_id = $_SESSION['s_id'];

$select = "SELECT * FROM student WHERE student_id='$student_id'";
$query = mysqli_query($con, $select);
$user = mysqli_fetch_assoc($query);

if (!$user) {
    die("Student not found.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    verify_csrf();

    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Server-side password validation
    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&#]).{8,}$/', $new_password)) {
        $error = "Password must be at least 8 characters with uppercase, lowercase, number, and special character.";
    }
    elseif ($new_password !== $confirm_password) {
        $error = "Passwords do not match.";
    }
    else {
        // Hash password (REAL-WORLD SECURITY)
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        // Prepared statement (prevent SQL Injection)
        $stmt = $con->prepare("UPDATE student SET password=? WHERE student_id=?");
        $stmt->bind_param("si", $hashed_password, $student_id);

        if ($stmt->execute()) {
            $success = "Password changed successfully!";
        } else {
            $error = "Failed to change password.";
        }

        $stmt->close();
    }
}

$student_class = $user['class'];

$class_query =  "select class_id,class_name,subject,section,name from class join teacher on class.teacher_id = teacher.teacher_id 
                where class_name = '$student_class';";
$class_result = mysqli_query($con, $class_query);

if (!$class_result) {
    die("Error fetching class details: " . mysqli_error($con));
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logout'])) {
    verify_csrf();
    logout();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home | Student</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css"> -->
    <link rel="icon" href="images/footer.png">
    <link rel="stylesheet" href="css/techel.css">
</head>
<body>
    <div class="sidebar">
        <h2>Student</h2>
        <ul>
            <li><a href="student.php">Home</a></li> 
            <li>
                <form method="post"><input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token()); ?>"><button type="submit" name="logout">Log Out <i class="fa-solid fa-right-from-bracket"></i></button></form>
            </li>
        </ul>
    </div>
    
    <div class="main-content">
        <div class="top-nav">
            <div class="left-menu">
                <h2>PU Myeik LMS System</h2>
            </div>
            <div class="right-menu">
                <span class="pname">Welcome, <?php echo htmlspecialchars($user['name']); ?></span>
                <img src="./images/profile.webp" alt="Edit Profile" onclick="toggleEditForm()" style="cursor: pointer; width: 40px; height: 40px; border-radius: 50%; margin-left: 10px;">   
            </div>
        </div>

        <div class="class-container">
            <?php
            if (mysqli_num_rows($class_result) > 0) {
                while ($class = mysqli_fetch_assoc($class_result)) {
                    echo '
                    <a href="classroom.php?class_id=' . $class['class_id'] . '&student_id=' . $user['student_id'] . '" class="card">
                        <div class="name">
                            <h3>' . htmlspecialchars($class['name']) . '</h3>
                        </div>
                        <div class="class-name">
                            <h3>' . htmlspecialchars($class['subject']) . '</h3>
                            <p>Section ' . htmlspecialchars($class['section']) . '</p>
                        </div>
                    </a>';
                }
            }
            ?>
        </div>

    </div>
    <div id="editForm" class="edit-box">
        <h2>Edit Profile</h2>
        <form id="profileForm" action="" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token()); ?>">
            <input type="hidden" name="teacher_id" value="<?php echo $user['student_id']; ?>">

            <label for="name">Name:</label>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" readonly required>

            <label for="new_password">New Password:</label>
            <input type="password" id="new_password" name="new_password" placeholder="Enter new password">

            <label for="confirm_password">Confirm New Password:</label>
            <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm new password">

            <ul id="passwordRules" style="list-style:none; padding-left:0; font-size:14px; display:none;">
                <li id="rule-length">❌ At least 8 characters</li>
                <li id="rule-upper">❌ At least one uppercase letter</li>
                <li id="rule-lower">❌ At least one lowercase letter</li>
                <li id="rule-number">❌ At least one number</li>
                <li id="rule-special">❌ At least one special character (@$!%*?&#)</li>
            </ul>
            
            <?php if (isset($success)): ?>
                <p style="color: green;"><?php echo $success; ?></p>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <p style="color: red;"><?php echo $error; ?></p>
            <?php endif; ?>

            <button type="submit" name="update" class="update-button">Change Password</button>
        </form>
    </div>
<script src="js/edit.js"></script>
<script>
    sessionStorage.setItem('modalState', 'open');
</script>
</body>
</html>
