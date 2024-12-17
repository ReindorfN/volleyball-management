<!-- Add User Modal -->
<div id="add-user-modal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Add New User</h2>
        <form id="add-user-form">
            <div class="form-group">
                <label for="firstName">First Name</label>
                <input type="text" id="firstName" name="firstName" required>
            </div>
            <div class="form-group">
                <label for="lastName">Last Name</label>
                <input type="text" id="lastName" name="lastName" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <label for="role">Role</label>
                <select id="role" name="role" required>
                    <option value="coach">Coach</option>
                    <option value="organizer">Organizer</option>
                </select>
            </div>
            <button type="submit">Add User</button>
        </form>
    </div>
</div>

<!-- Announcement Modal -->
<div id="announcement-modal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Create Announcement</h2>
        <form id="announcement-form">
            <div class="form-group">
                <label for="title">Title</label>
                <input type="text" id="title" name="title" required>
            </div>
            <div class="form-group">
                <label for="message">Message</label>
                <textarea id="message" name="message" required></textarea>
            </div>
            <button type="submit">Create Announcement</button>
        </form>
    </div>
</div>

<!-- Add Team Modal -->
<div id="add-team-modal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Add New Team</h2>
        <form id="add-team-form">
            <div class="form-group">
                <label for="teamName">Team Name</label>
                <input type="text" id="teamName" name="teamName" required>
            </div>
            <div class="form-group">
                <label for="coachId">Select Coach</label>
                <select id="coachId" name="coachId" required>
                    <option value="">Select a coach...</option>
                    <!-- Coaches will be loaded dynamically -->
                </select>
            </div>
            <button type="submit">Add Team</button>
        </form>
    </div>
</div>