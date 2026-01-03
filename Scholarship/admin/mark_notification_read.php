<?php
session_start();
require_once "../conn.php";

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$admin_id = (int)$_SESSION['user_id'];

// Check if marking all as read
if (isset($_POST['mark_all']) && $_POST['mark_all'] == '1') {
    $sql = "UPDATE notifications SET is_read = 1 WHERE admin_id = $admin_id AND is_read = 0";
    $result = mysqli_query($conn, $sql);
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'All notifications marked as read']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($conn)]);
    }
    exit;
}

// Mark single notification as read
if (isset($_POST['notif_id'])) {
    $notif_id = (int)$_POST['notif_id'];
    
    // Verify this notification belongs to the current admin
    $check = mysqli_query($conn, "SELECT id FROM notifications WHERE id = $notif_id AND admin_id = $admin_id");
    
    if (mysqli_num_rows($check) > 0) {
        $sql = "UPDATE notifications SET is_read = 1 WHERE id = $notif_id";
        $result = mysqli_query($conn, $sql);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Notification marked as read']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($conn)]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Notification not found']);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid request']);
?>
