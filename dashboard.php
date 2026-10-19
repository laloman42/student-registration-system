

<?php
require_once "config.php";
require_admin();

// Dashboard statistics
$total = (int)$pdo->query(
    "SELECT COUNT(*) FROM students"
)->fetchColumn();

$departments = (int)$pdo->query(
    "SELECT COUNT(DISTINCT department)
     FROM students
     WHERE department IS NOT NULL AND department <> ''"
)->fetchColumn();

// Five latest students
$recent = $pdo->query(
    "SELECT id, student_id, first_name, last_name,
            department, year_of_study, created_at
     FROM students
     ORDER BY created_at DESC, id DESC
     LIMIT 5"
)->fetchAll();

// Real department summary from the database
$departmentSummary = $pdo->query(
    "SELECT department, COUNT(*) AS total
     FROM students
     WHERE department IS NOT NULL AND department <> ''
     GROUP BY department
     ORDER BY total DESC, department ASC"
)->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Dashboard | Student Portal</title>

<style>
* {
    box-sizing: border-box;
}

:root {
    --blue: #2563eb;
    --navy: #142b4d;
    --text: #192b47;
    --muted: #718096;
    --border: #e4eaf3;
    --background: #f4f7fc;
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

.layout {
    display: flex;
    min-height: 100vh;
}

.sidebar {
    width: 245px;
    flex-shrink: 0;
    padding: 27px 17px;
    background: linear-gradient(180deg, #17365e, #102541);
    color: white;
}

.brand {
    display: flex;
    align-items: center;
    gap: 12px;
    color: white;
    font-size: 19px;
    font-weight: 800;
    margin: 0 0 38px;
}

.brand-icon {
    display: grid;
    place-items: center;
    width: 43px;
    height: 43px;
    background: var(--blue);
    border-radius: 12px;
    font-size: 22px;
}

.brand small {
    display: block;
    margin-top: 5px;
    font-size: 11px;
    font-weight: 400;
    color: #c7d7ee;
}

.nav-label {
    margin: 0 10px 13px;
    color: #a9bfdc;
    font-size: 11px;
    letter-spacing: 1.5px;
}

.nav-link {
    display: block;
    color: #e0eafb;
    padding: 13px 14px;
    border-radius: 9px;
    margin-bottom: 7px;
    font-size: 14px;
    font-weight: 600;
    transition: 0.2s;
}

.nav-link:hover,
.nav-link.active {
    color: white;
    background: #2563eb;
}

.sidebar-note {
    margin-top: 45px;
    padding: 15px;
    border: 1px solid #3b5474;
    border-radius: 12px;
    color: #d6e4f8;
    font-size: 12px;
    line-height: 1.7;
}

.main {
    flex: 1;
    min-width: 0;
}

.topbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 18px;
    padding: 17px 4%;
    background: white;
    border-bottom: 1px solid var(--border);
}

.search-box {
    width: min(500px, 100%);
}

.search-box input {
    width: 100%;
    padding: 13px 15px;
    border: 1px solid #dce5f1;
    border-radius: 9px;
    font-size: 14px;
    outline: none;
}

.search-box input:focus {
    border-color: var(--blue);
    box-shadow: 0 0 0 3px #2563eb18;
}

.admin-label {
    color: var(--text);
    font-size: 13px;
    font-weight: 700;
    white-space: nowrap;
}

.content {
    width: 92%;
    max-width: 1400px;
    margin: 30px auto;
}

.welcome {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 20px;
    margin-bottom: 25px;
}

.eyebrow {
    color: var(--blue);
    font-size: 11px;
    letter-spacing: 1.7px;
    font-weight: 800;
    margin-bottom: 10px;
}

h1 {
    margin: 0 0 10px;
    font-size: clamp(25px, 3vw, 34px);
}

.muted {
    color: var(--muted);
    font-size: 14px;
    line-height: 1.6;
    margin: 0;
}

.btn {
    display: inline-block;
    border: none;
    border-radius: 9px;
    padding: 13px 17px;
    background: var(--blue);
    color: white;
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
    transition: 0.2s;
}

.btn:hover {
    background: #1d4ed8;
    transform: translateY(-1px);
}

.stats {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 18px;
    margin-bottom: 24px;
}

.stat-card,
.panel {
    background: white;
    border: 1px solid var(--border);
    border-radius: 14px;
    box-shadow: 0 5px 20px #19375b08;
}

.stat-card {
    padding: 23px;
}

.stat-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
}

.stat-label {
    color: var(--muted);
    font-size: 13px;
    font-weight: 700;
}

.stat-icon {
    display: grid;
    place-items: center;
    width: 43px;
    height: 43px;
    border-radius: 12px;
    background: #eaf1ff;
    color: var(--blue);
    font-size: 21px;
}

.green {
    background: #e5f8ef;
    color: #09865a;
}

.purple {
    background: #f0eaff;
    color: #7445d8;
}

.stat-number {
    display: block;
    margin: 16px 0 7px;
    font-size: 32px;
    font-weight: 800;
}

