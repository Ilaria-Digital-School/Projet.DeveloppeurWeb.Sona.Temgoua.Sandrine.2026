// assets/js/article/autosave.js
export function initAutosave() {
    const form = document.getElementById('article-form');
    if (!form) return;

    let autoSaveTimeout;

    form.addEventListener('input', () => {
        clearTimeout(autoSaveTimeout);
        autoSaveTimeout = setTimeout(() => {
            const formData = new FormData(form);
            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            }).catch(err => console.warn('Auto-save failed', err));
        }, 3000);
    });
}

// Initialisation automatique
document.addEventListener('DOMContentLoaded', initAutosave);