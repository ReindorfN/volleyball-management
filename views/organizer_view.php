<?php
session_start();

// Redirect to login if not authenticated
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'organizer') {
    header("Location: ../views/login.html");
    exit();
}

// Fetch data for all matches and tournaments
require "../db/db_connection.php";

$database = new Database();
$conn = $database->getConnection();

$upcomingMatches = [];
$tournaments = [];

// Updated query to fetch matches with team names
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
    ORDER BY m.match_date ASC
";
$stmt = $conn->prepare($matchesQuery);
if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $upcomingMatches[] = $row;
    }
}

// Fetch all tournaments
$tournamentQuery = "SELECT tournament_id, tournament_name, start_date, end_date FROM v_ball_tournaments ORDER BY start_date ASC";
$stmt = $conn->prepare($tournamentQuery);
if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $tournaments[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Organizer Dashboard</title>
    <link rel="stylesheet" href="../assets/css/organizer_view.css">
</head>
<body>
    <header>
        <h1>Ashesi Volleyball Management | Organizer Dashboard</h1>
        <nav>
            <ul>
                <li><a href="#dashboard" id="dashboard_btn" class="active">Dashboard</a></li>
                <li><a href="#matches" id="matches_btn">Match Management</a></li>
                <li><a href="#tournaments" id="tournaments_btn">Tournament Management</a></li>
                <button id="logout_btn"><a href="../actions/logout.php">Logout</a></button>
            </ul>
        </nav>
    </header>

    <main>
        <!-- Dashboard Section -->
        <section id="dashboard" class="section active">
            <h2>Dashboard</h2>
            <div class="overview">
                <div class="stat">
                    <h3>Upcoming Matches</h3>
                    <table>
                        <thead>
                            <td>Match Id</td>
                            <td>Teams Involved</td>
                            <td>Match Date</td>
                            <td>Match venue</td>
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
                </div>
                <div class="stat">
                    <h3>Ongoing Tournaments</h3>
                    <ul>
                        <?php foreach ($tournaments as $tournament): ?>
                            <li>
                                <?= htmlspecialchars($tournament['tournament_name']); ?> (<?= htmlspecialchars($tournament['start_date']); ?> to <?= htmlspecialchars($tournament['end_date']); ?>)
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </section>

        <!-- Match Management Section -->
        <section id="matches" class="section">
            <h2>Match Management</h2>
            <button id="add_match_btn">Add Match</button>
            <div class="stat">
                <h3>Matches</h3>
                <table id="match_table">
                    <thead>
                        <tr>
                            <th>Match ID</th>
                            <th>Teams</th>
                            <th>Date</th>
                            <th>Venue</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                            <?php foreach ($upcomingMatches as $match): ?>
                            <tr>
                                <td><?= htmlspecialchars($match['match_id']); ?></td>
                                <td><?= htmlspecialchars($match['team1_name']); ?> vs <?= htmlspecialchars($match['team2_name']); ?></td>
                                <td><?= htmlspecialchars($match['match_date']); ?></td>
                                <td><?= htmlspecialchars($match['venue']); ?></td>
                                <td>
                                    <button 
                                        id="edit_match_<?= $match['match_id']; ?>" 
                                        class="edit-match-btn" 
                                        data-match-id="<?= $match['match_id']; ?>">
                                        Edit
                                    </button>
                                    <button 
                                        id="delete_match_<?= $match['match_id']; ?>" 
                                        class="delete-match-btn" 
                                        data-match-id="<?= $match['match_id']; ?>">
                                        Delete
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                </table>
            </div>
        </section>

        <!-- Tournament Management Section -->
        <section id="tournaments" class="section">
            <h2>Tournament Management</h2>
            <button id="add_tournament_btn">Add Tournament</button>
            <div class="stat">
                <h3>Tournaments</h3>
                <table id="tournament_table">
                    <thead>
                        <tr>
                            <th>Tournament ID</th>
                            <th>Name</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tournaments as $tournament): ?>
                            <tr>
                                <td><?= htmlspecialchars($tournament['tournament_id']); ?></td>
                                <td><?= htmlspecialchars($tournament['tournament_name']); ?></td>
                                <td><?= htmlspecialchars($tournament['start_date']); ?></td>
                                <td><?= htmlspecialchars($tournament['end_date']); ?></td>
                                <td>
                                    <button 
                                        id="edit_tournament_<?= $tournament['tournament_id']; ?>" 
                                        class="edit-tournament-btn" 
                                        data-tournament-id="<?= $tournament['tournament_id']; ?>">
                                        Edit
                                    </button>
                                    <button 
                                        id="delete_tournament_<?= $tournament['tournament_id']; ?>" 
                                        class="delete-tournament-btn" 
                                        data-tournament-id="<?= $tournament['tournament_id']; ?>">
                                        Delete
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>


            <!-- Add Tournament Modal -->
        <div id="add_tournament_modal" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span> <!-- Close button -->
                <form id="add_tournament_form" action="../actions/add_tournament.php" method="post">
                    <h1>Add Tournament</h1>

                    <div class="input-box">
                        <label for="tournament_name">Tournament Name:</label>
                        <input type="text" id="tournament_name" name="tournament_name" placeholder="Enter tournament name" required>
                    </div>

                    <div class="input-box">
                        <label for="start_date">Start Date:</label>
                        <input type="date" id="start_date" name="start_date" required>
                    </div>

                    <div class="input-box">
                        <label for="end_date">End Date:</label>
                        <input type="date" id="end_date" name="end_date" required>
                    </div>

                    <button type="submit" class="btn">Add Tournament</button>
                </form>
            </div>
        </div>

        <!-- Add Match Modal -->
        <div id="add_match_modal" class="modal">
            <div class="modal-content">
                <span class="close" id="match_close">&times;</span> <!-- Close button -->
                <form id="add_match_form" action="../actions/add_match.php" method="post">
                    <h1>Add Match</h1>

                    <div class="input-box">
                        <label for="team1_id">Team 1:</label>
                        <select id="team1_id" name="team1_id" required>
                            <option value="" disabled selected>Select Team 1</option>
                            <!-- Teams will be dynamically populated -->
                        </select>
                    </div>

                    <div class="input-box">
                        <label for="team2_id">Team 2:</label>
                        <select id="team2_id" name="team2_id" required>
                            <option value="" disabled selected>Select Team 2</option>
                            <!-- Teams will be dynamically populated -->
                        </select>
                    </div>

                    <div class="input-box">
                        <label for="match_date">Match Date:</label>
                        <input type="datetime-local" id="match_date" name="match_date" required>
                    </div>

                    <div class="input-box">
                        <label for="venue">Venue:</label>
                        <input type="text" id="venue" name="venue" placeholder="Enter venue" required>
                    </div>

                    <button type="submit" class="btn">Add Match</button>
                </form>
            </div>
        </div>

        <!-- Edit Match Modal -->
        <div id="edit_match_modal" class="modal">
            <div class="modal-content">
                <span class="close" id="edit_match_close">&times;</span>
                <h2>Edit Match</h2>
                <form id="edit_match_form">
                    <input type="hidden" id="edit_match_id">
                    <div class="form-group">
                        <label for="edit_team1_id">Team 1:</label>
                        <select id="edit_team1_id" required>
                            <option value="" disabled selected>Select Team</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_team2_id">Team 2:</label>
                        <select id="edit_team2_id" required>
                            <option value="" disabled selected>Select Team</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_match_date">Match Date:</label>
                        <input type="datetime-local" id="edit_match_date" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_match_venue">Venue:</label>
                        <input type="text" id="edit_match_venue" required>
                    </div>
                    <div class="button-group">
                        <button type="button" id="cancel_edit">Cancel</button>
                        <button type="submit">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Edit Tournament Modal -->
        <div id="edit_tournament_modal" class="modal">
            <div class="modal-content">
                <span class="close" id="edit_tournament_close">&times;</span>
                <h2>Edit Tournament</h2>
                <form id="edit_tournament_form">
                    <input type="hidden" id="edit_tournament_id">
                    <div class="form-group">
                        <label for="edit_tournament_name">Tournament Name:</label>
                        <input type="text" id="edit_tournament_name" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_start_date">Start Date:</label>
                        <input type="date" id="edit_start_date" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_end_date">End Date:</label>
                        <input type="date" id="edit_end_date" required>
                    </div>
                    <div class="button-group">
                        <button type="button" id="cancel_edit_tournament">Cancel</button>
                        <button type="submit">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>

    </main>

    <footer>
        <p>&copy; 2024 Ashesi Volleyball Management</p>
    </footer>

    <script src="../assets/js/organizer_view.js"></script>
</body>
</html>
