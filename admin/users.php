<?php
require_once __DIR__ . "/../includes/admin_auth.php";
require_once __DIR__ . "/../includes/db_connect.php";

// 🛡️ HANDLE USER ACTIONS
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['user_id'])) {
    $uid = (int)$_POST['user_id'];
    $act = $_POST['action'];

    if ($act === 'delete') {
        // Safety: only allow deleting regular users, never admins
        $check = $pdo->prepare("SELECT id, has_voted FROM users WHERE id = ? AND role = 'user'");
        $check->execute([$uid]);
        $target = $check->fetch();

        if ($target) {
            $pdo->beginTransaction();
            try {
                // Also delete the user's vote so results page reflects the change
                $pdo->prepare("DELETE FROM votes WHERE user_id = ?")->execute([$uid]);
                // Now delete the user account
                $pdo->prepare("DELETE FROM users WHERE id = ? AND role = 'user'")->execute([$uid]);
                $pdo->commit();
                header("Location: users.php?msg=Account+and+vote+deleted+successfully&type=danger");
            } catch (Exception $e) {
                $pdo->rollBack();
                header("Location: users.php?msg=Delete+failed&type=danger");
            }
        } else {
            header("Location: users.php?msg=User+not+found&type=danger");
        }
        exit;
    }

    if ($act === 'delete_vote') {
        $pdo->beginTransaction();
        try {
            $pdo->prepare("DELETE FROM votes WHERE user_id = ?")->execute([$uid]);
            $pdo->prepare("UPDATE users SET has_voted = 0 WHERE id = ?")->execute([$uid]);
            $pdo->commit();
            header("Location: users.php?msg=Vote+deleted+successfully&type=success");
        } catch (Exception $e) {
            $pdo->rollBack();
            header("Location: users.php?msg=Failed+to+delete+vote&type=danger");
        }
        exit;
    }

    $status = ($act === 'suspend') ? 'blocked' : 'active';
    $stmt = $pdo->prepare("UPDATE users SET status = ? WHERE id = ? AND role = 'user'");
    $stmt->execute([$status, $uid]);
    header("Location: users.php?msg=Status+Updated&type=success");
    exit;
}

// 🔍 Search Logic
$search = isset($_GET['search']) ? $_GET['search'] : '';
$query = "SELECT u.*, v.party AS voted_for FROM users u LEFT JOIN votes v ON u.id = v.user_id WHERE u.role = 'user'";
$params = [];

if ($search) {
    $query .= " AND (u.name LIKE ? OR u.email LIKE ? OR u.voter_id LIKE ?)";
    $params = ["%$search%", "%$search%", "%$search%"];
}

$query .= " ORDER BY u.created_at DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$users = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Voter Management | Admin Panel</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body>

    <?php include __DIR__ . "/../includes/admin_sidebar.php"; ?>
    <?php include __DIR__ . "/../includes/admin_header.php"; ?>

    <main class="main-area">
        <?php if (isset($_GET['msg'])): ?>
        <div class="alert alert-<?= htmlspecialchars($_GET['type'] ?? 'success') ?> alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-<?= ($_GET['type'] ?? 'success') === 'danger' ? 'exclamation-triangle' : 'check-circle' ?> me-2"></i>
            <?= htmlspecialchars($_GET['msg']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <div class="row align-items-center mb-4">
            <div class="col-md-6 text-md-start">
                <h2 class="fw-bold text-dark mb-0">Voter Directory</h2>
                <p class="text-secondary small">Manage and verify citizen credentials.</p>
            </div>
            <div class="col-md-6 mt-3 mt-md-0">
                <form class="d-flex" method="GET">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control border-secondary border-opacity-25 shadow-none" placeholder="Search by name, ID or email..." value="<?= htmlspecialchars($search) ?>">
                        <button class="btn btn-dark px-4" type="submit"><i class="bi bi-search"></i></button>
                    </div>
                </form>
            </div>
        </div>

        <!-- USERS TABLE -->
        <div class="admin-table-card card bg-white border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Citizen Identity</th>
                            <th>Voter ID</th>
                            <th>Status</th>
                            <th>Voted For</th>
                            <th class="text-end">Administrative Controls</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($users as $u): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold me-3 shadow-sm" style="width: 40px; height: 40px;">
                                        <?= substr($u['name'], 0, 1) ?>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark mb-0"><?= htmlspecialchars($u['name']) ?></div>
                                        <div class="text-secondary small" style="font-size: 0.75rem;"><?= htmlspecialchars($u['email']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="bg-light text-dark px-2 py-1 rounded small border fw-semibold">
                                    <?= htmlspecialchars($u['voter_id'] ?? 'N/A') ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($u['status'] === 'active'): ?>
                                    <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-1 fw-bold" style="font-size: 0.65rem;">ACTIVE</span>
                                <?php else: ?>
                                    <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-3 py-1 fw-bold" style="font-size: 0.65rem;">SUSPENDED</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($u['voted_for']): ?>
                                    <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3 py-1 fw-bold" style="font-size: 0.70rem;"><?= htmlspecialchars($u['voted_for']) ?></span>
                                <?php else: ?>
                                    <span class="text-muted small">Has not voted</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle px-3 rounded-pill" type="button" data-bs-toggle="dropdown">
                                        Manage
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                                        <li>
                                            <form method="POST" class="px-2 py-1">
                                                <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                                <?php if ($u['status'] === 'active'): ?>
                                                    <input type="hidden" name="action" value="suspend">
                                                    <button type="submit" class="dropdown-item small text-warning rounded py-2"><i class="bi bi-slash-circle me-2"></i> Suspend Account</button>
                                                <?php else: ?>
                                                    <input type="hidden" name="action" value="activate">
                                                    <button type="submit" class="dropdown-item small text-success rounded py-2"><i class="bi bi-person-check me-2"></i> Activate Account</button>
                                                <?php endif; ?>
                                            </form>
                                        </li>
                                        <?php if ($u['has_voted']): ?>
                                        <li>
                                            <form method="POST" class="px-2 py-1" onsubmit="return confirm('WARNING: Are you sure you want to delete this vote? The user will be able to vote again.')">
                                                <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                                <input type="hidden" name="action" value="delete_vote">
                                                <button type="submit" class="dropdown-item small text-danger rounded py-2"><i class="bi bi-x-circle me-2"></i> Delete Vote</button>
                                            </form>
                                        </li>
                                        <?php endif; ?>
                                        <li><hr class="dropdown-divider my-1"></li>
                                        <li>
                                            <form method="POST" class="px-2 py-1" onsubmit="return confirmDelete('<?= htmlspecialchars($u['name'], ENT_QUOTES) ?>')">
                                                <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                                <input type="hidden" name="action" value="delete">
                                                <button type="submit" class="dropdown-item small text-danger fw-bold rounded py-2"><i class="bi bi-trash3 me-2"></i> Delete Account</button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($users)): ?>
                        <tr><td colspan="4" class="text-center py-5 text-muted">No records found matching your search.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function confirmDelete(name) {
            return confirm('⚠️ WARNING: Are you sure you want to permanently delete the account of "' + name + '"?\n\nThis action CANNOT be undone.');
        }
    </script>
</body>
</html>
