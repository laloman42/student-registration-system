
<?php
require_once "config.php";

/* Check student login */
if (empty($_SESSION['student_id'])) {
    redirect('student_login.php');
}

/* Get the logged-in student's information */
$stmt = $pdo->prepare(
    "SELECT id, student_id, first_name, last_name, email,
            phone, gender, department, year_of_study
     FROM students
     WHERE id = ?
     LIMIT 1"
);

$stmt->execute([$_SESSION['student_id']]);
$student = $stmt->fetch();

if (!$student) {
    unset(
        $_SESSION['student_id'],
        $_SESSION['student_number'],
        $_SESSION['student_name'],
        $_SESSION['student_email']
    );

    redirect('student_login.php');
}

$studentName = trim(
    ($student['first_name'] ?? '') . ' ' .
    ($student['last_name'] ?? '')
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Student Dashboard</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f1f5f9;
            color: #1e293b;
        }

        .topbar {
            background: #1e3a8a;
            color: white;
            padding: 20px 6%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
        }

        .topbar h2 {
            margin: 0;
        }

        .topbar a {
            color: white;
            text-decoration: none;
            background: #dc2626;
            padding: 11px 18px;
            border-radius: 6px;
        }

        .topbar a:hover {
            background: #b91c1c;
        }

        .container {
            max-width: 1050px;
            margin: 35px auto;
            padding: 0 20px;
        }

        .welcome,
        .account {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 3px 12px rgba(0, 0, 0, .06);
            margin-bottom: 25px;
        }

        .welcome h1,
        .account h2 {
            color: #1e3a8a;
            margin-top: 0;
        }

        .student-id {
            display: inline-block;
            background: #dbeafe;
            color: #1d4ed8;
            padding: 9px 14px;
            border-radius: 20px;
            font-weight: bold;
        }

        .details {
            display: grid;
            grid-template-columns: repeat(
                auto-fit, minmax(220px, 1fr)
            );
            gap: 16px;
        }

        .detail {
            background: #f8fafc;
            padding: 17px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            overflow-wrap: anywhere;
        }

        .detail strong {
            display: block;
            color: #64748b;
            font-size: 13px;
            margin-bottom: 8px;
        }

        .detail span {
            font-weight: bold;
        }

        .actions {
            display: grid;
            grid-template-columns: repeat(
                auto-fit, minmax(200px, 1fr)
            );
            gap: 16px;
            margin-bottom: 30px;
        }

        .action-card {
            display: block;
            background: white;
            padding: 22px;
            border-radius: 10px;
            text-align: center;
            text-decoration: none;
            color: #1e293b;
            border: 1px solid #e2e8f0;
            box-shadow: 0 3px 12px rgba(0, 0, 0, .05);
            transition: transform .2s, box-shadow .2s;
        }

        .action-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 18px rgba(0, 0, 0, .1);
        }

        .action-card h3 {
            color: #1d4ed8;
            margin-top: 0;
        }

        .action-card p {
            color: #64748b;
            font-size: 14px;
            line-height: 1.5;
        }

        footer {
            text-align: center;
            padding: 25px;
            color: #64748b;
        }

        @media (max-width: 500px) {
            .topbar {
                padding: 18px;
            }

            .welcome,
            .account {
                padding: 18px;
            }
        }
    </style>
</head>

<body>

<header class="topbar">
    <h2>Student Registration System</h2>
    <a href="logout.php"
       onclick="return confirm('Are you sure you want to log out?');">
        Logout
    </a>
</header>

<main class="container">

    <section class="welcome">
        <h1>
            Welcome, <?= e($studentName !== '' ? $studentName : 'Student') ?>!
        </h1>

        <p>You have successfully logged in to your account.</p>

        <span class="student-id">
            Student ID: <?= e($student['student_id']) ?>
        </span>
    </section>

    <section class="account">
        <h2>My Account</h2>
        <p>Your registered student information is shown below.</p>

        <div class="details">

            <div class="detail">
                <strong>Full Name</strong>
                <span><?= e($studentName) ?></span>
            </div>

            <div class="detail">
                <strong>Student ID</strong>
                <span><?= e($student['student_id']) ?></span>
            </div>

            <div class="detail">
                <strong>Email Address</strong>
                <span><?= e($student['email']) ?></span>
            </div>

            <div class="detail">
                <strong>Phone Number</strong>
                <span><?= e($student['phone'] ?? '') ?: 'Not provided' ?></span>
            </div>

            <div class="detail">
                <strong>Gender</strong>
                <span><?= e($student['gender'] ?? '') ?: 'Not provided' ?></span>
            </div>

            <div class="detail">
                <strong>Department</strong>
                <span><?= e($student['department'] ?? '') ?: 'Not provided' ?></span>
            </div>

            <div class="detail">
                <strong>Year of Study</strong>
                <span><?= e($student['year_of_study'] ?? '') ?: 'Not provided' ?></span>
            </div>

        </div>
    </section>

    <section class="actions">

        <a class="action-card" href="student_profile.php">
            <h3>View My Profile</h3>
            <p>View your registered student information.</p>
        </a>

        <a class="action-card" href="edit_student_profile.php">
            <h3>Edit My Profile</h3>
            <p>Update your name, email and other details.</p>
        </a>

        <a class="action-card" href="change_password.php">
            <h3>Change Password</h3>
            <p>Change your account password securely.</p>
        </a>

    </section>

</main>

<footer>
    Student Registration System &copy; <?= date('Y') ?>
</footer>

</body>
</html>
