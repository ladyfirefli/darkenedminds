document.addEventListener("DOMContentLoaded", () => {
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
    console.log('onBlur event triggered'); // Add this line for debugging

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
                messageDiv.innerHTML = `<p style="color: red;">${data.message}</p>`;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            messageDiv.innerHTML = '<p style="color: red;">An error occurred while verifying Discord info. Please try again.</p>';
        });
}
