<?php
require_once __DIR__ . '/../core/database.php';
use Config\DatabaseConfig;

class models {
    private $pdo;

    public function __construct() {
        $this->pdo = DatabaseConfig::getConnection();
    }

    public function authUser(string $username, string $password): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM ims_users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return ($user && password_verify($password, $user['password'])) ? $user : null;
    }

    public function addItems(
        string $name, string $brand, string $model,
        string $serialNum, string $cat, string $cond,
        string $curr_stat, string $pr, int $qnty, string $borrower,
        int $usrid, string $role
    ): bool {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO ims_devices
                (name, brand, model, serial_number, category, device_condition, current_status, pr, quantity, borrower, usrid, role)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            return $stmt->execute([
                $name, $brand, $model, $serialNum, $cat, $cond,
                $curr_stat, $pr, $qnty, $borrower, $usrid, $role
            ]);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    public function getDevices(string $role): array {
        // All users see devices added by users with the same role
        $stmt = $this->pdo->prepare("SELECT * FROM ims_devices WHERE role = ? ORDER BY d_uid DESC");
        $stmt->execute([$role]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    

    public function updateDevice(
        int $d_uid,
        int $usrid, 
        string $name,
        string $brand,
        string $model,
        string $serialNum,
        string $cat,
        string $cond,
        string $curr_stat,
        string $pr,
        int $qnty,
        string $borrower
    ): bool {
        $stmt = $this->pdo->prepare("SELECT * FROM ims_devices WHERE d_uid=?");
        $stmt->execute([$d_uid]);
        $oldDevice = $stmt->fetch(PDO::FETCH_ASSOC);
    
        if (!$oldDevice) return false;
    
        $stmt = $this->pdo->prepare("
            UPDATE ims_devices SET
                name=?, brand=?, model=?, serial_number=?, category=?, device_condition=?, current_status=?, pr=?, quantity=?, borrower=?
            WHERE d_uid=?
        ");
        $updated = $stmt->execute([$name, $brand, $model, $serialNum, $cat, $cond, $curr_stat, $pr, $qnty, $borrower, $d_uid]);
    
        if ($updated) {
            $stmt = $this->pdo->prepare("
                INSERT INTO ims_device_logs (d_uid, usrid, action, old_data, new_data)
                VALUES (?, ?, 'update', ?, ?)
            ");
            $stmt->execute([
                $d_uid,
                $usrid,
                json_encode($oldDevice, JSON_UNESCAPED_SLASHES),
                json_encode([
                    'd_uid' => $d_uid,
                    'name' => $name,
                    'brand' => $brand,
                    'model' => $model,
                    'serial_number' => $serialNum,
                    'category' => $cat,
                    'device_condition' => $cond,
                    'current_status' => $curr_stat,
                    'pr' => $pr,
                    'quantity' => $qnty,
                    'borrower' => $borrower
                ], JSON_UNESCAPED_SLASHES)
            ]);
        }
    
        return $updated;
    }
    
    public function getDeviceById(int $d_uid): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM ims_devices WHERE d_uid=?");
        $stmt->execute([$d_uid]);
        $device = $stmt->fetch(PDO::FETCH_ASSOC);
        return $device ?: null;
    }

    public function archiveDevice(int $d_uid, int $usrid): bool {
        $stmt = $this->pdo->prepare("SELECT * FROM ims_devices WHERE d_uid=?");
        $stmt->execute([$d_uid]);
        $device = $stmt->fetch(PDO::FETCH_ASSOC);
    
        if (!$device) return false;
    
        $stmt = $this->pdo->prepare("SELECT role FROM ims_users WHERE usrid = ?");
        $stmt->execute([$usrid]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        $role = $user['role'] ?? 'unknown';
    
        try {
            $this->pdo->beginTransaction();
    
            $stmt = $this->pdo->prepare("
                INSERT INTO ims_archive 
                (d_uid, name, brand, model, serial_number, category, device_condition, current_status, pr, quantity, borrower, usrid, role)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $device['d_uid'], 
                $device['name'], 
                $device['brand'], 
                $device['model'], 
                $device['serial_number'],
                $device['category'], 
                $device['device_condition'], 
                $device['current_status'], 
                $device['pr'], 
                $device['quantity'], 
                $device['borrower'],
                $usrid, 
                $role
            ]);
    
            $stmt = $this->pdo->prepare("
                INSERT INTO ims_device_logs (d_uid, usrid, action, old_data, new_data)
                VALUES (?, ?, 'archive', ?, ?)
            ");
            $stmt->execute([
                $d_uid,
                $usrid,
                json_encode($device, JSON_UNESCAPED_SLASHES),
                json_encode([
                    'archived' => true,
                    'archived_at' => date('Y-m-d H:i:s'),
                    'archived_by' => $usrid,
                    'role' => $role
                ], JSON_UNESCAPED_SLASHES)
            ]);
    
            $stmt = $this->pdo->prepare("DELETE FROM ims_devices WHERE d_uid=?");
            $stmt->execute([$d_uid]);
    
            $this->pdo->commit();
            return true;
    
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            error_log("ArchiveDevice Error: " . $e->getMessage());
            return false;
        }
    }
    

    public function getDevicesForSearching(string $role): array {
        $sql = "SELECT * FROM ims_devices WHERE role = :role ORDER BY d_uid DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':role' => $role]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function countDevices(string $role): int {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM ims_devices WHERE role = ?");
        $stmt->execute([$role]);
        return (int)$stmt->fetchColumn();
    }
    


    public function getArchiveLogs(string $role): array
    {
        $query = "
            SELECT 
                a.archive_id,
                a.d_uid,
                a.name,
                a.brand,
                a.model,
                a.serial_number,
                a.category,
                a.device_condition,
                a.current_status,
                a.pr,
                a.quantity,
                a.borrower,
                a.archived_at,
                a.usrid,
                a.role,
                u.username,
                l.action,
                l.old_data,
                l.new_data,
                l.created_at AS log_created_at
            FROM ims_archive a
            LEFT JOIN ims_users u ON a.usrid = u.usrid
            LEFT JOIN ims_device_logs l ON l.d_uid = a.d_uid
            WHERE a.role = :role
            ORDER BY a.archived_at DESC, l.created_at DESC
        ";
    
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['role' => $role]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // for printing
    public function filterDevices(
        ?string $condition = null,
        ?string $status = null,
        ?string $category = null,
        ?string $from_date = null,
        ?string $to_date = null
    ): array {
        $query = "SELECT * FROM ims_devices WHERE 1=1";
        $params = [];
    
        if (!empty($condition)) {
            $query .= " AND device_condition = ?";
            $params[] = $condition;
        }
    
        if (!empty($status)) {
            $query .= " AND current_status = ?";
            $params[] = $status;
        }
    
        if (!empty($category)) {
            $query .= " AND category LIKE ?";
            $params[] = "%$category%";
        }
    
        if (!empty($from_date) && !empty($to_date)) {
            $query .= " AND DATE(created_at) BETWEEN ? AND ?";
            $params[] = $from_date;
            $params[] = $to_date;
        }
    
        $query .= " ORDER BY d_uid DESC";
    
        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
    
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    
    
    
    
}
