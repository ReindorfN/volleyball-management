<?php
session_start();

// Redirect if not authenticated as admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../views/login.html");
    exit();
}

require "../db/db_connection.php";
$database = new Database();
$conn = $database->getConnection();

// Fetch admin data
$adminData = [];
$adminQuery = "SELECT * FROM v_ball_users WHERE user_id = ?";
$stmt = $conn->prepare($adminQuery);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$adminData = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/admin_view.css">
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2>Admin Panel</h2>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li class="active" data-tab="dashboard">
                        <span class="icon">📊</span>Dashboard
                    </li>
                    <li data-tab="teams">
                        <span class="icon">👥</span>Teams
                    </li>
                    <li data-tab="players">
                        <span class="icon">🏃</span>Players
                    </li>
                    <li data-tab="matches">
                        <span class="icon">🏐</span>Matches
                    </li>
                    <li data-tab="users">
                        <span class="icon">👤</span>Users
                    </li>
                    <li data-tab="announcements">
                        <span class="icon">📢</span>Announcements
                    </li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Header -->
            <header class="main-header">
                <div class="header-search">
                    <input type="text" placeholder="Search...">
                </div>
                <div class="header-user">
                    <span class="user-name"><?= htmlspecialchars($adminData['first_name'] . ' ' . $adminData['last_name']) ?></span>
                    <button id="profile-btn" class="profile-icon">
                        <img src="<?= htmlspecialchars($adminData['profile_pic'] ?? '../assets/images/default-avatar.png') ?>" 
                             alt="Profile" class="profile-img">
                    </button>
                    <a href="../actions/logout.php" class="logout-btn">Logout</a>
                </div>
            </header>

            <!-- Content Sections -->
            <div class="content-container">
                <!-- Dashboard Section -->
                <section id="dashboard" class="content-section active">
                    <h2>Dashboard Overview</h2>
                    <div class="stats-grid">
                        <div class="stat-card">
                            <h3>Total Teams</h3>
                            <p class="stat-number" id="total-teams">Loading...</p>
                        </div>
                        <div class="stat-card">
                            <h3>Active Players</h3>
                            <p class="stat-number" id="active-players">Loading...</p>
                        </div>
                        <div class="stat-card">
                            <h3>Upcoming Matches</h3>
                            <p class="stat-number" id="upcoming-matches">Loading...</p>
                        </div>
                        <div class="stat-card">
                            <h3>Total Users</h3>
                            <p class="stat-number" id="total-users">Loading...</p>
                        </div>
                    </div>
                    <div class="recent-activity">
                        <h3>Recent Activity</h3>
                        <div class="activity-list" id="activity-list">
                            <!-- Activity items will be loaded here -->
                        </div>
                    </div>
                </section>

                <!-- Teams Section -->
                <section id="teams" class="content-section">
                    <div class="section-header">
                        <h2>Manage Teams</h2>
                        <button class="add-btn" id="add-team-btn">Add New Team</button>
                    </div>
                    <div class="teams-list" id="teams-list">
                        <!-- Teams will be loaded here -->
                    </div>
                </section>

                <!-- Players Section -->
                <section id="players" class="content-section">
                    <div class="section-header">
                        <h2>Manage Players</h2>
                        <button class="add-btn" id="add-player-btn">Add New Player</button>
                    </div>
                    <div class="players-list" id="players-list">
                        <!-- Players will be loaded here -->
                    </div>
                </section>

                <!-- Matches Section -->
                <section id="matches" class="content-section">
                    <div class="section-header">
                        <h2>Manage Matches</h2>
                        <button class="add-btn" id="schedule-match-btn">Schedule Match</button>
                    </div>
                    <div class="matches-list" id="matches-list">
                        <!-- Matches will be loaded here -->
                    </div>
                </section>

                <!-- Users Section -->
                <section id="users" class="content-section">
                    <div class="section-header">
                        <h2>Manage Users</h2>
                        <button class="add-btn" id="add-user-btn">Add New User</button>
                    </div>
                    <div class="users-list" id="users-list">
                        <!-- Users will be loaded here -->
                    </div>
                </section>

                <!-- Announcements Section -->
                <section id="announcements" class="content-section">
                    <div class="section-header">
                        <h2>Manage Announcements</h2>
                        <button class="add-btn" id="add-announcement-btn">New Announcement</button>
                    </div>
                    <div class="announcements-list" id="announcements-list">
                        <!-- Announcements will be loaded here -->
                    </div>
                </section>
            </div>
        </main>
    </div>

    <!-- Modals will be added here -->
    <?php include 'admin_modals.php'; ?>

    <script src="../assets/js/admin_view.js"></script>
</body>
</html>
<?php
$conn->close();
?>