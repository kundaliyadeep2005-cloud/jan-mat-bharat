<?php
// --- GLOBAL PERFORMANCE OPTIMIZATION ---
// 1. Enable Gzip Compression for fluent transmission
if (!ob_start("ob_gzhandler")) ob_start();

// 2. Set Cache-Control Headers for faster repeat loads
header("Cache-Control: public, max-age=3600"); // 1 hour browser cache
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: SAMEORIGIN");

// --- AUTO-DETECT PROJECT BASE URL ---
// Works whether project is at domain root or in a subfolder (e.g. /download/)
$_scriptPos = strpos($_SERVER['SCRIPT_NAME'], '/php/');
if ($_scriptPos === false) $_scriptPos = strpos($_SERVER['SCRIPT_NAME'], '/admin/');
if ($_scriptPos === false) $_scriptPos = strpos($_SERVER['SCRIPT_NAME'], '/includes/');

if ($_scriptPos !== false) {
    $_projectPath = substr($_SERVER['SCRIPT_NAME'], 0, $_scriptPos);
} else {
    $_projectPath = dirname($_SERVER['SCRIPT_NAME']);
}

define('BASE_URL', rtrim(str_replace('\\', '/', $_projectPath), '/') . '/');

$host = 'sql204.hstn.me';     // Database server (your local machine)
$user = 'mseet_41660868';          // MySQL username (default is root)
$pass = '25SOECE13011';              // Password (empty means no password set)
$dbname = 'mseet_41660868_janmatbharat'; // Your database name

