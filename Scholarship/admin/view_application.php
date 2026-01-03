<?php
session_start();
require_once "../conn.php";

if (!isset($_SESSION['user_id'])) { header("Location: ../index.php"); exit; }
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') { header("Location: ../student/dashboard.php"); exit; }

$app_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$success_msg = $_SESSION['success'] ?? '';
$error_msg = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);

// Get application details with user info
$res = mysqli_query($conn, "
  SELECT a.*, u.name, u.student_id, u.course, u.year_level, u.email, u.has_scholarship
  FROM scholarship_applications a
  JOIN users u ON u.id = a.user_id
  WHERE a.id = $app_id
  LIMIT 1
");

$app = $res ? mysqli_fetch_assoc($res) : null;

if (!$app) {
  die("Application not found.");
}

// Check if student already has scholarship
$studentHasScholarship = (int)$app['has_scholarship'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>View Application #<?= $app_id ?> | Admin</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root{--primary:#2c3e50;--secondary:#3498db;--bg:#f4f6f8;--card:#fff;--muted:#6b7280;--border:#e5e7eb;}
    *{margin:0;padding:0;box-sizing:border-box;font-family:'Segoe UI',Tahoma,Verdana,sans-serif;}
    body{background:var(--bg);color:#111827;}
    .layout{display:flex;min-height:100vh;}
    .content{flex:1;padding:24px;}
    .topbar{background:var(--card);border:1px solid var(--border);border-radius:12px;padding:16px 18px;margin-bottom:18px;display:flex;justify-content:space-between;align-items:center;}
    .topbar h1{font-size:18px;color:var(--primary);}
    .back{display:inline-flex;align-items:center;gap:8px;text-decoration:none;padding:8px 12px;border:1px solid var(--border);border-radius:10px;background:#fff;color:var(--primary);font-weight:700;font-size:12px;}
    .back:hover{border-color:#cbd5e1;}
    .panel{background:var(--card);border:1px solid var(--border);border-radius:12px;padding:20px;margin-bottom:18px;}
    .section-title{font-size:15px;font-weight:800;color:var(--primary);margin-bottom:14px;padding-bottom:8px;border-bottom:2px solid var(--border);}
    .row{display:grid;grid-template-columns:200px 1fr;gap:12px;padding:10px 0;border-bottom:1px solid var(--border);}
    .row:last-child{border-bottom:none;}
    .label{color:var(--muted);font-weight:700;}
    .value{font-weight:700;color:var(--primary);}
    .badge{display:inline-block;padding:4px 12px;border-radius:999px;font-size:12px;font-weight:800;}
    .b-pending{background:#fff7e6;color:#d48806;}
    .b-approved{background:#e6fffb;color:#08979c;}
    .b-rejected{background:#fff1f0;color:#cf1322;}
    .alert{padding:14px;border-radius:10px;margin-bottom:18px;font-weight:700;}
    .alert-warning{background:#fff7e6;color:#d48806;border:1px solid #ffe7ba;}
    .alert-danger{background:#fff1f0;color:#cf1322;border:1px solid #ffccc7;}
    .alert-success{background:#e6fffb;color:#08979c;border:1px solid #87e8de;}
    .actions{display:flex;gap:10px;margin-top:20px;}
    .btn{display:inline-flex;align-items:center;gap:8px;padding:12px 18px;border-radius:10px;border:none;font-weight:800;font-size:14px;cursor:pointer;text-decoration:none;}
    .btn-approve{background:#52c41a;color:#fff;}
    .btn-approve:hover{background:#389e0d;}
    .btn-reject{background:#ff4d4f;color:#fff;}
    .btn-reject:hover{background:#cf1322;}
    .btn-approve:disabled, .btn-reject:disabled{opacity:0.5;cursor:not-allowed;}
    .doc-link{display:inline-flex;align-items:center;gap:6px;padding:8px 12px;background:#f0f0f0;border:1px solid var(--border);border-radius:8px;text-decoration:none;color:var(--primary);font-weight:700;font-size:13px;margin-right:8px;}
    .doc-link:hover{border-color:#cbd5e1;background:#e8e8e8;}
    .doc-link i{color:var(--secondary);}
  </style>
</head>
<body>
<div class="layout">
  <?php include "../sidebar/asb.php"; ?>
  <main class="content">
    <div class="topbar">
      <h1><i class="fas fa-file-alt"></i> Application #<?= $app_id ?></h1>
      <a href="applications.php" class="back"><i class="fas fa-arrow-left"></i> Back to Applications</a>
    </div>

    <?php if ($success_msg): ?>
      <div class="alert alert-success">
        <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success_msg) ?>
      </div>
    <?php endif; ?>

    <?php if ($error_msg): ?>
      <div class="alert alert-danger">
        <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($error_msg) ?>
      </div>
    <?php endif; ?>

    <?php if ($studentHasScholarship === 1): ?>
      <div class="alert alert-danger">
        <i class="fas fa-exclamation-triangle"></i> <strong>Cannot Approve:</strong> This student already has an active scholarship. The system automatically blocks approval.
      </div>
    <?php elseif ($app['status'] === 'pending'): ?>
      <div class="alert alert-success">
        <i class="fas fa-check-circle"></i> <strong>Eligible:</strong> This student does NOT have a scholarship and can be approved.
      </div>
    <?php endif; ?>

    <!-- Student Information -->
    <div class="panel">
      <div class="section-title"><i class="fas fa-user"></i> Student Information</div>
      <div class="row"><div class="label">Name</div><div class="value"><?= htmlspecialchars($app['name']) ?></div></div>
      <div class="row"><div class="label">Student ID</div><div class="value"><?= htmlspecialchars($app['student_id']) ?></div></div>
      <div class="row"><div class="label">Course</div><div class="value"><?= htmlspecialchars($app['course']) ?></div></div>
      <div class="row"><div class="label">Year Level</div><div class="value"><?= htmlspecialchars($app['year_level']) ?></div></div>
      <div class="row"><div class="label">Email</div><div class="value"><?= htmlspecialchars($app['email']) ?></div></div>
      <div class="row">
        <div class="label">Scholarship Status</div>
        <div class="value">
          <?php if ($studentHasScholarship === 1): ?>
            <span style="color:#cf1322;"><i class="fas fa-check-circle"></i> HAS SCHOLARSHIP</span>
          <?php else: ?>
            <span style="color:#08979c;"><i class="fas fa-times-circle"></i> NO SCHOLARSHIP</span>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Application Details -->
    <div class="panel">
      <div class="section-title"><i class="fas fa-file-signature"></i> Application Details</div>
      <div class="row"><div class="label">Application ID</div><div class="value">#<?= $app['id'] ?></div></div>
      <div class="row"><div class="label">Scholarship Type</div><div class="value"><span style="background:#e0f2fe;color:#0369a1;padding:4px 12px;border-radius:999px;font-size:12px;font-weight:700;"><?= htmlspecialchars($app['scholarship_type'] ?? 'Academic') ?></span></div></div>
      <div class="row"><div class="label">Status</div><div class="value"><span class="badge b-<?= $app['status'] ?>"><?= strtoupper($app['status']) ?></span></div></div>
      <div class="row"><div class="label">Submitted At</div><div class="value"><?= date('M d, Y h:i A', strtotime($app['submitted_at'])) ?></div></div>
      <div class="row"><div class="label">Purpose/Reason</div><div class="value"><?= nl2br(htmlspecialchars($app['purpose'])) ?></div></div>
    </div>

    <!-- Uploaded Documents -->
    <div class="panel">
      <div class="section-title"><i class="fas fa-folder-open"></i> Uploaded Documents</div>
      <div style="padding:10px 0;">
        <?php if (!empty($app['cor_file'])): ?>
          <a href="../uploads/applications/<?= htmlspecialchars($app['cor_file']) ?>" target="_blank" class="doc-link">
            <i class="fas fa-file-pdf"></i> Certificate of Registration (COR)
          </a>
        <?php else: ?>
          <span style="color:var(--muted);">No COR uploaded</span>
        <?php endif; ?>
        
        <?php if (!empty($app['grades_file'])): ?>
          <a href="../uploads/applications/<?= htmlspecialchars($app['grades_file']) ?>" target="_blank" class="doc-link">
            <i class="fas fa-file-pdf"></i> Grades/Transcript
          </a>
        <?php else: ?>
          <span style="color:var(--muted);">No Grades uploaded</span>
        <?php endif; ?>
        
        <?php if (!empty($app['id_file'])): ?>
          <a href="../uploads/applications/<?= htmlspecialchars($app['id_file']) ?>" target="_blank" class="doc-link">
            <i class="fas fa-file-pdf"></i> Valid ID
          </a>
        <?php else: ?>
          <span style="color:var(--muted);">No ID uploaded</span>
        <?php endif; ?>
      </div>
    </div>

    <!-- Actions -->
    <?php if ($app['status'] === 'pending'): ?>
    <div class="panel">
      <div class="section-title"><i class="fas fa-cogs"></i> Actions</div>
      <form method="POST" action="process_application.php" onsubmit="return confirm('Are you sure you want to perform this action?');">
        <input type="hidden" name="app_id" value="<?= $app_id ?>">
        <div class="actions">
          <button type="submit" name="action" value="approve" class="btn btn-approve" <?= $studentHasScholarship === 1 ? 'disabled' : '' ?> onclick="return confirm('Are you sure you want to APPROVE this application?');">
            <i class="fas fa-check"></i> Approve Application
          </button>
          <button type="submit" name="action" value="reject" class="btn btn-reject" onclick="return confirm('Are you sure you want to REJECT this application?');">
            <i class="fas fa-times"></i> Reject Application
          </button>
        </div>
        <?php if ($studentHasScholarship === 1): ?>
          <p style="color:#cf1322;margin-top:12px;font-size:13px;">
            <i class="fas fa-info-circle"></i> Approval is disabled because the student already has an active scholarship.
          </p>
        <?php endif; ?>
      </form>
    </div>
    <?php else: ?>
      <div class="alert alert-warning">
        <i class="fas fa-info-circle"></i> This application has already been processed. Status: <strong><?= strtoupper($app['status']) ?></strong>
      </div>
    <?php endif; ?>
  </main>
</div>
</body>
</html>
