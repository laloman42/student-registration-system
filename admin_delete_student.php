
<?php
require_once "config.php";
require_admin();

$error = '';
$success = '';

// Delete only after the confirmation form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        http_response_code(403);
        exit('Invalid security token. Please reload the page and try again.');
    }

    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $confirmation = $_POST['confirmation'] ?? '';

    if (!$id) {
        $error = 'Invalid student selection.';
    } elseif ($confirmation !== 'DELETE') {
        $error = 'Please type DELETE to confirm.';
    } else {
        $stmt = $pdo->prepare(
            "DELETE FROM students WHERE id = ?"
        );
        $stmt->execute([$id]);

        if ($stmt->rowCount() > 0) {
            $success = 'Student deleted successfully.';
        } else {
            $error = 'Student was not found or was already deleted.';
        }
    }
}

// Get the student list
$stmt = $pdo->query(
    "SELECT id, student_id, first_name, last_name, email, department
     FROM students
     ORDER BY id DESC"
);
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin - Delete Student</title>

    <style>
        * { box-sizing: border-box; }

        body {
            margin: 0;
            padding: 25px 15px;
            font-family: Arial, sans-serif;
            background: #f1f5f9;
            color: #1e293b;
        }

        .container {
            max-width: 1000px;
            margin: auto;
            padding: 25px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px #0000000d;
        }

        h1 { color: #1e3a8a; }

        .table-wrap { overflow-x: auto; }

        table {
            width: 100%;
            min-width: 650px;
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

        .btn {
            display: inline-block;
            padding: 9px 13px;
            border: 0;
            border-radius: 5px;
            text-decoration: none;
            cursor: pointer;
            background: #475569;
            color: white;
        }

        .delete-btn {
            background: #dc2626;
        }

        .delete-btn:hover { background: #b91c1c; }

        .message {
            padding: 12px;
            border-radius: 6px;
            margin: 15px 0;
        }

        .error {
            background: #fee2e2;
            color: #991b1b;
        }

        .success {
            background: #dcfce7;
            color: #166534;
        }

        .warning {
            padding: 14px;
            margin: 15px 0;
            border-radius: 6px;
            background: #fff7ed;
            color: #9a3412;
        }
    </style>
</head>

<body>
<div class="container">

    <h1>Delete Student</h1>
    <p>Select a student only if you are sure the record should be removed.</p>

    <?php if ($error !== ''): ?>
        <div class="message error"><?= e($error) ?></div>
    <?php endif; ?>

    <?php if ($success !== ''): ?>
        <div class="message success"><?= e($success) ?></div>
    <?php endif; ?>

    <?php if (count($students) > 0): ?>
        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>Student ID</th>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Department</th>
                    <th>Action</th>
                </tr>
                </thead>

                <tbody>
                <?php foreach ($students as $student): ?>
                    <tr>
                        <td><?= e($student['student_id']) ?></td>
                        <td>
                            <?= e($student['first_name'] . ' ' . $student['last_name']) ?>
                        </td>
                        <td><?= e($student['email']) ?></td>
                        <td><?= e($student['department']) ?></td>
                        <td>
                            <form method="POST"
                                  onsubmit="return confirm('Are you sure you want to delete this student? This cannot easily be undone.');">

                                <input type="hidden" name="csrf_token"
                                       value="<?= e(csrf_token()) ?>">

                                <input type="hidden" name="id"
                                       value="<?= (int) $student['id'] ?>">

                                <label>
                                    Type DELETE:
                                    <input type="text"
                                           name="confirmation"
                                           required
                                           pattern="DELETE"
                                           title="Type DELETE in capital letters"
                                           style="width:100px;padding:7px;">
                                </label>

                                <button class="btn delete-btn" type="submit">
                                    Delete Student
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p>No students found.</p>
    <?php endif; ?>

    <p style="margin-top:25px;">
        <a class="btn" href="admin_dashboard.php">Back to Dashboard</a>
    </p>

</div>
</body>
</html>
