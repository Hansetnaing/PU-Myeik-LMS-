<?php
require 'auth.php';
require_role('teacher');

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

$class_name = $class['class_name'];

$sql = "SELECT name FROM student WHERE class = '$class_name';";
$res = mysqli_query($con,$sql);

if (!$res) {
    die("Query failed: " . mysqli_error($con));
}

$sub = "SELECT 
    s.name AS student_name, 
    sa.file AS submit_file, 
    DATE(sa.submitted_at) AS submit_date,
    a.assignment_id as assignment_no,
    a.title as assignment
    FROM submit_assignment sa
    JOIN student s ON sa.student_id = s.student_id
    JOIN assignment a ON sa.assignment_id = a.assignment_id
    JOIN teacher t ON a.teacher_id = t.teacher_id
    WHERE t.teacher_id = $teacher_id AND a.class_id = $class_id;";

$resSub = mysqli_query($con,$sub);

$groupedSubmissions = [];
while ($row = mysqli_fetch_assoc($resSub)) {
    $assignmentTitle = $row['assignment'];
    $groupedSubmissions[$assignmentTitle][] = $row;  
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher | Check Student Work</title>
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
                    <a href="class_details.php?class_id=<?php echo $class_id; ?>" class="btn" style="text-decoration: none;">
                        <i class="fa-sharp fa-solid fa-arrow-left" style="margin-right: 10px; font-size: 1rem;"></i>Back
                    </a>
                </div>
                <section>
                    <h1>Student Work Details<hr></h1>
                    <?php
                    if (!empty($groupedSubmissions)) {
                        foreach ($groupedSubmissions as $assignmentTitle => $submissions) {
                            echo "<h2 class='asstitle'>" . htmlspecialchars($assignmentTitle) . "</h2>";
                            echo "<table>";
                            echo "<thead>
                                    <tr>
                                        <th>Student Name</th>
                                        <th>Submitted File</th>
                                        <th>Submitted Date</th>
                                    </tr>
                                  </thead>";
                            echo "<tbody>";
                            foreach ($submissions as $submission) {
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($submission['student_name']) . "</td>";
                                echo "<td><a href='" . htmlspecialchars($submission['submit_file']) . "' target='_blank'>View File</a></td>";
                                echo "<td>" . htmlspecialchars($submission['submit_date']) . "</td>";
                                echo "</tr>";
                            }
                            echo "</tbody>";
                            echo "</table>";
                        }
                    }
                    ?>
                </section>
            </div>
        </div>
    </div>
</body>
</html>
