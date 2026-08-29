<?php
session_start();
require 'dbConnect.php';

if (!isset($_SESSION['attempts'])) {
    $_SESSION['attempts'] = 0;
}

if (!isset($_SESSION['last_attempt'])) {
    $_SESSION['last_attempt'] = time();
}

$remaining_time = 0;
$lock_time = 0;

/* ===== Exponential Lock Policy ===== */
if ($_SESSION['attempts'] >= 9) {
    $lock_time = 300;
} elseif ($_SESSION['attempts'] >= 6) {
    $lock_time = 60;
} elseif ($_SESSION['attempts'] >= 3) {
    $lock_time = 15;
}

if ($lock_time > 0) {
    $elapsed = time() - $_SESSION['last_attempt'];

    if ($elapsed < $lock_time) {
        $remaining_time = $lock_time - $elapsed;
    } else {
        $_SESSION['attempts'] = 0;
    }
}

if (isset($_POST['submit'])) {

    // If account locked
    if ($remaining_time > 0) {
        // $error = "Too many failed attempts. Please wait {$remaining_time} seconds.";
    } else {

        $name = trim($_POST['name']);
        $pw   = trim($_POST['pw']);

        // Configure these values in the web-server environment.  A hash avoids
        // keeping an admin password in this source file.
        $adminName = getenv('LEARNHUB_ADMIN_USERNAME');
        $adminHash = getenv('LEARNHUB_ADMIN_PASSWORD_HASH');
        if ($adminName && $adminHash && hash_equals($adminName, $name) && password_verify($pw, $adminHash)) {
            session_regenerate_id(true);
            $_SESSION['role'] = 'admin';
            $_SESSION['attempts'] = 0;
            header("Location: admin.php");
            exit;
        }

        // ===== STUDENT LOGIN =====
        $stmt = $con->prepare('SELECT student_id, name, password FROM student WHERE name = ?');
        $stmt->bind_param('s', $name);
        $stmt->execute();
        $result = $stmt->get_result();

        if (mysqli_num_rows($result) == 1) {

            $rows = mysqli_fetch_assoc($result);

            if (password_verify($pw, $rows['password']) || hash_equals($rows['password'], $pw)) {

                session_regenerate_id(true);

                $_SESSION['s_id'] = $rows['student_id'];
                $_SESSION['name'] = $rows['name'];
                $_SESSION['role'] = 'student';
                $_SESSION['attempts'] = 0;

                if (!password_get_info($rows['password'])['algo']) {
                    $hash = password_hash($pw, PASSWORD_DEFAULT);
                    $migration = $con->prepare('UPDATE student SET password = ? WHERE student_id = ?');
                    $migration->bind_param('si', $hash, $rows['student_id']);
                    $migration->execute();
                }

                header("Location: checkstu.php");
                exit();
            }
        }

        // ===== TEACHER LOGIN =====
        $stmt2 = $con->prepare("SELECT teacher_id, name, password FROM teacher WHERE name=?");
        $stmt2->bind_param("s", $name);
        $stmt2->execute();
        $result2 = $stmt2->get_result();

        if ($result2->num_rows == 1) {

            $rows2 = $result2->fetch_assoc();

            if (password_verify($pw, $rows2['password']) || hash_equals($rows2['password'], $pw)) {

                session_regenerate_id(true);

                $_SESSION['t_id'] = $rows2['teacher_id'];
                $_SESSION['name'] = $rows2['name'];
                $_SESSION['role'] = 'teacher';
                $_SESSION['attempts'] = 0;

                if (!password_get_info($rows2['password'])['algo']) {
                    $hash = password_hash($pw, PASSWORD_DEFAULT);
                    $migration = $con->prepare('UPDATE teacher SET password = ? WHERE teacher_id = ?');
                    $migration->bind_param('si', $hash, $rows2['teacher_id']);
                    $migration->execute();
                }

                header("Location: check.php");
                exit;
            }
        }

        // ===== Login Failed =====
        $_SESSION['attempts'] += 1;
        $_SESSION['last_attempt'] = time();
        $error = "Username or Password is incorrect!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="./css/login.css">
    <link rel="icon" href="images/footer.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body>
    <!-- nav-start  -->
    <header>
        <div class="text-logo">
            <div class="hamburger" id="hamburger">
                <i class="fa-solid fa-bars"></i>
            </div>
            <h2>Learning<span>Hub</span></h2>
        </div>
        <!-- <div class="contact">
            <span><i class="fa-solid fa-phone-volume"></i> Call us: </span>(+95) 9-8762778
            <span><i class="fa-solid fa-envelope"></i> E-mail: </span><a href="mailto:learninghub@gmail.com">learninghub@gmail.com</a>
        </div> -->
        <div class="log-in">
            <p>You are not logged in.(<a href="login.php">Log in</a>)</p>
        </div>
    </header>
    <!-- nav-end  -->

    <!-- aside-bar-start  -->
    <aside class="sidebar" id="sidebar">
        <ul>
            <li><a href="index.html">Home</a></li>
        </ul>
    </aside>
    <!-- aside-bar-end  -->
    <section class="body-img">
        <div class="background-container"></div>
    <div class="login-main">
        <div class="login-box">
            <h2>Learning Hub<br>Login</h2>
            <form action="" method="post">
                <input type="text" placeholder="Username" name="name" required>
                <input type="password" placeholder="Password" name="pw" required>

                <?php if (isset($error)): ?>
                    <div class="error-message"><?php echo $error; ?></div>
                <?php endif; ?>

                <?php if ($remaining_time > 0): ?>
                    <div class="error-message" id="lockMessage">
                        Too many failed attempts. Please wait 
                        <span id="countdown"><?php echo $remaining_time; ?></span> seconds.
                    </div>
                <?php endif; ?>

                <button type="submit" name="submit" class="login-button"
                    <?php if ($remaining_time > 0) echo 'disabled'; ?>>
                    Log in
                </button>
            </form>
        </div>
    </div>
</section>
    <!-- footer-start  -->
    <section class="footer-start">
        <div class="footer-box">
            <div class="footer-logo">
                <img src="images/footer.png" alt="">
            </div>
            <p>Simplifying education, inspiring excellence.<br>
            Let’s create a better tomorrow, one step at a time.</p>
        </div>
        <div class="footer-box">
            <h1>Info</h1>
            <p id="university-website">University Website</p>
            <p id="contact-us">Contact Us</p>
        </div>
        </div>
        <div class="footer-box">
            <h1>Contact Us</h1>
            <p>Myeik-Tanintharyi Highway Road, Myeik Township,<br>
            Tanintharyi Region, Myanmar, Postal Code: 14051</p>
            <div class="icon">
                <span><i class="fa-solid fa-phone-volume"></i> Call us: </span>(+95) 9-8762778
            </div>
            <div class="icon">
                <span><i class="fa-solid fa-envelope"></i> E-mail: </span><a href="mailto:learninghub@gmail.com">learninghub@gmail.com</a>
            </div>
        </div>
    </section>
    <section>
        <footer>
            <p>Copyright &copy; 2025 - Developed by Group (IV).  All rights reserved.</p>
        </footer>
    </section>
    <!-- footer-end  -->

<?php if ($remaining_time > 0): ?>
<script>
document.addEventListener("DOMContentLoaded", function () {

    let timeLeft = <?php echo (int)$remaining_time; ?>;
    let countdownElement = document.getElementById("countdown");
    let loginButton = document.querySelector(".login-button");
    let lockMessage = document.getElementById("lockMessage");

    if (!countdownElement) return;

    countdownElement.innerText = timeLeft;

    let timer = setInterval(function () {

        timeLeft--;

        if (timeLeft > 0) {
            countdownElement.innerText = timeLeft;
        } else {
            clearInterval(timer);
            loginButton.disabled = false;
            lockMessage.innerHTML = 
                "You can try logging in again now.";
        }

    }, 1000);

});
</script>
<?php endif; ?>

</body>
<script src="./js/aside.js"></script>
<script src="./js/window.js"></script>
</html>
