// Add these variables at the top of your file
const REFRESH_INTERVAL = 30000; // Real-time data loading after every 30 seconds
let refreshIntervals = {};

document.addEventListener('DOMContentLoaded', function() {
    // Navigation
    const navLinks = document.querySelectorAll('nav a');
    const sections = document.querySelectorAll('.content-section');
    
    // Initialize page
    loadTeams();
    loadMatches();
    loadStatistics();

    // Navigation handling
    navLinks.forEach(link => {
        link.addEventListener('click', (e) => {
            if (link.id === 'logout') return;
            
            e.preventDefault();
            const targetId = link.getAttribute('href').substring(1);
            
            // Update active states
            navLinks.forEach(l => l.classList.remove('active'));
            link.classList.add('active');
            
            sections.forEach(section => {
                section.classList.remove('active');
                if (section.id === targetId) {
                    section.classList.add('active');
                }
            });
        });
    });

    // Form Submissions
    document.getElementById('add-player-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        
        try {
            const response = await fetch('../actions/add_player.php', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();
            
            if (data.success) {
                alert('Player added successfully');
                loadTeams(); // Reload teams to show new player
                e.target.reset();
            } else {
                alert(data.error || 'Failed to add player');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Failed to add player');
        }
    });

    document.getElementById('strategy-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        
        try {
            const response = await fetch('../actions/set_strategy.php', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();
            
            if (data.success) {
                alert('Strategy set successfully');
                e.target.reset();
            } else {
                alert(data.error || 'Failed to set strategy');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Failed to set strategy');
        }
    });

    document.getElementById('announcement-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        
        try {
            const response = await fetch('../actions/send_announcement.php', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();
            
            if (data.success) {
                alert('Announcement sent successfully');
                e.target.reset();
            } else {
                alert(data.error || 'Failed to send announcement');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Failed to send announcement');
        }
    });

    // Data loading functions
    async function loadTeams(showLoadingIndicator = true) {
        if (showLoadingIndicator) {
            document.querySelector('.teams-container')?.classList.add('loading');
        }
        
        try {
            const response = await fetch('../actions/get_coach_teams.php');
            const data = await response.json();
            
            if (data.success) {
                // Populate teams container
                const teamsContainer = document.querySelector('.teams-container');
                teamsContainer.innerHTML = data.teams.map(team => `
                    <div class="team-card">
                        <h3>${team.team_name}</h3>
                        <ul class="player-list">
                            ${team.players.map(player => `
                                <li>
                                    ${player.name} - ${player.position} (#${player.jersey_number})
                                    <div class="player-actions">
                                        <button class="btn-remove" 
                                                onclick="removePlayer(${player.player_id}, '${player.name}', ${team.team_id})">
                                            <i class="fas fa-user-minus"></i> Remove
                                        </button>
                                    </div>
                                </li>
                            `).join('')}
                        </ul>
                    </div>
                `).join('');

                // Populate team select dropdowns (for add player and announcements)
                const teamSelects = document.querySelectorAll('#team-select, #announcement-team-select');
                teamSelects.forEach(select => {
                    select.innerHTML = `
                        <option value="">Select Team</option>
                        ${data.teams.map(team => `
                            <option value="${team.team_id}">${team.team_name}</option>
                        `).join('')}
                    `;
                });

                // Populate player select dropdown for statistics
                const playerSelect = document.getElementById('player-select');
                if (playerSelect) {
                    playerSelect.innerHTML = `
                        <option value="">Select Player</option>
                        ${data.teams.map(team => 
                            team.players.map(player => `
                                <option value="${player.player_id}">${player.name} (${team.team_name})</option>
                            `).join('')
                        ).join('')}
                    `;
                }

                document.querySelector('.teams-container')?.classList.remove('loading');
            }
        } catch (error) {
            console.error('Error:', error);
            document.querySelector('.teams-container')?.classList.remove('loading');
        }
    }

    async function loadMatches() {
        try {
            const response = await fetch('../actions/get_coach_matches.php');
            const data = await response.json();
            
            if (data.success) {
                const matchesContainer = document.querySelector('.matches-container');
                matchesContainer.innerHTML = data.matches.map(match => `
                    <div class="match-card">
                        <h3>${match.team1} vs ${match.team2}</h3>
                        <p>Date: ${new Date(match.match_date).toLocaleDateString()}</p>
                        <p>Venue: ${match.venue}</p>
                        <p>Status: ${match.match_status}</p>
                    </div>
                `).join('');

                // Update match select dropdown
                const matchSelect = document.getElementById('match-select');
                matchSelect.innerHTML = `
                    <option value="">Select Match</option>
                    ${data.matches.map(match => `
                        <option value="${match.match_id}">
                            ${match.team1} vs ${match.team2} - ${new Date(match.match_date).toLocaleDateString()}
                        </option>
                    `).join('')}
                `;
            }
        } catch (error) {
            console.error('Error:', error);
        }
    }

    async function loadStatistics(showLoadingIndicator = true) {
        if (showLoadingIndicator) {
            document.querySelector('.stats-container')?.classList.add('loading');
        }
        
        try {
            const response = await fetch('../actions/get_team_statistics.php');
            const data = await response.json();
            
            if (data.success) {
                const statsContainer = document.querySelector('.stats-container');
                statsContainer.innerHTML = `
                    <table class="stats-table">
                        <thead>
                            <tr>
                                <th>Player</th>
                                <th>Team</th>
                                <th>Spikes</th>
                                <th>Blocks</th>
                                <th>Serves</th>
                                <th>Errors</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${data.statistics.map(stat => `
                                <tr>
                                    <td>${stat.player_name}</td>
                                    <td>${stat.team_name}</td>
                                    <td>${stat.spikes}</td>
                                    <td>${stat.blocks}</td>
                                    <td>${stat.serves}</td>
                                    <td>${stat.errors}</td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                `;

                document.querySelector('.stats-container')?.classList.remove('loading');
            }
        } catch (error) {
            console.error('Error:', error);
            document.querySelector('.stats-container')?.classList.remove('loading');
        }
    }

    // Logout handling
    document.getElementById('logout').addEventListener('click', async (e) => {
        e.preventDefault();
        try {
            const response = await fetch('../actions/logout.php');
            // Check if the response is JSON
            const contentType = response.headers.get('content-type');
            if (contentType && contentType.includes('application/json')) {
                const data = await response.json();
                if (data.success) {
                    window.location.href = '../index.html';
                }
            } else {
                // If not JSON, just redirect
                window.location.href = '../index.html';
            }
        } catch (error) {
            console.error('Error:', error);
            // On error, still try to redirect
            window.location.href = '../index.html';
        }
    });

    // Add remove player function
    async function removePlayer(playerId, playerName, teamId) {
        if (!confirm(`Are you sure you want to remove ${playerName} from the team?`)) {
            return;
        }

        try {
            const response = await fetch('../actions/remove_player.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    player_id: playerId,
                    team_id: teamId
                })
            });

            const data = await response.json();
            if (data.success) {
                alert('Player removed successfully');
                loadTeams(); // Reload teams
            } else {
                alert(data.error || 'Failed to remove player');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Failed to remove player');
        }
    }

    // Add statistics update handler
    document.getElementById('update-stats-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        
        try {
            const response = await fetch('../actions/update_statistics.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            if (data.success) {
                alert('Statistics updated successfully');
                loadStatistics(); // Reload statistics
                e.target.reset();
            } else {
                alert(data.error || 'Failed to update statistics');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Failed to update statistics');
        }
    });

    // Add tab functionality
    const tabButtons = document.querySelectorAll('.tab-btn');
    tabButtons.forEach(button => {
        button.addEventListener('click', () => {
            tabButtons.forEach(btn => btn.classList.remove('active'));
            button.classList.add('active');
            loadAnnouncements(button.dataset.tab);
        });
    });

    // Load initial announcements
    loadAnnouncements();

    // Add to existing JavaScript
    async function loadAnnouncements(type = 'all', showLoadingIndicator = true) {
        if (showLoadingIndicator) {
            document.querySelector('.announcements-list')?.classList.add('loading');
        }
        
        try {
            const response = await fetch('../actions/get_announcements.php?type=' + type);
            const data = await response.json();
            
            if (data.success) {
                const announcementsList = document.querySelector('.announcements-list');
                announcementsList.innerHTML = data.announcements.map(announcement => `
                    <div class="announcement-card">
                        <div class="announcement-header">
                            <div>
                                <span class="announcement-title">${announcement.title}</span>
                                <span class="announcement-type type-${announcement.notification_type.toLowerCase()}">
                                    ${announcement.notification_type === 'TEAM_SPECIFIC' ? 'Team' : 'General'}
                                </span>
                            </div>
                            <span class="announcement-meta">
                                ${new Date(announcement.created_at).toLocaleDateString()}
                            </span>
                        </div>
                        <div class="announcement-message">${announcement.message}</div>
                        ${announcement.team_name ? 
                            `<div class="announcement-meta">Team: ${announcement.team_name}</div>` : 
                            ''}
                    </div>
                `).join('');

                document.querySelector('.announcements-list')?.classList.remove('loading');
            }
        } catch (error) {
            console.error('Error:', error);
            document.querySelector('.announcements-list')?.classList.remove('loading');
        }
    }

    // Add this to handle showing/hiding team select based on announcement type
    const announcementTypeSelect = document.querySelector('select[name="announcement_type"]');
    const teamSelect = document.getElementById('announcement-team-select');

    if (announcementTypeSelect && teamSelect) {
        // Initial state
        teamSelect.style.display = announcementTypeSelect.value === 'team' ? 'block' : 'none';

        // Handle changes
        announcementTypeSelect.addEventListener('change', function() {
            teamSelect.style.display = this.value === 'team' ? 'block' : 'none';
        });
    }

    // Function to start real-time updates
    function startRealTimeUpdates() {
        // Clear any existing intervals
        stopRealTimeUpdates();
        
        // Set up new intervals
        refreshIntervals.teams = setInterval(() => loadTeams(false), REFRESH_INTERVAL);
        refreshIntervals.announcements = setInterval(() => loadAnnouncements(document.querySelector('.tab-btn.active').dataset.tab, false), REFRESH_INTERVAL);
        refreshIntervals.statistics = setInterval(() => loadStatistics(false), REFRESH_INTERVAL);
    }

    // Function to stop real-time updates
    function stopRealTimeUpdates() {
        Object.values(refreshIntervals).forEach(interval => clearInterval(interval));
        refreshIntervals = {};
    }

    // Initial loads
    loadTeams();
    loadAnnouncements();
    loadStatistics();
    
    // Start real-time updates
    startRealTimeUpdates();
    
    // Handle tab visibility changes
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            stopRealTimeUpdates();
        } else {
            startRealTimeUpdates();
        }
    });
});

// Add loading indicator styles
const loadingStyles = document.createElement('style');
loadingStyles.textContent = `
    .loading {
        position: relative;
        opacity: 0.7;
        pointer-events: none;
    }
    
    .loading::after {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 24px;
        height: 24px;
        margin: -12px 0 0 -12px;
        border: 2px solid #3498db;
        border-top-color: transparent;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
        to {
            transform: rotate(360deg);
        }
    }
`;
document.head.appendChild(loadingStyles);

// Add error handling and retry logic
async function fetchWithRetry(url, options = {}, retries = 3) {
    for (let i = 0; i < retries; i++) {
        try {
            const response = await fetch(url, options);
            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
            return await response.json();
        } catch (error) {
            if (i === retries - 1) throw error;
            await new Promise(resolve => setTimeout(resolve, 1000 * Math.pow(2, i)));
        }
    }
}

// Add connection status indicator
const connectionIndicator = document.createElement('div');
connectionIndicator.className = 'connection-status';
document.body.appendChild(connectionIndicator);

// Add connection status styles
loadingStyles.textContent += `
    .connection-status {
        position: fixed;
        bottom: 20px;
        right: 20px;
        padding: 8px 16px;
        border-radius: 4px;
        font-size: 14px;
        z-index: 1000;
        transition: all 0.3s ease;
        opacity: 0;
    }
    
    .connection-status.online {
        background-color: #2ecc71;
        color: white;
    }
    
    .connection-status.offline {
        background-color: #e74c3c;
        color: white;
        opacity: 1;
    }
`;

// Update connection status
function updateConnectionStatus() {
    if (navigator.onLine) {
        connectionIndicator.textContent = 'Connected';
        connectionIndicator.className = 'connection-status online';
        setTimeout(() => {
            connectionIndicator.style.opacity = '0';
        }, 2000);
    } else {
        connectionIndicator.textContent = 'Connection Lost';
        connectionIndicator.className = 'connection-status offline';
    }
}

window.addEventListener('online', updateConnectionStatus);
window.addEventListener('offline', updateConnectionStatus);
