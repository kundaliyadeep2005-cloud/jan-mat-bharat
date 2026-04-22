<?php
require_once __DIR__ . "/../includes/db_connect.php";

$email = 'admin@janmatbharat.com';
$password = 'admin123';
$hashed = password_hash($password, PASSWORD_DEFAULT);

try {
    // Check if the admin account already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    
    if($stmt->rowCount() > 0) {
        // Force update the password, role, and active status
        $updateStmt = $pdo->prepare("UPDATE users SET password = ?, role = 'admin', status = 'active' WHERE email = ?");
        $updateStmt->execute([$hashed, $email]);
        echo "<h3>✅ Admin password has been successfully reset!</h3>";
    } else {
        // Create the admin account if it was missing
        $insertStmt = $pdo->prepare("INSERT INTO users (name, email, dob, state, password, role, status) VALUES ('Admin', ?, '1990-01-01', 'Delhi', ?, 'admin', 'active')");
        $insertStmt->execute([$email, $hashed]);
        echo "<h3>✅ Admin account has been successfully created!</h3>";
    }
    
    echo "<p>You can now log in at: <strong><a href='../admin/login.php'>/admin/login.php</a></strong></p>";
    echo "<ul>";
    echo "<li><b>Email:</b> " . htmlspecialchars($email) . "</li>";
    echo "<li><b>Password:</b> " . htmlspecialchars($password) . "</li>";
    echo "</ul>";
    
} catch (PDOException $e) {
    echo "<h3>❌ Error updating database</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?>
