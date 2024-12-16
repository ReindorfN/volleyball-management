<?php
session_start();

// Redirect to login if not authenticated
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'player') {
    // header("Location: ../actions/login.php");
    exit();
}

// Fetch player-specific data
require "../db/db_connection.php";

$database = new Database();
$conn = $database->getConnection();

$playerId = $_SESSION['user_id'];
$upcomingMatches = [];
$teamRoster = [];
$announcements = [];

// Fetch upcoming matches for the player's team
$matchesQuery = "
    SELECT 
        m.match_id,
        t1.team_name AS team1_name,
        t2.team_name AS team2_name,
        m.match_date,
        m.venue
    FROM v_ball_matches AS m
    LEFT JOIN v_ball_teams AS t1 ON m.team1_id = t1.team_id
    LEFT JOIN v_ball_teams AS t2 ON m.team2_id = t2.team_id
    WHERE t1.team_id = (SELECT team_id FROM v_ball_players WHERE player_id = ?)
       OR t2.team_id = (SELECT team_id FROM v_ball_players WHERE player_id = ?)
    ORDER BY m.match_date ASC";
$stmt = $conn->prepare($matchesQuery);
if ($stmt) {
    $stmt->bind_param("ii", $playerId, $playerId);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $upcomingMatches[] = $row;
    }
}

// Fetch team roster
$rosterQuery = "
    SELECT u.first_name, u.last_name, p.position, p.jersey_number
    FROM v_ball_users AS u
    JOIN v_ball_players AS p ON u.user_id = p.player_id
    WHERE p.team_id = (SELECT team_id FROM v_ball_players WHERE player_id = ?)";
$stmt = $conn->prepare($rosterQuery);
if ($stmt) {
    $stmt->bind_param("i", $playerId);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $teamRoster[] = $row;
    }
}

// Fetch announcements for the player's team
$announcementsQuery = "
    SELECT a.announcement_text, a.created_at
    FROM v_ball_announcements AS a
    WHERE a.team_id = (SELECT team_id FROM v_ball_players WHERE player_id = ?)
    ORDER BY a.created_at DESC";
$stmt = $conn->prepare($announcementsQuery);
if ($stmt) {
    $stmt->bind_param("i", $playerId);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $announcements[] = $row;
    }
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
        <h1>Ashesi Volleyball Management | Player Dashboard</h1>
        <nav>
            <ul>
                <li><a href="#dashboard" id="dashboard_btn" class="active">Dashboard</a></li>
                <li><a href="#schedule" id="schedule_btn">Match Schedule</a></li>
                <li><a href="#roster" id="roster_btn">Team Roster</a></li>
                <li><a href="#announcements" id="announcements_btn">Announcements</a></li>
                <li><a href="../actions/logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <!-- Dashboard Section -->
        <section id="dashboard" class="section active">
            <h2>Dashboard</h2>
            <div class="overview">
                <div class="stat">
                    <h3>Your Position</h3>
                    <p>Position: <span id="player_position">
                        <?php echo htmlspecialchars($_SESSION['position'] ?? 'N/A'); ?>
                    </span></p>
                    <p>Jersey Number: <span id="player_jersey">
                        <?php echo htmlspecialchars($_SESSION['jersey_number'] ?? 'N/A'); ?>
                    </span></p>
                </div>
            </div>
        </section>

        <!-- Match Schedule Section -->
        <section id="schedule" class="section">
            <h2>Match Schedule</h2>
            <table>
                <thead>
                    <tr>
                        <th>Match ID</th>
                        <th>Teams</th>
                        <th>Date</th>
                        <th>Venue</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($upcomingMatches as $match): ?>
                        <tr>
                            <td><?= htmlspecialchars($match['match_id']); ?></td>
                            <td><?= htmlspecialchars($match['team1_name']); ?> vs <?= htmlspecialchars($match['team2_name']); ?></td>
                            <td><?= htmlspecialchars($match['match_date']); ?></td>
                            <td><?= htmlspecialchars($match['venue']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>

        <!-- Team Roster Section -->
        <section id="roster" class="section">
            <h2>Team Roster</h2>
            <table>
                <thead>
                    <tr>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Position</th>
                        <th>Jersey Number</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($teamRoster as $teammate): ?>
                        <tr>
                            <td><?= htmlspecialchars($teammate['first_name']); ?></td>
                            <td><?= htmlspecialchars($teammate['last_name']); ?></td>
                            <td><?= htmlspecialchars($teammate['position']); ?></td>
                            <td><?= htmlspecialchars($teammate['jersey_number']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>

        <!-- Announcements Section -->
        <section id="announcements" class="section">
            <h2>Announcements</h2>
            <ul>
                <?php foreach ($announcements as $announcement): ?>
                    <li>
                        <p><?= htmlspecialchars($announcement['announcement_text']); ?></p>
                        <small>Posted on <?= htmlspecialchars($announcement['created_at']); ?></small>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>
    </main>

    <footer>
        <p>&copy; 2024 Ashesi Volleyball Management</p>
    </footer>

    <script src="../assets/js/player_view.js"></script>
</body>
</html>
