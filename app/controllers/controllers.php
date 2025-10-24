<?php
require_once __DIR__ . '/../models/models.php';

class controllers {
    private models $model;

    public function __construct() {
        $this->model = new models();
    }

    public function authUser(array $input): array {
        $username = trim($input['username'] ?? '');
        $password = $input['password'] ?? '';
        $csrf     = $input['tkn_csrf'] ?? '';

        if (empty($_SESSION['tkn_csrf']) || !hash_equals($_SESSION['tkn_csrf'], $csrf)) {
            http_response_code(403);
            return ['success' => false, 'error' => 'Invalid CSRF token'];
        }

        if (!$username || !$password) {
            http_response_code(400);
            return ['success' => false, 'error' => 'Missing username or password'];
        }

        $user = $this->model->authUser($username, $password);
        if (!$user) {
            return ['success' => false, 'error' => 'Invalid credentials'];
        }

        session_regenerate_id(true);
        $_SESSION['logged_in'] = true;
        $_SESSION['user_id']   = $user['usrid'];
        $_SESSION['username']  = $user['username'];
        $_SESSION['role']      = $user['role'];

        return [
            'success' => true,
            'message' => 'Login successful',
            'location' => ['redirect' => 'dashboard.php']
        ];
    }

    public function addItems(array $input): array {
        $usrid = isset($input['usrid']) ? (int)$input['usrid'] : 0;
        $role = $input['role'] ?? '';
        $name = trim($input['name'] ?? '');
        $brand = trim($input['brand'] ?? '');
        $model = trim($input['model'] ?? '');
        $serialNum = trim($input['serialNum'] ?? '');
        $cat = trim($input['category'] ?? '');
        $cond = trim($input['condition'] ?? '');
        $curr_stat = trim($input['current_status'] ?? '');
        $qnty = isset($input['quantity']) ? (int)$input['quantity'] : 0;
        $pr = trim($input['pr'] ?? '');
        $borrower = trim($input['borrower'] ?? '');

        $required = [
            'name' => $name,
            'brand' => $brand,
            'model' => $model,
            'serialNum' => $serialNum,
            'cat' => $cat,
            'cond' => $cond,
            'curr_stat' => $curr_stat,
            'qnty' => $qnty,
            'pr' => $pr
        ];

        foreach ($required as $field => $value) {
            if (empty($value)) {
                http_response_code(400);
                return ['success' => false, 'error' => ucfirst($field) . ' is required'];
            }
        }

        try {
            $added = $this->model->addItems(
                $name, $brand, $model, $serialNum, $cat, $cond,
                $curr_stat, $pr, $qnty, $borrower, $usrid, $role
            );

            return $added
                ? ['success' => true, 'message' => 'Device added successfully']
                : ['success' => false, 'error' => 'Failed to add device'];

        } catch (\Throwable $e) {
            error_log($e->getMessage());
            http_response_code(500);
            return ['success' => false, 'error' => 'Internal server error'];
        }
    }

    public function editItems(array $input): array {
        $d_uid = (int)($input['d_uid'] ?? 0);
        $usrid = (int)($_SESSION['user_id'] ?? 0);
        $name = trim($input['name'] ?? '');
        $brand = trim($input['brand'] ?? '');
        $model = trim($input['model'] ?? '');
        $serialNum = trim($input['serialNum'] ?? '');
        $cat = trim($input['category'] ?? '');
        $cond = trim($input['device_condition'] ?? '');
        $curr_stat = trim($input['current_status'] ?? '');
        $qnty = (int)($input['quantity'] ?? 0);
        $pr = trim($input['pr'] ?? '');
        $borrower = trim($input['borrower'] ?? '');
    
        if (!$d_uid || !$name || !$brand || !$model || !$serialNum) {
            http_response_code(400);
            return ['success' => false, 'error' => 'Missing required fields'];
        }
    
        try {
            $updated = $this->model->updateDevice($d_uid, $usrid, $name, $brand, $model, $serialNum, $cat, $cond, $curr_stat, $pr, $qnty, $borrower);
    
            if ($updated) {
                $device = $this->model->getDeviceById($d_uid);
                return ['success' => true, 'message' => 'Device updated successfully', 'device' => $device];
            }
            return ['success' => false, 'error' => 'Failed to update device'];
        } catch (\Throwable $e) {
            error_log($e->getMessage());
            http_response_code(500);
            return ['success' => false, 'error' => 'Internal server error'];
        }
    }

    public function archiveItem(array $input): array
        {
        
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            $d_uid = isset($input['d_uid']) ? (int)$input['d_uid'] : 0;
            $usrid = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;

            if ($d_uid <= 0 || $usrid <= 0) {
                http_response_code(400);
                return [
                    'success' => false,
                    'error' => 'Missing or invalid device/user ID.'
                ];
            }

            try {
                $archived = $this->model->archiveDevice($d_uid, $usrid);

                if ($archived) {
                    return [
                        'success' => true,
                        'message' => 'Device archived successfully.'
                    ];
                } else {
                    http_response_code(500);
                    return [
                        'success' => false,
                        'error' => 'Failed to archive the device. Please try again.'
                    ];
                }

            } catch (\Throwable $e) {
                error_log("[ArchiveItem Error] " . $e->getMessage());
                http_response_code(500);
                return [
                    'success' => false,
                    'error' => 'Internal server error.'
                ];
            }
        }

    
    
}
