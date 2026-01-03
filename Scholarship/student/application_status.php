<?php
session_start();
require_once "../conn.php";

if (!isset($_SESSION['user_id'])) { header("Location: ../index.php"); exit; }
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') { header("Location: ../admin/dashboard.php"); exit; }

$user_id = (int)$_SESSION['user_id'];

$res = mysqli_query($conn, "
  SELECT id, status, submitted_at, purpose, scholarship_type
  FROM scholarship_applications
  WHERE user_id=$user_id
  ORDER BY submitted_at DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Application Status | Student</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root{--primary:#2c3e50;--secondary:#3498db;--bg:#f4f6f8;--card:#fff;--muted:#6b7280;--border:#e5e7eb;}
    *{margin:0;padding:0;box-sizing:border-box;font-family:'Segoe UI',Tahoma,Verdana,sans-serif;}
    body{background:var(--bg);color:#111827;}
    .layout{display:flex;min-height:100vh;}
    .content{flex:1;padding:24px;}
    .topbar{background:var(--card);border:1px solid var(--border);border-radius:12px;padding:16px 18px;margin-bottom:18px;}
    .panel{background:var(--card);border:1px solid var(--border);border-radius:12px;padding:16px;}
    table{width:100%;border-collapse:collapse;font-size:13px;}
    th,td{padding:10px 8px;border-bottom:1px solid var(--border);text-align:left;}
    th{color:var(--muted);font-weight:800;background:#fafafa;}
    .badge{display:inline-block;padding:3px 10px;border-radius:999px;font-size:12px;font-weight:800;}
    .b-pending{background:#fff7e6;color:#d48806;}
    .b-approved{background:#e6fffb;color:#08979c;}
    .b-rejected{background:#fff1f0;color:#cf1322;}
  </style>
</head>
<body>
<div class="layout">
  <?php include "../sidebar/ssb.php"; ?>

  <main class="content">
    <div class="topbar">
      <h2 style="color:var(--primary);"><i class="fas fa-clock"></i> Application Status</h2>
      <div style="color:var(--muted);font-size:13px;margin-top:4px;">View your scholarship application history.</div>
    </div>

    <div class="panel">
      <table>
        <thead>
          <tr>
            <th>Scholarship Type</th>
            <th>Status</th>
            <th>Submitted At</th>
            <th>Purpose</th>
          </tr>
        </thead>
        <tbody>
        <?php if($res && mysqli_num_rows($res) > 0): ?>
          <?php while($r = mysqli_fetch_assoc($res)):
            $st = $r['status'];
            $cls = $st==='approved'?'b-approved':($st==='rejected'?'b-rejected':'b-pending');
          ?>
            <tr>
              <td><span class="badge" style="background:#e0f2fe;color:#0369a1;"><?php echo htmlspecialchars($r['scholarship_type'] ?? 'Academic'); ?></span></td>
              <td><span class="badge <?php echo $cls; ?>"><?php echo htmlspecialchars(ucfirst($st)); ?></span></td>
              <td><?php echo htmlspecialchars(date("M d, Y", strtotime($r['submitted_at']))); ?></td>
              <td><?php echo htmlspecialchars($r['purpose']); ?></td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="4">No applications found.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </main>
</div>
</body>
</html>
