// Authentication state management
let isAuthenticated = false;
let currentUser = null;

// Check authentication status on page load
function checkAuthStatus() {
    const savedUser = localStorage.getItem('currentUser');
    if (savedUser) {
        currentUser = JSON.parse(savedUser);
        isAuthenticated = true;
        showAuthenticatedApp();
    } else {
        showLoginScreen();
    }
}

// Show login screen
function showLoginScreen() {
    const screens = document.querySelectorAll('.screen');
    screens.forEach(screen => {
        screen.classList.remove('active');
    });
    document.getElementById('login-screen').classList.add('active');
    updateNavigationForAuth(false);
}

// Show signup screen
function showSignupScreen() {
    const screens = document.querySelectorAll('.screen');
    screens.forEach(screen => {
        screen.classList.remove('active');
    });
    document.getElementById('signup-screen').classList.add('active');
}

// Show authenticated app
function showAuthenticatedApp() {
    const screens = document.querySelectorAll('.screen');
    screens.forEach(screen => {
        screen.classList.remove('active');
    });
    document.getElementById('home-screen').classList.add('active');
    updateNavigationForAuth(true);
    updateUserInfo();
    
    // Update daily spending status after authentication
    if (dailyTarget > 0) {
        updateDailySpendingStatus();
    }
}

// Update navigation based on auth status
function updateNavigationForAuth(authenticated) {
    const navItems = document.querySelectorAll('.nav-item');
    if (authenticated) {
        // Show all navigation items
        navItems.forEach(item => {
            item.style.display = 'flex';
        });
    } else {
        // Hide navigation items when not authenticated
        navItems.forEach(item => {
            item.style.display = 'none';
        });
    }
}

// Update user information in the UI
function updateUserInfo() {
    if (currentUser) {
        // Update welcome message
        const welcomeTitle = document.querySelector('.welcome-section h1');
        if (welcomeTitle) {
            welcomeTitle.textContent = `Welcome back, ${currentUser.name}!`;
        }
        
        // Update profile information
        const profileName = document.querySelector('.profile-header h1');
        if (profileName) {
            profileName.textContent = currentUser.name;
        }
        
        const profileEmail = document.querySelector('.profile-header p');
        if (profileEmail) {
            profileEmail.textContent = currentUser.email;
        }
    }
}

// Handle login form submission
function handleLogin() {
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    const rememberCheckbox = document.getElementById('remember');
    const remember = rememberCheckbox ? rememberCheckbox.checked : false;
    
    // Simple validation
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
            // Save user data from API response
            currentUser = data.user;
            if (remember) {
                localStorage.setItem('currentUser', JSON.stringify(data.user));
            }
            
            isAuthenticated = true;
            
            // Show success message
            showNotification('Login successful! Welcome back.');
            
            // Show authenticated app
            setTimeout(() => {
                showAuthenticatedApp();
            }, 1000);
        } else {
            console.error('Login failed:', data.error);
            showNotification(data.error || 'Login failed', 'error');
        }
    })
    .catch(error => {
        console.error('Login error:', error);
        showNotification('Login failed. Please try again.', 'error');
    });
}

// Handle signup form submission
function handleSignup() {
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
        showNotification('Please accept the terms and conditions', 'error');
        return;
    }
    
    // Call registration API
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
        if (data.success) {
            // Save user data from API response
            currentUser = data.user;
            localStorage.setItem('currentUser', JSON.stringify(data.user));
            
            isAuthenticated = true;
            
            // Show success message
            showNotification('Account created successfully! Welcome to Financial Literacy.');
            
            // Show authenticated app directly
            setTimeout(() => {
                showAuthenticatedApp();
            }, 1500);
        } else {
            showNotification(data.error || 'Registration failed', 'error');
        }
    })
    .catch(error => {
        console.error('Registration error:', error);
        showNotification('Registration failed. Please try again.', 'error');
    });
}

