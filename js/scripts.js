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
