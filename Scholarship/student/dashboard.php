<?php
session_start();
require_once "../conn.php";

if (!isset($_SESSION['user_id'])) { header("Location: ../index.php"); exit; }
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') { header("Location: ../admin/dashboard.php"); exit; }

$user_id = (int)$_SESSION['user_id'];

// scholarship status (prefer session but also safe check)
$hasScholar = isset($_SESSION['has_scholarship']) ? (int)$_SESSION['has_scholarship'] : 0;

$appRes = mysqli_query($conn, "SELECT status, submitted_at FROM scholarship_applications WHERE user_id=$user_id ORDER BY submitted_at DESC LIMIT 1");
$latestApp = $appRes ? mysqli_fetch_assoc($appRes) : null;

$certRes = mysqli_query($conn, "SELECT status, requested_at FROM certificate_requests WHERE user_id=$user_id ORDER BY requested_at DESC LIMIT 1");
$latestCert = $certRes ? mysqli_fetch_assoc($certRes) : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Student Dashboard | ScholarManage</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    :root{--primary:#2c3e50;--secondary:#3498db;--bg:#f4f6f8;--card:#fff;--muted:#6b7280;--border:#e5e7eb;}
    *{margin:0;padding:0;box-sizing:border-box;font-family:'Segoe UI',Tahoma,Verdana,sans-serif;}
    body{background:var(--bg);color:#111827;}
    .layout{display:flex;min-height:100vh;}
    .content{flex:1;padding:24px;}
    .topbar{background:var(--card);border:1px solid var(--border);border-radius:12px;padding:16px 18px;margin-bottom:18px;display:flex;justify-content:space-between;align-items:center;}
    .topbar h1{font-size:18px;color:var(--primary);}
    .who{display:flex;align-items:center;gap:10px;color:var(--muted);font-size:14px;}
    .avatar{width:34px;height:34px;border-radius:50%;background:var(--secondary);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:800;}
    .grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:14px;margin-bottom:18px;}
    .card{background:var(--card);border:1px solid var(--border);border-radius:12px;padding:16px;box-shadow:0 2px 8px rgba(0,0,0,.03);}
    .label{font-size:13px;color:var(--muted);font-weight:700;}
    .value{font-size:20px;font-weight:800;margin-top:6px;color:var(--primary);}
    .hint{margin-top:10px;font-size:12px;color:var(--muted);line-height:1.5;}
    .btn{
      display:inline-flex;align-items:center;gap:8px;text-decoration:none;
      padding:10px 12px;border-radius:10px;border:1px solid var(--border);
      background:#fff;color:var(--primary);font-weight:800;font-size:13px;
    }
    .btn:hover{border-color:#cbd5e1;}
    .badge{display:inline-block;padding:3px 10px;border-radius:999px;font-size:12px;font-weight:800;}
    .b-pending{background:#fff7e6;color:#d48806;}
    .b-approved{background:#e6fffb;color:#08979c;}
    .b-rejected{background:#fff1f0;color:#cf1322;}
    .b-none{background:#eef2ff;color:#3730a3;}
  </style>
</head>
<body>
<div class="layout">
  <?php include "../sidebar/ssb.php"; ?>

  <main class="content">
    <div class="topbar">
      <div>
        <h1>Student Dashboard</h1>
        <div style="margin-top:4px;color:var(--muted);font-size:13px;">Welcome back, <?php echo htmlspecialchars($_SESSION['name'] ?? 'Student'); ?>.</div>
      </div>
      <div class="who">
        <div class="avatar"><?php echo strtoupper(substr($_SESSION['name'] ?? 'S',0,1)); ?></div>
        <div>
          <div style="font-weight:800;color:#111827;"><?php echo htmlspecialchars($_SESSION['name'] ?? 'Student'); ?></div>
          <div style="font-size:12px;"><?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?></div>
        </div>
      </div>
    </div>

    <section class="grid">
      <div class="card">
        <div class="label">Scholarship Status</div>
        <div class="value">
          <?php if($hasScholar): ?>
            <span class="badge b-approved">Active Scholar</span>
          <?php else: ?>
            <span class="badge b-none">Not a Scholar</span>
          <?php endif; ?>
        </div>
        <div class="hint">If you are an active scholar, certificate requests are blocked.</div>
      </div>

      <div class="card">
        <div class="label">Latest Application</div>
        <div class="value">
          <?php
            if ($latestApp) {
              $st = $latestApp['status'];
              $cls = $st==='approved'?'b-approved':($st==='rejected'?'b-rejected':'b-pending');
              echo '<span class="badge '.$cls.'">'.htmlspecialchars(ucfirst($st)).'</span>';
            } else {
              echo '<span class="badge b-none">No Application</span>';
            }
          ?>
        </div>
        <div class="hint">
          <?php if($latestApp): ?>
            Submitted: <?php echo htmlspecialchars(date("M d, Y", strtotime($latestApp['submitted_at']))); ?>
          <?php else: ?>
            You can apply for scholarship anytime (if not already active scholar).
          <?php endif; ?>
        </div>
        <div style="margin-top:10px;">
          <a class="btn" href="apply.php"><i class="fas fa-paper-plane"></i> Apply Scholarship</a>
        </div>
      </div>

      <div class="card">
        <div class="label">Latest Certificate Request</div>
        <div class="value">
          <?php
            if ($latestCert) {
              $st = $latestCert['status'];
              $cls = $st==='generated'?'b-approved':($st==='rejected'?'b-rejected':'b-pending');
              echo '<span class="badge '.$cls.'">'.htmlspecialchars(ucfirst($st)).'</span>';
            } else {
              echo '<span class="badge b-none">None</span>';
            }
          ?>
        </div>
        <div class="hint">
          <?php if($latestCert): ?>
            Requested: <?php echo htmlspecialchars(date("M d, Y", strtotime($latestCert['requested_at']))); ?>
          <?php else: ?>
            You can request a certificate if you have no active scholarship.
          <?php endif; ?>
        </div>
        <div style="margin-top:10px;">
          <a class="btn" href="request_certificate.php"><i class="fas fa-file-certificate"></i> Request Certificate</a>
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
