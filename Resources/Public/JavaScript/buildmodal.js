import AjaxRequest from '@typo3/core/ajax/ajax-request.js';
import DocumentService from '@typo3/core/document-service.js';
import Modal from '@typo3/backend/modal.js';
import Severity from '@typo3/backend/severity.js';

// Developer Info: Build modal for ExtensionController

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

    const content = document.createElement('div');
    content.className = 'extensionbuilder-build-modal';

    const info = document.createElement('div');
    info.className = 'mb-3';

    const vendorLine = document.createElement('div');
    vendorLine.innerHTML = '<strong>Vendor:</strong> ';

    const vendorValue = document.createElement('span');
    vendorValue.textContent = vendorName;
    vendorLine.appendChild(vendorValue);

    const extensionLine = document.createElement('div');
    extensionLine.innerHTML = '<strong>Extension:</strong> ';

    const extensionValue = document.createElement('span');
    extensionValue.textContent = extensionName;
    extensionLine.appendChild(extensionValue);

    info.appendChild(vendorLine);
    info.appendChild(extensionLine);

    const status = document.createElement('div');
    status.className = 'alert alert-info mb-3';
    status.setAttribute('role', 'status');
    status.textContent = 'Build wird vorbereitet...';

    const steps = document.createElement('ol');
    steps.className = 'mb-0';

    const createStep = (name, text) => {
      const item = document.createElement('li');
      item.dataset.extensionbuilderBuildStep = name;
      item.textContent = text;
      steps.appendChild(item);
      return item;
    };

    const prepareStep = createStep('prepare', 'Vorbereitung...');
    const requestStep = createStep('request', 'Build-Request...');
    const responseStep = createStep('response', 'Antwort verarbeiten...');
    const finishStep = createStep('finish', 'Abschluss...');

    content.appendChild(info);
    content.appendChild(status);
    content.appendChild(steps);

    const modal = Modal.advanced({
      title: 'Extension Build',
      content,
      severity: Severity.info,
      staticBackdrop: true,
      buttons: []
    });

    const modalElement = modal[0] || modal;

    const setStatus = (text, type = 'info') => {
      status.className = `alert alert-${type} mb-3`;
      status.textContent = text;
    };

    const addFooterButtons = (success) => {
      const footer = modalElement.querySelector('.modal-footer');

      if (!footer) {
        return;
      }

      footer.innerHTML = '';

      if (success) {
        const reloadButton = document.createElement('button');
        reloadButton.type = 'button';
        reloadButton.className = 'btn btn-primary';
        reloadButton.textContent = 'Seite neu laden';
        reloadButton.addEventListener('click', () => {
          window.location.reload();
        });
        footer.appendChild(reloadButton);
      }

      const closeButton = document.createElement('button');
      closeButton.type = 'button';
      closeButton.className = success ? 'btn btn-default' : 'btn btn-primary';
      closeButton.textContent = 'Schließen';

      closeButton.addEventListener('click', () => {
        if (typeof modalElement.hideModal === 'function') {
          modalElement.hideModal();
        } else {
          Modal.dismiss();
        }
      });

      footer.appendChild(closeButton);
    };

    try {
      prepareStep.textContent = '✓ Vorbereitung abgeschlossen';
      setStatus('Build wird gestartet...');

      const ajaxUrl = TYPO3.settings.ajaxUrls.extensionbuilder_typo3_build;

      if (!ajaxUrl) {
        throw new Error(
          'AJAX URL "extensionbuilder_typo3_build" wurde nicht gefunden.'
        );
      }

      requestStep.textContent = 'Build-Request läuft...';

      const buildUrl = new URL(ajaxUrl, window.location.origin);
      buildUrl.searchParams.set('vendorName', vendorName);
      buildUrl.searchParams.set('extensionName', extensionName);

      const response = await new AjaxRequest(buildUrl.toString()).post({});

      requestStep.textContent = '✓ Build-Request abgeschlossen';
      responseStep.textContent = 'Antwort wird verarbeitet...';
      setStatus('Antwort des Build-Service wird verarbeitet...');

      const data = await response.resolve();

      if (data?.success) {
        responseStep.textContent = '✓ Antwort erfolgreich';
        finishStep.textContent = '✓ Build erfolgreich abgeschlossen';

        setStatus(
          data.message || 'Die Extension wurde erfolgreich gebaut.',
          'success'
        );

        addFooterButtons(true);
        return;
      }

      responseStep.textContent = '✗ Antwort enthält Fehler';
      finishStep.textContent = '✗ Build fehlgeschlagen';

      setStatus(
        data?.message || 'Beim Build ist ein Fehler aufgetreten.',
        'danger'
      );

      addFooterButtons(false);
    } catch (error) {
      finishStep.textContent = '✗ Build-Fehler';

      setStatus(
        error?.message || 'Der Build konnte nicht ausgeführt werden.',
        'danger'
      );

      addFooterButtons(false);
    }
  });
});