// Handle logout
function handleLogout() {
    // Clear authentication data
    currentUser = null;
    isAuthenticated = false;
    localStorage.removeItem('currentUser');
    
    // Show notification
    showNotification('You have been logged out successfully.');
    
    // Show login screen
    setTimeout(() => {
        showLoginScreen();
    }, 1000);
}

// Navigation between screens (for authenticated users)
function showScreen(screenId) {
    // Only allow navigation if authenticated
    if (!isAuthenticated) {
        showNotification('Please login to access this feature', 'error');
        return;
    }
    
    // Hide all screens
    const screens = document.querySelectorAll('.screen');
    screens.forEach(screen => {
        screen.classList.remove('active');
    });
    
    // Show the selected screen
    const targetScreen = document.getElementById(screenId);
    if (targetScreen) {
        targetScreen.classList.add('active');
        
        // Load profile data when profile screen is shown
        if (screenId === 'profile-screen') {
            loadProfileData();
        }
    }
    
    // Update navigation active state
    updateNavigation(screenId);
}

// Update navigation active state
function updateNavigation(activeScreenId) {
    const navItems = document.querySelectorAll('.nav-item');
    navItems.forEach(item => {
        item.classList.remove('active');
    });
    
    // Map screen IDs to navigation indices
    const screenToNavIndex = {
        'home-screen': 0,
        'expense-screen': 1,
        'quiz-screen': 2,
        'report-screen': 3,
        'profile-screen': 4
    };
    
    const activeIndex = screenToNavIndex[activeScreenId];
    if (activeIndex !== undefined && navItems[activeIndex]) {
        navItems[activeIndex].classList.add('active');
    }
}

// Quiz functionality
let currentQuestion = 2;
let totalQuestions = 5;
let quizAnswers = [];

function selectAnswer(button) {
    // Remove selected class from all answers
    const answerButtons = button.parentElement.querySelectorAll('.answer-btn');
    answerButtons.forEach(btn => btn.classList.remove('selected'));
    
    // Add selected class to clicked button
    button.classList.add('selected');
    
    // Store answer
    const questionIndex = currentQuestion - 1;
    quizAnswers[questionIndex] = button.textContent;
}

function nextQuestion() {
    if (currentQuestion < totalQuestions) {
        currentQuestion++;
        updateQuizProgress();
        loadQuestion(currentQuestion);
    } else {
        showQuizResults();
    }
}

function previousQuestion() {
    if (currentQuestion > 1) {
        currentQuestion--;
        updateQuizProgress();
        loadQuestion(currentQuestion);
    }
}

function updateQuizProgress() {
    const progressFill = document.querySelector('.progress-fill');
    const progressText = document.querySelector('.quiz-progress span');
    const progress = (currentQuestion / totalQuestions) * 100;
    
    progressFill.style.width = progress + '%';
    progressText.textContent = `Question ${currentQuestion} of ${totalQuestions}`;
}

function loadQuestion(questionNumber) {
    // Sample questions - in a real app, these would come from a database
    const questions = [
        {
            question: "What is the recommended percentage of income to save?",
            answers: ["10%", "20%", "30%", "40%"]
        },
        {
            question: "What is a credit score?",
            answers: ["Your age", "Your income", "Your creditworthiness", "Your savings"]
        },
        {
            question: "What is compound interest?",
            answers: ["Interest on principal only", "Interest on principal and accumulated interest", "A type of loan", "A bank fee"]
        },
        {
            question: "What is a budget?",
            answers: ["A spending plan", "A savings account", "A type of investment", "A credit card"]
        },
        {
            question: "What is diversification?",
            answers: ["Putting all money in one investment", "Spreading investments across different types", "Buying only stocks", "Saving money only"]
        }
    ];
    
    const questionData = questions[questionNumber - 1];
    if (questionData) {
        document.querySelector('.question-card h3').textContent = questionData.question;
        
        const answerButtons = document.querySelectorAll('.answer-btn');
        answerButtons.forEach((btn, index) => {
            btn.textContent = questionData.answers[index];
            btn.classList.remove('selected');
        });
    }
}

