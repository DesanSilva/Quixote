-- Scrabble Score Tracking Database Schema
-- Drop tables if they exist
DROP TABLE IF EXISTS RatingHistory;
DROP TABLE IF EXISTS Bingo;
DROP TABLE IF EXISTS Game;
DROP TABLE IF EXISTS Player;

-- Create Player table
CREATE TABLE Player (
    playerID INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    registrationNumber VARCHAR(20),
    faculty VARCHAR(100),
    rating DECIMAL(10, 4) DEFAULT 1500.0000,
    ratingDeviation DECIMAL(10, 4) DEFAULT 350.0000,
    volatility DECIMAL(10, 6) DEFAULT 0.060000,
    wins INT DEFAULT 0,
    losses INT DEFAULT 0,
    draws INT DEFAULT 0,
    gamesPlayed INT DEFAULT 0,
    lastPlayed TIMESTAMP NULL,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_name (name),
    INDEX idx_rating (rating DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create Game table
CREATE TABLE Game (
    gameID INT AUTO_INCREMENT PRIMARY KEY,
    player1ID INT NOT NULL,
    player2ID INT NOT NULL,
    player1Score INT NOT NULL,
    player2Score INT NOT NULL,
    winner TINYINT NOT NULL COMMENT '1=player1, 2=player2, 0=draw',
    gameDate DATE NOT NULL,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (player1ID) REFERENCES Player(playerID) ON DELETE CASCADE,
    FOREIGN KEY (player2ID) REFERENCES Player(playerID) ON DELETE CASCADE,
    INDEX idx_player1 (player1ID),
    INDEX idx_player2 (player2ID),
    INDEX idx_gameDate (gameDate DESC),
    CHECK (player1ID != player2ID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create Bingo table
CREATE TABLE Bingo (
    bingoID INT AUTO_INCREMENT PRIMARY KEY,
    playerID INT NOT NULL,
    gameID INT NOT NULL,
    word VARCHAR(15) NOT NULL,
    points INT NOT NULL,
    FOREIGN KEY (playerID) REFERENCES Player(playerID) ON DELETE CASCADE,
    FOREIGN KEY (gameID) REFERENCES Game(gameID) ON DELETE CASCADE,
    INDEX idx_player (playerID),
    INDEX idx_game (gameID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create RatingHistory table (optional but useful for tracking)
CREATE TABLE RatingHistory (
    historyID INT AUTO_INCREMENT PRIMARY KEY,
    playerID INT NOT NULL,
    gameID INT NOT NULL,
    oldRating DECIMAL(10, 4) NOT NULL,
    newRating DECIMAL(10, 4) NOT NULL,
    oldRD DECIMAL(10, 4) NOT NULL,
    newRD DECIMAL(10, 4) NOT NULL,
    changeDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (playerID) REFERENCES Player(playerID) ON DELETE CASCADE,
    FOREIGN KEY (gameID) REFERENCES Game(gameID) ON DELETE CASCADE,
    INDEX idx_player (playerID),
    INDEX idx_date (changeDate DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;