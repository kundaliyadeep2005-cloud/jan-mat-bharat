<?php
require_once __DIR__ . "/../includes/admin_auth.php";
require_once __DIR__ . "/../includes/db_connect.php";
require_once __DIR__ . "/../includes/email_functions.php";

// 🛡️ HANDLE MESSAGE ACTIONS
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['message_id'])) {
    $mid = $_POST['message_id'];
    $act = $_POST['action'];
    $flashMsg = "Action Completed";
    
    if ($act === 'mark_read') {
        $stmt = $pdo->prepare("UPDATE support_messages SET status = 'read' WHERE id = ?");
        $stmt->execute([$mid]);
    } elseif ($act === 'delete') {
        $stmt = $pdo->prepare("DELETE FROM support_messages WHERE id = ?");
        $stmt->execute([$mid]);
    } elseif ($act === 'reply' && isset($_POST['reply_message'])) {
        $replyMessage = trim($_POST['reply_message']);
        
        $stmt = $pdo->prepare("SELECT email, name, subject FROM support_messages WHERE id = ?");
        $stmt->execute([$mid]);
        $msgData = $stmt->fetch();
        
        if ($msgData) {
            $userEmail = $msgData['email'];
            $userName = $msgData['name'];
            $origSubject = $msgData['subject'];
            
            $subject = "Re: " . $origSubject;
            $body = "
                <div style='font-family: Arial, sans-serif; color: #333;'>
                    <p>Hello {$userName},</p>
                    <p>Thank you for reaching out to us. Here is the response from our team regarding your message <strong>\"{$origSubject}\"</strong>:</p>
                    <div style='background-color: #f8f9fa; padding: 15px; border-left: 4px solid #0d6efd; margin: 20px 0;'>
                        " . nl2br(htmlspecialchars($replyMessage)) . "
                    </div>
                    <p>Best Regards,</p>
                    <p><strong>Jan-Mat Bharat Admin Team</strong></p>
                </div>
            ";
            
            if (send_smtp_email($userEmail, $subject, $body)) {
                $stmt = $pdo->prepare("UPDATE support_messages SET status = 'replied' WHERE id = ?");
                $stmt->execute([$mid]);
                $flashMsg = "Reply successfully sent to {$userEmail}.";
            } else {
                $flashMsg = "Failed to send reply. Please check SMTP configuration.";
            }
        }
    }
    
    header("Location: /admin/support.php?msg=" . urlencode($flashMsg));
    exit;
}

// 🔍 Search Logic
$search = isset($_GET['search']) ? $_GET['search'] : '';
$query = "SELECT * FROM support_messages WHERE 1=1";
$params = [];

if ($search) {
    $query .= " AND (name LIKE ? OR email LIKE ? OR subject LIKE ? OR message LIKE ?)";
    $params = ["%$search%", "%$search%", "%$search%", "%$search%"];
}

