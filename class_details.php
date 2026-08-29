<?php
require 'auth.php';
require_role('teacher');
require 'uploads.php';

require 'dbConnect.php';

if (!isset($_GET['class_id'])) {
    die("Class ID not provided.");
}
$class_id = $_GET['class_id'];
$teacher_id = $_SESSION['t_id'];

$select_class = "select * FROM class where class_id = '$class_id' and teacher_id = '$teacher_id';";
$resClass = mysqli_query($con,$select_class);
$class = mysqli_fetch_assoc($resClass);

if (!$class) {
    die("You are not authorized to access this class or the class does not exist.");
}

$select_assignments = "select * from assignment where teacher_id = '$teacher_id' and class_id = '$class_id' order by assignment_id desc; ";
$resAss = mysqli_query($con,$select_assignments);
$assignments = mysqli_fetch_all($resAss, MYSQLI_ASSOC);

$select_lectures = "select * FROM lecture where teacher_id = ? and class_id=?";
$stmt_lectures = mysqli_prepare($con, $select_lectures);
mysqli_stmt_bind_param($stmt_lectures, "ii", $_SESSION['t_id'],$class_id);
mysqli_stmt_execute($stmt_lectures);
$result_lectures = mysqli_stmt_get_result($stmt_lectures);
$lectures = mysqli_fetch_all($result_lectures, MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_assignment'])) {
    verify_csrf();
    $title = $_POST['title'];
    $description = $_POST['description'];
    $due_date = $_POST['due_date'];
    $teacher_id = $_SESSION['t_id'];

    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $file_path = upload_document($_FILES['file'], 'uploads/assignment');

        if ($file_path) {

            $insert_assignment = "INSERT INTO assignment (title, description, file, due_date, teacher_id,class_id) VALUES ('$title', '$description', '$file_path', '$due_date', '$teacher_id', '$class_id')";
            mysqli_query($con,$insert_assignment);

            header("Location: class_details.php?class_id=$class_id");
            exit;
        } else {
            $error = "Failed to upload file.";
        }
    }
    
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_lecture'])) {
    verify_csrf();
    $title = $_POST['title'];
    $description = $_POST['description'];

    if (isset($_FILES['lfile']) && $_FILES['lfile']['error'] === UPLOAD_ERR_OK) {
        $file_path = upload_document($_FILES['lfile'], 'uploads/lecture');
        
        if ($file_path) {
            $insert_lecture = "INSERT INTO lecture (title, description, file, teacher_id, class_id) VALUES ('$title', '$description', '$file_path', '$teacher_id', '$class_id')";
            mysqli_query($con,$insert_lecture);

            header("Location: class_details.php?class_id=$class_id");
            exit;
        } else {
            echo "Failed to upload file.";
        }
    } 
}

$class_name = $class['class_name'];

$sql = "SELECT name FROM student WHERE class = '$class_name';";
$res = mysqli_query($con,$sql);

if (!$res) {
    die("Query failed: " . mysqli_error($con));
}

// Delete Section //

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["delete"])) {
    verify_csrf();
    $delete_id = $_POST["delete_id"];
    $delete_id = (int) $delete_id;
    $qry = "DELETE FROM assignment WHERE assignment_id = $delete_id AND class_id = $class_id AND teacher_id = $teacher_id";
    $delete = mysqli_query($con,$qry);
    if($delete){
        header("Location: class_details.php?class_id=$class_id");
        exit;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["lecture"])) {
    verify_csrf();
    $delete_id = $_POST["delete_id"];
    $delete_id = (int) $delete_id;
    $qry = "DELETE FROM lecture WHERE lecture_id = $delete_id AND class_id = $class_id AND teacher_id = $teacher_id";
    $delete = mysqli_query($con,$qry);
    if($delete){
        header("Location: class_details.php?class_id=$class_id");
        exit;
    }
}

