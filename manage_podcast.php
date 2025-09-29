<?php
session_start();
require_once 'db_connect.php';

// Check if host is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'host') {
    header('Location: host_login.php?error=' . urlencode('Please log in to manage podcasts'));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        header('Location: host_dash.php?error=' . urlencode('Invalid CSRF token'));
        exit;
    }

    $action = $_POST['action'] ?? '';
    $host_id = $_SESSION['user_id'];

    if ($action === 'add' || $action === 'edit') {
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if (empty($name)) {
            header('Location: host_dash.php?error=' . urlencode('Podcast name is required'));
            exit;
        }

        if ($action === 'add') {
            $stmt = $conn->prepare("INSERT INTO podcasts (host_id, name, description) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $host_id, $name, $description);
        } else { // edit
            $podcast_id = trim($_POST['podcast_id'] ?? '');
            if (empty($podcast_id)) {
                header('Location: host_dash.php?error=' . urlencode('Invalid podcast ID'));
                exit;
            }

            // Verify podcast belongs to host
            $stmt = $conn->prepare("SELECT id FROM podcasts WHERE id = ? AND host_id = ?");
            $stmt->bind_param("ii", $podcast_id, $host_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows === 0) {
                header('Location: host_dash.php?error=' . urlencode('Unauthorized access to podcast'));
                $stmt->close();
                exit;
            }
            $stmt->close();

            $stmt = $conn->prepare("UPDATE podcasts SET name = ?, description = ? WHERE id = ?");
            $stmt->bind_param("ssi", $name, $description, $podcast_id);
        }

        if ($stmt->execute()) {
            header('Location: host_dash.php?success=' . urlencode('Podcast ' . $action . 'ed successfully'));
        } else {
            header('Location: host_dash.php?error=' . urlencode('Failed to ' . $action . ' podcast'));
        }
        $stmt->close();
    } elseif ($action === 'delete') {
        $podcast_id = trim($_POST['podcast_id'] ?? '');
        if (empty($podcast_id)) {
            header('Location: host_dash.php?error=' . urlencode('Invalid podcast ID'));
            exit;
        }

        // Verify podcast belongs to host
        $stmt = $conn->prepare("SELECT id FROM podcasts WHERE id = ? AND host_id = ?");
        $stmt->bind_param("ii", $podcast_id, $host_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            header('Location: host_dash.php?error=' . urlencode('Unauthorized access to podcast'));
            $stmt->close();
            exit;
        }
        $stmt->close();

        $stmt = $conn->prepare("DELETE FROM podcasts WHERE id = ?");
        $stmt->bind_param("i", $podcast_id);
        if ($stmt->execute()) {
            header('Location: host_dash.php?success=' . urlencode('Podcast deleted successfully'));
        } else {
            header('Location: host_dash.php?error=' . urlencode('Failed to delete podcast'));
        }
        $stmt->close();
    } elseif ($action === 'assign_staff') {
        $podcast_id = trim($_POST['podcast_id'] ?? '');
        $staff_ids = $_POST['staff_ids'] ?? [];

        if (empty($podcast_id)) {
            header('Location: host_dash.php?error=' . urlencode('Invalid podcast ID'));
            exit;
        }

        // Verify podcast belongs to host
        $stmt = $conn->prepare("SELECT id FROM podcasts WHERE id = ? AND host_id = ?");
        $stmt->bind_param("ii", $podcast_id, $host_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            header('Location: host_dash.php?error=' . urlencode('Unauthorized access to podcast'));
            $stmt->close();
            exit;
        }
        $stmt->close();

        // Clear existing assignments
        $stmt = $conn->prepare("DELETE FROM podcast_staff WHERE podcast_id = ?");
        $stmt->bind_param("i", $podcast_id);
        $stmt->execute();
        $stmt->close();

        // Add new assignments
        if (!empty($staff_ids)) {
            $stmt = $conn->prepare("INSERT INTO podcast_staff (podcast_id, staff_id) VALUES (?, ?)");
            foreach ($staff_ids as $staff_id) {
                $stmt->bind_param("ii", $podcast_id, $staff_id);
                $stmt->execute();
            }
            $stmt->close();
        }

        header('Location: host_dash.php?success=' . urlencode('Staff assigned successfully'));
    }
}

$conn->close();
