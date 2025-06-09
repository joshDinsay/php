<?php
session_start();
include 'db.php';

if (isset($_POST['register'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // File upload
    $imageName = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $targetDir = "uploads/";
        $imageName = time() . "_" . basename($_FILES['image']['name']);
        $targetFile = $targetDir . $imageName;
        $imageType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($imageType, $allowed)) {
            if ($_FILES['image']['size'] <= 2 * 1024 * 1024) { // 2MB limit
                move_uploaded_file($_FILES['image']['tmp_name'], $targetFile);
            } else {
                $error = "Image is too large (max 2MB).";
            }
        } else {
            $error = "Only JPG, JPEG, PNG, and GIF are allowed.";
        }
    }

    // Check for duplicate email
    if (!isset($error)) {
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res->num_rows > 0) {
            $error = "Email is already registered.";
        } else {
            $stmt = $conn->prepare("INSERT INTO users (name, email, password, image) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name, $email, $hashedPassword, $imageName);
            if ($stmt->execute()) {
                $success = "Registered successfully! You can now <a href='login.php'>login</a>.";
            } else {
                $error = "Registration failed. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Register</title>
    <meta charset="UTF-8">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="card shadow-sm w-100 w-md-50 mx-auto">
        <div class="card-body p-4">
            <h3 class="card-title text-center mb-4">
                <i class="fas fa-user-plus text-primary me-2"></i>Create Account
            </h3>
            <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
            <?php if (isset($success)) echo "<div class='alert alert-success'>$success</div>"; ?>

            <form method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label class="form-label"><i class="fas fa-user me-1"></i>Full Name</label>
                    <input name="name" type="text" class="form-control" required />
                </div>
                <div class="mb-3">
                    <label class="form-label"><i class="fas fa-envelope me-1"></i>Email</label>
                    <input name="email" type="email" class="form-control" required />
                </div>
                <div class="mb-3">
                    <label class="form-label"><i class="fas fa-lock me-1"></i>Password</label>
                    <input name="password" type="password" class="form-control" required />
                </div>
                <div class="mb-3">
                    <label class="form-label"><i class="fas fa-image me-1"></i>Profile Image</label>
                    <input name="image" type="file" class="form-control" accept="image/*" />
                    <small class="text-muted">Max 2MB | JPG, JPEG, PNG, GIF</small>
                </div>
                <button name="register" class="btn btn-primary w-100">
                    <i class="fas fa-user-plus me-1"></i>Register
                </button>
                <p class="mt-3 text-center">Already have an account? <a href="login.php">Login here</a></p>
            </form>
        </div>
    </div>
</div>
</body>
</html>
