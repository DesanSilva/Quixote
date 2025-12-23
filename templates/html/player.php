<?php
$pageTitle = 'Player Details - Quixote';
$activePage = '';
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/nav.php';
?>

    <div class="container">
        <?php if ($data['error']): ?>
            <div class="message error"><?php echo sanitizeOutput($data['error']); ?></div>
            <a href="<?php echo BASE_URL; ?>/?page=leaderboard" class="btn-primary">Back to Leaderboard</a>
        <?php else: 
            $player = $data['player'];
            $name = sanitizeOutput($player['name']);
            $regNum = $player['registrationNumber'] ? sanitizeOutput($player['registrationNumber']) : 'N/A';
            $faculty = sanitizeOutput($player['faculty']);
            $rating = round($player['rating'], 1);
            $ratingDev = round($player['ratingDeviation'], 1);
            $volatility = round($player['volatility'], 4);
            $wins = $player['wins'];
            $losses = $player['losses'];
            $draws = $player['draws'];
            $gamesPlayed = $player['gamesPlayed'];
            $winPct = $gamesPlayed > 0 ? round(($wins / $gamesPlayed) * 100, 1) : 0;
        ?>

        <div class="player-header">
            <div class="player-info">
                <h2><?php echo $name; ?></h2>
                <p class="faculty"><?php echo $faculty; ?></p>
            </div>
            <div class="player-stats-grid">
                <div class="stat-card">
                    <div class="stat-value"><?php echo number_format($rating, 0); ?></div>
                    <div class="stat-label">Rating</div>
                    <div class="stat-sublabel">±<?php echo $ratingDev; ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo $wins; ?>-<?php echo $losses; ?>-<?php echo $draws; ?></div>
                    <div class="stat-label">Win-Loss-Draw</div>
                    <div class="stat-sublabel"><?php echo $winPct; ?>% win rate</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo $gamesPlayed; ?></div>
                    <div class="stat-label">Games Played</div>
                </div>
            </div>
        </div>

        <h3>Game History</h3>
        
        <?php
        if ($data['gamesResult'] && $data['gamesResult']->num_rows > 0) {
            echo '<div class="games-table-container">';
            echo '<table class="games-table">';
            echo '<thead>';
            echo '<tr>';
            echo '<th>Date</th>';
            echo '<th>Opponent</th>';
            echo '<th>Result</th>';
            echo '<th>Score</th>';
            echo '<th>Opp. Score</th>';
            echo '<th>Margin</th>';
            echo '<th>Bingos</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';
            
            while ($game = $data['gamesResult']->fetch_assoc()) {
                $gameID = $game['gameID'];
                $gameDate = date('M j, Y', strtotime($game['gameDate']));
                
                $isPlayer1 = ($game['player1ID'] == $data['playerID']);
                
                // Determine opponent and scores based on player position
                $opponentName = sanitizeOutput($isPlayer1 ? $game['player2Name'] : $game['player1Name']);
                $opponentID = $isPlayer1 ? $game['player2ID'] : $game['player1ID'];
                $playerScore = $isPlayer1 ? $game['player1Score'] : $game['player2Score'];
                $opponentScore = $isPlayer1 ? $game['player2Score'] : $game['player1Score'];
                $result = getGameResult($isPlayer1, $game['winner']);
                
                $margin = $playerScore - $opponentScore;
                $marginDisplay = ($margin > 0 ? '+' : '') . $margin;

                $gameBingos = $data['bingosByGame'][(int)$gameID] ?? [];
                $bingosCount = count($gameBingos);
                $bingosDisplay = $bingosCount;

                if ($bingosCount > 0) {
                    $bingoWords = [];
                    foreach ($gameBingos as $bingo) {
                        $bingoWords[] = sanitizeOutput($bingo['word']) . ' (' . (int)$bingo['points'] . ')';
                    }
                    $bingosDisplay = $bingosCount . ' <span class="bingo-tooltip" title="' . implode(', ', $bingoWords) . '">ⓘ</span>';
                }
                
                $resultClass = strtolower($result);
                
                echo "<tr class='game-row'>";
                echo "<td>{$gameDate}</td>";
                echo "<td><a href='" . BASE_URL . "/?page=player&id={$opponentID}'>{$opponentName}</a></td>";
                echo "<td class='result {$resultClass}'>{$result}</td>";
                echo "<td class='score'>{$playerScore}</td>";
                echo "<td class='score'>{$opponentScore}</td>";
                echo "<td class='margin'>{$marginDisplay}</td>";
                echo "<td>{$bingosDisplay}</td>";
                echo "</tr>";
            }
            
            echo '</tbody>';
            echo '</table>';
            echo '</div>';
        } else {
            echo '<p class="no-data">No games played yet.</p>';
        }
        ?>

        <div class="additional-stats">
            <h3>Additional Statistics</h3>
            
            <?php
            if ($data['scoreStats']) {
                $avgScore = round($data['scoreStats']['avgScore'], 1);
                $highScore = $data['scoreStats']['highScore'];
                $lowScore = $data['scoreStats']['lowScore'];
                
                $totalBingos = $data['bingoStats']['totalBingos'];
                $totalBingoPoints = $data['bingoStats']['totalBingoPoints'];
                $avgBingosPerGame = $gamesPlayed > 0 ? round($totalBingos / $gamesPlayed, 2) : 0;
                
                echo '<div class="stats-grid">';
                echo '<div class="stat-box">';
                echo '<h4>' . number_format($avgScore, 1) . '</h4>';
                echo '<p>Average Score</p>';
                echo '</div>';
                echo '<div class="stat-box">';
                echo '<h4>' . $highScore . '</h4>';
                echo '<p>Highest Score</p>';
                echo '</div>';
                echo '<div class="stat-box">';
                echo '<h4>' . $lowScore . '</h4>';
                echo '<p>Lowest Score</p>';
                echo '</div>';
                echo '<div class="stat-box">';
                echo '<h4>' . $totalBingos . '</h4>';
                echo '<p>Total Bingos</p>';
                echo '</div>';
                echo '<div class="stat-box">';
                echo '<h4>' . $avgBingosPerGame . '</h4>';
                echo '<p>Bingos per Game</p>';
                echo '</div>';
                echo '</div>';
            }
            ?>
        </div>

        <div class="rating-history">
            <h3>Rating History</h3>
            
            <?php
            if ($data['historyResult'] && $data['historyResult']->num_rows > 0) {
                echo '<table class="history-table">';
                echo '<thead><tr><th>Date</th><th>Result</th><th>Old Rating</th><th>New Rating</th><th>Change</th></tr></thead>';
                echo '<tbody>';
                
                while ($history = $data['historyResult']->fetch_assoc()) {
                    $date = date('M j, Y', strtotime($history['changeDate']));
                    $result = $history['result'];
                    $oldRating = round($history['oldRating'], 1);
                    $newRating = round($history['newRating'], 1);
                    $change = round($newRating - $oldRating, 1);
                    $changeDisplay = ($change > 0 ? '+' : '') . $change;
                    $changeClass = $change > 0 ? 'positive' : ($change < 0 ? 'negative' : 'neutral');
                    
                    echo "<tr>";
                    echo "<td>{$date}</td>";
                    echo "<td class='result " . strtolower($result) . "'>{$result}</td>";
                    echo "<td>" . number_format($oldRating, 0) . "</td>";
                    echo "<td>" . number_format($newRating, 0) . "</td>";
                    echo "<td class='change {$changeClass}'>{$changeDisplay}</td>";
                    echo "</tr>";
                }
                
                echo '</tbody>';
                echo '</table>';
            } else {
                echo '<p class="no-data">No rating history available.</p>';
            }
            ?>
        </div>

        <div class="back-link">
            <a href="<?php echo BASE_URL; ?>/?page=leaderboard" class="btn-secondary">← Back to Leaderboard</a>
            <button type="button" class="btn-remove" onclick="openDeleteModal()">Delete Player</button>
        </div>

        <div id="deleteModal" class="modal">
            <div class="modal-content">
                <h3>Delete Player</h3>
                <p>Are you sure you want to permanently delete <strong><?php echo $name; ?></strong>?</p>
                <p>This will delete:</p>
                <ul>
                    <li>All games involving this player</li>
                    <li>All bingo records</li>
                    <li>All rating history</li>
                    <li>Player profile and statistics</li>
                </ul>
                <p>To confirm, type the following text exactly:</p>
                <div class="confirmation-text">DELETE PLAYER <?php echo strtoupper($name); ?></div>
                <form method="POST" action="<?php echo BASE_URL; ?>/?page=player&id=<?php echo $data['playerID']; ?>" onsubmit="return confirmDelete(event)">
                    <?php echo csrfField(); ?>
                    <input type="hidden" name="action" value="delete">
                    <div class="form-group">
                        <input type="text" id="deleteConfirmation" name="confirmation" placeholder="Type confirmation text here" autocomplete="off">
                    </div>
                    <div class="modal-buttons">
                        <button type="submit" class="btn-remove" id="deleteButton" disabled>Delete Player</button>
                        <button type="button" class="btn-secondary" onclick="closeDeleteModal()">Cancel</button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            const playerName = <?php echo json_encode(strtoupper($name)); ?>;
            const requiredText = 'DELETE PLAYER ' + playerName;
            
            function openDeleteModal() {
                document.getElementById('deleteModal').classList.add('active');
                document.getElementById('deleteConfirmation').value = '';
                document.getElementById('deleteButton').disabled = true;
            }
            
            function closeDeleteModal() {
                document.getElementById('deleteModal').classList.remove('active');
            }
            
            document.getElementById('deleteConfirmation').addEventListener('input', function(e) {
                const deleteButton = document.getElementById('deleteButton');
                if (e.target.value === requiredText) {
                    deleteButton.disabled = false;
                } else {
                    deleteButton.disabled = true;
                }
            });
            
            function confirmDelete(e) {
                const input = document.getElementById('deleteConfirmation').value;
                if (input !== requiredText) {
                    e.preventDefault();
                    alert('Confirmation text does not match.');
                    return false;
                }
                return true;
            }
            
            document.getElementById('deleteModal').addEventListener('click', function(e) {
                if (e.target === this) {
                    closeDeleteModal();
                }
            });
        </script>

        <?php endif; ?>
    </div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
