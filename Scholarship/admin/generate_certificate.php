<?php
session_start();
require_once "../conn.php";

if (!isset($_SESSION['user_id'])) { header("Location: ../index.php"); exit; }
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') { header("Location: ../student/dashboard.php"); exit; }

$cert_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get certificate request details
$res = mysqli_query($conn, "
  SELECT c.*, u.name, u.student_id, u.course, u.year_level, u.email, u.has_scholarship
  FROM certificate_requests c
  JOIN users u ON u.id = c.user_id
  WHERE c.id = $cert_id
  LIMIT 1
");

$cert = $res ? mysqli_fetch_assoc($res) : null;

if (!$cert) {
  die("Certificate request not found.");
}

$studentHasScholarship = (int)$cert['has_scholarship'];
$admin_id = (int)$_SESSION['user_id'];

// Generate certificate
if ($_SERVER["REQUEST_METHOD"] === "POST" && $cert['status'] === 'pending') {
  if ($studentHasScholarship === 1) {
    $error = "Cannot generate certificate: Student has an active scholarship.";
  } else {
    // Generate verification code
    $verification_code = strtoupper(substr(md5(uniqid() . $cert_id), 0, 10));
    
    // Create certificates directory
    $certDir = "../uploads/certificates/";
    if (!is_dir($certDir)) {
      mkdir($certDir, 0777, true);
    }
    
    // Generate HTML certificate file
    $certFileName = "cert_" . $cert['user_id'] . "_" . time() . ".html";
    $certPath = $certDir . $certFileName;
    
    // Certificate HTML content with print styles
    $certHtml = '
    <!DOCTYPE html>
    <html>
    <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>Certificate - ' . htmlspecialchars($cert['name']) . '</title>
      <style>
        @media print {
          body { margin: 0; padding: 0; }
          .no-print { display: none !important; }
        }
        body { 
          font-family: "Times New Roman", serif; 
          text-align: center; 
          padding: 50px 20px; 
          background: #fff; 
          margin: 0;
        }
        .certificate { 
          border: 10px solid #2c3e50; 
          padding: 60px 40px; 
          max-width: 900px; 
          margin: 0 auto; 
          background: #fff;
          box-shadow: 0 0 30px rgba(0,0,0,0.1);
          position: relative;
        }
        .qr-code {
          position: absolute;
          top: 20px;
          left: 20px;
          padding: 10px;
          background: white;
          border: 2px solid #2c3e50;
          border-radius: 8px;
        }
        .qr-code img {
          display: block;
          width: 100px;
          height: 100px;
        }
        .qr-label {
          font-size: 9px;
          color: #666;
          margin-top: 5px;
          font-weight: bold;
          text-align: center;
        }
        h1 { 
          font-size: 48px; 
          color: #2c3e50; 
          margin: 20px 0; 
          letter-spacing: 8px;
          font-weight: bold;
        }
        h2 { 
          font-size: 28px; 
          color: #3498db; 
          margin: 20px 0; 
          font-style: italic;
        }
        p { 
          font-size: 18px; 
          line-height: 1.8; 
          margin: 15px 0; 
          color: #333;
        }
        .student-name { 
          font-size: 42px; 
          font-weight: bold; 
          color: #2c3e50; 
          text-decoration: underline; 
          margin: 30px 0;
          font-family: "Georgia", serif;
        }
        .info-row {
          font-size: 16px;
          margin: 8px 0;
          color: #555;
        }
        .main-statement {
          font-size: 28px; 
          font-weight: bold;
          color: #e74c3c;
          margin: 40px 0;
          padding: 20px;
          border: 3px solid #e74c3c;
          display: inline-block;
        }
        .verification { 
          margin-top: 50px; 
          padding: 20px;
          background: #f8f9fa;
          border: 2px dashed #dee2e6;
          font-size: 14px; 
          color: #666; 
        }
        .verification-code {
          font-family: "Courier New", monospace;
          font-size: 18px;
          font-weight: bold;
          color: #2c3e50;
          letter-spacing: 2px;
        }
        .signature { 
          margin-top: 60px; 
          display: flex;
          justify-content: space-around;
          max-width: 700px;
          margin-left: auto;
          margin-right: auto;
        }
        .signature-box {
          text-align: center;
        }
        .signature-line { 
          border-top: 2px solid #000; 
          width: 250px; 
          margin: 10px auto; 
          padding-top: 8px; 
          font-weight: bold;
        }
        .seal {
          width: 100px;
          height: 100px;
          border: 3px solid #2c3e50;
          border-radius: 50%;
          display: flex;
          align-items: center;
          justify-content: center;
          margin: 20px auto;
          font-weight: bold;
          color: #2c3e50;
          font-size: 12px;
        }
        .print-btn {
          position: fixed;
          top: 20px;
          right: 20px;
          background: #3498db;
          color: white;
          border: none;
          padding: 12px 24px;
          border-radius: 8px;
          cursor: pointer;
          font-weight: bold;
          font-size: 14px;
          box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }
        .print-btn:hover {
          background: #2980b9;
        }
      </style>
    </head>
    <body>
      <button class="print-btn no-print" onclick="window.print()">Print Certificate</button>
      
      <div class="certificate">
        <div class="qr-code">
          <img src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=' . urlencode('CERT-' . $verification_code . '|' . $cert['name'] . '|' . date('Y-m-d')) . '" alt="QR Code">
          <div class="qr-label">Scan to Verify</div>
        </div>
        
        <div class="seal">OFFICIAL<br>SEAL</div>
        
        <h1>CERTIFICATE</h1>
        <h2>This is to certify that</h2>
        
        <p class="student-name">' . htmlspecialchars($cert['name']) . '</p>
        
        <p class="info-row"><strong>Student ID:</strong> ' . htmlspecialchars($cert['student_id']) . '</p>
        <p class="info-row"><strong>Course:</strong> ' . htmlspecialchars($cert['course']) . '</p>
        <p class="info-row"><strong>Year Level:</strong> ' . htmlspecialchars($cert['year_level']) . '</p>
        
        <br>
        
        <div class="main-statement">
          DOES NOT HAVE AN ACTIVE SCHOLARSHIP
        </div>
        
        <p>as of <strong>' . date('F d, Y') . '</strong></p>
        
        <br>
        
        <p style="font-style: italic;">This certificate is issued for official purposes and verification.</p>
        
        <div class="signature">
          <div class="signature-box">
            <div class="signature-line">
              Scholarship Coordinator
            </div>
          </div>
          <div class="signature-box">
            <div class="signature-line">
              School Administrator
            </div>
          </div>
        </div>
        
        <div class="verification">
          <strong>Verification Code:</strong><br>
          <span class="verification-code">' . $verification_code . '</span><br><br>
          <strong>Date Issued:</strong> ' . date('F d, Y h:i A') . '<br>
          <strong>Valid For:</strong> Official Use Only
        </div>
      </div>
      
      <div class="no-print" style="text-align: center; margin-top: 40px; padding: 20px; color: #666;">
        <p><strong>Note:</strong> Click the Print button to save as PDF (use "Save as PDF" option in print dialog)</p>
      </div>
    </body>
    </html>
    ';
    
    // Save certificate as HTML file
    file_put_contents($certPath, $certHtml);
    
    // Update certificate request
    $stmt = mysqli_prepare($conn, "UPDATE certificate_requests SET status='generated', pdf_path=?, verification_code=?, processed_at=NOW(), processed_by=? WHERE id=?");
    mysqli_stmt_bind_param($stmt, "ssii", $certFileName, $verification_code, $admin_id, $cert_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    
    $success = "Certificate generated successfully!";
    
    // Refresh data
    $res = mysqli_query($conn, "
      SELECT c.*, u.name, u.student_id, u.course, u.year_level, u.email, u.has_scholarship
      FROM certificate_requests c
      JOIN users u ON u.id = c.user_id
      WHERE c.id = $cert_id
      LIMIT 1
    ");
    $cert = $res ? mysqli_fetch_assoc($res) : null;
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Generate Certificate #<?= $cert_id ?> | Admin</title>
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
    .b-generated{background:#e6fffb;color:#08979c;}
    .alert{padding:14px;border-radius:10px;margin-bottom:18px;font-weight:700;}
    .alert-danger{background:#fff1f0;color:#cf1322;border:1px solid #ffccc7;}
    .alert-success{background:#e6fffb;color:#08979c;border:1px solid #87e8de;}
    .btn{display:inline-flex;align-items:center;gap:8px;padding:12px 18px;border-radius:10px;border:none;font-weight:800;font-size:14px;cursor:pointer;text-decoration:none;}
    .btn-primary{background:var(--secondary);color:#fff;}
    .btn-primary:hover{background:#2980b9;}
    .btn-success{background:#52c41a;color:#fff;}
    .btn-success:hover{background:#389e0d;}
    .btn:disabled{opacity:0.5;cursor:not-allowed;}
  </style>
</head>
<body>
<div class="layout">
  <?php include "../sidebar/asb.php"; ?>
  <main class="content">
    <div class="topbar">
      <h1><i class="fas fa-file-certificate"></i> Certificate Request #<?= $cert_id ?></h1>
      <a href="certificate_requests.php" class="back"><i class="fas fa-arrow-left"></i> Back to Requests</a>
    </div>

    <?php if (isset($error)): ?>
      <div class="alert alert-danger">
        <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <?php if (isset($success)): ?>
      <div class="alert alert-success">
        <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?>
      </div>
    <?php endif; ?>

    <?php if ($studentHasScholarship === 1 && $cert['status'] === 'pending'): ?>
      <div class="alert alert-danger">
        <i class="fas fa-ban"></i> <strong>Not Eligible:</strong> This student HAS an active scholarship. Certificate cannot be generated.
      </div>
    <?php elseif ($cert['status'] === 'pending'): ?>
      <div class="alert alert-success">
        <i class="fas fa-check-circle"></i> <strong>Eligible:</strong> This student does NOT have a scholarship. Certificate can be generated.
      </div>
    <?php endif; ?>

    <!-- Student Information -->
    <div class="panel">
      <div class="section-title"><i class="fas fa-user"></i> Student Information</div>
      <div class="row"><div class="label">Name</div><div class="value"><?= htmlspecialchars($cert['name']) ?></div></div>
      <div class="row"><div class="label">Student ID</div><div class="value"><?= htmlspecialchars($cert['student_id']) ?></div></div>
      <div class="row"><div class="label">Course</div><div class="value"><?= htmlspecialchars($cert['course']) ?></div></div>
      <div class="row"><div class="label">Year Level</div><div class="value"><?= htmlspecialchars($cert['year_level']) ?></div></div>
      <div class="row"><div class="label">Email</div><div class="value"><?= htmlspecialchars($cert['email']) ?></div></div>
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

    <!-- Request Details -->
    <div class="panel">
      <div class="section-title"><i class="fas fa-file-alt"></i> Request Details</div>
      <div class="row"><div class="label">Request ID</div><div class="value">#<?= $cert['id'] ?></div></div>
      <div class="row"><div class="label">Status</div><div class="value"><span class="badge b-<?= $cert['status'] ?>"><?= strtoupper($cert['status']) ?></span></div></div>
      <div class="row"><div class="label">Requested At</div><div class="value"><?= date('M d, Y h:i A', strtotime($cert['requested_at'])) ?></div></div>
      <?php if ($cert['status'] === 'generated'): ?>
        <div class="row"><div class="label">Processed At</div><div class="value"><?= date('M d, Y h:i A', strtotime($cert['processed_at'])) ?></div></div>
        <div class="row"><div class="label">Verification Code</div><div class="value" style="font-family:monospace;color:#3498db;"><?= htmlspecialchars($cert['verification_code']) ?></div></div>
      <?php endif; ?>
    </div>

    <!-- Actions -->
    <?php if ($cert['status'] === 'pending'): ?>
    <div class="panel">
      <div class="section-title"><i class="fas fa-cogs"></i> Generate Certificate</div>
      <form method="POST">
        <p style="color:var(--muted);margin-bottom:14px;line-height:1.6;">
          This will generate a PDF certificate certifying that <strong><?= htmlspecialchars($cert['name']) ?></strong> does NOT have an active scholarship.
        </p>
        <button type="submit" class="btn btn-success" <?= $studentHasScholarship === 1 ? 'disabled' : '' ?>>
          <i class="fas fa-file-pdf"></i> Generate Certificate PDF
        </button>
        <?php if ($studentHasScholarship === 1): ?>
          <p style="color:#cf1322;margin-top:12px;font-size:13px;">
            <i class="fas fa-info-circle"></i> Generation is disabled because the student has an active scholarship.
          </p>
        <?php endif; ?>
      </form>
    </div>
    <?php elseif ($cert['status'] === 'generated'): ?>
    <div class="panel">
      <div class="section-title"><i class="fas fa-download"></i> Download Certificate</div>
      <a href="../uploads/certificates/<?= htmlspecialchars($cert['pdf_path']) ?>" target="_blank" class="btn btn-primary">
        <i class="fas fa-eye"></i> View Certificate (Print to PDF)
      </a>
      <p style="margin-top:12px;color:var(--muted);font-size:13px;">
        <i class="fas fa-info-circle"></i> Open the certificate and use your browser's Print function to save as PDF.
      </p>
    </div>
    <?php endif; ?>
  </main>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    const generateBtn = document.querySelector('button[type="submit"]');
    const form = document.querySelector('form[method="POST"]');

    if (generateBtn) {
      generateBtn.addEventListener('click', function(e) {
        e.preventDefault();
        Swal.fire({
          title: 'Generate Certificate?',
          text: "This will create a certificate for the student.",
          icon: 'question',
          showCancelButton: true,
          confirmButtonColor: '#3498db',
          cancelButtonColor: '#95a5a6',
          confirmButtonText: 'Yes, Generate',
          cancelButtonText: 'Cancel'
        }).then((result) => {
          if (result.isConfirmed) {
            form.submit();
          }
        });
      });
    }

    <?php if (isset($success) && $success): ?>
      Swal.fire({
        icon: 'success',
        title: 'Certificate Generated!',
        text: 'The certificate has been created successfully.',
        confirmButtonColor: '#3498db'
      });
    <?php endif; ?>

    <?php if (isset($error) && $error !== ""): ?>
      Swal.fire({
        icon: 'error',
        title: 'Generation Failed',
        text: '<?= addslashes($error) ?>',
        confirmButtonColor: '#3498db'
      });
    <?php endif; ?>
  });
</script>
</body>
</html>
