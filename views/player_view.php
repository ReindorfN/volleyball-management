<?php
session_start();

// Redirect to login if not authenticated
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'player') {
    header("Location: ../views/login.html");
    exit();
}

require "../db/db_connection.php";

$database = new Database();
$conn = $database->getConnection();

$playerId = null;
$playerTeam = null;
$upcomingMatches = [];
$playerStats = [];

// Fetch player data including user information
$query = "SELECT u.*, p.player_id, p.team_id, p.position, p.jersey_number, t.team_name
          FROM v_ball_users u
          JOIN v_ball_players p ON u.user_id = p.user_id
          LEFT JOIN v_ball_teams t ON p.team_id = t.team_id
          WHERE u.user_id = ?";

$stmt = $conn->prepare($query);
if ($stmt) {
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $playerData = $result->fetch_assoc();
    
    if ($playerData) {
        $playerId = $playerData['player_id'];
        $playerTeam = $playerData['team_id'];
        $playerName = $playerData['first_name'] . ' ' . $playerData['last_name'];
        $teamName = $playerData['team_name'];
    }
    $stmt->close();
}

// Fetch upcoming matches
if ($playerTeam) {
    $matchesQuery = "
        SELECT 
            m.match_id,
            t1.team_name AS team1_name,
            t2.team_name AS team2_name,
            m.match_date,
            m.venue,
            m.match_status,
            m.score_team1,
            m.score_team2,
            ms.strategy_text
        FROM v_ball_matches m
        JOIN v_ball_teams t1 ON m.team1_id = t1.team_id
        JOIN v_ball_teams t2 ON m.team2_id = t2.team_id
        LEFT JOIN v_ball_match_strategies ms ON m.match_id = ms.match_id 
            AND ms.coach_id = (SELECT coach_id FROM v_ball_teams WHERE team_id = ?)
        WHERE (m.team1_id = ? OR m.team2_id = ?)
            AND m.match_date >= CURDATE()
        ORDER BY m.match_date ASC";
    
    $stmt = $conn->prepare($matchesQuery);
    if ($stmt) {
        $stmt->bind_param("iii", $playerTeam, $playerTeam, $playerTeam);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $upcomingMatches[] = $row;
        }
        $stmt->close();
    }

    // Fetch player statistics
    if ($playerId) {
        $statsQuery = "
            SELECT 
                COUNT(DISTINCT s.match_id) as matches_played,
                SUM(s.spikes) as total_spikes,
                SUM(s.blocks) as total_blocks,
                SUM(s.serves) as total_serves,
                SUM(s.errors) as total_errors
            FROM v_ball_statistics s
            WHERE s.player_id = ?";
        
        $stmt = $conn->prepare($statsQuery);
        if ($stmt) {
            $stmt->bind_param("i", $playerId);
            $stmt->execute();
            $playerStats = $stmt->get_result()->fetch_assoc();
            $stmt->close();
        }
    }
}

// Additional queries for new features
// 1. Fetch teammates
$teammatesQuery = "
    SELECT u.first_name, u.last_name, p.position, p.jersey_number
    FROM v_ball_players p
    JOIN v_ball_users u ON p.user_id = u.user_id
    WHERE p.team_id = ? AND p.player_id != ?
    ORDER BY p.jersey_number";

$teammates = [];
if ($stmt = $conn->prepare($teammatesQuery)) {
    $stmt->bind_param("ii", $playerTeam, $playerId);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $teammates[] = $row;
    }
    $stmt->close();
}

// 2. Fetch team announcements
$announcementsQuery = "
    SELECT a.announcement_text, a.created_at, 
           u.first_name, u.last_name
    FROM v_ball_announcements a
    JOIN v_ball_users u ON a.created_by = u.user_id
    WHERE a.team_id = ?
    ORDER BY a.created_at DESC
    LIMIT 5";

$announcements = [];
if ($stmt = $conn->prepare($announcementsQuery)) {
    $stmt->bind_param("i", $playerTeam);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $announcements[] = $row;
    }
    $stmt->close();
}

// 3. Fetch notifications
$notificationsQuery = "
    SELECT message, sent_at, is_read
    FROM v_ball_notifications
    WHERE user_id = ?
    ORDER BY sent_at DESC
    LIMIT 5";

