import AjaxRequest from '@typo3/core/ajax/ajax-request.js';

// Version 0.0

let editor = null;
let completionProviderRegistered = false;

let vendorName = '';
let extensionName = '';
let fileName = '';
let language = 'php';

const modal = document.getElementById('modalMonacoEditor');
const editorElement = document.getElementById('monacoeditor');
const saveButton = document.getElementById('save');
const closeButton = document.getElementById('close');

document.querySelectorAll('#myBtn').forEach((button) => {
    button.addEventListener('click', async function (e) {
        e.preventDefault();

        vendorName = this.dataset.vendorname || '';
        extensionName = this.dataset.extensionname || '';
        fileName = this.dataset.filename || '';
        language = this.dataset.language || 'php';

        modal.classList.remove('hidden');

        try {
            const response = await new AjaxRequest(
                TYPO3.settings.ajaxUrls.extensionbuilder_typo3_readDevCode
            ).post({
                vendorName,
                extensionName,
                fileName,
            });

            const data = await response.resolve();
// Resources/Public/JavaScript/Contrib/Monaco-vs-/vs/

//require.config({
//    paths: {
//        vs: TYPO3.settings.extensionBuilder.monacoVsPath
//    }
//});

require.config({
    paths: {
        vs: '/typo3conf/ext/extensionbuilder_typo3/Resources/Public/JavaScript/Contrib/Monaco-vs-0.55.1/vs'
    }
});

//            require.config({
//                paths: {
//                    vs: 'https://cdn.jsdelivr.net/npm/monaco-editor@latest/min/vs',
//                },
//            });

            require(['vs/editor/editor.main'], function () {
                if (editor) {
                    editor.dispose();
                    editor = null;
                }

                if (!completionProviderRegistered) {
                    monaco.languages.registerCompletionItemProvider('php', {
                        provideCompletionItems: () => ({
                            suggestions: [
                                {
                                    label: 'echo',
                                    kind: monaco.languages.CompletionItemKind.Keyword,
                                    insertText: 'echo ',
                                    detail: 'PHP Ausgabe',
                                },
                                {
                                    label: 'isset',
                                    kind: monaco.languages.CompletionItemKind.Function,
                                    insertText: 'isset($var)',
                                    detail: 'Variable prüfen',
                                },
                            ],
                        }),
                    });

                    completionProviderRegistered = true;
                }

                editor = monaco.editor.create(editorElement, {
                    language,
                    value: data.code || '',
                    theme: 'vs-dark',
                    fontSize: 14,
                    fixedOverflowWidgets: false,
                    automaticLayout: true,
                });
            });
        } catch (error) {
            console.error('Error reading file:', error);
        }
    });
});

saveButton.addEventListener('click', async function (e) {
    e.preventDefault();

    if (!editor) {
        return;
    }

    try {
        const response = await new AjaxRequest(
            TYPO3.settings.ajaxUrls.extensionbuilder_typo3_writeDevCode
        ).post({
            vendorName,
            extensionName,
            fileName,
            content: editor.getValue(),
        });

        const data = await response.resolve();
    } catch (error) {
        console.error('Error saving:', error);
    }

    saveButton.blur();
});

function closeEditorModal() {
    if (editor) {
        editor.dispose();
        editor = null;
    }

    modal.classList.add('hidden');
}

closeButton.addEventListener('click', function (e) {
    e.preventDefault();
    closeEditorModal();
});

document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
        e.preventDefault();
        closeEditorModal();
    }
});