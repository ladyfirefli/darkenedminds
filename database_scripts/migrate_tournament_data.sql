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
    -- Add additional fields as needed
    FOREIGN KEY (team_id) REFERENCES Teams(team_id),
    FOREIGN KEY (master_player_id) REFERENCES registrations(id),
    FOREIGN KEY (padawan_player_id) REFERENCES registrations(id)
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
INSERT INTO Historical_Tournaments (tournament_name, tournament_date, team_id, master_player_id, padawan_player_id, round_number, master_kills, padawan_kills, master_damage, padawan_damage, revives_points, placement_points, total_score)
SELECT
    'First Padawan Tournament 2024', -- Replace with actual name
    '2024-11-03', -- Or the actual end date
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
    total_score
FROM Scores
WHERE tournament_id = 1;

-- Step 7: Reset Active tournaments
DELETE FROM Scores WHERE tournament_id = 1;
DELETE FROM Teams WHERE tournament_id = 1;
UPDATE registrations SET is_active = 0 WHERE tournament_id = 1; -- Optional for managing active players