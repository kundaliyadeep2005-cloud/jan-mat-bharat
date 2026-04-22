<?php
require_once __DIR__ . "/../includes/admin_auth.php";
require_once __DIR__ . "/../includes/db_connect.php";

$msg = '';
$msgType = '';

// Handle Settings Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_settings'])) {
    foreach ($_POST['settings'] as $key => $value) {
        $stmt = $pdo->prepare("UPDATE settings SET value = ? WHERE name = ?");
        $stmt->execute([$value, $key]);
    }
    $msg = "System settings updated successfully!";
    $msgType = "success";
}

// Fetch all settings
$settings = $pdo->query("SELECT * FROM settings")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Settings | Jan Mat Bharat Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/admin.css">
    <style>
        .settings-card { background: #fff; border: 1px solid var(--border-color); border-radius: 12px; padding: 35px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
        .form-label { font-weight: 600; font-size: 0.85rem; color: #64748b; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px; }
        .form-control { border-color: #e2e8f0; padding: 12px 15px; border-radius: 8px; }
        .form-control:focus { box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.08); border-color: var(--primary); }
    </style>
</head>
<body>

    <?php include __DIR__ . "/../includes/admin_sidebar.php"; ?>
    <?php include __DIR__ . "/../includes/admin_header.php"; ?>

    <main class="main-area">
        <div class="row align-items-center mb-5">
            <div class="col-md-6">
                <h1 class="h2 fw-bold text-dark mb-1">Global Settings</h1>
                <p class="text-secondary small">Define system-wide configurations and identity parameters.</p>
            </div>
            <div class="col-md-6 text-md-end">
                <small class="text-muted"><i class="bi bi-shield-lock-fill me-1"></i> Root Administrator Session</small>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="settings-card">
                    <h5 class="fw-bold text-dark border-bottom pb-3 mb-4"><i class="bi bi-sliders me-2 text-primary"></i> Environment Control</h5>
                    
                    <?php if ($msg): ?>
                        <div class="alert alert-<?= $msgType ?> alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                            <i class="bi bi-check-circle-fill me-2"></i> <?= htmlspecialchars($msg) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                        <input type="hidden" name="update_settings" value="1">
                        
                        <?php foreach($settings as $s): ?>
                        <div class="mb-4">
                            <label class="form-label"><?= str_replace('_', ' ', $s['name']) ?></label>
                            <?php if ($s['name'] == 'site_description'): ?>
                                <textarea name="settings[<?= $s['name'] ?>]" class="form-control" rows="4"><?= htmlspecialchars($s['value']) ?></textarea>
                            <?php else: ?>
                                <input type="text" name="settings[<?= $s['name'] ?>]" class="form-control" value="<?= htmlspecialchars($s['value']) ?>">
                            <?php endif; ?>
                            <small class="text-muted small mt-1 ms-1 d-block opacity-75">Config Identifier: <?= $s['name'] ?></small>
                        </div>
                        <?php endforeach; ?>

                        <div class="d-grid mt-5">
                            <button type="submit" class="btn btn-primary py-3 fw-bold rounded-3 shadow-sm">
                                <i class="bi bi-cloud-arrow-up me-2"></i> Deploy System Changes
                            </button>
                        </div>
                    </form>
                </div>
                
                <div class="mt-4 p-4 rounded-3 border border-warning border-opacity-25" style="background: rgba(245, 158, 11, 0.05);">
                    <small class="text-warning fw-bold d-block mb-1"><i class="bi bi-exclamation-triangle-fill me-2"></i> Warning Container</small>
                    <p class="text-secondary small mb-0">Changes here affect the public-facing identity of Jan Mat Bharat immediately. Ensure all configuration values are audited before deployment.</p>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
