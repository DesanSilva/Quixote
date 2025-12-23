<?php
if (!defined('APP_ENTRY')) die('Direct access not permitted');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/glicko2.php';

$message = '';
$messageType = '';

if (isPostRequest()) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $message = 'Invalid security token. Please try again.';
        $messageType = 'error';
    } else {
        $player1ID = validateInt($_POST['player1ID'] ?? null);
        $player2ID = validateInt($_POST['player2ID'] ?? null);
        $player1Score = validateInt($_POST['player1Score'] ?? null);
        $player2Score = validateInt($_POST['player2Score'] ?? null);
        $gameDate = validateString($_POST['gameDate'] ?? '', 10);
        $player1Bingos = $_POST['player1Bingos'] ?? [];
        $player2Bingos = $_POST['player2Bingos'] ?? [];
        
        if ($player1ID === false || $player2ID === false || $player1ID === $player2ID) {
            $message = 'Invalid or identical player selection.';
            $messageType = 'error';
        } elseif ($player1Score === false || $player2Score === false || $player1Score < 0 || $player2Score < 0) {
            $message = 'Invalid scores. Must be non-negative integers.';
            $messageType = 'error';
        } elseif (!$gameDate || !validateDate($gameDate)) {
            $message = 'Invalid date format.';
            $messageType = 'error';
        } else {
            $conn = getDBConnection();
            $conn->begin_transaction();
            
            try {
                // Determine game outcome
                $outcome = getGameOutcome($player1Score, $player2Score);
                $winner = $outcome['winner'];
                $score1 = $outcome['score1'];
                
                $query = "SELECT playerID, rating, ratingDeviation, volatility FROM Player WHERE playerID IN (?, ?)";
                $result = executeQuery($query, "ii", [$player1ID, $player2ID]);
                
                $players = [];
                while ($row = $result->fetch_assoc()) {
                    $players[$row['playerID']] = [
                        'rating' => (float)$row['rating'],
                        'ratingDeviation' => (float)$row['ratingDeviation'],
                        'volatility' => (float)$row['volatility']
                    ];
                }
                
                if (count($players) !== 2) {
                    throw new Exception('One or both players not found.');
                }
                
                $newRatings = Glicko2::calculateNewRatings(
                    $players[$player1ID],
                    $players[$player2ID],
                    $score1
                );
                
                $gameQuery = "INSERT INTO Game (player1ID, player2ID, player1Score, player2Score, winner, gameDate) 
                              VALUES (?, ?, ?, ?, ?, ?)";
                $gameID = executeInsert($gameQuery, "iiiiss", [
                    $player1ID, $player2ID, $player1Score, $player2Score, $winner, $gameDate
                ]);
                
                if (!$gameID) {
                    throw new Exception('Failed to insert game record.');
                }
                
                $updateQuery = "UPDATE Player SET 
                               rating = ?, ratingDeviation = ?, volatility = ?,
                               wins = wins + ?, losses = losses + ?, draws = draws + ?,
                               gamesPlayed = gamesPlayed + 1, lastPlayed = NOW()
                               WHERE playerID = ?";
                
                // Update player 1 stats
                $p1Stats = getPlayerGameStats(true, $winner);
                
                executeUpdate($updateQuery, "dddiiii", [
                    $newRatings['player1']['rating'],
                    $newRatings['player1']['ratingDeviation'],
                    $newRatings['player1']['volatility'],
                    $p1Stats['wins'], $p1Stats['losses'], $p1Stats['draws'],
                    $player1ID
                ]);
                
                // Update player 2 stats
                $p2Stats = getPlayerGameStats(false, $winner);
                
                executeUpdate($updateQuery, "dddiiii", [
                    $newRatings['player2']['rating'],
                    $newRatings['player2']['ratingDeviation'],
                    $newRatings['player2']['volatility'],
                    $p2Stats['wins'], $p2Stats['losses'], $p2Stats['draws'],
                    $player2ID
                ]);
                
                // Record rating changes for historical tracking
                $historyQuery = "INSERT INTO RatingHistory (playerID, gameID, oldRating, newRating, oldRD, newRD)
                                VALUES (?, ?, ?, ?, ?, ?)";
                executeInsert($historyQuery, "iidddd", [
                    $player1ID, $gameID,
                    $players[$player1ID]['rating'], $newRatings['player1']['rating'],
                    $players[$player1ID]['ratingDeviation'], $newRatings['player1']['ratingDeviation']
                ]);
                executeInsert($historyQuery, "iidddd", [
                    $player2ID, $gameID,
                    $players[$player2ID]['rating'], $newRatings['player2']['rating'],
                    $players[$player2ID]['ratingDeviation'], $newRatings['player2']['ratingDeviation']
                ]);
                
                // Record bingo words for both players (optional)
                $bingoQuery = "INSERT INTO Bingo (playerID, gameID, word, points) VALUES (?, ?, ?, ?)";
                foreach ([$player1ID => $player1Bingos, $player2ID => $player2Bingos] as $playerID => $bingos) {
                    foreach ($bingos as $bingo) {
                        $word = validateString($bingo['word'] ?? '', 15);
                        $points = validateInt($bingo['points'] ?? null);
                        if ($word && $points !== false) {
                            executeInsert($bingoQuery, "iisi", [$playerID, $gameID, $word, $points]);
                        }
                    }
                }
                
                $conn->commit();
                
                $message = 'Game recorded successfully! Ratings updated.';
                $messageType = 'success';
                
            } catch (Exception $e) {
                $conn->rollback();
                $message = 'Error recording game: ' . sanitizeOutput($e->getMessage());
                $messageType = 'error';
                error_log("Game entry error: " . $e->getMessage());
            }
        }
    }
}

$playersResult = executeQuery("SELECT playerID, name, registrationNumber, faculty FROM Player ORDER BY name");
$players = [];
if ($playersResult) {
    while ($row = $playersResult->fetch_assoc()) $players[] = $row;
}

return [
    'message' => $message,
    'messageType' => $messageType,
    'players' => $players
];
?>