function showQuizResults() {
    // Calculate score (simplified - in real app would compare with correct answers)
    const score = Math.floor(Math.random() * 30) + 70; // Random score between 70-100
    
    // Create results screen
    const resultsHTML = `
        <div class="quiz-results">
            <h1>Quiz Completed!</h1>
            <div class="score-circle">
                <div class="score-value">${score}%</div>
                <div class="score-label">Your Score</div>
            </div>
            <div class="results-message">
                ${score >= 80 ? 'Excellent work!' : score >= 60 ? 'Good job!' : 'Keep practicing!'}
            </div>
            <div class="results-stats">
                <div class="stat">
                    <div class="stat-value">${Math.floor(score * 0.05)}/${totalQuestions}</div>
                    <div class="stat-label">Correct Answers</div>
                </div>
                <div class="stat">
                    <div class="stat-value">5 min</div>
                    <div class="stat-label">Time Taken</div>
                </div>
            </div>
            <button class="action-btn" onclick="showScreen('home-screen')">
                <span class="btn-icon">🏠</span>
                <span>Back to Home</span>
            </button>
        </div>
    `;
    
    document.getElementById('quiz-screen').innerHTML = resultsHTML;
}

// Daily Spending Target Management
let dailyTarget = 0;
let expenses = [];
let spendingLockEnabled = false;
let cooldownEnabled = false;
let emergencyOverrideEnabled = false;
let lastTransactionTime = null;

// Load daily target and expenses from localStorage
function loadDailyTargetData() {
    const savedTarget = localStorage.getItem('dailyTarget');
    const savedExpenses = localStorage.getItem('expenses');
    const savedControls = localStorage.getItem('spendingControls');
    const savedLastTransaction = localStorage.getItem('lastTransactionTime');
    
    if (savedTarget) {
        dailyTarget = parseFloat(savedTarget);
        document.getElementById('daily-target').value = dailyTarget;
        showSpendingProgress();
    }
    
    if (savedExpenses) {
        expenses = JSON.parse(savedExpenses);
    }
    
    if (savedControls) {
        const controls = JSON.parse(savedControls);
        spendingLockEnabled = controls.spendingLock || false;
        cooldownEnabled = controls.cooldown || false;
        emergencyOverrideEnabled = controls.emergencyOverride || false;
        
        // Update UI
        document.getElementById('spending-lock').checked = spendingLockEnabled;
        document.getElementById('cooldown-period').checked = cooldownEnabled;
        document.getElementById('emergency-override').checked = emergencyOverrideEnabled;
    }
    
    if (savedLastTransaction) {
        lastTransactionTime = parseInt(savedLastTransaction);
    }
}

// Save spending controls to localStorage
function saveSpendingControls() {
    const controls = {
        spendingLock: spendingLockEnabled,
        cooldown: cooldownEnabled,
        emergencyOverride: emergencyOverrideEnabled
    };
    localStorage.setItem('spendingControls', JSON.stringify(controls));
}

// Toggle spending controls
function toggleSpendingLock() {
    spendingLockEnabled = document.getElementById('spending-lock').checked;
    saveSpendingControls();
    showNotification(spendingLockEnabled ? 'Spending lock enabled' : 'Spending lock disabled');
}

function toggleCooldown() {
    cooldownEnabled = document.getElementById('cooldown-period').checked;
    saveSpendingControls();
    showNotification(cooldownEnabled ? '30-minute cooldown enabled' : 'Cooldown disabled');
}

function toggleEmergencyOverride() {
    emergencyOverrideEnabled = document.getElementById('emergency-override').checked;
    saveSpendingControls();
    showNotification(emergencyOverrideEnabled ? 'Emergency override enabled' : 'Emergency override disabled');
}

