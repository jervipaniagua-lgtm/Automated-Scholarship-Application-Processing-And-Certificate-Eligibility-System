<?php
session_start();
require_once "../conn.php";

if (!isset($_SESSION['user_id'])) { header("Location: ../index.php"); exit; }
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') { header("Location: ../admin/dashboard.php"); exit; }

$user_id = (int)$_SESSION['user_id'];
$hasScholar = isset($_SESSION['has_scholarship']) ? (int)$_SESSION['has_scholarship'] : 0;

$msg = "";

// Block applying if already scholar
if ($hasScholar === 1) {
  $msg = "You are already an active scholar. You cannot apply again.";
}

// Submit application
if ($_SERVER["REQUEST_METHOD"] === "POST" && $hasScholar === 0) {
  $purpose = trim($_POST['purpose'] ?? '');
  $scholarship_type = trim($_POST['scholarship_type'] ?? '');

  if ($purpose === "" || $scholarship_type === "") {
    $msg = "Please fill in all required fields.";
  } else {
    // Check for pending application
    $check = mysqli_query($conn, "SELECT id FROM scholarship_applications WHERE user_id=$user_id AND status='pending' LIMIT 1");
    if ($check && mysqli_num_rows($check) > 0) {
      $msg = "You already have a pending application.";
    } else {
      // Handle file uploads
      $uploadDir = "../uploads/applications/";
      if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
      }

      $corFile = "";
      $gradesFile = "";
      $idFile = "";

      // Upload COR
      if (isset($_FILES['cor']) && $_FILES['cor']['error'] === UPLOAD_ERR_OK) {
        $corFile = $user_id . "_" . time() . "_cor_" . basename($_FILES['cor']['name']);
        move_uploaded_file($_FILES['cor']['tmp_name'], $uploadDir . $corFile);
      }

      // Upload Grades
      if (isset($_FILES['grades']) && $_FILES['grades']['error'] === UPLOAD_ERR_OK) {
        $gradesFile = $user_id . "_" . time() . "_grades_" . basename($_FILES['grades']['name']);
        move_uploaded_file($_FILES['grades']['tmp_name'], $uploadDir . $gradesFile);
      }

      // Upload ID
      if (isset($_FILES['id_file']) && $_FILES['id_file']['error'] === UPLOAD_ERR_OK) {
        $idFile = $user_id . "_" . time() . "_id_" . basename($_FILES['id_file']['name']);
        move_uploaded_file($_FILES['id_file']['tmp_name'], $uploadDir . $idFile);
      }

      // Insert application
      $stmt = mysqli_prepare($conn, "INSERT INTO scholarship_applications (user_id, purpose, scholarship_type, cor_file, grades_file, id_file, status, submitted_at) VALUES (?, ?, ?, ?, ?, ?, 'pending', NOW())");
      mysqli_stmt_bind_param($stmt, "isssss", $user_id, $purpose, $scholarship_type, $corFile, $gradesFile, $idFile);
      
      if (mysqli_stmt_execute($stmt)) {
        $app_id = mysqli_insert_id($conn);
        mysqli_stmt_close($stmt);

        // Create notification for admin
        $studentName = $_SESSION['name'] ?? 'Student';
        $notifMsg = "New Scholarship Applicant: " . $studentName;
        
        // Get first admin user
        $adminRes = mysqli_query($conn, "SELECT id FROM users WHERE role='admin' LIMIT 1");
        if ($adminRes && $adminRow = mysqli_fetch_assoc($adminRes)) {
          $admin_id = (int)$adminRow['id'];
          $notifStmt = mysqli_prepare($conn, "INSERT INTO notifications (admin_id, type, message, ref_table, ref_id, is_read, created_at) VALUES (?, 'application', ?, 'scholarship_applications', ?, 0, NOW())");
          mysqli_stmt_bind_param($notifStmt, "isi", $admin_id, $notifMsg, $app_id);
          mysqli_stmt_execute($notifStmt);
          mysqli_stmt_close($notifStmt);
        }

        $msg = "Application submitted successfully! Status: Pending";
      } else {
        $msg = "Failed to submit application.";
        mysqli_stmt_close($stmt);
      }
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Apply Scholarship | Student</title>
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
    label{display:block;font-weight:800;color:var(--primary);margin:14px 0 6px;}
    label i{color:var(--secondary);margin-right:6px;}
    textarea,input[type="file"]{width:100%;padding:10px;border:1px solid var(--border);border-radius:10px;}
    textarea{font-family:inherit;resize:vertical;}
    input[type="file"]{background:#fafafa;cursor:pointer;}
    .btn{margin-top:12px;display:inline-flex;align-items:center;gap:8px;padding:10px 12px;border-radius:10px;border:1px solid var(--border);background:#fff;color:var(--primary);font-weight:800;text-decoration:none;cursor:pointer;}
    .btn:hover{border-color:#cbd5e1;}
    .msg{margin-bottom:12px;color:var(--muted);font-weight:700;}
    .warn{color:#b91c1c;}
  </style>
</head>
<body>
<div class="layout">
  <?php include "../sidebar/ssb.php"; ?>

  <main class="content">
    <div class="topbar">
      <h2 style="color:var(--primary);"><i class="fas fa-paper-plane"></i> Apply for Scholarship</h2>
      <div style="color:var(--muted);font-size:13px;margin-top:4px;">Submit your application with required documents (COR, Grades, ID).</div>
    </div>

    <div class="panel">
      <?php if($msg): ?>
        <div class="msg <?php echo ($hasScholar===1) ? 'warn' : ''; ?>"><?php echo htmlspecialchars($msg); ?></div>
      <?php endif; ?>

      <?php if($hasScholar === 1): ?>
        <div class="msg warn">Application is blocked because you are an active scholar.</div>
      <?php else: ?>
        <form method="POST" enctype="multipart/form-data">
          <label><i class="fas fa-graduation-cap"></i> Scholarship Type <span style="color:red;">*</span></label>
          <select name="scholarship_type" required style="width:100%;padding:10px;border:1px solid var(--border);border-radius:10px;font-size:14px;background:#fff;cursor:pointer;">
            <option value="">-- Select Scholarship Type --</option>
            <option value="Academic Excellence">Academic Excellence</option>
            <option value="Athletic">Athletic</option>
            <option value="Financial Need">Financial Need</option>
            <option value="Merit-Based">Merit-Based</option>
            <option value="Community Service">Community Service</option>
            <option value="STEM">STEM (Science, Technology, Engineering, Math)</option>
            <option value="Arts & Culture">Arts & Culture</option>
            <option value="Special Talent">Special Talent</option>
            <option value="Other">Other</option>
          </select>

          <label><i class="fas fa-comment"></i> Purpose / Reason</label>
          <textarea name="purpose" rows="5" placeholder="Why are you applying for this scholarship?" required></textarea>

          <label><i class="fas fa-file-alt"></i> Certificate of Registration (COR)</label>
          <input type="file" name="cor" accept=".pdf,.jpg,.jpeg,.png" required>

          <label><i class="fas fa-file-alt"></i> Grades / Transcript</label>
          <input type="file" name="grades" accept=".pdf,.jpg,.jpeg,.png" required>

          <label><i class="fas fa-id-card"></i> Valid ID</label>
          <input type="file" name="id_file" accept=".pdf,.jpg,.jpeg,.png" required>

          <button class="btn" type="submit">
            <i class="fas fa-paper-plane"></i> Submit Application
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
        title: 'Application Submitted!',
        text: '<?= addslashes($msg) ?>',
        confirmButtonColor: '#3498db'
      });
    <?php elseif ($hasScholar === 1): ?>
      Swal.fire({
        icon: 'info',
        title: 'Already a Scholar',
        text: '<?= addslashes($msg) ?>',
        confirmButtonColor: '#3498db'
      });
    <?php else: ?>
      Swal.fire({
        icon: 'error',
        title: 'Application Error',
        text: '<?= addslashes($msg) ?>',
        confirmButtonColor: '#3498db'
      });
    <?php endif; ?>
  <?php endif; ?>
</script>
</body>
</html>
