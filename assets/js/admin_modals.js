document.addEventListener('DOMContentLoaded', function() {
    // Modal Elements
    const modals = document.querySelectorAll('.modal');
    const closeButtons = document.querySelectorAll('.close');
    
    // Form Elements
    const addUserForm = document.getElementById('add-user-form');
    const announcementForm = document.getElementById('announcement-form');

    // Close Modal Function
    function closeModal(modal) {
        modal.style.display = 'none';
        // Reset form if exists
        const form = modal.querySelector('form');
        if (form) {
            form.reset();
            // Remove any error messages
            const errorMessages = form.querySelectorAll('.error-message');
            errorMessages.forEach(msg => msg.remove());
            // Remove error states
            const errorFields = form.querySelectorAll('.error');
            errorFields.forEach(field => field.classList.remove('error'));
        }
    }

    // Close modal when clicking close button
    closeButtons.forEach(button => {
        button.addEventListener('click', () => {
            const modal = button.closest('.modal');
            closeModal(modal);
        });
    });

    // Close modal when clicking outside
    window.addEventListener('click', (e) => {
        modals.forEach(modal => {
            if (e.target === modal) {
                closeModal(modal);
            }
        });
    });

    // Form Validation
    function validateForm(form) {
        let isValid = true;
        const inputs = form.querySelectorAll('input, select, textarea');
        
        inputs.forEach(input => {
            const formGroup = input.closest('.form-group');
            const errorMessage = formGroup.querySelector('.error-message');
            
            if (errorMessage) {
                errorMessage.remove();
            }
            
            formGroup.classList.remove('error');
            
            if (input.hasAttribute('required') && !input.value.trim()) {
                isValid = false;
                formGroup.classList.add('error');
                const error = document.createElement('div');
                error.className = 'error-message';
                error.textContent = 'This field is required';
                formGroup.appendChild(error);
            }
            
            if (input.type === 'email' && input.value) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(input.value)) {
                    isValid = false;
                    formGroup.classList.add('error');
                    const error = document.createElement('div');
                    error.className = 'error-message';
                    error.textContent = 'Please enter a valid email address';
                    formGroup.appendChild(error);
                }
            }
        });
        
        return isValid;
    }

    // Add User Form Submit
    if (addUserForm) {
        addUserForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            if (!validateForm(addUserForm)) {
                return;
            }
            
            const formData = new FormData(addUserForm);
            addUserForm.classList.add('loading');
            
            try {
                const response = await fetch('../actions/add_user.php', {
                    method: 'POST',
                    body: JSON.stringify(Object.fromEntries(formData)),
                    headers: {
                        'Content-Type': 'application/json'
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showSuccessMessage(addUserForm, 'User added successfully!');
                    setTimeout(() => {
                        closeModal(addUserForm.closest('.modal'));
                        // Refresh users list if it exists
                        if (typeof loadUsers === 'function') {
                            loadUsers();
                        }
                    }, 1500);
                } else {
                    throw new Error(data.error || 'Failed to add user');
                }
            } catch (error) {
                showErrorMessage(addUserForm, error.message);
            } finally {
                addUserForm.classList.remove('loading');
            }
        });
    }

    // Announcement Form Submit
    if (announcementForm) {
        announcementForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            if (!validateForm(announcementForm)) {
                return;
            }
            
            const formData = new FormData(announcementForm);
            announcementForm.classList.add('loading');
            
            try {
                const response = await fetch('../actions/create_notification.php', {
                    method: 'POST',
                    body: JSON.stringify({
                        type: 'GENERAL',
                        title: formData.get('title'),
                        message: formData.get('message')
                    }),
                    headers: {
                        'Content-Type': 'application/json'
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showSuccessMessage(announcementForm, 'Announcement created successfully!');
                    setTimeout(() => {
                        closeModal(announcementForm.closest('.modal'));
                        // Refresh announcements if function exists
                        if (typeof loadAnnouncements === 'function') {
                            loadAnnouncements();
                        }
                    }, 1500);
                } else {
                    throw new Error(data.error || 'Failed to create announcement');
                }
            } catch (error) {
                showErrorMessage(announcementForm, error.message);
            } finally {
                announcementForm.classList.remove('loading');
            }
        });
    }

    // Utility Functions
    function showSuccessMessage(form, message) {
        const successDiv = document.createElement('div');
        successDiv.className = 'success-message';
        successDiv.textContent = message;
        form.insertBefore(successDiv, form.firstChild);
        successDiv.style.display = 'block';
    }

    function showErrorMessage(form, message) {
        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-message';
        errorDiv.style.marginBottom = '1rem';
        errorDiv.textContent = message;
        form.insertBefore(errorDiv, form.firstChild);
    }
}); 