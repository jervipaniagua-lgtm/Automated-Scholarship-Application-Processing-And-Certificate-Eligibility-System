<?php
session_start();
require_once "../conn.php";

if (!isset($_SESSION['user_id'])) { header("Location: ../index.php"); exit; }
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') { header("Location: ../student/dashboard.php"); exit; }

$success_msg = $_SESSION['success'] ?? '';
$error_msg = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);

$res = mysqli_query($conn, "
  SELECT a.id, a.status, a.submitted_at, a.scholarship_type, u.name, u.student_id, u.course, u.year_level
  FROM scholarship_applications a
  JOIN users u ON u.id = a.user_id
  ORDER BY a.submitted_at DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Applications | Admin</title>
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
    table{width:100%;border-collapse:collapse;font-size:13px;}
    th,td{padding:10px 8px;border-bottom:1px solid var(--border);text-align:left;}
    th{color:var(--muted);font-weight:700;background:#fafafa;}
    .badge{display:inline-block;padding:3px 10px;border-radius:999px;font-size:12px;font-weight:700;}
    .b-pending{background:#fff7e6;color:#d48806;}
    .b-approved{background:#e6fffb;color:#08979c;}
    .b-rejected{background:#fff1f0;color:#cf1322;}
    .action{
      display:inline-flex;align-items:center;gap:8px;text-decoration:none;
      padding:8px 10px;border:1px solid var(--border);border-radius:10px;
      background:#fff;color:var(--primary);font-weight:700;font-size:12px;
    }
    .action:hover{border-color:#cbd5e1;}
    .alert{padding:12px 16px;border-radius:10px;margin-bottom:18px;font-weight:700;}
    .alert-success{background:#e6fffb;color:#08979c;border:1px solid #87e8de;}
    .alert-danger{background:#fff1f0;color:#cf1322;border:1px solid #ffccc7;}
  </style>
</head>
<body>
<div class="layout">
  <?php include "../sidebar/asb.php"; ?>
  <main class="content">
    <div class="topbar">
      <h1><i class="fas fa-file-signature"></i> Scholarship Applications</h1>
      <div style="color:var(--muted);font-size:13px;">All submitted applications</div>
    </div>

    <?php if ($success_msg): ?>
      <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($success_msg) ?></div>
    <?php endif; ?>
    
    <?php if ($error_msg): ?>
      <div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($error_msg) ?></div>
    <?php endif; ?>

    <div class="panel">
      <table>
        <thead>
          <tr>
            <th>Student</th>
            <th>Student ID</th>
            <th>Course / Year</th>
            <th>Scholarship Type</th>
            <th>Status</th>
            <th>Date Submitted</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
        <?php if ($res && mysqli_num_rows($res) > 0): ?>
          <?php while($r = mysqli_fetch_assoc($res)):
            $st = $r['status'];
            $cls = $st === 'approved' ? 'b-approved' : ($st === 'rejected' ? 'b-rejected' : 'b-pending');
          ?>
            <tr>
              <td><?php echo htmlspecialchars($r['name']); ?></td>
              <td><?php echo htmlspecialchars($r['student_id']); ?></td>
              <td><?php echo htmlspecialchars($r['course'] . " / " . $r['year_level']); ?></td>
              <td><span class="badge" style="background:#e0f2fe;color:#0369a1;font-size:11px;"><?php echo htmlspecialchars($r['scholarship_type'] ?? 'Academic'); ?></span></td>
              <td><span class="badge <?php echo $cls; ?>"><?php echo htmlspecialchars(ucfirst($st)); ?></span></td>
              <td><?php echo htmlspecialchars(date("M d, Y", strtotime($r['submitted_at']))); ?></td>
              <td>
                <!-- Placeholder view page (create later) -->
                <a class="action" href="view_application.php?id=<?php echo (int)$r['id']; ?>">
                  <i class="fas fa-eye"></i> View
                </a>
              </td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="7">No applications found.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </main>
</div>
</body>
</html>
