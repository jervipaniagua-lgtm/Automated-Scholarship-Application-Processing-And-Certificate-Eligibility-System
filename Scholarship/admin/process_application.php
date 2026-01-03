<?php
session_start();
require_once "../conn.php";

if (!isset($_SESSION['user_id'])) { header("Location: ../index.php"); exit; }
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') { header("Location: ../student/dashboard.php"); exit; }

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
  header("Location: applications.php");
  exit;
}

$app_id = isset($_POST['app_id']) ? (int)$_POST['app_id'] : 0;
$action = $_POST['action'] ?? '';
$admin_id = (int)$_SESSION['user_id'];

if (!in_array($action, ['approve', 'reject'])) {
  header("Location: applications.php");
  exit;
}

// Get application and user info
$res = mysqli_query($conn, "
  SELECT a.user_id, a.status, u.has_scholarship, u.name
  FROM scholarship_applications a
  JOIN users u ON u.id = a.user_id
  WHERE a.id = $app_id
  LIMIT 1
");

$app = $res ? mysqli_fetch_assoc($res) : null;

if (!$app) {
  $_SESSION['error'] = "Application not found.";
  header("Location: applications.php");
  exit;
}

if ($app['status'] !== 'pending') {
  $_SESSION['error'] = "Application has already been processed.";
  header("Location: applications.php");
  exit;
}

$user_id = (int)$app['user_id'];
$studentName = $app['name'];

// APPROVE
if ($action === 'approve') {
  // Check if student already has scholarship
  if ((int)$app['has_scholarship'] === 1) {
    $_SESSION['error'] = "Cannot approve: Student already has an active scholarship.";
    header("Location: applications.php");
    exit;
  }

  // Update application status with review info
  $updateApp = mysqli_query($conn, "UPDATE scholarship_applications SET status='approved', reviewed_by=$admin_id, reviewed_at=NOW() WHERE id=$app_id");
  
  // Update user to have scholarship
  $updateUser = mysqli_query($conn, "UPDATE users SET has_scholarship=1 WHERE id=$user_id");

  if ($updateApp && $updateUser) {
    // Update session if this is the current user (unlikely but safe)
    if (isset($_SESSION['user_id']) && (int)$_SESSION['user_id'] === $user_id) {
      $_SESSION['has_scholarship'] = 1;
    }
    $_SESSION['success'] = "Application approved! $studentName is now an active scholar.";
  } else {
    $appError = $updateApp ? '' : 'Application update failed: ' . mysqli_error($conn) . '. ';
    $userError = $updateUser ? '' : 'User update failed: ' . mysqli_error($conn);
    $_SESSION['error'] = "Database update failed. " . $appError . $userError;
  }
}

// REJECT
if ($action === 'reject') {
  // Update application status with review info
  $updateApp = mysqli_query($conn, "UPDATE scholarship_applications SET status='rejected', reviewed_by=$admin_id, reviewed_at=NOW() WHERE id=$app_id");
  
  if ($updateApp) {
    $_SESSION['success'] = "Application rejected.";
  } else {
    $_SESSION['error'] = "Database update failed. Error: " . mysqli_error($conn);
  }
}

header("Location: applications.php");
exit;
?>
