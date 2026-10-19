
<?php
require_once "config.php";

$totalStudents = 0;
$totalDepartments = 0;

try {
    $totalStudents = (int) $pdo
        ->query("SELECT COUNT(*) FROM students")
        ->fetchColumn();

    $totalDepartments = (int) $pdo
        ->query("SELECT COUNT(DISTINCT department)
                 FROM students
                 WHERE department IS NOT NULL
                 AND TRIM(department) <> ''")
        ->fetchColumn();
} catch (PDOException $e) {
    error_log("Home page statistics error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Portal | Student Registration System</title>
    <meta name="description" content="Student Registration System for student registration and academic record management.">

    <style>
        :root {
            --navy: #102344;
            --blue: #1558c0;
            --light-blue: #edf4ff;
            --text: #24344d;
            --muted: #68778c;
            --white: #ffffff;
            --border: #e3eaf4;
            --shadow: 0 12px 35px rgba(16,35,68,.08);
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        html { scroll-behavior: smooth; }

        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #fff;
            color: var(--text);
            line-height: 1.7;
        }

        a { text-decoration: none; }

        .navbar {
            min-height: 78px;
            padding: 16px 7%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            background: #fff;
            border-bottom: 1px solid var(--border);
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--navy);
            font-size: 21px;
            font-weight: 800;
        }

        .logo-icon {
            width: 43px;
            height: 43px;
            display: grid;
            place-items: center;
            border-radius: 13px;
            background: var(--light-blue);
            font-size: 23px;
        }

        .nav-links {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 20px;
            flex-wrap: wrap;
        }

        .nav-links a {
            color: #45546b;
            font-size: 14px;
            font-weight: 700;
            transition: color .2s ease;
        }

        .nav-links a:hover { color: var(--blue); }

        .nav-links .nav-button {
            padding: 11px 17px;
            background: var(--blue);
            color: white;
            border-radius: 9px;
        }

        .nav-links .nav-button:hover {
            background: #10469c;
            color: white;
        }

        .hero {
            padding: 80px 7%;
            background:
                radial-gradient(circle at 90% 10%, #dceaff 0, transparent 32%),
                linear-gradient(135deg, #f6f9ff, #fff 70%);
        }

        .hero-content {
            max-width: 1200px;
            margin: auto;
            display: grid;
            grid-template-columns: 1.2fr .8fr;
            align-items: center;
            gap: 55px;
        }

        .eyebrow {
            display: inline-block;
            margin-bottom: 18px;
            padding: 7px 12px;
            background: #e2edff;
            color: #174ea6;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 1px;
        }

        h1 {
            max-width: 700px;
            margin-bottom: 20px;
            color: var(--navy);
            font-size: clamp(35px, 4.5vw, 55px);
            line-height: 1.15;
            letter-spacing: -1.4px;
        }

        .hero-description {
            max-width: 610px;
            margin-bottom: 28px;
            color: var(--muted);
            font-size: 17px;
        }

        .hero-buttons {
            display: flex;
            align-items: center;
            gap: 13px;
            flex-wrap: wrap;
        }

        .button {
            display: inline-flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
            padding: 13px 20px;
            border: 1px solid transparent;
            border-radius: 9px;
            font-size: 14px;
            font-weight: 700;
            transition: transform .2s ease, background .2s ease;
        }

        .button:hover { transform: translateY(-2px); }

        .button-primary {
            background: var(--blue);
            color: white;
        }

        .button-primary:hover { background: #10469c; }

        .button-secondary {
            background: white;
            color: var(--navy);
            border-color: #d4deec;
        }

        .button-secondary:hover { background: var(--light-blue); }

        .hero-card {
            padding: 34px;
            border: 1px solid #e2eaf6;
            border-radius: 22px;
            background: rgba(255,255,255,.95);
            box-shadow: var(--shadow);
        }

        .hero-icon {
            width: 68px;
            height: 68px;
            margin-bottom: 22px;
            display: grid;
            place-items: center;
            border-radius: 18px;
            background: var(--light-blue);
            font-size: 34px;
        }

        .hero-card h2 {
            margin-bottom: 12px;
            color: var(--navy);
            font-size: 25px;
            line-height: 1.3;
        }

        .hero-card p { color: var(--muted); font-size: 15px; }

        .mini-feature {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 18px;
            color: #344760;
            font-size: 14px;
            font-weight: 700;
        }

        .mini-check { color: #16794b; font-size: 19px; }

        .section {
            max-width: 1200px;
            margin: auto;
            padding: 75px 7%;
        }

        .section-heading {
            max-width: 700px;
            margin: 0 auto 38px;
            text-align: center;
        }

        .section-heading h2 {
            margin-bottom: 10px;
            color: var(--navy);
            font-size: clamp(27px, 3vw, 36px);
            line-height: 1.3;
        }

        .section-heading p { color: var(--muted); font-size: 15px; }

        .stats {
            display: grid;
            grid-template-columns: repeat(2, minmax(0,1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            display: flex;
            align-items: center;
            gap: 18px;
            padding: 25px;
            border: 1px solid var(--border);
            border-radius: 16px;
            background: white;
            box-shadow: 0 6px 22px rgba(16,35,68,.04);
        }

        .stat-icon {
            flex: 0 0 56px;
            width: 56px;
            height: 56px;
            display: grid;
            place-items: center;
            border-radius: 15px;
            background: var(--light-blue);
            font-size: 27px;
        }

        .stat-number {
            color: var(--navy);
            font-size: 30px;
            font-weight: 800;
            line-height: 1.25;
        }

        .stat-label { color: var(--muted); font-size: 14px; }

        .services {
            display: grid;
            grid-template-columns: repeat(3, minmax(0,1fr));
            gap: 22px;
        }

        .service-card {
            padding: 28px;
            border: 1px solid var(--border);
            border-radius: 16px;
            background: white;
            box-shadow: 0 5px 20px rgba(16,35,68,.04);
            transition: transform .25s ease, box-shadow .25s ease;
        }

        .service-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow);
        }

        .service-icon {
            width: 57px;
            height: 57px;
            margin-bottom: 20px;
            display: grid;
            place-items: center;
            border-radius: 15px;
            background: var(--light-blue);
            font-size: 27px;
        }

        .service-card h3 {
            margin-bottom: 10px;
            color: var(--navy);
            font-size: 19px;
        }

        .service-card p {
            min-height: 83px;
            margin-bottom: 18px;
            color: var(--muted);
            font-size: 14px;
        }

        .service-card a {
            color: var(--blue);
            font-size: 14px;
            font-weight: 800;
        }

        .service-card a:hover { text-decoration: underline; }

        .about-section {
            padding: 72px 7%;
            background: #f5f8fe;
        }

        .about-content {
            max-width: 1050px;
            margin: auto;
            display: grid;
            grid-template-columns: .8fr 1.2fr;
            align-items: center;
            gap: 45px;
        }

        .about-illustration {
            min-height: 250px;
            display: grid;
            place-items: center;
            border: 1px solid #e1eafb;
            border-radius: 22px;
            background: white;
            font-size: 85px;
        }

        .about-text h2 {
            margin-bottom: 15px;
            color: var(--navy);
            font-size: 31px;
            line-height: 1.3;
        }

        .about-text p {
            margin-bottom: 15px;
            color: var(--muted);
            font-size: 15px;
        }

        .about-list {
            display: grid;
            gap: 9px;
            margin-top: 18px;
            list-style: none;
        }

        .about-list li {
            color: #344760;
            font-size: 14px;
            font-weight: 700;
        }

        .about-list li span { margin-right: 9px; color: #16794b; }

        .cta {
            margin: 70px 7%;
            padding: 45px 25px;
            border-radius: 22px;
            background: var(--navy);
            color: white;
            text-align: center;
        }

        .cta h2 { margin-bottom: 12px; font-size: 31px; }

        .cta p {
            margin-bottom: 23px;
            color: #d6e2f5;
            font-size: 15px;
        }

        .cta .button { background: white; color: var(--navy); }
        .cta .button:hover { background: #e9f1ff; }

        footer {
            padding: 30px 7%;
            background: #0b1930;
            color: #d4dfef;
            text-align: center;
        }

        footer p { font-size: 13px; }
        footer .footer-note { margin-top: 6px; color: #aabbd4; }

        a:focus-visible {
            outline: 3px solid #77a8ff;
            outline-offset: 4px;
        }

        @media (max-width: 850px) {
            .navbar { flex-direction: column; }
            .nav-links { gap: 15px; }
            .hero-content, .about-content { grid-template-columns: 1fr; }
            .hero { padding-top: 55px; padding-bottom: 55px; }
            .services { grid-template-columns: repeat(2, minmax(0,1fr)); }
            .about-illustration { min-height: 190px; }
        }

        @media (max-width: 560px) {
            .navbar { padding: 16px 5%; }
            .nav-links { gap: 12px; }
            .nav-links a { font-size: 13px; }
            .hero, .section, .about-section {
                padding-left: 5%;
                padding-right: 5%;
            }
            .hero-buttons { align-items: stretch; flex-direction: column; }
            .hero-card { padding: 25px; }
            .stats, .services { grid-template-columns: 1fr; }
            .stat-card { padding: 20px; }
            .service-card p { min-height: auto; }
            .cta { margin: 45px 5%; padding: 35px 20px; }
        }

        @media (prefers-reduced-motion: reduce) {
            html { scroll-behavior: auto; }
            *, *::before, *::after { transition: none !important; }
        }
    </style>
</head>

<body>

<header class="navbar">
    <a class="logo" href="index.php" aria-label="Student Portal Home">
        <span class="logo-icon" aria-hidden="true">🎓</span>
        <span>Student Portal</span>
    </a>

    <nav class="nav-links" aria-label="Main navigation">
        <a href="index.php">Home</a>
        <a href="#services">Services</a>
        <a href="#about">About</a>
        <a href="register.php">Register</a>
        <a class="nav-button" href="admin_login.php">Admin Login →</a>
    </nav>
</header>

<main>

    <section class="hero">
        <div class="hero-content">
            <div>
                <span class="eyebrow">STUDENT REGISTRATION SYSTEM</span>
                <h1>Welcome to Your Student Portal</h1>

                <p class="hero-description">
                    A simple and reliable platform for student registration
                    and academic record management. Register your details
                    and let authorized administrators manage student records
                    in one organized system.
                </p>

                <div class="hero-buttons">
                    <a class="button button-primary" href="register.php">
                        📝 Register as a Student →
                    </a>

                    <a class="button button-secondary" href="student_login.php">
                        🎓 Student Login
                    </a>

                    <a class="button button-secondary" href="admin_login.php">
                        🔐 Admin Login
                    </a>
                </div>
            </div>

            <div class="hero-card">
                <div class="hero-icon" aria-hidden="true">🎓</div>
                <h2>Your Academic Journey Starts Here</h2>
                <p>
                    Register your information, provide your academic details,
                    and keep student records organized in one place.
                </p>

                <div class="mini-feature"><span class="mini-check">✓</span> Simple student registration</div>
                <div class="mini-feature"><span class="mini-check">✓</span> Organized academic information</div>
                <div class="mini-feature"><span class="mini-check">✓</span> Administrator-controlled records</div>
            </div>
        </div>
    </section>

    <section class="section" id="statistics">
        <div class="section-heading">
            <h2>Portal Overview</h2>
            <p>A live overview of student information currently stored in the system database.</p>
        </div>

        <div class="stats">
            <article class="stat-card">
                <div class="stat-icon" aria-hidden="true">👨‍🎓</div>
                <div>
                    <div class="stat-number"><?= number_format($totalStudents) ?></div>
                    <div class="stat-label">Registered Students</div>
                </div>
            </article>

            <article class="stat-card">
                <div class="stat-icon" aria-hidden="true">🏫</div>
                <div>
                    <div class="stat-number"><?= number_format($totalDepartments) ?></div>
                    <div class="stat-label">Departments Represented</div>
                </div>
            </article>
        </div>
    </section>

    <section class="section" id="services">
        <div class="section-heading">
            <h2>Our Portal Services</h2>
            <p>Useful features designed to make student registration and record management easier.</p>
        </div>

        <div class="services">
            <article class="service-card">
                <div class="service-icon" aria-hidden="true">📝</div>
                <h3>Student Registration</h3>
                <p>Submit your student ID, personal details, department, year of study, and account information.</p>
                <a href="register.php">Register now →</a>
            </article>

            <article class="service-card">
                <div class="service-icon" aria-hidden="true">📚</div>
                <h3>Student Records</h3>
                <p>Student records can be viewed and managed by authorized administrators.</p>
                <a href="admin_login.php">Administrator access →</a>
            </article>

            <article class="service-card">
                <div class="service-icon" aria-hidden="true">🔐</div>
                <h3>Secure Administration</h3>
                <p>Authorized administrators can sign in to manage student information and registration records.</p>
                <a href="admin_login.php">Admin sign in →</a>
            </article>
        </div>
    </section>

    <section class="about-section" id="about">
        <div class="about-content">
            <div class="about-illustration" aria-hidden="true">🏛️</div>

            <div class="about-text">
                <span class="eyebrow">ABOUT THE SYSTEM</span>
                <h2>A Better Way to Manage Student Records</h2>

                <p>
                    The Student Registration System is a web-based application
                    designed to organize student registration information in
                    a centralized database.
                </p>

                <p>
                    It provides a registration page for students and a
                    protected administration area for authorized staff to
                    view, update, and delete student records.
                </p>

                <ul class="about-list">
                    <li><span>✓</span> Centralized student information</li>
                    <li><span>✓</span> Easy access to registration forms</li>
                    <li><span>✓</span> Database-based student statistics</li>
                    <li><span>✓</span> Administrator-protected management pages</li>
                </ul>
            </div>
        </div>
    </section>

    <section class="cta">
        <h2>Ready to Register?</h2>
        <p>Start your student registration by providing the required personal and academic information.</p>
        <a class="button" href="register.php">Start Registration →</a>
    </section>

</main>

<footer>
    <p>&copy; <?= date('Y') ?> Student Registration System. All rights reserved.</p>
    <p class="footer-note">Student registration and academic record management portal.</p>
</footer>

</body>
</html>
