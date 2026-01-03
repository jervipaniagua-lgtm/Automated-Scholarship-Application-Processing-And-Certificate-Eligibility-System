<?php
// sidebar/ssb.php
$current = basename($_SERVER['PHP_SELF']); // ex: dashboard.php

function activeLink(string $file, string $current): string {
  return $file === $current ? "active" : "";
}
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
  /* ✅ Scoped variables so it won't mess with page :root */
  .student-sidebar{
    --sb-primary:#2c3e50;
    --sb-secondary:#3498db;
    --sb-text:#ecf0f1;
    --sb-hover: rgba(255,255,255,0.12);
    --sb-active: rgba(255,255,255,0.20);
  }

  .student-sidebar {
    width: 260px;
    height: 100vh;
    background: var(--sb-primary);
    color: var(--sb-text);
    display: flex;
    flex-direction: column;
    position: sticky;
    top: 0;
    transition: transform 0.3s ease, width 0.3s ease;
    overflow: hidden;
  }

  .student-sidebar.hidden {
    transform: translateX(-260px);
  }

  .student-sidebar .brand {
    padding: 1.4rem 1.3rem;
    display: flex;
    align-items: center;
    gap: .8rem;
    border-bottom: 1px solid rgba(255,255,255,0.12);
  }

  .student-sidebar .brand i {
    font-size: 1.7rem;
    color: var(--sb-secondary);
    line-height: 1;
  }

  .student-sidebar .brand .title {
    font-size: 1.1rem;
    font-weight: 800;
    line-height: 1.1;
  }

  .student-sidebar .brand .sub {
    font-size: .8rem;
    opacity: .75;
    margin-top: 2px;
    line-height: 1.2;
  }

  .student-sidebar .nav {
    flex: 1;
    padding: .8rem 0;
    overflow-y: auto;
  }

  .student-sidebar .section-label {
    font-size: .72rem;
    text-transform: uppercase;
    letter-spacing: .08em;
    opacity: .65;
    padding: .9rem 1.3rem .35rem;
  }

  .student-sidebar a.nav-link {
    display: flex;
    align-items: center;
    gap: .75rem;
    padding: .75rem 1.3rem;
    color: var(--sb-text);
    text-decoration: none;
    font-size: .95rem;
    transition: background .2s, padding-left .2s;
    line-height: 1.2;
  }

  .student-sidebar a.nav-link i {
    width: 18px;
    text-align: center;
    font-size: 1rem;
    opacity: .95;
    line-height: 1;
  }

  .student-sidebar a.nav-link:hover {
    background: var(--sb-hover);
    padding-left: 1.55rem;
  }

  .student-sidebar a.nav-link.active {
    background: var(--sb-active);
    border-left: 4px solid var(--sb-secondary);
    padding-left: 1.1rem;
    font-weight: 700;
  }

  .student-sidebar .nav-link .badge {
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

  /* ✅ Fixed footer like admin */
  .student-sidebar .footer {
    padding: 1rem 1.3rem;
    border-top: 1px solid rgba(255,255,255,0.12);
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: .75rem;
  }

  .student-sidebar .footer .status {
    display: inline-flex;
    align-items: center;
    gap: .5rem;
    font-size: .85rem;
    line-height: 1;
    opacity: .85;
    white-space: nowrap;
  }

  .student-sidebar .footer .dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: #2ecc71;
    flex-shrink: 0;
  }

  .student-sidebar .logout {
    width: 38px;
    height: 38px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 10px;
    background: rgba(255,255,255,0.08);
    border: 1px solid rgba(255,255,255,0.12);
    text-decoration: none;
    color: #ffdddd;
    flex-shrink: 0;
  }

  .student-sidebar .logout i {
    font-size: 16px;
    line-height: 1;
  }

  .student-sidebar .logout:hover {
    background: rgba(255,255,255,0.12);
  }

  .sidebar-toggle {
    position: fixed;
    top: 15px;
    left: 15px;
    width: 45px;
    height: 45px;
    background: var(--sb-primary);
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
    .student-sidebar {
      position: fixed;
      z-index: 1000;
      box-shadow: 2px 0 10px rgba(0,0,0,0.2);
    }

    .student-sidebar.hidden {
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
    .student-sidebar {
      width: 280px;
    }

    .student-sidebar.hidden {
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

<aside class="student-sidebar">
  <div class="brand">
    <i class="fas fa-user-graduate"></i>
    <div>
      <div class="title">Student Panel</div>
      <div class="sub">ScholarManage</div>
    </div>
  </div>

  <nav class="nav">
    <div class="section-label">Overview</div>
    <a href="dashboard.php" class="nav-link <?= activeLink('dashboard.php', $current) ?>">
      <i class="fas fa-house"></i> Dashboard
    </a>

    <div class="section-label">Scholarship</div>
    <a href="apply.php" class="nav-link <?= activeLink('apply.php', $current) ?>">
      <i class="fas fa-paper-plane"></i> Apply Scholarship
    </a>
    <a href="application_status.php" class="nav-link <?= activeLink('application_status.php', $current) ?>">
      <i class="fas fa-clock"></i> Application Status
    </a>

    <div class="section-label">Certificates</div>
    <a href="request_certificate.php" class="nav-link <?= activeLink('request_certificate.php', $current) ?>">
      <i class="fas fa-file-certificate"></i> Request Certificate
    </a>
    <a href="my_certificates.php" class="nav-link <?= activeLink('my_certificates.php', $current) ?>">
      <i class="fas fa-download"></i> My Certificates
    </a>

    <div class="section-label">Account</div>
    <a href="profile.php" class="nav-link <?= activeLink('profile.php', $current) ?>">
      <i class="fas fa-id-card"></i> My Profile
    </a>
 
  </nav>

  <div class="footer">
    <div class="status">
      <span class="dot"></span>
      Logged In
    </div>
    <a class="logout" href="#" id="logoutBtn" title="Logout">
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
    document.getElementById('logoutBtn').addEventListener('click', function(e) {
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
    
    // Notification badge management
    function updateNotificationBadge() {
      // Get unread count from localStorage (in a real app, this would come from the server)
      const unreadCount = parseInt(localStorage.getItem('studentUnreadNotifications') || '3');
      
      const badge = document.getElementById('studentNotifBadge');
      if (badge) {
        if (unreadCount > 0) {
          badge.textContent = unreadCount > 99 ? '99+' : unreadCount;
          badge.style.display = 'inline-block';
        } else {
          badge.style.display = 'none';
        }
      }
    }
    
    // Mark notifications as read when clicking settings
    document.querySelector('a[href="settings.php"]')?.addEventListener('click', function() {
      setTimeout(() => {
        localStorage.setItem('studentUnreadNotifications', '0');
        updateNotificationBadge();
      }, 1000);
    });
    
    // Initialize badge
    updateNotificationBadge();
    
    // Sidebar toggle functionality
    const sidebar = document.querySelector('.student-sidebar');
    const toggle = document.getElementById('sidebarToggle');
    const toggleIcon = toggle.querySelector('i');
    const overlay = document.getElementById('sidebarOverlay');
    
    // Check if mobile
    function isMobile() {
      return window.innerWidth <= 1024;
    }
    
    // Load saved state from localStorage (only for desktop)
    let isHidden = localStorage.getItem('studentSidebarHidden') === 'true';
    
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
          localStorage.setItem('studentSidebarHidden', 'true');
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
          localStorage.setItem('studentSidebarHidden', 'false');
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
          const savedState = localStorage.getItem('studentSidebarHidden') === 'true';
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
