// assets/js/article/upload.js
export function initImageUpload() {
    const input = document.getElementById('fileInput');
    const preview = document.getElementById('preview');
    const dropzone = document.getElementById('dropzone');

    if (!input || !preview || !dropzone) return;

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
}

// Prévisualisation des images avant upload
document.addEventListener('change', function (e) {
    if (e.target.id !== 'fileInput') return;
    
    const previewContainer = document.getElementById('previewContainer');
    if (!previewContainer) return;
    
    previewContainer.innerHTML = '';
    Array.from(e.target.files).forEach(file => {
        if (!file.type.startsWith('image/')) return;
        const reader = new FileReader();
        reader.onload = function (event) {
            previewContainer.insertAdjacentHTML(
                'beforeend',
                `
                <div class="col-6 col-md-3">
                    <div class="card shadow-sm border-0">
                        <img src="${event.target.result}" 
                             class="card-img-top rounded" 
                             style="height:180px;object-fit:cover;">
                    </div>
                </div>
                `
            );
        };
        reader.readAsDataURL(file);
    });
});

// Initialisation automatique
document.addEventListener('DOMContentLoaded', initImageUpload);