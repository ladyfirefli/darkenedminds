<?php
// Include database connection and helper functions
require_once '../../private_html/config.php';

// Fetch dropdown data
function getDropdownData($conn, $tableName) {
    $data = [];
    $query = "SELECT * FROM $tableName";
    if ($result = $conn->query($query)) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        $result->free();
    } else {
        error_log("Error fetching data from $tableName: " . $conn->error);
    }
    return $data;
}

// Fetch active tournaments
function getActiveTournaments($conn) {
    $tournaments = [];
    $query = "CALL GetAllActiveTournaments()";
    if ($result = $conn->query($query)) {
        while ($row = $result->fetch_assoc()) {
            $tournaments[] = $row;
        }
        $result->free();
    } else {
        error_log("Error fetching active tournaments: " . $conn->error);
    }
    $conn->next_result(); // Clear additional results
    return $tournaments;
}

// Create a new tournament
function createTournament($conn, $postData) {
    $tournamentName = $postData['tournament_name'];
    $gameId = $postData['game_id'];
    $tournamentType = $postData['tournament_type'];
    $tournamentDate = $postData['tournament_date'];
    $registrationStartDate = $postData['registration_start_date'];
    $registrationEndDate = $postData['registration_end_date'];
    $format = $postData['format'];
    $entryFee = $postData['entry_fee'];
    $prizes = $postData['prizes'];

    try {
        // Insert into Tournaments table
        $stmt = $conn->prepare("
            INSERT INTO Tournaments (
                name, game_id, tournament_type, tournament_date, registration_start_date, registration_end_date, format
            ) VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("siissss", $tournamentName, $gameId, $tournamentType, $tournamentDate, $registrationStartDate, $registrationEndDate, $format);

        if ($stmt->execute()) {
            $tournamentId = $stmt->insert_id;

            // Insert into Tournament_Finances table
            $stmt = $conn->prepare("
                INSERT INTO Tournament_Finances (tournament_id, entry_fee) VALUES (?, ?)
            ");
            $stmt->bind_param("id", $tournamentId, $entryFee);
            $stmt->execute();

            // Insert into Prizes table
            $positions = ['1st', '2nd', '3rd'];
            foreach ($positions as $position) {
                $stmt = $conn->prepare("
                    INSERT INTO Prizes (tournament_id, position, description) VALUES (?, ?, ?)
                ");
                $stmt->bind_param("iss", $tournamentId, $position, $prizes);
                $stmt->execute();
            }

            return "Tournament created successfully!";
        } else {
            throw new Exception("Error inserting tournament: " . $stmt->error);
        }
    } catch (Exception $e) {
        return $e->getMessage();
    } finally {
        if (isset($stmt)) {
            $stmt->close();
        }
    }
}

// // Usage examples
// $games = getDropdownData($conn, 'Games');
// $tournamentTypes = getDropdownData($conn, 'Tournament_Types');
// $activeTournaments = getActiveTournaments($conn);

// // Example call to createTournament
// if ($_SERVER['REQUEST_METHOD'] === 'POST') {
//     $message = createTournament($conn, $_POST);
//     echo $message;
// }

?>
