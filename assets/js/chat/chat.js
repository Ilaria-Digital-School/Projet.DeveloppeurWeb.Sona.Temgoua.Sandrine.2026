// assets/js/chat/chat.js
export function initChat() {
    const chatForm = document.getElementById('chatForm');
    if (!chatForm) return;

    // Auto-scroll
    const chat = document.querySelector('.chat-messages');
    if (chat) {
        chat.scrollTop = chat.scrollHeight;
    }

    // Validation du formulaire
    chatForm.addEventListener('submit', function (e) {
        function isBlank(str) {
            if (!str) return true;
            const cleaned = str.replace(/[\p{Z}\s]/gu, '');
            return cleaned.length === 0;
        }

        const rawMessage = document.getElementById('messageInput')?.value || '';
        const messageIsBlank = isBlank(rawMessage);
        const image = document.getElementById('imageInput')?.files.length || 0;
        const file = document.getElementById('fileInput')?.files.length || 0;
        const audio = document.getElementById('audioInput')?.files.length || 0;

        if (messageIsBlank && !image && !file && !audio) {
            e.preventDefault();
            alert("Le message ne peut pas être vide.");
        }
    });
}

// Initialisation automatique
document.addEventListener('DOMContentLoaded', initChat);