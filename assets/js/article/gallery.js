// assets/js/article/gallery.js
export function initGallery() {
    const galleryDropzone = document.getElementById('dropzone-gallery');
    const galleryInput = document.getElementById('gallery-images-input');
    const galleryPreview = document.getElementById('gallery-preview');

    if (!galleryDropzone || !galleryInput || !galleryPreview) return;

    // Clic sur la dropzone
    galleryDropzone.addEventListener('click', () => galleryInput.click());

    // Changement de fichiers
    galleryInput.addEventListener('change', () => previewGalleryImages(galleryInput.files));

    // Drag & Drop
    galleryDropzone.addEventListener('dragover', (e) => {
        e.preventDefault();
        galleryDropzone.classList.add('border-blue-500', 'bg-blue-50');
    });

    galleryDropzone.addEventListener('dragleave', () => {
        galleryDropzone.classList.remove('border-blue-500', 'bg-blue-50');
    });

    galleryDropzone.addEventListener('drop', (e) => {
        e.preventDefault();
        galleryDropzone.classList.remove('border-blue-500', 'bg-blue-50');
        galleryInput.files = e.dataTransfer.files;
        previewGalleryImages(galleryInput.files);
    });

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

    // Suppression d'une image de la galerie
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
}

// Suppression d'une image de la galerie sans recharger la page
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

// Initialisation automatique
document.addEventListener('DOMContentLoaded', initGallery);