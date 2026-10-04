const quill = new Quill('#editor', {
    theme: 'snow',
    modules: {
        toolbar: [
            [{ header: [1, 2, 3, false] }],
            ['bold', 'italic', 'underline'],
            [{ list: 'ordered' }, { list: 'bullet' }]
        ]
    }
});

const contentInput = document.querySelector('#content');

if (contentInput.value !== '') {
    quill.clipboard.dangerouslyPasteHTML(contentInput.value);
}

quill.on('text-change', () => {
    formDirty = true;
    contentInput.value = quill.root.innerHTML;
});
