
<?php
require_once "config.php";

// Clear the current session.
$_SESSION = [];

if (session_status() === PHP_SESSION_ACTIVE) {
    session_destroy();
}

// Prevent caching of the old page.
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

// Return to admin login.
header("Location: admin_login.php");
exit;
?>
