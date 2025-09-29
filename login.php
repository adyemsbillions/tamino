<?php
session_start();
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userType = $_POST['userType'] ?? '';
    $action = $_POST['action'] ?? '';

    if ($action === 'logout') {
        // Verify CSRF token
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            header('Location: index.php?error=' . urlencode('Invalid CSRF token'));
            exit;
        }
        // Clear session and redirect
        session_unset();
        session_destroy();
        header('Location: index.php?success=' . urlencode('Logged out successfully'));
        exit;
    }

    if ($action === 'login') {
        if ($userType === 'staff') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            if (empty($email) || empty($password)) {
                header('Location: staff_login.php?error=' . urlencode('All fields are required'));
                exit;
            }

            $stmt = $conn->prepare("SELECT id, password, name FROM staff WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_type'] = 'staff';
                $_SESSION['user_name'] = $user['name'];
                header('Location: staff_dash.php?type=staff');
            } else {
                header('Location: staff_login.php?error=' . urlencode('Invalid email or password'));
            }
            $stmt->close();
        } elseif ($userType === 'admin') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            if (empty($email) || empty($password)) {
                header('Location: admin_login.php?error=' . urlencode('All fields are required'));
                exit;
            }

            $stmt = $conn->prepare("SELECT id, password, name FROM admins WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_type'] = 'admin';
                $_SESSION['user_name'] = $user['name'];
                header('Location: admin_dash.php');
            } else {
                header('Location: admin_login.php?error=' . urlencode('Invalid email or password'));
            }
            $stmt->close();
        } elseif ($userType === 'host') {
            $host_id = $_POST['host_id'] ?? '';
            $passcode = $_POST['passcode'] ?? '';

            if (empty($host_id) || empty($passcode)) {
                header('Location: host_login.php?error=' . urlencode('All fields are required'));
                exit;
            }

            $stmt = $conn->prepare("SELECT id, passcode, name FROM hosts WHERE host_id = ?");
            $stmt->bind_param("s", $host_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $host = $result->fetch_assoc();

            if ($host && password_verify($passcode, $host['passcode'])) {
                $_SESSION['user_id'] = $host['id'];
                $_SESSION['user_type'] = 'host';
                $_SESSION['user_name'] = $host['name'];
                header('Location: host_dash.php');
            } else {
                header('Location: host_login.php?error=' . urlencode('Invalid Host ID or Passcode'));
            }
            $stmt->close();
        } else {
            header('Location: index.php?error=' . urlencode('Invalid user type'));
        }
    } elseif ($action === 'signup') {
        if ($userType === 'admin') {
            // Verify CSRF token
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                header('Location: admin_login.php?error=' . urlencode('Invalid CSRF token'));
                exit;
            }

            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = trim($_POST['password'] ?? '');

            if (empty($name) || empty($email) || empty($password)) {
                header('Location: admin_login.php?error=' . urlencode('All fields are required'));
                exit;
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                header('Location: admin_login.php?error=' . urlencode('Invalid email format'));
                exit;
            }

            if (strlen($password) < 8) {
                header('Location: admin_login.php?error=' . urlencode('Password must be at least 8 characters'));
                exit;
            }

            $stmt = $conn->prepare("SELECT id FROM admins WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                header('Location: admin_login.php?error=' . urlencode('Email already exists'));
                $stmt->close();
                exit;
            }

            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO admins (email, password, name) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $email, $hashed_password, $name);

            if ($stmt->execute()) {
                $stmt->close();
                $stmt = $conn->prepare("SELECT id, name FROM admins WHERE email = ?");
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $result = $stmt->get_result();
                $user = $result->fetch_assoc();
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_type'] = 'admin';
                $_SESSION['user_name'] = $user['name'];
                header('Location: admin_dash.php?success=' . urlencode('Admin account created successfully'));
            } else {
                header('Location: admin_login.php?error=' . urlencode('Failed to create admin account'));
            }
            $stmt->close();
        } else {
            header('Location: admin_login.php?error=' . urlencode('Invalid user type for signup'));
        }
    }
}

$conn->close();
