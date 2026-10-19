
<?php
require_once "config.php";
require_admin();

/* Count all registered students */
$totalStudents = (int) $pdo->query(
    "SELECT COUNT(*) FROM students"
)->fetchColumn();

/* Get department totals */
$stmt = $pdo->query(
    "SELECT department, COUNT(*) AS total
     FROM students
     WHERE department IS NOT NULL
       AND department <> ''
     GROUP BY department
     ORDER BY department"
);

$departments = $stmt->fetchAll(PDO::FETCH_ASSOC);
$departmentCount = count($departments);

/* Students registered in the last 7 days */
$newStudents = (int) $pdo->query(
    "SELECT COUNT(*)
     FROM students
     WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)"
)->fetchColumn();

/* Five most recent student records */
$stmt = $pdo->query(
    "SELECT student_id, first_name, last_name,
            email, department, created_at
     FROM students
     ORDER BY created_at DESC
     LIMIT 5"
);

$latestStudents = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Student Registration System</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

<div class="dashboard-layout">

    <!-- Sidebar -->
    <aside class="admin-sidebar">
        <a class="admin-brand" href="admin_dashboard.php">
            <span class="admin-brand-mark">S</span>
            <span>Student Admin</span>
        </a>

        <p class="sidebar-label">MAIN MENU</p>

        <nav class="admin-navigation">
            <a class="admin-nav-link active"
               href="admin_dashboard.php">
                <span>🏠</span> Dashboard
            </a>

            <a class="admin-nav-link" href="register.php">
                <span>➕</span> Register Student
            </a>

            <a class="admin-nav-link" href="view_all_students.php">
                <span>👨‍🎓</span> View All Students
            </a>

            <a class="admin-nav-link" href="search_students.php">
                <span>🔎</span> Search Students
            </a>

            <a class="admin-nav-link" href="admin_edit_student.php">
                <span>✏️</span> Edit Student
            </a>

            <a class="admin-nav-link" href="admin_delete_student.php">
                <span>🗑️</span> Delete Student
            </a>
        </nav>

        <div class="admin-sidebar-bottom">
            <a class="admin-nav-link logout"
               href="admin_logout.php">
                <span>🚪</span> Logout
            </a>
        </div>
    </aside>

    <!-- Main content -->
    <div class="dashboard-main">

        <header class="admin-header">
            <div>
                <p class="eyebrow">STUDENT MANAGEMENT</p>
                <h1>Admin Dashboard</h1>
                <p>Welcome to your Student Registration System.</p>
            </div>

            <a class="btn" href="register.php">
                + Register Student
            </a>
        </header>

        <main class="dashboard">

            <!-- Welcome summary -->
            <section class="dashboard-welcome">
                <div>
                    <p class="dashboard-welcome-label">
                        SYSTEM OVERVIEW
                    </p>
                    <h2>Manage your students in one place</h2>
                    <p>
                        Review registrations, explore departments,
                        and manage student records.
                    </p>
                </div>

                <div class="welcome-total">
                    <span>Total Students</span>
                    <strong><?= $totalStudents ?></strong>
                </div>
            </section>

            <!-- Statistics -->
            <section class="stats-grid">

                <article class="stat-card">
                    <div class="stat-top">
                        <span class="stat-icon blue-icon">👨‍🎓</span>
                        <span class="stat-caption">All records</span>
                    </div>

                    <span class="stat-label">Total Students</span>
                    <strong><?= $totalStudents ?></strong>
                    <p class="stat-description">
                        Students registered in the system
                    </p>
                </article>

                <article class="stat-card">
                    <div class="stat-top">
                        <span class="stat-icon purple-icon">🏢</span>
                        <span class="stat-caption">Active records</span>
                    </div>

                    <span class="stat-label">Departments</span>
                    <strong><?= $departmentCount ?></strong>
                    <p class="stat-description">
                        Departments with registered students
                    </p>
                </article>

                <article class="stat-card">
                    <div class="stat-top">
                        <span class="stat-icon green-icon">🆕</span>
                        <span class="stat-caption">Last 7 days</span>
                    </div>

                    <span class="stat-label">New Students</span>
                    <strong><?= $newStudents ?></strong>
                    <p class="stat-description">
                        Recently registered students
                    </p>
                </article>

            </section>

            <!-- Quick actions -->
            <section class="dashboard-section">
                <div class="section-heading">
                    <div>
                        <h2>Quick Actions</h2>
                        <p>Choose a task to manage student records.</p>
                    </div>
                </div>

                <div class="admin-quick-actions">
                    <a class="quick-action" href="register.php">
                        <span class="quick-action-icon">➕</span>
                        <span>
                            <strong>Register Student</strong>
                            <small>Add a new student record</small>
                        </span>
                        <span class="quick-arrow">→</span>
                    </a>

                    <a class="quick-action" href="view_all_students.php">
                        <span class="quick-action-icon">👨‍🎓</span>
                        <span>
                            <strong>View Students</strong>
                            <small>Browse all student records</small>
                        </span>
                        <span class="quick-arrow">→</span>
                    </a>

                    <a class="quick-action" href="search_students.php">
                        <span class="quick-action-icon">🔎</span>
                        <span>
                            <strong>Search Students</strong>
                            <small>Find a student quickly</small>
                        </span>
                        <span class="quick-arrow">→</span>
                    </a>

                    <a class="quick-action" href="admin_edit_student.php">
                        <span class="quick-action-icon">✏️</span>
                        <span>
                            <strong>Edit Student</strong>
                            <small>Update student information</small>
                        </span>
                        <span class="quick-arrow">→</span>
                    </a>
                </div>
            </section>

            <!-- Department table -->
            <section class="card table-card dashboard-section">
                <div class="section-heading">
                    <div>
                        <h2>Students by Department</h2>
                        <p>Distribution of registered students.</p>
                    </div>
                </div>

                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Department</th>
                                <th>Total Students</th>
                            </tr>
                        </thead>

                        <tbody>
                        <?php if (!empty($departments)): ?>
                            <?php foreach ($departments as $department): ?>
                                <tr>
                                    <td>
                                        <?= e($department['department']) ?>
                                    </td>
                                    <td>
                                        <?= (int) $department['total'] ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="2" class="empty">
                                    No departments found.
                                </td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Recent students table -->
            <section class="card table-card dashboard-section">
                <div class="section-heading">
                    <div>
                        <h2>Recently Registered Students</h2>
                        <p>The five most recently registered records.</p>
                    </div>

                    <a href="view_all_students.php">
                        View All Students →
                    </a>
                </div>

                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Student ID</th>
                                <th>Full Name</th>
                                <th>Email</th>
                                <th>Department</th>
                                <th>Registered Date</th>
                            </tr>
                        </thead>

                        <tbody>
                        <?php if (!empty($latestStudents)): ?>
                            <?php foreach ($latestStudents as $student): ?>
                                <tr>
                                    <td>
                                        <?= e($student['student_id'] ?? '') ?>
                                    </td>
                                    <td>
                                        <?= e(
                                            ($student['first_name'] ?? '') .
                                            ' ' .
                                            ($student['last_name'] ?? '')
                                        ) ?>
                                    </td>
                                    <td>
                                        <?= e($student['email'] ?? '') ?>
                                    </td>
                                    <td>
                                        <?= e($student['department'] ?? '') ?>
                                    </td>
                                    <td>
                                        <?= e($student['created_at'] ?? '') ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="empty">
                                    No students registered yet.
                                </td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <footer class="admin-footer">
                Student Registration System
                &copy; <?= date('Y') ?>
            </footer>

        </main>
    </div>
</div>

</body>
</html>
