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


    
    // Get modal and buttons
    const addPlayerBtn = document.getElementById("add_player_btn");
    const modal = document.getElementById("add_player_modal");
    const closeModalBtn = document.querySelector(".close");
    const teamDropdown = document.getElementById("team_id");

    // Open the modal and fetch teams
    addPlayerBtn.addEventListener("click", () => {
        modal.style.display = "block";

        // Clear existing options in the dropdown
        teamDropdown.innerHTML = '<option value="" disabled selected>Select Team</option>';

        // Fetch teams from the server
        fetch("../actions/fetch_coachTeams.php")
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    console.error(data.error);
                    return;
                }

                // Populate the dropdown with teams
                data.forEach(team => {
                    const option = document.createElement("option");
                    option.value = team.team_id;
                    option.textContent = team.team_name;
                    teamDropdown.appendChild(option);
                });
            })
            .catch(error => console.error("Error fetching teams:", error));
    });
    // Close the modal when clicking the close button
    closeModalBtn.addEventListener("click", () => {
        modal.style.display = "none";
    });

    // Close the modal when clicking outside the modal content
    window.addEventListener("click", (event) => {
        if (event.target === modal) {
            modal.style.display = "none";
        }
    });

});
