<?php
// Include database connection and helper functions
include_once 'database.php';     

function fetchRoundScores($round_number) {
    $conn = getDatabaseConnection();

    $sql = "
        SELECT 
            t.team_id,
            master.gamertag AS master,
            padawan.gamertag AS padawan,
            s.placement_points,
            s.master_kills_points,
            s.master_damage_points,
            s.padawan_kills_points,
            s.padawan_damage_points,
            s.revives_points,
            s.total_score
        FROM 
            Scores s
        JOIN 
            Teams t ON s.team_id = t.team_id
        JOIN 
            registrations master ON t.master_player_id = master.id
        JOIN 
            registrations padawan ON t.padawan_player_id = padawan.id
        WHERE 
            s.round_number = ?
        ORDER BY 
            s.total_score DESC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $round_number);
    $stmt->execute();
    $result = $stmt->get_result();

    $scores = [];
    while ($row = $result->fetch_assoc()) {
        $scores[] = $row;
    }

    $stmt->close();
    return $scores;
}

?>