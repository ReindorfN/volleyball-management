
<?php
session_start();

// Redirect to login if not authenticated
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'coach') {
    header("Location: ../views/login.html");
    exit();
}

// Fetch coach-specific data, if needed
require "../db/db_connection.php";

$database = new Database();
$conn = $database->getConnection();

$coachId = $_SESSION['user_id'];
$teams = [];

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
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coach Dashboard</title>
    <link rel="stylesheet" href="../assets/css/coach_view.css">
</head>
<body>
    <header>
        <h1>Ashesi Volleyball Management | Coach Dashboard</h1>
        <nav>
            <ul>
                <li><a href="#dashboard" id="dashboard_btn" class="active">Dashboard</a></li>
                <li><a href="#team" id="team_btn">Team Management</a></li>
                <li><a href="#matches" id="matches_btn">Matches</a></li>
                <li><a href="#announcements" id="announcements_btn">Announcements</a></li>
                <button><a href="../actions/logout.php">Logout</a></button>
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
                    <ul id="upcoming_matches">
                        <!-- Dynamically populate with match data -->
                    </ul>
                </div>
                <div class="stat">
                    <h3>Team Performance</h3>
                    <p>Wins: <span id="team_wins">0</span></p>
                    <p>Losses: <span id="team_losses">0</span></p>
                </div>
            </div>
        </section>

        <!-- Team Management Section -->
        <section id="team" class="section">
            <h2>Team Management</h2>
            <button id="add_player_btn">Add Player</button>
            <div id="team_thingy">
                <table id="team_table">
                    <thead>
                        <tr>
                            <th>Player Name</th>
                            <th>Position</th>
                            <th>Jersey Number</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Dynamically populate team table -->
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Matches Section -->
        <section id="matches" class="section">
            <h2>Match Preparation</h2>
            <h3>Set Strategy</h3>
            <textarea id="match_strategy" placeholder="Enter strategy for the next match"></textarea>
            <button id="save_strategy_btn">Save Strategy</button>
        </section>

        <!-- Announcements Section -->
        <section id="announcements" class="section">
            <h2>Announcements</h2>
            <textarea id="announcement_text" placeholder="Write your announcement here"></textarea>
            <button id="post_announcement_btn">Post Announcement</button>
            <div id="announcements_list">
                <!-- Dynamically populated announcements -->
            </div>
        </section>

        <div id="add_player_modal" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span> <!-- Close button -->
                <form id="addplayer-form" action="../actions/add_player.php" method="post">
                    <h1>Add Player</h1>
        
                    <!-- Personal Information -->
                    <div class="input-box">
                        <ul>
                            <li><input type="text" placeholder="First Name" id="fname" name="fname" required></li>
                            <li><input type="text" placeholder="Last Name" id="lname" name="lname" required></li>
                        </ul>
                    </div>
                    <div class="input-box">
                        <input type="email" placeholder="Email" id="email" name="email" required>
                    </div>
                    <div class="input-box">
                        <input type="password" placeholder="Password" id="password" name="password" required>
                    </div>
        
                    <!-- Team-Related Information -->
                    <div class="input-box">
                        <select id="position" name="position" required>
                            <option value="" disabled selected>Select Position</option>
                            <option value="spiker">Spiker</option>
                            <option value="blocker">Blocker</option>
                            <option value="setter">Setter</option>
                            <option value="libero">Libero</option>
                            <option value="server">Server</option>
                        </select>
                    </div>
                    <div class="input-box">
                        <input type="number" placeholder="Jersey Number" id="jersey_number" name="jersey_number" required>
                    </div>
                    <div class="input-box">
                        <select id="team_id" name="team_id" required>
                            <option value="" disabled selected>Select Team</option>
                            <!-- Dynamically populate teams -->

                        </select>
                    </div>
        
                    <button type="submit" class="btn" id="submit">Add Player</button>
                </form>
            </div>
        </div>
        
        

       
    </main>


    <footer>
        <p>&copy; 2024 Ashesi Volleyball Management</p>
    </footer>

    <script src="../assets/js/coach_view.js"></script>
</body>
</html>
</html>