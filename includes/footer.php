<link rel="stylesheet" href="../css/footer.css">

<footer class="footer">
    <div class="footer-top">
        <h2 class="title"><?= $global_settings['site_name'] ?? 'जन-मत भारत' ?></h2>
        <p><?= $global_settings['site_tagline'] ?? 'आपका वोट • आपका अधिकार • आपका भविष्य' ?></p>
    </div>

    <div class="footer-links">
        <a href="../php/index.php">Home</a>
        <a href="../php/about.php">Our Mission</a> 
        <a href="../php/vote.php">Cast Your Vote</a>
        
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="../php/edit_profile.php">Voter Profile</a>
            <a href="../php/results.php">Live Results</a>
            <a href="../php/logout.php" style="color: #dc3545;">Sign Out</a>
        <?php else: ?>
            <a href="../php/register.php">Citizen Registration</a>
            <a href="../php/login.php">Secure Sign-In</a>
        <?php endif; ?>
    </div>

    <div class="footer-bottom">
        <p><?= $global_settings['footer_text'] ?? '© 2026 जन-मत भारत 🇮🇳' ?></p>
        <p>Developed with ❤️ for Bharat</p>
    </div>
</footer>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Bootstrap 5 JS Bundle (Required for Navbar Toggle and other components) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
