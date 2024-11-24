USE darkened_Minds_Tournaments;

DROP PROCEDURE IF EXISTS GetOrInsertPlayer;

DELIMITER $$

CREATE PROCEDURE GetOrInsertPlayer (
    IN p_gamertag VARCHAR(255),
    IN p_email VARCHAR(255),
    IN p_discord_name VARCHAR(255),
    IN p_discord_id VARCHAR(255),
    IN p_discord_username VARCHAR(255),
    IN p_discord_discriminator VARCHAR(255),
    IN p_discord_roles VARCHAR(255),
    IN p_discord_joined_at DATETIME,
    OUT p_player_id INT
)
BEGIN
    -- Check if the player already exists
    SELECT player_id INTO p_player_id
    FROM Players
    WHERE gamertag = p_gamertag;

    -- If the player does not exist, insert them
    IF p_player_id IS NULL THEN
        INSERT INTO Players (
            gamertag, email, discord_name, discord_id, discord_username, discord_discriminator, discord_roles, discord_joined_at
        ) VALUES (
            p_gamertag, p_email, p_discord_name, p_discord_id, p_discord_username, p_discord_discriminator, p_discord_roles, p_discord_joined_at
        );
        
        SET p_player_id = LAST_INSERT_ID(); -- Retrieve the newly inserted ID
    END IF;
END$$

DELIMITER ;



DROP PROCEDURE IF EXISTS GetAllActiveTournaments;

DELIMITER $$

CREATE PROCEDURE GetAllActiveTournaments()
BEGIN
    SELECT 
        t.tournament_id, 
        t.name AS tournament_name,
        g.name AS game_name,
        t.registration_end_date,
        t.tournament_date
    FROM Tournaments t
    INNER JOIN Games g ON t.game_id = g.game_id
    WHERE CURDATE() BETWEEN t.registration_start_date AND t.registration_end_date
    ORDER BY t.registration_end_date ASC;
END$$

DELIMITER ;

DROP PROCEDURE IF EXISTS CreateRegistrationIfNotExists;

DELIMITER $$

CREATE PROCEDURE CreateRegistrationIfNotExists (
    IN p_player_id INT,
    IN p_tournament_id INT,
    IN p_partner_gamertag VARCHAR(255),
    IN p_additional_data VARCHAR(255),
    OUT p_registration_id INT
)
BEGIN
    -- Initialize the output parameter
    SET p_registration_id = NULL;

    -- Check if the player is already registered for the tournament
    SELECT registration_id INTO p_registration_id
    FROM Registrations
    WHERE player_id = p_player_id AND tournament_id = p_tournament_id;

    -- If the player is not registered, insert the registration
    IF p_registration_id IS NULL THEN
        INSERT INTO Registrations (
            player_id, tournament_id, partner_gamertag, fee_paid, additional_data
        ) VALUES (
            p_player_id, p_tournament_id, p_partner_gamertag, 0, p_additional_data
        );

        -- Get the newly created registration_id
        SET p_registration_id = LAST_INSERT_ID();
    END IF;

    -- Return the registration_id
END$$

DELIMITER ;

DROP PROCEDURE IF EXISTS CreateGameStatsIfNotExists;

DELIMITER $$

CREATE PROCEDURE CreateGameStatsIfNotExists (
    IN p_registration_id INT,
    IN p_tournament_id INT,
    IN p_gamertag VARCHAR(255),
    IN p_kills INT,
    IN p_damage INT,
    IN p_matches_played INT,
    IN p_win_rate DECIMAL(5,2),
    IN p_kdr DECIMAL(5,2),
    IN p_additional_data JSON,
    OUT p_stats_id INT
)
BEGIN
    -- Declare a variable to store the game_id
    DECLARE v_game_id INT;
    
    -- Initialize the output parameter
    SET p_stats_id = NULL;

    -- Retrieve the game_id from the Tournaments table
    SELECT game_id INTO v_game_id
    FROM Tournaments
    WHERE tournament_id = p_tournament_id;

    -- Check if stats already exist for the given registration ID and tournament
    SELECT stats_id INTO p_stats_id
    FROM Game_Stats
    WHERE registration_id = p_registration_id AND tournament_id = p_tournament_id;

    -- If stats do not exist, insert them
    IF p_stats_id IS NULL THEN
        INSERT INTO Game_Stats (
            registration_id, tournament_id, gamertag, game_id, kills, damage, matches_played, win_rate, kdr, additional_data
        ) VALUES (
            p_registration_id, p_tournament_id, p_gamertag, v_game_id, p_kills, p_damage, p_matches_played, p_win_rate, p_kdr, p_additional_data
        );

        -- Retrieve the newly created stats_id
        SET p_stats_id = LAST_INSERT_ID();
    END IF;
END$$

DELIMITER ;
