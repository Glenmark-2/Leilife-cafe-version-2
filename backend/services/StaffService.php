<?php
require_once __DIR__ . '/../repositories/StaffRepository.php';

class StaffService
{
    private $staffRepo;

    public function __construct()
    {
        $this->staffRepo = new StaffRepository();
    }

    public function getAllStaffs()
    {
        return $this->staffRepo->getAllStaffs();
    }

    public function getArchivedStaffs()
    {
        return $this->staffRepo->getArchivedStaffs();
    }

    public function archiveStaff($id, $isArchived)
    {
        return $this->staffRepo->updateArchivedStatus($id, $isArchived);
    }

    public function addStaff($data)
    {
        $inputRole = strtolower($data['role']);
        $finalRole = 'Staff';
        $finalPosition = $data['role']; // e.g. "Cook", "Manager"

        if ($inputRole === 'admin' || $inputRole === 'driver') {
            $finalRole = $inputRole;
            $finalPosition = $data['role']; // e.g. "Admin", "Driver"
        }

        $staffData = [
            'full_name' => $data['fullName'],
            'role' => $finalRole,
            'position' => $finalPosition,
            'shift' => $data['shift'],
            'status' => $data['status'] ?? 'Active',
            'photo_path' => $data['photo_path'] ?? null
        ];

        $staffId = $this->staffRepo->createStaff($staffData);
        if (!$staffId) return false;

        if ($finalRole === 'admin') {
            $adminData = [
                'staff_id' => $staffId,
                'username' => $data['username'],
                'email' => $data['email'],
                'password' => password_hash($data['password'], PASSWORD_DEFAULT)
            ];
            $this->staffRepo->createAdmin($adminData);
        } else if ($finalRole === 'driver') {
            $driverData = [
                'staff_id' => $staffId,
                'username' => $data['username'],
                'email' => $data['email'],
                'password' => password_hash($data['password'], PASSWORD_DEFAULT)
            ];
            $this->staffRepo->createDriver($driverData);
        }

        return $staffId;
    }

    public function getStaff($id)
    {
        return $this->staffRepo->getStaffById($id);
    }

    public function updateStaff($data)
    {
        $inputRole = strtolower($data['role']);
        $finalRole = 'Staff';
        $finalPosition = $data['role'];

        if ($inputRole === 'admin' || $inputRole === 'driver') {
            $finalRole = $inputRole;
            $finalPosition = $data['role'];
        }

        $staffData = [
            'staff_id' => $data['staff_id'],
            'full_name' => $data['fullName'],
            'role' => $finalRole,
            'position' => $finalPosition,
            'shift' => $data['shift'],
            'status' => $data['status'] ?? 'Active'
        ];

        if (isset($data['photo_path'])) {
            // Get old photo path to delete it
            $oldStaff = $this->staffRepo->getStaffById($data['staff_id']);
            if ($oldStaff && $oldStaff['photo_path'] && $oldStaff['photo_path'] !== 'default_user.png') {
                $oldFilePath = __DIR__ . '/../../public/assets/staffs/' . $oldStaff['photo_path'];
                if (file_exists($oldFilePath)) {
                    unlink($oldFilePath);
                }
            }
            $staffData['photo_path'] = $data['photo_path'];
        }

        $success = $this->staffRepo->updateStaff($staffData);
        if (!$success) return false;

        if ($finalRole === 'admin') {
            $adminData = [
                'staff_id' => $data['staff_id'],
                'username' => $data['username'],
                'email' => $data['email']
            ];
            if (!empty($data['password'])) {
                $adminData['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            }
            $this->staffRepo->updateAdmin($adminData);
        } else if ($finalRole === 'driver') {
            $driverData = [
                'staff_id' => $data['staff_id'],
                'username' => $data['username'],
                'email' => $data['email']
            ];
            if (!empty($data['password'])) {
                $driverData['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            }
            $this->staffRepo->updateDriver($driverData);
        }

        return true;
    }
}
