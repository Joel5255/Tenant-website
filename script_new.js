// Global variables
let currentUser = null;
let isAuthenticated = false;

// Show/hide screens
function showScreen(screenId) {
    const screens = document.querySelectorAll('.screen');
    screens.forEach(screen => {
        screen.classList.remove('active');
    });
    
    const targetScreen = document.getElementById(screenId);
    if (targetScreen) {
        targetScreen.classList.add('active');
        
        // Load profile data when profile screen is shown
        if (screenId === 'profile-screen') {
            loadProfileData();
        }
    }
}

// Show login screen
function showLoginScreen() {
    showScreen('login-screen');
}

// Show signup screen
function showSignupScreen() {
    showScreen('signup-screen');
}

// Get current user from localStorage
function getCurrentUser() {
    const savedUser = localStorage.getItem('currentUser');
    if (savedUser) {
        return JSON.parse(savedUser);
    }
    return null;
}

// Show notification
function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = 'notification ' + type;
    notification.textContent = message;
    
    // Add to page
    document.body.appendChild(notification);
    
    // Remove after 3 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.parentNode.removeChild(notification);
        }
    }, 3000);
}

// Handle login
function handleLogin() {
    console.log('Login function called');
    
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    
    if (!email || !password) {
        showNotification('Please enter email and password', 'error');
        return;
    }
    
    // Call login API
    fetch('/api/login.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            email: email,
            password: password
        })
    })
    .then(response => response.json())
    .then(data => {
        console.log('Login response:', data);
        
        if (data.success && data.user) {
            currentUser = data.user;
            localStorage.setItem('currentUser', JSON.stringify(data.user));
            isAuthenticated = true;
            
            showNotification('Login successful!');
            showScreen('home-screen');
        } else {
            showNotification(data.error || 'Login failed', 'error');
        }
    })
    .catch(error => {
        console.error('Login error:', error);
        showNotification('Login failed. Please try again.', 'error');
    });
}

// Handle signup
function handleSignup() {
    console.log('Signup function called');
    
    const fullname = document.getElementById('fullname').value;
    const email = document.getElementById('signup-email').value;
    const password = document.getElementById('signup-password').value;
    const confirmPassword = document.getElementById('confirm-password').value;
    const termsAccepted = document.getElementById('terms').checked;
    
    // Validation
    if (!fullname || !email || !password || !confirmPassword) {
        showNotification('Please fill in all fields', 'error');
        return;
    }
    
    if (password !== confirmPassword) {
        showNotification('Passwords do not match', 'error');
        return;
    }
    
    if (!termsAccepted) {
        showNotification('Please accept terms and conditions', 'error');
        return;
    }
    
    // Call signup API
    fetch('/api/register.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            name: fullname,
            email: email,
            password: password
        })
    })
    .then(response => response.json())
    .then(data => {
        console.log('Signup response:', data);
        
        if (data.success) {
            showNotification('Account created successfully!');
            showLoginScreen();
        } else {
            showNotification(data.error || 'Signup failed', 'error');
        }
    })
    .catch(error => {
        console.error('Signup error:', error);
        showNotification('Signup failed. Please try again.', 'error');
    });
}

// Initialize when page loads
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, setting up buttons');
    
    // Setup buttons with direct onclick
    const loginBtn = document.querySelector('#login-screen .login-btn');
    if (loginBtn) {
        loginBtn.onclick = handleLogin;
        console.log('Login button setup complete');
    }
    
    const registerBtn = document.querySelector('#login-screen .register-btn');
    if (registerBtn) {
        registerBtn.onclick = showSignupScreen;
        console.log('Register button setup complete');
    }
    
    const createAccountBtn = document.querySelector('#signup-screen .login-btn');
    if (createAccountBtn) {
        createAccountBtn.onclick = handleSignup;
        console.log('Create Account button setup complete');
    }
    
    // Check auth status
    const savedUser = getCurrentUser();
    if (savedUser) {
        currentUser = savedUser;
        isAuthenticated = true;
        showScreen('home-screen');
    } else {
        showScreen('login-screen');
    }
});

// Profile functions
function updateProfile() {
    const user = getCurrentUser();
    if (!user) {
        showNotification('Please login first', 'error');
        return;
    }
    
    const phoneNumber = document.getElementById('profile-phone').value;
    
    if (!phoneNumber) {
        showNotification('Please enter phone number', 'error');
        return;
    }
    
    fetch('/api/update_profile.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            user_id: user.id,
            phone_number: phoneNumber
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Profile updated successfully!');
            user.phone_number = phoneNumber;
            localStorage.setItem('currentUser', JSON.stringify(user));
        } else {
            showNotification('Profile update failed: ' + data.error, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Profile update failed', 'error');
    });
}

function loadProfileData() {
    const user = getCurrentUser();
    if (!user) return;
    
    // Load profile data
    document.getElementById('profile-name').value = user.name || '';
    document.getElementById('profile-email').value = user.email || '';
    document.getElementById('profile-phone').value = user.phone_number || '';
}
