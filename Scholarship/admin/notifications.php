<?php
session_start();
require_once "../conn.php";

if (!isset($_SESSION['user_id'])) { header("Location: ../index.php"); exit; }
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') { header("Location: ../student/dashboard.php"); exit; }

$admin_id = (int)$_SESSION['user_id'];

$res = mysqli_query($conn, "
  SELECT id, type, message, is_read, created_at, ref_table, ref_id
  FROM notifications
  WHERE admin_id = $admin_id
  ORDER BY created_at DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Notifications | Admin</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root{--primary:#2c3e50;--secondary:#3498db;--bg:#f4f6f8;--card:#fff;--muted:#6b7280;--border:#e5e7eb;}
    *{margin:0;padding:0;box-sizing:border-box;font-family:'Segoe UI',Tahoma,Verdana,sans-serif;}
    body{background:var(--bg);color:#111827;}
    .layout{display:flex;min-height:100vh;}
    .content{flex:1;padding:24px;}
    .topbar{background:var(--card);border:1px solid var(--border);border-radius:12px;padding:16px 18px;margin-bottom:18px;display:flex;justify-content:space-between;align-items:center;}
    .topbar h1{font-size:18px;color:var(--primary);}
    .panel{background:var(--card);border:1px solid var(--border);border-radius:12px;padding:16px;}
    .item{
      padding:12px 10px;
      border-bottom:1px solid var(--border);
      display:flex;
      justify-content:space-between;
      gap:14px;
      transition:background .2s;
    }
    .item:hover{background:#fafafa;}
    .item:last-child{border-bottom:none;}
    .msg{font-weight:700;color:#111827;}
    .meta{font-size:12px;color:var(--muted);margin-top:4px;}
    .pill{padding:3px 10px;border-radius:999px;font-size:12px;font-weight:700;background:#e0f2fe;color:#0369a1;}
    .unread{background:#fffbeb;border-left:4px solid #f59e0b;}
    .unread .msg{color:var(--primary);font-weight:800;}
    .link{display:inline-flex;align-items:center;gap:6px;margin-top:8px;font-size:12px;color:var(--secondary);text-decoration:none;font-weight:700;}
    .link:hover{text-decoration:underline;}
    .mark-read-btn{
      background:#10b981;
      color:white;
      border:none;
      padding:6px 12px;
      border-radius:6px;
      font-size:11px;
      font-weight:700;
      cursor:pointer;
      transition:all 0.2s;
      display:inline-flex;
      align-items:center;
      gap:5px;
    }
    .mark-read-btn:hover{
      background:#059669;
      transform:scale(1.05);
    }
    .mark-all-btn{
      background:var(--secondary);
      color:white;
      border:none;
      padding:8px 16px;
      border-radius:6px;
      font-size:13px;
      font-weight:700;
      cursor:pointer;
      transition:all 0.2s;
      display:inline-flex;
      align-items:center;
      gap:6px;
    }
    .mark-all-btn:hover{
      background:#2980b9;
      transform:translateY(-2px);
      box-shadow:0 4px 8px rgba(0,0,0,0.15);
    }
    .actions{
      display:flex;
      flex-direction:column;
      gap:8px;
      align-items:flex-end;
    }
  </style>
</head>
<body>
<div class="layout">
  <?php include "../sidebar/asb.php"; ?>
  <main class="content">
    <div class="topbar">
      <h1><i class="fas fa-bell"></i> Notifications</h1>
      <div style="display:flex;align-items:center;gap:16px;">
        <div style="color:var(--muted);font-size:13px;">Admin alerts</div>
        <button class="mark-all-btn" onclick="markAllAsRead()">
          <i class="fas fa-check-double"></i> Mark All as Read
        </button>
      </div>
    </div>

    <div class="panel">
      <?php if ($res && mysqli_num_rows($res) > 0): ?>
        <?php while($r = mysqli_fetch_assoc($res)): 
          $unread = ((int)$r['is_read'] === 0);
          $type = $r['type'];
          $ref_id = (int)$r['ref_id'];
          
          // Generate link based on type
          $link = '';
          if ($type === 'application' && $ref_id > 0) {
            $link = 'view_application.php?id=' . $ref_id;
          } elseif ($type === 'certificate' && $ref_id > 0) {
            $link = 'generate_certificate.php?id=' . $ref_id;
          }
        ?>
          <div class="item <?php echo $unread ? 'unread' : ''; ?>" data-notif-id="<?php echo $r['id']; ?>">
            <div style="flex:1;">
              <div class="msg">
                <?php if ($unread): ?><i class="fas fa-circle" style="font-size:8px;color:#f59e0b;"></i> <?php endif; ?>
                <?php echo htmlspecialchars($r['message']); ?>
              </div>
              <div class="meta"><?php echo htmlspecialchars(date("M d, Y h:i A", strtotime($r['created_at']))); ?></div>
              <?php if ($link): ?>
                <a href="<?php echo htmlspecialchars($link); ?>" class="link">
                  <i class="fas fa-external-link-alt"></i> View Details
                </a>
              <?php endif; ?>
            </div>
            <div class="actions">
              <div class="pill"><?php echo htmlspecialchars(ucfirst($type)); ?></div>
              <?php if ($unread): ?>
                <button class="mark-read-btn" onclick="markAsRead(<?php echo $r['id']; ?>)">
                  <i class="fas fa-check"></i> Mark Read
                </button>
              <?php endif; ?>
            </div>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <div style="padding:40px;text-align:center;color:var(--muted);">
          <i class="fas fa-bell-slash" style="font-size:3rem;margin-bottom:12px;opacity:0.3;"></i>
          <p>No notifications yet.</p>
        </div>
      <?php endif; ?>
    </div>
  </main>
</div>

<script>
function markAsRead(notifId) {
  fetch('mark_notification_read.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: 'notif_id=' + notifId
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      const item = document.querySelector(`[data-notif-id="${notifId}"]`);
      if (item) {
        item.classList.remove('unread');
        const btn = item.querySelector('.mark-read-btn');
        if (btn) btn.remove();
        const icon = item.querySelector('.fa-circle');
        if (icon) icon.remove();
      }
      updateBadgeCount();
    }
  })
  .catch(err => console.error('Error:', err));
}

function markAllAsRead() {
  if (!confirm('Mark all notifications as read?')) return;
  
  fetch('mark_notification_read.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: 'mark_all=1'
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      location.reload();
    }
  })
  .catch(err => console.error('Error:', err));
}

function updateBadgeCount() {
  const unreadCount = document.querySelectorAll('.item.unread').length;
  localStorage.setItem('adminUnreadNotifications', unreadCount);
  
  // Update sidebar badge if exists
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

// Update badge count on page load
window.addEventListener('load', updateBadgeCount);
</script>
</body>
</html>
