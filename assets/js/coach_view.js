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

    // Player management elements
    const editPlayerModal = document.getElementById("edit_player_modal");
    const editPlayerClose = document.getElementById("edit_player_close");
    const editPlayerForm = document.getElementById("edit_player_form");
    const cancelEditPlayerBtn = document.getElementById("cancel_edit_player");

    // Edit player button click handler
    document.addEventListener('click', function(e) {
        if (e.target && e.target.classList.contains('edit-player-btn')) {
            const playerId = e.target.getAttribute('data-player-id');
            openEditPlayerModal(playerId);
        }
    });

    // Delete player button click handler
    document.addEventListener('click', function(e) {
        if (e.target && e.target.classList.contains('delete-player-btn')) {
            const playerId = e.target.getAttribute('data-player-id');
            if (confirm('Are you sure you want to delete this player?')) {
                deletePlayer(playerId);
            }
        }
    });

    function openEditPlayerModal(playerId) {
        editPlayerModal.style.display = "block";
        
        fetch(`../actions/fetch_player_details.php?player_id=${playerId}`)
            .then(response => response.json())
            .then(playerData => {
                document.getElementById("edit_player_id").value = playerData.player_id;
                document.getElementById("edit_position").value = playerData.position;
                document.getElementById("edit_jersey_number").value = playerData.jersey_number;
                document.getElementById("edit_team_id").value = playerData.team_id;
            })
            .catch(error => console.error("Error fetching player details:", error));
    }

    function deletePlayer(playerId) {
        fetch(`../actions/delete_player.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `player_id=${playerId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const playerRow = document.getElementById(`delete_player_${playerId}`).closest('tr');
                playerRow.remove();
                alert('Player deleted successfully!');
            } else {
                alert('Error deleting player: ' + data.error);
            }
        })
        .catch(error => console.error('Error:', error));
    }

    editPlayerForm.addEventListener("submit", (e) => {
        e.preventDefault();
        
        const formData = new FormData();
        formData.append("player_id", document.getElementById("edit_player_id").value);
        formData.append("position", document.getElementById("edit_position").value);
        formData.append("jersey_number", document.getElementById("edit_jersey_number").value);
        formData.append("team_id", document.getElementById("edit_team_id").value);

        fetch("../actions/update_player.php", {
            method: "POST",
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert("Player updated successfully!");
                editPlayerModal.style.display = "none";
                location.reload();
            } else {
                alert("Error updating player: " + data.error);
            }
        })
        .catch(error => console.error("Error updating player:", error));
    });

    // Modal close handlers
    editPlayerClose.addEventListener("click", () => {
        editPlayerModal.style.display = "none";
    });

    cancelEditPlayerBtn.addEventListener("click", () => {
        editPlayerModal.style.display = "none";
    });

    // Add to your existing window click handler
    window.addEventListener("click", (event) => {
        if (event.target === editPlayerModal) {
            editPlayerModal.style.display = "none";
        }
    });

    // Strategy modal elements
    const strategyModal = document.getElementById("strategy_modal");
    const strategyClose = document.getElementById("strategy_close");
    const strategyForm = document.getElementById("strategy_form");
    const cancelStrategyBtn = document.getElementById("cancel_strategy");

    // Strategy button click handler
    document.addEventListener('click', function(e) {
        if (e.target && e.target.classList.contains('set-strategy-btn')) {
            const matchId = e.target.getAttribute('data-match-id');
            openStrategyModal(matchId);
        }
    });

    function openStrategyModal(matchId) {
        strategyModal.style.display = "block";
        document.getElementById("strategy_match_id").value = matchId;
        
        // Get coach_id from PHP session
        const coachId = document.body.getAttribute('data-coach-id');
        
        // Fetch existing strategy if any
        fetch(`../actions/fetch_strategy.php?match_id=${matchId}&coach_id=${coachId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.strategy) {
                    document.getElementById("strategy_text").value = data.strategy;
                } else {
                    document.getElementById("strategy_text").value = '';
                }
            })
            .catch(error => console.error("Error fetching strategy:", error));
    }

    strategyForm.addEventListener("submit", (e) => {
        e.preventDefault();
        
        const formData = new FormData();
        formData.append("match_id", document.getElementById("strategy_match_id").value);
        formData.append("strategy", document.getElementById("strategy_text").value);
        formData.append("coach_id", document.body.getAttribute('data-coach-id'));

        fetch("../actions/update_strategy.php", {
            method: "POST",
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert("Strategy updated successfully!");
                strategyModal.style.display = "none";
            } else {
                alert("Error updating strategy: " + data.error);
            }
        })
        .catch(error => console.error("Error:", error));
    });

    // Strategy modal close handlers
    strategyClose.addEventListener("click", () => {
        strategyModal.style.display = "none";
    });

    cancelStrategyBtn.addEventListener("click", () => {
        strategyModal.style.display = "none";
    });

    // Add to your existing window click handler
    window.addEventListener("click", (event) => {
        if (event.target === strategyModal) {
            strategyModal.style.display = "none";
        }
    });

});
