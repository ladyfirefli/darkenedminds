<?php
include_once 'database.php';

function fetchScores() {
    $conn = getDatabaseConnection();
    
    $sql_scores = "
    SELECT 
        t.team_id,
        master.gamertag AS master,
        padawan.gamertag AS padawan,
        COALESCE(master_scores.master_total_score, 0) AS master_total_score,
        COALESCE(padawan_scores.padawan_total_score, 0) AS padawan_total_score,
        COALESCE(total_scores.team_total_score, 0) AS team_total_score,
        COALESCE(total_revives.revives_total, 0) AS revives_total,
        COALESCE(total_placement.placement_total, 0) AS placement_total
    FROM 
        Teams t
    JOIN 
        registrations master ON t.master_player_id = master.id
    JOIN 
        registrations padawan ON t.padawan_player_id = padawan.id
    LEFT JOIN (
        SELECT 
            s.team_id,
            SUM(s.master_kills_points + s.master_damage_points) AS master_total_score
        FROM 
            Scores s
        GROUP BY 
            s.team_id
    ) AS master_scores ON t.team_id = master_scores.team_id
    LEFT JOIN (
        SELECT 
            s.team_id,
            SUM(s.padawan_kills_points + s.padawan_damage_points) AS padawan_total_score
        FROM 
            Scores s
        GROUP BY 
            s.team_id
    ) AS padawan_scores ON t.team_id = padawan_scores.team_id
    LEFT JOIN (
        SELECT 
            s.team_id,
            SUM(s.total_score) AS team_total_score
        FROM 
            Scores s
        GROUP BY 
            s.team_id
    ) AS total_scores ON t.team_id = total_scores.team_id
    LEFT JOIN (
        SELECT 
            s.team_id,
            SUM(s.revives_points) AS revives_total
        FROM 
            Scores s
        GROUP BY 
            s.team_id
    ) AS total_revives ON t.team_id = total_revives.team_id
    LEFT JOIN (
        SELECT 
            s.team_id,
            SUM(s.placement_points) AS placement_total
        FROM 
            Scores s
        GROUP BY 
            s.team_id
    ) AS total_placement ON t.team_id = total_placement.team_id
    GROUP BY 
        t.team_id
    ORDER BY 
        team_total_score DESC;";

    $result = $conn->query($sql_scores);
    $conn->close();
    
    return $result;
}
?>
