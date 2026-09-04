import DocumentService from '@typo3/core/document-service.js';
import Modal from '@typo3/backend/modal.js';
import Severity from '@typo3/backend/severity.js';

// Developer Info: Beta maintenancemodal in ExtensionController

DocumentService.ready().then(() => {
  const cfg = globalThis.TYPO3?.settings?.myext?.maintenance;

  if (!cfg?.active) return;

  // optional: nur einmal pro Tab/Session
  const key = 'myext:maintenanceModalShown';
//  if (sessionStorage.getItem(key)) return;

//  sessionStorage.setItem(key, '1');

  Modal.confirm(
    'Wartung',
    cfg.message || 'Wartung aktiv.',
    Severity.warning,
    [
      {
        text: 'OK',
        active: true,
        btnClass: 'btn-primary',
        trigger: (e, modal) => modal.hideModal(),
      },
    ]
  );
});