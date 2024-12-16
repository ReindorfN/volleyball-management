document.addEventListener("DOMContentLoaded", () => {
    // Select navigation buttons and sections
    const navLinks = document.querySelectorAll("nav ul li a");
    const sections = document.querySelectorAll("main .section");

    // Highlight active section when a navigation link is clicked
    navLinks.forEach(link => {
        link.addEventListener("click", (event) => {
            event.preventDefault();

            // Update active link styling
            navLinks.forEach(nav => nav.classList.remove("active"));
            link.classList.add("active");

            // Show the corresponding section
            const targetSection = document.querySelector(link.getAttribute("href"));
            sections.forEach(section => section.classList.remove("active"));
            targetSection.classList.add("active");
        });
    });

    // Fetch announcements dynamically (optional)
    const announcementsSection = document.getElementById("announcements");
    if (announcementsSection) {
        fetchAnnouncements(announcementsSection);
    }

    /**
     * Fetch and display announcements dynamically.
     */
    function fetchAnnouncements(section) {
        // Make an AJAX call to fetch announcements
        fetch("../actions/fetch_announcements.php")
            .then(response => response.json())
            .then(data => {
                const announcementList = section.querySelector("ul");
                announcementList.innerHTML = ""; // Clear existing announcements

                if (data.error) {
                    console.error("Error fetching announcements:", data.error);
                    return;
                }

                // Populate the announcements
                data.forEach(announcement => {
                    const listItem = document.createElement("li");
                    listItem.innerHTML = `
                        <p>${announcement.announcement_text}</p>
                        <small>Posted on ${new Date(announcement.created_at).toLocaleString()}</small>
                    `;
                    announcementList.appendChild(listItem);
                });
            })
            .catch(error => console.error("Error fetching announcements:", error));
    }
});
