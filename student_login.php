<?php
require_once "config.php";

if (isset($_SESSION['student_id'])) {
    redirect('student_dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        http_response_code(403);
        exit('Invalid security token. Please reload the page and try again.');
    }
    $identity = trim($_POST['identity'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($identity === '' || $password === '') {
        $error = 'Please enter your Student ID/email and password.';
    } else {
        $stmt = $pdo->prepare(
            "SELECT id, student_id, first_name, last_name,
                    email, password_hash
             FROM students
             WHERE student_id = ? OR email = ?
             LIMIT 1"
        );

        $stmt->execute([$identity, $identity]);
        $student = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($student &&
            !empty($student['password_hash']) &&
            password_verify($password, $student['password_hash'])) {

            session_regenerate_id(true);

            $_SESSION['student_id'] = $student['id'];
            $_SESSION['student_number'] = $student['student_id'];
            $_SESSION['student_name'] =
                $student['first_name'] . ' ' . $student['last_name'];

            redirect('student_dashboard.php');
        } else {
            $error = 'Invalid Student ID/email or password.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #eef2f7;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }

        .login-box {
            background: white;
            width: 350px;
            max-width: 90%;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 5px 20px #00000018;
        }

        h2 { text-align: center; color: #173b70; }

        label {
            display: block;
            margin-top: 15px;
            font-weight: bold;
        }

        input {
            width: 100%;
            box-sizing: border-box;
            padding: 12px;
            margin-top: 7px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }

        button {
            width: 100%;
            padding: 12px;
            margin-top: 22px;
            background: #173b70;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }

        .error {
            background: #ffe8e8;
            color: #a40000;
            padding: 10px;
            border-radius: 6px;
        }

        .links { text-align: center; margin-top: 18px; }
        a { color: #173b70; }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>Student Login</h2>
        <p>Login to your student account.</p>

        <?php if ($error !== ''): ?>
            <p class="error">
                <?= e($error) ?>
            </p>
        <?php endif; ?>

        <form method="POST" action="">
            <input type="hidden" name="csrf_token"
                   value="<?= e(csrf_token()) ?>">

            <label for="identity">Student ID or Email</label>
            <input type="text" id="identity" name="identity"
                   required autocomplete="username">

            <label for="password">Password</label>
            <input type="password" id="password" name="password"
                   required autocomplete="current-password">

            <button type="submit">Login</button>
        </form>

        <div class="links">
            <p>Don't have an account?
                <a href="register.php">Register here</a>
            </p>
            <a href="index.php">Back to Home</a>
        </div>
    </div>
</body>
</html>
