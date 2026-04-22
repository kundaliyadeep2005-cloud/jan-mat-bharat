<?php
session_start();
require_once __DIR__ . "/../includes/db_connect.php";

// Fetch about page content
$stmt = $pdo->prepare("SELECT title, content FROM pages WHERE slug = 'about'");
$stmt->execute();
$page = $stmt->fetch();

if (!$page) {
    $page = [
        'title' => 'About Jan-Mat Bharat',
        'content' => '<p>Content coming soon...</p>'
    ];
}

include __DIR__ . "/../includes/header.php"; 
?>

<link rel="stylesheet" href="../css/about.css">

<!-- 
    HERO SECTION
-->
<section class="info-hero">
    <h1><?= htmlspecialchars($page['title']) ?></h1>
    <p><?= $global_settings['site_tagline'] ?? 'Your Voice • Your Power • Your Democracy 🇮🇳' ?></p>
</section>

<!-- 
    ABOUT CONTENT
-->
<section class="info-section">
    <div class="info-container">
        
        <?= $page['content'] ?>

        <!-- MISSION VIDEO SECTION -->
        <div class="info-card video-card mt-5">
            <h2 class="text-center mb-4">🎥 Our Vision in Motion</h2>
            <div class="video-wrapper">
                <iframe 
                    width="560" 
                    height="315" 
                    src="https://www.youtube.com/embed/ffcItiSzUuA" 
                    title="Our Mission - Jan-Mat Bharat" 
                    frameborder="0" 
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" 
                    referrerpolicy="strict-origin-when-cross-origin" 
                    allowfullscreen>
                </iframe>
            </div>
            <p class="text-center mt-3 text-secondary small">Watch how Jan-Mat Bharat is transforming democracy through digital empowerment.</p>
        </div>



    </div>
</section>

<?php include __DIR__ . "/../includes/footer.php"; ?>
