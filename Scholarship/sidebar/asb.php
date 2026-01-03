<?php
// sidebar/asb.php
$current = basename($_SERVER['PHP_SELF']); // ex: dashboard.php

function activeLink(string $file, string $current): string {
  return $file === $current ? "active" : "";
}

// Get actual counts from database
$admin_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
$unread_notif_count = 0;
$pending_cert_count = 0;

if ($admin_id > 0 && isset($conn)) {
  // Count unread notifications
  $notif_result = mysqli_query($conn, "SELECT COUNT(*) as count FROM notifications WHERE admin_id = $admin_id AND is_read = 0");
  if ($notif_result) {
    $notif_row = mysqli_fetch_assoc($notif_result);
    $unread_notif_count = (int)$notif_row['count'];
  }
  
  // Count pending certificate requests
  $cert_result = mysqli_query($conn, "SELECT COUNT(*) as count FROM certificate_requests WHERE status = 'pending'");
  if ($cert_result) {
    $cert_row = mysqli_fetch_assoc($cert_result);
    $pending_cert_count = (int)$cert_row['count'];
  }
}
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
  :root {
    --primary: #2c3e50;
    --secondary: #3498db;
    --text-light: #ecf0f1;
    --hover: rgba(255,255,255,0.12);
    --active: rgba(255,255,255,0.20);
  }

  .admin-sidebar {
    width: 260px;
    height: 100vh;
    background: var(--primary);
    color: var(--text-light);
    display: flex;
    flex-direction: column;
    position: sticky;
    top: 0;
    transition: transform 0.3s ease, width 0.3s ease;
    overflow: hidden;
  }

  .admin-sidebar.hidden {
    transform: translateX(-260px);
  }

  .admin-sidebar .brand {
    padding: 1.4rem 1.3rem;
    display: flex;
    align-items: center;
    gap: .8rem;
    border-bottom: 1px solid rgba(255,255,255,0.12);
  }

  .admin-sidebar .brand i {
    font-size: 1.7rem;
    color: var(--secondary);
  }

  .admin-sidebar .brand .title {
    font-size: 1.1rem;
    font-weight: 800;
    line-height: 1.1;
  }

  .admin-sidebar .brand .sub {
    font-size: .8rem;
    opacity: .75;
    margin-top: 2px;
  }

  .admin-sidebar .nav {
    flex: 1;
    padding: .8rem 0;
    overflow-y: auto;
  }

  .admin-sidebar .section-label {
    font-size: .72rem;
    text-transform: uppercase;
    letter-spacing: .08em;
    opacity: .65;
    padding: .9rem 1.3rem .35rem;
  }

  .admin-sidebar a.nav-link {
    display: flex;
    align-items: center;
    gap: .75rem;
    padding: .75rem 1.3rem;
    color: var(--text-light);
    text-decoration: none;
    font-size: .95rem;
    transition: background .2s, padding-left .2s;
  }

  .admin-sidebar a.nav-link i {
    width: 18px;
    text-align: center;
    font-size: 1rem;
    opacity: .95;
  }

  .admin-sidebar a.nav-link:hover {
    background: var(--hover);
    padding-left: 1.55rem;
  }

  .admin-sidebar a.nav-link.active {
    background: var(--active);
    border-left: 4px solid var(--secondary);
    padding-left: 1.1rem;
    font-weight: 700;
  }

  .admin-sidebar .nav-link .badge {
    margin-left: auto;
    background: #e74c3c;
    color: white;
    font-size: .7rem;
    font-weight: 700;
    padding: .2rem .5rem;
    border-radius: 10px;
    min-width: 20px;
    text-align: center;
    animation: pulse 2s infinite;
  }

  @keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.7; }
  }

  .admin-sidebar .footer {
    padding: 1rem 1.3rem;
    border-top: 1px solid rgba(255,255,255,0.12);
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: .75rem;
  }

  .admin-sidebar .footer .status {
    display: inline-flex;
    align-items: center;
    gap: .5rem;
    font-size: .8rem;
    opacity: .85;
  }

  .admin-sidebar .footer .dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: #2ecc71;
  }

  .admin-sidebar .logout {
    color: #ffdddd;
    text-decoration: none;
    font-size: .9rem;
    font-weight: 600;
  }

  .admin-sidebar .logout:hover {
    text-decoration: underline;
  }

  .sidebar-toggle {
    position: fixed;
    top: 15px;
    left: 15px;
    width: 45px;
    height: 45px;
    background: var(--primary);
    color: white;
    border: none;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.3rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.15);
    z-index: 999;
    transition: left 0.3s ease, transform 0.2s, opacity 0.2s;
    opacity: 0.85;
  }

  .sidebar-toggle:hover {
    transform: scale(1.05);
    opacity: 1;
  }

  .sidebar-toggle.shifted {
    left: 275px;
  }

  /* Responsive Design */
  @media (max-width: 1024px) {
    .admin-sidebar {
      position: fixed;
      z-index: 1000;
      box-shadow: 2px 0 10px rgba(0,0,0,0.2);
    }

    .admin-sidebar.hidden {
      transform: translateX(-260px);
    }

    .sidebar-toggle {
      left: 15px;
    }

    .sidebar-toggle.shifted {
      left: 275px;
    }
  }

  @media (max-width: 768px) {
    .admin-sidebar {
      width: 280px;
    }

    .admin-sidebar.hidden {
      transform: translateX(-280px);
    }

    .sidebar-toggle.shifted {
      left: 295px;
    }
  }

  /* Overlay for mobile when sidebar is open */
  .sidebar-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.5);
    z-index: 998;
    opacity: 0;
    transition: opacity 0.3s ease;
  }

  .sidebar-overlay.active {
    display: block;
    opacity: 1;
  }

  @media (min-width: 1025px) {
    .sidebar-overlay {
      display: none !important;
    }
  }

  /* Content adjustment when sidebar toggles (Desktop only) */
  @media (min-width: 1025px) {
    body.sidebar-open {
      margin-left: 0;
    }
    
    body.sidebar-closed {
      margin-left: 0;
    }
    
    /* Target common content wrappers */
    body.sidebar-open .main-content,
    body.sidebar-open main,
    body.sidebar-open .content {
      margin-left: 0;
      transition: margin-left 0.3s ease;
    }
    
    body.sidebar-closed .main-content,
    body.sidebar-closed main,
    body.sidebar-closed .content {
      margin-left: -260px;
      transition: margin-left 0.3s ease;
    }
  }
