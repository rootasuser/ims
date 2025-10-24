<?php
session_start();
if (empty($_SESSION['logged_in'])) {
    header('Location: index.php');
    exit;
}

$usrid = $_SESSION['user_id'];
$role = $_SESSION['role'];

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard</title>
<link href="node_modules/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet" />
<link rel="stylesheet" href="node_modules/sweetalert2/dist/sweetalert2.min.css" />
<link rel="stylesheet" href="assets/css/span.badge.css" />
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet" />
 <link rel="stylesheet" href="assets/css/dashboard.css" />
</head>
<body>

<div class="mobile-header d-lg-none">
    <button class="toggle-btn" onclick="toggleSidebar()"><i class="bi bi-list"></i></button>
    <span><?= htmlspecialchars($_SESSION['username']) ?> (<?= htmlspecialchars($_SESSION['role']) ?>)</span>
</div>

<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <h5>IMS Panel</h5>
        <small><?= htmlspecialchars($_SESSION['username']) ?> (<?= htmlspecialchars($_SESSION['role']) ?>)</small>
    </div>

    <a href="dashboard.php?r/imsdev/=home" class="active"><i class="bi bi-speedometer2 mr-2"></i> Dashboard</a>

    <a href="dashboard.php?r/imsdev/=d_i"><i class="bi bi-box-seam mr-2"></i> Device Inventory</a>


    <a href="#" class="dropdown-link">
        <i class="bi bi-people mr-2"></i> Users
        <i class="bi bi-chevron-down"></i>
    </a>
    <div class="dropdown">
        <a href="#"><i class="bi bi-person-plus mr-2"></i> Add User</a>
        <a href="#"><i class="bi bi-person-lines-fill mr-2"></i> Manage Users</a>
    </div>

    <a href="#" class="dropdown-link">
        <i class="bi bi-bar-chart-line mr-2"></i> Reports
        <i class="bi bi-chevron-down"></i>
    </a>
    <div class="dropdown">
        <a href="#"><i class="bi bi-graph-up mr-2"></i> Monthly Reports</a>
        <a href="#"><i class="bi bi-calendar-week mr-2"></i> Yearly Reports</a>
    </div>

    <hr class="bg-secondary">

    <button class="logout-btn" onclick="logout()">
        <i class="bi bi-box-arrow-right mr-2"></i> Logout
    </button>
</div>


<div class="content" id="content">
    <div class="container-fluid">
        <?php 
        $page = $_GET['r/imsdev/'] ?? 'home'; 

        $allowed_pages = [
            'home' => 'include/pages/home.php',
            'd_i' => 'include/pages/d_i.php'
        ];

        if (array_key_exists($page, $allowed_pages)) {
            include $allowed_pages[$page];
        } else {
            echo "<div class='alert alert-danger mt-4'>Invalid page access detected.</div>";
        }
        ?>
    </div>
</div>

<script src="node_modules/sweetalert2/dist/sweetalert2.min.js"></script>
<script src="node_modules/jquery/dist/jquery.min.js"></script>
<script src="node_modules/popper.js/dist/popper.min.js"></script>
<script src="node_modules/bootstrap/dist/js/bootstrap.min.js"></script>

<script>
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const content = document.getElementById('content');
    sidebar.classList.toggle('show');
    content.classList.toggle('shift');
}

document.querySelectorAll('.dropdown-link').forEach(link => {
    link.addEventListener('click', e => {
        e.preventDefault();
        link.classList.toggle('open');

        const dropdown = link.nextElementSibling;
        dropdown.classList.toggle('show');
    });
});

async function logout() {
    await fetch('api/auth.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ action: 'logout' }),
        credentials: 'same-origin'
    });
    window.location.href = 'index.php';
}
</script>
</body>
</html>
