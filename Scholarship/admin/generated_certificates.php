<?php
session_start();
require_once "../conn.php";

if (!isset($_SESSION['user_id'])) { header("Location: ../index.php"); exit; }
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') { header("Location: ../student/dashboard.php"); exit; }

$res = mysqli_query($conn, "
  SELECT c.id, c.pdf_path, c.verification_code, c.processed_at, u.name, u.student_id
  FROM certificate_requests c
  JOIN users u ON u.id = c.user_id
  WHERE c.status='generated'
  ORDER BY c.processed_at DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Generated Certificates | Admin</title>
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
    .download{
      display:inline-flex;align-items:center;gap:8px;text-decoration:none;
      padding:8px 10px;border:1px solid var(--border);border-radius:10px;
      background:#fff;color:var(--primary);font-weight:700;font-size:12px;
    }
  </style>
</head>
<body>
<div class="layout">
  <?php include "../sidebar/asb.php"; ?>
  <main class="content">
    <div class="topbar">
      <h1><i class="fas fa-folder-open"></i> Generated Certificates</h1>
      <div style="color:var(--muted);font-size:13px;">Download and verify codes</div>
    </div>

    <div class="panel">
      <table>
        <thead>
          <tr>
            <th>Student</th>
            <th>Student ID</th>
            <th>Verification Code</th>
            <th>Date Generated</th>
            <th>PDF</th>
          </tr>
        </thead>
        <tbody>
        <?php if ($res && mysqli_num_rows($res) > 0): ?>
          <?php while($r = mysqli_fetch_assoc($res)): ?>
            <tr>
              <td><?php echo htmlspecialchars($r['name']); ?></td>
              <td><?php echo htmlspecialchars($r['student_id']); ?></td>
              <td><?php echo htmlspecialchars($r['verification_code']); ?></td>
              <td><?php echo htmlspecialchars(date("M d, Y", strtotime($r['processed_at']))); ?></td>
              <td>
                <?php if (!empty($r['pdf_path'])): ?>
                  <a class="download" href="../uploads/certificates/<?php echo htmlspecialchars($r['pdf_path']); ?>" target="_blank">
                    <i class="fas fa-eye"></i> View Certificate
                  </a>
                <?php else: ?>
                  No file
                <?php endif; ?>
              </td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="5">No generated certificates yet.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </main>
</div>
</body>
</html>
