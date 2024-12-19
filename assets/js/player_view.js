document.addEventListener('DOMContentLoaded', function() {
    // Navigation and UI elements
    const navLinks = document.querySelectorAll('.main-nav a');
    const sections = document.querySelectorAll('.content-section');
    const profileModal = document.getElementById('profile-modal');
    const profileBtn = document.getElementById('profile-btn');
    const closeBtn = document.querySelector('.close');
    const profileForm = document.getElementById('profile-form');

    // Navigation handling
    navLinks.forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            const targetId = link.getAttribute('href').substring(1);
            
            navLinks.forEach(l => l.classList.remove('active'));
            link.classList.add('active');
            
            sections.forEach(section => {
                section.classList.remove('active');
                if (section.id === targetId) {
                    section.classList.add('active');
                }
            });

            // Load section-specific data
            switch(targetId) {
                case 'statistics':
                    loadStatistics();
                    break;
                case 'matches':
                    loadUpcomingMatches();
                    break;
                case 'notifications':
                    loadNotifications();
                    break;
            }
        });
    });

    // Profile Modal Handling
    profileBtn.addEventListener('click', () => {
        profileModal.style.display = 'block';
    });

    closeBtn.addEventListener('click', () => {
        profileModal.style.display = 'none';
    });

    window.addEventListener('click', (e) => {
        if (e.target === profileModal) {
            profileModal.style.display = 'none';
        }
    });

    // Profile Form Submission
    profileForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        // Validate passwords if provided
        const newPassword = profileForm.new_password.value;
        const confirmPassword = profileForm.confirm_password.value;
        
        if (newPassword) {
            if (newPassword !== confirmPassword) {
                showNotification('Passwords do not match', 'error');
                return;
            }
            if (newPassword.length < 6) {
                showNotification('Password must be at least 6 characters', 'error');
                return;
            }
        }

        try {
            const response = await fetch('../actions/update_player_profile.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    email: profileForm.email.value,
                    new_password: newPassword
                })
            });

            const data = await response.json();
            
            if (data.success) {
                showNotification('Profile updated successfully', 'success');
                profileModal.style.display = 'none';
            } else {
                showNotification(data.error || 'Failed to update profile', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            showNotification('An error occurred', 'error');
        }
    });

    // Load Notifications
    async function loadNotifications() {
        try {
            const [notificationsResponse, announcementsResponse] = await Promise.all([
                fetch('../actions/get_player_notifications.php'),
                fetch('../actions/fetch_announcements.php')
            ]);

            const notificationsData = await notificationsResponse.json();
            const announcementsData = await announcementsResponse.json();
            
            if (notificationsData.success) {
                updateNotificationsDisplay(notificationsData.notifications);
                updateNotificationCount(notificationsData.unreadCount);
            }

            updateAnnouncementsDisplay(announcementsData);
        } catch (error) {
            console.error('Error:', error);
            showNotification('Error loading notifications', 'error');
        }
    }

    // Update Notifications Display
    function updateNotificationsDisplay(notifications) {
        const notificationsList = document.getElementById('notifications-list');
        
        if (!notifications.length) {
            notificationsList.innerHTML = '<p class="no-data">No notifications available</p>';
            return;
        }

        notificationsList.innerHTML = notifications.map(notification => `
            <div class="notification-item ${!notification.read_status ? 'unread' : ''}" 
                 data-notification-id="${notification.notification_id}">
                <div class="notification-header">
                    <span class="notification-title">${escapeHtml(notification.title)}</span>
                    <span class="notification-date">${formatDate(notification.created_at)}</span>
                </div>
                <div class="notification-message">
                    ${escapeHtml(notification.message)}
                </div>
            </div>
        `).join('');

        // Add click handlers for unread notifications
        document.querySelectorAll('.notification-item.unread').forEach(item => {
            item.addEventListener('click', () => markNotificationAsRead(item));
        });
    }

    // Update Announcements Display
    function updateAnnouncementsDisplay(announcements) {
        const announcementsList = document.getElementById('announcements-list');
        
        if (!announcements.length) {
            announcementsList.innerHTML = '<p class="no-data">No announcements available</p>';
            return;
        }

        announcementsList.innerHTML = announcements.map(announcement => `
            <div class="notification-item">
                <div class="notification-header">
                    <span class="notification-title">Team Announcement</span>
                    <span class="notification-date">${formatDate(announcement.created_at)}</span>
                </div>
                <div class="notification-message">
                    ${escapeHtml(announcement.announcement_text)}
                </div>
            </div>
        `).join('');
    }

    // Mark Notification as Read
    async function markNotificationAsRead(notificationElement) {
        const notificationId = notificationElement.dataset.notificationId;
        
        try {
            const response = await fetch('../actions/mark_notification_read.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ notification_id: notificationId })
            });
            
            const data = await response.json();
            if (data.success) {
                notificationElement.classList.remove('unread');
                updateNotificationCount(document.querySelectorAll('.notification-item.unread').length);
            }
        } catch (error) {
            console.error('Error:', error);
            showNotification('Error updating notification status', 'error');
        }
    }

    // Load Statistics
    async function loadStatistics() {
        try {
            const response = await fetch('../actions/get_player_stats.php');
            const data = await response.json();
            
            if (data.success) {
                updateStatisticsDisplay(data.statistics);
                if (data.statistics.matches_played > 0) {
                    createPerformanceChart(data.statistics);
                }
            } else {
                showNotification('Failed to load statistics', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            showNotification('Error loading statistics', 'error');
        }
    }

    // Load Upcoming Matches
    async function loadUpcomingMatches() {
        try {
            const response = await fetch('../actions/get_matches.php');
            const data = await response.json();
            
            if (data.success) {
                updateMatchesDisplay(data.matches);
            } else {
                showNotification('Failed to load matches', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            showNotification('Error loading matches', 'error');
        }
    }

    // Load Team Information
    async function loadTeamInfo() {
        try {
            const response = await fetch('../actions/get_team_info.php');
            const data = await response.json();
            
            if (data.success) {
                updateTeamDisplay(data.team);
            } else {
                showNotification('Failed to load team information', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            showNotification('Error loading team information', 'error');
        }
    }

    // Create Performance Chart
    function createPerformanceChart(statistics) {
        const ctx = document.getElementById('performanceChart').getContext('2d');
        
        if (window.performanceChart) {
            window.performanceChart.destroy();
        }

        window.performanceChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: statistics.dates,
                datasets: [{
                    label: 'Spikes',
                    data: statistics.spikes,
                    borderColor: '#2196f3',
                    fill: false
                }, {
                    label: 'Blocks',
                    data: statistics.blocks,
                    borderColor: '#4caf50',
                    fill: false
                }, {
                    label: 'Serves',
                    data: statistics.serves,
                    borderColor: '#ff9800',
                    fill: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

    // Update Statistics Display
    function updateStatisticsDisplay(statistics) {
        const statsContainer = document.querySelector('.stats-summary');
        if (!statsContainer) return;

        // Update statistics table
        const rows = statsContainer.querySelectorAll('tr');
        rows.forEach(row => {
            const metric = row.cells[0].textContent.toLowerCase();
            if (statistics[`total_${metric}`]) {
                row.cells[1].textContent = statistics[`total_${metric}`];
                row.cells[2].textContent = statistics[`avg_${metric}`];
            }
        });
    }

    // Update Matches Display
    function updateMatchesDisplay(matches) {
        const matchesList = document.querySelector('.matches-list');
        if (!matchesList) return;

        if (!matches || matches.length === 0) {
            matchesList.innerHTML = '<p class="no-data">No upcoming matches scheduled</p>';
            return;
        }

        matchesList.innerHTML = matches.map(match => `
            <div class="match-card">
                <div class="match-header">
                    <span class="match-date">${formatDate(match.match_date)}</span>
                    <span class="match-venue">${escapeHtml(match.venue)}</span>
                </div>
                <div class="match-teams">
                    ${escapeHtml(match.team1_name)} vs ${escapeHtml(match.team2_name)}
                </div>
                ${match.strategy_text ? `
                    <div class="match-strategy">
                        <h4>Coach's Strategy:</h4>
                        <p>${escapeHtml(match.strategy_text)}</p>
                    </div>
                ` : ''}
            </div>
        `).join('');
    }

    // Update Team Display
    function updateTeamDisplay(team) {
        const teamInfo = document.querySelector('.team-info');
        if (!teamInfo) return;

        if (!team) {
            teamInfo.innerHTML = '<p class="no-data">You are not currently assigned to a team.</p>';
            return;
        }

        teamInfo.innerHTML = `
            <h3>${escapeHtml(team.team_name)}</h3>
            <p>Coach: ${escapeHtml(team.coach_name)}</p>
            
            <div class="teammates-list">
                <h4>Teammates</h4>
                <table>
                    <thead>
                        <tr>
                            <th>Jersey #</th>
                            <th>Name</th>
                            <th>Position</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${team.teammates.map(teammate => `
                            <tr>
                                <td>${teammate.jersey_number}</td>
                                <td>${escapeHtml(teammate.name)}</td>
                                <td>${escapeHtml(teammate.position)}</td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        `;
    }

    // Utility Functions
    function showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.remove();
        }, 3000);
    }

    function formatDate(dateString) {
        const options = { year: 'numeric', month: 'long', day: 'numeric' };
        return new Date(dateString).toLocaleDateString(undefined, options);
    }

    function escapeHtml(unsafe) {
        return unsafe
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    // Add this function definition before loadNotifications
    function updateNotificationCount(count) {
        const notificationLink = document.querySelector('nav a[href="#notifications"]');
        const existingBadge = notificationLink.querySelector('.notification-badge');
        
        if (existingBadge) {
            if (count > 0) {
                existingBadge.textContent = count;
            } else {
                existingBadge.remove();
            }
        } else if (count > 0) {
            const badge = document.createElement('span');
            badge.className = 'notification-badge';
            badge.textContent = count;
            notificationLink.appendChild(badge);
        }
    }

    // Initial load
    loadStatistics();
    loadNotifications();
    
    // Periodic notification check (every 5 minutes)
    setInterval(loadNotifications, 300000);
});
