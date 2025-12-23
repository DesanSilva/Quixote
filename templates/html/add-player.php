<?php
$pageTitle = 'Add Player - Quixote';
$activePage = 'add-player';
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/nav.php';
?>

    <div class="container">
        <h2>Add New Player</h2>

        <?php if ($data['message']): ?>
            <div class="message <?php echo $data['messageType']; ?>">
                <?php echo sanitizeOutput($data['message']); ?>
                <?php if ($data['messageType'] === 'success'): ?>
                    <br><br>
                    <a href="<?php echo BASE_URL; ?>/?page=leaderboard" class="btn-secondary">View Leaderboard</a>
                    <a href="<?php echo BASE_URL; ?>/?page=enter-game" class="btn-primary">Enter Game</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="form-container">
            <form method="POST" action="<?php echo BASE_URL; ?>/?page=add-player" class="player-form">
                <?php echo csrfField(); ?>
                <div class="form-group">
                    <label for="name">Player Name: <span class="required">*</span></label>
                    <input 
                        type="text" 
                        name="name" 
                        id="name" 
                        placeholder="Enter player's full name"
                        value="<?php echo isset($_POST['name']) ? sanitizeOutput($_POST['name']) : ''; ?>"
                        required
                        minlength="2"
                        maxlength="100"
                        autofocus>
                    <small>Minimum 2 characters, maximum 100 characters</small>
                </div>

                <div class="form-group">
                    <label for="registrationNumber">Registration Number:</label>
                    <input 
                        type="text" 
                        name="registrationNumber" 
                        id="registrationNumber" 
                        placeholder="e.g., 2024/SS/123"
                        pattern="\d{1,4}/[A-Z]{1,2}/\d{1,3}"
                        value="<?php echo isset($_POST['registrationNumber']) ? sanitizeOutput($_POST['registrationNumber']) : ''; ?>"
                        maxlength="20">
                    <small>Format: XXXX/XX/XXX (e.g., 2024/CS/123) - Optional</small>
                </div>

                <div class="form-group">
                    <label for="faculty">Faculty/Department:</label>
                    <input 
                        type="text" 
                        name="faculty" 
                        id="faculty" 
                        placeholder="e.g., Engineering, Science, Arts"
                        value="<?php echo isset($_POST['faculty']) ? sanitizeOutput($_POST['faculty']) : ''; ?>"
                        maxlength="100">
                    <small>Optional - helps organize players</small>
                </div>

                <div class="info-box-small">
                    <h4>Initial Rating Values</h4>
                    <p>New players start with:</p>
                    <ul>
                        <li><strong>Rating:</strong> 1500 (average)</li>
                        <li><strong>Rating Deviation:</strong> 350 (high uncertainty)</li>
                        <li><strong>Volatility:</strong> 0.06 (standard)</li>
                    </ul>
                    <p>These values will adjust as the player competes in games.</p>
                </div>

                <div class="button-group">
                    <button type="submit" class="btn-primary">Add Player</button>
                    <button type="reset" class="btn-secondary">Clear Form</button>
                </div>
            </form>
        </div>

        <div class="stats-box">
            <h3>Current Players</h3>
            <p class="large-number"><?php echo $data['playerCount']; ?></p>
            <p>Total players in database</p>
            <a href="<?php echo BASE_URL; ?>/?page=leaderboard" class="btn-secondary">View All Players</a>
        </div>

        <?php if ($data['recentPlayers'] && $data['recentPlayers']->num_rows > 0): ?>
            <div class="recent-players">
                <h3>Recently Added Players</h3>
                <table class="simple-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Registration No.</th>
                            <th>Faculty</th>
                            <th>Added</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($player = $data['recentPlayers']->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo sanitizeOutput($player['name']); ?></td>
                                <td><?php echo $player['registrationNumber'] ? sanitizeOutput($player['registrationNumber']) : '-'; ?></td>
                                <td><?php echo sanitizeOutput($player['faculty']); ?></td>
                                <td><?php echo date('M j, Y', strtotime($player['createdAt'])); ?></td>
                                <td>
                                    <a href="<?php echo BASE_URL; ?>/?page=player&id=<?php echo $player['playerID']; ?>" class="btn-small">View</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
