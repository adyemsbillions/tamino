<?php
session_start();
require_once 'db_connect.php';

header('Content-Type: application/json');

function respond($success, $message, $data = [])
{
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userType = $_POST['userType'] ?? '';

    switch ($userType) {
        case 'staff':
        case 'admin':
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            if (empty($email) || empty($password)) {
                respond(false, 'Email and password are required');
            }

            $table = ($userType === 'staff') ? 'staff' : 'admins';
            $stmt = $conn->prepare("SELECT id, email, password, name FROM $table WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 0) {
                respond(false, 'Invalid credentials');
            }

            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_type'] = $userType;
                $_SESSION['user_name'] = $user['name'];
                respond(true, 'Login successful', ['redirect' => $userType === 'staff' ? 'dashboard.html?type=staff' : 'admin-dashboard.html']);
            } else {
                respond(false, 'Invalid credentials');
            }
            $stmt->close();
            break;

        case 'guest':
            $sessionCode = $_POST['sessionCode'] ?? '';

            if (empty($sessionCode)) {
                respond(false, 'Session code is required');
            }

            $stmt = $conn->prepare("SELECT id, session_code, expires_at, is_active FROM guests WHERE session_code = ?");
            $stmt->bind_param("s", $sessionCode);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 0) {
                respond(false, 'Invalid session code');
            }

            $guest = $result->fetch_assoc();
            if (!$guest['is_active'] || $guest['expires_at'] === null || strtotime($guest['expires_at']) < time()) {
                respond(false, 'Session code is expired, inactive, or invalid');
            }

            $_SESSION['user_id'] = $guest['id'];
            $_SESSION['user_type'] = 'guest';
            respond(true, 'Login successful', ['redirect' => 'guest-dashboard.html']);
            $stmt->close();
            break;

        case 'host':
            $hostType = $_POST['hostType'] ?? '';
            $hostId = $_POST['hostId'] ?? '';
            $passcode = $_POST['passcode'] ?? '';

            if (empty($hostType) || empty($hostId) || empty($passcode)) {
                respond(false, 'Host type, ID, and passcode are required');
            }

            $stmt = $conn->prepare("SELECT id, host_id, passcode, name FROM hosts WHERE host_id = ? AND host_type = ?");
            $stmt->bind_param("ss", $hostId, $hostType);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 0) {
                respond(false, 'Invalid host ID');
            }

            $host = $result->fetch_assoc();
            if (password_verify($passcode, $host['passcode'])) {
                $_SESSION['user_id'] = $host['id'];
                $_SESSION['user_type'] = 'host';
                $_SESSION['host_type'] = $hostType;
                $_SESSION['user_name'] = $host['name'];
                respond(true, 'Login successful', ['redirect' => 'host-dashboard.html']);
            } else {
                respond(false, 'Invalid passcode');
            }
            $stmt->close();
            break;

        default:
            respond(false, 'Invalid user type');
    }
}

$conn->close();
