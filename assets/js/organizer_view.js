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

    // Edit match modal elements
    const editMatchModal = document.getElementById("edit_match_modal");
    const editMatchClose = document.getElementById("edit_match_close");
    const editMatchForm = document.getElementById("edit_match_form");
    const cancelEditBtn = document.getElementById("cancel_edit");
    const editTeam1Dropdown = document.getElementById("edit_team1_id");
    const editTeam2Dropdown = document.getElementById("edit_team2_id");
    let originalMatchData = null;

    // Tournament-related elements
    const editTournamentModal = document.getElementById("edit_tournament_modal");
    const editTournamentClose = document.getElementById("edit_tournament_close");
    const editTournamentForm = document.getElementById("edit_tournament_form");
    const cancelEditTournamentBtn = document.getElementById("cancel_edit_tournament");
    let originalTournamentData = null;

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

    // Edit match button click handler
    document.addEventListener('click', function(e) {
        if (e.target && e.target.classList.contains('edit-match-btn')) {
            const matchId = e.target.getAttribute('data-match-id');
            openEditMatchModal(matchId);
        }
    });

    // Delete match button click handler
    document.addEventListener('click', function(e) {
        if (e.target && e.target.classList.contains('delete-match-btn')) {
            const matchId = e.target.getAttribute('data-match-id');
            if (confirm('Are you sure you want to delete this match?')) {
                deleteMatch(matchId);
            }
        }
    });

    function openEditMatchModal(matchId) {
        editMatchModal.style.display = "block";
        
        // Clear and fetch team options
        [editTeam1Dropdown, editTeam2Dropdown].forEach(dropdown => {
            dropdown.innerHTML = '<option value="" disabled selected>Select Team</option>';
        });

        // Fetch teams for dropdowns
        fetch("../actions/fetch_teams.php")
            .then(response => response.json())
            .then(data => {
                data.forEach(team => {
                    const option1 = document.createElement("option");
                    const option2 = document.createElement("option");
                    option1.value = team.team_id;
                    option1.textContent = team.team_name;
                    option2.value = team.team_id;
                    option2.textContent = team.team_name;
                    editTeam1Dropdown.appendChild(option1);
                    editTeam2Dropdown.appendChild(option2.cloneNode(true));
                });

                // Fetch match details
                fetch(`../actions/fetch_match_details.php?match_id=${matchId}`)
                    .then(response => response.json())
                    .then(matchData => {
                        originalMatchData = matchData;
                        
                        // Populate form with match data
                        document.getElementById("edit_match_id").value = matchData.match_id;
                        editTeam1Dropdown.value = matchData.team1_id;
                        editTeam2Dropdown.value = matchData.team2_id;
                        document.getElementById("edit_match_date").value = 
                            matchData.match_date.replace(' ', 'T');
                        document.getElementById("edit_match_venue").value = matchData.venue;
                    })
                    .catch(error => console.error("Error fetching match details:", error));
            })
            .catch(error => console.error("Error fetching teams:", error));
    }

    function deleteMatch(matchId) {
        fetch(`../actions/delete_match.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `match_id=${matchId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Remove the match row from the table
                const matchRow = document.getElementById(`delete_match_${matchId}`).closest('tr');
                matchRow.remove();
                alert('Match deleted successfully!');
            } else {
                alert('Error deleting match: ' + data.error);
            }
        })
        .catch(error => console.error('Error:', error));
    }

    // Open add match modal
    addMatchBtn.addEventListener("click", () => {
        modal1.style.display = "block";

        // Clear and fetch team options
        [team1Dropdown, team2Dropdown].forEach(dropdown => {
            dropdown.innerHTML = '<option value="" disabled selected>Select Team</option>';
        });

        fetch("../actions/fetch_teams.php")
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    console.error(data.error);
                    return;
                }

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

    // Handle edit form submission
    editMatchForm.addEventListener("submit", (e) => {
        e.preventDefault();
        
        const formData = new FormData();
        formData.append("match_id", document.getElementById("edit_match_id").value);
        formData.append("team1_id", editTeam1Dropdown.value);
        formData.append("team2_id", editTeam2Dropdown.value);
        formData.append("match_date", document.getElementById("edit_match_date").value);
        formData.append("venue", document.getElementById("edit_match_venue").value);

        fetch("../actions/update_match.php", {
            method: "POST",
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert("Match updated successfully!");
                editMatchModal.style.display = "none";
                // Refresh the page to show updated data
                location.reload();
            } else {
                alert("Error updating match: " + data.error);
            }
        })
        .catch(error => console.error("Error updating match:", error));
    });

    // Open add tournament modal
    addTournamentBtn.addEventListener("click", () => {
        modal.style.display = "block";
    });

    // Close modal handlers
    closeModalBtn.addEventListener("click", () => {
        modal.style.display = "none";
    });

    match_close.addEventListener("click", () => {
        modal1.style.display = "none";
    });

    editMatchClose.addEventListener("click", () => {
        editMatchModal.style.display = "none";
    });

    cancelEditBtn.addEventListener("click", () => {
        if (originalMatchData) {
            // Restore original values
            document.getElementById("edit_match_id").value = originalMatchData.match_id;
            editTeam1Dropdown.value = originalMatchData.team1_id;
            editTeam2Dropdown.value = originalMatchData.team2_id;
            document.getElementById("edit_match_date").value = 
                originalMatchData.match_date.replace(' ', 'T');
            document.getElementById("edit_match_venue").value = originalMatchData.venue;
        }
        editMatchModal.style.display = "none";
    });

    // Close modals when clicking outside
    window.addEventListener("click", (event) => {
        if (event.target === modal) {
            modal.style.display = "none";
        } else if(event.target === modal1) {
            modal1.style.display = "none";
        } else if(event.target === editMatchModal) {
            editMatchModal.style.display = "none";
        }
    });

    // Edit tournament button click handler
    document.addEventListener('click', function(e) {
        if (e.target && e.target.classList.contains('edit-tournament-btn')) {
            const tournamentId = e.target.getAttribute('data-tournament-id');
            openEditTournamentModal(tournamentId);
        }
    });

    // Delete tournament button click handler
    document.addEventListener('click', function(e) {
        if (e.target && e.target.classList.contains('delete-tournament-btn')) {
            const tournamentId = e.target.getAttribute('data-tournament-id');
            if (confirm('Are you sure you want to delete this tournament?')) {
                deleteTournament(tournamentId);
            }
        }
    });

    function openEditTournamentModal(tournamentId) {
        editTournamentModal.style.display = "block";
        
        fetch(`../actions/fetch_tournament_details.php?tournament_id=${tournamentId}`)
            .then(response => response.json())
            .then(tournamentData => {
                originalTournamentData = tournamentData;
                
                document.getElementById("edit_tournament_id").value = tournamentData.tournament_id;
                document.getElementById("edit_tournament_name").value = tournamentData.tournament_name;
                document.getElementById("edit_start_date").value = tournamentData.start_date;
                document.getElementById("edit_end_date").value = tournamentData.end_date;
            })
            .catch(error => console.error("Error fetching tournament details:", error));
    }

    function deleteTournament(tournamentId) {
        fetch(`../actions/delete_tournament.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `tournament_id=${tournamentId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const tournamentRow = document.getElementById(`delete_tournament_${tournamentId}`).closest('tr');
                tournamentRow.remove();
                alert('Tournament deleted successfully!');
            } else {
                alert('Error deleting tournament: ' + data.error);
            }
        })
        .catch(error => console.error('Error:', error));
    }

    editTournamentForm.addEventListener("submit", (e) => {
        e.preventDefault();
        
        const formData = new FormData();
        formData.append("tournament_id", document.getElementById("edit_tournament_id").value);
        formData.append("tournament_name", document.getElementById("edit_tournament_name").value);
        formData.append("start_date", document.getElementById("edit_start_date").value);
        formData.append("end_date", document.getElementById("edit_end_date").value);

        fetch("../actions/update_tournament.php", {
            method: "POST",
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert("Tournament updated successfully!");
                editTournamentModal.style.display = "none";
                location.reload();
            } else {
                alert("Error updating tournament: " + data.error);
            }
        })
        .catch(error => console.error("Error updating tournament:", error));
    });

    // Tournament modal close handlers
    editTournamentClose.addEventListener("click", () => {
        editTournamentModal.style.display = "none";
    });

    cancelEditTournamentBtn.addEventListener("click", () => {
        if (originalTournamentData) {
            document.getElementById("edit_tournament_id").value = originalTournamentData.tournament_id;
            document.getElementById("edit_tournament_name").value = originalTournamentData.tournament_name;
            document.getElementById("edit_start_date").value = originalTournamentData.start_date;
            document.getElementById("edit_end_date").value = originalTournamentData.end_date;
        }
        editTournamentModal.style.display = "none";
    });

    // Add to your existing window click handler
    window.addEventListener("click", (event) => {
        if (event.target === editTournamentModal) {
            editTournamentModal.style.display = "none";
        }
    });
});
