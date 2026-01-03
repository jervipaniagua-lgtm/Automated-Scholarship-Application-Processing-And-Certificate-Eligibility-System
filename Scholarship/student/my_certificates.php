<?php
session_start();
require_once "../conn.php";

if (!isset($_SESSION['user_id'])) { header("Location: ../index.php"); exit; }
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') { header("Location: ../admin/dashboard.php"); exit; }

$user_id = (int)$_SESSION['user_id'];

$res = mysqli_query($conn, "
  SELECT id, status, requested_at, processed_at, pdf_path, verification_code
  FROM certificate_requests
  WHERE user_id=$user_id
  ORDER BY requested_at DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>My Certificates | Student</title>
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
    .b-generated{background:#e6fffb;color:#08979c;}
    .b-rejected{background:#fff1f0;color:#cf1322;}
    .btn{
      display:inline-flex;align-items:center;gap:8px;text-decoration:none;
      padding:8px 10px;border-radius:10px;border:1px solid var(--border);
      background:#fff;color:var(--primary);font-weight:800;font-size:12px;
    }
    .btn:hover{border-color:#cbd5e1;}
  </style>
</head>
<body>
<div class="layout">
  <?php include "../sidebar/ssb.php"; ?>

  <main class="content">
    <div class="topbar">
      <h2 style="color:var(--primary);"><i class="fas fa-download"></i> My Certificates</h2>
      <div style="color:var(--muted);font-size:13px;margin-top:4px;">Download generated certificate PDFs here.</div>
    </div>

    <div class="panel">
      <table>
        <thead>
          <tr>
            <th>Status</th>
            <th>Requested</th>
            <th>Generated</th>
            <th>Verification Code</th>
            <th>PDF</th>
          </tr>
        </thead>
        <tbody>
        <?php if($res && mysqli_num_rows($res) > 0): ?>
          <?php while($r = mysqli_fetch_assoc($res)):
            $st = $r['status'];
            $cls = $st==='generated'?'b-generated':($st==='rejected'?'b-rejected':'b-pending');
          ?>
            <tr>
              <td><span class="badge <?php echo $cls; ?>"><?php echo htmlspecialchars(ucfirst($st)); ?></span></td>
              <td><?php echo htmlspecialchars(date("M d, Y", strtotime($r['requested_at']))); ?></td>
              <td>
                <?php echo $r['processed_at'] ? htmlspecialchars(date("M d, Y", strtotime($r['processed_at']))) : "-"; ?>
              </td>
              <td><?php echo htmlspecialchars($r['verification_code'] ?? "-"); ?></td>
              <td>
                <?php if($st === 'generated' && !empty($r['pdf_path'])): ?>
                  <a class="btn" target="_blank" href="../uploads/certificates/<?php echo htmlspecialchars($r['pdf_path']); ?>">
                    <i class="fas fa-file-certificate"></i> View Certificate
                  </a>
                <?php else: ?>
                  -
                <?php endif; ?>
              </td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="5">No certificate requests found.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </main>
</div>
</body>
</html>