.stat-note {
    color: #8794a8;
    font-size: 12px;
}

.quick-actions {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 15px;
    margin-bottom: 24px;
}

.action {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 18px 20px;
    border: 1px solid var(--border);
    background: white;
    color: var(--text);
    border-radius: 12px;
    font-size: 14px;
    font-weight: 700;
    transition: 0.2s;
}

.action:hover {
    border-color: #b7cdfa;
    box-shadow: 0 5px 18px #2563eb12;
    transform: translateY(-2px);
}

.action span:first-child {
    color: var(--blue);
    font-size: 20px;
}

.panels {
    display: grid;
    grid-template-columns: minmax(0, 1.7fr) minmax(240px, 1fr);
    gap: 20px;
}

.panel {
    overflow: hidden;
    min-width: 0;
}

.panel-heading {
    padding: 22px 23px;
    border-bottom: 1px solid #edf1f6;
}

.panel-heading h2 {
    margin: 0 0 7px;
    font-size: 17px;
}

.table-wrap {
    width: 100%;
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
    min-width: 580px;
}

th {
    background: #f8faff;
    color: #77859b;
    text-align: left;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    font-size: 10px;
    padding: 15px 17px;
}

td {
    padding: 16px 17px;
    border-top: 1px solid #edf1f6;
    color: #485970;
    font-size: 12px;
}

tbody tr:hover {
    background: #f9fbff;
}

.student-id {
    color: var(--blue);
    font-weight: 700;
}

.year-badge {
    display: inline-block;
    background: #eaf1ff;
    color: #2457c5;
    padding: 6px 8px;
    border-radius: 20px;
    white-space: nowrap;
    font-size: 11px;
}

.empty {
    text-align: center;
    padding: 25px;
    color: var(--muted);
}

.dept-list {
    padding: 7px 22px 20px;
}

.dept-item {
    padding: 14px 0;
    border-bottom: 1px solid #edf1f6;
}

.dept-item:last-child {
    border-bottom: none;
}

.dept-top {
    display: flex;
    justify-content: space-between;
    gap: 10px;
    margin-bottom: 10px;
    font-size: 12px;
}

.dept-name {
    font-weight: 700;
    overflow-wrap: anywhere;
}

.dept-count {
    color: var(--blue);
    font-weight: 800;
    white-space: nowrap;
}

.progress {
    height: 7px;
    overflow: hidden;
    background: #edf2f8;
    border-radius: 10px;
}

