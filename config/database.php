<?php
require_once __DIR__ . '/credentials.php';

function getDBConnection() {
    static $conn = null;
    
    // Reuse connection if already established
    if ($conn === null) {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($conn->connect_error) {
            error_log("Database connection failed: " . $conn->connect_error);
            return null;
        }
        $conn->set_charset("utf8mb4");
    }
    
    return $conn;
}

function executePrepared($query, $types = "", $params = []) {
    $conn = getDBConnection();
    if (!$conn) return [null, null];

    $stmt = $conn->prepare($query);
    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error);
        return [null, null];
    }

    if (!empty($types) && !empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    if (!$stmt->execute()) {
        error_log("Execute failed: " . $stmt->error);
        $stmt->close();
        return [null, null];
    }

    return [$conn, $stmt];
}

function executeQuery($query, $types = "", $params = []) {
    [$conn, $stmt] = executePrepared($query, $types, $params);
    if (!$stmt) return false;

    // Return result set for SELECT, boolean for other queries
    if (stripos(trim($query), 'SELECT') === 0) {
        $result = $stmt->get_result();
        $stmt->close();
        return $result;
    } else {
        // For non-SELECT queries, return true on success
        $success = $stmt->affected_rows >= 0;
        $stmt->close();
        return $success;
    }
}

function executeInsert($query, $types = "", $params = []) {
    [$conn, $stmt] = executePrepared($query, $types, $params);
    if (!$stmt) return false;

    $insertId = $stmt->insert_id;
    $stmt->close();
    return $insertId;
}

function executeUpdate($query, $types = "", $params = []) {
    [$conn, $stmt] = executePrepared($query, $types, $params);
    if (!$stmt) return false;

    $affectedRows = $stmt->affected_rows;
    $stmt->close();
    return $affectedRows;
}

// Helper function to determine game outcome
function getGameOutcome($score1, $score2) {
    if ($score1 > $score2) return ['winner' => 1, 'score1' => 1.0];
    if ($score2 > $score1) return ['winner' => 2, 'score1' => 0.0];
    return ['winner' => 0, 'score1' => 0.5];
}

// Helper function to get win/loss/draw increments for a player
function getPlayerGameStats($isPlayer1, $winner) {
    if ($winner === 0) return ['wins' => 0, 'losses' => 0, 'draws' => 1];
    
    $didWin = ($isPlayer1 && $winner === 1) || (!$isPlayer1 && $winner === 2);
    return $didWin 
        ? ['wins' => 1, 'losses' => 0, 'draws' => 0]
        : ['wins' => 0, 'losses' => 1, 'draws' => 0];
}

// Helper function to determine game result text
function getGameResult($isPlayer1, $winner) {
    if ($winner === 0) return 'Draw';
    $didWin = ($isPlayer1 && $winner === 1) || (!$isPlayer1 && $winner === 2);
    return $didWin ? 'Win' : 'Loss';
}
?>