// Update Section //

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['updAss'])) {
    verify_csrf();
    $title = $_POST['title'];
    $description = $_POST['description'];
    $due_date = $_POST['due_date'];
    $id = $_POST['id'];

    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $file_name = $_FILES['file']['name'];
        $file_tmp = $_FILES['file']['tmp_name'];
        $file_path = "./uploads/assignment/" . basename($file_name);

        if (move_uploaded_file($file_tmp, $file_path)) {

            $id = (int) $id;
            $updateAssign = "UPDATE assignment SET title='$title', description='$description', file='$file_path', due_date='$due_date', created_at = now() WHERE assignment_id=$id AND class_id=$class_id AND teacher_id=$teacher_id";
            mysqli_query($con,$updateAssign);

            header("Location: class_details.php?class_id=$class_id");
            exit;
        } else {
            $error = "Failed to upload file.";
        }
    }
    
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upLec'])) {
    verify_csrf();
    $title = $_POST['title'];
    $description = $_POST['description'];
    $id = $_POST['id'];

    if (isset($_FILES['lfile']) && $_FILES['lfile']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = "uploads/lecture/"; 
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file_name = basename($_FILES['lfile']['name']);
        $file_path = $upload_dir . $file_name;
        
        if (move_uploaded_file($_FILES['lfile']['tmp_name'], $file_path)) {
            
            $id = (int) $id;
            $updateLec = "UPDATE lecture SET title='$title', description='$description', file='$file_path', create_at = now() WHERE lecture_id=$id AND class_id=$class_id AND teacher_id=$teacher_id";
            mysqli_query($con,$updateLec);

            header("Location: class_details.php?class_id=$class_id");
            exit;
        } else {
            echo "Failed to upload file.";
        }
    } 
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher | Class <?php echo htmlspecialchars($class['class_name']); ?></title>
    <link rel="stylesheet" href="css/class.css">
    <link rel="icon" href="images/footer.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body>
    <!-- Header -->
    <header>
        <h1><?php echo htmlspecialchars($class['class_name']); ?> - <?php echo htmlspecialchars($class['year']); ?></h1>
    </header>

    <div class="main-container">
    <aside class="sidebar">
        <div class="static-item">
            <ul>
                <li><a href="teacher.php?teacher_id=<?php echo $_SESSION['t_id']; ?>"><i style=' margin-right: 10px; ' class="fa-solid fa-house"></i>Home</a></li>
            </ul>
        </div>

        <div class="scrollable-items">
            <ul>    
                <?php
                if (mysqli_num_rows($res) > 0) {
                    while ($result = mysqli_fetch_assoc($res)) {
                        echo "<li><i style=' margin-right: 10px; ' class='fa-solid fa-user'></i>" . htmlspecialchars($result['name']) . "</li>";
                    }
                }
                ?>
            </ul>
        </div>
    </aside>
    
        <div class="content">
            <div class="container">
                <div class="class-btn">
                    <button class="btn" onclick="openModal('assignmentModal')">Add Assignment</button>
                    <button class="btn" onclick="openModal('lectureModal')">Add Lecture</button>
                    <form action="checkStuWork.php" >
                        <input type="hidden" name="class_id" value="<?php echo $class_id; ?>">
                        <button class="btn" >Check Student Work</button>
                    </form>
                </div>
                <!-- Assignments Section -->
                <section>
                    <h1>Assignments <hr></h1>
                    <?php if (!empty($assignments)): ?>
                        <?php foreach ($assignments as $assignment): ?>
                            <div class="assignment">
                                <h3><?php echo htmlspecialchars($assignment['title']); ?></h3>
                                <p><?php echo htmlspecialchars($assignment['description']); ?></p>
                                <p>Assign Date: <?php echo htmlspecialchars($assignment['created_at']); ?></p>
                                <p>File: <a href="<?php echo htmlspecialchars($assignment['file']); ?>" class="link" target="_blank">Download</a></p>
                                <p>Due Date: <?php echo htmlspecialchars($assignment['due_date']); ?></p>
                                <div class="delup-btn">
                                <button class="edit" title="Edit" onclick="openEditAssignmentModal(<?php echo $assignment['assignment_id']; ?>, '<?php echo addslashes($assignment['title']); ?>', '<?php echo addslashes($assignment['description']); ?>', '<?php echo $assignment['due_date']; ?>')">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                    <form method="POST" onsubmit="return confirm('Are you sure you want to delete this assignment?');">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token()); ?>">
                                    <input type="hidden" name="delete_id" value="<?php echo $assignment['assignment_id']; ?>">
                                    <button type="submit" class="delete" name="delete">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </section>

                <!-- Lectures Section -->
                <section>
                    <h1>Lectures <hr></h1>
                    <?php if (!empty($lectures)): ?>
                        <?php foreach ($lectures as $lecture): ?>
                            <div class="lecture">
                                <h3><?php echo htmlspecialchars($lecture['title']); ?></h3>
                                <p><?php echo htmlspecialchars($lecture['description']); ?></p>
                                <p>Assign Date: <?php echo htmlspecialchars($lecture['created_at']); ?></p>
                                <p>File: <a href="<?php echo htmlspecialchars($lecture['file']); ?>" class="link" target="_blank">Download</a></p>
                                <div class="delup-btn">
                                <button class="edit" title="Edit" onclick="openEditLectureModal(<?php echo $lecture['lecture_id']; ?>, '<?php echo addslashes($lecture['title']); ?>', '<?php echo addslashes($lecture['description']); ?>')">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                    <form method="POST" onsubmit="return confirm('Are you sure you want to delete this lecture?');">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token()); ?>">
                                    <input type="hidden" name="delete_id" value="<?php echo $lecture['lecture_id']; ?>">
                                    <button type="submit" class="delete" name="lecture">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </section>
            </div>

            <!-- Add Assignment Modal -->
            <div id="assignmentModal" class="modal">
                <div class="modal-content">
                    <span class="close" onclick="closeModal('assignmentModal')">&times;</span>
                    <h2>Add Assignment</h2>
                    <form method="POST" action="" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token()); ?>">
                        <input type="text" name="title" placeholder="Title" required>
                        <textarea name="description" placeholder="Description" rows="5" required></textarea>
                        <input type="file" name="file" required>
                        <input type="date" name="due_date" required>
                        <button type="submit" name="add_assignment">Add Assignment</button>
                    </form>
                </div>
            </div>
            
            <!-- Add Lecture Modal -->
            <div id="lectureModal" class="modal">
                <div class="modal-content">
                    <span class="close" onclick="closeModal('lectureModal')">&times;</span>
                    <h2>Add Lecture</h2>
                    <form method="POST" action="" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token()); ?>">
                        <input type="text" name="title" placeholder="Title" required>
                        <textarea name="description" placeholder="Description" rows="5" required></textarea>
                        <input type="file" name="lfile" required>
                        <button type="submit" name="add_lecture">Add Lecture</button>
                    </form>
                </div>
            </div>

            <!-- Edit Assignment  -->
            <div id="editAssignmentModal" class="editModal">
                <div class="edit-content">
                    <span class="close" onclick="closeModal('editAssignmentModal')">&times;</span>
                    <h2>Edit Assignment</h2>
                    <form id="editAssignmentForm" method="POST" action="" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token()); ?>">
                        <input type="hidden" id="editAssignmentId" name="id">
                        <label for="editAssignmentTitle">Title:</label>
                        <input type="text" id="editAssignmentTitle" name="title" required>

                        <label for="editAssignmentDescription">Description:</label>
                        <textarea id="editAssignmentDescription" name="description" rows="5" required></textarea>

                        <label for="editAssignmentFile">File:</label>
                        <input type="file" id="editAssignmentFile" name="file">

                        <label for="editAssignmentDueDate">Due Date:</label>
                        <input type="date" id="editAssignmentDueDate" name="due_date" required>

                        <button type="submit" name="updAss">Update Assignment</button>
                    </form>
                </div>
            </div>

            <!-- Edit Lecture Modal -->
            <div id="editLectureModal" class="modal">
                <div class="modal-content">
                    <span class="close" onclick="closeModal('editLectureModal')">&times;</span>
                    <h2>Edit Lecture</h2>
                    <form id="editLectureForm" method="POST" action="" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token()); ?>">
                        <input type="hidden" id="editLectureId" name="id">
                        <label for="editLectureTitle">Title:</label>
                        <input type="text" id="editLectureTitle" name="title" required>

                        <label for="editLectureDescription">Description:</label>
                        <textarea id="editLectureDescription" name="description" rows="5" required></textarea>

                        <label for="editLectureFile">File:</label>
                        <input type="file" id="editLectureFile" name="lfile">

                        <button type="submit" name="upLec">Update Lecture</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
<script>
function openModal(modalId) {
    document.getElementById(modalId).style.display = 'flex';
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

function openEditAssignmentModal(id, title, description, dueDate) {

    document.getElementById('editAssignmentId').value = id;
    document.getElementById('editAssignmentTitle').value = title;
    document.getElementById('editAssignmentDescription').value = description;
    document.getElementById('editAssignmentDueDate').value = dueDate;

    openModal('editAssignmentModal');
}

function openEditLectureModal(id, title, description) {

    document.getElementById('editLectureId').value = id;
    document.getElementById('editLectureTitle').value = title;
    document.getElementById('editLectureDescription').value = description;

    openModal('editLectureModal');
}

window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.style.display = 'none';
    }
}
</script>
</body>
</html>
