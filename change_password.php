
<?php
require_once "config.php";

// Student login godhe qofa seenuu danda'a.
if (!isset($_SESSION['student_id'])) {
    redirect('student_login.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        http_response_code(403);
        exit('Invalid security token. Please reload the page and try again.');
    }

    // Keep your existing password-change code below.

    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (
        $current_password === '' ||
        $new_password === '' ||
        $confirm_password === ''
    ) {
        $error = 'Please fill in all password fields.';
    } elseif (strlen($new_password) < 8) {
        $error = 'New password must be at least 8 characters.';
    } elseif ($new_password !== $confirm_password) {
        $error = 'New password and confirmation do not match.';
    } elseif ($current_password === $new_password) {
        $error = 'Your new password must be different from the current password.';
    } else {
        // Password hash student kanaa qofa barbaadi.
        $stmt = $pdo->prepare(
            "SELECT password_hash
             FROM students
             WHERE id = ?
             LIMIT 1"
        );
        $stmt->execute([$_SESSION['student_id']]);
        $student = $stmt->fetch(PDO::FETCH_ASSOC);

        if (
            !$student ||
            empty($student['password_hash']) ||
            !password_verify(
                $current_password,
                $student['password_hash']
            )
        ) {
            $error = 'Current password is incorrect.';
        } else {
            $new_hash = password_hash(
                $new_password,
                PASSWORD_DEFAULT
            );

            $update = $pdo->prepare(
                "UPDATE students
                 SET password_hash = ?
                 WHERE id = ?"
            );

            $update->execute([
                $new_hash,
                $_SESSION['student_id']
            ]);

            $success = 'Password changed successfully. Please remember your new password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f1f5f9;
            margin: 0;
            padding: 30px 15px;
            color: #1e293b;
        }

        .container {
            max-width: 500px;
            margin: auto;
            padding: 30px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px #00000012;
        }

        h2 {
            text-align: center;
            color: #1e3a8a;
            margin-bottom: 25px;
        }

        label {
            display: block;
            font-weight: bold;
            margin-top: 17px;
            margin-bottom: 7px;
        }

        input {
            display: block;
            width: 100%;
            padding: 12px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            font-size: 15px;
        }

        input:focus {
            outline: 2px solid #93c5fd;
            border-color: #2563eb;
        }

        button {
            width: 100%;
            padding: 13px;
            margin-top: 24px;
            background: #2563eb;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
        }

        button:hover {
            background: #1d4ed8;
        }

        .message {
            padding: 12px;
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
            text-align: center;
            margin-top: 22px;
            line-height: 2;
        }

        .links a {
            color: #2563eb;
            text-decoration: none;
            margin: 0 5px;
        }
    </style>
</head>

<body>
<div class="container">

    <h2>Change Password</h2>

    <?php if ($error !== ''): ?>
        <p class="message error"><?= e($error) ?></p>
    <?php endif; ?>

    <?php if ($success !== ''): ?>
        <p class="message success"><?= e($success) ?></p>
    <?php endif; ?>

    <form method="POST" action="">
        <input type="hidden" name="csrf_token"
               value="<?= e(csrf_token()) ?>">

        <label for="current_password">Current Password</label>
        <input type="password"
               id="current_password"
               name="current_password"
               autocomplete="current-password"
               required>

        <label for="new_password">New Password</label>
        <input type="password"
               id="new_password"
               name="new_password"
               minlength="8"
               autocomplete="new-password"
               required>

        <label for="confirm_password">Confirm New Password</label>
        <input type="password"
               id="confirm_password"
               name="confirm_password"
               minlength="8"
               autocomplete="new-password"
               required>

        <button type="submit">Change Password</button>
    </form>

    <div class="links">
        <a href="student_dashboard.php">Back to Dashboard</a> |
        <a href="student_profile.php">My Profile</a>
		<a href="change_password.php">Change Password</a>
    </div>

</div>
</body>
</html>