// Check if user is in cooldown period
function isInCooldown() {
    if (!cooldownEnabled || !lastTransactionTime) {
        return false;
    }
    
    const now = new Date().getTime();
    const timeDiff = now - lastTransactionTime;
    const cooldownPeriod = 30 * 60 * 1000; // 30 minutes in milliseconds
    
    return timeDiff < cooldownPeriod;
}

// Get remaining cooldown time
function getCooldownRemaining() {
    if (!lastTransactionTime) return 0;
    
    const now = new Date().getTime();
    const timeDiff = lastTransactionTime + (30 * 60 * 1000) - now;
    return Math.max(0, timeDiff);
}

// Check if spending should be locked
function isSpendingLocked() {
    if (!spendingLockEnabled) return false;
    
    const todaySpending = getTodaySpending();
    return todaySpending >= dailyTarget;
}

// Show spending lock modal
function showSpendingLock() {
    const modal = document.getElementById('spending-lock-modal');
    const targetAmount = document.getElementById('lock-target-amount');
    const emergencyBtn = document.getElementById('emergency-override-btn');
    
    targetAmount.textContent = `$${dailyTarget.toFixed(2)}`;
    
    if (emergencyOverrideEnabled) {
        emergencyBtn.style.display = 'block';
    } else {
        emergencyBtn.style.display = 'none';
    }
    
    modal.style.display = 'flex';
}

// Close spending lock modal
function closeSpendingLock() {
    document.getElementById('spending-lock-modal').style.display = 'none';
}

// Show emergency confirmation modal
function showEmergencyConfirmation() {
    document.getElementById('spending-lock-modal').style.display = 'none';
    document.getElementById('emergency-modal').style.display = 'flex';
}

// Close emergency modal
function closeEmergencyModal() {
    document.getElementById('emergency-modal').style.display = 'none';
    document.getElementById('emergency-reason').value = '';
}

// Confirm emergency override
function confirmEmergencyOverride() {
    const reason = document.getElementById('emergency-reason').value.trim();
    
    if (!reason) {
        showNotification('Please provide a reason for the emergency override', 'error');
        return;
    }
    
    // Log emergency override
    const emergencyLog = {
        timestamp: new Date().toISOString(),
        reason: reason,
        dailySpending: getTodaySpending(),
        dailyTarget: dailyTarget
    };
    
    // Save to emergency log
    let emergencyLogs = JSON.parse(localStorage.getItem('emergencyLogs') || '[]');
    emergencyLogs.push(emergencyLog);
    localStorage.setItem('emergencyLogs', JSON.stringify(emergencyLogs));
    
    showNotification('Emergency override confirmed. Please use this feature only for true emergencies.', 'error');
    closeEmergencyModal();
    
    // Allow the transaction to proceed
    return true;
}

// Check if transaction can proceed
function canProceedWithTransaction(amount) {
    // Check spending lock
    if (isSpendingLocked()) {
        showSpendingLock();
        return false;
    }
    
    // Check cooldown
    if (isInCooldown()) {
        const remainingMinutes = Math.ceil(getCooldownRemaining() / (60 * 1000));
        showNotification(`Please wait ${remainingMinutes} minutes before making another transaction.`, 'error');
        return false;
    }
    
    // Check if transaction would exceed target (if lock is enabled)
    if (spendingLockEnabled) {
        const todaySpending = getTodaySpending();
        const projectedSpending = todaySpending + parseFloat(amount);
        
        if (projectedSpending > dailyTarget) {
            showSpendingLock();
            return false;
        }
    }
    
    return true;
}

// Set daily spending target
function setDailyTarget() {
    const targetInput = document.getElementById('daily-target');
    const targetValue = parseFloat(targetInput.value);
    
    if (targetValue && targetValue > 0) {
        dailyTarget = targetValue;
        localStorage.setItem('dailyTarget', dailyTarget.toString());
        
        showNotification(`Daily target set to $${dailyTarget.toFixed(2)}`);
        showSpendingProgress();
        showSpendingControls();
        updateDailySpendingStatus();
    } else {
        showNotification('Please enter a valid target amount', 'error');
    }
}

