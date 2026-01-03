<?php
session_start();
require_once "conn.php"; // uses $conn

// Only allow POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit;
}

// Get inputs
$email = trim($_POST["email"] ?? "");
$password = $_POST["password"] ?? "";

// Basic validation
if ($email === "" || $password === "") {
    $_SESSION["auth_error"] = "Please enter email and password.";
    header("Location: index.php");
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION["auth_error"] = "Invalid email format.";
    header("Location: index.php");
    exit;
}

// Prepare query
$sql = "SELECT id, role, name, email, password_hash, has_scholarship
        FROM users
        WHERE email = ?
        LIMIT 1";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    $_SESSION["auth_error"] = "Server error. Try again.";
    header("Location: index.php");
    exit;
}

mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$user = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

// Check user exists
if (!$user) {
    $_SESSION["auth_error"] = "Invalid credentials.";
    header("Location: index.php");
    exit;
}

// Verify password
if (!password_verify($password, $user["password_hash"])) {
    $_SESSION["auth_error"] = "Incorrect password. Please try again.";
    header("Location: index.php");
    exit;
}

// Successful login: set session
session_regenerate_id(true);

$_SESSION["user_id"] = (int)$user["id"];
$_SESSION["role"] = $user["role"];
$_SESSION["name"] = $user["name"];
$_SESSION["email"] = $user["email"];
$_SESSION["has_scholarship"] = (int)$user["has_scholarship"];

// Set welcome message
$_SESSION["success"] = "Welcome, " . $user["name"] . "!";

// Redirect based on role
if ($user["role"] !== "admin") {
    // Any role that is not admin = student side
    header("Location: student/dashboard.php");
    exit;
} else {
    header("Location: admin/dashboard.php");
    exit;
}
