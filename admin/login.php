<?php
session_name('JAN_MAT_ADMIN_SESSION');
session_start();
require_once __DIR__ . "/../includes/db_connect.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email']);
    $email = filter_var($email, FILTER_VALIDATE_EMAIL);
    $pass  = $_POST['password'];

    if ($email && $pass) {
        try {
            // Only fetching users where role is 'admin'
            $stmt = $pdo->prepare("SELECT id, name, password, role FROM users WHERE email = ? AND role = 'admin' AND status = 'active'");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($pass, $user['password'])) {
                session_regenerate_id(true); // Prevent Session Fixation
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['name']    = $user['name'];
                $_SESSION['role']    = $user['role'];
                
                header("Location: index.php");
                exit;
            } else {
                $error = "Invalid admin credentials or account is blocked.";
            }
        } catch (PDOException $e) {
            $error = "Login failed: " . $e->getMessage();
        }
    } else {
        $error = "Please fill all required fields!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login | Jan Mat Bharat</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Using admin specific login styling -->
    <link rel="stylesheet" href="../css/admin_login.css">
</head>
<body>

<main class="page-wrapper">
    <div class="login-box">
                    
        <h1 class="mb-2">जन-मत भारत</h1>
        <img src="<?= BASE_URL ?>images/ashoka-chakra.png" class="chakra mb-3" alt="Ashoka Chakra">
        <p class="tagline mb-4">Jan-Mat Bharat Admin Portal</p>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <!-- LOGIN FORM (Bootstrap Validation) -->
        <form method="POST" novalidate id="adminLoginForm" class="needs-validation">

            <div class="mb-3 text-start">
                <input type="email" class="form-control" id="email" name="email" placeholder="Admin Email" required>
                <div class="invalid-feedback">Please enter valid admin email.</div>
            </div>

            <div class="mb-3 text-start">
                <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                <div class="invalid-feedback">Please enter password.</div>
            </div>

            <div class="d-grid gap-2 mt-4">
                <button type="submit" class="btn btn-admin">Login to Admin</button>
            </div>

            <p class="link mt-3 pt-2 text-center border-top">
                <a href="../php/index.php">&larr; Back to Main Site</a>
            </p>
        </form>

    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Simple Validation
(function () {
  'use strict'
  var forms = document.querySelectorAll('.needs-validation')
  Array.prototype.slice.call(forms)
    .forEach(function (form) {
      form.addEventListener('submit', function (event) {
        if (!form.checkValidity()) {
          event.preventDefault()
          event.stopPropagation()
        }
        form.classList.add('was-validated')
      }, false)
    })
})()
</script>
</body>
</html>
