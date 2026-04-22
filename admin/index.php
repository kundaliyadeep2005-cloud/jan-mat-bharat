<?php
require_once __DIR__ . "/../includes/admin_auth.php";
require_once __DIR__ . "/../includes/db_connect.php";

// Fetch Stats Data
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn();
$totalVotes = $pdo->query("SELECT COUNT(*) FROM votes")->fetchColumn();

// Recent Users — fetch voter_id too for better identification
$recentUsers = $pdo->query("SELECT name, email, voter_id, created_at FROM users WHERE role = 'user' ORDER BY created_at DESC LIMIT 5")->fetchAll();

// Recent Votes (Activities)
$recentVotes = $pdo->query("SELECT party, created_at FROM votes ORDER BY created_at DESC LIMIT 5")->fetchAll();

// Support Stats
$unreadMessages = $pdo->query("SELECT COUNT(*) FROM support_messages WHERE status = 'unread'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard | Admin Panel</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body>

    <?php include __DIR__ . "/../includes/admin_sidebar.php"; ?>
    <?php include __DIR__ . "/../includes/admin_header.php"; ?>

    <main class="main-area">
        <div class="row align-items-center mb-4">
            <div class="col-md-6">
                <h2 class="fw-bold text-dark mb-1">जन-मत भारत Control Center</h2>
                <p class="text-secondary small">Governance dashboard for electoral oversight. 🇮🇳</p>
            </div>
            <div class="col-md-6 text-md-end">
                <!-- Removed Last Updated Time -->
            </div>
        </div>

        <!-- STAT CARDS ROW -->
        <div class="row g-4 mb-5">
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="icon text-primary"><i class="bi bi-people-fill"></i></div>
                    <div class="label">Total Registered Citizens</div>
                    <div class="value"><?= number_format($totalUsers) ?></div>
                    <div class="text-success small mt-2 fw-medium"><i class="bi bi-graph-up me-1"></i> Growing Base</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="icon text-warning"><i class="bi bi-check2-all"></i></div>
                    <div class="label">Electronic Ballots Cast</div>
                    <div class="value"><?= number_format($totalVotes) ?></div>
                    <div class="text-primary small mt-2 fw-medium"><i class="bi bi-lightning-charge me-1"></i> Total Recorded</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="icon text-success"><i class="bi bi-chat-left-dots-fill"></i></div>
                    <div class="label">Support Inquiries</div>
                    <div class="value"><?= number_format($unreadMessages) ?> <small style="font-size: 0.9rem; font-weight: normal;">Unread</small></div>
                    <a href="../admin/support.php" class="text-decoration-none text-success small mt-2 fw-medium d-block">View Inbox <i class="bi bi-arrow-right ms-1"></i></a>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- RECENT REGISTERED USERS -->
            <div class="col-lg-6">
                <div class="admin-table-card card bg-white border-0 shadow-sm">
                    <h5 class="fw-bold text-dark p-4 border-bottom m-0">
                        <i class="bi bi-person-plus-fill me-2 text-primary"></i>Recent User Onboarding
                    </h5>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Voter ID</th>
                                    <th>Joined At</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($recentUsers)): ?>
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-4">
                                        <i class="bi bi-inbox me-1"></i> No registered users yet.
                                    </td>
                                </tr>
                                <?php else: ?>
                                <?php foreach($recentUsers as $ru): ?>
                                <tr>
                                    <td>
                                        <span class="fw-bold"><?= htmlspecialchars($ru['name']) ?></span><br>
                                        <small class="text-muted" style="font-size:0.75rem;"><?= htmlspecialchars($ru['email']) ?></small>
                                    </td>
                                    <td>
                                        <code style="font-size:0.8rem; background:#f1f5f9; padding:2px 6px; border-radius:4px;">
                                            <?= htmlspecialchars($ru['voter_id'] ?? '—') ?>
                                        </code>
                                    </td>
                                    <td class="text-secondary small">
                                        <?php
                                            if (!empty($ru['created_at']) && $ru['created_at'] !== '0000-00-00 00:00:00') {
                                                echo date('d M Y', strtotime($ru['created_at'])) . '<br>';
                                                echo '<span style="font-size:0.72rem; color:#94a3b8;">' . date('h:i A', strtotime($ru['created_at'])) . '</span>';
                                            } else {
                                                echo '<span class="text-muted">—</span>';
                                            }
                                        ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- RECENT ACTIVITY (VOTES) -->
            <div class="col-lg-6">
                <div class="admin-table-card card bg-white border-0 shadow-sm">
                    <h5 class="fw-bold text-dark p-4 border-bottom m-0">Live Vote Stream</h5>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th>Party/Constituency</th>
                                    <th>Timestamp</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($recentVotes as $rv): ?>
                                <tr>
                                    <td><span class="fw-bold text-dark"><?= htmlspecialchars($rv['party']) ?></span></td>
                                    <td class="text-secondary small"><?= date('d M, h:i A', strtotime($rv['created_at'])) ?></td>
                                    <td><span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-1 fw-bold" style="font-size: 0.7rem;">RECORDED</span></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
