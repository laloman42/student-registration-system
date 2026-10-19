<?php
require_once "config.php";
require_admin();

$error = '';
$success = '';
$student = null;
$students = [];

/* Update student information */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Verify CSRF token before processing the form
    if (!verify_csrf()) {
        http_response_code(403);
        exit('Invalid security token. Please reload the page and try again.');
    }

    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $department = trim($_POST['department'] ?? '');
    $year = filter_input(INPUT_POST, 'year_of_study', FILTER_VALIDATE_INT);
    $gender = trim($_POST['gender'] ?? '');

    if (!$id || $id < 1) {
        $error = 'Invalid student ID.';
    } elseif (
        $firstName === '' ||
        $lastName === '' ||
        !filter_var($email, FILTER_VALIDATE_EMAIL) ||
        $department === '' ||
        $year === false ||
        $year === null ||
        $year < 1 ||
        $year > 20 ||
        !in_array($gender, ['Male', 'Female', 'Other'], true)
    ) {
        $error = 'Please enter valid information in all required fields.';
    } elseif (
        strlen($firstName) > 80 ||
        strlen($lastName) > 80 ||
        strlen($email) > 150 ||
        strlen($phone) > 25 ||
        strlen($department) > 100
    ) {
        $error = 'One or more fields exceed the allowed length.';
    } elseif (
        $phone !== '' &&
        !preg_match('/^[0-9+\s()-]+$/', $phone)
    ) {
        $error = 'Phone number contains invalid characters.';
    } else {
        try {
            $stmt = $pdo->prepare(
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

            $stmt->execute([
                $firstName,
                $lastName,
                $email,
                $phone,
                $department,
                $year,
                $gender,
                $id
            ]);

            $success = 'Student information updated successfully.';

        } catch (PDOException $e) {
            error_log($e->getMessage());

            if ($e->getCode() === '23000') {
                $error = 'This email address is already used by another student.';
            } else {
                $error = 'Could not update student information.';
            }
        }
    }

    // Reload the selected student
    if ($id && $id > 0) {
        $stmt = $pdo->prepare(
            "SELECT id, student_id, first_name, last_name,
                    email, phone, department, year_of_study, gender
             FROM students
             WHERE id = ?
             LIMIT 1"
        );

        $stmt->execute([$id]);
        $student = $stmt->fetch();
    }
}

/* Select a student from the list */
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

    if ($id !== null && $id !== false && $id > 0) {
        $stmt = $pdo->prepare(
            "SELECT id, student_id, first_name, last_name,
                    email, phone, department, year_of_study, gender
             FROM students
             WHERE id = ?
             LIMIT 1"
        );

        $stmt->execute([$id]);
        $student = $stmt->fetch();

        if (!$student) {
            $error = 'Student not found.';
        }
    }
}

/* Load student list when no student is selected */
if (!$student && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $pdo->query(
        "SELECT id, student_id, first_name, last_name, email
         FROM students
         ORDER BY id DESC"
    );

    $students = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin - Edit Student</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f1f5f9;
            color: #1e293b;
            margin: 0;
            padding: 25px 15px;
        }

        .container {
            max-width: 900px;
            margin: auto;
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 15px #0000000d;
        }

        h1 {
            color: #1e3a8a;
        }

        .table-wrap {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            text-align: left;
            padding: 12px;
            border-bottom: 1px solid #e2e8f0;
        }

        th {
            background: #1e3a8a;
            color: white;
        }

        label {
            display: block;
            margin-top: 14px;
            font-weight: bold;
        }

        input, select {
            width: 100%;
            padding: 11px;
            margin-top: 6px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
        }

        button, .btn {
            display: inline-block;
            margin-top: 18px;
            padding: 11px 16px;
            border: 0;
            border-radius: 6px;
            background: #2563eb;
            color: white;
            text-decoration: none;
            cursor: pointer;
        }

        .back {
            background: #475569;
        }

        .error, .success {
            padding: 12px;
            border-radius: 6px;
            margin: 12px 0;
        }

        .error {
            background: #fee2e2;
            color: #991b1b;
        }

        .success {
            background: #dcfce7;
            color: #166534;
        }
    </style>
</head>

<body>
<div class="container">

    <h1>Admin - Edit Student</h1>

    <?php if ($error !== ''): ?>
        <div class="error"><?= e($error) ?></div>
    <?php endif; ?>

    <?php if ($success !== ''): ?>
        <div class="success"><?= e($success) ?></div>
    <?php endif; ?>

    <?php if ($student): ?>

        <p>
            Editing student:
            <strong><?= e($student['student_id']) ?></strong>
        </p>

        <form method="POST" action="admin_edit_student.php">

            <input
                type="hidden"
                name="csrf_token"
                value="<?= e(csrf_token()) ?>"
            >

            <input
                type="hidden"
                name="id"
                value="<?= (int) $student['id'] ?>"
            >

            <label for="first_name">First Name *</label>
            <input
                id="first_name"
                name="first_name"
                maxlength="80"
                required
                value="<?= e($student['first_name']) ?>"
            >

            <label for="last_name">Last Name *</label>
            <input
                id="last_name"
                name="last_name"
                maxlength="80"
                required
                value="<?= e($student['last_name']) ?>"
            >

            <label for="email">Email *</label>
            <input
                id="email"
                name="email"
                type="email"
                maxlength="150"
                required
                value="<?= e($student['email']) ?>"
            >

            <label for="phone">Phone</label>
            <input
                id="phone"
                name="phone"
                maxlength="25"
                value="<?= e($student['phone'] ?? '') ?>"
            >

            <label for="department">Department *</label>
            <input
                id="department"
                name="department"
                maxlength="100"
                required
                value="<?= e($student['department']) ?>"
            >

            <label for="year_of_study">Year of Study *</label>
            <input
                id="year_of_study"
                name="year_of_study"
                type="number"
                min="1"
                max="20"
                required
                value="<?= (int) $student['year_of_study'] ?>"
            >

            <label for="gender">Gender *</label>
            <select id="gender" name="gender" required>
                <?php foreach (['Male', 'Female', 'Other'] as $option): ?>
                    <option
                        value="<?= e($option) ?>"
                        <?= $student['gender'] === $option ? 'selected' : '' ?>
                    >
                        <?= e($option) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit">Save Changes</button>

            <a class="btn back" href="admin_edit_student.php">
                Student List
            </a>
        </form>

    <?php else: ?>

        <h2>Select a Student to Edit</h2>

        <?php if (count($students) > 0): ?>

            <div class="table-wrap">
                <table>
                    <thead>
                    <tr>
                        <th>Student ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Action</th>
                    </tr>
                    </thead>

                    <tbody>
                    <?php foreach ($students as $item): ?>
                        <tr>
                            <td><?= e($item['student_id']) ?></td>

                            <td>
                                <?= e(
                                    $item['first_name'] . ' ' .
                                    $item['last_name']
                                ) ?>
                            </td>

                            <td><?= e($item['email']) ?></td>

                            <td>
                                <a href="admin_edit_student.php?id=<?= (int) $item['id'] ?>">
                                    Edit
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        <?php else: ?>
            <p>No students found.</p>
        <?php endif; ?>

    <?php endif; ?>

    <p>
        <a class="btn back" href="admin_dashboard.php">
            Back to Dashboard
        </a>
    </p>

</div>
</body>
</html>