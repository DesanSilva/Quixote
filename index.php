<?php
session_start();

define('APP_ENTRY', true);
define('BASE_URL', rtrim(dirname($_SERVER['SCRIPT_NAME']), '/'));

require_once __DIR__ . '/includes/security.php';
setSecurityHeaders();

// Simple routing: validate and load requested page
$allowedPages = ['home', 'add-player', 'enter-game', 'leaderboard', 'player'];
$page = preg_replace('/[^a-z-]/', '', strtolower($_GET['page'] ?? 'home'));
$page = in_array($page, $allowedPages, true) ? $page : 'home';

$controllerPath = __DIR__ . '/pages/' . $page . '.php';
$templatePath = __DIR__ . '/templates/html/' . $page . '.php';

// Execute controller (gets data), then render template (displays data)
if (file_exists($controllerPath) && file_exists($templatePath)) {
    $data = require_once $controllerPath;
    require_once $templatePath;
} else {
    http_response_code(404);
    die('<!DOCTYPE html><html><head><title>404 Not Found</title></head><body><h1>404 - Page Not Found</h1><p>The requested page does not exist.</p><a href="/">Return to Home</a></body></html>');
}
?>