$notifications = [];
if ($stmt = $conn->prepare($notificationsQuery)) {
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $notifications[] = $row;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Player Dashboard</title>
    <link rel="stylesheet" href="../assets/css/player_view.css">
</head>
<body>
    <header>
        <h1>Player Dashboard</h1>
        <div class="user-info">
            <p>Welcome, <?= htmlspecialchars($playerName) ?></p>
            <p>Team: <?= htmlspecialchars($teamName) ?></p>
        </div>
        <nav>
            <button id="profile-btn" class="profile-icon">
                <img src="<?= htmlspecialchars($playerData['profile_pic'] ?? '../assets/images/default-avatar.png') ?>" 
                     alt="Profile" class="profile-img">
            </button>
            <a href="../actions/logout.php">Logout</a>
        </nav>
    </header>

    <!-- Profile Modal -->
    <div id="profile-modal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>My Profile</h2>
            <form id="profile-form" enctype="multipart/form-data">
                <div class="profile-image-section">
                    <img src="<?= htmlspecialchars($playerData['profile_pic'] ?? '../assets/images/default-avatar.png') ?>" 
                         alt="Profile Picture" id="profile-preview">
                    <label for="profile-pic" class="upload-btn">Change Picture</label>
                    <input type="file" id="profile-pic" name="profile_pic" accept="image/*" hidden>
                </div>
                <div class="form-group">
                    <label for="first_name">First Name</label>
                    <input type="text" id="first_name" name="first_name" 
                           value="<?= htmlspecialchars($playerData['first_name']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="last_name">Last Name</label>
                    <input type="text" id="last_name" name="last_name" 
                           value="<?= htmlspecialchars($playerData['last_name']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" 
                           value="<?= htmlspecialchars($playerData['email']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="jersey_number">Jersey Number</label>
                    <input type="number" id="jersey_number" name="jersey_number" 
                           value="<?= htmlspecialchars($playerData['jersey_number']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="position">Position</label>
                    <select id="position" name="position" required>
                        <option value="spiker" <?= $playerData['position'] == 'spiker' ? 'selected' : '' ?>>Spiker</option>
                        <option value="blocker" <?= $playerData['position'] == 'blocker' ? 'selected' : '' ?>>Blocker</option>
                        <option value="setter" <?= $playerData['position'] == 'setter' ? 'selected' : '' ?>>Setter</option>
                        <option value="libero" <?= $playerData['position'] == 'libero' ? 'selected' : '' ?>>Libero</option>
                        <option value="server" <?= $playerData['position'] == 'server' ? 'selected' : '' ?>>Server</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <input type="password" id="new_password" name="new_password" 
                           placeholder="Leave blank to keep current password">
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password">
                </div>
                <div class="button-group">
                    <button type="submit">Save Changes</button>
                    <button type="button" class="cancel-btn">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <main>
        <!-- Quick Stats Overview -->
        <section id="quick-stats" class="dashboard-section">
            <h2>My Performance</h2>
            <div class="stats-container">
                <div class="stat-card">
                    <h3>Matches Played</h3>
                    <p><?= htmlspecialchars($playerStats['matches_played'] ?? 0); ?></p>
                </div>
                <div class="stat-card">
                    <h3>Total Spikes</h3>
                    <p><?= htmlspecialchars($playerStats['total_spikes'] ?? 0); ?></p>
                </div>
                <div class="stat-card">
                    <h3>Total Blocks</h3>
                    <p><?= htmlspecialchars($playerStats['total_blocks'] ?? 0); ?></p>
                </div>
                <div class="stat-card">
                    <h3>Total Serves</h3>
                    <p><?= htmlspecialchars($playerStats['total_serves'] ?? 0); ?></p>
                </div>
                <div class="stat-card">
                    <h3>Total Errors</h3>
                    <p><?= htmlspecialchars($playerStats['total_errors'] ?? 0); ?></p>
                </div>
            </div>
        </section>

        <!-- Notifications and Announcements -->
        <div class="flex-container">
            <section id="notifications" class="dashboard-section half-width">
                <h2>Notifications</h2>
                <div class="notification-list">
                    <?php if (empty($notifications)): ?>
                        <p class="empty-state">No new notifications</p>
                    <?php else: ?>
                        <?php foreach ($notifications as $notification): ?>
                            <div class="notification-item <?= $notification['is_read'] ? 'read' : 'unread' ?>">
                                <p><?= htmlspecialchars($notification['message']) ?></p>
                                <small><?= date('M d, Y', strtotime($notification['sent_at'])) ?></small>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </section>

            <section id="announcements" class="dashboard-section half-width">
                <h2>Team Announcements</h2>
                <div class="announcement-list">
                    <?php if (empty($announcements)): ?>
                        <p class="empty-state">No announcements yet</p>
                    <?php else: ?>
                        <?php foreach ($announcements as $announcement): ?>
                            <div class="announcement-item">
                                <p><?= htmlspecialchars($announcement['announcement_text']) ?></p>
                                <small>By <?= htmlspecialchars($announcement['first_name'] . ' ' . $announcement['last_name']) ?> 
                                       on <?= date('M d, Y', strtotime($announcement['created_at'])) ?></small>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </section>
        </div>

        <!-- Upcoming Matches -->
        <section id="matches" class="dashboard-section">
            <h2>Upcoming Matches</h2>
            <div class="table-container">
                <table id="matches_table">
                    <thead>
                        <tr>
                            <th>Match</th>
                            <th>Date</th>
                            <th>Venue</th>
                            <th>Status</th>
                            <th>Score</th>
                            <th>Strategy</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($upcomingMatches)): ?>
                            <tr><td colspan="6" class="empty-state">No upcoming matches scheduled</td></tr>
                        <?php else: ?>
                            <?php foreach ($upcomingMatches as $match): ?>
                            <tr>
                                <td><?= htmlspecialchars($match['team1_name'] . ' vs ' . $match['team2_name']); ?></td>
                                <td><?= htmlspecialchars($match['match_date']); ?></td>
                                <td><?= htmlspecialchars($match['venue']); ?></td>
                                <td data-status="<?= htmlspecialchars($match['match_status']); ?>">
                                    <?= htmlspecialchars($match['match_status']); ?>
                                </td>
                                <td><?= htmlspecialchars($match['score_team1'] . ' - ' . $match['score_team2']); ?></td>
                                <td><?= htmlspecialchars($match['strategy_text'] ?? 'No strategy set'); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Team Roster -->
        <section id="team-roster" class="dashboard-section">
            <h2>Team Roster</h2>
            <div class="table-container">
                <table id="roster_table">
                    <thead>
                        <tr>
                            <th>Jersey #</th>
                            <th>Name</th>
                            <th>Position</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($teammates)): ?>
                            <tr><td colspan="3" class="empty-state">No teammates found</td></tr>
                        <?php else: ?>
                            <?php foreach ($teammates as $teammate): ?>
                            <tr>
                                <td><?= htmlspecialchars($teammate['jersey_number']); ?></td>
                                <td><?= htmlspecialchars($teammate['first_name'] . ' ' . $teammate['last_name']); ?></td>
                                <td><?= htmlspecialchars($teammate['position']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    <script src="../assets/js/player_view.js"></script>
</body>
</html>
