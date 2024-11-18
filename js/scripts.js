let discordData = null; // Global variable to store Discord data
let fortniteData = null; // Global variable to store fortnite data

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
        })
        .catch(error => {
            console.error('Error:', error);
            discordData = null; // Clear stored data on error
            messageDiv.innerHTML = '<p style="color: red;">An error occurred while verifying Discord info. Please try again.</p>';
        });
}

document.querySelector('.registration-form').addEventListener('submit', function (event) {
    // field to re-use the already retrieved discord data instead of refetching it
    if (!discordData) {
        event.preventDefault(); // Prevent form submission if Discord data is missing
        alert('Please verify your Discord name before submitting.');
        return;
    }

    // Add the Discord data to a hidden input field
    const discordDataInput = document.createElement('input');
    discordDataInput.type = 'hidden';
    discordDataInput.name = 'discord_data';
    discordDataInput.value = JSON.stringify(discordData); // Serialize the data as a JSON string

    this.appendChild(discordDataInput); // Add the hidden input to the form
});
