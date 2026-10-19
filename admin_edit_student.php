<?php
require_once "config.php";
require_admin();

$error = '';
$success = '';
$student = null;
$students = [];

/* Process update form */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        http_response_code(403);
        exit('Invalid security token. Please reload the page.');
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
        $year > 6 ||
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
        $error = 'One or more fields are too long.';
    } else {
        try {
            /* Check whether this student exists */
            $check = $pdo->prepare(
                "SELECT id FROM students WHERE id = ?"
            );
            $check->execute([$id]);

            if (!$check->fetch()) {
                $error = 'Student not found.';
            } else {
                /* Prevent duplicate email addresses */
                $checkEmail = $pdo->prepare(
                    "SELECT id FROM students
                     WHERE email = ? AND id <> ?
                     LIMIT 1"
                );
                $checkEmail->execute([$email, $id]);

                if ($checkEmail->fetch()) {
                    $error = 'This email is already used by another student.';
                } else {
                    /* Update student information */
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
                        $firstName,
                        $lastName,
                        $email,
                        $phone !== '' ? $phone : null,
                        $department,
                        $year,
                        $gender,
                        $id
                    ]);

                    $success = 'Student information updated successfully.';
                }
            }
        } catch (PDOException $e) {
            error_log('Student update error: ' . $e->getMessage());
            $error = 'Unable to update student information.';
        }
    }

    /* Keep the selected student visible after submission */
    if ($id && $id > 0) {
        $selectedId = $id;
    }
} else {
    $selectedId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
}

/* Load students for the selection list */
try {
    $list = $pdo->query(
        "SELECT id, student_id, first_name, last_name
         FROM students
         ORDER BY id DESC"
    );
    $students = $list->fetchAll(PDO::FETCH_ASSOC);

    /* Load selected student */
    if (!empty($selectedId) && $selectedId > 0) {
        $stmt = $pdo->prepare(
            "SELECT id, student_id, first_name, last_name,
                    email, phone, department, year_of_study, gender
             FROM students
             WHERE id = ?
             LIMIT 1"
        );
        $stmt->execute([$selectedId]);
        $student = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$student && $error === '') {
            $error = 'Student not found.';
        }
    }
} catch (PDOException $e) {
    error_log('Student list error: ' . $e->getMessage());
    $error = 'Unable to load student records.';
}

function h($value): string
{
    return htmlspecialchars(
        (string) $value,
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
    <title>Edit Student</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .edit-container {
            max-width: 850px;
            margin: 30px auto;
            padding: 25px;
            border-radius: 10px;
            background: #fff;
            color: #222;
            box-shadow: 0 3px 12px rgba(0,0,0,.12);
        }

        .edit-container h2 {
            margin-top: 0;
        }

        .edit-form {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 15px;
        }

        .edit-form label {
            display: block;
            margin-bottom: 6px;
            font-weight: bold;
        }

        .edit-form input,
        .edit-form select {
            width: 100%;
            box-sizing: border-box;
            padding: 10px;
            border: 1px solid #bbb;
            border-radius: 5px;
        }

        .edit-form button {
            padding: 12px;
            border: 0;
            border-radius: 5px;
            background: #087fca;
            color: white;
            cursor: pointer;
        }

        .message {
            padding: 12px;
            margin: 12px 0;
            border-radius: 5px;
        }

        .error {
            background: #ffe5e5;
            color: #9b111e;
        }

        .success {
            background: #e2f7e8;
            color: #17652d;
        }

        .full-width {
            grid-column: 1 / -1;
        }

        .student-list {
            margin-bottom: 25px;
        }

        .student-list a {
            display: inline-block;
            margin: 5px;
            padding: 8px 12px;
            border: 1px solid #087fca;
            border-radius: 5px;
            text-decoration: none;
        }

        @media (max-width: 600px) {
            .edit-form {
                grid-template-columns: 1fr;
            }

            .full-width {
                grid-column: auto;
            }
        }
    </style>
</head>
<body>

<div class="edit-container">
    <h2>Edit Student Information</h2>

    <p>
        <a href="admin_dashboard.php">Back to Admin Dashboard</a>
    </p>

    <?php if ($error !== ''): ?>
        <div class="message error"><?= h($error) ?></div>
    <?php endif; ?>

    <?php if ($success !== ''): ?>
        <div class="message success"><?= h($success) ?></div>
    <?php endif; ?>

    <div class="student-list">
        <h3>Select a student to edit</h3>

        <?php if (empty($students)): ?>
            <p>No students registered yet.</p>
        <?php else: ?>
            <?php foreach ($students as $item): ?>
                <a href="admin_edit_student.php?id=<?= (int) $item['id'] ?>">
                    <?= h($item['student_id']) ?> -
                    <?= h($item['first_name'] . ' ' . $item['last_name']) ?>
                </a>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <?php if ($student): ?>
        <h3>
            Editing:
            <?= h($student['first_name'] . ' ' . $student['last_name']) ?>
        </h3>

        <form method="POST" action="admin_edit_student.php?id=<?= (int) $student['id'] ?>"
              class="edit-form">

            <input type="hidden" name="csrf_token"
                   value="<?= h(csrf_token()) ?>">

            <input type="hidden" name="id"
                   value="<?= (int) $student['id'] ?>">

            <div>
                <label>Student ID</label>
                <input type="text"
                       value="<?= h($student['student_id']) ?>"
                       readonly>
            </div>

            <div>
                <label for="first_name">First Name</label>
                <input id="first_name" name="first_name"
                       maxlength="80" required
                       value="<?= h($student['first_name']) ?>">
            </div>

            <div>
                <label for="last_name">Last Name</label>
                <input id="last_name" name="last_name"
                       maxlength="80" required
                       value="<?= h($student['last_name']) ?>">
            </div>

            <div>
                <label for="email">Email</label>
                <input id="email" type="email" name="email"
                       maxlength="150" required
                       value="<?= h($student['email']) ?>">
            </div>

            <div>
                <label for="phone">Phone</label>
                <input id="phone" type="text" name="phone"
                       maxlength="25"
                       value="<?= h($student['phone'] ?? '') ?>">
            </div>

            <div>
                <label for="department">Department</label>
                <input id="department" type="text" name="department"
                       maxlength="100" required
                       value="<?= h($student['department']) ?>">
            </div>

            <div>
                <label for="year_of_study">Year of Study</label>
                <select id="year_of_study" name="year_of_study" required>
                    <?php for ($y = 1; $y <= 6; $y++): ?>
                        <option value="<?= $y ?>"
                            <?= (int) $student['year_of_study'] === $y
                                ? 'selected' : '' ?>>
                            Year <?= $y ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>

            <div>
                <label for="gender">Gender</label>
                <select id="gender" name="gender" required>
                    <?php foreach (['Male', 'Female', 'Other'] as $g): ?>
                        <option value="<?= h($g) ?>"
                            <?= $student['gender'] === $g
                                ? 'selected' : '' ?>>
                            <?= h($g) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="full-width">
                <button type="submit">Update Student</button>
            </div>
        </form>
    <?php else: ?>
        <p>Select a student from the list above to edit their details.</p>
    <?php endif; ?>
</div>

</body>
</html>
