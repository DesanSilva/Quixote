<?php
$pageTitle = 'Leaderboard - Quixote';
$activePage = 'leaderboard';
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/nav.php';
?>

    <div class="container">
        <h2>Player Leaderboard</h2>

        <div class="leaderboard-container">
            <table class="leaderboard-table">
                <thead>
                    <tr>
                        <th>Rank</th>
                        <th>Player Name</th>
                        <th>Faculty</th>
                        <th>Rating</th>
                        <th>W-L-D</th>
                        <th>Games</th>
                        <th>Win %</th>
                        <th>Last Played</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($data['playersResult'] && $data['playersResult']->num_rows > 0) {
                        $rank = 1;
                        while ($row = $data['playersResult']->fetch_assoc()) {
                            $playerID = $row['playerID'];
                            $name = sanitizeOutput($row['name']);
                            $faculty = sanitizeOutput($row['faculty']);
                            $rating = round($row['rating'], 1);
                            $ratingDev = round($row['ratingDeviation'], 1);
                            $wins = $row['wins'];
                            $losses = $row['losses'];
                            $draws = $row['draws'];
                            $gamesPlayed = $row['gamesPlayed'];
                            $lastPlayed = $row['lastPlayed'] ? date('M j, Y', strtotime($row['lastPlayed'])) : 'Never';
                            
                            $winPct = $gamesPlayed > 0 ? round(($wins / $gamesPlayed) * 100, 1) : 0;
                            $ratingClass = $ratingDev > 200 ? 'uncertain-rating' : 'confident-rating';
                            
                            echo "<tr class='player-row' onclick='window.location=\"" . BASE_URL . "/?page=player&id={$playerID}\"'>";
                            echo "<td class='rank'>{$rank}</td>";
                            echo "<td class='player-name'>{$name}</td>";
                            echo "<td>{$faculty}</td>";
                            echo "<td class='rating {$ratingClass}'>" . number_format($rating, 0) . 
                                 " <span class='rating-dev'>±{$ratingDev}</span></td>";
                            echo "<td class='record'>{$wins}-{$losses}-{$draws}</td>";
                            echo "<td>{$gamesPlayed}</td>";
                            echo "<td>{$winPct}%</td>";
                            echo "<td class='last-played'>{$lastPlayed}</td>";
                            echo "</tr>";
                            
                            $rank++;
                        }
                    } else {
                        echo "<tr><td colspan='8' class='no-data'>No players found. Add players to get started!</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <div class="stats-summary">
            <?php
            if ($data['stats']) {
                $totalPlayers = $data['stats']['totalPlayers'] ?? 0;
                $totalGames = $data['stats']['totalGames'] ?? 0;
                $avgRating = round($data['stats']['avgRating'] ?? 0, 1);
                $maxRating = round($data['stats']['maxRating'] ?? 0, 1);
                $minRating = round($data['stats']['minRating'] ?? 0, 1);
                
                echo "<div class='stats-grid'>";
                echo "<div class='stat-box'>";
                echo "<h3>" . number_format($totalPlayers) . "</h3>";
                echo "<p>Total Players</p>";
                echo "</div>";
                echo "<div class='stat-box'>";
                echo "<h3>" . number_format($totalGames) . "</h3>";
                echo "<p>Total Games</p>";
                echo "</div>";
                echo "<div class='stat-box'>";
                echo "<h3>" . number_format($avgRating, 0) . "</h3>";
                echo "<p>Average Rating</p>";
                echo "</div>";
                echo "<div class='stat-box'>";
                echo "<h3>" . number_format($maxRating, 0) . " / " . number_format($minRating, 0) . "</h3>";
                echo "<p>Highest / Lowest</p>";
                echo "</div>";
                echo "</div>";
            }
            ?>
        </div>
    </div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
