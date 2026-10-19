
<?php
require_once "config.php";
require_admin();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    http_response_code(400);
    exit('Invalid student ID.');
}

$stmt = $pdo->prepare('SELECT * FROM students WHERE id = ?');
$stmt->execute([$id]);
$student = $stmt->fetch();

if (!$student) {
    http_response_code(404);
    exit('Student not found.');
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $student_id = trim($_POST['student_id'] ?? '');
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $department = trim($_POST['department'] ?? '');
    $year = filter_var(
        $_POST['year_of_study'] ?? null,
        FILTER_VALIDATE_INT
    );

    $student = array_merge(
        $student,
        compact(
            'student_id',
            'first_name',
            'last_name',
            'email',
            'phone',
            'department'
        )
    );

    $student['year_of_study'] = $year ?: '';

    if (
        $student_id === '' ||
        $first_name === '' ||
        $last_name === '' ||
        !filter_var($email, FILTER_VALIDATE_EMAIL) ||
        $department === '' ||
        !$year ||
        $year < 1 ||
        $year > 6
    ) {
        $message = 'Please complete all required fields correctly.';
    } else {
        try {
            $update = $pdo->prepare(
                'UPDATE students
                 SET student_id = ?,
                     first_name = ?,
                     last_name = ?,
                     email = ?,
                     phone = ?,
                     department = ?,
                     year_of_study = ?
                 WHERE id = ?'
            );

            $update->execute([
                $student_id,
                $first_name,
                $last_name,
                $email,
                $phone ?: null,
                $department,
                $year,
                $id
            ]);

            redirect('students.php?updated=1');

        } catch (PDOException $ex) {
            $message = $ex->getCode() === '23000'
                ? 'Student ID or email is already used by another record.'
                : 'Update failed. Please check your data.';

            if ($ex->getCode() !== '23000') {
                error_log($ex->getMessage());
            }
        }
    }
}

$departments = [
    'Computer Science and Engineering',
    'Information Technology',
    'Software Engineering',
    'Electrical Engineering',
    'Other'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Edit Student | Student Portal</title>

    <link rel="stylesheet" href="style.css">

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #f3f6fc;
            color: #172b4d;
            font-family: Arial, Helvetica, sans-serif;
        }

        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            padding: 16px 5%;
            background: #ffffff;
            border-bottom: 1px solid #e5eaf3;
        }

        .brand {
            color: #183b75;
            font-size: 21px;
            font-weight: 800;
            text-decoration: none;
        }

        .brand-mark {
            display: inline-grid;
            place-items: center;
            width: 36px;
            height: 36px;
            margin-right: 8px;
            background: #2457d6;
            color: #ffffff;
            border-radius: 10px;
        }

        .nav-links {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 8px;
        }

        .nav-link {
            padding: 10px 13px;
            border-radius: 8px;
            color: #53647c;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
        }

        .nav-link:hover {
            background: #edf3ff;
            color: #2457d6;
        }

        .container {
            width: 92%;
            max-width: 1000px;
            margin: 38px auto;
        }

        .form-card {
            padding: 32px;
            background: #ffffff;
            border: 1px solid #e5eaf3;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(26, 48, 87, 0.06);
        }

        .eyebrow {
            margin-bottom: 10px;
            color: #2457d6;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 2px;
        }

        h1 {
            margin: 0 0 10px;
            color: #183b75;
            font-size: 32px;
        }

        .muted {
            margin: 0 0 25px;
            color: #748198;
            line-height: 1.7;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 20px;
        }

        .form-grid label {
            display: block;
            margin-bottom: 8px;
            color: #344563;
            font-size: 14px;
            font-weight: 700;
        }

        .form-grid input,
        .form-grid select {
            display: block;
            width: 100%;
            min-width: 0;
            min-height: 47px;
            padding: 12px 13px;
            border: 1px solid #d5ddea;
            border-radius: 9px;
            background: #fbfcff;
            color: #263955;
            font-family: inherit;
            font-size: 14px;
            outline: none;
        }

        .form-grid input:focus,
        .form-grid select:focus {
            border-color: #2457d6;
            box-shadow: 0 0 0 3px rgba(36, 87, 214, 0.1);
        }

        .btn {
            display: inline-flex;
            justify-content: center;
            align-items: center;
            min-height: 45px;
            margin-top: 25px;
            margin-right: 8px;
            padding: 12px 20px;
            border: none;
            border-radius: 9px;
            background: #2457d6;
            color: #ffffff;
            text-decoration: none;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: background 0.2s;
        }

        .btn:hover {
            background: #1944b2;
            color: #ffffff;
        }

        .btn-light {
            background: #edf2fa;
            color: #334765;
        }

        .btn-light:hover {
            background: #dfe8f7;
            color: #183b75;
        }

        .message {
            margin: 0 0 22px;
            padding: 13px 15px;
            border-radius: 9px;
            font-size: 14px;
            line-height: 1.6;
        }

        .error {
            border: 1px solid #f4d0d0;
            background: #fff0f0;
            color: #a52828;
        }

        @media (max-width: 650px) {
            .navbar {
                padding: 14px 4%;
            }

            .nav-links {
                width: 100%;
            }

            .nav-link {
                padding: 9px;
                font-size: 12px;
            }

            .container {
                width: 94%;
                margin: 25px auto;
            }

            .form-card {
                padding: 22px 18px;
            }

            h1 {
                font-size: 27px;
            }

            .form-grid {
                grid-template-columns: 1fr;
                gap: 16px;
            }

            .btn {
                margin-top: 18px;
            }
        }
    </style>
