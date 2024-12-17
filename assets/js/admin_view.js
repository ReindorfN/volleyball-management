document.addEventListener('DOMContentLoaded', function() {
    // Tab Navigation
    const sidebarLinks = document.querySelectorAll('.sidebar-nav li');
    const contentSections = document.querySelectorAll('.content-section');

    function switchTab(tabId) {
        // Remove active class from all tabs and sections
        sidebarLinks.forEach(link => link.classList.remove('active'));
        contentSections.forEach(section => section.classList.remove('active'));

        // Add active class to selected tab and section
        document.querySelector(`[data-tab="${tabId}"]`).classList.add('active');
        document.getElementById(tabId).classList.add('active');
    }

    sidebarLinks.forEach(link => {
        link.addEventListener('click', () => {
            switchTab(link.getAttribute('data-tab'));
        });
    });

    // Dashboard Statistics Loading
    async function loadDashboardStats() {
        try {
            const response = await fetch('../actions/get_dashboard_stats.php');
            const data = await response.json();

            if (data.success) {
                document.getElementById('total-teams').textContent = data.stats.teams;
                document.getElementById('active-players').textContent = data.stats.players;
                document.getElementById('upcoming-matches').textContent = data.stats.matches;
                document.getElementById('total-users').textContent = data.stats.users;
            }
        } catch (error) {
            console.error('Error loading dashboard stats:', error);
        }
    }

    // Recent Activity Loading
    async function loadRecentActivity() {
        try {
            const response = await fetch('../actions/get_recent_activity.php');
            const data = await response.json();

            if (data.success) {
                const activityList = document.getElementById('activity-list');
                activityList.innerHTML = data.activities.map(activity => `
                    <div class="activity-item">
                        <span class="activity-time">${formatTime(activity.timestamp)}</span>
                        <span class="activity-text">${activity.description}</span>
                    </div>
                `).join('');
            }
        } catch (error) {
            console.error('Error loading recent activity:', error);
        }
    }

    // Teams Management
    async function loadTeams() {
        try {
            const response = await fetch('../actions/get_teams.php');
            const data = await response.json();
            
            const teamsList = document.getElementById('teams-list');
            if (data.success) {
                teamsList.innerHTML = `
                    <table>
                        <thead>
                            <tr>
                                <th>Team Name</th>
                                <th>Coach</th>
                                <th>Players</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${data.teams.map(team => `
                                <tr>
                                    <td>${team.team_name}</td>
                                    <td>${team.coach_name || 'No Coach Assigned'}</td>
                                    <td>${team.player_count}</td>
                                    <td>
                                        <button onclick="deleteTeam(${team.team_id})" class="btn-delete">
                                            Delete
                                        </button>
                                    </td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                `;
            }
        } catch (error) {
            console.error('Error loading teams:', error);
        }
    }

    // Search Functionality
    const searchInput = document.querySelector('.header-search input');
    searchInput.addEventListener('input', debounce(function(e) {
        const searchTerm = e.target.value.toLowerCase();
        const activeSection = document.querySelector('.content-section.active');
        const items = activeSection.querySelectorAll('table tbody tr');

        items.forEach(item => {
            const text = item.textContent.toLowerCase();
            item.style.display = text.includes(searchTerm) ? '' : 'none';
        });
    }, 300));

    // Utility Functions
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    function formatTime(timestamp) {
        const date = new Date(timestamp);
        return date.toLocaleString();
    }

    // Initial Load
    loadDashboardStats();
    loadRecentActivity();
    loadTeams();

    // Add Event Listeners for Add Buttons
    document.getElementById('add-team-btn').addEventListener('click', async () => {
        const modal = document.getElementById('add-team-modal');
        const coachSelect = document.getElementById('coachId');
        
        try {
            const response = await fetch('../actions/fetch_available_coaches.php');
            const data = await response.json();
            
            if (data.success) {
                coachSelect.innerHTML = '<option value="">Select a coach...</option>' +
                    data.coaches.map(coach => 
                        `<option value="${coach.id}">${coach.name}</option>`
                    ).join('');
                
                modal.style.display = 'block';
            }
        } catch (error) {
            console.error('Error fetching coaches:', error);
        }
    });

    document.getElementById('add-team-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        
        try {
            const form = e.target;
            form.classList.add('loading');

            const response = await fetch('../actions/add_team.php', {
                method: 'POST',
                body: JSON.stringify({
                    teamName: formData.get('teamName'),
                    coachId: formData.get('coachId')
                }),
                headers: {
                    'Content-Type': 'application/json'
                }
            });
            
            const data = await response.json();
            if (data.success) {
                showSuccessMessage('Team added successfully');
                closeModal('add-team-modal');
                loadTeams();
                loadRecentActivity();
            } else {
                showErrorMessage(data.error || 'Failed to add team');
            }
        } catch (error) {
            showErrorMessage('Error adding team: ' + error.message);
        } finally {
            e.target.classList.remove('loading');
        }
    });

    document.getElementById('add-player-btn').addEventListener('click', () => {
        // Show add player modal
    });

    document.getElementById('schedule-match-btn').addEventListener('click', () => {
        // Show schedule match modal
    });

    document.getElementById('add-user-btn').addEventListener('click', async () => {
        const modal = document.getElementById('add-user-modal');
        modal.style.display = 'block';
    });

    document.getElementById('add-user-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        const userData = {
            firstName: formData.get('firstName'),
            lastName: formData.get('lastName'),
            email: formData.get('email'),
            password: formData.get('password'),
            role: formData.get('role')
        };

        try {
            const form = e.target;
            form.classList.add('loading');
            
            const response = await fetch('../actions/add_user.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(userData)
            });

            const data = await response.json();
            if (data.success) {
                showSuccessMessage('User added successfully');
                closeModal('add-user-modal');
                loadUsers();
                loadRecentActivity();
            } else {
                showErrorMessage(data.error || 'Failed to add user');
            }
        } catch (error) {
            showErrorMessage('Failed to add user');
        } finally {
            e.target.classList.remove('loading');
        }
    });

    document.getElementById('add-announcement-btn').addEventListener('click', async () => {
        const modal = document.getElementById('announcement-modal');
        modal.style.display = 'block';
    });

    document.getElementById('announcement-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        
        try {
            const form = e.target;
            form.classList.add('loading');

            const response = await fetch('../actions/create_notification.php', {
                method: 'POST',
                body: JSON.stringify({
                    title: formData.get('title'),
                    message: formData.get('message'),
                    type: 'GENERAL'
                }),
                headers: {
                    'Content-Type': 'application/json'
                }
            });
            
            const data = await response.json();
            if (data.success) {
                showSuccessMessage('Announcement created successfully');
                closeModal('announcement-modal');
                loadRecentActivity();
            } else {
                showErrorMessage(data.error || 'Failed to create announcement');
            }
        } catch (error) {
            showErrorMessage('Error creating announcement: ' + error.message);
        } finally {
            e.target.classList.remove('loading');
        }
    });

    // Load users function
    async function loadUsers() {
        try {
            const response = await fetch('../actions/fetch_users.php');
            const data = await response.json();
            
            const usersList = document.getElementById('users-list');
            usersList.innerHTML = `
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Team</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${data.map(user => `
                            <tr>
                                <td>${user.first_name} ${user.last_name}</td>
                                <td>${user.email}</td>
                                <td>${user.role}</td>
                                <td>${user.team_name || 'N/A'}</td>
                                <td>
                                    <button 
                                        onclick="deleteUser(${user.user_id})" 
                                        class="btn-delete"
                                        ${user.role === 'admin' ? 'disabled' : ''}
                                    >
                                        Delete
                                    </button>
                                </td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            `;
        } catch (error) {
            console.error('Error loading users:', error);
        }
    }

    // Add delete user function
    async function deleteUser(userId) {
        if (!confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
            return;
        }

        try {
            const response = await fetch('../actions/delete_user.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ userId })
            });

            const data = await response.json();
            
            if (data.success) {
                showSuccessMessage('User deleted successfully');
                loadUsers();
                loadRecentActivity();
            } else {
                showErrorMessage(data.error || 'Failed to delete user');
            }
        } catch (error) {
            showErrorMessage('Error deleting user');
            console.error('Error:', error);
        }
    }

    // Initial load
    loadUsers();

    // Add Utility Functions
    function showSuccessMessage(message) {
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-success';
        alertDiv.textContent = message;
        document.querySelector('.content-container').prepend(alertDiv);
        setTimeout(() => alertDiv.remove(), 3000);
    }

    function showErrorMessage(message) {
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-error';
        alertDiv.textContent = message;
        document.querySelector('.content-container').prepend(alertDiv);
        setTimeout(() => alertDiv.remove(), 3000);
    }

    function closeModal(modalId) {
        const modal = document.getElementById(modalId);
        modal.style.display = 'none';
        const form = modal.querySelector('form');
        if (form) {
            form.reset();
            form.classList.remove('loading');
        }
    }

    // Add these functions after the existing loadTeams() function

    async function loadPlayers() {
        try {
            const response = await fetch('../actions/get_players.php');
            const data = await response.json();
            
            const playersContainer = document.getElementById('players-list');
            if (data.success) {
                playersContainer.innerHTML = `
                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Team</th>
                                <th>Position</th>
                                <th>Jersey Number</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${data.players.map(player => `
                                <tr>
                                    <td>${player.first_name} ${player.last_name}</td>
                                    <td>${player.team_name || 'Unassigned'}</td>
                                    <td>${player.position}</td>
                                    <td>${player.jersey_number}</td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                `;
            }
        } catch (error) {
            console.error('Error loading players:', error);
        }
    }

    async function loadMatches() {
        try {
            const response = await fetch('../actions/get_matches.php');
            const data = await response.json();
            
            const matchesContainer = document.getElementById('matches-list');
            if (data.success) {
                matchesContainer.innerHTML = `
                    <div class="matches-grid">
                        ${data.matches.map(match => `
                            <div class="match-card">
                                <div class="match-header">
                                    <span class="match-date">${new Date(match.match_date).toLocaleDateString()}</span>
                                    <span class="match-status ${match.match_status}">${match.match_status}</span>
                                </div>
                                <div class="match-teams">
                                    <div class="team team1">
                                        <span class="team-name">${match.team1_name}</span>
                                        <span class="team-score">${match.score_team1 || '0'}</span>
                                    </div>
                                    <div class="vs">VS</div>
                                    <div class="team team2">
                                        <span class="team-name">${match.team2_name}</span>
                                        <span class="team-score">${match.score_team2 || '0'}</span>
                                    </div>
                                </div>
                                <div class="match-footer">
                                    <span class="venue"><i class="fas fa-map-marker-alt"></i> ${match.venue}</span>
                                    <button onclick="viewMatchDetails(${match.match_id})" class="btn-details">
                                        View Details
                                    </button>
                                </div>
                            </div>
                        `).join('')}
                    </div>
                `;
            }
        } catch (error) {
            console.error('Error loading matches:', error);
        }
    }

    async function loadAnnouncements() {
        try {
            const response = await fetch('../actions/get_announcements.php');
            const data = await response.json();
            
            const announcementsContainer = document.getElementById('announcements-list');
            if (data.success) {
                announcementsContainer.innerHTML = `
                    <div class="announcements-grid">
                        ${data.announcements.map(announcement => `
                            <div class="announcement-card">
                                <div class="announcement-header">
                                    <h3>${announcement.title}</h3>
                                    <span class="timestamp">${formatTime(announcement.created_at)}</span>
                                </div>
                                <p>${announcement.message}</p>
                                <div class="announcement-footer">
                                    <span>By: ${announcement.sender_name}</span>
                                    <span>Recipients: ${announcement.recipient_count}</span>
                                </div>
                            </div>
                        `).join('')}
                    </div>
                `;
            }
        } catch (error) {
            console.error('Error loading announcements:', error);
        }
    }

    // Load all data
    loadUsers();
    loadTeams();
    loadPlayers();
    loadMatches();
    loadAnnouncements();
    loadRecentActivity();

    // Add delete team function
    async function deleteTeam(teamId) {
        if (!confirm('Are you sure you want to delete this team? This action cannot be undone.')) {
            return;
        }

        try {
            const response = await fetch('../actions/delete_team.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ teamId })
            });

            const data = await response.json();
            
            if (data.success) {
                showSuccessMessage('Team deleted successfully');
                loadTeams();
                loadRecentActivity();
            } else {
                showErrorMessage(data.error || 'Failed to delete team');
            }
        } catch (error) {
            showErrorMessage('Error deleting team');
            console.error('Error:', error);
        }
    }
});
