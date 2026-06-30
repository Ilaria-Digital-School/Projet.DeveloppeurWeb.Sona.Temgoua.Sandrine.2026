// assets/js/article/editor.js
export function initEditor() {
    const editorContainer = document.getElementById('editor-container');
    const contentHidden = document.getElementById('content-hidden');

    if (!editorContainer || !contentHidden) return;

    // Chargement dynamique de Quill si nécessaire
    if (typeof Quill !== 'undefined') {
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
            contentHidden.value = quill.root.innerHTML;
        });
    } else {
        console.warn('Quill n\'est pas chargé');
    }
}

// Initialisation automatique
document.addEventListener('DOMContentLoaded', initEditor);