</head>

<body>

<nav class="navbar">
    <a class="brand" href="index.php">
        <span class="brand-mark">S</span>
        Student Portal
    </a>

    <div class="nav-links">
        <a class="nav-link" href="dashboard.php">Dashboard</a>
        <a class="nav-link" href="students.php">Students</a>
        <a class="nav-link" href="logout.php">Log out</a>
    </div>
</nav>

<main class="container">

    <section class="form-card">

        <div class="eyebrow">RECORD MANAGEMENT</div>

        <h1>Edit Student</h1>

        <p class="muted">
            Update the student information below.
        </p>

        <?php if ($message): ?>
            <div class="message error">
                <?= e($message) ?>
            </div>
        <?php endif; ?>

        <form method="post" action="edit_student.php?id=<?= (int) $id ?>">

            <input
                type="hidden"
                name="csrf_token"
                value="<?= e(csrf_token()) ?>"
            >

            <div class="form-grid">

                <div>
                    <label for="student_id">Student ID *</label>
                    <input
                        id="student_id"
                        name="student_id"
                        required
                        maxlength="30"
                        value="<?= e($student['student_id']) ?>"
                    >
                </div>

                <div>
                    <label for="first_name">First Name *</label>
                    <input
                        id="first_name"
                        name="first_name"
                        required
                        maxlength="80"
                        value="<?= e($student['first_name']) ?>"
                    >
                </div>

                <div>
                    <label for="last_name">Last Name *</label>
                    <input
                        id="last_name"
                        name="last_name"
                        required
                        maxlength="80"
                        value="<?= e($student['last_name']) ?>"
                    >
                </div>

                <div>
                    <label for="email">Email Address *</label>
                    <input
                        id="email"
                        name="email"
                        type="email"
                        required
                        maxlength="150"
                        value="<?= e($student['email']) ?>"
                    >
                </div>

                <div>
                    <label for="phone">Phone Number</label>
                    <input
                        id="phone"
                        name="phone"
                        type="tel"
                        maxlength="25"
                        value="<?= e($student['phone'] ?? '') ?>"
                    >
                </div>

                <div>
                    <label for="department">Department *</label>
                    <select id="department" name="department" required>
                        <?php foreach ($departments as $dept): ?>
                            <option
                                value="<?= e($dept) ?>"
                                <?= $student['department'] === $dept ? 'selected' : '' ?>
                            >
                                <?= e($dept) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label for="year_of_study">Year of Study *</label>
                    <select id="year_of_study" name="year_of_study" required>
                        <?php for ($i = 1; $i <= 6; $i++): ?>
                            <option
                                value="<?= $i ?>"
                                <?= (string) $student['year_of_study'] === (string) $i ? 'selected' : '' ?>
                            >
                                Year <?= $i ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>

            </div>

            <button class="btn" type="submit">
                Save Changes
            </button>

            <a class="btn btn-light" href="students.php">
                Cancel
            </a>

        </form>

    </section>

</main>

</body>
</html>
