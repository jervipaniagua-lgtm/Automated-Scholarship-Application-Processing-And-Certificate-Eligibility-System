<?php
session_start();
require_once "../conn.php";

if (!isset($_SESSION['user_id'])) { header("Location: ../index.php"); exit; }
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') { header("Location: ../admin/dashboard.php"); exit; }

$user_id = (int)$_SESSION['user_id'];
$success_msg = "";
$error_msg = "";

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
  $name = mysqli_real_escape_string($conn, trim($_POST['name']));
  $student_id = mysqli_real_escape_string($conn, trim($_POST['student_id']));
  $course = mysqli_real_escape_string($conn, trim($_POST['course']));
  $year_level = mysqli_real_escape_string($conn, trim($_POST['year_level']));
  $email = mysqli_real_escape_string($conn, trim($_POST['email']));
  
  $stmt = mysqli_prepare($conn, "UPDATE users SET name=?, student_id=?, course=?, year_level=?, email=? WHERE id=?");
  mysqli_stmt_bind_param($stmt, "sssssi", $name, $student_id, $course, $year_level, $email, $user_id);
  
  if (mysqli_stmt_execute($stmt)) {
    $success_msg = "Profile updated successfully!";
  } else {
    $error_msg = "Failed to update profile.";
  }
  mysqli_stmt_close($stmt);
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
  $current_password = $_POST['current_password'];
  $new_password = $_POST['new_password'];
  $confirm_password = $_POST['confirm_password'];
  
  // Verify current password
  $check = mysqli_query($conn, "SELECT password FROM users WHERE id=$user_id");
  $user = mysqli_fetch_assoc($check);
  
  if (!password_verify($current_password, $user['password'])) {
    $error_msg = "Current password is incorrect.";
  } elseif ($new_password !== $confirm_password) {
    $error_msg = "New passwords do not match.";
  } elseif (strlen($new_password) < 6) {
    $error_msg = "Password must be at least 6 characters.";
  } else {
    $hashed = password_hash($new_password, PASSWORD_DEFAULT);
    $stmt = mysqli_prepare($conn, "UPDATE users SET password=? WHERE id=?");
    mysqli_stmt_bind_param($stmt, "si", $hashed, $user_id);
    
    if (mysqli_stmt_execute($stmt)) {
      $success_msg = "Password changed successfully!";
    } else {
      $error_msg = "Failed to change password.";
    }
    mysqli_stmt_close($stmt);
  }
}

$res = mysqli_query($conn, "SELECT name, student_id, course, year_level, email FROM users WHERE id=$user_id LIMIT 1");
$u = $res ? mysqli_fetch_assoc($res) : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Profile | Student</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root{--primary:#2c3e50;--secondary:#3498db;--bg:#f4f6f8;--card:#fff;--muted:#6b7280;--border:#e5e7eb;}
    *{margin:0;padding:0;box-sizing:border-box;font-family:'Segoe UI',Tahoma,Verdana,sans-serif;}
    body{background:var(--bg);color:#111827;}
    .layout{display:flex;min-height:100vh;}
    .content{flex:1;padding:24px;}
    .topbar{background:var(--card);border:1px solid var(--border);border-radius:12px;padding:16px 18px;margin-bottom:18px;}
    .panel{background:var(--card);border:1px solid var(--border);border-radius:12px;padding:24px;margin-bottom:18px;}
    .row{display:grid;grid-template-columns:180px 1fr;gap:12px;padding:10px 0;border-bottom:1px solid var(--border);}
    .row:last-child{border-bottom:none;}
    .label{color:var(--muted);font-weight:700;font-size:14px;}
    .value{font-weight:600;color:var(--primary);}
    .form-group{margin-bottom:16px;}
    .form-group label{display:block;margin-bottom:6px;font-weight:600;color:var(--primary);font-size:14px;}
    .form-group input,.form-group select{width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:6px;font-size:14px;}
    .form-group input:focus,.form-group select:focus{outline:none;border-color:var(--secondary);}
    .btn{padding:10px 20px;border:none;border-radius:6px;font-weight:600;cursor:pointer;transition:all 0.2s;font-size:14px;}
    .btn-primary{background:var(--secondary);color:white;}
    .btn-primary:hover{background:#2980b9;transform:translateY(-2px);box-shadow:0 4px 8px rgba(0,0,0,0.15);}
    .btn-success{background:#10b981;color:white;}
    .btn-success:hover{background:#059669;transform:translateY(-2px);box-shadow:0 4px 8px rgba(0,0,0,0.15);}
    .alert{padding:12px 16px;border-radius:6px;margin-bottom:16px;font-size:14px;}
    .alert-success{background:#d1fae5;color:#065f46;border:1px solid #6ee7b7;}
    .alert-error{background:#fee2e2;color:#991b1b;border:1px solid #fca5a5;}
    .section-title{font-size:18px;font-weight:700;color:var(--primary);margin-bottom:16px;padding-bottom:8px;border-bottom:2px solid var(--border);}
    .grid-2{display:grid;grid-template-columns:1fr 1fr;gap:18px;}
    @media(max-width:768px){.grid-2{grid-template-columns:1fr;}}
  </style>
</head>
<body>
<div class="layout">
  <?php include "../sidebar/ssb.php"; ?>

  <main class="content">
    <div class="topbar">
      <h2 style="color:var(--primary);"><i class="fas fa-id-card"></i> My Profile</h2>
      <div style="color:var(--muted);font-size:13px;margin-top:4px;">Your account information.</div>
    </div>

    <div class="panel">
      <?php if(!$u): ?>
        Profile not found.
      <?php else: ?>
        <div class="row"><div class="label">Name</div><div class="value"><?php echo htmlspecialchars($u['name']); ?></div></div>
        <div class="row"><div class="label">Student ID</div><div class="value"><?php echo htmlspecialchars($u['student_id']); ?></div></div>
        <div class="row"><div class="label">Course</div><div class="value"><?php echo htmlspecialchars($u['course']); ?></div></div>
        <div class="row"><div class="label">Year Level</div><div class="value"><?php echo htmlspecialchars($u['year_level']); ?></div></div>
        <div class="row"><div class="label">Email</div><div class="value"><?php echo htmlspecialchars($u['email']); ?></div></div>
      <?php endif; ?>
    </div>
  </main>
</div>
</body>
</html>
