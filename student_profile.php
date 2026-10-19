
<?php
require_once "config.php";

// Check whether the student is logged in
if (!isset($_SESSION['student_id'])) {
    redirect('student_login.php');
    exit;
}

// Get the logged-in student's information
$stmt = $pdo->prepare(
    "SELECT id, student_id, first_name, last_name, email,
            phone, department, year_of_study, gender,
            created_at, profile_photo
     FROM students
     WHERE id = ?
     LIMIT 1"
);

$stmt->execute([$_SESSION['student_id']]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    session_unset();
    session_destroy();
    header("Location: student_login.php");
    exit;
}

$fullName = trim($student['first_name'] . ' ' . $student['last_name']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>My Student Profile</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 30px 15px;
            font-family: Arial, sans-serif;
            background: #f1f5f9;
            color: #1e293b;
        }

        .container {
            max-width: 750px;
            margin: auto;
            padding: 30px;
            background: white;
            border-radius: 14px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        }

        h1 {
            text-align: center;
            color: #1e3a8a;
            margin-bottom: 25px;
        }

        .profile-photo {
            width: 140px;
            height: 140px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #2563eb;
            display: block;
            margin: 0 auto 12px;
        }

        .no-photo {
            width: 140px;
            height: 140px;
            border-radius: 50%;
            background: #dbeafe;
            border: 4px solid #2563eb;
            margin: 0 auto 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #1e3a8a;
            font-weight: bold;
        }

        .photo-section {
            text-align: center;
            margin-bottom: 25px;
        }

        .photo-link {
            display: inline-block;
            margin-top: 8px;
            color: #2563eb;
            text-decoration: none;
            font-weight: bold;
        }

        .photo-link:hover {
            text-decoration: underline;
        }

        .student-name {
            text-align: center;
            font-size: 22px;
            font-weight: bold;
            margin: 10px 0 25px;
        }

        .details {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            overflow: hidden;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            gap: 15px;
            padding: 14px;
            border-bottom: 1px solid #e2e8f0;
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-label {
            font-weight: bold;
            color: #475569;
        }

        .detail-value {
            text-align: right;
            overflow-wrap: anywhere;
        }

        .actions {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 12px;
            margin-top: 25px;
        }

        .btn {
            display: inline-block;
            padding: 11px 17px;
            border-radius: 6px;
            background: #2563eb;
            color: white;
            text-decoration: none;
        }

        .btn:hover {
            background: #1d4ed8;
        }

        .btn-secondary {
            background: #475569;
        }

        .btn-secondary:hover {
            background: #334155;
        }

        @media (max-width: 480px) {
            .container {
                padding: 20px 15px;
            }

            .detail-row {
                flex-direction: column;
                gap: 6px;
            }

            .detail-value {
                text-align: left;
            }
        }
    </style>
</head>

<body>
<div class="container">

    <h1>My Student Profile</h1>

    <div class="photo-section">

        <?php if (!empty($student['profile_photo'])): ?>

            <img
                src="<?= e($student['profile_photo']) ?>"
                alt="Student Profile Photo"
                class="profile-photo"
            >

        <?php else: ?>

            <div class="no-photo">
                No Photo
            </div>

        <?php endif; ?>

        <a class="photo-link" href="upload_profile_photo.php">
            Upload / Change Profile Photo
        </a>

    </div>

    <div class="student-name">
        <?= e($fullName) ?>
    </div>

    <div class="details">

        <div class="detail-row">
            <span class="detail-label">Student ID</span>
            <span class="detail-value">
                <?= e($student['student_id']) ?>
            </span>
        </div>

        <div class="detail-row">
            <span class="detail-label">First Name</span>
            <span class="detail-value">
                <?= e($student['first_name']) ?>
            </span>
        </div>

        <div class="detail-row">
            <span class="detail-label">Last Name</span>
            <span class="detail-value">
                <?= e($student['last_name']) ?>
            </span>
        </div>

        <div class="detail-row">
            <span class="detail-label">Email</span>
            <span class="detail-value">
                <?= e($student['email']) ?>
            </span>
        </div>

        <div class="detail-row">
            <span class="detail-label">Phone</span>
            <span class="detail-value">
                <?= e($student['phone'] ?? '') ?>
            </span>
        </div>

        <div class="detail-row">
            <span class="detail-label">Department</span>
            <span class="detail-value">
                <?= e($student['department']) ?>
            </span>
        </div>

        <div class="detail-row">
            <span class="detail-label">Year of Study</span>
            <span class="detail-value">
                <?= e($student['year_of_study']) ?>
            </span>
        </div>

        <div class="detail-row">
            <span class="detail-label">Gender</span>
            <span class="detail-value">
                <?= e($student['gender']) ?>
            </span>
        </div>

        <div class="detail-row">
            <span class="detail-label">Registration Date</span>
            <span class="detail-value">
                <?= e($student['created_at'] ?? '') ?>
            </span>
        </div>

    </div>

    <div class="actions">
        <a class="btn" href="edit_student_profile.php">
            Edit Profile
        </a>

        <a class="btn" href="change_password.php">
            Change Password
        </a>

        <a class="btn btn-secondary" href="student_dashboard.php">
            Back to Dashboard
        </a>
    </div>

</div>
</body>
</html>
