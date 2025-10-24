<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_httponly' => true,
        'cookie_secure'   => true,
        'use_strict_mode' => true,
        'cookie_samesite' => 'Strict',
    ]);
}

header('Content-Type: application/json; charset=utf-8');
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

require_once __DIR__ . '/../app/controllers/controllers.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(['error' => 'Method Not Allowed']); exit; }

    $input = json_decode(file_get_contents('php://input'), true);

    if (!is_array($input)) $input = $_POST;

    $action = strtolower($input['action'] ?? '');
    if (!$action) { http_response_code(400); echo json_encode(['error' => 'Missing action param']); exit; }

    $allowed = ['login', 'logout', 'additem', 'edititem', 'archiveitem'];
    if (!in_array($action, $allowed, true)) { http_response_code(400); echo json_encode(['error' => 'Invalid action']); exit; }

    $controller = new controllers();

    switch ($action) {
        case 'login':
            $response = $controller->authUser($input);
            break;

        case 'additem':
            $response = $controller->addItems($input);
            break;
        
        case 'edititem':
            $response = $controller->editItems($input);
            break;

        case 'archiveitem':
            $response = $controller->archiveItem($input);
            break;

        case 'logout':
            session_destroy();
            $response = ['success' => true, 'message' => 'Logged out'];
            break;
    }

    echo json_encode($response, JSON_UNESCAPED_SLASHES);

} catch (Throwable $e) { error_log($e->getMessage()); http_response_code(500); echo json_encode(['error' => 'I.S Err']); }
