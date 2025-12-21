<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../services/StaffService.php';

// Handle multipart/form-data
$data = $_POST;

if (empty($data['fullName']) || empty($data['role']) || empty($data['shift'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields.']);
    exit;
}

// Additional validation for Admin/Driver
if (in_array(strtolower($data['role']), ['admin', 'driver'])) {
    if (empty($data['email']) || empty($data['password']) || empty($data['username'])) {
        echo json_encode(['success' => false, 'message' => 'Username, Email and Password are required for Admin/Driver.']);
        exit;
    }
}

$photo_path = 'default_user.png';

// Handle Photo Upload
if (isset($_FILES['staffPicture']) && $_FILES['staffPicture']['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['staffPicture']['tmp_name'];
    $fileName = $_FILES['staffPicture']['name'];
    $fileNameCmps = explode(".", $fileName);
    $fileExtension = strtolower(end($fileNameCmps));
    
    $newFileName = str_replace(' ', '_', strtolower($data['fullName'])) . '_' . time() . '.' . $fileExtension;
    $uploadFileDir = __DIR__ . '/../../../public/assets/staffs/';
    $dest_path = $uploadFileDir . $newFileName;
    
    $allowedfileExtensions = array('jpg', 'gif', 'png', 'jpeg', 'webp');
    if (in_array($fileExtension, $allowedfileExtensions)) {
        if (move_uploaded_file($fileTmpPath, $dest_path)) {
            $photo_path = $newFileName;
        } else {
             echo json_encode(['success' => false, 'message' => 'Failed to upload photo. Check permissions.']);
             exit;
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid file extension. Allowed: ' . implode(',', $allowedfileExtensions)]);
        exit;
    }
}

$data['photo_path'] = $photo_path;

$staffService = new StaffService();
$result = $staffService->addStaff($data);

if ($result) {
    echo json_encode(['success' => true, 'message' => 'Staff added successfully!', 'staff_id' => $result]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to save staff data to database.']);
}
