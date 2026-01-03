<?php
session_start();
require_once "conn.php";

$msg = "";
$success = false;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $name = trim($_POST['name'] ?? '');
  $student_id = trim($_POST['student_id'] ?? '');
  $course = trim($_POST['course'] ?? '');
  $year_level = trim($_POST['year_level'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $password = $_POST['password'] ?? '';
  $confirm_password = $_POST['confirm_password'] ?? '';

  // Validation
  if (empty($name) || empty($student_id) || empty($course) || empty($year_level) || empty($email) || empty($password)) {
    $msg = "All fields are required.";
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $msg = "Invalid email format.";
  } elseif ($password !== $confirm_password) {
    $msg = "Passwords do not match.";
  } elseif (strlen($password) < 6) {
    $msg = "Password must be at least 6 characters.";
  } else {
    // Check if email already exists
    $checkEmail = mysqli_query($conn, "SELECT id FROM users WHERE email='".mysqli_real_escape_string($conn, $email)."' LIMIT 1");
    if ($checkEmail && mysqli_num_rows($checkEmail) > 0) {
      $msg = "Email already registered.";
    } else {
      // Check if student_id already exists
      $checkStudentId = mysqli_query($conn, "SELECT id FROM users WHERE student_id='".mysqli_real_escape_string($conn, $student_id)."' LIMIT 1");
      if ($checkStudentId && mysqli_num_rows($checkStudentId) > 0) {
        $msg = "Student ID already registered.";
      } else {
        // Hash password and insert
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = mysqli_prepare($conn, "INSERT INTO users (role, name, student_id, course, year_level, email, password_hash, has_scholarship) VALUES ('student', ?, ?, ?, ?, ?, ?, 0)");
        mysqli_stmt_bind_param($stmt, "ssssss", $name, $student_id, $course, $year_level, $email, $password_hash);
        
        if (mysqli_stmt_execute($stmt)) {
          $success = true;
          $msg = "Registration successful! You can now login.";
        } else {
          $msg = "Registration failed. Please try again.";
        }
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
  <title>Student Registration | ScholarManage</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    * { margin:0; padding:0; box-sizing:border-box; font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
    :root { --primary:#2c3e50; --secondary:#3498db; --bg:#f4f6f8; --card:#fff; --muted:#6b7280; --border:#e5e7eb; }
    body { background:var(--bg); color:#111827; min-height:100vh; display:flex; align-items:center; justify-content:center; padding:20px; }
    
    .container { max-width:500px; width:100%; background:var(--card); border:1px solid var(--border); border-radius:12px; padding:32px; box-shadow:0 4px 20px rgba(0,0,0,.08); }
    .header { text-align:center; margin-bottom:28px; }
    .header i { font-size:3rem; color:var(--secondary); margin-bottom:10px; }
    .header h1 { font-size:1.8rem; color:var(--primary); margin-bottom:6px; }
    .header p { color:var(--muted); font-size:14px; }
    
    .form-group { margin-bottom:18px; }
    label { display:block; font-weight:700; color:var(--primary); margin-bottom:6px; font-size:14px; }
    input, select { width:100%; padding:12px; border:1px solid var(--border); border-radius:8px; font-size:14px; transition:border .3s; }
    input:focus, select:focus { outline:none; border-color:var(--secondary); }
    
    .btn { width:100%; padding:14px; background:var(--secondary); color:#fff; border:none; border-radius:8px; font-weight:700; font-size:15px; cursor:pointer; transition:all .3s; display:flex; align-items:center; justify-content:center; gap:8px; }
    .btn:hover { background:#2980b9; transform:translateY(-2px); box-shadow:0 4px 12px rgba(52,152,219,.3); }
    
    .msg { padding:12px; border-radius:8px; margin-bottom:18px; font-size:14px; font-weight:600; text-align:center; }
    .msg.error { background:#fee; color:#c00; border:1px solid #fcc; }
    .msg.success { background:#e6fffb; color:#08979c; border:1px solid #87e8de; }
    
    .footer { text-align:center; margin-top:20px; font-size:14px; color:var(--muted); }
    .footer a { color:var(--secondary); text-decoration:none; font-weight:700; }
    .footer a:hover { text-decoration:underline; }
  </style>
</head>
<body>
  <div class="container">
    <div class="header">
      <i class="fas fa-user-graduate"></i>
      <h1>Student Registration</h1>
      <p>Create your ScholarManage account</p>
    </div>

    <?php if ($msg): ?>
      <div class="msg <?= $success ? 'success' : 'error' ?>"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <?php if (!$success): ?>
    <form method="POST">
      <div class="form-group">
        <label><i class="fas fa-user"></i> Full Name</label>
        <input type="text" name="name" required placeholder="Enter your full name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
      </div>

      <div class="form-group">
        <label><i class="fas fa-id-card"></i> Student ID</label>
        <input type="text" name="student_id" required placeholder="e.g., 2021-12345" value="<?= htmlspecialchars($_POST['student_id'] ?? '') ?>">
      </div>

      <div class="form-group">
        <label><i class="fas fa-book"></i> Course</label>
        <input type="text" name="course" required placeholder="e.g., BS Computer Science" value="<?= htmlspecialchars($_POST['course'] ?? '') ?>">
      </div>

      <div class="form-group">
        <label><i class="fas fa-layer-group"></i> Year Level</label>
        <select name="year_level" required>
          <option value="">Select Year Level</option>
          <option value="1st Year" <?= (($_POST['year_level'] ?? '') === '1st Year') ? 'selected' : '' ?>>1st Year</option>
          <option value="2nd Year" <?= (($_POST['year_level'] ?? '') === '2nd Year') ? 'selected' : '' ?>>2nd Year</option>
          <option value="3rd Year" <?= (($_POST['year_level'] ?? '') === '3rd Year') ? 'selected' : '' ?>>3rd Year</option>
          <option value="4th Year" <?= (($_POST['year_level'] ?? '') === '4th Year') ? 'selected' : '' ?>>4th Year</option>
          <option value="5th Year" <?= (($_POST['year_level'] ?? '') === '5th Year') ? 'selected' : '' ?>>5th Year</option>
        </select>
      </div>

      <div class="form-group">
        <label><i class="fas fa-envelope"></i> Email Address</label>
        <input type="email" name="email" required placeholder="your.email@example.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
      </div>

      <div class="form-group">
        <label><i class="fas fa-lock"></i> Password</label>
        <input type="password" name="password" required placeholder="Minimum 6 characters">
      </div>

      <div class="form-group">
        <label><i class="fas fa-lock"></i> Confirm Password</label>
        <input type="password" name="confirm_password" required placeholder="Re-enter your password">
      </div>

      <button type="submit" class="btn">
        <i class="fas fa-user-plus"></i> Register
      </button>
    </form>
    <?php endif; ?>

    <div class="footer">
      <?php if ($success): ?>
        <a href="index.php"><i class="fas fa-sign-in-alt"></i> Go to Login</a>
      <?php else: ?>
        Already have an account? <a href="index.php">Login here</a>
      <?php endif; ?>
    </div>
  </div>

  <script>
    <?php if ($msg !== ""): ?>
      <?php if ($success): ?>
        Swal.fire({
          icon: 'success',
          title: 'Registration Successful!',
          text: 'You can now login with your credentials.',
          confirmButtonColor: '#3498db'
        }).then(() => {
          window.location.href = 'index.php';
        });
      <?php else: ?>
        Swal.fire({
          icon: 'error',
          title: 'Registration Failed',
          text: '<?= addslashes($msg) ?>',
          confirmButtonColor: '#3498db'
        });
      <?php endif; ?>
    <?php endif; ?>
  </script>
</body>
</html>
