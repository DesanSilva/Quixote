<?php
if (!defined('APP_ENTRY')) die('Direct access not permitted');

require_once __DIR__ . '/../config/database.php';

$message = '';
$messageType = '';

if (isPostRequest()) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $message = 'Invalid security token. Please try again.';
        $messageType = 'error';
    } else {
        $name = validateString($_POST['name'] ?? '', 100);
        $registrationNumber = validateString($_POST['registrationNumber'] ?? '', 20);
        $faculty = validateString($_POST['faculty'] ?? '', 100);
        
        if ($name === false || strlen($name) < 2) {
            $message = 'Player name must be at least 2 characters.';
            $messageType = 'error';
        } elseif ($registrationNumber && !validateRegistrationNumber($registrationNumber)) {
            $message = 'Registration number must be in format XXXX/XX/XXX';
            $messageType = 'error';
        } else {
            // Check for duplicate player
            $checkQuery = $registrationNumber
                ? "SELECT playerID FROM Player WHERE name = ? OR registrationNumber = ?"
                : "SELECT playerID FROM Player WHERE name = ?";
            $checkParams = $registrationNumber ? [$name, $registrationNumber] : [$name];
            $checkTypes = $registrationNumber ? "ss" : "s";
            $checkResult = executeQuery($checkQuery, $checkTypes, $checkParams);
            
            if ($checkResult && $checkResult->num_rows > 0) {
                $message = 'A player with this name or registration number already exists.';
                $messageType = 'error';
            } else {
                $insertQuery = "INSERT INTO Player (name, registrationNumber, faculty, rating, ratingDeviation, volatility) 
                               VALUES (?, ?, ?, 1500, 350, 0.06)";
                $playerID = executeInsert($insertQuery, "sss", [$name, $registrationNumber, $faculty]);
                
                if ($playerID) {
                    $message = "Player '$name' added successfully!";
                    $messageType = 'success';
                    $_POST = [];
                } else {
                    $message = 'Error adding player. Please try again.';
                    $messageType = 'error';
                }
            }
        }
    }
}

$countResult = executeQuery("SELECT COUNT(*) as total FROM Player");
$playerCount = $countResult ? $countResult->fetch_assoc()['total'] : 0;

$recentPlayers = executeQuery("SELECT playerID, name, registrationNumber, faculty, createdAt 
                               FROM Player ORDER BY createdAt DESC LIMIT 5");

return [
    'message' => $message,
    'messageType' => $messageType,
    'playerCount' => $playerCount,
    'recentPlayers' => $recentPlayers
];
?>
