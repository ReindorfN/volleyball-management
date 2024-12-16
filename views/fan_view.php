<?php
session_start();

// Redirect if not authenticated as fan
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'fan') {
    header("Location: ../views/login.html");
    exit();
}

require "../db/db_connection.php";
$database = new Database();
$conn = $database->getConnection();

// Fetch user data
$userData = [];
$userQuery = "SELECT * FROM v_ball_users WHERE user_id = ?";
$stmt = $conn->prepare($userQuery);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$userData = $stmt->get_result()->fetch_assoc();

// Fetch followed teams
$followedTeams = [];
$followQuery = "SELECT t.* 
                FROM v_ball_teams t
                JOIN v_ball_fan_follows f ON t.team_id = f.team_id
                WHERE f.fan_id = ?";
$stmt = $conn->prepare($followQuery);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $followedTeams[] = $row;
}

// Fetch upcoming matches
$upcomingMatches = [];
$matchQuery = "SELECT m.*, 
               t1.team_name as team1_name,
               t2.team_name as team2_name
               FROM v_ball_matches m
               JOIN v_ball_teams t1 ON m.team1_id = t1.team_id
               JOIN v_ball_teams t2 ON m.team2_id = t2.team_id
               WHERE m.match_date >= CURDATE()
               ORDER BY m.match_date ASC
               LIMIT 5";
$result = $conn->query($matchQuery);
while ($row = $result->fetch_assoc()) {
    $upcomingMatches[] = $row;
}

// Fetch team rankings
$rankings = [];
$rankingsQuery = "SELECT 
                    t.team_id,
                    t.team_name,
                    COUNT(m.match_id) as matches_played,
                    SUM(CASE 
                        WHEN (m.team1_id = t.team_id AND m.score_team1 > m.score_team2) OR
                             (m.team2_id = t.team_id AND m.score_team2 > m.score_team1) 
                        THEN 1 ELSE 0 END) as matches_won,
                    SUM(CASE 
                        WHEN (m.team1_id = t.team_id AND m.score_team1 < m.score_team2) OR
                             (m.team2_id = t.team_id AND m.score_team2 < m.score_team1) 
                        THEN 1 ELSE 0 END) as matches_lost
                  FROM v_ball_teams t
                  LEFT JOIN v_ball_matches m ON (t.team_id = m.team1_id OR t.team_id = m.team2_id)
                       AND m.match_status = 'completed'
                  GROUP BY t.team_id, t.team_name
                  ORDER BY matches_won DESC, matches_lost ASC
                  LIMIT 10";
$result = $conn->query($rankingsQuery);
while ($row = $result->fetch_assoc()) {
    $rankings[] = $row;
}

// HTML content starts here
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fan Dashboard</title>
    <link rel="stylesheet" href="../assets/css/fan_view.css">
