<?php
session_start();
require_once "../conn.php";

if (!isset($_SESSION['user_id'])) { header("Location: ../index.php"); exit; }
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') { header("Location: ../admin/dashboard.php"); exit; }

$user_id = (int)$_SESSION['user_id'];
$hasScholar = isset($_SESSION['has_scholarship']) ? (int)$_SESSION['has_scholarship'] : 0;

$msg = "";

// Block if student HAS scholarship (inverted logic from before)
if ($hasScholar === 1) {
  $msg = "You have an active scholarship. Certificate request is not allowed for active scholars.";
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && $hasScholar === 0) {

  // Check pending request
  $check = mysqli_query($conn, "SELECT id FROM certificate_requests WHERE user_id=$user_id AND status='pending' LIMIT 1");
  if ($check && mysqli_num_rows($check) > 0) {
    $msg = "You already have a pending certificate request.";
  } else {
    $stmt = mysqli_prepare($conn, "INSERT INTO certificate_requests (user_id, status, requested_at) VALUES (?, 'pending', NOW())");
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    
    if (mysqli_stmt_execute($stmt)) {
      $cert_id = mysqli_insert_id($conn);
      mysqli_stmt_close($stmt);

      // Create notification for admin
      $studentName = $_SESSION['name'] ?? 'Student';
      $notifMsg = "New Certification Request: " . $studentName;
      
      // Get first admin user
      $adminRes = mysqli_query($conn, "SELECT id FROM users WHERE role='admin' LIMIT 1");
      if ($adminRes && $adminRow = mysqli_fetch_assoc($adminRes)) {
        $admin_id = (int)$adminRow['id'];
        $notifStmt = mysqli_prepare($conn, "INSERT INTO notifications (admin_id, type, message, ref_table, ref_id, is_read, created_at) VALUES (?, 'certificate', ?, 'certificate_requests', ?, 0, NOW())");
        mysqli_stmt_bind_param($notifStmt, "isi", $admin_id, $notifMsg, $cert_id);
        mysqli_stmt_execute($notifStmt);
        mysqli_stmt_close($notifStmt);
      }

      $msg = "Certificate request submitted successfully! Status: Pending.";
    } else {
      $msg = "Failed to submit request.";
      mysqli_stmt_close($stmt);
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Request Certificate | Student</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    :root{--primary:#2c3e50;--secondary:#3498db;--bg:#f4f6f8;--card:#fff;--muted:#6b7280;--border:#e5e7eb;}
    *{margin:0;padding:0;box-sizing:border-box;font-family:'Segoe UI',Tahoma,Verdana,sans-serif;}
    body{background:var(--bg);color:#111827;}
    .layout{display:flex;min-height:100vh;}
    .content{flex:1;padding:24px;}
    .topbar{background:var(--card);border:1px solid var(--border);border-radius:12px;padding:16px 18px;margin-bottom:18px;}
    .panel{background:var(--card);border:1px solid var(--border);border-radius:12px;padding:16px;}
    .msg{margin-bottom:12px;font-weight:800;color:var(--muted);}
    .warn{color:#b91c1c;}
    .btn{
      display:inline-flex;align-items:center;gap:8px;text-decoration:none;
      padding:10px 12px;border-radius:10px;border:1px solid var(--border);
      background:#fff;color:var(--primary);font-weight:800;font-size:13px;
      cursor:pointer;
    }
    .btn:hover{border-color:#cbd5e1;}
  </style>
</head>
<body>
<div class="layout">
  <?php include "../sidebar/ssb.php"; ?>

  <main class="content">
    <div class="topbar">
      <h2 style="color:var(--primary);"><i class="fas fa-file-certificate"></i> Request Certificate</h2>
      <div style="color:var(--muted);font-size:13px;margin-top:4px;">
        Certificate is allowed only if you have NO active scholarship.
      </div>
    </div>

    <div class="panel">
      <?php if($msg): ?>
        <div class="msg <?php echo ($hasScholar===1) ? 'warn' : ''; ?>"><?php echo htmlspecialchars($msg); ?></div>
      <?php endif; ?>

      <?php if($hasScholar === 1): ?>
        <div style="padding:20px;text-align:center;color:#b91c1c;">
          <i class="fas fa-ban" style="font-size:3rem;margin-bottom:12px;"></i>
          <h3>Certificate Request Not Allowed</h3>
          <p style="margin-top:8px;color:var(--muted);">You currently have an active scholarship. Only students WITHOUT scholarships can request certificates.</p>
        </div>
      <?php else: ?>
        <div style="margin-bottom:18px;padding:12px;background:#e6fffb;border:1px solid #87e8de;border-radius:8px;color:#08979c;">
          <i class="fas fa-check-circle"></i> <strong>You are eligible</strong> to request a certificate (No active scholarship)
        </div>
        
        <form method="POST">
          <p style="color:var(--muted);margin-bottom:14px;line-height:1.6;">
            This certificate will verify that you <strong>do not have an active scholarship</strong>. 
            The admin will review your request and generate a PDF certificate with verification code.
          </p>
          
          <button class="btn" type="submit">
            <i class="fas fa-paper-plane"></i> Submit Certificate Request
          </button>
        </form>
      <?php endif; ?>
    </div>
  </main>
</div>

<script>
  <?php if ($msg !== ""): ?>
    <?php if (strpos($msg, 'successfully') !== false): ?>
      Swal.fire({
        icon: 'success',
        title: 'Request Submitted!',
        text: '<?= addslashes($msg) ?>',
        confirmButtonColor: '#3498db'
      });
    <?php elseif ($hasScholar === 1): ?>
      Swal.fire({
        icon: 'warning',
        title: 'Not Allowed',
        text: '<?= addslashes($msg) ?>',
        confirmButtonColor: '#3498db'
      });
    <?php else: ?>
      Swal.fire({
        icon: 'error',
        title: 'Request Error',
        text: '<?= addslashes($msg) ?>',
        confirmButtonColor: '#3498db'
      });
    <?php endif; ?>
  <?php endif; ?>
</script>
</body>
</html>