</style>

<aside class="admin-sidebar">
  <div class="brand">
    <i class="fas fa-user-shield"></i>
    <div>
      <div class="title">Admin Panel</div>
      <div class="sub">ScholarManage</div>
    </div>
  </div>

  <nav class="nav">
    <div class="section-label">Overview</div>
    <a href="dashboard.php" class="nav-link <?= activeLink('dashboard.php', $current) ?>">
      <i class="fas fa-chart-line"></i>
      Dashboard
    </a>

    <div class="section-label">Scholarship</div>
    <a href="applications.php" class="nav-link <?= activeLink('applications.php', $current) ?>">
      <i class="fas fa-file-signature"></i>
      Applications
    </a>
    <a href="active_scholars.php" class="nav-link <?= activeLink('active_scholars.php', $current) ?>">
      <i class="fas fa-user-graduate"></i>
      Active Scholars
    </a>

    <div class="section-label">Certificates</div>
    <a href="certificate_requests.php" class="nav-link <?= activeLink('certificate_requests.php', $current) ?>">
      <i class="fas fa-certificate"></i>
      Certificate Requests
      <?php if ($pending_cert_count > 0): ?>
        <span class="badge" id="certRequestBadge"><?php echo $pending_cert_count > 99 ? '99+' : $pending_cert_count; ?></span>
      <?php endif; ?>
    </a>
    <a href="generated_certificates.php" class="nav-link <?= activeLink('generated_certificates.php', $current) ?>">
      <i class="fas fa-folder-open"></i>
      Generated Certificates
    </a>

    <div class="section-label">System</div>
    <a href="users.php" class="nav-link <?= activeLink('users.php', $current) ?>">
      <i class="fas fa-users"></i>
      Manage Users
    </a>
    <a href="notifications.php" class="nav-link <?= activeLink('notifications.php', $current) ?>">
      <i class="fas fa-bell"></i>
      Notifications
      <?php if ($unread_notif_count > 0): ?>
        <span class="badge" id="adminNotifBadge"><?php echo $unread_notif_count > 99 ? '99+' : $unread_notif_count; ?></span>
      <?php endif; ?>
    </a>
    
  </nav>

  <div class="footer">
    <div class="status">
      <span class="dot"></span>
      Admin Online
    </div>
    <a class="logout" href="#" id="adminLogoutBtn" title="Logout">
      <i class="fas fa-sign-out-alt"></i>
    </a>
  </div>
</aside>

<button class="sidebar-toggle" id="sidebarToggle" title="Toggle Sidebar">
  <i class="fas fa-bars"></i>
</button>

<div class="sidebar-overlay" id="sidebarOverlay"></div>

