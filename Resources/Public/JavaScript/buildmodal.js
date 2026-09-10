import AjaxRequest from '@typo3/core/ajax/ajax-request.js';
import DocumentService from '@typo3/core/document-service.js';
import Modal from '@typo3/backend/modal.js';
import Severity from '@typo3/backend/severity.js';

// Developer Info: Build modal for Extension Builder for TYPO3

DocumentService.ready().then(() => {
    document.addEventListener('click', async (event) => {
        const buildButton = event.target.closest(
            '[data-extensionbuilder-build-button="1"]'
        );

        if (!buildButton) {
            return;
        }

        event.preventDefault();

        const vendorName = buildButton.dataset.vendorName || '';
        const extensionName = buildButton.dataset.extensionName || '';

        if (!vendorName || !extensionName) {
            Modal.alert(
                'Build error',
                'Vendor name or extension name is missing.',
                Severity.error
            );
            return;
        }

        openBuildModal(vendorName, extensionName);
    });
});


async function openBuildModal(vendorName, extensionName) {
    const content = document.createElement('div');
    content.className = 'extensionbuilder-build-modal';

    /*
     * Extension information
     */
    const extensionInformation = document.createElement('div');
    extensionInformation.className = 'mb-3';

    extensionInformation.innerHTML = `
        <div class="d-flex flex-wrap gap-3">
            <div>
                <strong>Vendor:</strong>
                <span></span>
            </div>

            <div>
                <strong>Extension:</strong>
                <span></span>
            </div>
        </div>
    `;

    const informationValues =
        extensionInformation.querySelectorAll('span');

    informationValues[0].textContent = vendorName;
    informationValues[1].textContent = extensionName;

    /*
     * Status
     */
    const status = document.createElement('div');

    status.className = 'alert alert-info mb-3';
    status.setAttribute('role', 'status');
    status.dataset.extensionbuilderBuildStatus = '1';

    status.style.whiteSpace = 'pre-wrap';
    status.style.overflowWrap = 'anywhere';

    status.textContent = 'Preparing build...';

    /*
     * Build steps
     */
    const steps = document.createElement('div');
    steps.className = 'list-group mb-3';

    const prepareStep = createBuildStep(
        'prepare',
        'Preparing build'
    );

    const requestStep = createBuildStep(
        'request',
        'Waiting for build request'
    );

    const responseStep = createBuildStep(
        'response',
        'Waiting for response'
    );

    const finishStep = createBuildStep(
        'finish',
        'Waiting for completion'
    );

    steps.appendChild(prepareStep);
    steps.appendChild(requestStep);
    steps.appendChild(responseStep);
    steps.appendChild(finishStep);

    /*
     * Debug block
     */
    const debugContainer = document.createElement('div');

    debugContainer.className = 'alert alert-secondary mt-3 d-none';
    debugContainer.dataset.extensionbuilderBuildDebug = '1';

    const debugHeadline = document.createElement('strong');
    debugHeadline.textContent = 'Debug';

    const debugOutput = document.createElement('pre');

    debugOutput.className = 'mt-2 mb-0';
    debugOutput.style.whiteSpace = 'pre-wrap';
    debugOutput.style.overflowWrap = 'anywhere';
    debugOutput.style.maxHeight = '400px';
    debugOutput.style.overflow = 'auto';

    debugContainer.appendChild(debugHeadline);
    debugContainer.appendChild(debugOutput);

    /*
     * Error details
     */
    const errorContainer = document.createElement('div');

    errorContainer.className = 'alert alert-danger mt-3 d-none';
    errorContainer.dataset.extensionbuilderBuildError = '1';

    const errorOutput = document.createElement('pre');

    errorOutput.className = 'mb-0';
    errorOutput.style.whiteSpace = 'pre-wrap';
    errorOutput.style.overflowWrap = 'anywhere';
    errorOutput.style.maxHeight = '500px';
    errorOutput.style.overflow = 'auto';

    errorContainer.appendChild(errorOutput);

    /*
     * Assemble modal content
     */
    content.appendChild(extensionInformation);
    content.appendChild(status);
    content.appendChild(steps);
    content.appendChild(debugContainer);
    content.appendChild(errorContainer);

    /*
     * Open TYPO3 modal
     */
    const modal = Modal.advanced({
        title: 'Build Extension',
        content: content,
        severity: Severity.info,
        staticBackdrop: true,
        buttons: []
    });

    /*
     * Helper functions
     */
    const setStatus = (message, type = 'info') => {
        status.className = `alert alert-${type} mb-3`;
        status.textContent = message;
    };

    const setStep = (element, message, type = '') => {
        element.className = 'list-group-item';

        if (type === 'success') {
            element.classList.add('list-group-item-success');
        }

        if (type === 'danger') {
            element.classList.add('list-group-item-danger');
        }

        if (type === 'info') {
            element.classList.add('list-group-item-info');
        }

        element.textContent = message;
    };

    const showDebug = (debug) => {
        if (
            debug === undefined ||
            debug === null ||
            debug === ''
        ) {
            return;
        }

        debugContainer.classList.remove('d-none');
        debugOutput.textContent = formatDebug(debug);
    };

    const showError = (data, fallbackMessage = '') => {
        const errorText = buildErrorText(
            data,
            fallbackMessage
        );

        if (!errorText) {
            return;
        }

        errorContainer.classList.remove('d-none');
        errorOutput.textContent = errorText;
    };

    /*
     * Start
     */
    try {
        setStep(
            prepareStep,
            '✓ Preparation completed',
            'success'
        );

        setStatus(
            'Build is being started...',
            'info'
        );

        /*
         * AJAX URL
         */
        const ajaxUrl =
            TYPO3?.settings?.ajaxUrls?.extensionbuilder_typo3_build;

        if (!ajaxUrl) {
            throw new Error(
                'AJAX URL "extensionbuilder_typo3_build" was not found.'
            );
        }

        const requestUrl = new URL(
            ajaxUrl,
            window.location.origin
        );

        requestUrl.searchParams.set(
            'vendorName',
            vendorName
        );

        requestUrl.searchParams.set(
            'extensionName',
            extensionName
        );

        /*
         * Build request
         */
        setStep(
            requestStep,
            'Build request is running...',
            'info'
        );

        setStatus(
            'Build request is running...',
            'info'
        );

        const response = await new AjaxRequest(
            requestUrl.toString()
        ).post({});

        setStep(
            requestStep,
            '✓ Build request completed',
            'success'
        );

        /*
         * Process response
         */
        setStep(
            responseStep,
            'Processing build service response...',
            'info'
        );

        setStatus(
            'Processing build service response...',
            'info'
        );

        const data = await response.resolve();

        /*
         * DEBUG
         *
         * Supported:
         *
         * {
         *     "debug": "..."
         * }
         *
         * and optionally:
         *
         * {
         *     "data": {
         *         "debug": "..."
         *     }
         * }
         */
        const debug =
            data?.debug ??
            data?.data?.debug ??
            data?.result?.debug ??
            null;

        showDebug(debug);

        /*
         * Successful response
         */
        if (data?.success === true) {
            setStep(
                responseStep,
                '✓ Response successfully processed',
                'success'
            );

            setStep(
                finishStep,
                '✓ Build completed successfully',
                'success'
            );

            setStatus(
                data?.message ||
                'The extension was built successfully.',
                'success'
            );

            addFooterButtons(
                modal,
                true
            );

            return;
        }

        /*
         * Build service responded,
         * but reported a build error.
         */
        setStep(
            responseStep,
            '✗ Response contains errors',
            'danger'
        );

        setStep(
            finishStep,
            '✗ Build failed',
            'danger'
        );

        showError(
            data,
            'An error occurred during the build.'
        );

        setStatus(
            data?.message ||
            'An error occurred during the build.',
            'danger'
        );

        addFooterButtons(
            modal,
            false
        );
    } catch (error) {
        console.error(
            'Extension Builder build error:',
            error
        );

        setStep(
            responseStep,
            '✗ Response contains errors',
            'danger'
        );

        setStep(
            finishStep,
            '✗ Build failed',
            'danger'
        );

        let errorData = null;

        /*
         * TYPO3 AjaxRequest error response
         */
        if (error?.response) {
            try {
                const responseText =
                    await error.response.text();

                if (responseText) {
                    try {
                        errorData =
                            JSON.parse(responseText);
                    } catch (jsonError) {
                        errorData = {
                            message:
                                error?.message ||
                                'The build could not be executed.',
                            response: responseText
                        };
                    }
                }
            } catch (responseError) {
                console.error(
                    'Could not read build response:',
                    responseError
                );
            }
        }

        /*
         * Fallback
         */
        if (!errorData) {
            errorData = {
                message:
                    error?.message ||
                    'The build could not be executed.'
            };
        }

        /*
         * Show debug from failed HTTP response.
         */
        const debug =
            errorData?.debug ??
            errorData?.data?.debug ??
            errorData?.result?.debug ??
            null;

        showDebug(debug);

        showError(
            errorData,
            error?.message
        );

        setStatus(
            errorData?.message ||
            error?.message ||
            'The build could not be executed.',
            'danger'
        );

        addFooterButtons(
            modal,
            false
        );
    }
}