</head>
<body>
    <header>
        <h1>Fan Dashboard</h1>
        <div class="user-info">
            <p>Welcome, <?= htmlspecialchars($userData['first_name'] . ' ' . $userData['last_name']) ?></p>
        </div>
        <nav>
            <button id="profile-btn" class="profile-icon">
                <img src="<?= htmlspecialchars($userData['profile_pic'] ?? '../assets/images/default-avatar.jpg') ?>" 
                     alt="Profile" class="profile-img">
            </button>
            <a href="../actions/logout.php" class="logout-btn">Logout</a>
        </nav>
    </header>

    <main>
        <!-- Featured Match Section -->
        <section id="featured-match" class="dashboard-section">
            <h2>Featured Match</h2>
            <div class="featured-match-card">
                <?php if (!empty($upcomingMatches)): ?>
                    <div class="match-details">
                        <h3><?= htmlspecialchars($upcomingMatches[0]['team1_name']) ?> vs <?= htmlspecialchars($upcomingMatches[0]['team2_name']) ?></h3>
                        <p class="match-date"><?= date('F j, Y', strtotime($upcomingMatches[0]['match_date'])) ?></p>
                        <p class="match-venue"><?= htmlspecialchars($upcomingMatches[0]['venue']) ?></p>
                    </div>
                <?php else: ?>
                    <p>No upcoming matches scheduled</p>
                <?php endif; ?>
            </div>
        </section>

        <!-- Quick Actions -->
        <section id="quick-actions" class="dashboard-section">
            <div class="action-buttons">
                <button id="follow-team-btn">Follow Team</button>
                <button id="view-standings-btn">View Standings</button>
                <button id="search-matches-btn">Search Matches</button>
            </div>
        </section>

        <!-- Upcoming Matches Section -->
        <div class="flex-container">
            <section id="upcoming-matches" class="dashboard-section half-width">
                <h2>Upcoming Matches</h2>
                <div class="matches-list">
                    <?php foreach ($upcomingMatches as $match): ?>
                        <div class="match-item" data-match-id="<?= $match['match_id'] ?>">
                            <div class="match-teams">
                                <?= htmlspecialchars($match['team1_name']) ?> vs <?= htmlspecialchars($match['team2_name']) ?>
                            </div>
                            <div class="match-info">
                                <span class="match-date"><?= date('M j, Y', strtotime($match['match_date'])) ?></span>
                                <span class="match-venue"><?= htmlspecialchars($match['venue']) ?></span>
                                <span class="match-status"><?= htmlspecialchars($match['match_status']) ?></span>
                            </div>
                            <?php if ($match['match_status'] === 'ongoing'): ?>
                                <div class="score" data-match-id="<?= $match['match_id'] ?>">
                                    <?= $match['score_team1'] ?> - <?= $match['score_team2'] ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>

            <section id="followed-teams" class="dashboard-section half-width">
                <h2>My Teams</h2>
                <div class="teams-list">
                    <?php if (!empty($followedTeams)): ?>
                        <?php foreach ($followedTeams as $team): ?>
                            <div class="team-card" data-team-id="<?= $team['team_id'] ?>">
                                <h3><?= htmlspecialchars($team['team_name']) ?></h3>
                                <button class="follow-team-button following" data-team-id="<?= $team['team_id'] ?>">
                                    Following
                                </button>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="empty-state">You haven't followed any teams yet</p>
                    <?php endif; ?>
                </div>
            </section>
        </div>

        <!-- Match Results Section -->
        <section id="match-results" class="dashboard-section">
            <h2>Recent Results</h2>
            <div class="table-container">
                <table id="results-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Teams</th>
                            <th>Score</th>
                            <th>Venue</th>
                            <th>Tournament</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Match results content -->
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Team Rankings Section -->
        <section id="team-rankings" class="dashboard-section">
            <h2>Team Rankings</h2>
            <div class="table-container">
                <table id="rankings-table">
                    <thead>
                        <tr>
                            <th>Rank</th>
                            <th>Team</th>
                            <th>Matches</th>
                            <th>Won</th>
                            <th>Lost</th>
                            <th>Points</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rankings as $index => $team): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td><?= htmlspecialchars($team['team_name']) ?></td>
                                <td><?= $team['matches_played'] ?></td>
                                <td><?= $team['matches_won'] ?></td>
                                <td><?= $team['matches_lost'] ?></td>
                                <td><?= $team['matches_won'] * 2 ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Team Rosters Section -->
        <section id="team-rosters" class="dashboard-section">
            <h2>Team Rosters</h2>
            <div class="roster-container">
                <!-- Team rosters content -->
            </div>
        </section>
    </main>

    <!-- Follow Team Modal -->
    <div id="follow-team-modal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Follow Teams</h2>
            <div class="search-teams">
                <input type="text" id="team-search" placeholder="Search teams...">
            </div>
            <div class="teams-grid">
                <?php
                // Fetch all teams and current user's followed teams
                $teamsQuery = "SELECT t.*, 
                              CASE WHEN ff.team_id IS NOT NULL THEN 1 ELSE 0 END as is_following
                              FROM v_ball_teams t
                              LEFT JOIN v_ball_fan_follows ff ON t.team_id = ff.team_id 
                              AND ff.fan_id = ?
                              ORDER BY t.team_name";
                
                $stmt = $conn->prepare($teamsQuery);
                $stmt->bind_param("i", $_SESSION['user_id']);
                $stmt->execute();
                $teams = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

                foreach ($teams as $team):
                ?>
                    <div class="team-card" data-team-id="<?= $team['team_id'] ?>">
                        <div class="team-info">
                            <h3><?= htmlspecialchars($team['team_name']) ?></h3>
                            <p class="team-details">
                                Coach: <?= htmlspecialchars($team['coach_name'] ?? 'TBA') ?>
                            </p>
                        </div>
                        <button class="follow-team-button <?= $team['is_following'] ? 'following' : '' ?>"
                                data-team-id="<?= $team['team_id'] ?>">
                            <?= $team['is_following'] ? 'Following' : 'Follow' ?>
                        </button>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Profile Modal -->
    <div id="profile-modal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>My Profile</h2>
            <form id="profile-form" enctype="multipart/form-data">
                <!-- Profile form content -->
            </form>
        </div>
    </div>

    <script src="../assets/js/fan_view.js"></script>
</body>
</html>
<?php
// Close the connection only after all HTML is rendered
$conn->close();
?>
