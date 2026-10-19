
<?php
require_once 'config.php';

$errors = [];
$success = '';

$student_id = '';
$full_name = '';
$email = '';
$phone = '';
$gender = '';
$department = '';
$year = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify the security token first.
    if (!verify_csrf()) {
        http_response_code(403);
        exit('Invalid security token. Please reload the page and try again.');
    }

    // Read and clean form values.
    $student_id = trim($_POST['student_id'] ?? '');
   $first_name = trim($name_parts[0] ?? '');
   $last_name = trim($name_parts[1] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $gender = trim($_POST['gender'] ?? '');
    $department = trim($_POST['department'] ?? '');
    $year = trim($_POST['year'] ?? '');

    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Required field validation.
    if (
        $student_id === '' ||
        $full_name === '' ||
        $email === '' ||
        $gender === '' ||
        $department === '' ||
        $year === '' ||
        $password === '' ||
        $confirm_password === ''
    ) {
        $errors[] = 'Please fill in all required fields.';
    }

    // Match database column lengths.
    if (strlen($student_id) > 30) {
        $errors[] = 'Student ID must not exceed 30 characters.';
    }

    if (strlen($email) > 150) {
        $errors[] = 'Email must not exceed 150 characters.';
    }

    if (strlen($department) > 100) {
        $errors[] = 'Department must not exceed 100 characters.';
    }

    if (strlen($phone) > 25) {
        $errors[] = 'Phone number must not exceed 25 characters.';
    }

    // Validate email.
    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
	// Validate phone number.
if ($phone !== '' && !preg_match('/^[0-9+\s()-]+$/', $phone)) {
    $errors[] = 'Phone number can contain only digits, spaces, +, -, and parentheses.';
}

    // Validate password.
    if ($password !== '' && strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters long.';
    }

    if ($password !== $confirm_password) {
        $errors[] = 'Passwords do not match.';
    }

    // Validate gender.
    if (!in_array($gender, ['Male', 'Female', 'Other'], true)) {
        $errors[] = 'Please select a valid gender.';
    }

    // Validate year of study.
    if (!in_array($year, ['1', '2', '3', '4', '5', '6'], true)) {
        $errors[] = 'Please select a valid year of study.';
    }

    // Split full name into first and last names.
    $name_parts = preg_split('/\s+/', $full_name, 2);
    $first_name = trim($name_parts[0] ?? '');
    $last_name = trim($name_parts[1] ?? '');

    if (
        $first_name === '' ||
        $last_name === '' ||
        strlen($first_name) > 80 ||
        strlen($last_name) > 80
    ) {
        $errors[] = 'Enter a valid first name and last name (80 characters maximum each).';
    }

    if (empty($errors)) {
        try {
            // Check existing Student ID or email.
            $check = $pdo->prepare(
                'SELECT id
                 FROM students
                 WHERE student_id = :student_id
                    OR email = :email
                 LIMIT 1'
            );

            $check->execute([
                ':student_id' => $student_id,
                ':email' => $email
            ]);

            if ($check->fetch()) {
                $errors[] = 'Student ID or email already exists. Please use a different one.';
            } else {
                // Hash the password before saving.
                $password_hash = password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );

                $sql = 'INSERT INTO students (
                    student_id,
                    first_name,
                    last_name,
                    email,
                    phone,
                    gender,
                    department,
                    year_of_study,
                    password_hash
                ) VALUES (
                    :student_id,
                    :first_name,
                    :last_name,
                    :email,
                    :phone,
                    :gender,
                    :department,
                    :year_of_study,
                    :password_hash
                )';

                $stmt = $pdo->prepare($sql);

                $stmt->execute([
                    ':student_id' => $student_id,
                    ':first_name' => $first_name,
                    ':last_name' => $last_name,
                    ':email' => $email,
                    ':phone' => $phone !== '' ? $phone : null,
                    ':gender' => $gender,
                    ':department' => $department,
                    ':year_of_study' => (int)$year,
                    ':password_hash' => $password_hash
                ]);

                $success = 'Registration successful! You can now log in.';

                // Clear personal fields after successful registration.
                $student_id = '';
                $full_name = '';
                $email = '';
                $phone = '';
                $gender = '';
                $department = '';
                $year = '';

                // Passwords are never repopulated.
                $password = '';
                $confirm_password = '';
            }
        } catch (PDOException $e) {
            // Log technical details privately.
            error_log($e->getMessage());

            // Unique indexes also protect against duplicate submissions.
            if ($e->getCode() === '23000') {
                $errors[] = 'Student ID or email already exists. Please check your details.';
            } else {
                $errors[] = 'Registration failed. Please try again.';
            }
        }
    }
}

