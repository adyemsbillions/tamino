<?php
session_start();
require_once 'db_connect.php';

// Validate CSRF token
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    header('Location: podcast_details.php?id=' . urlencode($_POST['podcast_id'] ?? '') . '&error=' . urlencode('Invalid CSRF token'));
    exit;
}

$action = $_POST['action'] ?? '';
$podcast_id = trim($_POST['podcast_id'] ?? '');
$host_id = $_SESSION['user_id'];

// Validate podcast ownership and status (except for 'add' action)
if ($action !== 'add') {
    if (empty($podcast_id)) {
        header('Location: host_dash.php?error=' . urlencode('Invalid podcast ID'));
        exit;
    }
    $stmt = $conn->prepare("SELECT id, status FROM podcasts WHERE id = ? AND host_id = ?");
    $stmt->bind_param("ii", $podcast_id, $host_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $podcast = $result->fetch_assoc();
    if (!$podcast) {
        header('Location: podcast_details.php?id=' . urlencode($podcast_id) . '&error=' . urlencode('Unauthorized access'));
        $stmt->close();
        exit;
    }
    // Check if podcast is approved for actions other than edit/delete
    if (in_array($action, ['assign_staff', 'add_task', 'remove_staff']) && $podcast['status'] !== 'approved') {
        header('Location: podcast_details.php?id=' . urlencode($podcast_id) . '&error=' . urlencode('Podcast must be approved by admin to perform this action'));
        $stmt->close();
        exit;
    }
    $stmt->close();
}

if ($action === 'add') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    if (empty($name)) {
        header('Location: host_dash.php?error=' . urlencode('Podcast name is required'));
        exit;
    }
    $stmt = $conn->prepare("INSERT INTO podcasts (name, description, host_id, status) VALUES (?, ?, ?, 'pending')");
    $stmt->bind_param("ssi", $name, $description, $host_id);
    $stmt->execute();
    $new_podcast_id = $conn->insert_id;
    $stmt->close();
    header('Location: podcast_details.php?id=' . urlencode($new_podcast_id) . '&success=' . urlencode('Podcast created successfully and is pending admin approval'));
    exit;
} elseif ($action === 'edit') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    if (empty($name)) {
        header('Location: podcast_details.php?id=' . urlencode($podcast_id) . '&error=' . urlencode('Podcast name is required'));
        exit;
    }
    $stmt = $conn->prepare("UPDATE podcasts SET name = ?, description = ? WHERE id = ? AND host_id = ?");
    $stmt->bind_param("ssii", $name, $description, $podcast_id, $host_id);
    $stmt->execute();
    $stmt->close();
    header('Location: podcast_details.php?id=' . urlencode($podcast_id) . '&success=' . urlencode('Podcast updated successfully'));
    exit;
} elseif ($action === 'assign_staff') {
    // Clear existing staff assignments
    $stmt = $conn->prepare("DELETE FROM podcast_staff WHERE podcast_id = ?");
    $stmt->bind_param("i", $podcast_id);
    $stmt->execute();
    $stmt->close();

    // Add new staff assignments
    if (!empty($_POST['staff_ids'])) {
        $stmt = $conn->prepare("INSERT INTO podcast_staff (podcast_id, staff_id) VALUES (?, ?)");
        foreach ($_POST['staff_ids'] as $staff_id) {
            $stmt->bind_param("ii", $podcast_id, $staff_id);
            $stmt->execute();
        }
        $stmt->close();
    }
    header('Location: podcast_details.php?id=' . urlencode($podcast_id) . '&success=' . urlencode('Staff assigned successfully'));
    exit;
} elseif ($action === 'add_task') {
    $staff_ids = $_POST['staff_ids'] ?? [];
    $description = trim($_POST['description'] ?? '');
    if (empty($staff_ids) || empty($description)) {
        header('Location: podcast_details.php?id=' . urlencode($podcast_id) . '&error=' . urlencode('Staff and description are required'));
        exit;
    }
    $pdf_path = null;
    if (isset($_FILES['pdf_file']) && $_FILES['pdf_file']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'Uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $pdf_name = uniqid('task_') . '.pdf';
        $pdf_path = $upload_dir . $pdf_name;
        if (!move_uploaded_file($_FILES['pdf_file']['tmp_name'], $pdf_path)) {
            header('Location: podcast_details.php?id=' . urlencode($podcast_id) . '&error=' . urlencode('Failed to upload PDF'));
            exit;
        }
    }
    $stmt = $conn->prepare("INSERT INTO tasks (podcast_id, staff_id, description, pdf_file_path, status, created_at) VALUES (?, ?, ?, ?, 'pending', NOW())");
    foreach ($staff_ids as $staff_id) {
        $stmt->bind_param("iiss", $podcast_id, $staff_id, $description, $pdf_path);
        $stmt->execute();
    }
    $stmt->close();
    header('Location: podcast_details.php?id=' . urlencode($podcast_id) . '&success=' . urlencode('Task added successfully'));
    exit;
} elseif ($action === 'remove_staff') {
    $staff_id = trim($_POST['staff_id'] ?? '');
    if (empty($staff_id)) {
        header('Location: podcast_details.php?id=' . urlencode($podcast_id) . '&error=' . urlencode('Invalid staff ID'));
        exit;
    }
    $stmt = $conn->prepare("DELETE FROM podcast_staff WHERE podcast_id = ? AND staff_id = ?");
    $stmt->bind_param("ii", $podcast_id, $staff_id);
    $stmt->execute();
    $stmt->close();
    header('Location: podcast_details.php?id=' . urlencode($podcast_id) . '&success=' . urlencode('Staff removed successfully'));
    exit;
} elseif ($action === 'delete') {
    $stmt = $conn->prepare("DELETE FROM tasks WHERE podcast_id = ?");
    $stmt->bind_param("i", $podcast_id);
    $stmt->execute();
    $stmt->close();
    $stmt = $conn->prepare("DELETE FROM podcast_staff WHERE podcast_id = ?");
    $stmt->bind_param("i", $podcast_id);
    $stmt->execute();
    $stmt->close();
    $stmt = $conn->prepare("DELETE FROM podcasts WHERE id = ? AND host_id = ?");
    $stmt->bind_param("ii", $podcast_id, $host_id);
    $stmt->execute();
    $stmt->close();
    header('Location: host_dash.php?success=' . urlencode('Podcast deleted successfully'));
    exit;
} else {
    header('Location: podcast_details.php?id=' . urlencode($podcast_id) . '&error=' . urlencode('Invalid action'));
    exit;
}