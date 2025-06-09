<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user = $_SESSION['user'];
$success = $error = "";

// Image directory
$uploadDir = "uploads/";
$defaultImage = "default.png"; // Make sure this exists in the uploads folder

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $imgTmp = $_FILES['image']['tmp_name'];
        $imgName = basename($_FILES['image']['name']);
        $imgExt = strtolower(pathinfo($imgName, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];

        if (!in_array($imgExt, $allowed)) {
            $error = "Only JPG, JPEG, PNG, GIF images allowed.";
        } else {
            $newImgName = uniqid('profile_', true) . '.' . $imgExt;
            $uploadPath = $uploadDir . $newImgName;

            if (move_uploaded_file($imgTmp, $uploadPath)) {
                // Delete old image if not default
                if ($user['image'] && file_exists($uploadDir . $user['image']) && $user['image'] !== $defaultImage) {
                    unlink($uploadDir . $user['image']);
                }
                $user['image'] = $newImgName;
            } else {
                $error = "Failed to upload image.";
            }
        }
    }

    // If no error, update DB
    if (!$error) {
        $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, image = ? WHERE id = ?");
        $stmt->bind_param("sssi", $name, $email, $user['image'], $user['id']);
        if ($stmt->execute()) {
            $success = "Profile updated successfully.";
            $_SESSION['user']['name'] = $name;
            $_SESSION['user']['email'] = $email;
            $_SESSION['user']['image'] = $user['image'];
        } else {
            $error = "Database update failed.";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Edit Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
</head>
<body class="bg-light">
<div class="container mt-5" style="max-width: 500px;">
    <h2 class="mb-4 text-center">Edit Profile</h2>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php elseif ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php
    $imgFile = $_SESSION['user']['image'];
    $imgPath = $uploadDir . $imgFile;
    $imgToShow = (file_exists($imgPath) && $imgFile) ? $imgPath : $uploadDir . $defaultImage;
    ?>

    <form method="POST" enctype="multipart/form-data">
        <div class="mb-3 text-center">
            <img src="<?= $imgToShow ?>" alt="Profile Image" class="rounded-circle" style="width: 120px; height: 120px; object-fit: cover; border: 2px solid #333;">
        </div>

        <div class="mb-3">
            <label for="image" class="form-label">Change Profile Image</label>
            <input type="file" name="image" id="image" class="form-control" accept="image/jpeg,image/png,image/gif,image/jpg" />
        </div>

        <div class="mb-3">
            <label for="name" class="form-label">Full Name</label>
            <input type="text" name="name" id="name" value="<?= htmlspecialchars($user['name']) ?>" class="form-control" required />
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Email Address</label>
            <input type="email" name="email" id="email" value="<?= htmlspecialchars($user['email']) ?>" class="form-control" required />
        </div>

        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-save me-2"></i>Save Changes</button>
        <a href="dashboard.php" class="btn btn-secondary w-100 mt-2"><i class="fas fa-arrow-left me-2"></i>Back to Dashboard</a>
    </form>
</div>
</body>
</html>
