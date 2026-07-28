(() => {
    // Shared modal controller used by view-page modules such as forward and read receipts.
    const controller = {
        open: () => {},
        close: () => {},
    };

    window.GakumasMessageModals = controller;

    document.addEventListener('DOMContentLoaded', () => {
        const conversationActionMenu = document.querySelector('[data-conversation-action-menu]');
        const conversationActionToggle = document.querySelector('[data-conversation-action-toggle]');
        const conversationActionPanel = document.querySelector('[data-conversation-action-panel]');
        const modalOpenButtons = Array.from(document.querySelectorAll('[data-modal-open]'));
        const modals = Array.from(document.querySelectorAll('.message-modal'));
        const modalSearchInputs = Array.from(document.querySelectorAll('[data-modal-search]'));
        let activeModalTrigger = null;

        // Each modal list can opt into simple client-side filtering.
        const filterModalSearch = (input) => {
            const listId = input.dataset.modalSearchTarget || '';
            const list = listId ? document.getElementById(listId) : null;

            if (!list) {
                return;
            }

            const rows = Array.from(list.querySelectorAll('[data-modal-search-row]'));
            const emptyState = document.querySelector(`[data-modal-search-empty="${listId}"]`);
            const query = input.value.trim().toLocaleLowerCase();
            let visibleCount = 0;

            rows.forEach((row) => {
                const searchText = (row.dataset.modalSearchText || '').toLocaleLowerCase();
                const isVisible = query === '' || searchText.includes(query);

                row.hidden = !isVisible;
                visibleCount += isVisible ? 1 : 0;
            });

            if (emptyState) {
                emptyState.hidden = visibleCount !== 0;
            }
        };

        const resetModalSearch = (modal) => {
            modal.querySelectorAll('[data-modal-search]').forEach((input) => {
                input.value = '';
                filterModalSearch(input);
            });
        };

        const closeModal = (modal, restoreFocus = true) => {
            if (!modal || modal.hidden) {
                return;
            }

            resetModalSearch(modal);
            modal.hidden = true;
            document.body.classList.remove('message-modal-open');

            if (restoreFocus) {
                activeModalTrigger?.focus();
            }

            activeModalTrigger = null;
        };

        const closeConversationActionMenu = (restoreFocus = false) => {
            if (!conversationActionPanel || !conversationActionToggle) {
                return;
            }

            conversationActionPanel.hidden = true;
            conversationActionToggle.setAttribute('aria-expanded', 'false');

            if (restoreFocus) {
                conversationActionToggle.focus();
            }
        };

        const openModal = (modal, trigger) => {
            if (!modal) {
                return;
            }

            modals.forEach((existingModal) => closeModal(existingModal, false));
            activeModalTrigger = trigger;
            modal.hidden = false;
            document.body.classList.add('message-modal-open');
            closeConversationActionMenu();
            modal.querySelector('button, input, textarea, select, a[href]')?.focus();
        };

        controller.open = openModal;
        controller.close = closeModal;

        modalOpenButtons.forEach((button) => {
            button.addEventListener('click', () => {
                openModal(document.getElementById(button.dataset.modalOpen || ''), button);
            });
        });

        modals.forEach((modal) => {
            modal.querySelectorAll('[data-modal-close]').forEach((closeButton) => {
                closeButton.addEventListener('click', () => closeModal(modal));
            });
        });

        modalSearchInputs.forEach((input) => {
            input.addEventListener('input', () => filterModalSearch(input));
        });

        document.addEventListener('keydown', (event) => {
            if (event.key !== 'Escape') {
                return;
            }

            const openModalElement = modals.find((modal) => !modal.hidden);

            if (openModalElement) {
                closeModal(openModalElement);
            }
        });

        if (conversationActionMenu && conversationActionToggle && conversationActionPanel) {
            // Opens or closes the action panel for conversation-level actions.
            conversationActionToggle.addEventListener('click', () => {
                const shouldOpen = conversationActionPanel.hidden;
                conversationActionPanel.hidden = !shouldOpen;
                conversationActionToggle.setAttribute('aria-expanded', String(shouldOpen));

                if (shouldOpen) {
                    conversationActionPanel.querySelector('[role="menuitem"]')?.focus();
                }
            });

            document.addEventListener('click', (event) => {
                if (!conversationActionMenu.contains(event.target)) {
                    closeConversationActionMenu();
                }
            });

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && !conversationActionPanel.hidden) {
                    closeConversationActionMenu(true);
                }
            });
        }
    });
})();
