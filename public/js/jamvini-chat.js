// JamVini AI Chat Widget
(function() {
    'use strict';
    
    // Wait for DOM to be ready
    document.addEventListener('DOMContentLoaded', function() {
        initGuestChat();
        initLoggedInChatHint();
    });
    
    // Guest Chat Handler
    function initGuestChat() {
        const guestToggle = document.getElementById('guestChatToggle');
        const guestWindow = document.getElementById('guestChatWindow');
        const guestClose = document.getElementById('closeGuestChat');
        
        if (!guestToggle || !guestWindow) return;
        
        // Open chat window
        guestToggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            guestWindow.classList.toggle('open');
        });
        
        // Close chat window
        if (guestClose) {
            guestClose.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                guestWindow.classList.remove('open');
            });
        }
        
        // Close when clicking outside
        document.addEventListener('click', function(e) {
            if (guestWindow.classList.contains('open')) {
                if (!guestWindow.contains(e.target) && !guestToggle.contains(e.target)) {
                    guestWindow.classList.remove('open');
                }
            }
        });
        
        // Prevent window from closing when clicking inside
        guestWindow.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    }
    
    // Logged-in user hint bubble animation
    function initLoggedInChatHint() {
        const chatHint = document.querySelector('.jv-chat-hint');
        if (!chatHint) return;
        
        // Fade out after 5 seconds
        setTimeout(() => {
            chatHint.style.transition = 'opacity 1s ease';
            chatHint.style.opacity = '0';
        }, 5000);
        
        // Remove from DOM after animation
        setTimeout(() => {
            if (chatHint.parentNode) {
                chatHint.style.display = 'none';
            }
        }, 6000);
    }
})();