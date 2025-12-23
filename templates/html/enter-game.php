<?php
$pageTitle = 'Enter Game - Quixote';
$activePage = 'enter-game';
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/nav.php';
?>

    <div class="container">
        <h2>Enter Game Results</h2>

        <?php if ($data['message']): ?>
            <div class="message <?php echo $data['messageType']; ?>">
                <?php echo sanitizeOutput($data['message']); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?php echo BASE_URL; ?>/?page=enter-game" class="game-form" id="gameForm">
            <?php echo csrfField(); ?>
            <div class="form-row">
                <div class="form-group half">
                    <h3>Player 1</h3>
                    <label for="player1ID">Select Player:</label>
                    <select name="player1ID" id="player1ID" required>
                        <option value="">-- Select Player --</option>
                        <?php foreach ($data['players'] as $player): ?>
                            <option value="<?php echo $player['playerID']; ?>">
                                <?php echo sanitizeOutput($player['name']); ?>
                                <?php if ($player['registrationNumber']): ?>
                                    (<?php echo sanitizeOutput($player['registrationNumber']); ?>)
                                <?php elseif ($player['faculty']): ?>
                                    (<?php echo sanitizeOutput($player['faculty']); ?>)
                                <?php endif; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <label for="player1Score">Score:</label>
                    <input type="number" name="player1Score" id="player1Score" min="0" required>
                    
                    <div class="bingos-section">
                        <label>Bingos (optional):</label>
                        <div id="player1BingosContainer"></div>
                        <button type="button" class="btn-small" onclick="addBingo(1)">+ Add Bingo</button>
                    </div>
                </div>

                <div class="form-group half">
                    <h3>Player 2</h3>
                    <label for="player2ID">Select Player:</label>
                    <select name="player2ID" id="player2ID" required>
                        <option value="">-- Select Player --</option>
                        <?php foreach ($data['players'] as $player): ?>
                            <option value="<?php echo $player['playerID']; ?>">
                                <?php echo sanitizeOutput($player['name']); ?>
                                <?php if ($player['registrationNumber']): ?>
                                    (<?php echo sanitizeOutput($player['registrationNumber']); ?>)
                                <?php elseif ($player['faculty']): ?>
                                    (<?php echo sanitizeOutput($player['faculty']); ?>)
                                <?php endif; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <label for="player2Score">Score:</label>
                    <input type="number" name="player2Score" id="player2Score" min="0" required>
                    
                    <div class="bingos-section">
                        <label>Bingos (optional):</label>
                        <div id="player2BingosContainer"></div>
                        <button type="button" class="btn-small" onclick="addBingo(2)">+ Add Bingo</button>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="gameDate">Game Date:</label>
                <input type="date" name="gameDate" id="gameDate" value="<?php echo date('Y-m-d'); ?>" required>
            </div>

            <button type="submit" class="btn-primary">Record Game</button>
        </form>
    </div>

    <script>
        let bingoCounters = {1: 0, 2: 0};

        function addBingo(playerNum) {
            const container = document.getElementById(`player${playerNum}BingosContainer`);
            const bingoIndex = bingoCounters[playerNum]++;
            
            const bingoDiv = document.createElement('div');
            bingoDiv.className = 'bingo-entry';
            bingoDiv.innerHTML = `
                <input type="text" 
                       name="player${playerNum}Bingos[${bingoIndex}][word]" 
                       placeholder="Word" 
                       maxlength="15">
                <input type="number" 
                       name="player${playerNum}Bingos[${bingoIndex}][points]" 
                       placeholder="Points" 
                       min="50" 
                       max="150">
                <button type="button" class="btn-remove" onclick="this.parentElement.remove()">×</button>
            `;
            container.appendChild(bingoDiv);
        }

        document.getElementById('player1ID').addEventListener('change', validatePlayers);
        document.getElementById('player2ID').addEventListener('change', validatePlayers);

        function validatePlayers() {
            const player1 = document.getElementById('player1ID').value;
            const player2 = document.getElementById('player2ID').value;
            
            if (player1 && player2 && player1 === player2) {
                alert('Please select different players.');
                document.getElementById('player2ID').value = '';
            }
        }
    </script>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
