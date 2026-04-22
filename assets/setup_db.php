<?php
$host = 'localhost';
$user = 'root';
$pass = '';

try {
    // Connect without DB first to create it
    $pdo = new PDO("mysql:host=$host", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create DB
    $pdo->exec("CREATE DATABASE IF NOT EXISTS jan_mat_bharat CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "Database created or already exists.\n";

    // Select DB
    $pdo->exec("USE jan_mat_bharat");

    // Create Users table
    $usersTable = "
    CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        dob DATE NOT NULL,
        state VARCHAR(100) NOT NULL,
        password VARCHAR(255) NOT NULL,
        role ENUM('user', 'admin') DEFAULT 'user',
        status ENUM('active', 'blocked') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($usersTable);
    echo "Users table created.\n";

    // Create Votes table
    $votesTable = "
    CREATE TABLE IF NOT EXISTS votes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT UNIQUE NOT NULL,
        party VARCHAR(100) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    $pdo->exec($votesTable);
    echo "Votes table created.\n";

    // Create default admin user
    $adminPass = password_hash('admin123', PASSWORD_DEFAULT);
    $checkAdmin = $pdo->query("SELECT id FROM users WHERE email = 'admin@janmatbharat.com'");
    if ($checkAdmin->rowCount() == 0) {
        $insertAdmin = $pdo->prepare("INSERT INTO users (name, email, dob, state, password, role) VALUES (?, ?, ?, ?, ?, ?)");
        $insertAdmin->execute(['Admin', 'admin@janmatbharat.com', '1990-01-01', 'Delhi', $adminPass, 'admin']);
        echo "Default admin user created (admin@janmatbharat.com / admin123).\n";
    }

} catch (PDOException $e) {
    die("DB Setup Failed: " . $e->getMessage() . "\n");
}
?>
