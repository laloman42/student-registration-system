<?php
require_once "config.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($username === "HIDDEN" && $password === "HIDDEN") {
        session_regenerate_id(true);
        $_SESSION["admin_logged_in"] = true;

        header("Location: admin_dashboard.php");
        exit;
    } else {
        $error = "Username ykn password dogoggora.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | Student Registration System</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #f1f5f9;
            min-height: 100vh;
            color: #172554;
        }

        .header {
            background: linear-gradient(135deg, #142b50, #2456a4);
            color: white;
            text-align: center;
            padding: 35px 20px;
            border-bottom: 5px solid #60a5fa;
        }

        .header .cap {
            font-size: 42px;
            margin-bottom: 10px;
        }

        .header h1 {
            font-size: 27px;
            margin-bottom: 10px;
            letter-spacing: 1px;
        }

        .header p {
            color: #dbeafe;
            font-size: 15px;
        }

        .container {
            width: 92%;
            max-width: 480px;
            margin: 45px auto;
        }

        .login-card {
            background: white;
            padding: 35px;
            border-radius: 18px;
            box-shadow: 0 12px 35px rgba(15, 40, 80, 0.12);
            border: 1px solid #e2e8f0;
        }

        .admin-icon {
            width: 75px;
            height: 75px;
            margin: 0 auto 18px;
            border-radius: 50%;
            background: #dbeafe;
            color: #2563eb;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 35px;
        }

        h2 {
            text-align: center;
            font-size: 30px;
            margin-bottom: 10px;
        }

        .welcome {
            text-align: center;
            color: #64748b;
            line-height: 1.6;
            margin-bottom: 25px;
        }

        .error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #b91c1c;
            padding: 13px;
            border-radius: 9px;
            margin-bottom: 22px;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 21px;
        }

        label {
            display: block;
            font-weight: bold;
            margin-bottom: 9px;
            color: #1e293b;
        }

        input {
            width: 100%;
            padding: 14px 15px;
            border: 1px solid #cbd5e1;
            border-radius: 9px;
            font-size: 16px;
            outline: none;
            transition: 0.2s;
            background: #fff;
        }

        input:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.13);
        }

        .login-btn {
            width: 100%;
            padding: 15px;
            border: none;
            border-radius: 9px;
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: white;
            font-size: 17px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.2s;
            margin-top: 5px;
        }

        .login-btn:hover {
            background: #1e40af;
            transform: translateY(-1px);
        }

        .footer {
            text-align: center;
            border-top: 1px solid #e2e8f0;
            margin-top: 25px;
            padding-top: 20px;
            color: #64748b;
            font-size: 13px;
        }

        @media (max-width: 480px) {
            .header h1 {
                font-size: 21px;
            }

            .container {
                margin: 25px auto;
            }

            .login-card {
                padding: 25px 20px;
            }

            h2 {
                font-size: 26px;
            }
        }
    </style>
</head>

<body>

    <header class="header">
        <div class="cap">🎓</div>
        <h1>STUDENT REGISTRATION SYSTEM</h1>
        <p>Manage Students &bull; Build a Better Future</p>
    </header>

    <main class="container">
        <section class="login-card">

            <div class="admin-icon">👤</div>

            <h2>Admin Login</h2>
            <p class="welcome">
                Welcome back! Please sign in to your account.
            </p>

            <?php if ($error !== ""): ?>
                <div class="error" role="alert">
                    ⚠️ <?= htmlspecialchars($error, ENT_QUOTES, "UTF-8") ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="admin_login.php">

                <div class="form-group">
                    <label for="username">Username</label>
                    <input
                        type="text"
                        id="username"
                        name="username"
                        placeholder="Enter your username"
                        autocomplete="username"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Enter your password"
                        autocomplete="current-password"
                        required
                    >
                </div>

                <button type="submit" class="login-btn">
                    ⇥ &nbsp; Login
                </button>

            </form>

            <div class="footer">
                🔒 Authorized administrators only
            </div>

        </section>
    </main>

</body>
</html>
