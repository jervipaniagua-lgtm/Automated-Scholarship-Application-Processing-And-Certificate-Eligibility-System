<?php
session_start();
require_once "../conn.php";

if (!isset($_SESSION['user_id'])) { header("Location: ../index.php"); exit; }
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') { header("Location: ../student/dashboard.php"); exit; }

$admin_id = (int)$_SESSION['user_id'];
$success_msg = $_SESSION['success'] ?? '';
$error_msg = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);

// Handle user deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
    $user_id = (int)$_POST['user_id'];
    
    // Don't allow deleting self
    if ($user_id === $admin_id) {
        $_SESSION['error'] = "You cannot delete your own account!";
    } else {
        $delete = mysqli_query($conn, "DELETE FROM users WHERE id = $user_id");
        if ($delete) {
            $_SESSION['success'] = "User deleted successfully!";
        } else {
            $_SESSION['error'] = "Failed to delete user: " . mysqli_error($conn);
        }
    }
    header("Location: users.php");
    exit;
}

// Handle add/edit user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_user'])) {
    $user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
    $name = mysqli_real_escape_string($conn, trim($_POST['name']));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $student_id = mysqli_real_escape_string($conn, trim($_POST['student_id']));
    $course = mysqli_real_escape_string($conn, trim($_POST['course']));
    $year_level = mysqli_real_escape_string($conn, trim($_POST['year_level']));
    $role = mysqli_real_escape_string($conn, $_POST['role']);
    $has_scholarship = isset($_POST['has_scholarship']) ? 1 : 0;
    $password = trim($_POST['password']);
    
    if ($user_id > 0) {
        // Update existing user
        if (!empty($password)) {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $sql = "UPDATE users SET name='$name', email='$email', student_id='$student_id', course='$course', year_level='$year_level', role='$role', has_scholarship=$has_scholarship, password_hash='$password_hash' WHERE id=$user_id";
        } else {
            $sql = "UPDATE users SET name='$name', email='$email', student_id='$student_id', course='$course', year_level='$year_level', role='$role', has_scholarship=$has_scholarship WHERE id=$user_id";
        }
        
        if (mysqli_query($conn, $sql)) {
            $_SESSION['success'] = "User updated successfully!";
        } else {
            $_SESSION['error'] = "Failed to update user: " . mysqli_error($conn);
        }
    } else {
        // Add new user
        if (empty($password)) {
            $_SESSION['error'] = "Password is required for new users!";
        } else {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (name, email, student_id, course, year_level, role, has_scholarship, password_hash) VALUES ('$name', '$email', '$student_id', '$course', '$year_level', '$role', $has_scholarship, '$password_hash')";
            
            if (mysqli_query($conn, $sql)) {
                $_SESSION['success'] = "User added successfully!";
            } else {
                $_SESSION['error'] = "Failed to add user: " . mysqli_error($conn);
            }
        }
    }
    header("Location: users.php");
    exit;
}

