<?php
session_start();
include 'db.php';
if (!isset($_SESSION['user'])) header("Location: login.php");

$search = $_GET['search'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 8;
$offset = ($page - 1) * $limit;

// Count total users
$countStmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE name LIKE ?");
$searchTerm = "%$search%";
$countStmt->bind_param("s", $searchTerm);
$countStmt->execute();
$countStmt->bind_result($totalUsers);
$countStmt->fetch();
$countStmt->close();

// Fetch users with search and pagination
$stmt = $conn->prepare("SELECT * FROM users WHERE name LIKE ? ORDER BY id DESC LIMIT ? OFFSET ?");
$stmt->bind_param("sii", $searchTerm, $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <title>Dashboard</title>
  <meta charset="UTF-8" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
</head>
<body class="bg-light">

<!-- Navigation Bar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="#"><i class="fas fa-shield-alt me-2"></i>SmartGuard</a>
    <div class="collapse navbar-collapse justify-content-end">
      <ul class="navbar-nav align-items-center">
        <!-- Logged-in User Profile Image -->
        <li class="nav-item me-3">
          <img src="uploads/<?= htmlspecialchars($_SESSION['user']['image']) ?>" 
               alt="Profile Image" 
               class="rounded-circle" 
               style="width: 40px; height: 40px; object-fit: cover; border: 2px solid white;">
        </li>
        <li class="nav-item">
          <a class="nav-link" href="edit_profile.php"><i class="fas fa-user-edit me-1"></i>Edit Profile</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt me-1"></i>Logout</a>
        </li>
      </ul>
    </div>
  </div>
</nav>

<!-- Dashboard Content -->
<div class="container mt-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3><i class="fas fa-users me-2 text-primary"></i>All Users</h3>
    <form class="d-flex" method="GET">
      <input class="form-control me-2" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search user..." />
      <button class="btn btn-outline-primary" type="submit"><i class="fas fa-search"></i></button>
    </form>
  </div>

  <div class="row">
    <?php while($row = $result->fetch_assoc()): ?>
      <div class="col-md-3">
        <div class="card mb-4 shadow-sm">
          <img src="uploads/<?= htmlspecialchars($row['image']) ?>" class="card-img-top" style="height: 200px; object-fit: cover;" />
          <div class="card-body text-center">
            <h5 class="card-title"><?= htmlspecialchars($row['name']) ?></h5>
            <p class="card-text text-muted"><?= htmlspecialchars($row['email']) ?></p>
          </div>
        </div>
      </div>
    <?php endwhile; ?>
  </div>

  <!-- Pagination -->
  <nav aria-label="User pagination">
    <ul class="pagination justify-content-center">
      <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
          <a class="page-link" href="?search=<?= urlencode($search) ?>&page=<?= $i ?>"><?= $i ?></a>
        </li>
      <?php endfor; ?>
    </ul>
  </nav>
</div>

</body>
</html>
