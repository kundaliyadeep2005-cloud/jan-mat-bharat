<?php
require_once __DIR__ . "/../includes/admin_auth.php";
require_once __DIR__ . "/../includes/db_connect.php";

$msg = '';
$msgType = '';

// Handle Page Updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_page'])) {
    $id = $_POST['page_id'];
    $title = $_POST['title'];
    $content = $_POST['content'];

    $stmt = $pdo->prepare("UPDATE pages SET title = ?, content = ? WHERE id = ?");
    if ($stmt->execute([$title, $content, $id])) {
        $msg = "Page content successfully updated!";
        $msgType = "success";
    }
}

// Fetch all pages
$stmt = $pdo->query("SELECT * FROM pages");
$pagesArr = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Pages | Jan Mat Bharat Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/admin.css">
    <style>
        .page-card { background: #fff; border: 1px solid var(--border-color); border-radius: 12px; margin-bottom: 25px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
        .page-card-header { padding: 20px 25px; border-bottom: 1px solid #f1f5f9; display: flex; align-items: center; justify-content: space-between; }
        .page-card-body { padding: 25px; }
        .form-label { font-weight: 600; font-size: 0.8rem; color: #64748b; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.5px; }
        .form-control { border-color: #e2e8f0; padding: 10px 15px; border-radius: 6px; font-size: 0.9rem; }
        .form-control:focus { box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.08); border-color: var(--primary); }
    </style>
</head>
<body>

    <?php include __DIR__ . "/../includes/admin_sidebar.php"; ?>
    <?php include __DIR__ . "/../includes/admin_header.php"; ?>

    <main class="main-area">
        <div class="row align-items-center mb-5">
            <div class="col-md-6">
                <h1 class="h2 fw-bold text-dark mb-1">Content Management</h1>
                <p class="text-secondary small">Edit and optimize public-facing information directly from the database.</p>
            </div>
            <div class="col-md-6 text-md-end">
                <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3 py-2 fw-bold"><i class="bi bi-hdd-network me-1"></i> Public Data Interface</span>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <?php if ($msg): ?>
                    <div class="alert alert-<?= $msgType ?> alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i> <?= htmlspecialchars($msg) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <div class="accordion" id="pagesAccordion">
                    <?php foreach($pagesArr as $index => $pg): ?>
                    <div class="page-card">
                        <div class="page-card-header">
                            <div class="d-flex align-items-center">
                                <div class="bg-light p-2 rounded-2 me-3"><i class="bi bi-file-earmark-text text-primary"></i></div>
                                <div>
                                    <h6 class="fw-bold text-dark mb-0"><?= strtoupper($pg['slug']) ?> PAGE</h6>
                                    <small class="text-muted small fw-medium">Last Modified: <?= date('d M, Y') ?></small>
                                </div>
                            </div>
                            <button class="btn btn-sm btn-outline-primary px-3 rounded-pill fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $pg['id'] ?>">
                                <i class="bi bi-pencil-square me-1"></i> Edit Content
                            </button>
                        </div>
                        <div id="collapse<?= $pg['id'] ?>" class="collapse <?= ($index === 0) ? 'show' : '' ?>" data-bs-parent="#pagesAccordion">
                            <div class="page-card-body">
                                <form method="POST">
                                    <input type="hidden" name="page_id" value="<?= $pg['id'] ?>">
                                    <input type="hidden" name="update_page" value="1">
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Internal Title</label>
                                        <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($pg['title']) ?>" required>
                                    </div>
                                    <div class="mb-4">
                                        <label class="form-label">Dynamic Content (HTML Supported)</label>
                                        <textarea name="content" class="form-control" rows="8"><?= htmlspecialchars($pg['content']) ?></textarea>
                                        <div class="mt-2 p-3 bg-light rounded-3 small text-secondary">
                                            <i class="bi bi-info-circle me-1"></i> TIP: Use standard HTML tags (e.g. <code>&lt;p&gt;</code>, <code>&lt;strong&gt;</code>) to format the front-end display.
                                        </div>
                                    </div>
                                    
                                    <div class="text-end">
                                        <button type="submit" class="btn btn-primary px-4 py-2 fw-bold shadow-sm rounded-pill">
                                            Save Page Data <i class="bi bi-check2 ms-1"></i>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
