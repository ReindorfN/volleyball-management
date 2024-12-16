<?php
session_start();

// Redirect to login if not authenticated
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'coach') {
    header("Location: ../views/login.html");
    exit();
}

// Fetch coach-specific data
require "../db/db_connection.php";

$database = new Database();
$conn = $database->getConnection();

$coachId = $_SESSION['user_id'];
$teams = [];
$teamPlayers = [];
$upcomingMatches = [];
$playerStats = [];

// Fetch teams assigned to the coach
$query = "SELECT team_id, team_name FROM v_ball_teams WHERE coach_id = ?";
$stmt = $conn->prepare($query);
if ($stmt) {
    $stmt->bind_param("i", $coachId);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $teams[] = $row;
    }
    $stmt->close();
}

// Fetch players in coach's teams
if (!empty($teams)) {
    $teamIds = array_column($teams, 'team_id');
    $teamIdsStr = implode(',', $teamIds);
    
    $query = "SELECT p.player_id, u.first_name, u.last_name, p.position, 
                     p.jersey_number, t.team_name 
              FROM v_ball_players p
              JOIN v_ball_users u ON p.user_id = u.user_id
              JOIN v_ball_teams t ON p.team_id = t.team_id
              WHERE p.team_id IN ($teamIdsStr)
              ORDER BY t.team_name, p.jersey_number";
    
    $result = $conn->query($query);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $teamPlayers[] = $row;
        }
    }
}

// Fetch upcoming matches for coach's teams
if (!empty($teams)) {
    $teamIds = array_column($teams, 'team_id');
    $teamIdsStr = implode(',', $teamIds);
    
    // Add debug output
    echo "<!-- Debug: Team IDs: $teamIdsStr -->";
    
    $query = "SELECT m.match_id, t1.team_name as team1_name, 
                     t2.team_name as team2_name, m.match_date, 
                     m.venue, m.match_status, m.score_team1, m.score_team2,
                     ms.strategy_text
              FROM v_ball_matches m
              JOIN v_ball_teams t1 ON m.team1_id = t1.team_id
              JOIN v_ball_teams t2 ON m.team2_id = t2.team_id
              LEFT JOIN v_ball_match_strategies ms ON m.match_id = ms.match_id 
                   AND ms.coach_id = ?
              WHERE (m.team1_id IN ($teamIdsStr) OR m.team2_id IN ($teamIdsStr))
                AND m.match_date >= CURDATE()
              ORDER BY m.match_date ASC";
    
    if($stmt = $conn->prepare($query)) {
        $stmt->bind_param("i", $coachId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        // Add debug output
        echo "<!-- Debug: Number of matches found: " . $result->num_rows . " -->";
        
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $upcomingMatches[] = $row;
            }
        }
        $stmt->close();
    }
}

// Add this debug code after line 82
if (empty($upcomingMatches)) {
    echo "<!-- Debug: No matches found. Teams: " . print_r($teams, true) . " -->";
}

// Fetch player statistics
if (!empty($teams)) {
    $query = "SELECT 
                u.first_name,
                u.last_name,
                p.player_id,
                COUNT(DISTINCT s.match_id) as matches_played,
                SUM(s.spikes) as total_spikes,
                SUM(s.blocks) as total_blocks,
                SUM(s.serves) as total_serves,
                SUM(s.errors) as total_errors
              FROM v_ball_players p
              JOIN v_ball_users u ON p.user_id = u.user_id
              LEFT JOIN v_ball_statistics s ON p.player_id = s.player_id
              WHERE p.team_id IN ($teamIdsStr)
              GROUP BY p.player_id, u.first_name, u.last_name
              ORDER BY total_spikes DESC";
    
    $result = $conn->query($query);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $playerStats[] = $row;
        }
    }
}

// Close database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coach Dashboard</title>
    <link rel="stylesheet" href="../assets/css/coach_view.css">
    <link rel="icon" href="../assets/images/v-ball_favicon.ico">
