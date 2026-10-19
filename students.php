
<?php
require_once "config.php";
require_admin();

// Search keyword
$q = trim($_GET['q'] ?? '');

// Total students in the database
$allStudents = (int)$pdo->query(
    "SELECT COUNT(*) FROM students"
)->fetchColumn();

// Search students
if ($q !== '') {
    $like = '%' . $q . '%';

    $stmt = $pdo->prepare(
        "SELECT *
         FROM students
         WHERE student_id LIKE ?
            OR first_name LIKE ?
            OR last_name LIKE ?
            OR email LIKE ?
            OR department LIKE ?
         ORDER BY created_at DESC, id DESC"
    );

    $stmt->execute([
        $like,
        $like,
        $like,
        $like,
        $like
    ]);

    $students = $stmt->fetchAll();
} else {
    $students = $pdo->query(
        "SELECT *
         FROM students
         ORDER BY created_at DESC, id DESC"
    )->fetchAll();
}

$resultCount = count($students);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Manage Students | Student Portal</title>

    <style>
        * {
            box-sizing: border-box;
        }

        :root {
            --blue: #2457d6;
            --blue-dark: #1944b2;
            --navy: #17365e;
            --text: #172b4d;
            --muted: #748198;
            --border: #e5eaf3;
            --background: #f3f6fc;
            --white: #ffffff;
        }

        body {
            margin: 0;
            background: var(--background);
            color: var(--text);
            font-family: Arial, Helvetica, sans-serif;
        }

        a {
            text-decoration: none;
        }

        .navbar {
            padding: 16px 5%;
            background: var(--white);
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .brand {
            color: var(--navy);
            font-size: 21px;
            font-weight: 800;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .brand-mark {
            display: grid;
            place-items: center;
            width: 39px;
            height: 39px;
            background: var(--blue);
            color: white;
            border-radius: 11px;
        }

        .nav-links {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 7px;
        }

        .nav-link {
            color: #53647c;
            padding: 10px 13px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 700;
        }

        .nav-link:hover,
        .nav-link.active {
            color: var(--blue);
            background: #edf3ff;
        }

        .main {
            max-width: 1300px;
            width: 92%;
            margin: 35px auto;
        }

        .page-heading {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 25px;
        }

        .eyebrow {
            color: var(--blue);
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 2px;
            margin-bottom: 10px;
        }

        h1 {
            color: var(--navy);
            font-size: clamp(26px, 4vw, 34px);
            margin: 0 0 10px;
        }

        .muted {
            color: var(--muted);
            line-height: 1.6;
            font-size: 13px;
            margin: 0;
        }

        .btn {
            display: inline-flex;
            justify-content: center;
            align-items: center;
            min-height: 43px;
            padding: 11px 17px;
            background: var(--blue);
            color: white;
            border: 0;
            border-radius: 9px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            transition: 0.2s;
        }

        .btn:hover {
            background: var(--blue-dark);
            color: white;
        }

        .btn-light {
            background: #edf2fa;
            color: #334765;
        }

        .btn-light:hover {
            background: #dfe8f7;
            color: var(--navy);
        }

        .summary-cards {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px;
            margin-bottom: 22px;
        }

        .summary-card {
            background: white;
            border: 1px solid var(--border);
            border-radius: 13px;
            padding: 22px;
            box-shadow: 0 5px 18px rgba(26, 48, 87, 0.04);
        }

        .summary-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
        }

        .summary-label {
            color: var(--muted);
            font-size: 13px;
            font-weight: 700;
        }

        .summary-icon {
            width: 40px;
            height: 40px;
            display: grid;
            place-items: center;
            background: #edf3ff;
            color: var(--blue);
            border-radius: 11px;
            font-size: 20px;
        }

        .summary-number {
            display: block;
            color: var(--navy);
            font-size: 30px;
            font-weight: 800;
            margin-top: 14px;
        }

        .summary-note {
            color: #8a97aa;
            font-size: 12px;
            margin-top: 7px;
        }

        .card {
            background: white;
            border: 1px solid var(--border);
            border-radius: 15px;
            box-shadow: 0 8px 28px rgba(26, 48, 87, 0.05);
            overflow: hidden;
        }

        .card-heading {
            padding: 23px 25px;
            border-bottom: 1px solid #edf0f5;
        }

        .card-heading h2 {
            margin: 0 0 8px;
            color: var(--navy);
            font-size: 18px;
        }

        .card-body {
            padding: 23px 25px;
        }

        .search-form {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 11px;
            margin-bottom: 21px;
        }

        .search-form input {
            flex: 1 1 270px;
            min-width: 0;
            min-height: 45px;
            padding: 12px 15px;
            border: 1px solid #d5ddea;
            border-radius: 9px;
            background: #fbfcff;
            color: var(--text);
            font-size: 14px;
            outline: none;
        }

        .search-form input:focus {
            border-color: var(--blue);
            box-shadow: 0 0 0 3px rgba(36, 87, 214, 0.1);
        }

        .results-note {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 15px;
        }

        .results-note strong {
            color: var(--navy);
        }

        .table-wrap {
            width: 100%;
            overflow-x: auto;
            border: 1px solid #e8edf5;
            border-radius: 10px;
        }

        table {
            width: 100%;
            min-width: 850px;
            border-collapse: collapse;
            background: white;
        }

        th {
            padding: 16px 14px;
            background: #f5f8fe;
            color: #63738b;
            text-align: left;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            font-size: 10px;
            white-space: nowrap;
        }

        td {
            padding: 16px 14px;
            border-top: 1px solid #edf0f5;
            color: #40516b;
            font-size: 12px;
            vertical-align: middle;
        }

        tbody tr:hover {
            background: #fafcff;
        }

        .student-id {
            color: var(--blue);
            font-weight: 800;
            white-space: nowrap;
        }

        .student-name {
            color: var(--text);
            font-weight: 700;
        }

        .year-badge {
            display: inline-block;
            background: #edf3ff;
            color: var(--blue);
            padding: 6px 9px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            white-space: nowrap;
        }

        .actions {
            white-space: nowrap;
        }

        .actions form {
            display: inline;
            margin: 0;
        }

        .action-link {
            display: inline-block;
            padding: 8px 11px;
            border: 1px solid #d7e3ff;
            border-radius: 7px;
            background: #edf3ff;
            color: var(--blue);
            font-size: 11px;
            font-weight: 700;
            cursor: pointer;
            font-family: inherit;
        }

        .action-link:hover {
            background: #dce8ff;
        }

        .danger-link {
            margin-left: 4px;
            background: #fff0f0;
            color: #c03939;
            border-color: #f4d0d0;
        }

        .danger-link:hover {
            background: #ffdddd;
            color: #9d2020;
        }

        .empty {
            padding: 35px !important;
            text-align: center;
            color: var(--muted) !important;
        }

        .empty strong {
            display: block;
            color: var(--navy);
            margin-bottom: 8px;
            font-size: 15px;
        }

        .card-footer {
            padding: 17px 25px;
            border-top: 1px solid #edf0f5;
            color: var(--muted);
            font-size: 12px;
        }

        .footer {
            text-align: center;
            color: #8a97aa;
            font-size: 12px;
            padding: 28px 10px;
        }

        .sr-only {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border: 0;
        }

        @media (max-width: 650px) {
            .navbar {
                padding: 14px 4%;
            }

            .nav-links {
                width: 100%;
                gap: 4px;
            }

            .nav-link {
                padding: 9px;
                font-size: 12px;
            }

            .main {
                width: 94%;
                margin: 24px auto;
            }

            .page-heading {
                align-items: flex-start;
            }

            .summary-cards {
                grid-template-columns: 1fr;
                gap: 13px;
            }

            .card-body {
                padding: 15px;
            }

            .card-heading {
                padding: 19px 15px;
            }

            .search-form {
                align-items: stretch;
            }

            .search-form input {
                flex-basis: 100%;
            }

            .search-form .btn {
                flex: 1;
            }
        }
    </style>
</head>

<body>

<nav class="navbar">
    <a class="brand" href="dashboard.php">
        <span class="brand-mark">S</span>
        Student Portal
    </a>

    <div class="nav-links">
        <a class="nav-link" href="dashboard.php">Dashboard</a>
        <a class="nav-link active" href="students.php">Students</a>
        <a class="nav-link" href="register.php">Register Student</a>
        <a class="nav-link" href="logout.php">Log Out</a>
    </div>
</nav>

<main class="main">

    <section class="page-heading">
        <div>
            <div class="eyebrow">RECORD MANAGEMENT</div>
            <h1>Student Records</h1>
            <p class="muted">
                Search, edit, and manage registered student records.
            </p>
        </div>

        <a class="btn" href="register.php">
            + Register Student
        </a>
    </section>

    <section class="summary-cards">

        <article class="summary-card">
            <div class="summary-top">
                <span class="summary-label">Total Students</span>
                <span class="summary-icon">♙</span>
            </div>

            <strong class="summary-number">
                <?= $allStudents ?>
            </strong>

            <div class="summary-note">
                All registered student records
            </div>
        </article>

        <article class="summary-card">
            <div class="summary-top">
                <span class="summary-label">Search Results</span>
                <span class="summary-icon">⌕</span>
            </div>

            <strong class="summary-number">
                <?= $resultCount ?>
            </strong>

            <div class="summary-note">
                <?= $q !== ''
                    ? 'Matching your search'
                    : 'Students currently displayed' ?>
            </div>
        </article>

    </section>

    <section class="card">

        <div class="card-heading">
            <h2>Manage Student Records</h2>
            <p class="muted">
                Search by Student ID, name, email, or department.
            </p>
        </div>

        <div class="card-body">

            <form
                class="search-form"
                method="get"
                action="students.php"
            >
                <label class="sr-only" for="q">
                    Search students
                </label>

                <input
                    id="q"
                    type="search"
                    name="q"
                    placeholder="Enter ID, name, email, department..."
                    value="<?= e($q) ?>"
                >

                <button class="btn" type="submit">
                    Search
                </button>

                <?php if ($q !== ''): ?>
                    <a class="btn btn-light" href="students.php">
                        Clear Search
                    </a>
                <?php endif; ?>
            </form>

            <div class="results-note">
                <p class="muted">
                    <?php if ($q !== ''): ?>
                        Search term:
                        <strong><?= e($q) ?></strong>
                    <?php else: ?>
                        Showing all student records
                    <?php endif; ?>
                </p>

                <p class="muted">
                    Results: <strong><?= $resultCount ?></strong>
                </p>
            </div>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Student ID</th>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Department</th>
                            <th>Year</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php if (!$students): ?>
                        <tr>
                            <td colspan="6" class="empty">
                                <strong>No students found</strong>
                                <?php if ($q !== ''): ?>
                                    Try another search term or clear the search.
                                <?php else: ?>
                                    Register a student to see records here.
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($students as $s): ?>
                            <tr>
                                <td class="student-id">
                                    <?= e($s['student_id']) ?>
                                </td>

                                <td class="student-name">
                                    <?= e(
                                        $s['first_name'] . ' ' . $s['last_name']
                                    ) ?>
                                </td>

                                <td>
                                    <?= e($s['email']) ?>
                                </td>

                                <td>
                                    <?= e($s['department'] ?? '') ?>
                                </td>

                                <td>
                                    <span class="year-badge">
                                        Year <?= (int)$s['year_of_study'] ?>
                                    </span>
                                </td>

                                <td class="actions">
                                    <a
                                        class="action-link"
                                        href="edit_student.php?id=<?= (int)$s['id'] ?>"
                                    >
                                        Edit
                                    </a>

                                    <form
                                        method="post"
                                        action="delete_student.php"
                                        onsubmit="return confirm('Are you sure you want to delete this student record?');"
                                    >
                                        <input
                                            type="hidden"
                                            name="csrf_token"
                                            value="<?= e(csrf_token()) ?>"
                                        >

                                        <input
                                            type="hidden"
                                            name="id"
                                            value="<?= (int)$s['id'] ?>"
                                        >

                                        <button
                                            class="action-link danger-link"
                                            type="submit"
                                        >
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>

        <div class="card-footer">
            Student Portal &middot; Secure student record management
        </div>

    </section>

    <footer class="footer">
        Student Portal &copy; <?= date('Y') ?>.
        Student Registration Management System.
    </footer>

</main>

</body>
</html>
