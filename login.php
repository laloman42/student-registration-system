
<?php
require_once "config.php";

$error = "";

if (!empty($_SESSION['student_id'])) {
    redirect('student_dashboard.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $error = "Invalid request. Please refresh the page and try again.";
    } else {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($email === '' || $password === '') {
            $error = "Please enter your email and password.";
        } else {
            try {
                $stmt = $pdo->prepare(
                    "SELECT id, student_id, first_name, last_name,
                            email, password_hash
                     FROM students
                     WHERE email = ?
                     LIMIT 1"
                );

                $stmt->execute([$email]);
                $student = $stmt->fetch(PDO::FETCH_ASSOC);

                if (
                    $student &&
                    password_verify($password, $student['password_hash'])
                ) {
                    session_regenerate_id(true);

                    $_SESSION['student_id'] = (int) $student['id'];
                    $_SESSION['student_number'] = $student['student_id'];
                    $_SESSION['student_name'] =
                        $student['first_name'] . ' ' .
                        $student['last_name'];
                    $_SESSION['student_email'] = $student['email'];

                    redirect('student_dashboard.php');
                } else {
                    $error = "Incorrect email or password.";
                }
            } catch (PDOException $e) {
                $error = "Unable to sign in right now. Please try again.";
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
    <title>Student Login</title>
    <link rel="stylesheet" href="style.css">

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f1f5f9;
            margin: 0;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            box-sizing: border-box;
        }

        .login-box {
            background: white;
            width: 100%;
            max-width: 420px;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 5px 22px rgba(0, 0, 0, 0.10);
            box-sizing: border-box;
        }

        .login-box h1 {
            text-align: center;
            color: #1e3a8a;
            margin-top: 0;
        }

        .login-box p {
            text-align: center;
            color: #64748b;
        }

        .login-box label {
            display: block;
            margin-top: 16px;
            margin-bottom: 7px;
            font-weight: bold;
        }

        .login-box input {
            width: 100%;
            padding: 12px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            box-sizing: border-box;
        }

        .login-box button {
            width: 100%;
            padding: 12px;
            margin-top: 22px;
            background: #1d4ed8;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
        }

        .login-box button:hover {
            background: #1e40af;
        }

        .error {
            padding: 12px;
            color: #991b1b;
            background: #fee2e2;
            border-radius: 6px;
            margin-top: 15px;
        }

        .links {
            margin-top: 20px;
            text-align: center;
            line-height: 2;
        }

        .links a {
            color: #1d4ed8;
            text-decoration: none;
        }

        .links a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <div class="login-box">
        <h1>Student Login</h1>
        <p>Sign in to your student account.</p>

        <?php if ($error !== ''): ?>
            <div class="error">
                <?= e($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="student_login.php">
            <input
                type="hidden"
                name="csrf_token"
                value="<?= e(csrf_token()) ?>"
            >

            <label for="email">Email Address</label>
            <input
                type="email"
                id="email"
                name="email"
                placeholder="Enter your email"
                value="<?= e($_POST['email'] ?? '') ?>"
                required
                autocomplete="email"
            >

            <label for="password">Password</label>
            <input
                type="password"
                id="password"
                name="password"
                placeholder="Enter your password"
                required
                autocomplete="current-password"
            >

            <button type="submit">Login</button>
        </form>

        <div class="links">
            <div>Don't have an account?
                <a href="register.php">Register here</a>
            </div>
            <div>
                <a href="index.php">Back to Home</a>
            </div>
        </div>
    </div>
</body>
</html>
