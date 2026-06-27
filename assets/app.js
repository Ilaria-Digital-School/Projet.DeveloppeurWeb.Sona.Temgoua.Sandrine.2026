// app.js - Fichier principal JavaScript pour l'application
// Importation des styles et des dépendances
import './styles/app.css';
import 'bootstrap/dist/js/bootstrap.bundle.min.js';
import { Carousel } from 'bootstrap';

// Initialisation des fonctionnalités après le chargement du DOM

document.addEventListener('DOMContentLoaded', () => {

    //  CAROUSEL
    const heroCarousel = document.querySelector('#carouselPromo');
    if (heroCarousel) {
    new Carousel(heroCarousel, {
    interval: 12000,
    ride: 'carousel',
    pause: false,
    wrap: true
});
    }

    //  IMAGE PREVIEW
    const imageInput = document.getElementById('imageInput');
    const previewContainer = document.getElementById('previewContainer');
    const imagePreview = document.getElementById('imagePreview');
    console.log(imageInput, previewContainer, imagePreview);

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

 // AUTO SCROLL CHAT
    const chat = document.querySelector('.chat-messages');
    if (chat) {
        chat.scrollTop = chat.scrollHeight;
    }

// Toggle de la barre de recherche
const searchBox = document.querySelector('.search-box');
const toggleBtn = document.getElementById('searchToggle');

if (toggleBtn && searchBox) {

    toggleBtn.addEventListener('click', () => {

        searchBox.classList.toggle('active');

    });

}

// code pour la page adminarticles  

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
    
    
    // chat auto scroll
    const chat = document.getElementById('chat');
    chat.scrollTop = chat.scrollHeight;
}

// Initialisation de Quill
const quill = new Quill('#editor-container', {
    theme: 'snow',
    placeholder: 'Rédigez votre article ici...',
    modules: {
        toolbar: [
            [{ 'header': [1, 2, 3, false] }],
            ['bold', 'italic', 'underline', 'strike'],
            [{ 'list': 'ordered' }, { 'list': 'bullet' }],
            ['link', 'image', 'video'],
            ['clean']
        ]
    }
});

quill.on('text-change', function () {
    document.getElementById('content-hidden').value = quill.root.innerHTML;
});

// Gestion de l'image principale
const mainDropzone = document.getElementById('dropzone-main');
const mainInput = document.getElementById('main-image-input');
const mainPreview = document.getElementById('main-image-preview');
const mainPreviewImg = document.getElementById('main-preview-img');

// Clic sur la dropzone pour ouvrir le sélecteur de fichiers

if (mainDropzone && mainInput) {

    mainDropzone.addEventListener('click', () => mainInput.click());

}

mainInput.addEventListener('change', (e) => {
    if (e.target.files.length) {
        const file = e.target.files[0];
        const reader = new FileReader();
        reader.onload = (event) => {
            mainPreviewImg.src = event.target.result;
            mainPreview.classList.remove('hidden');
        };
        reader.readAsDataURL(file);
    }
});

window.clearMainImage = function () {
    mainInput.value = '';
    mainPreview.classList.add('hidden');
};

// Gestion de la galerie
const galleryDropzone = document.getElementById('dropzone-gallery');
const galleryInput = document.getElementById('gallery-images-input');
const galleryPreview = document.getElementById('gallery-preview');

galleryDropzone.addEventListener('click', () => galleryInput.click());
galleryInput.addEventListener('change', () => previewGalleryImages(galleryInput.files));

function previewGalleryImages(files) {
    galleryPreview.innerHTML = '';
    Array.from(files).forEach((file, index) => {
        const reader = new FileReader();
        reader.onload = (e) => {
            const div = document.createElement('div');
            div.className = 'relative group';
            div.innerHTML = `
                <img src="${e.target.result}" class="w-full h-32 object-cover rounded-lg">
                <button type="button" class="remove-gallery-img absolute top-2 right-2 bg-red-600 text-white rounded-full w-6 h-6 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity" data-index="${index}">
                    ×
                </button>
            `;
            galleryPreview.appendChild(div);
        };
        reader.readAsDataURL(file);
    });
}

// Suppression d'une image de la galerie (délégation d'événement)
galleryPreview.addEventListener('click', (e) => {
    const btn = e.target.closest('.remove-gallery-img');
    if (!btn) return;
    const index = parseInt(btn.dataset.index);
    const dt = new DataTransfer();
    const files = Array.from(galleryInput.files);
    files.splice(index, 1);
    files.forEach(file => dt.items.add(file));
    galleryInput.files = dt.files;
    previewGalleryImages(galleryInput.files);
});

// Drag & Drop amélioré
function setupDragAndDrop(dropzone, input) {
    dropzone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropzone.classList.add('border-blue-500', 'bg-blue-50');
    });
    dropzone.addEventListener('dragleave', () => {
        dropzone.classList.remove('border-blue-500', 'bg-blue-50');
    });
    dropzone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropzone.classList.remove('border-blue-500', 'bg-blue-50');
        if (input.id === 'gallery-images-input') {
            input.files = e.dataTransfer.files;
            previewGalleryImages(input.files);
        } else if (input.id === 'main-image-input') {
            input.files = e.dataTransfer.files;
            const changeEvent = new Event('change');
            input.dispatchEvent(changeEvent);
        }
    });
}

