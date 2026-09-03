import DocumentService from '@typo3/core/document-service.js';
import Hotkeys, { ModifierKeys } from '@typo3/backend/hotkeys.js';

// Version 1.0

const scope = 'extensionbuilder_typo3/backend-module';

DocumentService.ready().then(() => {
    Hotkeys.register(
        [
            Hotkeys.normalizedCtrlModifierKey,
            ModifierKeys.SHIFT,
            'x'
        ],
        (keyboardEvent) => {
            keyboardEvent.preventDefault();
            const button = document.querySelector('[data-hotkey-action="close"]');
            if (button instanceof HTMLElement) {
                button.click();
            }
        },
        {
            scope: scope,
            allowOnEditables: true,
        }
    );
    Hotkeys.register(
        [
            Hotkeys.normalizedCtrlModifierKey,
            ModifierKeys.SHIFT,
            'a'
        ],
        (keyboardEvent) => {
            keyboardEvent.preventDefault();
            const button = document.querySelector('[data-hotkey-action="add"]');
            if (button instanceof HTMLElement) {
                button.click();
            }
        },
        {
            scope: scope,
            allowOnEditables: true,
        }
    );
    Hotkeys.register(
        [
            Hotkeys.normalizedCtrlModifierKey,
            ModifierKeys.SHIFT,
            's'
        ],
        (keyboardEvent) => {
            keyboardEvent.preventDefault();
            const button = document.querySelector('[data-hotkey-action="save"]');
            if (button instanceof HTMLElement) {
                button.click();
            }
        },
        {
            scope: scope,
            allowOnEditables: true,
        }
    );
    Hotkeys.register(
        [
            Hotkeys.normalizedCtrlModifierKey,
            ModifierKeys.SHIFT,
            'b'
        ],
        (keyboardEvent) => {
            keyboardEvent.preventDefault();
            const button = document.querySelector('[data-hotkey-action="build"]');
            if (button instanceof HTMLElement) {
                button.click();
            }
        },
        {
            scope: scope,
            allowOnEditables: true,
        }
    );
    Hotkeys.setScope(scope);
});