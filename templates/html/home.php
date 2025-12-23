<?php
$pageTitle = 'Home - Quixote';
$activePage = 'home';
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/nav.php';
?>

    <div class="container">
        <div class="hero">
            <h2>Welcome to Quixote</h2>
            <p>Track competitive Scrabble games with advanced Glicko-2 rating system</p>
        </div>

        <div class="home-grid">
            <div class="home-card">
                <h3>Recent Games</h3>
                <?php if ($data['recentGames'] && $data['recentGames']->num_rows > 0): ?>
                    <div class="recent-games-list">
                        <?php while ($game = $data['recentGames']->fetch_assoc()): ?>
                            <?php
                            $date = date('M j', strtotime($game['gameDate']));
                            $p1Name = sanitizeOutput($game['player1Name']);
                            $p2Name = sanitizeOutput($game['player2Name']);
                            $p1ID = $game['player1ID'];
                            $p2ID = $game['player2ID'];
                            $score = $game['player1Score'] . '-' . $game['player2Score'];
                            
                            $resultText = '';
                            if ($game['winner'] == 1) {
                                $resultText = '<strong>' . $p1Name . '</strong> defeats ' . $p2Name;
                            } elseif ($game['winner'] == 2) {
                                $resultText = '<strong>' . $p2Name . '</strong> defeats ' . $p1Name;
                            } else {
                                $resultText = $p1Name . ' draws with ' . $p2Name;
                            }
                            ?>
                            <div class="game-item">
                                <span class="game-date"><?php echo $date; ?></span>
                                <span class="game-result"><?php echo $resultText; ?></span>
                                <span class="game-score"><?php echo $score; ?></span>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <p class="no-data">No games recorded yet.</p>
                <?php endif; ?>
                <a href="<?php echo BASE_URL; ?>/?page=enter-game" class="btn-primary">Enter New Game</a>
            </div>

            <div class="home-card">
                <h3>Top Players</h3>
                
                <?php if ($data['topPlayers'] && $data['topPlayers']->num_rows > 0): ?>
                    <div class="top-players-list">
                        <?php 
                        $rank = 1;
                        while ($player = $data['topPlayers']->fetch_assoc()): 
                            $name = sanitizeOutput($player['name']);
                            $rating = round($player['rating'], 0);
                            $record = $player['wins'] . '-' . $player['losses'] . '-' . $player['draws'];
                        ?>
                            <div class="player-item">
                                <span class="player-rank"><?php echo $rank; ?></span>
                                <a href="<?php echo BASE_URL; ?>/?page=player&id=<?php echo $player['playerID']; ?>" class="player-link">
                                    <span class="player-name-home"><?php echo $name; ?></span>
                                    <span class="player-rating-home"><?php echo number_format($rating); ?></span>
                                </a>
                                <span class="player-record"><?php echo $record; ?></span>
                            </div>
                        <?php 
                            $rank++;
                        endwhile; 
                        ?>
                    </div>
                <?php else: ?>
                    <p class="no-data">No players with games yet.</p>
                <?php endif; ?>
                <a href="<?php echo BASE_URL; ?>/?page=leaderboard" class="btn-secondary">View Full Leaderboard</a>
            </div>
        </div>
    </div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