setupDragAndDrop(mainDropzone, mainInput);
setupDragAndDrop(galleryDropzone, galleryInput);

// Auto-save brouillon (optionnel)
let autoSaveTimeout;
const form = document.getElementById('article-form');
if (form) {
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
// Supprimer une image de la galerie sans recharger la page 

document.querySelectorAll('.delete-image-form').forEach(form => {
    form.addEventListener('submit', function (e) {
        e.preventDefault();

        if (!confirm("Supprimer cette image ?")) return;

        const url = this.action;
        const formData = new FormData(this);
        const card = this.closest('.image-card');

        fetch(url, {
            method: "POST",
            body: formData
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    card.remove();
                    showFlash("Image supprimée ✅", "success");
                }
            });
    });
});

function showFlash(message, type) {
    const alert = document.createElement('div');
    alert.className = `alert alert-${type} position-fixed top-0 end-0 m-4`;
    alert.innerText = message;
    document.body.appendChild(alert);

    setTimeout(() => alert.remove(), 3000);

}


// Validation du formulaire de message dans le chat
const chatForm = document.getElementById('chatForm');

if (chatForm) {
    chatForm.addEventListener('submit', function(e) {
        // Fonction utilitaire pour détecter si une chaîne ne contient que des blancs (y compris Unicode)
        function isBlank(str) {
            if (!str) return true;
            // Supprime tous les caractères de type "séparateur" (espaces, insécables, fins, etc.)
            const cleaned = str.replace(/[\p{Z}\s]/gu, '');
            return cleaned.length === 0;
        }

        const rawMessage = document.getElementById('messageInput')?.value || '';
        const messageIsBlank = isBlank(rawMessage);

        const image = document.getElementById('imageInput')?.files.length || 0;
        const file  = document.getElementById('fileInput')?.files.length || 0;
        const audio = document.getElementById('audioInput')?.files.length || 0;

        if (messageIsBlank && !image && !file && !audio) {
            e.preventDefault();
            alert("Le message ne peut pas être vide.");
        }
    });
}

// Prévisualisation des images avant upload d'un article

document.addEventListener('change', function (e) {

    if (e.target.id !== 'fileInput') {
        return;
    }

    const previewContainer = document.getElementById('previewContainer');

    previewContainer.innerHTML = '';

    Array.from(e.target.files).forEach(file => {

        if (!file.type.startsWith('image/')) {
            return;
        }

        const reader = new FileReader();

        reader.onload = function (event) {

            previewContainer.insertAdjacentHTML(
                'beforeend',
                `
                <div class="col-6 col-md-3">
                    <div class="card shadow-sm border-0">
                        <img
                            src="${event.target.result}"
                            class="card-img-top rounded"
                            style="height:180px;object-fit:cover;"
                        >
                    </div>
                </div>
                `
            );
        };

        reader.readAsDataURL(file);
    });
});

// Validation du formulaire avec Bootstrap 5    
document.addEventListener('DOMContentLoaded', function () {

    const forms = document.querySelectorAll('.needs-validation');

    Array.from(forms).forEach(form => {

        form.addEventListener('submit', function(event) {

            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }

            form.classList.add('was-validated');

        }, false);

    });

});







// public/js/search.js

class SearchManager {
    constructor() {
        this.searchToggle = document.getElementById('searchToggle');
        this.searchInput = document.getElementById('searchInput');
        this.resultsContainer = null;
        this.debounceTimer = null;
        this.isSearching = false;
        this.searchUrl = '/search/ajax';
        
        this.init();
    }

    init() {
        this.createResultsContainer();
        this.setupEventListeners();
    }

    createResultsContainer() {
        // Créer le conteneur de résultats
        this.resultsContainer = document.createElement('div');
        this.resultsContainer.className = 'search-results-container';
        this.resultsContainer.innerHTML = `
            <div class="search-results-header">
                <h6>Résultats de recherche</h6>
                <button class="close-results">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="search-results-body"></div>
        `;
        
        // Insérer après l'input de recherche
        this.searchInput.parentNode.appendChild(this.resultsContainer);
        
        // Écouteur pour fermer les résultats
        const closeBtn = this.resultsContainer.querySelector('.close-results');
        closeBtn.addEventListener('click', () => this.hideResults());
    }