try {
    date_default_timezone_set('Asia/Kolkata');
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    // Set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Fetch objects by default
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // Set MySQL Timezone to match PHP (Asia/Kolkata is +05:30)
    $pdo->exec("SET time_zone = '+05:30'");

    // --- AUTO-MIGRATION LOGIC FOR PRIVACY FIX ---
    try {
        // 1. Add has_voted to users table if it doesn't exist
        $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'has_voted'");
        if ($stmt && $stmt->rowCount() == 0) {
            $pdo->exec("ALTER TABLE users ADD COLUMN has_voted TINYINT(1) DEFAULT 0");
        }
        
        // 1b. Add reset password columns
        $stmt2 = $pdo->query("SHOW COLUMNS FROM users LIKE 'reset_token'");
        if ($stmt2 && $stmt2->rowCount() == 0) {
            $pdo->exec("ALTER TABLE users ADD COLUMN reset_token VARCHAR(64) NULL, ADD COLUMN reset_token_expiry DATETIME NULL");
        }
        
        // 1c. Add Biometric descriptor column
        $stmt3 = $pdo->query("SHOW COLUMNS FROM users LIKE 'face_descriptor'");
        if ($stmt3 && $stmt3->rowCount() == 0) {
            $pdo->exec("ALTER TABLE users ADD COLUMN face_descriptor TEXT NULL");
        }

        // 1d. Add Profile Photo column
        $stmt4 = $pdo->query("SHOW COLUMNS FROM users LIKE 'profile_photo'");
        if ($stmt4 && $stmt4->rowCount() == 0) {
            $pdo->exec("ALTER TABLE users ADD COLUMN profile_photo VARCHAR(255) DEFAULT 'images/profiles/default_avatar.png'");
        }
        
        // 2. Add user_id to votes table if it doesn't exist (voter tracking)
        $stmt = $pdo->query("SHOW COLUMNS FROM votes LIKE 'user_id'");
        if ($stmt && $stmt->rowCount() == 0) {
            $pdo->exec("ALTER TABLE votes ADD COLUMN user_id INT NULL");
            try {
                $pdo->exec("ALTER TABLE votes ADD CONSTRAINT fk_user_id FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL");
            } catch (PDOException $e) { }
        }

        // 1e. Add Pages table and basic content
        $pdo->exec("CREATE TABLE IF NOT EXISTS `pages` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `slug` VARCHAR(50) UNIQUE NOT NULL,
            `title` VARCHAR(255) NOT NULL,
            `content` LONGTEXT NOT NULL,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        // Check if pages are empty
        $stmtCheck = $pdo->query("SELECT COUNT(*) FROM pages");
        if ($stmtCheck->fetchColumn() == 0) {
            // Insert initial content
            $pages = [
                [
                    'slug' => 'home',
                    'title' => 'Home',
                    'content' => json_encode([
                        'hero_title' => 'जन-मत भारत',
                        'hero_tagline' => 'Your Vote • Your Right • Your Future',
                        'hero_quote' => '“मतदान राष्ट्र सेवा का पहला कदम है”',
                        'intro_text' => 'मतदान सरल, सशक्त और महत्वपूर्ण है।<br>एक वोट भविष्य को आकार देता है।'
                    ])
                ],
                [
                    'slug' => 'about',
                    'title' => 'About Jan-Mat Bharat',
                    'content' => '<div class="info-card">
            <h2>🎯 Our Mission</h2>
            <p>Jan-Mat Bharat is a digital voting platform designed to make democracy accessible, transparent, and secure for every Indian citizen. We believe that every vote matters and every voice deserves to be heard.</p>
        </div>
        <div class="info-card">
            <h2>💡 What We Do</h2>
            <p>We provide a simple, secure, and user-friendly platform where citizens can:</p>
            <ul>
                <li>Register as voters with verified credentials</li>
                <li>Cast their votes securely and confidentially</li>
                <li>View real-time election results</li>
                <li>Participate in strengthening Indian democracy</li>
            </ul>
        </div>
        <div class="info-card">
            <h2>🌐 Why Digital Voting?</h2>
            <p>Digital voting platforms offer several advantages:</p>
            <ul>
                <li><strong>Convenience:</strong> Vote from anywhere, anytime</li>
                <li><strong>Speed:</strong> Instant vote counting and results</li>
                <li><strong>Accuracy:</strong> Eliminates manual counting errors</li>
                <li><strong>Cost-Effective:</strong> Reduces election expenses</li>
                <li><strong>Eco-Friendly:</strong> Paperless voting process</li>
            </ul>
        </div>'
                ],
                [
                    'slug' => 'contact',
                    'title' => 'Contact Jan-Mat Bharat',
                    'content' => '<div class="info-card">
                <h2>📞 Get In Touch</h2>
                <p>We are here to support every citizen of Bharat. If you have any questions or need assistance, please reach out to us.</p>
            </div>'
                ]
            ];

            $insertStmt = $pdo->prepare("INSERT INTO `pages` (`slug`, `title`, `content`) VALUES (?, ?, ?)");
            foreach ($pages as $page) {
                $insertStmt->execute([$page['slug'], $page['title'], $page['content']]);
            }
        }

        // 1f. Ensure Settings table and basic content
        $pdo->exec("CREATE TABLE IF NOT EXISTS `settings` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(100) UNIQUE NOT NULL,
            `value` TEXT NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        // Insert default settings if empty
        $stmtCheckSettings = $pdo->query("SELECT COUNT(*) FROM settings");
        if ($stmtCheckSettings->fetchColumn() == 0) {
            $defaultSettings = [
                ['site_name', 'जन-मत भारत'],
                ['site_tagline', 'आपका वोट • आपका अधिकार • आपका भविष्य'],
                ['footer_text', '© 2026 जन-मत भारत 🇮🇳'],
                ['developer_name', 'Deep Kundaliya'],
                ['contact_email', 'support@janmatbharat.in'],
                ['contact_phone', '1800-XXX-XXXX (Toll-Free)'],
                ['facebook_url', 'https://facebook.com'],
                ['twitter_url', 'https://twitter.com'],
                ['instagram_url', 'https://instagram.com']
            ];
            $insertSetting = $pdo->prepare("INSERT INTO `settings` (`name`, `value`) VALUES (?, ?)");
            foreach ($defaultSettings as $s) {
                $insertSetting->execute($s);
            }
        }

        // Load all settings into a global array for easy access
        $global_settings = [];
        $stmtSettings = $pdo->query("SELECT name, value FROM settings");
        while ($row = $stmtSettings->fetch()) {
            $global_settings[$row['name']] = $row['value'];
        }

        // 1g. REBUILD-ON-LEGACY strategy to fix duplication permanently
        try {
            $tableInfo = $pdo->query("SHOW CREATE TABLE parties")->fetch();
            if (strpos($tableInfo['Create Table'], 'UNIQUE KEY `name`') === false) {
                 $pdo->exec("DROP TABLE IF EXISTS parties");
                 $pdo->exec("DROP TABLE IF EXISTS voting_reasons");
            }
        } catch (PDOException $e) { }

        // Core schema with UNIQUE locks
        $pdo->exec("CREATE TABLE IF NOT EXISTS `parties` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(100) UNIQUE NOT NULL,
            `description` TEXT NOT NULL,
            `logo` VARCHAR(255) NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $pdo->exec("CREATE TABLE IF NOT EXISTS `voting_reasons` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `title` VARCHAR(100) UNIQUE NOT NULL,
            `description` TEXT NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        // Sync Data (Consolidated Source)
        $partiesSync = [
            ['Bharatiya Janata Party', 'Focuses on nationalism, development, and governance.', 'images/bjp.png'],
            ['Indian National Congress', 'Emphasizes social justice, equality, and democracy.', 'images/ins.webp'],
            ['Aam Aadmi Party', 'Works for transparency, education, and public welfare.', 'images/aap.webp'],
            ['Communist Party of India', 'Workers and socialist welfare focus.', 'images/comunist.png'],
            ['Trinamool Congress', 'Grassroots secularism and state-level progress.', 'images/trinamool.jpg'],
            ['Bahujan Samaj Party', 'Social justice and equality focus.', 'images/bsp.webp'],
            ['None of the Above', 'Select if you do not support any candidate.', 'images/nota.png']
        ];
        $insertParty = $pdo->prepare("INSERT IGNORE INTO `parties` (`name`, `description`, `logo`) VALUES (?, ?, ?)");
        $updatePartyLogo = $pdo->prepare("UPDATE `parties` SET `logo` = ? WHERE `name` = ? AND (`logo` = '' OR `logo` IS NULL)");
        foreach ($partiesSync as $p) {
            $insertParty->execute($p);
            $updatePartyLogo->execute([$p[2], $p[0]]);
        }

        // Ultra-aggressive normalization: remove any '../' or '/' prefixes from all existing rows
        $pdo->exec("UPDATE parties SET logo = 'images/bjp.png' WHERE name LIKE '%Bharatiya Janata%'");
        $pdo->exec("UPDATE parties SET logo = 'images/ins.webp' WHERE name LIKE '%Indian National Congress%'");
        $pdo->exec("UPDATE parties SET logo = 'images/aap.webp' WHERE name LIKE '%Aam Aadmi Party%'");
        $pdo->exec("UPDATE parties SET logo = 'images/comunist.png' WHERE name LIKE '%Communist Party%'");
        $pdo->exec("UPDATE parties SET logo = 'images/trinamool.jpg' WHERE name LIKE '%Trinamool Congress%'");
        $pdo->exec("UPDATE parties SET logo = 'images/bsp.webp' WHERE name LIKE '%Bahujan Samaj%'");
        $pdo->exec("UPDATE parties SET logo = 'images/nota.png' WHERE name LIKE '%None of the Above%' OR name LIKE '%NOTA%'");
        
        $pdo->exec("UPDATE users SET profile_photo = 'images/profiles/default_avatar.png' WHERE profile_photo LIKE '%default_avatar.png%' OR profile_photo IS NULL OR profile_photo = ''");

        $reasonsSync = [
            ['Strong Leadership', 'Elect leaders who serve the people.'],
            ['Equal Power', 'Every citizen’s vote has equal value.'],
            ['Nation Building', 'Your vote strengthens Bharat.']
        ];
        $insertReason = $pdo->prepare("INSERT IGNORE INTO `voting_reasons` (`title`, `description`) VALUES (?, ?)");
        foreach ($reasonsSync as $r) { $insertReason->execute($r); }


        // 1i. Add voter_id column to users (Migration)
        try {
            $pdo->query("SELECT voter_id FROM users LIMIT 1");
        } catch (PDOException $e) {
            $pdo->exec("ALTER TABLE users ADD voter_id VARCHAR(20) UNIQUE AFTER email");
        }

        // 1j. Add status column to users (Migration)
        try {
            $pdo->query("SELECT status FROM users LIMIT 1");
        } catch (PDOException $e) {
            $pdo->exec("ALTER TABLE users ADD status ENUM('active', 'blocked') DEFAULT 'active' AFTER voter_id");
        }

        // 1n. Add role column to users (Migration)
        try {
            $pdo->query("SELECT role FROM users LIMIT 1");
        } catch (PDOException $e) {
            $pdo->exec("ALTER TABLE users ADD role ENUM('user', 'admin') DEFAULT 'user' AFTER password");
        }

        // Fix any users with missing roles
        $pdo->exec("UPDATE users SET role = 'user' WHERE role IS NULL OR role = ''");

        // 1k. Create support_messages table
        $pdo->exec("CREATE TABLE IF NOT EXISTS `support_messages` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(255) NOT NULL,
            `email` VARCHAR(255) NOT NULL,
            `subject` VARCHAR(255) NOT NULL,
            `message` TEXT NOT NULL,
            `status` ENUM('unread', 'read', 'replied') DEFAULT 'unread',
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        // 1m. Consolidate 'NOTA' and 'None of the Above' in votes table
        $pdo->exec("UPDATE votes SET party = 'None of the Above' WHERE party = 'NOTA'");

    } catch (PDOException $e) { 
        // Ignore migration errors if any
    }

} catch (PDOException $e) {
    die("Database Connection failed: " . $e->getMessage());
}
?>
