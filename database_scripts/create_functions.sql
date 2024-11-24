USE Darkened_Minds_Tournaments;

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

