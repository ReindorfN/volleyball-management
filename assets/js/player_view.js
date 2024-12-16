document.addEventListener("DOMContentLoaded", () => {
    // Profile Modal Elements
    const profileModal = document.getElementById('profile-modal');
    const profileBtn = document.getElementById('profile-btn');
    const closeBtn = profileModal.querySelector('.close');
    const cancelBtn = profileModal.querySelector('.cancel-btn');
    const profileForm = document.getElementById('profile-form');
    const profilePicInput = document.getElementById('profile-pic');
    const profilePreview = document.getElementById('profile-preview');

    // Profile Modal Functions
    function openProfileModal() {
        profileModal.style.display = 'block';
    }

    function closeProfileModal() {
        profileModal.style.display = 'none';
    }

    // Event Listeners for Profile Modal
    profileBtn.addEventListener('click', openProfileModal);
    closeBtn.addEventListener('click', closeProfileModal);
    cancelBtn.addEventListener('click', closeProfileModal);

    // Close modal on outside click
    window.addEventListener('click', (e) => {
        if (e.target === profileModal) {
            closeProfileModal();
        }
    });
});