function createBuildStep(name, text) {
    const element = document.createElement('div');

    element.className = 'list-group-item';
    element.dataset.extensionbuilderBuildStep = name;
    element.textContent = text;

    return element;
}


function formatDebug(debug) {
    if (typeof debug === 'string') {
        return debug;
    }

    if (
        typeof debug === 'number' ||
        typeof debug === 'boolean'
    ) {
        return String(debug);
    }

    try {
        return JSON.stringify(
            debug,
            null,
            2
        );
    } catch (error) {
        return String(debug);
    }
}


function buildErrorText(data, fallbackMessage = '') {
    const output = [];

    const message =
        data?.message ||
        fallbackMessage;

    if (message) {
        output.push(message);
    }

    if (data?.file) {
        output.push(
            `File: ${normalizeFilePath(data.file)}`
        );
    }

    if (
        data?.line !== undefined &&
        data?.line !== null &&
        data?.line !== ''
    ) {
        output.push(
            `Line: ${data.line}`
        );
    }

    /*
     * Non-JSON server response.
     */
    if (data?.response) {
        output.push(
            `Response:\n${data.response}`
        );
    }

    return output.join('\n\n');
}


function normalizeFilePath(file) {
    if (
        !file ||
        typeof file !== 'string'
    ) {
        return '';
    }

    /*
     * Example:
     *
     * /var/www/vhosts/.../packages/
     * extensionbuilder_typo3_core/Classes/Test.php
     *
     * becomes:
     *
     * extensionbuilder_typo3_core/Classes/Test.php
     */
    const marker =
        'extensionbuilder_typo3_core/';

    const markerPosition =
        file.indexOf(marker);

    if (markerPosition !== -1) {
        return file.substring(markerPosition);
    }

    return file;
}


function addFooterButtons(modal, successful) {
    const modalElement =
        modal?.[0] ||
        modal;

    if (!modalElement) {
        return;
    }

    const footer =
        modalElement.querySelector?.(
            '.modal-footer'
        );

    if (!footer) {
        return;
    }

    footer.innerHTML = '';

    /*
     * Reload after successful build.
     */
    if (successful) {
        const reloadButton =
            document.createElement('button');

        reloadButton.type = 'button';
        reloadButton.className =
            'btn btn-primary';

        reloadButton.textContent =
            'Reload';

        reloadButton.addEventListener(
            'click',
            () => {
                window.location.reload();
            }
        );

        footer.appendChild(
            reloadButton
        );
    }

    /*
     * Close button
     */
    const closeButton =
        document.createElement('button');

    closeButton.type = 'button';

    closeButton.className =
        successful
            ? 'btn btn-default'
            : 'btn btn-primary';

    closeButton.textContent =
        'Close';

    closeButton.addEventListener(
        'click',
        () => {
            Modal.dismiss();
        }
    );

    footer.appendChild(
        closeButton
    );
}