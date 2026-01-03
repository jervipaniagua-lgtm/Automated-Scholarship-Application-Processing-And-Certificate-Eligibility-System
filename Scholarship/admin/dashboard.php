<?php
// admin/dashboard.php
session_start();
require_once "../conn.php";

// ✅ block if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

// ✅ block if not admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../student/dashboard.php");
    exit;
}

// Helper to fetch single count
function get_count(mysqli $conn, string $sql): int {
    $res = mysqli_query($conn, $sql);
    if (!$res) return 0;
    $row = mysqli_fetch_row($res);
    return (int)($row[0] ?? 0);
}

// Dashboard numbers
$pendingApps = get_count($conn, "SELECT COUNT(*) FROM scholarship_applications WHERE status='pending'");
$pendingCerts = get_count($conn, "SELECT COUNT(*) FROM certificate_requests WHERE status='pending'");
$activeScholars = get_count($conn, "SELECT COUNT(*) FROM users WHERE role='student' AND has_scholarship=1");
$totalStudents = get_count($conn, "SELECT COUNT(*) FROM users WHERE role='student'");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Admin Dashboard | ScholarManage</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <style>
    :root{
      --primary:#2c3e50;
      --secondary:#3498db;
      --bg:#f4f6f8;
      --card:#ffffff;
      --muted:#6b7280;
      --border:#e5e7eb;
    }
    *{margin:0;padding:0;box-sizing:border-box;font-family:'Segoe UI',Tahoma,Verdana,sans-serif;}
    body{background:var(--bg); color:#111827;}

    /* Layout */
    .layout{display:flex; min-height:100vh;}
    .content{flex:1; padding:24px;}
    .topbar{
      background:var(--card);
      border:1px solid var(--border);
      border-radius:12px;
      padding:16px 18px;
      display:flex;
      justify-content:space-between;
      align-items:center;
      margin-bottom:18px;
    }
    .topbar h1{font-size:18px; color:var(--primary);}
    .topbar .who{
      display:flex; align-items:center; gap:10px; color:var(--muted); font-size:14px;
    }
    .avatar{
      width:34px; height:34px; border-radius:50%;
      background:var(--secondary); color:white;
      display:flex; align-items:center; justify-content:center;
      font-weight:700;
    }
    .logout{
      display:inline-flex; align-items:center; gap:8px;
      text-decoration:none;
      padding:10px 12px;
      border-radius:10px;
      border:1px solid var(--border);
      color:#111827;
      background:#fff;
      font-weight:600;
      font-size:14px;
    }
    .logout:hover{border-color:#cbd5e1;}

    /* Cards */
    .grid{
      display:grid;
      grid-template-columns:repeat(auto-fit, minmax(220px, 1fr));
      gap:14px;
      margin-bottom:18px;
    }
    .card{
      background:var(--card);
      border:1px solid var(--border);
      border-radius:12px;
      padding:16px;
      box-shadow:0 2px 8px rgba(0,0,0,.03);
    }
    .card .row{display:flex; justify-content:space-between; align-items:flex-start;}
    .card .label{font-size:13px; color:var(--muted); font-weight:600;}
    .card .value{font-size:28px; font-weight:800; margin-top:6px; color:var(--primary);}
    .card .icon{
      width:38px; height:38px; border-radius:10px;
      background:rgba(52,152,219,.12);
      display:flex; align-items:center; justify-content:center;
      color:var(--secondary);
      font-size:16px;
    }
    .card .hint{margin-top:10px; font-size:12px; color:var(--muted);}

    /* Tables */
    .two-col{
      display:grid;
      grid-template-columns:1.2fr .8fr;
      gap:14px;
    }
    .panel{
      background:var(--card);
      border:1px solid var(--border);
      border-radius:12px;
      padding:16px;
    }
    .panel h2{
      font-size:15px;
      color:var(--primary);
      margin-bottom:12px;
      display:flex;
      align-items:center;
      gap:8px;
    }
    table{width:100%; border-collapse:collapse; font-size:13px;}
    th, td{padding:10px 8px; border-bottom:1px solid var(--border); text-align:left;}
    th{color:var(--muted); font-weight:700; background:#fafafa;}
    .badge{
      display:inline-block;
      padding:3px 10px;
      border-radius:999px;
      font-size:12px;
      font-weight:700;
    }
    .b-pending{background:#fff7e6; color:#d48806;}
    .b-approved{background:#e6fffb; color:#08979c;}
    .b-rejected{background:#fff1f0; color:#cf1322;}

    .link-btn{
      display:inline-flex; align-items:center; gap:8px;
      text-decoration:none;
      padding:10px 12px;
      border-radius:10px;
      border:1px solid var(--border);
      background:#fff;
      font-weight:700;
      font-size:13px;
      color:var(--primary);
    }
    .link-btn:hover{border-color:#cbd5e1;}

    @media (max-width: 980px){
      .two-col{grid-template-columns:1fr;}
    }
  </style>
</head>
<body>

  <div class="layout">
    <!-- ✅ SIDEBAR INCLUDE -->
    <?php include "../sidebar/asb.php"; ?>

    <main class="content">
      <div class="topbar">
        <div>
          <h1>Admin Dashboard</h1>
          <div style="margin-top:4px;color:#6b7280;font-size:13px;">
            Monitor applications, requests, and scholar status.
          </div>
        </div>

        <div class="who">
          <div class="avatar"><?php echo strtoupper(substr($_SESSION['name'] ?? 'A', 0, 1)); ?></div>
          <div>
            <div style="font-weight:700;color:#111827;"><?php echo htmlspecialchars($_SESSION['name'] ?? 'Admin'); ?></div>
            <div style="font-size:12px;"><?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?></div>
          </div>
          <a class="logout" href="../logout.php" title="Logout">
            <i class="fas fa-right-from-bracket"></i> Logout
          </a>
        </div>
      </div>

      <!-- Cards -->
      <section class="grid">
        <div class="card">
          <div class="row">
            <div>
              <div class="label">Pending Applications</div>
              <div class="value"><?php echo $pendingApps; ?></div>
            </div>
            <div class="icon"><i class="fas fa-file-signature"></i></div>
          </div>
          <div class="hint">Students waiting for review.</div>
        </div>

        <div class="card">
          <div class="row">
            <div>
              <div class="label">Pending Certificate Requests</div>
              <div class="value"><?php echo $pendingCerts; ?></div>
            </div>
            <div class="icon"><i class="fas fa-certificate"></i></div>
          </div>
          <div class="hint">Eligible requests for PDF generation.</div>
        </div>

        <div class="card">
          <div class="row">
            <div>
              <div class="label">Active Scholars</div>
              <div class="value"><?php echo $activeScholars; ?></div>
            </div>
            <div class="icon"><i class="fas fa-user-graduate"></i></div>
          </div>
          <div class="hint">Students with active scholarship.</div>
        </div>

        <div class="card">
          <div class="row">
            <div>
              <div class="label">Total Students</div>
              <div class="value"><?php echo $totalStudents; ?></div>
            </div>
            <div class="icon"><i class="fas fa-users"></i></div>
          </div>
          <div class="hint">Registered student accounts.</div>
        </div>
      </section>

      <!-- Recent lists -->
      <section class="two-col">
        <div class="panel">
          <h2><i class="fas fa-clock"></i> Recent Scholarship Applications</h2>
          <table>
            <thead>
              <tr>
                <th>Student</th>
                <th>Status</th>
                <th>Date</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $sql = "
                SELECT a.status, a.submitted_at, u.name
                FROM scholarship_applications a
                JOIN users u ON u.id = a.user_id
                ORDER BY a.submitted_at DESC
                LIMIT 6
              ";
              $res = mysqli_query($conn, $sql);
              if ($res && mysqli_num_rows($res) > 0):
                while ($r = mysqli_fetch_assoc($res)):
                  $st = $r['status'];
                  $cls = $st === 'approved' ? 'b-approved' : ($st === 'rejected' ? 'b-rejected' : 'b-pending');
              ?>
                <tr>
                  <td><?php echo htmlspecialchars($r['name']); ?></td>
                  <td><span class="badge <?php echo $cls; ?>"><?php echo htmlspecialchars(ucfirst($st)); ?></span></td>
                  <td><?php echo htmlspecialchars(date("M d, Y", strtotime($r['submitted_at']))); ?></td>
                </tr>
              <?php endwhile; else: ?>
                <tr><td colspan="3">No applications yet.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>

          <div style="margin-top:12px;">
            <a class="link-btn" href="applications.php"><i class="fas fa-arrow-right"></i> View All Applications</a>
          </div>
        </div>

        <div class="panel">
          <h2><i class="fas fa-bolt"></i> Quick Actions</h2>

          <div style="display:flex; flex-direction:column; gap:10px;">
            <a class="link-btn" href="applications.php"><i class="fas fa-file-signature"></i> Review Applications</a>
            <a class="link-btn" href="certificate_requests.php"><i class="fas fa-certificate"></i> Process Certificates</a>
            <a class="link-btn" href="active_scholars.php"><i class="fas fa-user-graduate"></i> View Active Scholars</a>
            <a class="link-btn" href="notifications.php"><i class="fas fa-bell"></i> Notifications</a>
          </div>

          <div style="margin-top:14px;color:var(--muted);font-size:12px;line-height:1.5;">
            Tip: Approval should be blocked if the student already has an active scholarship.
          </div>
        </div>
      </section>

    </main>
  </div>
  
  <script>
    <?php if (isset($_SESSION['success'])): ?>
      Swal.fire({
        icon: 'success',
        title: 'Welcome!',
        text: '<?php echo addslashes($_SESSION['success']); ?>',
        confirmButtonColor: '#3498db',
        timer: 3000,
        timerProgressBar: true
      });
      <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
  </script>
</body>
</html>
