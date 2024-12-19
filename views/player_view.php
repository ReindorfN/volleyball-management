<?php
session_start();
require_once '../db/db_connection.php';

// Check if user is logged in and is a player
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'player') {
    header('Location: login.php');
    exit;
}

$database = new Database();
$conn = $database->getConnection();

try {
    $userId = $_SESSION['user_id'];
    
    // Get player's information including team details
    $query = "SELECT 
                u.first_name,
                u.last_name,
                u.email,
                p.player_id,
                p.position,
                p.jersey_number,
                t.team_id,
                t.team_name,
                c.first_name as coach_first_name,
                c.last_name as coach_last_name
              FROM v_ball_users u
              JOIN v_ball_players p ON u.user_id = p.user_id
              LEFT JOIN v_ball_teams t ON p.team_id = t.team_id
              LEFT JOIN v_ball_users c ON t.coach_id = c.user_id
              WHERE u.user_id = ?";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $playerInfo = $result->fetch_assoc();

    // Get player's statistics
    if ($playerInfo) {
        $statsQuery = "SELECT 
                        COUNT(DISTINCT s.match_id) as matches_played,
                        SUM(s.spikes) as total_spikes,
                        SUM(s.blocks) as total_blocks,
                        SUM(s.serves) as total_serves,
                        SUM(s.errors) as total_errors,
                        ROUND(AVG(s.spikes), 1) as avg_spikes,
                        ROUND(AVG(s.blocks), 1) as avg_blocks,
                        ROUND(AVG(s.serves), 1) as avg_serves
                      FROM v_ball_statistics s
                      WHERE s.player_id = ?";
        
        $stmt = $conn->prepare($statsQuery);
        $stmt->bind_param("i", $playerInfo['player_id']);
        $stmt->execute();
        $playerStats = $stmt->get_result()->fetch_assoc();
    }

    // Get upcoming matches
    if ($playerInfo['team_id']) {
        $matchesQuery = "SELECT 
                          m.*,
                          t1.team_name as team1_name,
                          t2.team_name as team2_name,
                          ms.strategy_text
                        FROM v_ball_matches m
                        JOIN v_ball_teams t1 ON m.team1_id = t1.team_id
                        JOIN v_ball_teams t2 ON m.team2_id = t2.team_id
                        LEFT JOIN v_ball_match_strategies ms ON m.match_id = ms.match_id
                        WHERE (m.team1_id = ? OR m.team2_id = ?)
                        AND m.match_status = 'scheduled'
                        ORDER BY m.match_date ASC";
        
        $stmt = $conn->prepare($matchesQuery);
        $stmt->bind_param("ii", $playerInfo['team_id'], $playerInfo['team_id']);
        $stmt->execute();
        $upcomingMatches = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // Get teammates
    if ($playerInfo['team_id']) {
        $teammatesQuery = "SELECT 
                            u.first_name,
                            u.last_name,
                            p.position,
                            p.jersey_number
                          FROM v_ball_players p
                          JOIN v_ball_users u ON p.user_id = u.user_id
                          WHERE p.team_id = ? AND p.player_id != ?
                          ORDER BY p.jersey_number";
        
        $stmt = $conn->prepare($teammatesQuery);
        $stmt->bind_param("ii", $playerInfo['team_id'], $playerInfo['player_id']);
        $stmt->execute();
        $teammates = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

} catch (Exception $e) {
    // Log error and show generic message
    error_log("Player view error: " . $e->getMessage());
    $error = "An error occurred while loading the page.";
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Player Dashboard - V-Ball</title>
    <link rel="stylesheet" href="../assets/css/player_view.css">
    <link rel="icon" href="../assets/images/v-ball_favicon.ico">
</head>
<body>
    <!-- Sidebar Navigation -->
    <div class="sidebar">
        <div class="logo">
            <img src="../assets/images/v-ball_logo.png" alt="V-Ball Logo">
            <h2>Player Panel</h2>
        </div>
        <nav>
            <ul class="main-nav">
                <li><a href="#dashboard" class="active"><i class="fas fa-home"></i> Dashboard</a></li>
                <li><a href="#matches"><i class="fas fa-volleyball-ball"></i> Matches</a></li>
                <li><a href="#statistics"><i class="fas fa-chart-bar"></i> Statistics</a></li>
                <li><a href="#team"><i class="fas fa-users"></i> Team</a></li>
                <li><a href="#notifications"><i class="fas fa-bell"></i> Notifications</a></li>
            </ul>
            <ul class="user-nav">
                <li><a href="#" id="profile-btn"><i class="fas fa-user"></i> Profile</a></li>
                <li><a href="../actions/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Dashboard Section -->
        <section id="dashboard" class="content-section active">
            <div class="section-header">
                <h2>Dashboard Overview</h2>
            </div>
            <div class="welcome-banner">
                <h3>Welcome, <?= htmlspecialchars($playerInfo['first_name']) ?></h3>
                <p>Team: <?= htmlspecialchars($playerInfo['team_name'] ?? 'Not Assigned') ?></p>
                <p>Position: <?= htmlspecialchars($playerInfo['position']) ?></p>
                <p>Jersey Number: <?= htmlspecialchars($playerInfo['jersey_number']) ?></p>
            </div>
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Matches Played</h3>
                    <p class="stat-number"><?= $playerStats['matches_played'] ?? 0 ?></p>
                </div>
                <div class="stat-card">
                    <h3>Total Points</h3>
                    <p class="stat-number"><?= ($playerStats['total_spikes'] ?? 0) + ($playerStats['total_blocks'] ?? 0) ?></p>
                </div>
                <div class="stat-card">
                    <h3>Success Rate</h3>
                    <p class="stat-number">
                        <?php
                        $totalActions = ($playerStats['total_spikes'] ?? 0) + 
                                      ($playerStats['total_blocks'] ?? 0) + 
                                      ($playerStats['total_serves'] ?? 0);
                        $totalErrors = $playerStats['total_errors'] ?? 0;
                        echo $totalActions > 0 ? 
                             round(($totalActions - $totalErrors) / $totalActions * 100, 1) . '%' : 
                             '0%';
                        ?>
                    </p>
                </div>
            </div>
        </section>

        <!-- Matches Section -->
        <section id="matches" class="content-section">
            <div class="section-header">
                <h2>Upcoming Matches</h2>
            </div>
            <div class="matches-list">
                <?php if (!empty($upcomingMatches)): ?>
                    <?php foreach ($upcomingMatches as $match): ?>
                        <div class="match-card">
                            <div class="match-header">
                                <span class="match-date"><?= date('F j, Y', strtotime($match['match_date'])) ?></span>
                                <span class="match-venue"><?= htmlspecialchars($match['venue']) ?></span>
                            </div>
                            <div class="match-teams">
                                <?= htmlspecialchars($match['team1_name']) ?> vs <?= htmlspecialchars($match['team2_name']) ?>
                            </div>
                            <?php if ($match['strategy_text']): ?>
                                <div class="match-strategy">
                                    <h4>Coach's Strategy:</h4>
                                    <p><?= htmlspecialchars($match['strategy_text']) ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="no-data">No upcoming matches scheduled</p>
                <?php endif; ?>
            </div>
        </section>

        <!-- Statistics Section -->
        <section id="statistics" class="content-section">
            <div class="section-header">
                <h2>Performance Statistics</h2>
            </div>
            <div class="stats-container">
                <div class="stats-summary">
                    <h3>Career Statistics</h3>
                    <table>
                        <tr>
                            <th>Metric</th>
                            <th>Total</th>
                            <th>Average per Match</th>
                        </tr>
                        <tr>
                            <td>Spikes</td>
                            <td><?= $playerStats['total_spikes'] ?? 0 ?></td>
                            <td><?= $playerStats['avg_spikes'] ?? 0 ?></td>
                        </tr>
                        <tr>
                            <td>Blocks</td>
                            <td><?= $playerStats['total_blocks'] ?? 0 ?></td>
                            <td><?= $playerStats['avg_blocks'] ?? 0 ?></td>
                        </tr>
                        <tr>
                            <td>Serves</td>
                            <td><?= $playerStats['total_serves'] ?? 0 ?></td>
                            <td><?= $playerStats['avg_serves'] ?? 0 ?></td>
                        </tr>
                        <tr>
                            <td>Errors</td>
                            <td><?= $playerStats['total_errors'] ?? 0 ?></td>
                            <td><?= $playerStats['matches_played'] ? 
                                    round($playerStats['total_errors'] / $playerStats['matches_played'], 1) : 
                                    0 ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </section>

        <!-- Team Section -->
        <section id="team" class="content-section">
            <div class="section-header">
                <h2>Team Information</h2>
            </div>
            <?php if ($playerInfo['team_id']): ?>
                <div class="team-info">
                    <h3><?= htmlspecialchars($playerInfo['team_name']) ?></h3>
                    <p>Coach: <?= htmlspecialchars($playerInfo['coach_first_name'] . ' ' . $playerInfo['coach_last_name']) ?></p>
                    
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
                                <?php foreach ($teammates as $teammate): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($teammate['jersey_number']) ?></td>
                                        <td><?= htmlspecialchars($teammate['first_name'] . ' ' . $teammate['last_name']) ?></td>
                                        <td><?= htmlspecialchars($teammate['position']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php else: ?>
                <p class="no-data">You are not currently assigned to a team.</p>
            <?php endif; ?>
        </section>

        <!-- Notifications Section -->
        <section id="notifications" class="content-section">
            <div class="section-header">
                <h2>Announcements & Notifications</h2>
            </div>
            <div class="notifications-container">
                <div class="system-announcements">
                    <h3>System Announcements</h3>
                    <div id="announcements-list" class="announcements-list">
                        <!-- Announcements will be loaded here -->
                    </div>
                </div>
                <div class="personal-notifications">
                    <h3>My Notifications</h3>
                    <div id="notifications-list" class="notifications-list">
                        <!-- Notifications will be loaded here -->
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- Profile Modal -->
    <div id="profile-modal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Edit Profile</h2>
            <form id="profile-form">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($playerInfo['email']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="new_password">New Password (leave blank to keep current)</label>
                    <input type="password" id="new_password" name="new_password">
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password">
                </div>
                <button type="submit" class="btn-primary">Save Changes</button>
            </form>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="../assets/js/player_view.js"></script>
</body>
</html>
