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

    if (!vendorName) {
      Modal.alert(
        'Build not possible',
        'Vendor name is missing from the build button.',
        Severity.error
      );
      return;
    }

    if (!extensionName) {
      Modal.alert(
        'Build not possible',
        'Extension name is missing from the build button.',
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
    status.textContent = 'Build is being prepared...';

    const steps = document.createElement('ol');
    steps.className = 'mb-0';

    const createStep = (name, text) => {
      const item = document.createElement('li');
      item.dataset.extensionbuilderBuildStep = name;
      item.textContent = text;
      steps.appendChild(item);
      return item;
    };

    const prepareStep = createStep('prepare', 'Preparation...');
    const requestStep = createStep('request', 'Build-Request...');
    const responseStep = createStep('response', 'Processing response...');
    const finishStep = createStep('finish', 'Completion...');

    content.appendChild(info);
    content.appendChild(status);
    content.appendChild(steps);

    const modal = Modal.advanced({
      title: 'Extension Builder for TYPO3',
      content,
      severity: Severity.info,
      staticBackdrop: true,
      buttons: []
    });

    const modalElement = modal[0] || modal;

    const setStatus = (text, type = 'info') => {
      status.className = `alert alert-${type} mb-3`;
      status.style.whiteSpace = 'pre-wrap';
      status.style.maxHeight = '500px';
      status.style.overflow = 'auto';
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
      prepareStep.textContent = '✓ Preparation completed';
      setStatus('Starting build...');

      const ajaxUrl = TYPO3.settings.ajaxUrls.extensionbuilder_typo3_build;

      if (!ajaxUrl) {
        throw new Error(
          'AJAX URL "extensionbuilder_typo3_build" was not found.'
        );
      }

      requestStep.textContent = 'Build request in progress...';

      const buildUrl = new URL(ajaxUrl, window.location.origin);
      buildUrl.searchParams.set('vendorName', vendorName);
      buildUrl.searchParams.set('extensionName', extensionName);

      const response = await new AjaxRequest(buildUrl.toString()).post({});


      requestStep.textContent = '✓ Build request completed';
      responseStep.textContent = 'Processing response...';
      setStatus('Processing build service response...');

      const data = await response.resolve();

      if (data?.success) {
        responseStep.textContent = '✓ Response received successfully';
        finishStep.textContent = '✓ Build completed successfully';

        setStatus(
          data.message || 'The extension was built successfully.',
          'success'
        );

        addFooterButtons(true);
        return;
      }

      responseStep.textContent = '✗ Response contains errors';
      finishStep.textContent = '✗ Build failed';

      setStatus(
        data?.message || 'An error occurred during the build.',
        'danger'
      );

      addFooterButtons(false);
    } catch (error) {
        finishStep.textContent = '✗ Build error';

        let errorMessage =
            error?.message ||
            'The build could not be completed.';

        if (error?.response) {
            try {
                const responseText = await error.response.text();

                if (responseText) {
                    const data = JSON.parse(responseText)
                    const message = data?.message || 'no message';
                    const file = (data?.file || 'Unknown file').replace(/^.*?(extensionbuilder_typo3_core\/)/, '$1');
                    const line = data?.line || 'no message';

                    errorMessage += `\n\n${message}\n\n`;
                    errorMessage += `File: ${file}\n`;
                    errorMessage += `Line: ${line}\n`;
                }
            } catch (responseError) {
                console.error(
                    'Could not read AJAX response:',
                    responseError
                );
            }
        }

        setStatus(errorMessage, 'danger');

        addFooterButtons(false);
    }
  });
});
