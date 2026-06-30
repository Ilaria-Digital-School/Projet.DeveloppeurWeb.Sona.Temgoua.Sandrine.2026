// assets/js/admin/articles.js
export function initAdminArticles() {
    const searchInput = document.getElementById('searchInput');
    const spinner = document.getElementById('searchSpinner');
    const tableBody = document.getElementById('articlesTableBody');
    const paginationContainer = document.getElementById('paginationContainer');

    if (!searchInput || !tableBody) return;

    let searchTimeout;

    async function loadArticles(page = 1) {
        const search = searchInput.value;
        if (spinner) spinner.classList.remove('hidden');

        try {
            const response = await fetch(`/article?search=${encodeURIComponent(search)}&page=${page}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });

            if (!response.ok) throw new Error('Erreur réseau');

            const html = await response.text();
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');

            const newTbody = doc.querySelector('#articlesTableBody');
            const newPagination = doc.querySelector('#paginationContainer');

            if (newTbody) tableBody.innerHTML = newTbody.innerHTML;
            if (newPagination && paginationContainer) paginationContainer.innerHTML = newPagination.innerHTML;

            attachDeleteEvents();
            attachPaginationEvents();
            updateUrl(search, page);
        } catch (error) {
            console.error(error);
            showNotification('Erreur de chargement', 'error');
        } finally {
            if (spinner) spinner.classList.add('hidden');
        }
    }

    function attachDeleteEvents() {
        document.querySelectorAll('.delete-btn').forEach(btn => {
            btn.removeEventListener('click', handleDelete);
            btn.addEventListener('click', handleDelete);
        });
    }

    async function handleDelete(e) {
        const btn = e.currentTarget;
        const id = btn.dataset.id;
        const token = btn.dataset.token;

        if (!confirm('Supprimer cet article ?')) return;

        try {
            const response = await fetch(`/article/${id}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ _token: token })
            });

            if (response.ok) {
                btn.closest('tr').remove();
                showNotification('Article supprimé', 'success');

                if (document.querySelectorAll('#articlesTableBody tr').length === 0) {
                    loadArticles(1);
                }
            } else {
                showNotification('Erreur lors de la suppression', 'error');
            }
        } catch (err) {
            console.error(err);
            showNotification('Erreur réseau', 'error');
        }
    }

    function attachPaginationEvents() {
        document.querySelectorAll('#paginationContainer .page-link').forEach(link => {
            link.removeEventListener('click', handlePaginationClick);
            link.addEventListener('click', handlePaginationClick);
        });
    }

    function handlePaginationClick(e) {
        e.preventDefault();
        const url = new URL(e.currentTarget.href);
        const page = url.searchParams.get('page') || 1;
        loadArticles(page);
    }

    function updateUrl(search, page) {
        const url = new URL(window.location.href);
        if (search) url.searchParams.set('search', search);
        else url.searchParams.delete('search');
        url.searchParams.set('page', page);
        window.history.pushState({}, '', url);
    }

    function showNotification(message, type = 'success') {
        const notif = document.createElement('div');
        notif.className = `fixed top-4 right-4 z-50 px-4 py-2 rounded shadow-lg text-white ${type === 'success' ? 'bg-success' : 'bg-danger'} alert-notification`;
        notif.textContent = message;
        document.body.appendChild(notif);
        setTimeout(() => notif.remove(), 3000);
    }

    searchInput.addEventListener('input', () => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => loadArticles(1), 400);
    });

    // Initialisation
    attachDeleteEvents();
    attachPaginationEvents();
}

// Initialisation automatique
document.addEventListener('DOMContentLoaded', initAdminArticles);