// Show spending controls section
function showSpendingControls() {
    if (dailyTarget > 0) {
        const controlsSection = document.getElementById('spending-controls');
        controlsSection.style.display = 'block';
    }
}

// Get today's date string in YYYY-MM-DD format
function getTodayDateString() {
    return new Date().toISOString().split('T')[0];
}

// Get today's expenses
function getTodayExpenses() {
    const today = getTodayDateString();
    return expenses.filter(expense => expense.date === today);
}

// Calculate today's total spending
function getTodaySpending() {
    const todayExpenses = getTodayExpenses();
    return todayExpenses.reduce((total, expense) => total + parseFloat(expense.amount), 0);
}

// Show spending progress section
function showSpendingProgress() {
    if (dailyTarget > 0) {
        const progressSection = document.getElementById('spending-progress');
        progressSection.style.display = 'block';
        updateSpendingProgress();
    }
}

// Update spending progress display
function updateSpendingProgress() {
    const todaySpending = getTodaySpending();
    const progressFill = document.getElementById('daily-progress-fill');
    const progressText = document.getElementById('progress-text');
    const progressStatus = document.getElementById('progress-status');
    
    const percentage = Math.min((todaySpending / dailyTarget) * 100, 100);
    
    progressFill.style.width = percentage + '%';
    progressText.textContent = `$${todaySpending.toFixed(2)} / $${dailyTarget.toFixed(2)}`;
    
    // Update progress bar color and status based on percentage
    progressFill.className = 'progress-bar-fill';
    progressStatus.className = 'progress-status';
    
    if (percentage >= 100) {
        progressFill.classList.add('danger');
        progressStatus.classList.add('danger');
        progressStatus.textContent = 'Target Exceeded!';
    } else if (percentage >= 80) {
        progressFill.classList.add('warning');
        progressStatus.classList.add('warning');
        progressStatus.textContent = 'Warning: Close to Limit';
    } else {
        progressStatus.textContent = 'On Track';
    }
}

// Check if spending exceeds target and show alert
function checkSpendingAlert(newExpenseAmount) {
    const todaySpending = getTodaySpending();
    const projectedSpending = todaySpending + parseFloat(newExpenseAmount);
    
    if (projectedSpending > dailyTarget) {
        const overAmount = projectedSpending - dailyTarget;
        showNotification(
            `⚠️ Alert: This expense will put you $${overAmount.toFixed(2)} over your daily target of $${dailyTarget.toFixed(2)}!`,
            'error'
        );
    } else if (projectedSpending >= dailyTarget * 0.8) {
        const remaining = dailyTarget - projectedSpending;
        showNotification(
            `⚠️ Warning: After this expense, you'll have only $${remaining.toFixed(2)} remaining for today.`,
            'info'
        );
    }
}

// Update daily spending status on home screen
function updateDailySpendingStatus() {
    const todaySpending = getTodaySpending();
    
    if (dailyTarget > 0) {
        const percentage = (todaySpending / dailyTarget) * 100;
        const remaining = dailyTarget - todaySpending;
        
        // Find or create daily spending stat card
        let dailyStatCard = document.querySelector('.daily-spending-stat');
        
        if (!dailyStatCard) {
            // Add new stat card for daily spending
            const statsGrid = document.querySelector('.stats-grid');
            const newCard = document.createElement('div');
            newCard.className = 'stat-card daily-spending-stat';
            newCard.innerHTML = `
                <div class="stat-icon">🎯</div>
                <div class="stat-content">
                    <div class="stat-value">$${todaySpending.toFixed(2)}</div>
                    <div class="stat-label">Daily Spending</div>
                </div>
            `;
            statsGrid.appendChild(newCard);
        } else {
            dailyStatCard.querySelector('.stat-value').textContent = `$${todaySpending.toFixed(2)}`;
        }
    }
}

