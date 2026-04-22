<?php
require_once __DIR__ . "/includes/db_connect.php";

try {
    // Add photo column
    $pdo->exec("ALTER TABLE users ADD COLUMN photo VARCHAR(255) DEFAULT NULL");
    echo "Column photo added to users table successfully.\n";
} catch (PDOException $e) {
    echo "Error or column already exists: " . $e->getMessage() . "\n";
}

// Create uploads directory
$uploadDir = __DIR__ . '/uploads';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
    echo "Created uploads directory.\n";
} else {
    echo "Uploads directory already exists.\n";
}
?>
