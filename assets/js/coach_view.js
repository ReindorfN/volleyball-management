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

    // Add this function to your existing coach_view.js
    async function loadStatistics() {
        try {
            const response = await fetch('../actions/get_player_stats.php');
            const data = await response.json();
            
            const statisticsTable = document.getElementById('statistics_table');
            if (data.success) {
                statisticsTable.innerHTML = `
                    <table>
                        <thead>
                            <tr>
                                <th>Player Name</th>
                                <th>Position</th>
                                <th>Jersey #</th>
                                <th>Matches</th>
                                <th>Points/Game</th>
                                <th>Assists/Game</th>
                                <th>Blocks/Game</th>
                                <th>Digs/Game</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${data.players.map(player => `
                                <tr>
                                    <td>${player.player_name}</td>
                                    <td>${player.position}</td>
                                    <td>${player.jersey_number}</td>
                                    <td>${player.matches_played}</td>
                                    <td>${player.avg_points}</td>
                                    <td>${player.avg_assists}</td>
                                    <td>${player.avg_blocks}</td>
                                    <td>${player.avg_digs}</td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                    ${data.players.length === 0 ? '<p class="no-data">No statistics available</p>' : ''}
                `;
            } else {
                statisticsTable.innerHTML = `<p class="error-message">${data.error || 'Failed to load statistics'}</p>`;
            }
        } catch (error) {
            console.error('Error loading statistics:', error);
            document.getElementById('statistics_table').innerHTML = 
                '<p class="error-message">Error loading statistics</p>';
        }
    }

    // Add this function to your existing coach_view.js
    async function loadProfile() {
        try {
            const response = await fetch('../actions/get_coach_profile.php');
            const data = await response.json();
            
            if (data.success) {
                const profile = data.profile;
                
                // Update profile information
                document.getElementById('profile-info').innerHTML = `
                    <div class="profile-card">
                        <div class="profile-header">
                            <h3>Coach Information</h3>
                        </div>
                        <div class="profile-details">
                            <p><strong>Name:</strong> ${profile.first_name} ${profile.last_name}</p>
                            <p><strong>Email:</strong> ${profile.email}</p>
                        </div>
                    </div>
                `;

                // Update team information
                document.getElementById('team-info').innerHTML = `
                    <div class="team-card">
                        <div class="team-header">
                            <h3>Team Information</h3>
                        </div>
                        <div class="team-details">
                            <p><strong>Team Name:</strong> ${profile.team_name || 'No team assigned'}</p>
                            <p><strong>Total Players:</strong> ${profile.player_count}</p>
                            <p><strong>Matches Played:</strong> ${profile.matches_played}</p>
                            <p><strong>Upcoming Matches:</strong> ${profile.upcoming_matches}</p>
                        </div>
                    </div>
                `;
            } else {
                showErrorMessage(data.error || 'Failed to load profile');
            }
        } catch (error) {
            console.error('Error loading profile:', error);
            showErrorMessage('Error loading profile');
        }
    }

    // Add profile link click handler
    document.querySelector('a[href="#profile"]').addEventListener('click', () => {
        loadProfile();
    });

    // Load all data
    loadDashboardStats();
    loadTeamPlayers();
    loadMatches();
    loadStatistics();
    // Only load profile if it's the active section
    if (document.getElementById('profile').classList.contains('active')) {
        loadProfile();
    }

    // Profile Modal
    const profileModal = document.getElementById('profile-modal');
    const profileBtn = document.getElementById('profile-btn');
    const closeBtn = profileModal.querySelector('.close');
    const profileForm = document.getElementById('profile-form');

    // Open profile modal
    profileBtn.addEventListener('click', async () => {
        try {
            const response = await fetch('../actions/get_coach_profile.php');
            const data = await response.json();
            
            if (data.success) {
                // Populate form with current data
                profileForm.first_name.value = data.profile.first_name;
                profileForm.last_name.value = data.profile.last_name;
                profileForm.email.value = data.profile.email;
                
                // Clear password fields
                profileForm.new_password.value = '';
                profileForm.confirm_password.value = '';
                
                profileModal.style.display = 'block';
            } else {
                showErrorMessage(data.error || 'Failed to load profile');
            }
        } catch (error) {
            console.error('Error loading profile:', error);
            showErrorMessage('Error loading profile');
        }
    });

    // Close modal
    closeBtn.addEventListener('click', () => {
        profileModal.style.display = 'none';
    });

    // Close modal when clicking outside
    window.addEventListener('click', (event) => {
        if (event.target === profileModal) {
            profileModal.style.display = 'none';
        }
    });

    // Handle form submission
    profileForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        // Validate passwords if provided
        if (profileForm.new_password.value) {
            if (profileForm.new_password.value !== profileForm.confirm_password.value) {
                showErrorMessage('Passwords do not match');
                return;
            }
            if (profileForm.new_password.value.length < 6) {
                showErrorMessage('Password must be at least 6 characters');
                return;
            }
        }

        try {
            const response = await fetch('../actions/update_coach_profile.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    first_name: profileForm.first_name.value,
                    last_name: profileForm.last_name.value,
                    email: profileForm.email.value,
                    new_password: profileForm.new_password.value
                })
            });

            const data = await response.json();
            
            if (data.success) {
                showSuccessMessage('Profile updated successfully');
                profileModal.style.display = 'none';
                // Reload profile data if shown
                if (document.getElementById('profile').classList.contains('active')) {
                    loadProfile();
                }
            } else {
                showErrorMessage(data.error || 'Failed to update profile');
            }
        } catch (error) {
            console.error('Error updating profile:', error);
            showErrorMessage('Error updating profile');
        }
    });
});
