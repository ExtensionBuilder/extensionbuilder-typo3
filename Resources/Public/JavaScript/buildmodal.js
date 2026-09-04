import AjaxRequest from '@typo3/core/ajax/ajax-request.js';
import DocumentService from '@typo3/core/document-service.js';
import Modal from '@typo3/backend/modal.js';
import Severity from '@typo3/backend/severity.js';

// Developer Info: Beta modal build info ExtensionController

DocumentService.ready().then(() => {
  document.addEventListener('click', async (event) => {
    const button = event.target.closest('[data-extensionbuilder-build-button="1"]');

    if (!button) {
      return;
    }

    event.preventDefault();

    const vendorName = button.dataset.vendorName || '';
    const extensionName = button.dataset.extensionName || '';

    if (!vendorName || !extensionName) {
      Modal.alert(
        'Build nicht möglich',
        'vendorName oder extensionName fehlt am Build-Button.',
        Severity.error
      );
      return;
    }

    const modal = Modal.advanced({
      title: 'Build läuft',
      content: `
        <div class="extensionbuilder-build-modal">
          <p data-extensionbuilder-build-status>Build wird vorbereitet...</p>

          <ol>
            <li data-extensionbuilder-build-step="prepare">Vorbereitung...</li>
            <li data-extensionbuilder-build-step="request">Build-Request...</li>
            <li data-extensionbuilder-build-step="response">Antwort verarbeiten...</li>
            <li data-extensionbuilder-build-step="finish">Abschluss...</li>
          </ol>
        </div>
      `,
      severity: Severity.info,
      staticBackdrop: true,
      buttons: []
    });

    const modalElement = modal[0] || modal;

    const setStatus = (text) => {
      const status = modalElement.querySelector('[data-extensionbuilder-build-status]');
      if (status) {
        status.textContent = text;
      }
    };

    const markStep = (stepName, text) => {
      const step = modalElement.querySelector(`[data-extensionbuilder-build-step="${stepName}"]`);
      if (step) {
        step.textContent = text;
      }
    };

    try {
      setStatus('Build wird gestartet...');
      markStep('prepare', '✓ Vorbereitung abgeschlossen');

      const ajaxUrl = TYPO3.settings.ajaxUrls.extensionbuilder_typo3_build;

      if (!ajaxUrl) {
        throw new Error('AJAX URL "extensionbuilder_typo3_build" wurde nicht gefunden.');
      }

      markStep('request', 'Build-Request läuft...');

    const buildUrl = new URL(ajaxUrl, window.location.origin);
    buildUrl.searchParams.set('vendorName', vendorName);
    buildUrl.searchParams.set('extensionName', extensionName);

    const response = await new AjaxRequest(buildUrl.toString()).post({});

console.log("Log 9" + response);

      markStep('request', '✓ Build-Request abgeschlossen');
      markStep('response', 'Antwort wird verarbeitet...');

console.log("Log 10");
console.log(response);

      const data = await response.resolve();

console.log("Log 11");

      if (data.success) {

console.log("Log 12");

        markStep('response', '✓ Antwort erfolgreich');
        markStep('finish', '✓ Build erfolgreich abgeschlossen');
        setStatus(data.message || 'Build erfolgreich abgeschlossen.');

        Modal.dismiss();

        Modal.confirm(
          'Build erfolgreich',
          data.message || 'Die Extension wurde erfolgreich gebaut.',
          Severity.ok,
          [
            {
              text: 'Seite neu laden',
              active: true,
              btnClass: 'btn-primary',
              trigger: () => {
                window.location.reload();
              }
            },
            {
              text: 'Schließen',
              btnClass: 'btn-default',
              trigger: (event, modal) => {
                modal.hideModal();
              }
            }
          ]
        );

        return;
      }

      markStep('response', 'Antwort enthält Fehler');
      markStep('finish', 'Build fehlgeschlagen');
      setStatus(data.message || 'Build fehlgeschlagen.');


      Modal.dismiss();

      Modal.confirm(
        'Build fehlgeschlagen',
        data.message || 'Beim Build ist ein Fehler aufgetreten.',
        Severity.error,
        [
          {
            text: 'Schließen',
            active: true,
            btnClass: 'btn-primary',
            trigger: (event, modal) => {
              modal.hideModal();
            }
          }
        ]
      );
    } catch (error) {
      markStep('finish', 'Build-Fehler');
      setStatus(error?.message || 'Der Build konnte nicht ausgeführt werden.');

console.log("Catch - Modal.dismiss()");

      Modal.dismiss();

      Modal.confirm(
        'Build-Fehler',
        error?.message || 'Der Build konnte nicht ausgeführt werden.',
        Severity.error,
        [
          {
            text: 'Schließen',
            active: true,
            btnClass: 'btn-primary',
            trigger: (event, modal) => {
              modal.hideModal();
            }
          }
        ]
      );
    }
  });
});