// Get all users
$users_result = mysqli_query($conn, "SELECT * FROM users ORDER BY role DESC, name ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management | Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root{--primary:#2c3e50;--secondary:#3498db;--success:#2ecc71;--danger:#e74c3c;--warning:#f39c12;--bg:#f4f6f8;--card:#fff;--border:#e5e7eb;--text:#1f2937;--muted:#6b7280;}
        *{margin:0;padding:0;box-sizing:border-box;font-family:'Segoe UI',Tahoma,sans-serif;}
        body{background:var(--bg);color:var(--text);}
        .layout{display:flex;min-height:100vh;}
        .content{flex:1;padding:24px;}
        
        .topbar{background:var(--card);border:1px solid var(--border);border-radius:12px;padding:18px 20px;margin-bottom:20px;display:flex;justify-content:space-between;align-items:center;}
        .topbar h1{font-size:20px;color:var(--primary);display:flex;align-items:center;gap:10px;}
        
        .btn{padding:10px 20px;border-radius:6px;border:none;cursor:pointer;font-weight:600;font-size:14px;transition:all 0.2s;display:inline-flex;align-items:center;gap:8px;text-decoration:none;}
        .btn-primary{background:var(--secondary);color:white;}
        .btn-primary:hover{background:#2980b9;transform:translateY(-2px);box-shadow:0 4px 8px rgba(0,0,0,0.15);}
        .btn-success{background:var(--success);color:white;}
        .btn-success:hover{background:#27ae60;}
        .btn-danger{background:var(--danger);color:white;font-size:12px;padding:6px 12px;}
        .btn-danger:hover{background:#c0392b;}
        .btn-warning{background:var(--warning);color:white;font-size:12px;padding:6px 12px;}
        .btn-warning:hover{background:#e67e22;}
        
        .alert{padding:14px 18px;border-radius:8px;margin-bottom:20px;font-weight:600;display:flex;align-items:center;gap:10px;}
        .alert-success{background:#d1f2eb;color:#0c5e4d;border:1px solid #7dcea0;}
        .alert-danger{background:#fadbd8;color:#922b21;border:1px solid #e6b0aa;}
        
        .panel{background:var(--card);border:1px solid var(--border);border-radius:12px;padding:20px;overflow-x:auto;}
        
        table{width:100%;border-collapse:collapse;}
        thead{background:#f9fafb;border-bottom:2px solid var(--border);}
        th{padding:12px;text-align:left;font-size:13px;font-weight:700;color:var(--primary);text-transform:uppercase;letter-spacing:0.5px;}
        td{padding:14px 12px;border-bottom:1px solid var(--border);font-size:14px;}
        tbody tr:hover{background:#f9fafb;}
        
        .badge{padding:4px 10px;border-radius:12px;font-size:11px;font-weight:700;text-transform:uppercase;}
        .badge-admin{background:#e8f4fd;color:#0369a1;}
        .badge-student{background:#f0fdf4;color:#15803d;}
        .badge-yes{background:#dcfce7;color:#166534;}
        .badge-no{background:#fee2e2;color:#991b1b;}
        
        .actions{display:flex;gap:6px;}
        
        /* Modal */
        .modal{display:none;position:fixed;inset:0;background:rgba(0,0,0,0.6);z-index:1000;align-items:center;justify-content:center;}
        .modal.active{display:flex;}
        .modal-content{background:white;border-radius:12px;max-width:600px;width:90%;max-height:90vh;overflow-y:auto;box-shadow:0 20px 60px rgba(0,0,0,0.3);}
        .modal-header{padding:20px 24px;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center;}
        .modal-header h2{font-size:18px;color:var(--primary);}
        .modal-close{background:none;border:none;font-size:24px;cursor:pointer;color:var(--muted);}
        .modal-close:hover{color:var(--text);}
        .modal-body{padding:24px;}
        
        .form-group{margin-bottom:18px;}
        .form-group label{display:block;margin-bottom:6px;font-weight:600;font-size:13px;color:var(--text);}
        .form-group input, .form-group select{width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:6px;font-size:14px;}
        .form-group input:focus, .form-group select:focus{outline:none;border-color:var(--secondary);box-shadow:0 0 0 3px rgba(52,152,219,0.1);}
        .checkbox-group{display:flex;align-items:center;gap:8px;}
        .checkbox-group input{width:auto;}
        
        .form-actions{display:flex;gap:10px;justify-content:flex-end;margin-top:20px;padding-top:20px;border-top:1px solid var(--border);}
    </style>
</head>
<body>
<div class="layout">
    <?php include "../sidebar/asb.php"; ?>
    
    <main class="content">
        <div class="topbar">
            <h1><i class="fas fa-users"></i> User Management</h1>
            <button class="btn btn-primary" onclick="openAddModal()">
                <i class="fas fa-plus"></i> Add New User
            </button>
        </div>
        
        <?php if ($success_msg): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success_msg) ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error_msg): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error_msg) ?>
            </div>
        <?php endif; ?>
        
        <div class="panel">
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Student ID</th>
                        <th>Course</th>
                        <th>Role</th>
                        <th>Scholarship</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($users_result && mysqli_num_rows($users_result) > 0): ?>
                        <?php while($user = mysqli_fetch_assoc($users_result)): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($user['name']) ?></strong></td>
                                <td><?= htmlspecialchars($user['email']) ?></td>
                                <td><?= htmlspecialchars($user['student_id']) ?></td>
                                <td><?= htmlspecialchars($user['course']) ?> - <?= htmlspecialchars($user['year_level']) ?></td>
                                <td><span class="badge badge-<?= $user['role'] === 'admin' ? 'admin' : 'student' ?>"><?= htmlspecialchars($user['role']) ?></span></td>
                                <td><span class="badge badge-<?= $user['has_scholarship'] ? 'yes' : 'no' ?>"><?= $user['has_scholarship'] ? 'Yes' : 'No' ?></span></td>
                                <td class="actions">
                                    <button class="btn btn-warning" onclick='editUser(<?= json_encode($user) ?>)'>
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <?php if ($user['id'] != $admin_id): ?>
                                        <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this user?')">
                                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                            <button type="submit" name="delete_user" class="btn btn-danger">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align:center;padding:40px;color:var(--muted);">
                                <i class="fas fa-users" style="font-size:3rem;opacity:0.3;margin-bottom:10px;display:block;"></i>
                                No users found.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<!-- Add/Edit User Modal -->
<div class="modal" id="userModal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modalTitle">Add New User</h2>
            <button class="modal-close" onclick="closeModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form method="POST">
                <input type="hidden" name="user_id" id="user_id">
                
                <div class="form-group">
                    <label>Full Name *</label>
                    <input type="text" name="name" id="name" required>
                </div>
                
                <div class="form-group">
                    <label>Email *</label>
                    <input type="email" name="email" id="email" required>
                </div>
                
                <div class="form-group">
                    <label>Student ID *</label>
                    <input type="text" name="student_id" id="student_id" required>
                </div>
                
                <div class="form-group">
                    <label>Course *</label>
                    <input type="text" name="course" id="course" required>
                </div>
                
                <div class="form-group">
                    <label>Year Level *</label>
                    <select name="year_level" id="year_level" required>
                        <option value="1st Year">1st Year</option>
                        <option value="2nd Year">2nd Year</option>
                        <option value="3rd Year">3rd Year</option>
                        <option value="4th Year">4th Year</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Role *</label>
                    <select name="role" id="role" required>
                        <option value="student">Student</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Password <span id="passwordNote">(Leave empty to keep current password)</span></label>
                    <input type="password" name="password" id="password">
                </div>
                
                <div class="form-group">
                    <div class="checkbox-group">
                        <input type="checkbox" name="has_scholarship" id="has_scholarship" value="1">
                        <label for="has_scholarship">Has Active Scholarship</label>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn" onclick="closeModal()" style="background:#e5e7eb;color:var(--text);">Cancel</button>
                    <button type="submit" name="save_user" class="btn btn-success">
                        <i class="fas fa-save"></i> Save User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const modal = document.getElementById('userModal');

function openAddModal() {
    document.getElementById('modalTitle').textContent = 'Add New User';
    document.getElementById('user_id').value = '';
    document.getElementById('name').value = '';
    document.getElementById('email').value = '';
    document.getElementById('student_id').value = '';
    document.getElementById('course').value = '';
    document.getElementById('year_level').value = '1st Year';
    document.getElementById('role').value = 'student';
    document.getElementById('password').value = '';
    document.getElementById('password').required = true;
    document.getElementById('passwordNote').style.display = 'none';
    document.getElementById('has_scholarship').checked = false;
    modal.classList.add('active');
}

function editUser(user) {
    document.getElementById('modalTitle').textContent = 'Edit User';
    document.getElementById('user_id').value = user.id;
    document.getElementById('name').value = user.name;
    document.getElementById('email').value = user.email;
    document.getElementById('student_id').value = user.student_id;
    document.getElementById('course').value = user.course;
    document.getElementById('year_level').value = user.year_level;
    document.getElementById('role').value = user.role;
    document.getElementById('password').value = '';
    document.getElementById('password').required = false;
    document.getElementById('passwordNote').style.display = 'inline';
    document.getElementById('has_scholarship').checked = user.has_scholarship == 1;
    modal.classList.add('active');
}

function closeModal() {
    modal.classList.remove('active');
}

window.addEventListener('click', (e) => {
    if (e.target === modal) closeModal();
});
</script>
</body>
</html>
