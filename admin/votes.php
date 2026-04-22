<?php
require_once __DIR__ . "/../includes/admin_auth.php";
require_once __DIR__ . "/../includes/db_connect.php";

// 🗳️ AGGREGATE VOTE DATA
$totalVotes = $pdo->query("SELECT COUNT(*) FROM votes")->fetchColumn() ?: 1;
$votesByParty = $pdo->query("SELECT party, COUNT(*) as count FROM votes GROUP BY party ORDER BY count DESC")->fetchAll();

// 🕒 RECENT ACTIVITY FEED
$recentActivity = $pdo->query("
    SELECT v.created_at, v.party as vote_party, p.name as party_name, p.logo as party_logo
    FROM votes v
    LEFT JOIN parties p ON v.party = p.name
    ORDER BY v.created_at DESC LIMIT 8
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Votes | Jan Mat Bharat Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/admin.css">
    <style>
        .party-stat-card { background: #fff; border: 1px solid var(--border-color); border-radius: 12px; padding: 20px; transition: all 0.3s; margin-bottom: 15px; position: relative; overflow: hidden; }
        .party-stat-card:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(0,0,0,0.05); border-color: var(--primary); }
        .progress { height: 8px; border-radius: 50px; background: #f1f5f9; }
    </style>
</head>
<body>

    <?php include __DIR__ . "/../includes/admin_sidebar.php"; ?>
    <?php include __DIR__ . "/../includes/admin_header.php"; ?>

    <main class="main-area">
        <div class="row align-items-center mb-5">
            <div class="col-md-6">
                <h2 class="fw-bold text-dark mb-1">Live Vote Center</h2>
                <p class="text-secondary small">Real-time election tracking and consensus analysis.</p>
            </div>
            <div class="col-md-6 text-md-end">
                <h4 class="fw-bold text-primary mb-0"><?= number_format($totalVotes) ?> <small class="text-secondary small fw-medium" style="font-size: 0.75rem;">TOTAL ELECTORAL CASTS</small></h4>
            </div>
        </div>

        <div class="row g-4">
            <!-- PARTY STANDINGS -->
            <div class="col-lg-7">
                <div class="admin-table-card card bg-white border-0 shadow-sm p-4">
                    <h5 class="fw-bold text-dark border-bottom pb-3 mb-4">Constituency Performance</h5>
                    
                    <?php 
                    $colors = ['#FF9933', '#3b82f6', '#10b981', '#ef4444', '#8b5cf6'];
                    foreach($votesByParty as $index => $party): 
                        $pct = round(($party['count'] / $totalVotes) * 100, 1);
                        $color = $colors[$index % count($colors)];
                    ?>
                    <div class="party-stat-card">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="d-flex align-items-center">
                                <div class="bg-light rounded p-2 me-3" style="border: 1px solid <?= $color ?>;"><i class="bi bi-flag-fill text-dark" style="color: <?= $color ?>;"></i></div>
                                <span class="fw-bold text-dark"><?= htmlspecialchars($party['party']) ?></span>
                            </div>
                            <div class="text-end">
                                <span class="fw-bold text-dark d-block"><?= $pct ?>%</span>
                                <small class="text-secondary small fw-medium"><?= number_format($party['count']) ?> Votes</small>
                            </div>
                        </div>
                        <div class="progress shadow-sm">
                            <div class="progress-bar" style="width: <?= $pct ?>%; background: <?= $color ?>;"></div>
                        </div>
                    </div>
                    <?php endforeach; ?>

                    <?php if(empty($votesByParty)): ?>
                    <div class="text-center py-5 text-muted">Awaiting first electronic ballot cast.</div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- TALLY STREAM -->
            <div class="col-lg-5">
                <div class="admin-table-card card bg-white border-0 shadow-sm p-4">
                    <h5 class="fw-bold text-dark border-bottom pb-3 mb-4">Live Activity Stream</h5>
                    <div class="feed-list">
                        <?php foreach($recentActivity as $act): ?>
                        <div class="p-3 mb-3 border border-secondary border-opacity-10 rounded-3" style="background: #fdfdfd;">
                            <div class="d-flex align-items-center mb-2">
                                <?php 
                                    $rawLogo = !empty($act['party_logo']) ? $act['party_logo'] : 'images/ashoka-chakra.png';
                                    $filename = basename($rawLogo);
                                    $cleanLogoUrl = BASE_URL . "images/" . $filename;
                                ?>
                                <img src="<?= $cleanLogoUrl ?>" class="rounded-circle me-2" style="width: 28px; height: 28px; border: 1px solid #eee;" onerror="this.src='https://cdn-icons-png.flaticon.com/512/281/281561.png'">
                                <span class="small fw-bold text-dark"><?= htmlspecialchars($act['party_name'] ?: $act['vote_party']) ?></span>
                                <span class="badge bg-success bg-opacity-10 text-success ms-auto small border border-success border-opacity-25">+1 Vote</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-secondary small"><i class="bi bi-clock me-1"></i><?= date('h:i:s A', strtotime($act['created_at'])) ?></small>
                                <small class="text-primary fw-bold" style="font-size: 0.65rem;">VERIFIED</small>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php if(empty($recentActivity)): ?>
                        <p class="text-center text-muted py-5 small">No recent activity detected.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
