<?php
if (!defined('APP_ENTRY')) die('Direct access not permitted');

require_once __DIR__ . '/../config/database.php';

$playersResult = executeQuery("SELECT playerID, name, faculty, rating, ratingDeviation,
                                wins, losses, draws, gamesPlayed, lastPlayed
                                FROM Player ORDER BY rating DESC, name ASC");

$statsResult = executeQuery("SELECT COUNT(*) as totalPlayers, SUM(gamesPlayed) / 2 as totalGames,
                              AVG(rating) as avgRating, MAX(rating) as maxRating, MIN(rating) as minRating
                              FROM Player");
$stats = $statsResult ? $statsResult->fetch_assoc() : null;

return [
    'playersResult' => $playersResult,
    'stats' => $stats
];
?>