.progress-bar {
    height: 100%;
    background: linear-gradient(90deg, #2563eb, #60a5fa);
    border-radius: 10px;
}

.panel-footer {
    padding: 0 22px 22px;
}

.text-link {
    color: var(--blue);
    font-size: 12px;
    font-weight: 700;
}

.footer {
    text-align: center;
    color: #8a97aa;
    font-size: 12px;
    padding: 27px 10px;
}

@media (max-width: 1050px) {
    .panels {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 760px) {
    .layout {
        display: block;
    }

    .sidebar {
        width: 100%;
        padding: 18px;
    }

    .brand {
        margin-bottom: 18px;
    }

    .nav-label,
    .sidebar-note {
        display: none;
    }

    .nav-link {
        display: inline-block;
        margin: 0 3px 5px 0;
        padding: 10px;
        font-size: 12px;
    }

    .topbar {
        padding: 14px 4%;
    }

    .content {
        width: 92%;
        margin: 23px auto;
    }

    .stats {
        grid-template-columns: 1fr;
        gap: 13px;
    }

    .stat-card {
        padding: 20px;
    }

    .quick-actions {
        grid-template-columns: 1fr;
    }

    .welcome .btn {
        text-align: center;
    }
}
</style>
</head>

<body>
<div class="layout">

    <aside class="sidebar">
        <a class="brand" href="dashboard.php">
            <span class="brand-icon">S</span>
            <span>
                Student Portal
                <small>Registration Management</small>
            </span>
        </a>

        <div class="nav-label">MAIN MENU</div>

        <a class="nav-link active" href="dashboard.php">
            ◈ &nbsp; Dashboard
        </a>

        <a class="nav-link" href="students.php">
            ♙ &nbsp; Students
        </a>

        <a class="nav-link" href="register.php">
            + &nbsp; Register Student
        </a>

        <div class="sidebar-note">
            <strong>Administrator Panel</strong><br>
            Manage student records securely from one place.
        </div>

        <a class="nav-link" href="logout.php" style="margin-top:22px">
            ↪ &nbsp; Log Out
        </a>
    </aside>

    <div class="main">

        <header class="topbar">
            <div class="search-box">
                <input
                    type="search"
                    id="studentSearch"
                    placeholder="Search recent students by name, ID or department..."
                    aria-label="Search recent students"
                >
            </div>

            <div class="admin-label">
                Admin: <?= e($_SESSION['admin_username'] ?? 'Admin') ?>
            </div>
        </header>

        <main class="content">

            <section class="welcome">
                <div>
                    <div class="eyebrow">ADMIN DASHBOARD</div>
                    <h1>
                        Welcome, <?= e($_SESSION['admin_username'] ?? 'Admin') ?>!
                    </h1>
                    <p class="muted">
                        Manage your student records from one simple dashboard.
                    </p>
                </div>

                <a class="btn" href="register.php">
                    + Register Student
                </a>
            </section>

            <section class="stats">

                <article class="stat-card">
                    <div class="stat-top">
                        <span class="stat-label">Total Students</span>
                        <span class="stat-icon">♙</span>
                    </div>
                    <strong class="stat-number"><?= $total ?></strong>
                    <span class="stat-note">Registered student records</span>
                </article>

                <article class="stat-card">
                    <div class="stat-top">
                        <span class="stat-label">Departments</span>
                        <span class="stat-icon green">▤</span>
                    </div>
                    <strong class="stat-number"><?= $departments ?></strong>
                    <span class="stat-note">Departments in your records</span>
                </article>

                <article class="stat-card">
                    <div class="stat-top">
                        <span class="stat-label">Admin Account</span>
                        <span class="stat-icon purple">✓</span>
                    </div>
                    <strong class="stat-number" style="color:#09865a">
                        Active
                    </strong>
                    <span class="stat-note">Administrator session</span>
                </article>

            </section>

            <section class="quick-actions">
                <a class="action" href="register.php">
                    <span>＋</span>
                    <span style="flex:1">Register New Student</span>
                    <span>→</span>
                </a>

                <a class="action" href="students.php">
                    <span>♙</span>
                    <span style="flex:1">View All Students</span>
                    <span>→</span>
                </a>
            </section>

            <section class="panels">

                <article class="panel">
                    <div class="panel-heading">
                        <h2>Recently Registered Students</h2>
                        <p class="muted">
                            The five latest student records.
                        </p>
                    </div>

                    <div class="table-wrap">
                        <table id="studentsTable">
                            <thead>
                                <tr>
                                    <th>Student ID</th>
                                    <th>Full Name</th>
                                    <th>Department</th>
                                    <th>Year</th>
                                </tr>
                            </thead>

                            <tbody>
                            <?php if (!$recent): ?>
                                <tr>
                                    <td colspan="4" class="empty">
                                        No students registered yet.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($recent as $s): ?>
                                    <tr>
                                        <td class="student-id">
                                            <?= e($s['student_id']) ?>
                                        </td>

                                        <td>
                                            <?= e($s['first_name'] . ' ' . $s['last_name']) ?>
                                        </td>

                                        <td>
                                            <?= e($s['department'] ?? '') ?>
                                        </td>

                                        <td>
                                            <span class="year-badge">
                                                Year <?= (int)$s['year_of_study'] ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="panel-footer" style="padding-top:18px">
                        <a class="text-link" href="students.php">
                            View all students →
                        </a>
                    </div>
                </article>

                <article class="panel">
                    <div class="panel-heading">
                        <h2>Department Summary</h2>
                        <p class="muted">
                            Students grouped by department.
                        </p>
                    </div>

                    <div class="dept-list">
                    <?php if (!$departmentSummary): ?>
                        <p class="muted">
                            No department records available yet.
                        </p>
                    <?php else: ?>
                        <?php foreach ($departmentSummary as $dept): ?>
                            <?php
                            $percentage = $total > 0
                                ? round(((int)$dept['total'] / $total) * 100)
                                : 0;
                            ?>
                            <div class="dept-item">
                                <div class="dept-top">
                                    <span class="dept-name">
                                        <?= e($dept['department']) ?>
                                    </span>
                                    <span class="dept-count">
                                        <?= (int)$dept['total'] ?>
                                    </span>
                                </div>

                                <div
                                    class="progress"
                                    role="progressbar"
                                    aria-valuenow="<?= $percentage ?>"
                                    aria-valuemin="0"
                                    aria-valuemax="100"
                                    aria-label="<?= e($dept['department']) ?>"
                                >
                                    <div
                                        class="progress-bar"
                                        style="width:<?= $percentage ?>%"
                                    ></div>
                                </div>

                                <p class="muted" style="font-size:11px;margin-top:6px">
                                    <?= $percentage ?>% of all students
                                </p>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </div>

                    <div class="panel-footer">
                        <a class="text-link" href="students.php">
                            View student records →
                        </a>
                    </div>
                </article>

            </section>

            <footer class="footer">
                Student Portal &copy; <?= date('Y') ?>.
                Student Registration Management System.
            </footer>

        </main>
    </div>
</div>

<script>
// Search the five recent student rows shown on this dashboard.
const searchInput = document.getElementById('studentSearch');
const studentRows = document.querySelectorAll('#studentsTable tbody tr');

searchInput.addEventListener('input', function () {
    const searchTerm = this.value.toLowerCase().trim();

    studentRows.forEach(function (row) {
        const rowText = row.textContent.toLowerCase();
        row.style.display = rowText.includes(searchTerm) ? '' : 'none';
    });
});
</script>

</body>
</html>
