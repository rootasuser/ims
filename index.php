<?php
session_start();
if (empty($_SESSION['tkn_csrf'])) {
    $_SESSION['tkn_csrf'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['tkn_csrf'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>IMS Portal</title>
<link rel="stylesheet" href="node_modules/bootstrap/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="col-md-5 mx-auto card p-4 shadow-sm">
       <div class="d-flex align-items-center justify-content-center">
       <img src="assets/images/ims.png" alt="ims logo" class="rounded-circle" width="150" height="150">
       </div>
       <h2 class="text-center">PORTAL</h2>
       <hr> 
        <form id="loginForm">
            <div class="mb-3">
                <input type="text" class="form-control" id="username" placeholder="Username" required>
            </div>
            <div class="mb-3">
                <input type="password" class="form-control" id="password" placeholder="Password" required>
            </div>
            <input type="hidden" id="tkn_csrf" value="<?= htmlspecialchars($csrf_token) ?>">
            <button type="submit" class="btn btn-success w-100">Login</button>
        </form>
        <div id="msg" class="text-center mt-3 text-danger"></div>
    </div>
</div>

<script src="server.js"></script>

</body>
</html>
