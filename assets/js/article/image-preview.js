// assets/js/article/image-preview.js

export class ImagePreviewManager {
    constructor() {
        this.dropzone = document.getElementById('dropzone');
        this.previewContainer = document.getElementById('previewContainer');
        
        // Chercher l'input par son nom plutôt que par son ID
        this.fileInput = document.querySelector('input[type="file"][name*="[images]"]');
        
        // Image principale
        this.mainImageInput = document.querySelector('input[type="file"][name*="[image]"]');
        
        this.selectedFiles = [];
        
        // Afficher ce qu'on a trouvé
        console.log('Dropzone:', this.dropzone);
        console.log('FileInput:', this.fileInput);
        console.log('MainImageInput:', this.mainImageInput);
        console.log('PreviewContainer:', this.previewContainer);
        
        this.init();
    }

    init() {
        if (!this.fileInput) {
            console.error('Input file non trouvé !');
            console.log('Recherche avec sélecteur:', 'input[type="file"][name*="[images]"]');
            console.log('Tous les inputs file:', document.querySelectorAll('input[type="file"]'));
            return;
        }
        
        if (this.dropzone) {
            this.setupDropzone();
        }
        
        this.setupFileInput();
        
        if (this.mainImageInput) {
            this.setupMainImagePreview();
        }
    }

    setupDropzone() {
        if (!this.dropzone || !this.fileInput) return;
        
        console.log('Setup dropzone');

        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            this.dropzone.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
            });
        });

        this.dropzone.addEventListener('dragenter', () => {
            this.dropzone.classList.add('dragover');
        });

        this.dropzone.addEventListener('dragover', () => {
            this.dropzone.classList.add('dragover');
        });

        this.dropzone.addEventListener('dragleave', () => {
            this.dropzone.classList.remove('dragover');
        });

        this.dropzone.addEventListener('drop', (e) => {
            this.dropzone.classList.remove('dragover');
            const files = Array.from(e.dataTransfer.files).filter(file => 
                file.type.startsWith('image/')
            );
            
            if (files.length > 0) {
                this.addFiles(files);
            }
        });

        this.dropzone.addEventListener('click', (e) => {
            if (e.target === this.dropzone || e.target.closest('.dropzone-content')) {
                console.log('Clic sur dropzone, ouverture input');
                this.fileInput.click();
            }
        });
    }

    setupFileInput() {
        if (!this.fileInput) return;
        
        console.log('Setup fileInput');
        
        this.fileInput.addEventListener('change', (e) => {
            console.log('Fichiers sélectionnés:', e.target.files);
            const files = Array.from(e.target.files);
            if (files.length > 0) {
                this.addFiles(files);
            }
        });
    }

    setupMainImagePreview() {
        if (!this.mainImageInput) return;
        
        this.mainImageInput.addEventListener('change', (e) => {
            const file = e.target.files[0];
            
            const oldPreview = document.querySelector('.main-image-preview');
            if (oldPreview) oldPreview.remove();
            
            if (file) {
                this.createMainImagePreview(file);
            }
        });
    }

    createMainImagePreview(file) {
        const reader = new FileReader();
        
        reader.onload = (e) => {
            const preview = document.createElement('div');
            preview.className = 'main-image-preview';
            
            preview.innerHTML = `
                <img src="${e.target.result}" alt="Image principale" style="width:200px;height:200px;object-fit:cover;">
                <button type="button" class="btn-delete-main" title="Supprimer l'image">
                    <i class="fas fa-times"></i>
                </button>
                <div class="main-badge">Principale</div>
            `;
            
            const deleteBtn = preview.querySelector('.btn-delete-main');
            deleteBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                preview.remove();
                this.mainImageInput.value = '';
            });
            
            this.mainImageInput.parentNode.insertBefore(preview, this.mainImageInput.nextSibling);
        };
        
        reader.readAsDataURL(file);
    }

    addFiles(files) {
        console.log('addFiles appelé avec:', files);
        
        files.forEach(file => {
            if (!file.type.startsWith('image/')) {
                alert(`Le fichier "${file.name}" n'est pas une image.`);
                return;
            }
            
            if (file.size > 5 * 1024 * 1024) {
                alert(`Le fichier "${file.name}" dépasse 5 Mo.`);
                return;
            }
            
            const isDuplicate = this.selectedFiles.some(
                existingFile => existingFile.name === file.name && existingFile.size === file.size
            );
            
            if (isDuplicate) {
                alert(`Le fichier "${file.name}" est déjà ajouté.`);
                return;
            }
            
            this.selectedFiles.push(file);
            this.createPreviewCard(file);
        });
        
        this.updateFileInput();
    }

    createPreviewCard(file) {
        console.log('Création preview pour:', file.name);
        
        const reader = new FileReader();
        
        reader.onload = (e) => {
            const card = document.createElement('div');
            card.className = 'preview-card';
            card.dataset.filename = file.name;
            card.dataset.filesize = file.size;
            
            card.innerHTML = `
                <img src="${e.target.result}" alt="${file.name}">
                <button type="button" class="btn-delete" title="Supprimer cette image">
                    <i class="fas fa-times"></i>
                </button>
            `;
            
            const deleteBtn = card.querySelector('.btn-delete');
            deleteBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.removeFile(card, file);
            });
            
            if (this.previewContainer) {
                this.previewContainer.appendChild(card);
                console.log('Carte ajoutée au DOM');
            } else {
                console.error('previewContainer est null');
            }
        };
        
        reader.readAsDataURL(file);
    }

    removeFile(card, file) {
        card.classList.add('removing');
        
        setTimeout(() => {
            card.remove();
            this.selectedFiles = this.selectedFiles.filter(f => 
                !(f.name === file.name && f.size === file.size)
            );
            this.updateFileInput();
        }, 300);
    }

    updateFileInput() {
        if (!this.fileInput) return;
        
        const dt = new DataTransfer();
        this.selectedFiles.forEach(file => {
            dt.items.add(file);
        });
        
        this.fileInput.files = dt.files;
        console.log('Files mis à jour:', this.fileInput.files);
    }
}

// Initialisation automatique
document.addEventListener('DOMContentLoaded', () => {
    console.log('DOM chargé - Initialisation ImagePreviewManager');
    new ImagePreviewManager();
});