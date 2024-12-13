document.addEventListener("DOMContentLoaded", () => {
    const navLinks = document.querySelectorAll("nav ul li a");
    const sections = document.querySelectorAll("main .section");

    const addTournamentBtn = document.getElementById("add_tournament_btn");
    const modal = document.getElementById("add_tournament_modal");
    const closeModalBtn = document.querySelector(".close");

    const addMatchBtn = document.getElementById("add_match_btn");
    const modal1 = document.getElementById("add_match_modal");
    const team1Dropdown = document.getElementById("team1_id");
    const team2Dropdown = document.getElementById("team2_id");
    const match_close = document.getElementById("match_close");


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


    // Open add match modal
    addMatchBtn.addEventListener("click", () => {
        modal1.style.display = "block";

        // Clear and fetch team options
        [team1Dropdown, team2Dropdown].forEach(dropdown => {
            dropdown.innerHTML = '<option value="" disabled selected>Select Team</option>';
        });

        fetch("../actions/fetch_teams.php") // Endpoint to fetch all teams
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    console.error(data.error);
                    return;
                }

                // Populate both dropdowns with team data
                data.forEach(team => {
                    const option1 = document.createElement("option");
                    const option2 = document.createElement("option");
                    option1.value = team.team_id;
                    option1.textContent = team.team_name;
                    option2.value = team.team_id;
                    option2.textContent = team.team_name;
                    team1Dropdown.appendChild(option1);
                    team2Dropdown.appendChild(option2);
                });
            })
            .catch(error => console.error("Error fetching teams:", error));
    });


    // Open add tournament modal
    addTournamentBtn.addEventListener("click", () => {
        modal.style.display = "block";
    });

    // Close the modal when clicking the close button
    closeModalBtn.addEventListener("click", () => {
        modal.style.display = "none";
    });

    match_close.addEventListener("click", () => {
        modal1.style.display = "none";
    });

    // Close the modal when clicking outside the modal content
    window.addEventListener("click", (event) => {
        if (event.target === modal) {
            modal.style.display = "none";
        } else if(event.target === modal1){
            modal1.style.display = "none";
        }
    });

    // Add functionality for buttons (e.g., Add Match or Add Tournament) as needed
});
