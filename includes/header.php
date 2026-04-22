<?php
require_once __DIR__ . "/../includes/db_connect.php";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= $global_settings['site_tagline'] ?? 'जन-मत भारत - Your Vote, Your Right, Your Future' ?>">
    <title><?= $global_settings['site_name'] ?? 'जन-मत भारत' ?></title>

    <link rel="stylesheet" href="../css/navbar.css?v=6">

    <!-- Google Fonts: Poppins & Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        /* ══ NAVBAR BASE ══════════════════════════════════════════ */
        .navbar-custom {
            min-height: 60px;
            background: linear-gradient(to right, #ff9933, #ffffff, #138808) !important;
            box-shadow: 0 2px 10px rgba(0,0,0,0.12);
            padding: 0 24px;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            width: 100%;
            z-index: 1090;
        }

        body {
            padding-top: 60px !important;
        }

        /* ══ BRAND ════════════════════════════════════════════════ */
        .navbar-brand-custom {
            font-size: 1.2rem;
            font-weight: 700;
            color: #0A3D62 !important;
            white-space: nowrap;
        }

        /* ══ NAV LINKS ════════════════════════════════════════════ */
        .nav-link-custom {
            color: #1e293b !important;
            font-size: 0.85rem;
            font-weight: 600;
            margin: 0 8px;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            border-bottom: 3px solid transparent; /* Prepare for active state border */
            padding-bottom: 4px; /* Space between text and border */
            transition: all 0.2s;
        }
        .nav-link-custom.active {
            color: #138808 !important;
            font-weight: 700 !important;
            border-bottom: 3px solid #138808 !important; /* Prominent active line */
            background: transparent !important;
        }

        /* ══ HAMBURGER TOGGLER ════════════════════════════════════ */
        .navbar-toggler {
            border: 1.5px solid rgba(0,0,0,0.4) !important;
            border-radius: 6px !important;
            padding: 5px 9px !important;
            background: rgba(255,255,255,0.5) !important;
        }
        .navbar-toggler:focus { box-shadow: none !important; }
        .navbar-toggler-icon {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba%280,0,0,0.75%29' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2.5' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
        }

        /* ══ MOBILE MENU COLLAPSE — Dropdown anchored below header ═════ */
        @media (max-width: 767px) {
            .navbar-custom {
                padding: 5px 10px; /* Reduced padding on mobile */
                height: 60px; /* Fixed height so hamburger is vertically centered */
            }

            .navbar-collapse {
                position: absolute;
                top: 60px; /* Right below the 60px navbar */
                left: 0;
                right: 0;
                background: linear-gradient(to bottom, #ffffff, #fdfbf7) !important;
                border-top: 3px solid #ff9933;
                padding: 15px;
                border-radius: 0 0 15px 15px !important;
                box-shadow: 0 10px 25px rgba(0,0,0,0.15) !important;
                margin-top: 0 !important;
                width: 100vw; /* Full width */
                z-index: 1000;
            }

            .nav-link-custom {
                margin: 4px 0;
                padding: 10px 15px !important;
                text-align: left;
                border-radius: 8px;
                display: block;
                width: 100%;
                border-bottom: 1px solid rgba(0,0,0,0.04);
            }
            .nav-link-custom:hover {
                background: rgba(19,136,8,0.05);
            }
            .nav-link-custom.active {
                background: rgba(19,136,8,0.08) !important;
                border-left: 4px solid #138808 !important;
                border-bottom: none !important; /* Remove desktop bottom border on mobile */
                color: #138808 !important;
            }

            .portal-action-pill {
                margin: 15px 0 5px;
                justify-content: flex-start !important;
                padding: 6px;
            }
        }

        /* ══ USER AVATAR PILL ═════════════════════════════════════ */
        .portal-action-pill {
            display: flex;
            align-items: center;
            background: rgba(255,255,255,0.3);
            border: 1px solid rgba(0,0,0,0.08);
            border-radius: 50px;
            padding: 3px 8px 3px 3px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .navbar-avatar {
            width: 30px !important;
            height: 30px !important;
            border-radius: 50% !important;
            object-fit: cover !important;
            border: 2px solid #138808 !important;
        }
        .user-identity-text {
            color: #0A3D62 !important;
            font-weight: 700 !important;
            margin: 0 10px 0 7px !important;
            font-size: 0.82rem !important;
        }

        /* ══ ACTION BUTTONS ═══════════════════════════════════════ */
        .portal-btn-sm {
            border-radius: 50px !important;
            font-size: 0.72rem !important;
            font-weight: 700 !important;
            padding: 5px 14px !important;
            text-transform: uppercase !important;
            letter-spacing: 0.4px !important;
            border: none !important;
            transition: all 0.2s;
        }
        .portal-btn-logout  { background: #dc3545 !important; color: #fff !important; }
        .portal-btn-register { background: #ff9933 !important; color: #fff !important; }
        .portal-btn-login   { background: #138808 !important; color: #fff !important; }
    </style>

</head>
<body>

<!-- NAVBAR (Bootstrap 5 Structure + Custom Design) -->
<nav class="navbar navbar-expand-md navbar-custom fixed-top">
  <div class="container-fluid">
    <a class="navbar-brand navbar-brand-custom" href="../php/index.php"><?= $global_settings['site_name'] ?? 'जन-मत भारत' ?></a>
    
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto align-items-center">
        <?php if (isset($_SESSION['user_id'])): ?>
            <li class="nav-item">
              <a class="nav-link nav-link-custom <?php echo ($current_page == 'index.php' || $current_page == 'php') ? 'active' : ''; ?>" aria-current="page" href="../php/index.php">Home</a>
            </li>
            <li class="nav-item">
              <a class="nav-link nav-link-custom <?php echo ($current_page == 'about.php') ? 'active' : ''; ?>" href="../php/about.php">Our Mission</a>
            </li>
             <li class="nav-item">
              <a class="nav-link nav-link-custom <?php echo ($current_page == 'contact.php') ? 'active' : ''; ?>" href="../php/contact.php">Support Center</a>
            </li>
            <li class="nav-item">
              <a class="nav-link nav-link-custom <?php echo ($current_page == 'vote.php') ? 'active' : ''; ?>" href="../php/vote.php">Cast Your Vote</a>
            </li>
            <li class="nav-item">
              <a class="nav-link nav-link-custom <?php echo ($current_page == 'results.php') ? 'active' : ''; ?>" href="../php/results.php">Live Results</a>
            </li>
           
            <li class="nav-item text-center">
                <a class="nav-link nav-link-custom <?php echo ($current_page == 'edit_profile.php') ? 'active' : ''; ?>" href="../php/edit_profile.php">Voter Profile</a>
            </li>

            <li class="nav-item ms-lg-3">
                <div class="portal-action-pill">
                    <img src="<?php echo BASE_URL . ((!empty($_SESSION['profile_photo'])) ? $_SESSION['profile_photo'] : 'images/profiles/default_avatar.png'); ?>" class="navbar-avatar" alt="User">
                    <span class="user-identity-text d-none d-md-inline"><?= htmlspecialchars($_SESSION['name'] ?? 'Voter') ?></span>
                    <a class="btn portal-btn-sm portal-btn-logout" href="../php/logout.php">Sign Out</a>
                </div>
            </li>
        <?php else: ?>
            <li class="nav-item">
              <a class="nav-link nav-link-custom <?php echo ($current_page == 'index.php' || $current_page == 'php') ? 'active' : ''; ?>" aria-current="page" href="../php/index.php">Home</a>
            </li>
            <li class="nav-item">
              <a class="nav-link nav-link-custom <?php echo ($current_page == 'about.php') ? 'active' : ''; ?>" href="../php/about.php">Our Mission</a>
            </li>
             <li class="nav-item">
              <a class="nav-link nav-link-custom <?php echo ($current_page == 'contact.php') ? 'active' : ''; ?>" href="../php/contact.php">Support Center</a>
            </li>
            <li class="nav-item ms-lg-3">
                <div class="portal-action-pill">
                    <a class="btn portal-btn-sm portal-btn-register me-1" href="../php/register.php">Citizen Registration</a>
                    <a class="btn portal-btn-sm portal-btn-login" href="../php/login.php">Secure Sign-In</a>
                </div>
            </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>
