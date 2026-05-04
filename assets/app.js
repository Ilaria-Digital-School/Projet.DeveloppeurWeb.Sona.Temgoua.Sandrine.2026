// assets/app.js
import './styles/app.css';
import 'bootstrap/dist/js/bootstrap.bundle.min.js';
import { Carousel } from 'bootstrap';

document.addEventListener('DOMContentLoaded', () => {

    // 🎠 CAROUSEL
    const heroCarousel = document.querySelector('#carouselPromo');
    if (heroCarousel) {
        new Carousel(heroCarousel, {
            interval: 4000,
            ride: 'carousel',
            pause: false,
            wrap: true
        });
    }

    // 💬 AUTO SCROLL CHAT
    const chat = document.querySelector('.chat-messages');
    if (chat) {
        chat.scrollTop = chat.scrollHeight;
    }

    // 📷 IMAGE PREVIEW
    const imageInput = document.getElementById('imageInput');
    const previewContainer = document.getElementById('previewContainer');
    const imagePreview = document.getElementById('imagePreview');

    if (imageInput && previewContainer && imagePreview) {
        imageInput.addEventListener('change', function () {
            const file = this.files[0];

            if (file) {
                const reader = new FileReader();

                reader.onload = function (e) {
                    imagePreview.src = e.target.result;
                    previewContainer.style.display = 'block';
                };

                reader.readAsDataURL(file);
            } else {
                previewContainer.style.display = 'none';
            }
        });
    }

});
const searchBox = document.querySelector('.search-box');
const toggleBtn = document.getElementById('searchToggle');

toggleBtn.addEventListener('click', () => {
  searchBox.classList.toggle('active');
});

// assets/app.js

document.addEventListener('DOMContentLoaded', () => {
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
            if (newPagination) paginationContainer.innerHTML = newPagination.innerHTML;

            // Réattacher les événements de suppression
            attachDeleteEvents();
            // Réattacher les événements de pagination
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

                // Si plus aucune ligne, recharger la page 1
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
        setTimeout(() => {
            notif.remove();
        }, 3000);
    }

    searchInput.addEventListener('input', () => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => loadArticles(1), 400);
    });

    // Initialisation
    attachDeleteEvents();
    attachPaginationEvents();
});
//article animation
const input = document.getElementById('fileInput');
const preview = document.getElementById('preview');
const dropzone = document.getElementById('dropzone');

if (input && preview && dropzone) {

    let filesArray = [];

    function renderPreview() {
        preview.innerHTML = "";

        filesArray.forEach((file, index) => {
            const reader = new FileReader();

            reader.onload = (e) => {
                const col = document.createElement('div');
                col.className = "col-md-3";

                col.innerHTML = `
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden position-relative">
                        <img src="${e.target.result}" class="w-100" style="height:140px; object-fit:cover;">
                        <button class="btn btn-danger btn-sm position-absolute top-0 end-0 m-2">✖</button>
                    </div>
                `;

                col.querySelector('button').addEventListener('click', () => {
                    filesArray.splice(index, 1);
                    updateInput();
                    renderPreview();
                });

                preview.appendChild(col);
            };

            reader.readAsDataURL(file);
        });
    }

    function updateInput() {
        const dt = new DataTransfer();
        filesArray.forEach(f => dt.items.add(f));
        input.files = dt.files;
    }

    function handleFiles(files) {
        Array.from(files).forEach(file => {
            if (file.type.startsWith('image/')) {
                filesArray.push(file);
            }
        });

        updateInput();
        renderPreview();
    }

    input.addEventListener('change', () => handleFiles(input.files));

    dropzone.addEventListener('dragover', e => {
        e.preventDefault();
        dropzone.classList.add('border-success');
    });

    dropzone.addEventListener('dragleave', () => {
        dropzone.classList.remove('border-success');
    });

    dropzone.addEventListener('drop', e => {
        e.preventDefault();
        dropzone.classList.remove('border-success');
        handleFiles(e.dataTransfer.files);
    });
function handleImageChange(e) {
    const preview = document.getElementById('preview');
    preview.innerHTML = "";

    Array.from(e.target.files).forEach(file => {
        const reader = new FileReader();
        reader.onload = function(event) {
            const img = document.createElement('img');
            img.src = event.target.result;
            img.style.width = "100px";
            img.style.margin = "5px";
            preview.appendChild(img);
        };
        reader.readAsDataURL(file);
    });
    }
}