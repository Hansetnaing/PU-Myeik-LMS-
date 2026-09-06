<?php
require 'auth.php';
require_role('teacher');

require 'dbConnect.php';

$user_id = $_SESSION['t_id'];
$select = "SELECT * FROM teacher where teacher_id = '$user_id'";
$stmt = mysqli_query($con, $select);
$user = mysqli_fetch_assoc($stmt);

if (!$user) {
    die("User not found.");
}

$select_classes = "SELECT * FROM class where teacher_id = '$user_id'";
$stmt_classes = mysqli_query($con,$select_classes);
$classes = mysqli_fetch_all($stmt_classes, MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_class'])) {
    verify_csrf();
    $class_name = $_POST['cname'];
    $subject = $_POST['subject'];
    $year = $_POST['year'];
    $section = $_POST['section'];

    $create = $con->prepare('INSERT INTO class (class_name, subject, year, section, teacher_id) VALUES (?, ?, ?, ?, ?)');
    $create->bind_param('ssssi', $class_name, $subject, $year, $section, $user_id);
    $create->execute();

    header("Location: teacher.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_class'])) {
    verify_csrf();
    $class_id = filter_input(INPUT_POST, 'class_id', FILTER_VALIDATE_INT);

    if (!$class_id) {
        $_SESSION['flash_error'] = 'Invalid class selected.';
    } else {
        try {
            mysqli_begin_transaction($con);

            // Remove submissions before their assignments, then remove all class content.
            $delete_submissions = $con->prepare(
                'DELETE sa FROM submit_assignment sa INNER JOIN assignment a ON sa.assignment_id = a.assignment_id WHERE a.class_id = ?'
            );
            $delete_submissions->bind_param('i', $class_id);
            $delete_submissions->execute();
            $delete_submissions->close();

            $delete_assignments = $con->prepare('DELETE FROM assignment WHERE class_id = ?');
            $delete_assignments->bind_param('i', $class_id);
            $delete_assignments->execute();
            $delete_assignments->close();

            $delete_lectures = $con->prepare('DELETE FROM lecture WHERE class_id = ?');
            $delete_lectures->bind_param('i', $class_id);
            $delete_lectures->execute();
            $delete_lectures->close();

            // The teacher_id condition prevents a teacher deleting another teacher's class.
            $delete_class = $con->prepare('DELETE FROM class WHERE class_id = ? AND teacher_id = ?');
            $delete_class->bind_param('ii', $class_id, $user_id);
            $delete_class->execute();
            $deleted = $delete_class->affected_rows;
            $delete_class->close();

            if ($deleted !== 1) {
                throw new RuntimeException('Class not found or you do not have permission to delete it.');
            }

            mysqli_commit($con);
            $_SESSION['flash_success'] = 'Class and its lectures, assignments, and submissions were deleted.';
        } catch (Throwable $e) {
            mysqli_rollback($con);
            $_SESSION['flash_error'] = 'The class could not be deleted. Please try again.';
        }
    }

    header('Location: teacher.php');
    exit;
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
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        $stmt = $con->prepare("UPDATE teacher SET password=? WHERE teacher_id=?");
        $stmt->bind_param("si", $hashed_password, $user_id);

        if ($stmt->execute()) {
            $success = "Password changed successfully!";
        } else {
            $error = "Failed to change password.";
        }

        $stmt->close();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logout'])) {
    verify_csrf();
    logout();
}

$flash_success = $_SESSION['flash_success'] ?? null;
$flash_error = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home | Teacher</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css"> -->
    <link rel="stylesheet" href="css/techel.css">
    <link rel="icon" href="images/footer.png">
</head>
<body>
    <div class="sidebar">
        <h2>Teacher</h2>
        <ul>
            <li><a href="teacher.php?teacher_id=<?php echo $_SESSION['t_id']; ?>">Home</a></li> 
            <?php if (!empty($classes)): ?>
                <?php foreach ($classes as $class): ?>
                    <li>
                        <a href="class_details.php?class_id=<?php echo $class['class_id']; ?>">
                            <?php echo htmlspecialchars($class['class_name']); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            <?php endif; ?>
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
        <div class="create-container">
            <span class="createclass" id="createGroupBtn" onclick="openModal('modal1')">Create Class <i class="fa-solid fa-plus"></i></span>
        </div>
        <?php if ($flash_success): ?>
            <p class="flash-message flash-success"><?php echo htmlspecialchars($flash_success); ?></p>
        <?php endif; ?>
        <?php if ($flash_error): ?>
            <p class="flash-message flash-error"><?php echo htmlspecialchars($flash_error); ?></p>
        <?php endif; ?>
        <div class="card-container">
            <?php if (!empty($classes)): ?>
                <?php foreach ($classes as $class): ?>
                    <div class="card class-card">
                        <a class="class-card-link" href="class_details.php?class_id=<?php echo (int) $class['class_id']; ?>">
                            <h3><?php echo htmlspecialchars($class['class_name']); ?></h3>
                            <p>Subject: <?php echo htmlspecialchars($class['subject']); ?></p>
                            <p>Year: <?php echo htmlspecialchars($class['year']); ?></p>
                            <p>Section: <?php echo htmlspecialchars($class['section']); ?></p>
                        </a>
                        <form method="post" class="delete-class-form" onsubmit="return confirm('Delete this class and all of its lectures, assignments, and submissions? This cannot be undone.');">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token()); ?>">
                            <input type="hidden" name="class_id" value="<?php echo (int) $class['class_id']; ?>">
                            <button type="submit" name="delete_class" class="delete-class-button"><i class="fa-solid fa-trash"></i> Delete class</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
    </div>

    <div id="editForm" class="edit-box">
        <h2>Edit Profile</h2>
        <form id="profileForm" action="" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token()); ?>">
            <input type="hidden" name="teacher_id" value="<?php echo $user['teacher_id']; ?>">

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

    <div id="createGroupModal" class="modal">
        <div class="modal-content">
            <span class="close" id="closeModal">&times;</span>
            <h2>Create Class</h2>
            <form action="" method="post">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token()); ?>">
                <input type="text" name="cname" placeholder="Enter Class Name" style="width: 100%; padding: 10px; margin-top: 10px;">
                <input type="text" name="year" placeholder="Enter Year" style="width: 100%; padding: 10px; margin-top: 10px;">
                <input type="text" name="subject" placeholder="Enter Subject" style="width: 100%; padding: 10px; margin-top: 10px;">
                <input type="text" name="section" placeholder="Section" style="width: 100%; padding: 10px; margin-top: 10px;">
                <button type="submit" name="create_class" style="margin-top: 10px; padding: 10px; width: 30%; background-color: #28a745; color: white; border: none; cursor: pointer; border-radius: 5px;">Create</button>
                <button type="reset" name="reset_class" style="margin-top: 10px; padding: 10px; width: 30%; background-color: #28a745; color: white; border: none; cursor: pointer; border-radius: 5px;">Cancel</button>
            </form>
        </div>
    </div>

    <script src="js/class.js"></script>
    <script src="js/edit.js"></script>
    <!-- <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> -->
</body>
</html>
