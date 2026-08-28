import { Tab } from 'bootstrap';

const initializeExtensionBuilderTabs = () => {
    document
        .querySelectorAll('.eb-build-fields [data-bs-toggle="tab"]')
        .forEach((trigger) => {
            Tab.getOrCreateInstance(trigger);
        });
};

initializeExtensionBuilderTabs();

document.addEventListener('click', (event) => {
    const trigger = event.target.closest(
        '.eb-build-fields [data-bs-toggle="tab"]'
    );

    if (!trigger) {
        return;
    }

    event.preventDefault();

    Tab.getOrCreateInstance(trigger).show();
});