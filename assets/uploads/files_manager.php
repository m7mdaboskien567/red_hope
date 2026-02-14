<?php
header('Content-Type: application/json');
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Super Admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$uploadDir = __DIR__ . '/';
$metaFile = __DIR__ . '/ai_docs_meta.json';

if (!file_exists($metaFile)) {
    file_put_contents($metaFile, json_encode([]));
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'upload':
        handleUpload($uploadDir, $metaFile);
        break;
    case 'list':
        handleList($metaFile);
        break;
    case 'toggle_status':
        handleToggleStatus($metaFile);
        break;
    case 'delete':
        handleDelete($uploadDir, $metaFile);
        break;
    default:
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
        break;
}

function handleUpload($dir, $metaFile)
{
    if (!isset($_FILES['file'])) {
        echo json_encode(['success' => false, 'error' => 'No file uploaded']);
        return;
    }

    $file = $_FILES['file'];
    $fileName = $file['name'];
    $fileTmp = $file['tmp_name'];
    $fileSize = $file['size'];
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    if ($fileExt !== 'txt') {
        echo json_encode(['success' => false, 'error' => 'Only .txt files are allowed']);
        return;
    }

    $uniqueName = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9\._-]/', '', $fileName);
    $targetPath = $dir . $uniqueName;

    if (move_uploaded_file($fileTmp, $targetPath)) {
        $meta = json_decode(file_get_contents($metaFile), true);
        $meta[$uniqueName] = [
            'original_name' => $fileName,
            'size' => $fileSize,
            'uploaded_at' => date('Y-m-d H:i:s'),
            'status' => 'inactive'
        ];
        file_put_contents($metaFile, json_encode($meta, JSON_PRETTY_PRINT));

        echo json_encode(['success' => true, 'message' => 'File uploaded successfully']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to move uploaded file']);
    }
}

function handleList($metaFile)
{
    $meta = json_decode(file_get_contents($metaFile), true);
    uasort($meta, function ($a, $b) {
        return strtotime($b['uploaded_at']) - strtotime($a['uploaded_at']);
    });
    $response = [];
    foreach ($meta as $id => $data) {
        $data['id'] = $id;
        $response[] = $data;
    }

    echo json_encode(['success' => true, 'files' => $response]);
}

function handleToggleStatus($metaFile)
{
    $id = $_POST['id'] ?? '';
    if (!$id) {
        echo json_encode(['success' => false, 'error' => 'No file ID provided']);
        return;
    }

    $meta = json_decode(file_get_contents($metaFile), true);
    if (!isset($meta[$id])) {
        echo json_encode(['success' => false, 'error' => 'File not found']);
        return;
    }

    $currentStatus = $meta[$id]['status'];
    $newStatus = ($currentStatus === 'active') ? 'inactive' : 'active';
    $meta[$id]['status'] = $newStatus;

    file_put_contents($metaFile, json_encode($meta, JSON_PRETTY_PRINT));
    echo json_encode(['success' => true, 'new_status' => $newStatus]);
}

function handleDelete($dir, $metaFile)
{
    $id = $_POST['id'] ?? '';
    if (!$id) {
        echo json_encode(['success' => false, 'error' => 'No file ID provided']);
        return;
    }

    $meta = json_decode(file_get_contents($metaFile), true);
    if (!isset($meta[$id])) {
        echo json_encode(['success' => false, 'error' => 'File metadata not found']);
        return;
    }

    $filePath = $dir . $id;
    if (file_exists($filePath)) {
        unlink($filePath);
    }
    unset($meta[$id]);
    file_put_contents($metaFile, json_encode($meta, JSON_PRETTY_PRINT));

    echo json_encode(['success' => true, 'message' => 'File deleted']);
}