</head>
<body data-coach-id="<?php echo $_SESSION['user_id']; ?>">
    <header>
        <h1>Ashesi Volleyball Management | Coach</h1>
        <nav>
            <ul>
                <li><a href="#team" class="active">Team Management</a></li>
                <li><a href="#matches">Match Preparation</a></li>
                <li><a href="#statistics">Statistics</a></li>
                <button id="logout_btn"><a href="../actions/logout.php">Logout</a></button>
            </ul>
        </nav>
    </header>

    <main>
        <!-- Team Management Section -->
        <section id="team" class="section active">
            <h2>Team Management</h2>
            <button id="add_player_btn">Add Player</button>
            <div class="stat">
                <h3>Team Players</h3>
                <table id="team_table">
                    <thead>
                        <tr>
                            <th>Player ID</th>
                            <th>Player Name</th>
                            <th>Position</th>
                            <th>Jersey Number</th>
                            <th>Team</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($teamPlayers as $player): ?>
                        <tr>
                            <td><?= htmlspecialchars($player['player_id']); ?></td>
                            <td><?= htmlspecialchars($player['first_name'] . ' ' . $player['last_name']); ?></td>
                            <td><?= htmlspecialchars($player['position']); ?></td>
                            <td><?= htmlspecialchars($player['jersey_number']); ?></td>
                            <td><?= htmlspecialchars($player['team_name']); ?></td>
                            <td>
                                <button 
                                    id="edit_player_<?= $player['player_id']; ?>" 
                                    class="edit-player-btn" 
                                    data-player-id="<?= $player['player_id']; ?>">
                                    Edit
                                </button>
                                <button 
                                    id="delete_player_<?= $player['player_id']; ?>" 
                                    class="delete-player-btn" 
                                    data-player-id="<?= $player['player_id']; ?>">
                                    Delete
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Match Preparation Section -->
        <section id="matches" class="section">
            <h2>Match Preparation</h2>
            <div class="stat">
                <h3>Upcoming Matches</h3>
                <table id="matches_table">
                    <thead>
                        <tr>
                            <th>Match ID</th>
                            <th>Teams</th>
                            <th>Date</th>
                            <th>Venue</th>
                            <th>Status</th>
                            <th>Score</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($upcomingMatches as $match): ?>
                        <tr>
                            <td><?= htmlspecialchars($match['match_id']); ?></td>
                            <td><?= htmlspecialchars($match['team1_name'] . ' vs ' . $match['team2_name']); ?></td>
                            <td><?= htmlspecialchars($match['match_date']); ?></td>
                            <td><?= htmlspecialchars($match['venue']); ?></td>
                            <td><?= htmlspecialchars($match['match_status']); ?></td>
                            <td><?= htmlspecialchars($match['score_team1'] . ' - ' . $match['score_team2']); ?></td>
                            <td>
                                <button 
                                    class="set-strategy-btn" 
                                    data-match-id="<?= $match['match_id']; ?>">
                                    Set Strategy
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Statistics Section -->
        <section id="statistics" class="section">
            <h2>Team Statistics</h2>
            <div class="stat">
                <h3>Player Performance</h3>
                <table id="statistics_table">
                    <thead>
                        <tr>
                            <th>Player Name</th>
                            <th>Matches Played</th>
                            <th>Points Scored</th>
                            <th>Assists</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($playerStats as $stat): ?>
                        <tr>
                            <td><?= htmlspecialchars($stat['player_name']); ?></td>
                            <td><?= htmlspecialchars($stat['matches_played']); ?></td>
                            <td><?= htmlspecialchars($stat['points_scored']); ?></td>
                            <td><?= htmlspecialchars($stat['assists']); ?></td>
                            <td>
                                <button 
                                    class="update-stats-btn" 
                                    data-player-id="<?= $stat['player_id']; ?>">
                                    Update Stats
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    <!-- Add Player Modal -->
    <div id="add_player_modal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Add New Player</h2>
            <form id="add_player_form">
                <div class="form-group">
                    <label for="first_name">First Name:</label>
                    <input type="text" id="first_name" name="fname" required>
                </div>
                <div class="form-group">
                    <label for="last_name">Last Name:</label>
                    <input type="text" id="last_name" name="lname" required>
                </div>
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="position">Position:</label>
                    <select id="position" name="position" required>
                        <option value="" disabled selected>Select Position</option>
                        <option value="Setter">Setter</option>
                        <option value="Outside Hitter">Outside Hitter</option>
                        <option value="Middle Blocker">Middle Blocker</option>
                        <option value="Opposite">Opposite</option>
                        <option value="Libero">Libero</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="jersey_number">Jersey Number:</label>
                    <input type="number" id="jersey_number" name="jersey_number" required>
                </div>
                <div class="form-group">
                    <label for="team_id">Team:</label>
                    <select id="team_id" name="team_id" required>
                        <option value="" disabled selected>Select Team</option>
                    </select>
                </div>
                <button type="submit">Add Player</button>
            </form>
        </div>
    </div>

    <!-- Strategy Modal -->
    <div id="strategy_modal" class="modal">
        <div class="modal-content">
            <span class="close" id="strategy_close">&times;</span>
            <h2>Set Match Strategy</h2>
            <form id="strategy_form">
                <input type="hidden" id="strategy_match_id">
                <div class="form-group">
                    <label for="strategy_text">Strategy Notes:</label>
                    <textarea id="strategy_text" name="strategy" rows="6" required 
                        placeholder="Enter match strategy details..."></textarea>
                </div>
                <div class="button-group">
                    <button type="button" id="cancel_strategy">Cancel</button>
                    <button type="submit">Save Strategy</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Player Modal -->
    <div id="edit_player_modal" class="modal">
        <div class="modal-content">
            <span class="close" id="edit_player_close">&times;</span>
            <h2>Edit Player</h2>
            <form id="edit_player_form">
                <input type="hidden" id="edit_player_id">
                <div class="form-group">
                    <label for="edit_position">Position:</label>
                    <select id="edit_position" name="position" required>
                        <option value="spiker">Spiker</option>
                        <option value="blocker">Blocker</option>
                        <option value="setter">Setter</option>
                        <option value="libero">Libero</option>
                        <option value="server">Server</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="edit_jersey_number">Jersey Number:</label>
                    <input type="number" id="edit_jersey_number" name="jersey_number" required>
                </div>
                <div class="form-group">
                    <label for="edit_team_id">Team:</label>
                    <select id="edit_team_id" name="team_id" required>
                        <?php foreach ($teams as $team): ?>
                            <option value="<?= $team['team_id'] ?>"><?= htmlspecialchars($team['team_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="button-group">
                    <button type="button" id="cancel_edit_player">Cancel</button>
                    <button type="submit">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../assets/js/coach_view.js"></script>
</body>
</html>