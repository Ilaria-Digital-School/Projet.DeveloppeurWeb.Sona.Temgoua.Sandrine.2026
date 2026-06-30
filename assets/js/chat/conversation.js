// assets/js/chat/conversation.js
export function initConversations() {
    // Gestion des conversations
    const conversationItems = document.querySelectorAll('.conversation-item');
    conversationItems.forEach(item => {
        item.addEventListener('click', function() {
            // Marquer comme lu
            this.classList.remove('unread');
        });
    });
}

// Initialisation automatique
document.addEventListener('DOMContentLoaded', initConversations);