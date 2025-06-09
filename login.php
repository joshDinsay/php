<?php
session_start();
include 'db.php';

if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $pass = $_POST['password'];

    $sql = "SELECT * FROM users WHERE email=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 1) {
        $row = $res->fetch_assoc();
        if (password_verify($pass, $row['password'])) {
            $_SESSION['user'] = $row;
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "❌ Invalid credentials.";
        }
    } else {
        $error = "❌ User not found.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Login</title>
    <meta charset="UTF-8">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="card shadow-sm w-100 w-md-50 mx-auto">
        <div class="card-body p-4">
            <h3 class="card-title text-center mb-4">
                <i class="fas fa-user-lock text-success me-2"></i>Login
            </h3>
            <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label"><i class="fas fa-envelope me-1"></i>Email</label>
                    <input name="email" type="email" class="form-control" placeholder="Enter email" required />
                </div>
                <div class="mb-3">
                    <label class="form-label"><i class="fas fa-lock me-1"></i>Password</label>
                    <input name="password" type="password" class="form-control" placeholder="Enter password" required />
                </div>
                <button name="login" class="btn btn-success w-100">
                    <i class="fas fa-sign-in-alt me-1"></i>Login
                </button>
                <p class="mt-3 text-center">Don't have an account? <a href="register.php">Register here</a></p>
            </form>
        </div>
    </div>
</div>
</body>
</html>
