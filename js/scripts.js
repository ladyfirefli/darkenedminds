let discordData = null; // Global variable to store Discord data
let fortniteData = null; // Global variable to store fortnite data
let gamertagVisited = false;
let platformVisited = false;
let lastGamertag = null; // Last fetched gamertag
let lastPlatform = null; // Last fetched platform


document.addEventListener("DOMContentLoaded", () => {
    // Function to resize the header based on the navbar
    console.log("DOM fully loaded and parsed");
    const navbar = document.getElementById("main-navbar");
    const header = document.querySelector("header");

    if (navbar && header) {

        console.log("Navbar and header found");
        // Calculate navbar height
        const navbarHeight = navbar.offsetHeight;

        // Apply the height as a margin or padding to the header
        header.style.marginTop = `${navbarHeight}px`;

        // Update dynamically if the navbar changes layout (e.g., on resize)
        window.addEventListener("resize", () => {
            const updatedNavbarHeight = navbar.offsetHeight;
            header.style.marginTop = `${updatedNavbarHeight}px`;
        });
    } else {
        console.error("Navbar or header not found");
    }
});

function fetchDiscordInfo() {
    // Function for getting discord info as soon as the field is left on the
    // registration form

    const discordName = document.getElementById('discord_name').value;
    const messageDiv = document.getElementById('discord-info-message');

    // Clear any previous messages
    messageDiv.innerHTML = '';

    if (!discordName) {
        messageDiv.innerHTML = '<p style="color: red;">Discord Name is required.</p>';
        return;
    }

    // Send AJAX request to the server
    fetch('../services/check_discord.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ discord_name: discordName }),
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                discordData = data; // Store the data globally

                // Format the join date
                const joinDate = data.discord_joined_at
                    ? new Date(data.discord_joined_at).toLocaleDateString('en-US', {
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric'
                    })
                    : 'unknown';

                messageDiv.innerHTML = `<p style="color: green;">Discord name verified: ${data.discord_username}. Joined on: ${joinDate}</p>`;
            } else {
                discordData = null; // Clear stored data on failure
                messageDiv.innerHTML = `<p style="color: red;">${data.message}</p>`;
            }
            toggleRegisterButton();
        })
        .catch(error => {
            console.error('Error:', error);
            discordData = null; // Clear stored data on error
            messageDiv.innerHTML = '<p style="color: red;">An error occurred while verifying Discord info. Please try again.</p>';
            toggleRegisterButton();
        });
}

document.querySelector('.registration-form').addEventListener('submit', function (event) {
    // field to re-use the already retrieved discord data instead of refetching it
    if (!discordData) {
        event.preventDefault(); // Prevent form submission if Discord data is missing
        alert('Please verify your Discord name before submitting.');
        return;
    }

    if (!fortniteData) {
        event.preventDefault();
        alert('Please verify your Fortnite gamertag before submitting.');
        return;
    }

    // Add the Discord data to a hidden input field
    const discordDataInput = document.createElement('input');
    discordDataInput.type = 'hidden';
    discordDataInput.name = 'discord_data';
    discordDataInput.value = JSON.stringify(discordData); // Serialize the data as a JSON string

    // Add Fortnite data to a hidden input
    const fortniteDataInput = document.createElement('input');
    fortniteDataInput.type = 'hidden';
    fortniteDataInput.name = 'fortnite_data';
    fortniteDataInput.value = JSON.stringify(fortniteData);

    this.appendChild(discordDataInput); // Add the hidden input to the form
    this.appendChild(fortniteDataInput);
});

function fetchFortniteStats() {
    const gamertag = document.getElementById('gamertag').value;
    const platform = document.querySelector('input[name="platform"]:checked')?.value;
    const messageDiv = document.getElementById('fortnite-info-message');

    messageDiv.innerHTML = ''; // Clear previous messages

    if (!gamertag || !platform) {
        messageDiv.innerHTML = '<p style="color: red;">Gamertag and platform are required.</p>';
        fortniteData = null;
        toggleRegisterButton();
        return;
    }

    fetch('../services/check_fortnite.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ gamertag, platform }),
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                fortniteData = data; // Store Fortnite data
                messageDiv.innerHTML = `<p style="color: green;">Gamertag verified: ${data.gamertag}. Wins: ${data.stats.wins}, Matches: ${data.stats.matches}</p>`;
            } else {
                fortniteData = null; // Clear Fortnite data on failure
                messageDiv.innerHTML = `<p style="color: red;">${data.message}</p>`;
            }
            toggleRegisterButton();
        })
        .catch(error => {
            console.error('Error:', error);
            fortniteData = null; // Clear Fortnite data on error
            messageDiv.innerHTML = '<p style="color: red;">An error occurred while verifying Fortnite stats. Please try again.</p>';
            toggleRegisterButton();
        });
}

function markGamertagVisited() {
    const gamertagField = document.getElementById('gamertag').value.trim();
    gamertagVisited = gamertagField !== ''; // Set flag based on input


    // Only reset if the gamertag has changed
    if (gamertagField !== lastGamertag) {
        fortniteData = null; // Clear previous data
        if (gamertagVisited) {
            attemptFortniteFetch(); // Check if a fetch can proceed
        } else {
            const messageDiv = document.getElementById('fortnite-info-message');
            messageDiv.innerHTML = ''; // Clear previous messages
            toggleRegisterButton();
        }
        lastGamertag = gamertagField; // Update the last fetched gamertag
    }

}

function markPlatformVisited() {
    const platform = document.querySelector('input[name="platform"]:checked')?.value;
    platformVisited = !!platform; // Set flag if a platform is selected

    // Only reset if the platform has changed
    if ((platform !== lastPlatform) && platformVisited) {
        fortniteData = null; // Clear previous data
        attemptFortniteFetch(); // Check if a fetch can proceed
        lastPlatform = platform; // Update the last fetched platform
    }
}

function attemptFortniteFetch() {
    if (!gamertagVisited || !platformVisited) {
        return; // Don't trigger the API call until both fields have been visited
    }

    const gamertag = document.getElementById('gamertag').value;
    const platform = document.querySelector('input[name="platform"]:checked')?.value;
    const messageDiv = document.getElementById('fortnite-info-message');

    messageDiv.innerHTML = ''; // Clear previous messages

    // Don't proceed if gamertag or platform is missing or hasn't changed
    if (!gamertag || !platform || (gamertag === lastGamertag && platform === lastPlatform)) {
        return;
    }

    // Send AJAX request to check Fortnite stats
    fetch('../services/check_fortnite.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ gamertag, platform }),
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                fortniteData = data; // Store the response in fortniteData
                messageDiv.innerHTML = `<p style="color: green;">Gamertag verified: ${data.gamertag}. Wins: ${data.stats.wins}, Matches: ${data.stats.matches}</p>`;
            } else {
                fortniteData = null; // Clear fortniteData on failure
                messageDiv.innerHTML = `<p style="color: red;">${data.message}</p>`;
            }
            toggleRegisterButton(); // Check if the register button can be enabled
        })
        .catch(error => {
            console.error('Error:', error);
            fortniteData = null; // Clear fortniteData on error
            messageDiv.innerHTML = '<p style="color: red;">An error occurred while verifying Fortnite stats. Please try again.</p>';
            toggleRegisterButton(); // Ensure the register button is updated
        });
}


function toggleRegisterButton() {
    const registerButton = document.querySelector('button[type="submit"]');
    if (discordData && fortniteData) {
        registerButton.disabled = false; // Enable the button
    } else {
        registerButton.disabled = true; // Disable the button
    }
}