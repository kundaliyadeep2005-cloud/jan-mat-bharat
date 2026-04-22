<!-- admin_sidebar.php -->

<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<!-- CRITICAL INLINE CSS — Ensures sidebar always shows correctly even if admin.css fails to load -->
<style>
:root {
    --sidebar-bg: #1e293b;
    --sidebar-w: 250px;
}

/* ── Sidebar core — always dark ── */
#adminSidebar,
#adminSidebar .offcanvas-body,
.admin-sidebar-brand {
    background-color: #1e293b !important;
    background: #1e293b !important;
    --bs-offcanvas-bg: #1e293b !important;
    color: #f8fafc !important;
}

#adminSidebar {
    border-right: 1px solid rgba(255,255,255,0.06) !important;
    color-scheme: dark;
}

/* ── Body adjustment — ensures sidebar can stretch fully ── */
body {
    position: relative !important;
    min-height: 100vh !important;
}

/* ── Desktop: always pinned to the absolute top-left corner ── */
@media (min-width: 992px) {
    #adminSidebar {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        width: var(--sidebar-w) !important;
        height: 100vh !important;
        background-color: #1e293b !important;
        z-index: 2000 !important; /* Extremely high to ensure it covers everything in the corner */
        transform: none !important;
        visibility: visible !important;
        display: flex !important;
        flex-direction: column !important;
        border-right: 1px solid rgba(255,255,255,0.06) !important;
        margin: 0 !important;
        padding: 0 !important;
    }
    #adminSidebar .offcanvas-body {
        display: flex !important;
        flex-direction: column !important;
        flex-grow: 1 !important;
        overflow-y: auto !important;
        padding: 0 !important;
    }
    .offcanvas-backdrop { display: none !important; }
}

/* ── Mobile: hidden by default, slide in as drawer ── */
@media (max-width: 991px) {
    #adminSidebar {
        width: 260px !important;
        box-shadow: 6px 0 32px rgba(0,0,0,0.55) !important;
    }
}

/* ── Brand strip ── */
.admin-sidebar-brand {
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
    padding: 18px 20px 16px !important;
    border-bottom: 1px solid rgba(255,255,255,0.10) !important;
    flex-shrink: 0 !important;
}

/* ── Nav links ── */
.admin-nav-link {
    display: flex;
    align-items: center;
    gap: 13px;
    padding: 13px 20px;
    color: #cbd5e1 !important;
    text-decoration: none !important;
    font-size: 0.91rem;
    font-weight: 500;
    transition: background 0.2s, color 0.2s;
    border-left: 3px solid transparent;
}
.admin-nav-link i { font-size: 1.1rem; width: 20px; text-align: center; flex-shrink: 0; }
.admin-nav-link:hover { background: rgba(255,255,255,0.09); color: #fff !important; border-left-color: rgba(255,255,255,0.3); }
.admin-nav-link.active { background: rgba(59,130,246,0.2); color: #93c5fd !important; border-left-color: #3b82f6; }

/* ── Top header ── */
.main-header {
    height: 60px;
    background: #ffffff !important;
    background-color: #ffffff !important;
    border-bottom: 3px solid #ff9933;
    display: flex;
    align-items: center;
    padding: 0 12px;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    width: 100%;
    z-index: 1029 !important;
    box-shadow: 0 2px 12px rgba(0,0,0,0.10);
    opacity: 1 !important;
    visibility: visible !important;
}
@media (min-width: 992px) {
    .main-header {
        left: var(--sidebar-w) !important;
        width: calc(100% - var(--sidebar-w)) !important;
    }
}

/* ── Hamburger ── */
.admin-hamburger {
    background: #1e293b !important;
    border: none !important;
    border-radius: 8px !important;
    padding: 6px 12px !important;
    font-size: 1.6rem !important;
    color: #ffffff !important;
    line-height: 1;
    cursor: pointer;
    min-width: 44px;
    min-height: 44px;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
}

/* ── Main content area ── */
.main-area {
    padding: 24px;
    margin-top: 60px;
    min-height: calc(100vh - 60px);
}
@media (min-width: 992px) {
    .main-area { margin-left: var(--sidebar-w); }
}
@media (max-width: 991px) {
    .main-area { padding: 16px; }
}
</style>

<!--
    offcanvas-lg = drawer on mobile (< 992px), always-visible on desktop (≥ 992px)
-->
<div class="offcanvas offcanvas-lg offcanvas-start" tabindex="-1" id="adminSidebar">

    <!-- Brand header -->
    <div class="admin-sidebar-brand">
        <div>
            <div class="fw-bold" style="color: #ff9933; font-size: 1rem; text-shadow: 0 1px 2px rgba(0,0,0,0.3);">
                जन-मत भारत
            </div>
            <small style="font-size: 0.6rem; color: #94a3b8; letter-spacing: 1.2px; text-transform: uppercase;">
                Jan-Mat Bharat Governance
            </small>
        </div>
        <!-- Close button — only on mobile -->
        <button type="button" class="btn-close btn-close-white d-lg-none" data-bs-dismiss="offcanvas"
            aria-label="Close sidebar" style="filter: brightness(1.5);"></button>
    </div>

    <!-- Navigation links -->
    <div class="offcanvas-body p-0">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a href="../admin/index.php"
                    class="admin-nav-link <?= (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'active' : '' ?>">
                    <i class="bi bi-speedometer2"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="../admin/users.php"
                    class="admin-nav-link <?= (basename($_SERVER['PHP_SELF']) == 'users.php') ? 'active' : '' ?>">
                    <i class="bi bi-people"></i>
                    <span>Voter Registry</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="../admin/votes.php"
                    class="admin-nav-link <?= (basename($_SERVER['PHP_SELF']) == 'votes.php') ? 'active' : '' ?>">
                    <i class="bi bi-check2-square"></i>
                    <span>Ballot Records</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="../admin/manage_pages.php"
                    class="admin-nav-link <?= (basename($_SERVER['PHP_SELF']) == 'manage_pages.php') ? 'active' : '' ?>">
                    <i class="bi bi-file-earmark-text"></i>
                    <span>Portal Content</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="../admin/settings.php"
                    class="admin-nav-link <?= (basename($_SERVER['PHP_SELF']) == 'settings.php') ? 'active' : '' ?>">
                    <i class="bi bi-gear"></i>
                    <span>System Config</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="../admin/support.php"
                    class="admin-nav-link <?= (basename($_SERVER['PHP_SELF']) == 'support.php') ? 'active' : '' ?>">
                    <i class="bi bi-chat-dots"></i>
                    <span>Support Inbox</span>
                </a>
            </li>
            <li class="nav-item" style="margin-top: auto; border-top: 1px solid rgba(255,255,255,0.1);">
                <a href="../admin/logout.php" class="admin-nav-link" style="color: #f87171 !important;">
                    <i class="bi bi-box-arrow-right"></i>
                    <span>Sign Out</span>
                </a>
            </li>
        </ul>
    </div>
</div>