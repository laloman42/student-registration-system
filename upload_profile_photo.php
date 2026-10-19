<?php
require_once "config.php";

if (!isset($_SESSION['student_id'])) {
    redirect('student_login.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    if (
        !isset($_FILES['profile_photo']) ||
        $_FILES['profile_photo']['error'] !== UPLOAD_ERR_OK
    ) {
        $error = 'Please select a photo.';
    } elseif ($_FILES['profile_photo']['size'] > 2 * 1024 * 1024) {
        $error = 'Photo must be 2 MB or smaller.';
    } else {
        $tmp = $_FILES['profile_photo']['tmp_name'];
        $imageInfo = @getimagesize($tmp);
        $mime = $imageInfo['mime'] ?? '';

        $allowed = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp'
        ];

        if (!$imageInfo || !isset($allowed[$mime])) {
            $error = 'Only JPG, PNG, and WEBP photos are allowed.';
        } else {
            $folder = __DIR__ . '/uploads/profile_photos/';

            if (!is_dir($folder)) {
                mkdir($folder, 0755, true);
            }

            $filename = bin2hex(random_bytes(16))
                      . '.' . $allowed[$mime];

            if (move_uploaded_file($tmp, $folder . $filename)) {
                $photo = 'uploads/profile_photos/' . $filename;

                $stmt = $pdo->prepare(
                    "UPDATE students SET profile_photo = ?
                     WHERE id = ?"
                );

                $stmt->execute([
                    $photo,
                    $_SESSION['student_id']
                ]);

                redirect('student_profile.php');
            } else {
                $error = 'Photo upload failed. Please try again.';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Upload Profile Photo</title>
    <style>
        body {
            font-family: Arial;
            background: #f1f5f9;
            padding: 30px;
        }

        .container {
            max-width: 500px;
            margin: auto;
            padding: 25px;
            background: white;
            border-radius: 12px;
        }

        h2 {
            text-align: center;
            color: #1e3a8a;
        }

        input {
            margin: 20px 0;
        }

        button {
            padding: 12px;
            background: #2563eb;
            color: white;
            border: none;
            cursor: pointer;
        }

        .error {
            color: red;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Upload Profile Photo</h2>

    <?php if ($error !== ''): ?>
        <p class="error"><?= e($error) ?></p>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token"
               value="<?= e(csrf_token()) ?>">

        <label>Choose Photo (JPG, PNG, WEBP; maximum 2 MB)</label>
        <br>
        <input type="file" name="profile_photo"
               accept=".jpg,.jpeg,.png,.webp" required>
        <br>
        <button type="submit">Upload Photo</button>
    </form>

    <p>
        <a href="student_profile.php">Back to My Profile</a>
    </p>
</div>
</body>
</html>