// Expense form functionality
function handleExpenseSubmit(event) {
    event.preventDefault();
    
    const amount = document.getElementById('amount').value;
    const category = document.getElementById('category').value;
    const date = document.getElementById('date').value || getTodayDateString();
    
    if (amount && category) {
        // Check if transaction can proceed based on spending controls
        if (!canProceedWithTransaction(amount)) {
            return; // Transaction blocked by spending controls
        }
        
        // Check for spending alerts before adding expense
        if (dailyTarget > 0) {
            checkSpendingAlert(amount);
        }
        
        // Create expense object
        const expense = {
            id: Date.now(),
            amount: parseFloat(amount),
            category: category,
            date: date,
            timestamp: new Date().toISOString()
        };
        
        // Add to expenses array
        expenses.push(expense);
        
        // Save to localStorage
        localStorage.setItem('expenses', JSON.stringify(expenses));
        
        // Update last transaction time for cooldown
        lastTransactionTime = new Date().getTime();
        localStorage.setItem('lastTransactionTime', lastTransactionTime.toString());
        
        console.log('Expense logged:', expense);
        
        // Update spending progress if target is set
        if (dailyTarget > 0) {
            updateSpendingProgress();
            updateDailySpendingStatus();
        }
        
        // Show success message
        showNotification('Expense logged successfully!');
        
        // Reset form
        event.target.reset();
        
        // Reset date to today
        const dateInput = document.getElementById('date');
        if (dateInput) {
            dateInput.valueAsDate = new Date();
        }
        
        // Go back to home after a delay
        setTimeout(() => {
            showScreen('home-screen');
        }, 1500);
    }
}

// Notification system
function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = 'notification';
    notification.textContent = message;
    
    // Set color based on type
    let bgColor = 'linear-gradient(135deg, #1e3c72, #2a5298)';
    if (type === 'error') {
        bgColor = 'linear-gradient(135deg, #dc3545, #c82333)';
    } else if (type === 'info') {
        bgColor = 'linear-gradient(135deg, #17a2b8, #138496)';
    }
    
    notification.style.cssText = `
        position: fixed;
        top: 80px;
        left: 50%;
        transform: translateX(-50%);
        background: ${bgColor};
        color: white;
        padding: 16px 24px;
        border-radius: 12px;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
        z-index: 1000;
        animation: slideDown 0.3s ease-out;
        max-width: 400px;
        text-align: center;
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.animation = 'slideUp 0.3s ease-out';
        setTimeout(() => {
            if (document.body.contains(notification)) {
                document.body.removeChild(notification);
            }
        }, 300);
    }, 3000);
}

// Add slide animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateX(-50%) translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateX(-50%) translateY(0);
        }
    }
    
    @keyframes slideUp {
        from {
            opacity: 1;
            transform: translateX(-50%) translateY(0);
        }
        to {
            opacity: 0;
            transform: translateX(-50%) translateY(-20px);
        }
    }
    
    .quiz-results {
        text-align: center;
        padding: 24px;
    }
    
    .score-circle {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea, #764ba2);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        margin: 32px auto;
        color: white;
    }
    
    .score-value {
        font-size: 32px;
        font-weight: 700;
    }
    
    .score-label {
        font-size: 12px;
        opacity: 0.9;
    }
    
    .results-message {
        font-size: 18px;
        font-weight: 600;
        color: #333;
        margin-bottom: 32px;
    }
    
    .results-stats {
        display: flex;
        justify-content: center;
        gap: 32px;
        margin-bottom: 32px;
    }
    
    .results-stats .stat {
        text-align: center;
    }
    
    .results-stats .stat-value {
        font-size: 20px;
        font-weight: 700;
        color: #333;
        margin-bottom: 4px;
    }
    
    .results-stats .stat-label {
        font-size: 12px;
        color: #666;
    }
`;
document.head.appendChild(style);

