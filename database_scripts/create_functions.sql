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
