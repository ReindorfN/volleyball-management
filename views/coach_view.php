<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'coach') {
    header('Location: login.html');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coach Dashboard</title>
    <link rel="stylesheet" href="../assets/css/coach_view.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2>Coach Dashboard</h2>
            </div>
            <nav>
                <ul>
                    <li><a href="#teams" class="active"><i class="fas fa-users"></i> My Teams</a></li>
                    <li><a href="#matches"><i class="fas fa-volleyball-ball"></i> Matches</a></li>
                    <li><a href="#announcements"><i class="fas fa-bullhorn"></i> Announcements</a></li>
                    <li><a href="#statistics"><i class="fas fa-chart-bar"></i> Statistics</a></li>
                    <li><a href="#" id="logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </aside>

        <main class="main-content">
            <!-- Teams Section -->
            <section id="teams" class="content-section active">
                <h2>My Teams</h2>
                <div class="teams-container">
                    <!-- Teams will be loaded here -->
                </div>
                <div class="player-management">
                    <h3>Add New Player</h3>
                    <form id="add-player-form">
                        <select name="team" id="team-select" required>
                            <!-- Teams will be loaded here -->
                        </select>
                        <input type="text" name="first_name" placeholder="First Name" required>
                        <input type="text" name="last_name" placeholder="Last Name" required>
                        <input type="email" name="email" placeholder="Email" required>
                        <input type="password" name="password" placeholder="Set Default Password" required>
                        <select name="position" required>
                            <option value="spiker">Spiker</option>
                            <option value="blocker">Blocker</option>
                            <option value="setter">Setter</option>
                            <option value="libero">Libero</option>
                            <option value="server">Server</option>
                        </select>
                        <input type="number" name="jersey_number" placeholder="Jersey Number" required>
                        <button type="submit">Add Player</button>
                    </form>
                </div>
            </section>

            <!-- Matches Section -->
            <section id="matches" class="content-section">
                <h2>Upcoming Matches</h2>
                <div class="matches-container">
                    <!-- Matches will be loaded here -->
                </div>
                <div class="strategy-form">
                    <h3>Set Match Strategy</h3>
                    <form id="strategy-form">
                        <select name="match" id="match-select" required>
                            <!-- Matches will be loaded here -->
                        </select>
                        <textarea name="strategy" placeholder="Enter match strategy..." required></textarea>
                        <button type="submit">Set Strategy</button>
                    </form>
                </div>
            </section>

            <!-- Announcements Section -->
            <section id="announcements" class="content-section">
                <h2>Announcements</h2>
                
                <!-- Create Announcement Form -->
                <div class="announcement-form-container">
                    <h3>Create New Announcement</h3>
                    <form id="announcement-form">
                        <select name="announcement_type" required>
                            <option value="team">Team Announcement</option>
                            <option value="general">General Announcement</option>
                        </select>
                        <select name="team" id="announcement-team-select">
                            <!-- Teams will be loaded here -->
                        </select>
                        <input type="text" name="title" placeholder="Announcement Title" required>
                        <textarea name="message" placeholder="Announcement Message" required></textarea>
                        <button type="submit">Send Announcement</button>
                    </form>
                </div>

                <!-- View Announcements -->
                <div class="announcements-container">
                    <div class="announcements-tabs">
                        <button class="tab-btn active" data-tab="all">All</button>
                        <button class="tab-btn" data-tab="team">Team</button>
                        <button class="tab-btn" data-tab="general">General</button>
                    </div>
                    
                    <div class="announcements-list">
                        <!-- Announcements will be loaded here -->
                    </div>
                </div>
            </section>

            <!-- Statistics Section -->
            <section id="statistics" class="content-section">
                <h2>Player Statistics</h2>
                <div class="stats-container">
                    <!-- Statistics table will be loaded here -->
                </div>
                
                <!-- Add Statistics Update Form -->
                <div class="stats-update-form">
                    <h3>Update Player Statistics</h3>
                    <form id="update-stats-form">
                        <select name="player_id" id="player-select" required>
                            <option value="">Select Player</option>
                            <!-- Players will be loaded here -->
                        </select>
                        <div class="stats-inputs">
                            <input type="number" name="spikes" placeholder="Spikes" min="0" required>
                            <input type="number" name="blocks" placeholder="Blocks" min="0" required>
                            <input type="number" name="serves" placeholder="Serves" min="0" required>
                            <input type="number" name="errors" placeholder="Errors" min="0" required>
                        </div>
                        <button type="submit">Update Statistics</button>
                    </form>
                </div>
            </section>
        </main>
    </div>

    <script src="../assets/js/coach_view.js"></script>
</body>
</html>