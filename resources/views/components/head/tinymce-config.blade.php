<script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.3/tinymce.min.js" referrerpolicy="origin" crossorigin="anonymous"></script>

{{-- KaTeX: konversi LaTeX ke MathML (native browser rendering, tanpa CSS eksternal) --}}
<script defer src="https://cdn.jsdelivr.net/npm/katex@0.16.11/dist/katex.min.js"></script>

<script>
function latexToHtml(latex, display = false) {
    if (typeof katex === 'undefined') return (display ? '\\[ ' : '\\( ') + latex + (display ? ' \\]' : ' \\)');
    try {
        return katex.renderToString(latex, {
            throwOnError: false,
            displayMode: display,
            output: 'mathml'
        });
    } catch (err) {
        return (display ? '\\[ ' : '\\( ') + latex + (display ? ' \\]' : ' \\)');
    }
}

function buildMathSpan(latex, innerMarkup, display = false) {
    const displayStyle = display
        ? 'display:block; text-align:center; margin:8px 4px;'
        : 'display:inline-block; vertical-align:middle; margin:0 2px;';
    return '<span class="mathlive-wrapper mceNonEditable"'
        + ' data-latex="' + latex.replace(/"/g, '&quot;') + '"'
        + ' data-display="' + (display ? '1' : '0') + '"'
        + ' contenteditable="false"'
        + ' style="cursor:pointer; padding:1px 6px; border-radius:3px; border:1px solid #e2e8f0; background:#f8fafc; line-height:normal; ' + displayStyle + '">' 
        + innerMarkup
        + '</span>';
}

// Konversi semua format LaTeX dalam string HTML ke span visual
function convertLatexInHtml(html) {
    // Urutan penting: $$...$$ dulu sebelum $...$
    return html
        // Display math: $$...$$
        .replace(/\$\$([\s\S]+?)\$\$/g, (m, latex) =>
            buildMathSpan(latex.trim(), latexToHtml(latex.trim(), true), true))
        // Display math: \[...\]
        .replace(/\\\[([\s\S]+?)\\\]/g, (m, latex) =>
            buildMathSpan(latex.trim(), latexToHtml(latex.trim(), true), true))
        // Inline math: \(...\)
        .replace(/\\\(([\s\S]*?)\\\)/g, (m, latex) =>
            buildMathSpan(latex.trim(), latexToHtml(latex.trim(), false), false))
        // Inline math: $...$ (bukan $$)
        .replace(/(?<!\$)\$(?!\$)([^\$\n]+?)(?<!\$)\$(?!\$)/g, (m, latex) =>
            buildMathSpan(latex.trim(), latexToHtml(latex.trim(), false), false));
}

document.addEventListener('alpine:init', () => {
    Alpine.data('tinyEditor', (wireModelName, height = 400, customToolbar = null, customMenubar = true) => ({
        content: '',
        height: height,
        wireModelName: wireModelName,
        toolbar: customToolbar || 'undo redo | blocks | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | indent outdent | bullist numlist | link image media mathlive table | removeformat | code fullscreen',
        menubar: customMenubar,
        editorInstance: null,

        init() {
            const waitAndInit = () => {
                if (typeof katex !== 'undefined' && typeof tinymce !== 'undefined') {
                    console.log('TinyMCE & KaTeX ready. Initializing editor...');
                    setTimeout(() => this.initializeTinyMCE(), 50);
                } else {
                    setTimeout(waitAndInit, 100);
                }
            };
            waitAndInit();

            if (this.wireModelName && this.$wire) {
                this.content = this.$wire.get(this.wireModelName) || '';
                
                // Watch logic: when Livewire resets the property, update the editor
                this.$wire.$watch(this.wireModelName, (value) => {
                    if (this.editorInstance && this.editorInstance.getContent() !== (value || '')) {
                        this.editorInstance.setContent(value || '');
                    }
                    this.content = value || '';
                });

                this.$watch('content', (value) => {
                    if (this.editorInstance && this.editorInstance.getContent() !== value) {
                        this.editorInstance.setContent(value || '');
                    }
                });
            }
        },

        initializeTinyMCE() {
            const el = this.$refs.textarea || (this.$el.tagName === 'TEXTAREA' ? this.$el : this.$el.querySelector('textarea'));
            if (!el) return;
            if (this.content) el.value = this.content;

            tinymce.init({
                target: el,
                plugins: 'code table lists image media link searchreplace visualblocks fullscreen wordcount',
                toolbar: this.toolbar,
                menubar: this.menubar,
                height: this.height,
                skin: 'oxide',
                extended_valid_elements: '*[*]',
                content_css: [],
                content_style: `
                    body { font-family: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif; font-size: 16px; line-height: 1.6; }
                    .mathlive-wrapper { display: inline-block !important; cursor: pointer; vertical-align: middle; padding: 1px 6px; border-radius: 3px; border: 1px solid #e2e8f0; background: #f8fafc; margin: 0 2px; line-height: normal; }
                    math { display: inline; vertical-align: middle; }
                `,
                images_upload_handler: (blobInfo) => {
                    return new Promise((resolve, reject) => {
                        const formData = new FormData();
                        formData.append('file', blobInfo.blob(), blobInfo.filename());
                        // Null-safe CSRF token: fallback ke cookie jika meta tag tidak ada
                        const metaTag = document.querySelector('meta[name="csrf-token"]');
                        const token = metaTag ? metaTag.getAttribute('content') : '';
                        fetch('/upload-media', {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': token },
                            body: formData
                        })
                        .then(r => r.ok ? r.json() : r.json().then(e => Promise.reject(e.error || 'Upload error ' + r.status)))
                        .then(result => result.success ? resolve(result.url) : reject(result.error || 'Upload gagal'))
                        .catch(err => reject('Upload failed: ' + err));
                    });
                },
                // Tombol "Upload" di modal gambar TinyMCE
                file_picker_types: 'image media file',
                file_picker_callback: (callback, value, meta) => {
                    const input = document.createElement('input');
                    input.setAttribute('type', 'file');
                    if (meta.filetype === 'media') {
                        input.setAttribute('accept', 'video/mp4,video/webm,video/ogg');
                    } else if (meta.filetype === 'image') {
                        input.setAttribute('accept', 'image/jpeg,image/png,image/gif,image/svg+xml,image/webp');
                    } else {
                        // Accept any file for 'file' type
                        input.setAttribute('accept', '*/*');
                    }
                    input.addEventListener('change', (e) => {
                        const file = e.target.files[0];
                        if (!file) return;

                        const formData = new FormData();
                        formData.append('file', file, file.name);
                        const metaTag = document.querySelector('meta[name="csrf-token"]');
                        const token = metaTag ? metaTag.getAttribute('content') : '';

                        fetch('/upload-media', {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': token },
                            body: formData
                        })
                        .then(r => r.ok ? r.json() : r.json().then(e => Promise.reject(e.error || 'Error ' + r.status)))
                        .then(result => {
                            if (result.success) {
                                callback(result.url, { title: file.name });
                            } else {
                                alert('Upload gagal: ' + (result.error || 'Tidak diketahui'));
                            }
                        })
                        .catch(err => alert('Upload error: ' + err));
                    });
                    input.click();
                },
                setup: (editor) => {
                    this.editorInstance = editor;

                    editor.ui.registry.addButton('mathlive', {
                        icon: 'superscript',
                        tooltip: 'Sisipkan Rumus Matematika',
                        onAction: () => this.openMathLiveModal('', null)
                    });

                    editor.on('init', () => {
                        console.log('TinyMCE Editor is ready!', { id: editor.id });
                        if (this.content) editor.setContent(this.content);
                    });

                    // Semua format LaTeX di konten → span visual
                    editor.on('BeforeSetContent', (e) => {
                        if (!e.content) return;
                        e.content = convertLatexInHtml(e.content);
                    });

                    // Konversi format LaTeX di konten yang di-paste (copy-paste dari luar)
                    editor.on('PastePostProcess', (e) => {
                        const node = e.node;
                        if (!node) return;

                        // Cari semua text node yang mengandung LaTeX
                        const hasLatex = (s) => /\$\$|\\\[|\\\(|(?<!\$)\$(?!\$)/.test(s);
                        const walker = document.createTreeWalker(node, NodeFilter.SHOW_TEXT);
                        const toReplace = [];
                        let tn;
                        while ((tn = walker.nextNode())) {
                            if (hasLatex(tn.nodeValue)) toReplace.push(tn);
                        }
                        toReplace.forEach(tn => {
                            const parent = tn.parentNode;
                            if (!parent) return;
                            const tmp = document.createElement('span');
                            tmp.innerHTML = convertLatexInHtml(tn.nodeValue);
                            while (tmp.firstChild) parent.insertBefore(tmp.firstChild, tn);
                            parent.removeChild(tn);
                        });

                        if (toReplace.length > 0) editor.fire('change');
                    });

                    // span visual → \(...\) saat konten diambil
                    editor.on('GetContent', (e) => {
                        if (e.format !== 'html' || !e.content) return;
                        const tmp = document.createElement('div');
                        tmp.innerHTML = e.content;
                        let changed = false;
                        tmp.querySelectorAll('.mathlive-wrapper').forEach(el => {
                            const latex = el.getAttribute('data-latex');
                            if (latex) {
                                el.replaceWith(document.createTextNode('\\( ' + latex + ' \\)'));
                                changed = true;
                            }
                        });
                        if (changed) e.content = tmp.innerHTML;
                    });

                    // Hapus formula: gunakan editor.dom.getParent (berjalan di dalam iframe)
                    editor.on('keydown', (e) => {
                        if (e.keyCode !== 8 && e.keyCode !== 46) return;

                        const del = (node) => {
                            editor.selection.select(node);
                            editor.selection.setContent('');
                            editor.fire('change');
                            e.preventDefault();
                        };

                        // Cari wrapper dari node yang sedang dipilih (gunakan TinyMCE DOM API)
                        const node = editor.selection.getNode();
                        const wrapperFromNode = editor.dom.getParent(node, '.mathlive-wrapper');
                        if (wrapperFromNode) { del(wrapperFromNode); return; }

                        // Cari wrapper dari range container (cursor mungkin di dalam MathML)
                        try {
                            const rng = editor.selection.getRng();
                            const wrapperFromRange = editor.dom.getParent(rng.startContainer, '.mathlive-wrapper');
                            if (wrapperFromRange) { del(wrapperFromRange); return; }

                            // Sibling check: cursor di text node di sebelah wrapper
                            const c = rng.startContainer;
                            const off = rng.startOffset;
                            if (e.keyCode === 8) {
                                const prev = c.nodeType === 3
                                    ? c.previousSibling
                                    : (c.childNodes && off > 0 ? c.childNodes[off - 1] : null);
                                if (prev && prev.nodeType === 1 && prev.classList && prev.classList.contains('mathlive-wrapper')) {
                                    del(prev); return;
                                }
                            } else {
                                const next = c.nodeType === 3
                                    ? c.nextSibling
                                    : (c.childNodes ? c.childNodes[off] : null);
                                if (next && next.nodeType === 1 && next.classList && next.classList.contains('mathlive-wrapper')) {
                                    del(next); return;
                                }
                            }
                        } catch (_) {}
                    });

                    // Klik formula → buka modal edit
                    editor.on('click', (e) => {
                        const wrapper = editor.dom.getParent(e.target, '.mathlive-wrapper');
                        if (wrapper) {
                            const latex = wrapper.getAttribute('data-latex');
                            if (latex) {
                                editor.selection.select(wrapper);
                                this.openMathLiveModal(latex, wrapper);
                            }
                        }
                    });

                    // Sync ke Livewire tapi JANGAN force commit saat mengetik (mencegah flickering re-render)
                    editor.on('input change undo redo SetContent ExecCommand blur', (e) => {
                        const tmp = document.createElement('div');
                        tmp.innerHTML = editor.getContent({ format: 'html' });
                        tmp.querySelectorAll('.mathlive-wrapper').forEach(el => {
                            const latex = el.getAttribute('data-latex');
                            if (latex) el.replaceWith(document.createTextNode('\\( ' + latex + ' \\)'));
                        });
                        const cleaned = tmp.innerHTML;
                        if (this.content !== cleaned) {
                            this.content = cleaned;
                            if (this.wireModelName && this.$wire) {
                                // .set(..., ..., false) artinya defer (tidak nge-trigger network layer)
                                this.$wire.set(this.wireModelName, cleaned, false);
                                
                                // AUTO-SAVE DEBOUNCE (menyimpan tanpa menunggu blur jika berhenti ngetik)
                                if (this.saveTimeout) { clearTimeout(this.saveTimeout); }
                                
                                const triggerSync = () => {
                                    if (typeof this.$wire.$commit === 'function') {
                                        this.$wire.$commit(); 
                                    } else {
                                        this.$wire.$refresh();
                                    }
                                };

                                if (e.type === 'blur') {
                                    triggerSync();
                                } else {
                                    this.saveTimeout = setTimeout(triggerSync, 2000);
                                }
                            }
                        }
                    });
                }
            });
        },

        openMathLiveModal(initialLatex = '', existingWrapper = null) {
            if (document.getElementById('mathlive-overlay')) return;

            const overlay = document.createElement('div');
            overlay.id = 'mathlive-overlay';
            overlay.className = 'fixed inset-0 bg-black/50 z-[99990] flex items-center justify-center p-4 backdrop-blur-sm';
            overlay.innerHTML = `
                <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl overflow-hidden flex flex-col">
                    <div class="p-4 bg-indigo-600 text-white flex justify-between items-center">
                        <h3 class="font-bold">Editor Rumus (MathLive)</h3>
                        <button type="button" id="close-mathlive" class="hover:text-red-200 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                        </button>
                    </div>
                    <div class="p-6">
                        <p class="text-sm text-gray-500 mb-4">Ketik LaTeX atau gunakan keyboard virtual.</p>
                        <math-field id="mathlive-input" class="w-full text-xl p-4 border border-indigo-200 rounded-lg shadow-inner min-h-[100px]"></math-field>
                    </div>
                    <div class="p-4 bg-gray-50 border-t flex justify-between items-center">
                        <div id="mathlive-delete-container"></div>
                        <button type="button" id="insert-mathlive" class="bg-indigo-600 text-white px-5 py-2 rounded-lg font-bold hover:bg-indigo-700 transition shadow-md">
                            Sisipkan Rumus
                        </button>
                    </div>
                </div>
            `;
            document.body.appendChild(overlay);

            const mathField = document.getElementById('mathlive-input');

            if (initialLatex) {
                mathField.setValue(initialLatex);
                document.getElementById('mathlive-delete-container').innerHTML = `
                    <button type="button" id="delete-mathlive" class="bg-red-50 text-red-600 hover:bg-red-100 px-4 py-2 rounded-lg font-semibold transition flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                        Hapus Rumus
                    </button>
                `;
                document.getElementById('delete-mathlive').addEventListener('click', () => {
                    const ed = this.editorInstance;
                    if (existingWrapper && existingWrapper.parentNode) {
                        existingWrapper.parentNode.removeChild(existingWrapper);
                    } else if (ed) {
                        ed.selection.setContent('');
                    }
                    if (ed) { ed.fire('change'); ed.focus(); }
                    overlay.remove();
                });
            }

            mathField.mathVirtualKeyboardPolicy = 'manual';
            setTimeout(() => {
                mathField.focus();
                if (window.mathVirtualKeyboard) window.mathVirtualKeyboard.show();
            }, 200);

            document.getElementById('close-mathlive').addEventListener('click', () => {
                overlay.remove();
                if (this.editorInstance) this.editorInstance.focus();
            });

            document.getElementById('insert-mathlive').addEventListener('click', () => {
                const latex = mathField.value;
                if (!latex || !latex.trim()) return;

                const html = buildMathSpan(latex.trim(), latexToHtml(latex.trim()));
                const ed = this.editorInstance;
                if (ed) {
                    if (existingWrapper && existingWrapper.parentNode) {
                        // Ganti rumus lama
                        const iframeDoc = ed.getDoc();
                        const tmp = iframeDoc.createElement('div');
                        tmp.innerHTML = html;
                        existingWrapper.replaceWith(tmp.firstChild);
                    } else {
                        // Insert baru via DOM langsung
                        const iframeDoc = ed.getDoc();
                        const rng = ed.selection.getRng();
                        rng.collapse(true);
                        const tmp = iframeDoc.createElement('div');
                        tmp.innerHTML = html;
                        const span = tmp.firstChild;
                        if (span) {
                            rng.insertNode(span);
                            // Tempatkan kursor setelah span
                            const newRng = iframeDoc.createRange();
                            newRng.setStartAfter(span);
                            newRng.collapse(true);
                            ed.selection.setRng(newRng);
                        }
                    }
                    ed.fire('change');
                }
                overlay.remove();
                if (ed) ed.focus();
            });
        }
    }));
});
</script>