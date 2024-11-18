-- create_new_database.sql

CREATE DATABASE Darkened_Minds_Tournaments
CHARACTER SET utf8mb4
COLLATE utf8mb4_0900_ai_ci;

USE Darkened_Minds_Tournaments;

CREATE TABLE `Games` (
  `game_id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL UNIQUE, -- Name of the game (e.g., Fortnite, Apex)
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO `Games` (`name`) 
VALUES 
    ('Apex'), 
    ('Fortnite'), 
    ('Valorant'), 
    ('COD'), 
    ('Other');

CREATE TABLE `Tournament_Types` (
  `type_id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL UNIQUE, -- Name of the format
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO `Tournament_Types` (`name`) 
VALUES 
    ('standard'), 
    ('duos'), 
    ('trios');

-- Tournaments Table
CREATE TABLE `Tournaments` (
  `tournament_id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `game_id` INT NOT NULL, -- Links to the Games table
  `tournament_type` INT NOT NULL, -- Links to the types table
  `tournament_date` DATE NOT NULL,
  `registration_start_date` DATE NOT NULL,
  `registration_end_date` DATE NOT NULL,
  `format` VARCHAR(50) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`game_id`) REFERENCES `Games`(`game_id`) ON DELETE CASCADE,
    FOREIGN KEY (`tournament_type`) REFERENCES `Tournament_Types`(`type_id`) ON DELETE CASCADE
);

-- Players Table
CREATE TABLE `Players` (
  `player_id` INT AUTO_INCREMENT PRIMARY KEY,
  `gamertag` VARCHAR(255) NOT NULL UNIQUE,
  `discord_name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) DEFAULT NULL,
  `discord_id` BIGINT NOT NULL COMMENT 'Discord user ID, unique across Discord',
  `discord_username` VARCHAR(255) DEFAULT NULL COMMENT 'Current Discord username',
  `discord_discriminator` VARCHAR(10) DEFAULT NULL COMMENT 'User discriminator (e.g., #1234 or 0)',
  `discord_roles` JSON DEFAULT NULL COMMENT 'JSON array of role IDs assigned in the server',
  `discord_joined_at` TIMESTAMP DEFAULT NULL COMMENT 'Date and time the user joined the server',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE `Registrations` (
  `registration_id` INT AUTO_INCREMENT PRIMARY KEY,
  `player_id` INT NOT NULL, -- Links to Players table
  `tournament_id` INT NOT NULL, -- Links to Tournaments table
  `partner_gamertag` VARCHAR(255) DEFAULT NULL,
  `fee_paid` BOOLEAN DEFAULT FALSE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `additional_data` VARCHAR(255) DEFAULT NULL,
  FOREIGN KEY (`player_id`) REFERENCES `Players`(`player_id`) ON DELETE CASCADE,
  FOREIGN KEY (`tournament_id`) REFERENCES `Tournaments`(`tournament_id`) ON DELETE CASCADE
);

CREATE TABLE `Game_Stats` (
  `stats_id` INT AUTO_INCREMENT PRIMARY KEY,
  `registration_id` INT NOT NULL, -- Links to the player registration
  `tournament_id` INT NOT NULL, -- Links to the tournament
  `gamertag` VARCHAR(255) NOT NULL, -- Gamertag of the player
  `game_id` INT NOT NULL, -- Links to the Games table
  `kills` INT DEFAULT 0, -- Total kills from the retrieved stats
  `damage` INT DEFAULT 0, -- Total damage from the retrieved stats
  `matches_played` INT DEFAULT 0, -- Matches played
  `win_rate` DECIMAL(5, 2) DEFAULT NULL, -- Win percentage (if available)
  `kdr` DECIMAL(5, 2) DEFAULT NULL, -- Kill/Death Ratio
  `additional_data` JSON DEFAULT NULL, -- Any extra data not covered by the columns
  `retrieved_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP, -- When the stats were retrieved
  FOREIGN KEY (`registration_id`) REFERENCES `Registrations`(`registration_id`) ON DELETE CASCADE,
  FOREIGN KEY (`tournament_id`) REFERENCES `Tournaments`(`tournament_id`) ON DELETE CASCADE,
    FOREIGN KEY (`game_id`) REFERENCES `Games`(`game_id`) ON DELETE CASCADE
);


-- Teams Table
CREATE TABLE `Teams` (
  `team_id` INT AUTO_INCREMENT PRIMARY KEY,
  `tournament_id` INT NOT NULL,
  `name` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`tournament_id`) REFERENCES `Tournaments`(`tournament_id`) ON DELETE CASCADE
);

-- Team_Players Table
CREATE TABLE `Team_Players` (
  `team_player_id` INT AUTO_INCREMENT PRIMARY KEY,
  `team_id` INT NOT NULL,
  `player_id` INT NOT NULL,
  `designation` ENUM('Leader', 'Player 2', 'Player 3') NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`team_id`) REFERENCES `Teams`(`team_id`) ON DELETE CASCADE,
  FOREIGN KEY (`player_id`) REFERENCES `Players`(`player_id`) ON DELETE CASCADE
);


CREATE TABLE Tournament_Finances (
    finance_id INT AUTO_INCREMENT PRIMARY KEY,
    tournament_id INT NOT NULL,
    seed_money DECIMAL(10, 2) DEFAULT 0.00,
    entry_fee DECIMAL(10, 2) NOT NULL,         -- Adding entry_fee as a column
    registration_count INT DEFAULT 0,           -- Adding registration_count as a column
    total_expected_pot DECIMAL(10, 2) GENERATED ALWAYS AS (entry_fee * registration_count) STORED,
    total_collected DECIMAL(10, 2) DEFAULT 0.00,
    difference DECIMAL(10, 2) GENERATED ALWAYS AS (total_collected - total_expected_pot) STORED,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (tournament_id) REFERENCES Tournaments(tournament_id)
);

CREATE TABLE `Prizes` (
  `prize_id` INT AUTO_INCREMENT PRIMARY KEY,
  `tournament_id` INT NOT NULL,
  `position` ENUM('1st', '2nd', '3rd') NOT NULL,
  `description` VARCHAR(255),
  `value` DECIMAL(10, 2) DEFAULT 0.00,
  FOREIGN KEY (`tournament_id`) REFERENCES `Tournaments`(`tournament_id`) ON DELETE CASCADE
);


-- Scores Table
CREATE TABLE `Scores` (
  `score_id` INT AUTO_INCREMENT PRIMARY KEY,
  `tournament_id` INT NOT NULL,
  `team_id` INT NOT NULL,
  `round_number` INT NOT NULL,
  `kills` INT DEFAULT 0,
  `damage` INT DEFAULT 0,
  `revives` INT DEFAULT 0,
  `placement_points` INT DEFAULT 0,
  `bonus_points` INT DEFAULT 0,
  `total_points` INT AS (kills + damage + revives + placement_points + bonus_points) STORED,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`tournament_id`) REFERENCES `Tournaments`(`tournament_id`) ON DELETE CASCADE,
  FOREIGN KEY (`team_id`) REFERENCES `Teams`(`team_id`) ON DELETE CASCADE
);

-- Historical_Tournaments Table
CREATE TABLE `Historical_Tournaments` (
  `historic_id` INT AUTO_INCREMENT PRIMARY KEY,
  `tournament_id` INT NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `completion_date` DATE NOT NULL,
  `winning_team_id` INT NOT NULL,
  `prize_pool` DECIMAL(10, 2) DEFAULT 0.00,
  `total_rounds` INT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`tournament_id`) REFERENCES `Tournaments`(`tournament_id`) ON DELETE CASCADE
);

CREATE TABLE `Prize_Distribution` (
  `distribution_id` INT AUTO_INCREMENT PRIMARY KEY,
  `tournament_id` INT NOT NULL, -- Links to the tournament
  `prize_id` INT NOT NULL, -- Links to the Prizes table
  `team_id` INT NOT NULL, -- Links to the winning team
  `status` ENUM('Pending', 'Sent', 'Failed') NOT NULL DEFAULT 'Pending', -- Status of prize distribution
  `sent_date` TIMESTAMP DEFAULT NULL, -- When the prize was sent (if applicable)
  `notes` VARCHAR(255) DEFAULT NULL, -- Optional notes for tracking (e.g., delivery details, failures)
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`tournament_id`) REFERENCES `Tournaments`(`tournament_id`) ON DELETE CASCADE,
  FOREIGN KEY (`prize_id`) REFERENCES `Prizes`(`prize_id`) ON DELETE CASCADE,
  FOREIGN KEY (`team_id`) REFERENCES `Teams`(`team_id`) ON DELETE CASCADE
);

CREATE TABLE `Feedback` (
  `feedback_id` INT AUTO_INCREMENT PRIMARY KEY,
  `tournament_id` INT NOT NULL,
  `player_id` INT NOT NULL,
  `rating` INT CHECK (rating BETWEEN 1 AND 5), -- Player rating (1 to 5 stars)
  `comments` TEXT DEFAULT NULL, -- Player comments
  `submitted_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`tournament_id`) REFERENCES `Tournaments`(`tournament_id`) ON DELETE CASCADE,
  FOREIGN KEY (`player_id`) REFERENCES `Players`(`player_id`) ON DELETE CASCADE
);

