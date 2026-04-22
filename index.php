<?php
/**
 * Main Entry Point for Jan-Mat Bharat
 * Redirects to the PHP directory where the actual index logic resides.
 * This ensures the application works even if .htaccess is not processed.
 */

// Include the home page logic from the php/ directory
require_once __DIR__ . './php/index.php';
?>
