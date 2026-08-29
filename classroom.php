<?php
require 'auth.php';
require_role('student');
require 'uploads.php';

require 'dbConnect.php';

// Check if class_id and student_id are provided in the URL
if (!isset($_GET['class_id']) || !isset($_GET['student_id'])) {
    die("Class ID or Student ID not provided.");
}

$class_id = trim($_GET['class_id']);
$class_id = intval($class_id);
$student_id = $_GET['student_id'];

$select = "SELECT * FROM student WHERE student_id='$student_id'";
$query = mysqli_query($con, $select);
$user = mysqli_fetch_assoc($query);

if (!$user) {
    die("Student not found.");
}


if ($_SESSION['s_id'] != $student_id) {
    die("You are not authorized to access this page.");
}

$studentClass = mysqli_real_escape_string($con, $user['class']);
$class_query = "select class_name,subject,section,name from class join teacher on class.teacher_id = teacher.teacher_id where class_id = '$class_id' and class.class_name = '$studentClass';";
$class_result = mysqli_query($con, $class_query);
$class = mysqli_fetch_assoc($class_result);

if (!$class) {
    die("Class not found.");
}

$student_query = "SELECT * FROM student WHERE student_id = '$student_id'";
$student_result = mysqli_query($con, $student_query);
$student = mysqli_fetch_assoc($student_result);

if (!$student) {
    die("Student not found.");
}

$assignments_query = "SELECT * FROM assignment WHERE class_id = '$class_id' ORDER BY assignment_id DESC";
$assignments_result = mysqli_query($con, $assignments_query);

if (!$assignments_result) {
    die("Error fetching assignments: " . mysqli_error($con));
}

$assignments = mysqli_fetch_all($assignments_result, MYSQLI_ASSOC);

if (!$assignments) {
    echo count($assignments);
}

$lecture_query = "select * from lecture where class_id= '$class_id' order by lecture_id desc;";
$lecture_result = mysqli_query($con,$lecture_query);

if (!$lecture_result) {
    die("Error fetching assignments: " . mysqli_error($con));
}

$lectures = mysqli_fetch_all($lecture_result, MYSQLI_ASSOC);