function h($value): string {
    return htmlspecialchars(
        (string)$value,
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    );
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Registration | Student Portal</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            padding: 35px 15px;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            font-family: Arial, Helvetica, sans-serif;
            color: #172b4d;
            background: linear-gradient(135deg, #edf3ff, #f8faff 55%, #eaf0ff);
        }

        .registration-container {
            width: 100%;
            max-width: 880px;
            margin: 10px auto;
            padding: 38px;
            background: #fff;
            border: 1px solid #e2e9f5;
            border-radius: 20px;
            box-shadow: 0 18px 50px rgba(26, 48, 87, .10);
        }

        .brand {
            text-align: center;
            margin-bottom: 25px;
        }

        .brand-icon {
            width: 60px;
            height: 60px;
            margin: 0 auto 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 17px;
            background: #e9efff;
            color: #2457d6;
            font-size: 27px;
            font-weight: 800;
        }

        h1 {
            margin: 0 0 10px;
            color: #183b75;
            font-size: clamp(26px, 4vw, 34px);
            text-align: center;
        }

        .subtitle {
            margin: 0;
            color: #718096;
            line-height: 1.7;
            text-align: center;
        }

        .section-heading {
            margin: 30px 0 20px;
            padding-bottom: 12px;
            border-bottom: 1px solid #e8edf5;
            color: #28466f;
            font-size: 16px;
            font-weight: 700;
        }

        .registration-form {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 21px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 9px;
            min-width: 0;
        }

        .full-width {
            grid-column: 1 / -1;
        }

        .form-group > label {
            color: #334765;
            font-size: 14px;
            font-weight: 700;
        }

        .form-group input:not([type="checkbox"]),
        .form-group select {
            width: 100%;
            min-height: 48px;
            padding: 12px 14px;
            border: 1px solid #d5ddea;
            border-radius: 9px;
            outline: none;
            background: #fbfcff;
            color: #243651;
            font: inherit;
            font-size: 15px;
            transition: border-color .2s, box-shadow .2s;
        }

        .form-group input:focus,
        .form-group select:focus {
            border-color: #2864dc;
            box-shadow: 0 0 0 3px rgba(40, 100, 220, .12);
            background: #fff;
        }

        .form-group input::placeholder {
            color: #9aa6b7;
        }

        .password-toggle {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #64748b;
            font-size: 13px;
            cursor: pointer;
        }

        .password-toggle input {
            width: 16px;
            height: 16px;
            accent-color: #2457d6;
        }

        .message {
            padding: 15px 18px;
            margin: 0 0 22px;
            border-radius: 10px;
            font-size: 14px;
            line-height: 1.6;
        }

        .error-message {
            color: #842029;
            background: #fff0f1;
            border: 1px solid #f3c4c8;
        }

        .success-message {
            color: #12653e;
            background: #eaf8f0;
            border: 1px solid #b8e4ca;
        }

        .error-message ul {
            margin: 0;
            padding-left: 20px;
        }

        .message a {
            color: inherit;
            font-weight: 700;
        }

        .submit-button {
            width: 100%;
            min-height: 51px;
            padding: 14px 20px;
            border: none;
            border-radius: 10px;
            background: #2457d6;
            color: white;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 5px 12px rgba(36, 87, 214, .18);
            transition: background .2s, transform .2s;
        }

        .submit-button:hover {
            background: #1944b2;
            transform: translateY(-1px);
        }

        .login-link {
            margin: 25px 0 0;
            text-align: center;
            color: #718096;
            font-size: 14px;
        }

        .login-link a {
            color: #2457d6;
            font-weight: 700;
            text-decoration: none;
        }

        .login-link a:hover {
            text-decoration: underline;
        }

        .required-note {
            margin: 0 0 20px;
            color: #718096;
            font-size: 12px;
        }

        @media (max-width: 600px) {
            body {
                padding: 15px 10px;
            }

            .registration-container {
                padding: 25px 18px;
                border-radius: 14px;
            }

            .registration-form {
                grid-template-columns: 1fr;
                gap: 18px;
            }

            .full-width {
                grid-column: auto;
            }
        }
    </style>
</head>

<body>
<div class="registration-container">

    <div class="brand">
        <div class="brand-icon" aria-hidden="true">S</div>
        <h1>Student Registration</h1>
        <p class="subtitle">
            Create your account to join the Student Portal.
        </p>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="message error-message" role="alert">
            <strong>Please check the following:</strong>
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= h($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if ($success !== ''): ?>
        <div class="message success-message" role="status">
            <?= h($success) ?>
            <a href="login.php">Go to login</a>
        </div>
    <?php endif; ?>

    <p class="required-note">Fields marked with * are required.</p>

    <h2 class="section-heading">Personal Information</h2>

    <form method="POST" action="register.php" class="registration-form">
        <input
            type="hidden"
            name="csrf_token"
            value="<?= h(csrf_token()) ?>"
        >

        <div class="form-group">
            <label for="student_id">Student ID *</label>
            <input
                type="text"
                id="student_id"
                name="student_id"
                value="<?= h($student_id) ?>"
                placeholder="Enter your student ID"
                maxlength="30"
                autocomplete="off"
                required>
        </div>

        <div class="form-group">
            <label for="full_name">Full Name *</label>
            <input
                type="text"
                id="full_name"
                name="full_name"
                value="<?= h($full_name) ?>"
                placeholder="First name and last name"
                maxlength="161"
                autocomplete="name"
                required>
        </div>

        <div class="form-group">
            <label for="email">Email Address *</label>
            <input
                type="email"
                id="email"
                name="email"
                value="<?= h($email) ?>"
                placeholder="example@email.com"
                maxlength="150"
                autocomplete="email"
                required>
        </div>

        <div class="form-group">
            <label for="phone">Phone Number</label>
            <input
                type="tel"
                id="phone"
                name="phone"
                value="<?= h($phone) ?>"
                placeholder="Enter phone number"
                maxlength="25"
                autocomplete="tel">
        </div>

        <div class="form-group">
            <label for="gender">Gender *</label>
            <select id="gender" name="gender" required>
                <option value="">-- Select Gender --</option>
                <option value="Male" <?= $gender === 'Male' ? 'selected' : '' ?>>Male</option>
                <option value="Female" <?= $gender === 'Female' ? 'selected' : '' ?>>Female</option>
                <option value="Other" <?= $gender === 'Other' ? 'selected' : '' ?>>Other</option>
            </select>
        </div>

        <div class="form-group">
            <label for="department">Department *</label>
            <input
                type="text"
                id="department"
                name="department"
                value="<?= h($department) ?>"
                placeholder="e.g. Computer Science"
                maxlength="100"
                required>
        </div>

        <div class="form-group">
            <label for="year">Year of Study *</label>
            <select id="year" name="year" required>
                <option value="">-- Select Year --</option>
                <?php for ($i = 1; $i <= 6; $i++): ?>
                    <option
                        value="<?= $i ?>"
                        <?= (string)$year === (string)$i ? 'selected' : '' ?>>
                        Year <?= $i ?>
                    </option>
                <?php endfor; ?>
            </select>
        </div>

        <div class="form-group full-width">
            <h2 class="section-heading">Account Security</h2>
        </div>

        <div class="form-group">
            <label for="password">Password *</label>
            <input
                type="password"
                id="password"
                name="password"
                placeholder="At least 8 characters"
                minlength="8"
                autocomplete="new-password"
                required>

            <label class="password-toggle">
                <input
                    type="checkbox"
                    onchange="togglePassword('password', this)">
                Show Password
            </label>
        </div>

        <div class="form-group">
            <label for="confirm_password">Confirm Password *</label>
            <input
                type="password"
                id="confirm_password"
                name="confirm_password"
                placeholder="Enter password again"
                minlength="8"
                autocomplete="new-password"
                required>

            <label class="password-toggle">
                <input
                    type="checkbox"
                    onchange="togglePassword('confirm_password', this)">
                Show Confirm Password
            </label>
        </div>

        <div class="form-group full-width">
            <button type="submit" class="submit-button">
                Create Student Account
            </button>
        </div>
    </form>

    <p class="login-link">
        Already have an account?
        <a href="login.php">Go to Login</a>
    </p>

</div>

<script>
function togglePassword(fieldId, checkbox) {
    const field = document.getElementById(fieldId);

    if (field) {
        field.type = checkbox.checked ? 'text' : 'password';
    }
}
</script>
</body>
</html>