    setupEventListeners() {
        // Toggle de la barre de recherche
        this.searchToggle.addEventListener('click', (e) => {
            e.preventDefault();
            this.toggleSearch();
        });

        // Recherche en temps réel avec debounce
        this.searchInput.addEventListener('input', (e) => {
            clearTimeout(this.debounceTimer);
            const term = e.target.value.trim();
            
            if (term.length >= 2) {
                this.debounceTimer = setTimeout(() => {
                    this.performSearch(term);
                }, 300);
            } else {
                this.hideResults();
            }
        });

        // Touche Entrée pour la recherche
        this.searchInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                const term = this.searchInput.value.trim();
                if (term.length >= 2) {
                    window.location.href = `/search?q=${encodeURIComponent(term)}`;
                }
            }
        });

        // Fermer les résultats en cliquant ailleurs
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.search-box')) {
                this.hideResults();
            }
        });
    }

    toggleSearch() {
        this.searchInput.classList.toggle('active');
        
        if (this.searchInput.classList.contains('active')) {
            this.searchInput.focus();
        } else {
            this.searchInput.value = '';
            this.hideResults();
        }
    }

    async performSearch(term) {
        if (this.isSearching) return;
        
        this.isSearching = true;
        this.showLoading();
        
        try {
            const response = await fetch(`${this.searchUrl}?q=${encodeURIComponent(term)}`);
            
            if (!response.ok) {
                throw new Error('Erreur réseau');
            }
            
            const data = await response.json();
            
            if (data.success) {
                this.displayResults(data.results, data.term);
            } else {
                this.showError(data.message);
            }
        } catch (error) {
            console.error('Erreur de recherche:', error);
            this.showError('Une erreur est survenue lors de la recherche');
        } finally {
            this.isSearching = false;
        }
    }

    displayResults(results, term) {
        const resultsBody = this.resultsContainer.querySelector('.search-results-body');
        
        if (!results || results.length === 0) {
            resultsBody.innerHTML = `
                <div class="search-no-results">
                    <i class="fas fa-search"></i>
                    <p>Aucun résultat pour "${term}"</p>
                </div>
            `;
        } else {
            resultsBody.innerHTML = results.map(item => this.createResultItem(item)).join('');
        }
        
        this.resultsContainer.classList.add('show');
    }

    createResultItem(item) {
        const imageUrl = item.image || '/images/default-article.jpg';
        const priceHtml = item.price ? `<span class="result-price">${item.price}</span>` : '';
        
        return `
            <a href="${item.url}" class="search-result-item">
                <img src="${imageUrl}" 
                     alt="${this.escapeHtml(item.title)}" 
                     class="result-image"
                     onerror="this.src='/images/default-article.jpg'">
                <div class="result-content">
                    <div class="result-title">${this.escapeHtml(item.title)}</div>
                    ${item.summary ? `<div class="result-summary">${this.escapeHtml(item.summary)}</div>` : ''}
                    <div class="result-meta">
                        ${item.category ? `<span class="result-badge badge-category">${this.escapeHtml(item.category)}</span>` : ''}
                        ${item.transactionType ? `<span class="result-badge badge-transaction">${this.escapeHtml(item.transactionType)}</span>` : ''}
                        ${priceHtml}
                        ${item.publishedAt ? `<span>${item.publishedAt}</span>` : ''}
                    </div>
                </div>
            </a>
        `;
    }

    showLoading() {
        const resultsBody = this.resultsContainer.querySelector('.search-results-body');
        resultsBody.innerHTML = `
            <div class="search-loading">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Recherche...</span>
                </div>
                <p class="mt-2">Recherche en cours...</p>
            </div>
        `;
        this.resultsContainer.classList.add('show');
    }

    showError(message) {
        const resultsBody = this.resultsContainer.querySelector('.search-results-body');
        resultsBody.innerHTML = `
            <div class="search-no-results">
                <i class="fas fa-exclamation-triangle"></i>
                <p>${this.escapeHtml(message)}</p>
            </div>
        `;
        this.resultsContainer.classList.add('show');
    }

    hideResults() {
        this.resultsContainer.classList.remove('show');
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}

// Initialisation au chargement de la page
document.addEventListener('DOMContentLoaded', () => {
    new SearchManager();
});

// Réinitialisation après les requêtes Turbo (si vous utilisez Turbo)
document.addEventListener('turbo:load', () => {
    new SearchManager();
});

// Gestion des toasts de notification
document.addEventListener('DOMContentLoaded', function() {
    const toasts = document.querySelectorAll('.flash-toast');
    
    toasts.forEach(function(toast, index) {
        // Délai progressif pour éviter que tous disparaissent en même temps
        const delay = 2000 + (index * 300);
        
        setTimeout(function() {
            toast.style.transition = 'all 0.5s ease-out';
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(100%)';
            
            setTimeout(function() {
                toast.remove();
            }, 500);
        }, delay);
    });
});


