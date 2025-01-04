require(['TYPO3/CMS/Backend/Modal'], function(Modal) {
    Modal.show(
        'Mein Titel', // Titel des Modals
        'Hier ist der Inhalt des Modals.', // Inhalt des Modals
        Severity.info, // Schweregrad: notice, info, warning, error
        [
            {
                text: 'Ok',
                btnClass: 'btn-primary',
                name: 'ok',
                active: true,
                trigger: function() {
                    console.log('OK wurde geklickt');
                    Modal.dismiss(); // Modal schließen
                }
            },
            {
                text: 'Abbrechen',
                btnClass: 'btn-default',
                trigger: function() {
                    console.log('Abbrechen wurde geklickt');
                    Modal.dismiss(); // Modal schließen
                }
            }
        ]
    );
});
