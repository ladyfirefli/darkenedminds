document.getElementById("tournament_id").addEventListener("change", function() {
    let tournamentId = this.value;
    
    fetch(`../services/fetch_registrations.php?tournament_id=${tournamentId}`)
        .then(response => response.json())
        .then(data => {
            let tableBody = document.querySelector("#registrationsTable tbody");
            let playerCountElement = document.getElementById("playerCount");
            
            tableBody.innerHTML = ""; // Clear existing table rows
            
            if (data.length > 0) {
                data.forEach(reg => {
                    let row = `<tr>
                        <td>${reg.gamertag}</td>
                        <td>${reg.fee_paid ? "Paid" : "Not Paid"}</td>
                    </tr>`;
                    tableBody.innerHTML += row;
                });

                // Update the player count dynamically
                playerCountElement.textContent = data.length;
            } else {
                tableBody.innerHTML = `<tr><td colspan="2">No registrations found.</td></tr>`;
                playerCountElement.textContent = 0; // Reset count if no players
            }
        })
        .catch(error => console.error("Error fetching registrations:", error));
});
