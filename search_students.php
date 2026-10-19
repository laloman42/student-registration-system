
<?php
require_once "config.php";

// Only an authenticated admin can search students
require_admin();

$search = trim($_GET['search'] ?? '');
$students = [];

if ($search !== '') {
    $stmt = $pdo->prepare(
        "SELECT id, student_id, first_name, last_name,
                email, phone, department, year_of_study,
                gender, created_at
         FROM students
         WHERE CAST(student_id AS CHAR) = :student_id
            OR first_name LIKE :first_name
            OR last_name LIKE :last_name
            OR email LIKE :email
         ORDER BY id DESC"
    );

    $term = '%' . $search . '%';

    $stmt->execute([
        'student_id' => $search,
        'first_name' => $term,
        'last_name'  => $term,
        'email'      => $term
    ]);

    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Search Students | Student Portal</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 25px 15px;
            font-family: Arial, sans-serif;
            background: #f1f5f9;
            color: #1e293b;
        }

        .container {
            max-width: 1150px;
            margin: auto;
            padding: 25px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px #0000000d;
        }

        h1 {
            color: #1e3a8a;
            margin-top: 0;
        }

        .search-form {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin: 20px 0;
        }

        .search-form input {
            flex: 1;
            min-width: 220px;
            padding: 12px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            font-size: 16px;
        }

        button, .btn {
            display: inline-block;
            padding: 12px 17px;
            border: none;
            border-radius: 6px;
            background: #2563eb;
            color: white;
            text-decoration: none;
            cursor: pointer;
            font-size: 15px;
        }

        button:hover, .btn:hover {
            background: #1d4ed8;
        }

        .back {
            background: #475569;
        }

        .table-wrapper {
            overflow-x: auto;
        }

        table {
            width: 100%;
            min-width: 850px;
            border-collapse: collapse;
            margin-top: 20px;
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

        tr:nth-child(even) {
            background: #f8fafc;
        }

        .message {
            margin-top: 20px;
            padding: 14px;
            border-radius: 6px;
            background: #eff6ff;
            color: #1e3a8a;
        }

        .count {
            color: #475569;
        }

        @media (max-width: 600px) {
            .container {
                padding: 18px;
            }

            .search-form {
                flex-direction: column;
            }

            .search-form input {
                min-width: 100%;
            }

            .search-form button,
            .search-form .btn {
                text-align: center;
            }
        }
    </style>
</head>

<body>
<div class="container">

    <h1>Search Students</h1>

    <p>
        Search by exact student ID, first name, last name, or email.
    </p>

    <form method="GET" action="search_students.php" class="search-form">
        <input
            type="search"
            name="search"
            placeholder="Enter student ID, name, or email..."
            value="<?= e($search) ?>"
            aria-label="Search students"
        >

        <button type="submit">Search</button>

        <a class="btn back" href="search_students.php">
            Clear
        </a>
    </form>

    <?php if ($search !== ''): ?>

        <p class="count">
            Results found: <strong><?= count($students) ?></strong>
        </p>

        <?php if (count($students) > 0): ?>

            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Student ID</th>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Department</th>
                            <th>Year</th>
                            <th>Gender</th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php foreach ($students as $student): ?>
                        <tr>
                            <td><?= e($student['student_id']) ?></td>
                            <td><?= e($student['first_name']) ?></td>
                            <td><?= e($student['last_name']) ?></td>
                            <td><?= e($student['email']) ?></td>
                            <td><?= e($student['phone'] ?? '') ?></td>
                            <td><?= e($student['department']) ?></td>
                            <td><?= e($student['year_of_study']) ?></td>
                            <td><?= e($student['gender']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        <?php else: ?>

            <div class="message">
                No students found. Check the name or student ID and try again.
            </div>

        <?php endif; ?>

    <?php else: ?>

        <div class="message">
            Enter a student name, student ID, or email address to begin searching.
        </div>

    <?php endif; ?>

    <p style="margin-top: 25px;">
        <a class="btn back" href="admin_dashboard.php">
            Back to Admin Dashboard
        </a>
    </p>

</div>
</body>
</html>
