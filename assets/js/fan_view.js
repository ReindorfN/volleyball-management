console.log('Fan view JS loaded');

document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM fully loaded');
    const navLinks = document.querySelectorAll("nav ul li a");
    const sections = document.querySelectorAll("main .section");

    // Navigation behavior
    navLinks.forEach(link => {
        link.addEventListener("click", (event) => {
            event.preventDefault();

            // Update active navigation
            navLinks.forEach(nav => nav.classList.remove("active"));
            link.classList.add("active");

            // Show corresponding section
            const targetSection = document.querySelector(link.getAttribute("href"));
            sections.forEach(section => section.classList.remove("active"));
            targetSection.classList.add("active");
        });
    });

    // Modal Elements
    const followTeamModal = document.getElementById('follow-team-modal');
    const profileModal = document.getElementById('profile-modal');
    const followTeamBtn = document.getElementById('follow-team-btn');
    const profileBtn = document.getElementById('profile-btn');
    const closeButtons = document.querySelectorAll('.close');

    // Button Elements
    const viewStandingsBtn = document.getElementById('view-standings-btn');
    const searchMatchesBtn = document.getElementById('search-matches-btn');

    // Modal Controls
    function openModal(modal) {
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden'; // Prevent background scrolling
    }

    function closeModal(modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }

    // Event Listeners for Modals
    followTeamBtn.addEventListener('click', () => openModal(followTeamModal));
    profileBtn.addEventListener('click', () => openModal(profileModal));

    closeButtons.forEach(button => {
        button.addEventListener('click', () => {
            closeModal(button.closest('.modal'));
        });
    });

    // Close modals when clicking outside
    window.addEventListener('click', (e) => {
        if (e.target.classList.contains('modal')) {
            closeModal(e.target);
        }
    });

    // Follow/Unfollow Team Functionality
    document.addEventListener('click', async (e) => {
        if (e.target.classList.contains('follow-team-button')) {
            const teamId = e.target.dataset.teamId;
            const isFollowing = e.target.classList.contains('following');

            try {
                const response = await fetch('../actions/toggle_follow_team.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ team_id: teamId })
                });

                const data = await response.json();
                if (data.success) {
                    e.target.classList.toggle('following');
                    e.target.textContent = isFollowing ? 'Follow' : 'Following';
                    updateFollowedTeamsList();
                }
            } catch (error) {
                console.error('Error:', error);
            }
        }
    });

    // Live Match Updates
    function updateMatchScores() {
        fetch('../actions/get_live_scores.php')
            .then(response => response.json())
            .then(data => {
                data.matches.forEach(match => {
                    const matchElement = document.querySelector(`[data-match-id="${match.match_id}"]`);
                    if (matchElement) {
                        matchElement.querySelector('.score').textContent = 
                            `${match.score_team1} - ${match.score_team2}`;
                        
                        // Add animation for score updates
                        matchElement.querySelector('.score').classList.add('score-updated');
                        setTimeout(() => {
                            matchElement.querySelector('.score').classList.remove('score-updated');
                        }, 1000);
                    }
                });
            })
            .catch(error => console.error('Error:', error));
    }

    // Update scores every 30 seconds for live matches
    setInterval(updateMatchScores, 30000);

    // Search Functionality
    const searchInput = document.createElement('input');
    searchInput.type = 'text';
    searchInput.placeholder = 'Search matches...';
    searchInput.classList.add('search-input', 'hidden');

    searchMatchesBtn.parentNode.insertBefore(searchInput, searchMatchesBtn.nextSibling);

    searchMatchesBtn.addEventListener('click', () => {
        searchInput.classList.toggle('hidden');
        if (!searchInput.classList.contains('hidden')) {
            searchInput.focus();
        }
    });

    // Live search functionality
    searchInput.addEventListener('input', debounce(function(e) {
        const searchTerm = e.target.value.toLowerCase();
        const matchItems = document.querySelectorAll('.match-item');

        matchItems.forEach(item => {
            const text = item.textContent.toLowerCase();
            item.style.display = text.includes(searchTerm) ? '' : 'none';
        });
    }, 300));

    // Standings Toggle
    viewStandingsBtn.addEventListener('click', () => {
        const rankingsSection = document.getElementById('team-rankings');
        rankingsSection.scrollIntoView({ behavior: 'smooth' });
    });

    // Interactive Team Cards
    document.querySelectorAll('.team-card').forEach(card => {
        card.addEventListener('click', function() {
            const teamId = this.dataset.teamId;
            showTeamDetails(teamId);
        });
    });

    // Show Team Details
    async function showTeamDetails(teamId) {
        try {
            const response = await fetch(`../actions/get_team_details.php?team_id=${teamId}`);
            const data = await response.json();
            
            if (data.success) {
                // Create and show team details modal
                const detailsHTML = `
                    <div class="team-details">
                        <h3>${data.team.name}</h3>
                        <p>Coach: ${data.team.coach}</p>
                        <h4>Players</h4>
                        <ul class="player-list">
                            ${data.team.players.map(player => `
                                <li>${player.name} - ${player.position}</li>
                            `).join('')}
                        </ul>
                        <h4>Recent Matches</h4>
                        <div class="recent-matches">
                            ${data.team.recent_matches.map(match => `
                                <div class="match-result">
                                    <span>${match.opponent}</span>
                                    <span>${match.score}</span>
                                    <span>${match.date}</span>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                `;

                // Show in modal
                const modal = document.createElement('div');
                modal.className = 'modal';
                modal.innerHTML = `
                    <div class="modal-content">
                        <span class="close">&times;</span>
                        ${detailsHTML}
                    </div>
                `;
                document.body.appendChild(modal);
                openModal(modal);

                // Add close functionality
                modal.querySelector('.close').addEventListener('click', () => {
                    document.body.removeChild(modal);
                });
            }
        } catch (error) {
            console.error('Error:', error);
        }
    }

    // Utility function for debouncing
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

    // Add loading states
    function setLoading(element, isLoading) {
        if (isLoading) {
            element.classList.add('loading');
            element.disabled = true;
        } else {
            element.classList.remove('loading');
            element.disabled = false;
        }
    }

    // Initialize tooltips
    const tooltipElements = document.querySelectorAll('[data-tooltip]');
    tooltipElements.forEach(element => {
        element.addEventListener('mouseenter', (e) => {
            const tooltip = document.createElement('div');
            tooltip.className = 'tooltip';
            tooltip.textContent = e.target.dataset.tooltip;
            document.body.appendChild(tooltip);

            const rect = e.target.getBoundingClientRect();
            tooltip.style.top = `${rect.top - tooltip.offsetHeight - 5}px`;
            tooltip.style.left = `${rect.left + (rect.width - tooltip.offsetWidth) / 2}px`;
        });

        element.addEventListener('mouseleave', () => {
            const tooltip = document.querySelector('.tooltip');
            if (tooltip) {
                tooltip.remove();
            }
        });
    });

    // Team search functionality
    const teamSearch = document.getElementById('team-search');
    const teamsGrid = document.querySelector('.teams-grid');

    teamSearch.addEventListener('input', debounce(function(e) {
        const searchTerm = e.target.value.toLowerCase();
        const teamCards = teamsGrid.querySelectorAll('.team-card');

        teamCards.forEach(card => {
            const teamName = card.querySelector('h3').textContent.toLowerCase();
            const shouldShow = teamName.includes(searchTerm);
            card.style.display = shouldShow ? '' : 'none';
        });
    }, 300));

    // Follow/Unfollow button hover effect
    document.querySelectorAll('.follow-team-button').forEach(button => {
        button.addEventListener('mouseenter', function() {
            if (this.classList.contains('following')) {
                this.textContent = 'Unfollow';
            }
        });

        button.addEventListener('mouseleave', function() {
            if (this.classList.contains('following')) {
                this.textContent = 'Following';
            }
        });
    });

    // Update button state after follow/unfollow action
    function updateFollowButton(button, isFollowing) {
        button.classList.toggle('following', isFollowing);
        button.textContent = isFollowing ? 'Following' : 'Follow';
    }

    // Handle follow/unfollow action
    async function toggleFollowTeam(teamId, button) {
        try {
            const response = await fetch('../actions/toggle_follow_team.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ team_id: teamId })
            });

            const data = await response.json();
            if (data.success) {
                updateFollowButton(button, data.following);
                // Update the main dashboard if needed
                if (typeof updateFollowedTeamsList === 'function') {
                    updateFollowedTeamsList();
                }
            } else {
                throw new Error(data.error || 'Failed to update follow status');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Failed to update follow status. Please try again.');
        }
    }

    // Event delegation for follow buttons
    teamsGrid.addEventListener('click', (e) => {
        if (e.target.classList.contains('follow-team-button')) {
            const teamId = e.target.dataset.teamId;
            toggleFollowTeam(teamId, e.target);
        }
    });
});
