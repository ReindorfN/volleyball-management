document.addEventListener("DOMContentLoaded", () => {
    // Navigation functionality
    const navLinks = document.querySelectorAll("nav ul li a");
    const sections = document.querySelectorAll("main .section");

    navLinks.forEach(link => {
        link.addEventListener("click", (event) => {
            event.preventDefault();

            // Highlight active link
            navLinks.forEach(nav => nav.classList.remove("active"));
            link.classList.add("active");

            // Show active section
            const targetSection = document.querySelector(link.getAttribute("href"));
            sections.forEach(section => section.classList.remove("active"));
            targetSection.classList.add("active");
        });
    });

    // Fetch data and populate sections (mock data for now)
    const teamTable = document.querySelector("#team_table tbody");
    const players = [
        { name: "John Doe", position: "Spiker", jersey: 10 },
        { name: "Jane Smith", position: "Setter", jersey: 8 },
    ];

    players.forEach(player => {
        const row = document.createElement("tr");
        row.innerHTML = `
            <td>${player.name}</td>
            <td>${player.position}</td>
            <td>${player.jersey}</td>
            <td>
                <button>Edit</button>
                <button>Remove</button>
            </td>
        `;
        teamTable.appendChild(row);
    });
});
