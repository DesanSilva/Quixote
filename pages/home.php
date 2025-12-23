<?php
if (!defined('APP_ENTRY')) die('Direct access not permitted');

require_once __DIR__ . '/../config/database.php';

$recentGames = executeQuery("SELECT g.gameID, g.gameDate, g.player1Score, g.player2Score, g.winner,
                              p1.name as player1Name, p1.playerID as player1ID,
                              p2.name as player2Name, p2.playerID as player2ID
                              FROM Game g
                              JOIN Player p1 ON g.player1ID = p1.playerID
                              JOIN Player p2 ON g.player2ID = p2.playerID
                              ORDER BY g.gameDate DESC, g.gameID DESC LIMIT 10");

$topPlayers = executeQuery("SELECT playerID, name, rating, wins, losses, draws, gamesPlayed
                            FROM Player WHERE gamesPlayed > 0
                            ORDER BY rating DESC LIMIT 5");

return [
    'recentGames' => $recentGames,
    'topPlayers' => $topPlayers
];
?>
