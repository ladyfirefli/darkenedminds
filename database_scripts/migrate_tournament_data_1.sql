-- migrate_tournament_data.sql

-- Step 1: Create Historical_Tournaments table if it doesn’t exist
CREATE TABLE IF NOT EXISTS Historical_Tournaments (
    historical_id INT AUTO_INCREMENT PRIMARY KEY,
    tournament_name VARCHAR(100) NOT NULL,
    tournament_date DATE NOT NULL,
    team_id INT,
    master_player_id INT,
    padawan_player_id INT,
    round_number INT,
    master_kills INT,
    padawan_kills INT,
    master_damage DECIMAL(10,0),
    padawan_damage DECIMAL(10,0),
    revives_points INT,
    placement_points INT,
    total_score DECIMAL(10,0),
    tournament_id INT NOT NULL,
    -- Add additional fields as needed
    FOREIGN KEY (team_id) REFERENCES Teams(team_id),
    FOREIGN KEY (master_player_id) REFERENCES registrations(id),
    FOREIGN KEY (padawan_player_id) REFERENCES registrations(id),
    FOREIGN KEY (tournament_id) REFERENCES Tournaments(tournament_id)
);

-- Step 2: Tournament-Specific Metadata Table
CREATE TABLE Tournaments (
    tournament_id INT AUTO_INCREMENT PRIMARY KEY,
    tournament_name VARCHAR(100) NOT NULL,
    start_date DATE,
    registration_end_date DATE,
    end_date DATE,
    tournament_type ENUM('standard', 'duos', 'trios') DEFAULT 'standard',
    point_structure JSON, -- JSON field for flexible point allocation and rules
    is_active TINYINT(1) DEFAULT 1,
    entry_fee DECIMAL(10, 2) DEFAULT 0.00,
    seed_money DECIMAL(10, 2) DEFAULT 0.00;
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

CREATE TABLE Tournament_Prizes (
    prize_id INT AUTO_INCREMENT PRIMARY KEY,
    tournament_id INT NOT NULL,
    first_place_prize DECIMAL(10, 2),             -- Cash prize based on the tournament pot
    second_place_description VARCHAR(255),        -- Item description for second place
    second_place_cost DECIMAL(10, 2) DEFAULT 0.00, -- Cost of the second-place prize
    third_place_description VARCHAR(255),         -- Item description for third place
    third_place_cost DECIMAL(10, 2) DEFAULT 0.00, -- Cost of the third-place prize
    FOREIGN KEY (tournament_id) REFERENCES Tournaments(tournament_id)
);

-- Step 3: Update registrations table to track if the player is active in the current tournament
ALTER TABLE registrations
ADD COLUMN is_active TINYINT(1) DEFAULT 1,
ADD COLUMN current_tournament_id INT,
ADD COLUMN last_tournament_date DATE,
ADD COLUMN registration_date DATE,
ADD FOREIGN KEY (current_tournament_id) REFERENCES Tournaments(tournament_id);

-- Step 4: Link teams and scores to tournaments
ALTER TABLE Teams
ADD COLUMN tournament_id INT;

ALTER TABLE Teams
ADD CONSTRAINT fk_teams_tournament_id
FOREIGN KEY (tournament_id) REFERENCES Tournaments(tournament_id);


ALTER TABLE Scores
ADD COLUMN tournament_id INT;

ALTER TABLE Scores
ADD CONSTRAINT fk_scores_tournament_id
FOREIGN KEY (tournament_id) REFERENCES Tournaments(tournament_id);

-- Step 5: Create Tournament Entry for First Tournament
INSERT INTO Tournaments (
    tournament_name,
    start_date,
    registration_end_date,
    end_date,
    tournament_type,
    point_structure,
    is_active,
    entry_fee,
    seed_money
) VALUES (
    'First Padawan Tournament 2024',           -- tournament_name
    '2024-11-03',                        -- start_date
    '2024-11-03',                        -- registration_end_date
    '2024-11-03',                        -- end_date
    'duos',                              -- tournament_type
    '{"kills": 5, "damage": 2, "revives": 1}', -- point_structure (JSON format)
    1,                                   -- is_active
    2,                                   -- entry_fee
    50                                   -- seed_money
);

-- Step 6: Update all existing data with the new Tournament ID
UPDATE Scores
SET tournament_id = 1;

UPDATE Teams
SET tournament_id = 1;

UPDATE registrations
SET current_tournament_id = 1;

-- Step 6: Transfer existing data to the historical tournaments table
INSERT INTO Historical_Tournaments (
    tournament_name, 
    tournament_date, 
    team_id, 
    master_player_id, 
    padawan_player_id, 
    round_number, 
    master_kills, 
    padawan_kills, 
    master_damage, 
    padawan_damage, 
    revives_points, 
    placement_points, 
    total_score, 
    tournament_id
)
SELECT
    'First Padawan Tournament 2024',         -- Tournament name
    '2024-11-03',                            -- Tournament date
    Scores.team_id,
    Teams.master_player_id,
    Teams.padawan_player_id,
    Scores.round_number,
    Scores.master_kills,
    Scores.padawan_kills,
    Scores.master_damage,
    Scores.padawan_damage,
    Scores.revives_points,
    Scores.placement_points,
    Scores.total_score,
    Scores.tournament_id
FROM Scores
JOIN Teams ON Scores.team_id = Teams.team_id
WHERE Scores.tournament_id = 1;


-- Step 7: Reset Active tournaments
DELETE FROM Scores WHERE tournament_id = 1;
DELETE FROM Teams WHERE tournament_id = 1;
UPDATE registrations SET is_active = 0 WHERE tournament_id = 1; -- Optional for managing active players