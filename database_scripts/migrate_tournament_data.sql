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
    is_active TINYINT(1) DEFAULT 1
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

-- Step 5: Transfer existing data to the historical tournaments table
INSERT INTO Historical_Tournaments (tournament_name, tournament_date, team_id, master_player_id, padawan_player_id, round_number, master_kills, padawan_kills, master_damage, padawan_damage, revives_points, placement_points, total_score)
SELECT
    'Tournament Name', -- Replace with actual name
    CURDATE(), -- Or the actual end date
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
WHERE tournament_id = [Current Tournament ID];

-- Step 6: Reset Active tournaments
DELETE FROM Scores WHERE tournament_id = [Current Tournament ID];
DELETE FROM Teams WHERE tournament_id = [Current Tournament ID];
UPDATE registrations SET is_active = 0 WHERE tournament_id = [Current Tournament ID]; -- Optional for managing active players