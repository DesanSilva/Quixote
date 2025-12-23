-- Database Cleaner Script
-- This script removes all game data and resets player statistics
-- Use with caution - this action cannot be undone!

-- Disable foreign key checks temporarily
SET FOREIGN_KEY_CHECKS = 0;

-- Clear all game-related data
TRUNCATE TABLE Player;
TRUNCATE TABLE RatingHistory;
TRUNCATE TABLE Bingo;
TRUNCATE TABLE Game;

-- Reset all player statistics to initial values
UPDATE Player SET 
    rating = 1500.0000,
    ratingDeviation = 350.0000,
    volatility = 0.060000,
    wins = 0,
    losses = 0,
    draws = 0,
    gamesPlayed = 0,
    lastPlayed = NULL;

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- Show result
SELECT 'Database cleaned successfully!' as Status;
SELECT COUNT(*) as RemainingPlayers FROM Player;
SELECT COUNT(*) as RemainingGames FROM Game;
SELECT COUNT(*) as RemainingBingos FROM Bingo;
SELECT COUNT(*) as RemainingHistory FROM RatingHistory;