<script>
  (function() {
    // Logout confirmation with SweetAlert2
    document.getElementById('adminLogoutBtn').addEventListener('click', function(e) {
      e.preventDefault();
      
      Swal.fire({
        title: 'Are you sure?',
        text: "Do you want to logout?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3498db',
        cancelButtonColor: '#95a5a6',
        confirmButtonText: 'Yes, logout',
        cancelButtonText: 'Cancel'
      }).then((result) => {
        if (result.isConfirmed) {
          window.location.href = '../logout.php';
        }
      });
    });
    
    // Initialize counts from server
    const serverNotifCount = <?php echo $unread_notif_count; ?>;
    const serverCertCount = <?php echo $pending_cert_count; ?>;
    
    // Store in localStorage for client-side updates
    localStorage.setItem('adminUnreadNotifications', serverNotifCount);
    localStorage.setItem('certRequestCount', serverCertCount);
    
    // Notification badge management
    function updateNotificationBadge() {
      const unreadCount = parseInt(localStorage.getItem('adminUnreadNotifications') || '0');
      
      const badge = document.getElementById('adminNotifBadge');
      if (badge) {
        if (unreadCount > 0) {
          badge.textContent = unreadCount > 99 ? '99+' : unreadCount;
          badge.style.display = 'inline-block';
        } else {
          badge.style.display = 'none';
        }
      }
    }
    
    // Certificate request badge management
    function updateCertRequestBadge() {
      const requestCount = parseInt(localStorage.getItem('certRequestCount') || '0');
      
      const badge = document.getElementById('certRequestBadge');
      if (badge) {
        if (requestCount > 0) {
          badge.textContent = requestCount > 99 ? '99+' : requestCount;
          badge.style.display = 'inline-block';
        } else {
          badge.style.display = 'none';
        }
      }
    }
    
    // Mark notifications as read when clicking notifications link
    document.querySelector('a[href="notifications.php"]')?.addEventListener('click', function() {
      setTimeout(() => {
        localStorage.setItem('adminUnreadNotifications', '0');
        updateNotificationBadge();
      }, 1000);
    });
    
    // Update cert request badge when visiting certificate requests page
    document.querySelector('a[href="certificate_requests.php"]')?.addEventListener('click', function() {
      setTimeout(() => {
        localStorage.setItem('certRequestCount', '0');
        updateCertRequestBadge();
      }, 1000);
    });
    
    // Initialize badges
    updateNotificationBadge();
    updateCertRequestBadge();
    
    // Sidebar toggle functionality
    const sidebar = document.querySelector('.admin-sidebar');
    const toggle = document.getElementById('sidebarToggle');
    const toggleIcon = toggle.querySelector('i');
    const overlay = document.getElementById('sidebarOverlay');
    
    // Check if mobile
    function isMobile() {
      return window.innerWidth <= 1024;
    }
    
    // Load saved state from localStorage (only for desktop)
    let isHidden = localStorage.getItem('adminSidebarHidden') === 'true';
    
    // On mobile, always start hidden
    if (isMobile()) {
      isHidden = true;
    }
    
    if (isHidden) {
      sidebar.classList.add('hidden');
      toggle.classList.remove('shifted');
      toggleIcon.classList.remove('fa-bars');
      toggleIcon.classList.add('fa-chevron-right');
      if (!isMobile()) {
        document.body.classList.add('sidebar-closed');
        document.body.classList.remove('sidebar-open');
      }
    } else {
      toggle.classList.add('shifted');
      if (!isMobile()) {
        document.body.classList.add('sidebar-open');
        document.body.classList.remove('sidebar-closed');
      }
    }
    
    toggle.addEventListener('click', function() {
      sidebar.classList.toggle('hidden');
      toggle.classList.toggle('shifted');
      
      if (sidebar.classList.contains('hidden')) {
        toggleIcon.classList.remove('fa-bars');
        toggleIcon.classList.add('fa-chevron-right');
        overlay.classList.remove('active');
        if (!isMobile()) {
          localStorage.setItem('adminSidebarHidden', 'true');
          document.body.classList.add('sidebar-closed');
          document.body.classList.remove('sidebar-open');
        }
      } else {
        toggleIcon.classList.remove('fa-chevron-right');
        toggleIcon.classList.add('fa-bars');
        if (isMobile()) {
          overlay.classList.add('active');
        }
        if (!isMobile()) {
          localStorage.setItem('adminSidebarHidden', 'false');
          document.body.classList.add('sidebar-open');
          document.body.classList.remove('sidebar-closed');
        }
      }
    });
    
    // Close sidebar when clicking overlay (mobile only)
    overlay.addEventListener('click', function() {
      if (isMobile() && !sidebar.classList.contains('hidden')) {
        toggle.click();
      }
    });
    
    // Handle window resize
    let resizeTimer;
    window.addEventListener('resize', function() {
      clearTimeout(resizeTimer);
      resizeTimer = setTimeout(function() {
        if (isMobile()) {
          // On mobile, hide sidebar and remove overlay if switching from desktop
          if (!sidebar.classList.contains('hidden')) {
            sidebar.classList.add('hidden');
            toggle.classList.remove('shifted');
            toggleIcon.classList.remove('fa-bars');
            toggleIcon.classList.add('fa-chevron-right');
            overlay.classList.remove('active');
          }
          document.body.classList.remove('sidebar-open', 'sidebar-closed');
        } else {
          // On desktop, restore saved preference and remove overlay
          overlay.classList.remove('active');
          const savedState = localStorage.getItem('adminSidebarHidden') === 'true';
          if (savedState) {
            sidebar.classList.add('hidden');
            toggle.classList.remove('shifted');
            toggleIcon.classList.remove('fa-bars');
            toggleIcon.classList.add('fa-chevron-right');
            document.body.classList.add('sidebar-closed');
            document.body.classList.remove('sidebar-open');
          } else {
            sidebar.classList.remove('hidden');
            toggle.classList.add('shifted');
            toggleIcon.classList.remove('fa-chevron-right');
            toggleIcon.classList.add('fa-bars');
            document.body.classList.add('sidebar-open');
            document.body.classList.remove('sidebar-closed');
          }
        }
      }, 250);
    });
  })();
</script>