// Initialize the app
document.addEventListener('DOMContentLoaded', function() {
    // Check authentication status first
    checkAuthStatus();
    
    // Load daily target and expense data
    loadDailyTargetData();
    
    // Set today's date as default in expense form
    const dateInput = document.getElementById('date');
    if (dateInput) {
        dateInput.valueAsDate = new Date();
    }
    
    // Button event handlers - completely rewritten
    function setupButtons() {
        // Login button
        const loginBtn = document.querySelector('#login-screen .login-btn');
        if (loginBtn) {
            loginBtn.onclick = function() {
                console.log('Login button clicked');
                handleLogin();
            };
        }
        
        // Register button
        const registerBtn = document.querySelector('#login-screen .register-btn');
        if (registerBtn) {
            registerBtn.onclick = function() {
                console.log('Register button clicked');
                showSignupScreen();
            };
        }
        
        // Create Account button
        const createAccountBtn = document.querySelector('#signup-screen .login-btn');
        if (createAccountBtn) {
            createAccountBtn.onclick = function() {
                console.log('Create Account button clicked');
                handleSignup();
            };
        }
        
        // Show login link
        const showLoginLink = document.getElementById('showLogin');
        if (showLoginLink) {
            showLoginLink.onclick = function(e) {
                console.log('Show login link clicked');
                e.preventDefault();
                showLoginScreen();
            };
        }
    }
    
    // Setup buttons when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', setupButtons);
    } else {
        setupButtons();
    }
    
    // M-Pesa functionality
    loadMpesaData();
    
    // Handle M-Pesa transaction form
    const transactionForm = document.getElementById('transactionForm');
    if (transactionForm) {
        transactionForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const user = getCurrentUser();
            if (!user) {
                showNotification('Please login first', 'error');
                return;
            }
            
            const amount = document.getElementById('mpesaAmount').value;
            const phoneNumber = document.getElementById('mpesaPhone').value;
            
            fetch('/api/mpesa.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'simulate_transaction',
                    user_id: user.id,
                    amount: amount,
                    phone_number: phoneNumber
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('Transaction simulated successfully!');
                    transactionForm.reset();
                    loadTransactions();
                    loadDailyTarget();
                } else {
                    showNotification('Transaction failed: ' + data.error, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Transaction failed', 'error');
            });
        });
    }
    
    // Social login buttons (placeholder functionality)
    const socialButtons = document.querySelectorAll('.social-btn');
    socialButtons.forEach(button => {
        button.addEventListener('click', function() {
            const provider = this.classList.contains('google') ? 'Google' : 'Facebook';
            showNotification(`${provider} login coming soon!`, 'info');
        });
    });
    
    // Forgot password link
    const forgotPasswordLink = document.querySelector('.forgot-password');
    if (forgotPasswordLink) {
        forgotPasswordLink.addEventListener('click', function(e) {
            e.preventDefault();
            showNotification('Password reset coming soon!', 'info');
        });
    }
});

// M-Pesa Functions
function loadMpesaData() {
    loadDailyTarget();
    loadTransactions();
}

function loadDailyTarget() {
    const user = getCurrentUser();
    if (!user) {
        document.getElementById('targetContent').innerHTML = '<p>Please login to view targets</p>';
        return;
    }
    
    fetch('/api/mpesa.php?action=daily_target&user_id=' + user.id)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.target) {
                const target = data.target;
                const percentage = (target.current_spent / target.target_amount) * 100;
                
                let alertHtml = '';
                if (percentage >= 100) {
                    alertHtml = '<div class="alert-warning">⚠️ Daily target reached! SMS alert sent.</div>';
                }
                
                document.getElementById('targetContent').innerHTML = `
                    ${alertHtml}
                    <p><strong>Target:</strong> KES ${target.target_amount}</p>
                    <p><strong>Spent:</strong> KES ${target.current_spent}</p>
                    <p><strong>Progress:</strong> ${percentage.toFixed(1)}%</p>
                    <div class="target-progress">
                        <div class="target-progress-bar" style="width: ${Math.min(percentage, 100)}%"></div>
                    </div>
                `;
            } else {
                createDefaultTarget();
            }
        })
        .catch(error => {
            console.error('Error loading target:', error);
            document.getElementById('targetContent').innerHTML = '<p>Error loading target</p>';
        });
}

