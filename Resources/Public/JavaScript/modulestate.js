import DocumentService from '@typo3/core/document-service.js';
import RegularEvent from '@typo3/core/event/regular-event.js';
import Icons from '@typo3/backend/icons.js';

// Developer Info: Store the module status for the BackendController.

const STORAGE_KEY = 'extensionbuilder_typo3.uiState.v1';

DocumentService.ready().then(() => {
    const moduleKey = getCurrentModuleKey();

    saveCurrentModule(moduleKey);
    restoreCollapseStates(moduleKey);
    initializeCollapseIcons();

    new RegularEvent('shown.bs.collapse', (event) => {
        handleCollapseChanged(event, moduleKey, true);
    }).bindTo(document);

    new RegularEvent('hidden.bs.collapse', (event) => {
        handleCollapseChanged(event, moduleKey, false);
    }).bindTo(document);

    new RegularEvent('click', (event) => {
        event.preventDefault();
        resetCurrentModuleState(moduleKey);
    }).delegateTo(document, '.js-eb-reset-module-state');

    new RegularEvent('click', (event) => {
        event.preventDefault();
        openLastModule();
    }).delegateTo(document, '.js-eb-open-last-module');
});

function getCurrentModuleKey() {
    const moduleElement = document.querySelector('[data-eb-module]');

    if (moduleElement && moduleElement.dataset.ebModule) {
        return moduleElement.dataset.ebModule;
    }

    return window.location.pathname + window.location.search;
}

function getState() {
    try {
        const rawState = window.localStorage.getItem(STORAGE_KEY);

        if (!rawState) {
            return createEmptyState();
        }

        const state = JSON.parse(rawState);

        return normalizeState(state);
    } catch (error) {
        return createEmptyState();
    }
}

function saveState(state) {
    try {
        window.localStorage.setItem(STORAGE_KEY, JSON.stringify(normalizeState(state)));
    } catch (error) {
        // localStorage can be disabled. Collapse must still work.
    }
}

function createEmptyState() {
    return {
        currentModule: '',
        currentUrl: '',
        modules: {},
    };
}

function normalizeState(state) {
    if (!state || typeof state !== 'object') {
        state = createEmptyState();
    }

    if (typeof state.currentModule !== 'string') {
        state.currentModule = '';
    }

    if (typeof state.currentUrl !== 'string') {
        state.currentUrl = '';
    }

    if (!state.modules || typeof state.modules !== 'object') {
        state.modules = {};
    }

    return state;
}

function getModuleState(state, moduleKey) {
    if (!state.modules[moduleKey] || typeof state.modules[moduleKey] !== 'object') {
        state.modules[moduleKey] = {
            url: '',
            collapses: {},
        };
    }

    if (!state.modules[moduleKey].collapses || typeof state.modules[moduleKey].collapses !== 'object') {
        state.modules[moduleKey].collapses = {};
    }

    return state.modules[moduleKey];
}

function saveCurrentModule(moduleKey) {
    const state = getState();
    const moduleState = getModuleState(state, moduleKey);

    state.currentModule = moduleKey;
    state.currentUrl = window.location.href;
    moduleState.url = window.location.href;

    saveState(state);
}

function restoreCollapseStates(moduleKey) {
    const state = getState();
    const moduleState = getModuleState(state, moduleKey);

    document.querySelectorAll('.collapse[id]').forEach((collapseElement) => {
        const collapseKey = getCollapseKey(collapseElement);

        if (typeof moduleState.collapses[collapseKey] === 'undefined') {
            updateCollapseControls(
                collapseElement,
                collapseElement.classList.contains('show')
            );
            return;
        }

        const isOpen = moduleState.collapses[collapseKey] === true;

        setCollapseDomState(collapseElement, isOpen);
        updateCollapseControls(collapseElement, isOpen);
    });
}

function handleCollapseChanged(event, moduleKey, isOpen) {
    const collapseElement = event.target;

    if (!collapseElement || !collapseElement.id) {
        return;
    }

    const collapseKey = getCollapseKey(collapseElement);

    const state = getState();
    const moduleState = getModuleState(state, moduleKey);

    state.currentModule = moduleKey;
    state.currentUrl = window.location.href;
    moduleState.url = window.location.href;
    moduleState.collapses[collapseKey] = isOpen;

    saveState(state);

    updateCollapseControls(collapseElement, isOpen);
}

function initializeCollapseIcons() {
    document.querySelectorAll('.collapse[id]').forEach((collapseElement) => {
        const isOpen = collapseElement.classList.contains('show');

        updateCollapseControls(collapseElement, isOpen);
    });
}

function setCollapseDomState(collapseElement, isOpen) {
    collapseElement.classList.remove('collapsing');

    collapseElement.classList.toggle('show', isOpen);

    updateCollapseControls(collapseElement, isOpen);
}

function updateCollapseControls(collapseElement, isOpen) {
    const controls = getCollapseControls(collapseElement.id);

    controls.forEach((control) => {
        control.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        control.classList.toggle('collapsed', !isOpen);

        updateCollapseIcon(control, isOpen);
    });
}

function updateCollapseIcon(control, isOpen) {
    const iconContainer = control.querySelector('.collapseIcon');

    if (!iconContainer) {
        return;
    }

    Icons.getIcon(
        isOpen ? 'actions-view-list-collapse' : 'actions-view-list-expand',
        Icons.sizes.small
    ).then((markup) => {
        iconContainer.innerHTML = '';
        iconContainer.appendChild(
            document.createRange().createContextualFragment(markup)
        );
    });
}

function getCollapseControls(collapseId) {
    const escapedId = escapeSelector(collapseId);

    return document.querySelectorAll(
        `[data-bs-target="#${escapedId}"], [href="#${escapedId}"]`
    );
}

function getCollapseKey(collapseElement) {
    if (collapseElement.dataset.ebCollapseKey) {
        return collapseElement.dataset.ebCollapseKey;
    }

    if (collapseElement.dataset.table) {
        return collapseElement.dataset.table;
    }

    return collapseElement.id;
}

function resetCurrentModuleState(moduleKey) {
    const state = getState();

    if (state.modules[moduleKey]) {
        state.modules[moduleKey].collapses = {};
    }

    saveState(state);

    window.location.reload();
}

function openLastModule() {
    const state = getState();

    if (state.currentUrl) {
        window.location.href = state.currentUrl;
    }
}

function escapeSelector(value) {
    if (window.CSS && typeof window.CSS.escape === 'function') {
        return window.CSS.escape(value);
    }

    return value.replace(/([ #;?%&,.+*~':"!^$[\]()=>|/@])/g, '\\$1');
}