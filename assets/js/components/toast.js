// assets/js/components/toast.js
export function showNotification(message, type = 'success') {
    const notif = document.createElement('div');
    notif.className = `fixed top-4 right-4 z-50 px-4 py-2 rounded shadow-lg text-white ${type === 'success' ? 'bg-success' : 'bg-danger'} alert-notification`;
    notif.textContent = message;
    document.body.appendChild(notif);
    
    setTimeout(() => {
        notif.style.transition = 'all 0.5s ease-out';
        notif.style.opacity = '0';
        notif.style.transform = 'translateX(100%)';
        setTimeout(() => notif.remove(), 500);
    }, 3000);
}

export function showFlash(message, type) {
    const alert = document.createElement('div');
    alert.className = `alert alert-${type} position-fixed top-0 end-0 m-4`;
    alert.innerText = message;
    document.body.appendChild(alert);
    setTimeout(() => alert.remove(), 3000);
}

// Gestion automatique des toasts
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.flash-toast').forEach((toast, index) => {
        const delay = 2000 + (index * 300);
        setTimeout(() => {
            toast.style.transition = 'all 0.5s ease-out';
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(100%)';
            setTimeout(() => toast.remove(), 500);
        }, delay);
    });
});