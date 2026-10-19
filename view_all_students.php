
<?php
require_once "config.php";

// Only logged-in admins can view all students
require_admin();

// Get all students from the database
$stmt = $pdo->query(
    "SELECT id, student_id, first_name, last_name, email,
            phone, department, year_of_study, gender,
            created_at, profile_photo
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
    <title>View All Students</title>

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
            max-width: 1250px;
            margin: auto;
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 15px #0000000d;
        }

        h1 {
            color: #1e3a8a;
            margin-top: 0;
        }

        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }

        .btn {
            display: inline-block;
            padding: 10px 15px;
            background: #2563eb;
            color: white;
            text-decoration: none;
            border-radius: 6px;
        }

        .btn:hover {
            background: #1d4ed8;
        }

        .table-wrapper {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 950px;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }

        th {
            background: #1e3a8a;
            color: white;
        }

        tr:nth-child(even) {
            background: #f8fafc;
        }

        tr:hover {
            background: #eff6ff;
        }

        .student-photo {
            width: 48px;
            height: 48px;
            object-fit: cover;
            border-radius: 50%;
        }

        .no-photo {
            color: #64748b;
            font-size: 12px;
        }

        .empty {
            padding: 20px;
            background: #eff6ff;
            border-radius: 6px;
        }
    </style>
</head>

<body>
<div class="container">

    <div class="top-bar">
        <div>
            <h1>View All Students</h1>
            <p>
                Total registered students:
                <strong><?= count($students) ?></strong>
            </p>
        </div>

        <a class="btn" href="admin_dashboard.php">
            Back to Dashboard
        </a>
    </div>

    <?php if (count($students) > 0): ?>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Photo</th>
                        <th>Student ID</th>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Department</th>
                        <th>Year</th>
                        <th>Gender</th>
                        <th>Registered At</th>
                    </tr>
                </thead>

                <tbody>
                <?php foreach ($students as $student): ?>
                    <tr>
                        <td>
                            <?php if (!empty($student['profile_photo'])): ?>
                                <img
                                    class="student-photo"
                                    src="<?= e($student['profile_photo']) ?>"
                                    alt="Student photo"
                                >
                            <?php else: ?>
                                <span class="no-photo">No photo</span>
                            <?php endif; ?>
                        </td>

                        <td><?= e($student['student_id']) ?></td>
                        <td><?= e($student['first_name']) ?></td>
                        <td><?= e($student['last_name']) ?></td>
                        <td><?= e($student['email']) ?></td>
                        <td><?= e($student['phone'] ?? '') ?></td>
                        <td><?= e($student['department']) ?></td>
                        <td><?= e($student['year_of_study']) ?></td>
                        <td><?= e($student['gender']) ?></td>
                        <td><?= e($student['created_at'] ?? '') ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    <?php else: ?>

        <p class="empty">
            No students registered yet.
        </p>

    <?php endif; ?>

</div>
</body>
</html>