CREATE TABLE `Admin_Activity` (
  `activity_id` INT AUTO_INCREMENT PRIMARY KEY,
  `admin_id` INT NOT NULL, -- Links to an Admins table
  `action_type` ENUM('Create Tournament', 'Update Scores', 'Distribute Prizes', 'Update Registration') NOT NULL,
  `details` TEXT DEFAULT NULL, -- Description of the action
  `action_time` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE `Tournament_Revenue` (
  `revenue_id` INT AUTO_INCREMENT PRIMARY KEY,
  `tournament_id` INT NOT NULL,
  `entry_fees_collected` DECIMAL(10, 2) DEFAULT 0.00,
  `sponsorships` DECIMAL(10, 2) DEFAULT 0.00,
  `expenses` DECIMAL(10, 2) DEFAULT 0.00,
  `net_profit` DECIMAL(10, 2) GENERATED ALWAYS AS (entry_fees_collected + sponsorships - expenses) STORED,
  `last_updated` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`tournament_id`) REFERENCES `Tournaments`(`tournament_id`) ON DELETE CASCADE
);

CREATE TABLE `Custom_Rules` (
  `rule_id` INT AUTO_INCREMENT PRIMARY KEY,
  `tournament_id` INT NOT NULL,
  `rule_description` TEXT NOT NULL,
  `points_awarded` INT DEFAULT 0,
  FOREIGN KEY (`tournament_id`) REFERENCES `Tournaments`(`tournament_id`) ON DELETE CASCADE
);

