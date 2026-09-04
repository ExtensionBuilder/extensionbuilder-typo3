import RegularEvent from '@typo3/core/event/regular-event.js';
import AjaxRequest from '@typo3/core/ajax/ajax-request.js';

// Developer Info: Beta use in ExtensionController

// ToDo Doku

async function changedBuildFields(el) {
    const config = el.dataset.config;
    const key = el.dataset.key;
    const base = el.dataset.base;
    const fields = el.dataset.fields;

    console.log('BuildFieldsChanged', el);

    if (!config || !key || !base || !fields) { return; }

    try {
        const response = await new AjaxRequest(
            TYPO3.settings.ajaxUrls.extensionbuilder_getFieldsValues
        ).post({
            config: config,
            key: document.getElementById(base + '.' + key).value,
            base: base,
            fields: fields,
        });
        const data = await response.resolve();

        for (const value of JSON.parse(fields)) {
            const target = document.getElementById(base + '.' + value);
            if (target) {
                target.value = data.value[value];
            } else {
                console.warn('Feld nicht gefunden: ', base + '.' + value);
            }
        }
    } catch (error) {
        console.error('AJAX-Fehler', error);

console.error(fields);

    }
}

new RegularEvent('change', function (event) {
    changedBuildFields(event.target);
}).delegateTo(document, '.js-change-buildfields');
