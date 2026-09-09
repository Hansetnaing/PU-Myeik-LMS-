<?php
require 'auth.php';
require_role('teacher');

require 'dbConnect.php';

if (!isset($_GET['class_id'])) {
    die("Class ID not provided.");
}
$class_id = filter_input(INPUT_GET, 'class_id', FILTER_VALIDATE_INT);
$teacher_id = (int) $_SESSION['t_id'];

if (!$class_id) {
    die("Invalid class ID.");
}

$select_class = mysqli_prepare($con, 'SELECT * FROM class WHERE class_id = ? AND teacher_id = ?');
mysqli_stmt_bind_param($select_class, 'ii', $class_id, $teacher_id);
mysqli_stmt_execute($select_class);
$class = mysqli_fetch_assoc(mysqli_stmt_get_result($select_class));

if (!$class) {
    die("You are not authorized to access this class or the class does not exist.");
}

$class_name = $class['class_name'];

// A student belongs to a class through student.class in the current schema.
// The class itself is already restricted by both class_id and teacher_id above.
$students_stmt = mysqli_prepare($con, 'SELECT student_id, name FROM student WHERE class = ? ORDER BY name');
mysqli_stmt_bind_param($students_stmt, 's', $class_name);
mysqli_stmt_execute($students_stmt);
$res = mysqli_stmt_get_result($students_stmt);

if (!$res) {
    die("Query failed: " . mysqli_error($con));
}

/*
 * Start with assignments and enrolled students, then LEFT JOIN submissions.
 * This preserves students who have not submitted and prevents submissions from
 * another assignment, class, or teacher from being counted.
 */
$work_stmt = mysqli_prepare($con, "SELECT
        a.assignment_id,
        a.title AS assignment_title,
        s.student_id,
        s.name AS student_name,
        sa.file AS submit_file,
        DATE(sa.submitted_at) AS submit_date
    FROM assignment a
    INNER JOIN class c ON c.class_id = a.class_id AND c.teacher_id = a.teacher_id
    LEFT JOIN student s ON s.class = c.class_name
    LEFT JOIN submit_assignment sa
        ON sa.assignment_id = a.assignment_id
        AND sa.student_id = s.student_id
    WHERE a.class_id = ? AND a.teacher_id = ?
    ORDER BY a.assignment_id DESC, s.name ASC");
mysqli_stmt_bind_param($work_stmt, 'ii', $class_id, $teacher_id);
mysqli_stmt_execute($work_stmt);
$resSub = mysqli_stmt_get_result($work_stmt);

if (!$resSub) {
    die("Query failed: " . mysqli_error($con));
}

$assignments = [];
while ($row = mysqli_fetch_assoc($resSub)) {
    $assignment_id = (int) $row['assignment_id'];
    if (!isset($assignments[$assignment_id])) {
        $assignments[$assignment_id] = [
            'title' => $row['assignment_title'],
            'students' => [],
            'submitted' => 0,
            'not_submitted' => 0,
        ];
    }

    if ($row['student_id'] !== null) {
        $row['is_submitted'] = !empty($row['submit_file']);
        if ($row['is_submitted']) {
            $assignments[$assignment_id]['submitted']++;
        } else {
            $assignments[$assignment_id]['not_submitted']++;
        }
        $assignments[$assignment_id]['students'][] = $row;
    }
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
                    <a href="class_details.php?class_id=<?php echo $class_id; ?>" class="btn back-to-class">
                        <i class="fa-sharp fa-solid fa-arrow-left"></i>
                        <span>Back to Class</span>
                    </a>
                </div>
                <section class="student-work-section">
                    <div class="work-page-heading">
                        <div>
                            <p class="eyebrow">TEACHER DASHBOARD</p>
                            <h1>Student Work Details</h1>
                            <p>Track assignment submissions for this class.</p>
                        </div>
                        <div class="assignment-total"><strong><?php echo count($assignments); ?></strong><span>Assignments</span></div>
                    </div>
                    <?php
                    if (!empty($assignments)) {
                        foreach ($assignments as $assignment) {
                            $totalStudents = count($assignment['students']);
                            echo "<article class='work-assignment-card'>";
                            echo "<div class='assignment-card-heading'>";
                            echo "<div><p class='assignment-label'>ASSIGNMENT</p><h2 class='asstitle'>" . htmlspecialchars($assignment['title']) . "</h2></div>";
                            echo "<span class='completion-rate'>" . $assignment['submitted'] . "/" . $totalStudents . " submitted</span>";
                            echo "</div>";
                            echo "<div class='submission-summary'>";
                            echo "<span class='total-count'><strong>" . $totalStudents . "</strong><small>Total Students</small></span>";
                            echo "<span class='submitted-count'><strong>" . $assignment['submitted'] . "</strong><small>Submitted</small></span>";
                            echo "<span class='not-submitted-count'><strong>" . $assignment['not_submitted'] . "</strong><small>Not Submitted</small></span>";
                            echo "</div>";
                            echo "<div class='work-table-wrap'><table>";
                            echo "<thead>
                                    <tr>
                                        <th>Student Name</th>
                                        <th>Status / Submitted File</th>
                                        <th>Submitted Date</th>
                                    </tr>
                                  </thead>";
                            echo "<tbody>";
                            foreach ($assignment['students'] as $submission) {
                                echo "<tr>";
                                echo "<td><span class='student-avatar'>" . htmlspecialchars(strtoupper(substr($submission['student_name'], 0, 1))) . "</span>" . htmlspecialchars($submission['student_name']) . "</td>";
                                if ($submission['is_submitted']) {
                                    echo "<td><span class='status submitted'><i class='fa-solid fa-circle-check'></i> Submitted</span> <a class='view-file' href='" . htmlspecialchars($submission['submit_file']) . "' target='_blank' rel='noopener'>View File <i class='fa-solid fa-arrow-up-right-from-square'></i></a></td>";
                                    echo "<td>" . htmlspecialchars($submission['submit_date']) . "</td>";
                                } else {
                                    echo "<td><span class='status not-submitted'><i class='fa-solid fa-clock'></i> Not Submitted</span></td>";
                                    echo "<td>-</td>";
                                }
                                echo "</tr>";
                            }
                            if ($totalStudents === 0) {
                                echo "<tr><td colspan='3'>No students are enrolled in this class.</td></tr>";
                            }
                            echo "</tbody>";
                            echo "</table></div>";
                            echo "</article>";
                        }
                    } else {
                        echo "<p>No assignments have been created for this class yet.</p>";
                    }
                    ?>
                </section>
            </div>
        </div>
    </div>
</body>
</html>
