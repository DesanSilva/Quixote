<?php
if (!defined('APP_ENTRY')) die('Direct access not permitted');

require_once __DIR__ . '/../config/database.php';

$playerID = validateInt($_GET['id'] ?? null);

// Handle delete request
if (isPostRequest() && ($_POST['action'] ?? '') === 'delete') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        die('Invalid security token.');
    }
    
    $playerResult = executeQuery("SELECT name FROM Player WHERE playerID = ?", "i", [$playerID]);
    if ($playerResult && $playerResult->num_rows > 0) {
        $player = $playerResult->fetch_assoc();
        $expectedConfirmation = 'DELETE PLAYER ' . strtoupper($player['name']);
        $confirmation = $_POST['confirmation'] ?? '';
        
        if ($confirmation === $expectedConfirmation) {
            // Delete player (cascade will handle related records)
            $deleteQuery = "DELETE FROM Player WHERE playerID = ?";
            $affected = executeUpdate($deleteQuery, "i", [$playerID]);
            
            if ($affected !== false && $affected > 0) {
                header('Location: ' . BASE_URL . '/?page=leaderboard');
                exit();
            } else {
                die('Error deleting player from database or player not found.');
            }
        }
    }
    
    die('Invalid confirmation or player not found.');
}

if ($playerID === false) {
    return ['error' => 'Invalid player ID.', 'player' => null];
}

$playerResult = executeQuery("SELECT * FROM Player WHERE playerID = ?", "i", [$playerID]);

if (!$playerResult || $playerResult->num_rows === 0) {
    return ['error' => 'Player not found.', 'player' => null];
}

$player = $playerResult->fetch_assoc();

$gamesResult = executeQuery("SELECT g.gameID, g.player1ID, g.player2ID, g.player1Score, g.player2Score,
                              g.winner, g.gameDate, p1.name as player1Name, p2.name as player2Name
                              FROM Game g
                              JOIN Player p1 ON g.player1ID = p1.playerID
                              JOIN Player p2 ON g.player2ID = p2.playerID
                              WHERE g.player1ID = ? OR g.player2ID = ?
                              ORDER BY g.gameDate DESC, g.gameID DESC", "ii", [$playerID, $playerID]);

$bingosByGame = [];
$bingosResult = executeQuery("SELECT gameID, word, points FROM Bingo WHERE playerID = ? ORDER BY gameID DESC", "i", [$playerID]);
if ($bingosResult) {
    while ($bingoRow = $bingosResult->fetch_assoc()) {
        $gameID = (int)$bingoRow['gameID'];
        if (!isset($bingosByGame[$gameID])) $bingosByGame[$gameID] = [];
        $bingosByGame[$gameID][] = [
            'word' => (string)$bingoRow['word'],
            'points' => (int)$bingoRow['points']
        ];
    }
}

$scoreStats = executeQuery("SELECT 
                            AVG(CASE WHEN player1ID = ? THEN player1Score ELSE player2Score END) as avgScore,
                            MAX(CASE WHEN player1ID = ? THEN player1Score ELSE player2Score END) as highScore,
                            MIN(CASE WHEN player1ID = ? THEN player1Score ELSE player2Score END) as lowScore
                            FROM Game WHERE player1ID = ? OR player2ID = ?",
                            "iiiii", [$playerID, $playerID, $playerID, $playerID, $playerID])
                            ->fetch_assoc();

$bingoStats = executeQuery("SELECT COUNT(*) as totalBingos, SUM(points) as totalBingoPoints 
                            FROM Bingo WHERE playerID = ?", "i", [$playerID])->fetch_assoc();

$historyResult = executeQuery("SELECT rh.oldRating, rh.newRating, rh.changeDate, g.gameDate,
                                CASE 
                                    WHEN g.player1ID = ? AND g.winner = 1 THEN 'Win'
                                    WHEN g.player2ID = ? AND g.winner = 2 THEN 'Win'
                                    WHEN g.winner = 0 THEN 'Draw'
                                    ELSE 'Loss'
                                END as result
                                FROM RatingHistory rh
                                JOIN Game g ON rh.gameID = g.gameID
                                WHERE rh.playerID = ?
                                ORDER BY rh.changeDate DESC LIMIT 10", "iii", [$playerID, $playerID, $playerID]);

return [
    'error' => null,
    'player' => $player,
    'playerID' => $playerID,
    'gamesResult' => $gamesResult,
    'bingosByGame' => $bingosByGame,
    'scoreStats' => $scoreStats,
    'bingoStats' => $bingoStats,
    'historyResult' => $historyResult
];
?>