$query .= " ORDER BY created_at DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$messages = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Support Inbox | Admin Panel</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/admin.css">
    <style>
        .unread-row { background-color: rgba(25, 135, 84, 0.05) !important; }
        .message-content { max-width: 400px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    </style>
</head>
<body>

    <?php include __DIR__ . "/../includes/admin_sidebar.php"; ?>
    <?php include __DIR__ . "/../includes/admin_header.php"; ?>

    <main class="main-area">
        <div class="row align-items-center mb-4">
            <div class="col-md-6 text-md-start">
                <h2 class="fw-bold text-dark mb-0">Support Inbox</h2>
                <p class="text-secondary small">Review and manage citizen inquiries.</p>
            </div>
            <div class="col-md-6 mt-3 mt-md-0">
                <form class="d-flex" method="GET">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control border-secondary border-opacity-25 shadow-none" placeholder="Search messages..." value="<?= htmlspecialchars($search) ?>">
                        <button class="btn btn-dark px-4" type="submit"><i class="bi bi-search"></i></button>
                    </div>
                </form>
            </div>
        </div>

        <?php if (isset($_GET['msg'])): ?>
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                <i class="bi bi-check-circle me-2"></i> <?= htmlspecialchars($_GET['msg']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- MESSAGES TABLE -->
        <div class="admin-table-card card bg-white border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>From</th>
                            <th>Subject & Message</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($messages as $m): ?>
                        <tr class="<?= ($m['status'] === 'unread') ? 'unread-row' : '' ?>">
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold me-3 shadow-sm" style="width: 40px; height: 40px;">
                                        <?= substr($m['name'], 0, 1) ?>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark mb-0"><?= htmlspecialchars($m['name']) ?></div>
                                        <div class="text-secondary small" style="font-size: 0.75rem;"><?= htmlspecialchars($m['email']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="fw-bold text-dark small"><?= htmlspecialchars($m['subject']) ?></div>
                                <div class="text-muted small message-content" title="<?= htmlspecialchars($m['message']) ?>"><?= htmlspecialchars($m['message']) ?></div>
                            </td>
                            <td>
                                <span class="text-secondary small"><?= date('d M Y, H:i', strtotime($m['created_at'])) ?></span>
                            </td>
                            <td>
                                <?php if ($m['status'] === 'unread'): ?>
                                    <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-1 fw-bold" style="font-size: 0.65rem;">NEW</span>
                                <?php elseif ($m['status'] === 'read'): ?>
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill px-3 py-1 fw-bold" style="font-size: 0.65rem;">READ</span>
                                <?php else: ?>
                                    <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3 py-1 fw-bold" style="font-size: 0.65rem;">REPLIED</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle px-3 rounded-pill" type="button" data-bs-toggle="dropdown">
                                        Action
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                                        <li>
                                            <button type="button" class="dropdown-item small py-2" data-bs-toggle="modal" data-bs-target="#viewModal<?= $m['id'] ?>">
                                                <i class="bi bi-eye me-2"></i> View Full Message
                                            </button>
                                        </li>
                                        <?php if ($m['status'] === 'unread'): ?>
                                        <li>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="message_id" value="<?= $m['id'] ?>">
                                                <input type="hidden" name="action" value="mark_read">
                                                <button type="submit" class="dropdown-item small text-success py-2"><i class="bi bi-check2-all me-2"></i> Mark as Read</button>
                                            </form>
                                        </li>
                                        <?php endif; ?>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this message?');">
                                                <input type="hidden" name="message_id" value="<?= $m['id'] ?>">
                                                <input type="hidden" name="action" value="delete">
                                                <button type="submit" class="dropdown-item small text-danger py-2"><i class="bi bi-trash me-2"></i> Delete</button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>

                                <!-- View Modal -->
                                <div class="modal fade" id="viewModal<?= $m['id'] ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <form method="POST" action="" class="w-100">
                                            <div class="modal-content border-0 shadow-lg">
                                                <div class="modal-header bg-light border-0">
                                                    <h5 class="modal-title fw-bold">Message Details</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body text-start p-4">
                                                    <div class="mb-3">
                                                        <label class="text-uppercase small fw-bold text-secondary">From</label>
                                                        <p class="mb-0 text-dark"><?= htmlspecialchars($m['name']) ?> (<?= htmlspecialchars($m['email']) ?>)</p>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="text-uppercase small fw-bold text-secondary">Subject</label>
                                                        <p class="mb-0 text-dark"><?= htmlspecialchars($m['subject']) ?></p>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="text-uppercase small fw-bold text-secondary">Date Received</label>
                                                        <p class="mb-0 text-dark"><?= date('F j, Y, g:i a', strtotime($m['created_at'])) ?></p>
                                                    </div>
                                                    <hr>
                                                    <div class="mb-3">
                                                        <label class="text-uppercase small fw-bold text-secondary">Message</label>
                                                        <p class="mt-2 p-3 bg-light rounded text-dark" style="white-space: pre-wrap;"><?= htmlspecialchars($m['message']) ?></p>
                                                    </div>
                                                    <?php if($m['status'] !== 'replied'): ?>
                                                    <hr class="my-4">
                                                    <div class="mb-0">
                                                        <label class="text-uppercase small fw-bold text-primary"><i class="bi bi-reply-fill me-1"></i> Write a Reply</label>
                                                        <textarea name="reply_message" class="form-control mt-2 shadow-sm" rows="4" required placeholder="Type your reply to <?= htmlspecialchars($m['name']) ?>..."></textarea>
                                                        <input type="hidden" name="message_id" value="<?= $m['id'] ?>">
                                                        <input type="hidden" name="action" value="reply">
                                                        <small class="text-muted mt-2 d-block">This reply will be sent via email to <strong><?= htmlspecialchars($m['email']) ?></strong>.</small>
                                                    </div>
                                                    <?php else: ?>
                                                    <div class="alert alert-success mt-3 mb-0 border-0 bg-success bg-opacity-10 text-success">
                                                        <i class="bi bi-check-circle-fill me-2"></i> This message has been replied to.
                                                    </div>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="modal-footer border-0 bg-light">
                                                    <?php if($m['status'] !== 'replied'): ?>
                                                    <button type="submit" class="btn btn-primary px-4 rounded-pill shadow-sm"><i class="bi bi-send me-1"></i> Send Reply Email</button>
                                                    <?php endif; ?>
                                                    <button type="button" class="btn btn-outline-secondary px-4 rounded-pill" data-bs-dismiss="modal">Close</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($messages)): ?>
                        <tr><td colspan="5" class="text-center py-5 text-muted">No messages found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
