<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../services/StaffService.php';

$data = $_POST;

if (empty($data['staff_id']) || empty($data['fullName']) || empty($data['role']) || empty($data['shift'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields.']);
    exit;
}

// Additional validation for Admin/Driver
if (in_array(strtolower($data['role']), ['admin', 'driver'])) {
    if (empty($data['email']) || empty($data['username'])) {
        echo json_encode(['success' => false, 'message' => 'Username and Email are required for Admin/Driver.']);
        exit;
    }
}

// Handle Photo Upload
if (isset($_FILES['staffPicture']) && $_FILES['staffPicture']['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['staffPicture']['tmp_name'];
    $fileName = $_FILES['staffPicture']['name'];
    $fileSize = $_FILES['staffPicture']['size'];
    $fileType = $_FILES['staffPicture']['type'];
    $fileNameCmps = explode(".", $fileName);
    $fileExtension = strtolower(end($fileNameCmps));

    $newFileName = str_replace(' ', '_', strtolower($data['fullName'])) . '_' . time() . '.' . $fileExtension;
    $uploadFileDir = __DIR__ . '/../../../public/assets/staffs/';
    $dest_path = $uploadFileDir . $newFileName;

    $allowedfileExtensions = array('jpg', 'gif', 'png', 'jpeg', 'webp');
    if (in_array($fileExtension, $allowedfileExtensions)) {
        if (move_uploaded_file($fileTmpPath, $dest_path)) {
            $data['photo_path'] = $newFileName;
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to upload photo.']);
            exit;
        }
    }
}

$staffService = new StaffService();
$success = $staffService->updateStaff($data);

if ($success) {
    echo json_encode(['success' => true, 'message' => 'Staff updated successfully!']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update staff data.']);
}