function createDefaultTarget() {
    const user = getCurrentUser();
    if (!user) return;
    
    fetch('/api/mpesa.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'create_target',
            user_id: user.id,
            target_amount: 1000
        })
    })
    .then(response => response.json())
    .then(data => {
        loadDailyTarget();
    });
}

function loadTransactions() {
    const user = getCurrentUser();
    if (!user) {
        document.getElementById('transactionList').innerHTML = '<p>Please login to view transactions</p>';
        return;
    }
    
    fetch('/api/mpesa.php?action=transactions&user_id=' + user.id)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayTransactions(data.transactions);
            } else {
                document.getElementById('transactionList').innerHTML = '<p>Error loading transactions</p>';
            }
        })
        .catch(error => {
            console.error('Error loading transactions:', error);
            document.getElementById('transactionList').innerHTML = '<p>Error loading transactions</p>';
        });
}

function displayTransactions(transactions) {
    const listDiv = document.getElementById('transactionList');
    
    if (transactions.length === 0) {
        listDiv.innerHTML = '<p>No transactions yet</p>';
        return;
    }
    
    let html = '';
    transactions.forEach(transaction => {
        const date = new Date(transaction.transaction_time);
        const timeStr = date.toLocaleString();
        
        html += `
            <div class="transaction-item">
                <div>
                    <div><strong>${transaction.transaction_type}</strong></div>
                    <div style="font-size: 12px; color: #666;">${timeStr}</div>
                    <div style="font-size: 12px; color: #666;">${transaction.phone_number}</div>
                </div>
                <div class="amount">KES ${transaction.amount}</div>
            </div>
        `;
    });
    
    listDiv.innerHTML = html;
}

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
            loadProfileData();
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
    
    // Load statistics
    loadProfileStats(user.id);
}

function loadProfileStats(userId) {
    // Load transaction count
    fetch('/api/mpesa.php?action=transactions&user_id=' + userId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('transaction-count').textContent = data.transactions.length;
            }
        });
    
    // Load target count
    fetch('/api/mpesa.php?action=daily_target&user_id=' + userId)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.target) {
                document.getElementById('target-count').textContent = '1';
            } else {
                document.getElementById('target-count').textContent = '0';
            }
        });
}

// Update time display (removed since header was removed)
function updateTime() {
    // Time display functionality removed
}

// Add smooth scrolling
document.querySelectorAll('.main-content').forEach(element => {
    element.style.scrollBehavior = 'smooth';
});

// Add touch gestures for mobile (optional enhancement)
let touchStartX = 0;
let touchEndX = 0;

document.addEventListener('touchstart', function(e) {
    touchStartX = e.changedTouches[0].screenX;
});

document.addEventListener('touchend', function(e) {
    touchEndX = e.changedTouches[0].screenX;
    handleSwipe();
});

function handleSwipe() {
    const swipeThreshold = 50;
    const diff = touchStartX - touchEndX;
    
    if (Math.abs(diff) > swipeThreshold) {
        const screens = ['home-screen', 'expense-screen', 'quiz-screen', 'report-screen', 'profile-screen'];
        const currentScreen = document.querySelector('.screen.active').id;
        const currentIndex = screens.indexOf(currentScreen);
        
        if (diff > 0 && currentIndex < screens.length - 1) {
            // Swipe left - next screen
            showScreen(screens[currentIndex + 1]);
        } else if (diff < 0 && currentIndex > 0) {
            // Swipe right - previous screen
            showScreen(screens[currentIndex - 1]);
        }
    }
}