if (!$lectures) {
    $assignments = []; 
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    if (isset($_POST['assignment_id'])) {
        $assignment_id = $_POST['assignment_id'];

        // Check if the student has already submitted the assignment
        $check_query = "SELECT * FROM submit_assignment WHERE student_id = '$student_id' AND assignment_id = '$assignment_id'";
        $check_result = mysqli_query($con, $check_query);
        $submission = mysqli_fetch_assoc($check_result);

        // If the student is submitting a new file
        if (isset($_FILES['submit-ass']) && $_FILES['submit-ass']['error'] === UPLOAD_ERR_OK) {
            $file_path = upload_document($_FILES['submit-ass'], 'uploads/submit');

            // If the student has already submitted, delete the old file
            if ($submission && file_exists($submission['file'])) {
                unlink($submission['file']); // Delete the old file
            }

            // Move the new file to the upload directory
            if ($file_path) {
                // Insert or update the submission record
                if ($submission) {
                    $query = "UPDATE submit_assignment SET file = '$file_path' WHERE student_id = '$student_id' AND assignment_id = '$assignment_id'";
                } else {
                    $query = "INSERT INTO submit_assignment (student_id, assignment_id, file) 
                              VALUES ('$student_id', '$assignment_id', '$file_path')";
                }

                if (mysqli_query($con, $query)) {
                    $success = 'Submit Successfully!';
                    header("Location: classroom.php?class_id=$class_id&student_id=$student_id&success=Assignment submitted successfully!");
                    exit;
                }
            }
        }

        // If the student is unsubmitting (deleting the submission)
        if (isset($_POST['unsubmit-ass'])) {
            if ($submission) {
                // Delete the file from the server
                if (file_exists($submission['file'])) {
                    unlink($submission['file']);
                }

                // Delete the submission record from the database
                $delete_query = "DELETE FROM submit_assignment WHERE student_id = '$student_id' AND assignment_id = '$assignment_id'";
                if (mysqli_query($con, $delete_query)) {
                    $success = 'Unsubmit Successfully!';
                    header("Location: classroom.php?class_id=$class_id&student_id=$student_id&success=Assignment unsubmitted successfully!");
                    exit;
                }
            }
        }
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {

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


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student | Classroom</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="css/techel.css">
    <link rel="icon" href="images/footer.png">
</head>
<body>
    <div class="sidebar">
        <h2>Student</h2>
        <ul>
            <li><a href="student.php">Home</a></li>
        </ul>
    </div>
    
    <div class="main-content">
        <div class="top-nav">
            <div class="left-menu">
                <h2>Learning Hub</h2>
            </div>
            <div class="right-menu">
                <span class="pname">Welcome, <?php echo htmlspecialchars($student['name']); ?></span>
                <img src="./images/profile.webp" alt="Edit Profile" onclick="toggleEditForm()" style="cursor: pointer; width: 40px; height: 40px; border-radius: 50%; margin-left: 10px;">   
            </div>
        </div>

        <section>
            <div class="teacher">
            <h2>Teacher: <?php echo htmlspecialchars($class['name']); ?></h2>
            <h3>Subject: <?php echo htmlspecialchars($class['subject']); ?><hr></h3>
            </div>
        </section>
        <section>
        <div class="sub-assignment">
    <h1><i class="fa-regular fa-file-lines"></i> Assignments</h1>
    <?php if (!empty($assignments)): ?>
        <?php foreach ($assignments as $assignment): ?>
            <?php
            // Check if the student has already submitted the assignment
            $assignment_id = $assignment['assignment_id'];
            $check_query = "SELECT * FROM submit_assignment WHERE student_id = '$student_id' AND assignment_id = '$assignment_id'";
            $check_result = mysqli_query($con, $check_query);
            $submission = mysqli_fetch_assoc($check_result);
            ?>
            <div class="assignment">
                <p><strong><?php echo htmlspecialchars($assignment['title']); ?></strong></p>
                <p><?php echo htmlspecialchars($assignment['description']); ?></p>
                <p><strong>Due Date:</strong> <?php echo htmlspecialchars($assignment['due_date']); ?></p>
                <p><strong>File:</strong> <a href="<?php echo htmlspecialchars($assignment['file']); ?>" class="link" target="_blank">Download</a></p>
                <form action="" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token()); ?>">
                    <input type="hidden" name="assignment_id" value="<?php echo htmlspecialchars($assignment['assignment_id']); ?>">    
                    <label for="upload">Submitted File: </label>
                    <?php if ($submission): ?>
                        <!-- Display the submitted file in green -->
                        <p style="color: green;">Submitted File: <a href="<?php echo htmlspecialchars($submission['file']); ?>" target="_blank"><?php echo htmlspecialchars(basename($submission['file'])); ?></a></p>
                        <!-- Unsubmit button -->
                        <button type="submit" name="unsubmit-ass" class="submit-btn">Unsubmit<i class="fa-solid fa-arrow-down"></i></button>
                    <?php else: ?>
                        <!-- File input for new submission -->
                        <input type="file" name="submit-ass">
                        <?php if (isset($success)): ?>
                            <p style="color: green;"><?php echo $success; ?></p>
                        <?php endif; ?>
                        <!-- Submit button -->
                        <button type="submit" name="sub-ass" class="submit-btn">Submit<i class="fa-solid fa-arrow-up"></i></button>
                    <?php endif; ?>
                </form>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>    
        </section>
        <section>
            <div class="sub-assignment">
            <h1><i class="fa-solid fa-book-bookmark"></i> Lectures</h1>
                <?php if (!empty($lectures)): ?>
                    <?php foreach ($lectures as $lecture): ?>
                        <div class="lecture">
                            <p><strong><?php echo htmlspecialchars($lecture['title']); ?></strong></p>
                            <p><?php echo htmlspecialchars($lecture['description']); ?></p>
                            <p><strong>File:</strong> <a href="<?php echo htmlspecialchars($lecture['file']); ?>" class="link" target="_blank">Download</a></p>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                </div>
        </section>
    </div>

    <div id="editForm" class="edit-box">
        <h2>Edit Profile</h2>
        <form id="profileForm" action="" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token()); ?>">
            <input type="hidden" name="student_id" value="<?php echo $user['student_id']; ?>">

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
<!-- <script src="js/edit.js"></script> -->
<?php if (isset($success) || isset($error)): ?>
<script>
    sessionStorage.setItem('modalState', 'open');
</script>
<?php endif; ?>
</body>
</html>
