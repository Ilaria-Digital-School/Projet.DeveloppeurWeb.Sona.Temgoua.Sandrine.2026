// assets/js/components/search.js

export class SearchManager {
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
        if (!this.searchInput) {
            console.warn('Search input not found');
            return;
        }

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
        
        this.searchInput.parentNode.appendChild(this.resultsContainer);
        
        const closeBtn = this.resultsContainer.querySelector('.close-results');
        if (closeBtn) {
            closeBtn.addEventListener('click', () => this.hideResults());
        }
    }

    setupEventListeners() {
        if (!this.searchToggle || !this.searchInput) return;

        this.searchToggle.addEventListener('click', (e) => {
            e.preventDefault();
            this.toggleSearch();
        });

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

        this.searchInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                const term = this.searchInput.value.trim();
                if (term.length >= 2) {
                    window.location.href = `/search?q=${encodeURIComponent(term)}`;
                }
            }
        });

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
        const resultsBody = this.resultsContainer?.querySelector('.search-results-body');
        if (!resultsBody) return;
        
        if (!results || results.length === 0) {
            resultsBody.innerHTML = `
                <div class="search-no-results">
                    <i class="fas fa-search"></i>
                    <p>Aucun résultat pour "${this.escapeHtml(term)}"</p>
                </div>
            `;
        } else {
            resultsBody.innerHTML = results.map(item => this.createResultItem(item)).join('');
        }
        
        this.resultsContainer.classList.add('show');
    }

    createResultItem(item) {
        const imageUrl = item.image ? item.image : '/images/default.jpg';
        const priceHtml = item.price ? '<span class="result-price">' + this.escapeHtml(item.price) + '</span>' : '';
        
        return `
            <a href="${item.url}" class="search-result-item">
                <img src="${imageUrl}" 
                     alt="${this.escapeHtml(item.title)}" 
                     class="result-image"
                     onerror="this.src='/images/default.jpg'">
                <div class="result-content">
                    <div class="result-title">${this.escapeHtml(item.title)}</div>
                    ${item.summary ? '<div class="result-summary">' + this.escapeHtml(item.summary) + '</div>' : ''}
                    <div class="result-meta">
                        ${item.category ? '<span class="result-badge badge-category">' + this.escapeHtml(item.category) + '</span>' : ''}
                        ${item.transactionType ? '<span class="result-badge badge-transaction">' + this.escapeHtml(item.transactionType) + '</span>' : ''}
                        ${priceHtml}
                        ${item.publishedAt ? '<span>' + item.publishedAt + '</span>' : ''}
                    </div>
                </div>
            </a>
        `;
    }

    showLoading() {
        const resultsBody = this.resultsContainer?.querySelector('.search-results-body');
        if (!resultsBody) return;
        
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
        const resultsBody = this.resultsContainer?.querySelector('.search-results-body');
        if (!resultsBody) return;
        
        resultsBody.innerHTML = `
            <div class="search-no-results">
                <i class="fas fa-exclamation-triangle"></i>
                <p>${this.escapeHtml(message)}</p>
            </div>
        `;
        this.resultsContainer.classList.add('show');
    }

    hideResults() {
        this.resultsContainer?.classList.remove('show');
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}

// Initialisation automatique
document.addEventListener('DOMContentLoaded', () => {
    new SearchManager();
});

// Réinitialisation après Turbo
document.addEventListener('turbo:load', () => {
    new SearchManager();
});