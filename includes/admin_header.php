<!-- admin_header.php -->
<header class="main-header" id="adminTopHeader">

    <!-- LEFT: Hamburger + Brand (mobile only, hidden on desktop) -->
    <div class="d-flex align-items-center me-auto d-lg-none" style="gap: 10px;">
        <button
            type="button"
            class="admin-hamburger"
            data-bs-toggle="offcanvas"
            data-bs-target="#adminSidebar"
            aria-controls="adminSidebar"
            aria-label="Open menu"
            style="background:#1e293b;border:none;color:#fff;border-radius:8px;padding:6px 12px;font-size:1.6rem;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;min-width:44px;min-height:44px;"
        >&#9776;</button>
        <strong style="font-size:0.9rem;color:#1e293b;letter-spacing:0.3px;white-space:nowrap;">जन-मत भारत</strong>
    </div>

    <!-- Desktop brand (hidden on mobile) -->
    <div class="d-none d-lg-flex align-items-center me-auto">
        <span class="fw-bold text-dark me-3" style="letter-spacing: 0.5px; font-size: 0.95rem;">जन-मत भारत</span>
        <span class="fw-semibold text-secondary small border-start ps-3" style="border-color: #cbd5e1 !important;">
            <i class="bi bi-shield-check me-1 text-primary"></i>Admin Panel
        </span>
    </div>

    <!-- RIGHT: User info + Sign out (always visible) -->
    <div class="d-flex align-items-center" style="gap:8px;flex-shrink:0;">
        <span class="text-secondary small fw-medium d-none d-sm-inline">
            <i class="bi bi-person-circle me-1"></i><?= htmlspecialchars($_SESSION['name'] ?? 'Admin') ?>
        </span>
        <a href="../admin/logout.php"
           style="font-size:0.78rem;padding:5px 12px;white-space:nowrap;background:#fff;border:1px solid #dc3545;color:#dc3545;border-radius:20px;text-decoration:none;display:inline-flex;align-items:center;gap:4px;">
            <i class="bi bi-box-arrow-right"></i>
            <span class="d-none d-sm-inline">Sign Out</span>
        </a>
    </div>

</header>
