
<?php
require_once "config.php";

if (empty($_SESSION['student_id'])) {
    redirect('student_login.php');
}

$error = '';
$success = '';

$stmt = $pdo->prepare(
    "SELECT id, student_id, first_name, last_name, email, phone,
            department, year_of_study, gender
     FROM students
     WHERE id = ?
     LIMIT 1"
);

$stmt->execute([$_SESSION['student_id']]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    unset(
        $_SESSION['student_id'],
        $_SESSION['student_number'],
        $_SESSION['student_name'],
        $_SESSION['student_email']
    );

    redirect('student_login.php');
}

// Values shown in the form
$first_name = $student['first_name'];
$last_name = $student['last_name'];
$email = $student['email'];
$phone = $student['phone'] ?? '';
$department = $student['department'];
$year = (string) $student['year_of_study'];
$gender = $student['gender'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify the CSRF token
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        http_response_code(403);
        exit('Invalid security token. Please reload the page and try again.');
    }

    // Keep the existing profile-update code below.else {
        // Read submitted values
        $first_name = trim($_POST['first_name'] ?? '');
        $last_name = trim($_POST['last_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $department = trim($_POST['department'] ?? '');
        $yearInput = trim($_POST['year_of_study'] ?? '');
        $gender = $_POST['gender'] ?? '';

        $yearValue = filter_var(
            $yearInput,
            FILTER_VALIDATE_INT
        );

        if (
            $first_name === '' ||
            $last_name === '' ||
            $email === '' ||
            $department === ''
        ) {
            $error = 'Please fill in all required fields.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
        } elseif (
            strlen($first_name) > 80 ||
            strlen($last_name) > 80 ||
            strlen($email) > 150 ||
            strlen($phone) > 25 ||
            strlen($department) > 100
        ) {
            $error = 'One or more fields are too long.';
        } elseif (
            $yearValue === false ||
            $yearValue === null ||
            $yearValue < 1 ||
            $yearValue > 255
        ) {
            $error = 'Please enter a valid year of study.';
        } elseif (
            !in_array($gender, ['Male', 'Female', 'Other'], true)
        ) {
            $error = 'Please select a valid gender.';
        } else {
            try {
                // Check whether another student uses this email
                $check = $pdo->prepare(
                    "SELECT id
                     FROM students
                     WHERE email = ? AND id != ?
                     LIMIT 1"
                );

                $check->execute([$email, $student['id']]);

                if ($check->fetch()) {
                    $error = 'This email is already used by another student.';
                } else {
                    // Update only the currently logged-in student's record
                    $update = $pdo->prepare(
                        "UPDATE students
                         SET first_name = ?,
                             last_name = ?,
                             email = ?,
                             phone = ?,
                             department = ?,
                             year_of_study = ?,
                             gender = ?
                         WHERE id = ?"
                    );

                    $update->execute([
                        $first_name,
                        $last_name,
                        $email,
                        $phone !== '' ? $phone : null,
                        $department,
                        $yearValue,
                        $gender,
                        $student['id']
                    ]);

                    // Refresh session information
                    $_SESSION['student_name'] =
                        $first_name . ' ' . $last_name;
                    $_SESSION['student_email'] = $email;

                    // Redirect to prevent duplicate form submission
                    redirect('student_dashboard.php');
                }
            } catch (PDOException $e) {
                $error = 'Unable to update your profile. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Edit My Profile - Student Registration System</title>

    <link rel="stylesheet" href="style.css">

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f1f5f9;
            color: #1e293b;
            margin: 0;
            padding: 30px 15px;
        }

        .container {
            max-width: 650px;
            margin: 20px auto;
            padding: 30px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 18px rgba(0, 0, 0, 0.08);
        }

        h1 {
            text-align: center;
            color: #1e3a8a;
            margin-top: 0;
        }

        .subtitle {
            text-align: center;
            color: #64748b;
            margin-bottom: 25px;
        }

        label {
            display: block;
            margin-top: 16px;
            margin-bottom: 7px;
            font-weight: bold;
        }

        input,
        select {
            display: block;
            width: 100%;
            padding: 12px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            font-size: 15px;
            background: white;
        }

        input:focus,
        select:focus {
            outline: 2px solid #93c5fd;
            border-color: #2563eb;
        }

        .save-button {
            width: 100%;
            padding: 13px;
            margin-top: 24px;
            background: #2563eb;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
        }

        .save-button:hover {
            background: #1d4ed8;
        }

        .message {
            padding: 13px;
            border-radius: 6px;
            line-height: 1.5;
        }

        .error {
            background: #fee2e2;
            color: #991b1b;
        }

        .success {
            background: #dcfce7;
            color: #166534;
        }

        .links {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 18px;
            margin-top: 22px;
        }

        .links a {
            color: #1d4ed8;
            text-decoration: none;
        }

        .links a:hover {
            text-decoration: underline;
        }

        .required-note {
            font-size: 13px;
            color: #64748b;
        }

        @media (max-width: 480px) {
            .container {
                padding: 20px;
            }
        }
    </style>
</head>

<body>

<div class="container">

    <h1>Edit My Profile</h1>

    <p class="subtitle">
        Update your registered student information.
    </p>

    <p class="required-note">Fields marked * are required.</p>

    <?php if ($error !== ''): ?>
        <div class="message error">
            <?= e($error) ?>
        </div>
    <?php endif; ?>

    <?php if ($success !== ''): ?>
        <div class="message success">
            <?= e($success) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="edit_student_profile.php">

        <input
            type="hidden"
            name="csrf_token"
            value="<?= e(csrf_token()) ?>"
        >

        <label for="first_name">First Name *</label>
        <input
            type="text"
            id="first_name"
            name="first_name"
            maxlength="80"
            required
            value="<?= e($first_name) ?>"
        >

        <label for="last_name">Last Name *</label>
        <input
            type="text"
            id="last_name"
            name="last_name"
            maxlength="80"
            required
            value="<?= e($last_name) ?>"
        >

        <label for="email">Email Address *</label>
        <input
            type="email"
            id="email"
            name="email"
            maxlength="150"
            required
            value="<?= e($email) ?>"
        >

        <label for="phone">Phone Number</label>
        <input
            type="text"
            id="phone"
            name="phone"
            maxlength="25"
            value="<?= e($phone) ?>"
        >

        <label for="department">Department *</label>
        <input
            type="text"
            id="department"
            name="department"
            maxlength="100"
            required
            value="<?= e($department) ?>"
        >

        <label for="year_of_study">Year of Study *</label>
        <input
            type="number"
            id="year_of_study"
            name="year_of_study"
            min="1"
            max="255"
            required
            value="<?= e($year) ?>"
        >

        <label for="gender">Gender *</label>
        <select id="gender" name="gender" required>
            <option value="Male"
                <?= $gender === 'Male' ? 'selected' : '' ?>>
                Male
            </option>

            <option value="Female"
                <?= $gender === 'Female' ? 'selected' : '' ?>>
                Female
            </option>

            <option value="Other"
                <?= $gender === 'Other' ? 'selected' : '' ?>>
                Other
            </option>
        </select>

        <button type="submit" class="save-button">
            Save Changes
        </button>

    </form>

    <div class="links">
        <a href="student_profile.php">My Profile</a>
        <a href="student_dashboard.php">Dashboard</a>
    </div>

</div>

</body>
</html>
