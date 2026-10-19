<?php
require_once "config.php";
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed.');
}
verify_csrf();
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if ($id) {
    $stmt = $pdo->prepare('DELETE FROM students WHERE id = ?');
    $stmt->execute([$id]);
}
redirect('students.php');
