    <nav>
        <div class="container">
            <h1>Quixote</h1>
            <ul>
                <li><a href="<?php echo BASE_URL; ?>/?page=home" <?php echo (isset($activePage) && $activePage === 'home') ? 'class="active"' : ''; ?>>Home</a></li>
                <li><a href="<?php echo BASE_URL; ?>/?page=add-player" <?php echo (isset($activePage) && $activePage === 'add-player') ? 'class="active"' : ''; ?>>Add Player</a></li>
                <li><a href="<?php echo BASE_URL; ?>/?page=enter-game" <?php echo (isset($activePage) && $activePage === 'enter-game') ? 'class="active"' : ''; ?>>Enter Game</a></li>
                <li><a href="<?php echo BASE_URL; ?>/?page=leaderboard" <?php echo (isset($activePage) && $activePage === 'leaderboard') ? 'class="active"' : ''; ?>>Leaderboard</a></li>
            </ul>
        </div>
    </nav>
