document.addEventListener('DOMContentLoaded', () => {
    // Controls the search box in compose.php
    const recipientSearch = document.querySelector('[data-recipient-search]');
    const recipientRows = Array.from(document.querySelectorAll('[data-recipient-row]'));
    const recipientNoResults = document.querySelector('[data-recipient-no-results]');
    const messageInput = document.querySelector('[data-message-input]');
    const characterCount = document.querySelector('[data-message-character-count]');
    const conversationThread = document.querySelector('[data-conversation-thread]');

    if (recipientSearch && recipientRows.length > 0 && recipientNoResults) {
        recipientSearch.addEventListener('input', () => {
            const query = recipientSearch.value.trim().toLocaleLowerCase();
            let visibleCount = 0;

            recipientRows.forEach((row) => {
                // Check each reci[ient row]
                const searchText = (row.dataset.recipientSearch || '').toLocaleLowerCase();
                const isVisible = query === '' || searchText.includes(query);

                //If matched, stay the row visible, and vice versa
                row.hidden = !isVisible;
                visibleCount += isVisible ? 1 : 0;
            });

            recipientNoResults.hidden = visibleCount !== 0;
        });
    }

    // Controls the textarea in the chat page
    if (messageInput) {
        const updateMessageInput = () => {
            messageInput.style.height = 'auto';
            messageInput.style.height = `${Math.min(messageInput.scrollHeight, 144)}px`;

            if (characterCount) {
                characterCount.textContent = `${messageInput.value.length} / 5000`;
            }
        };

        messageInput.addEventListener('input', updateMessageInput);
        updateMessageInput();
    }

    // The conversation page will auto scroll to the bottom of the chat
    if (conversationThread) {
        conversationThread.scrollTop = conversationThread.scrollHeight;
    }

    // The user clicks the pencil button, and hides the normal message text, shows the edit form
    document.querySelectorAll('[data-message-edit-open]').forEach((editButton) => {
        editButton.addEventListener('click', () => {
            const bubble = editButton.closest('.chat-message-bubble');
            const messageBody = bubble?.querySelector('[data-message-body]');
            const editForm = bubble?.querySelector('[data-message-edit-form]');
            const textarea = editForm?.querySelector('textarea');

            if (!messageBody || !editForm || !textarea) {
                return;
            }

            messageBody.hidden = true;
            editForm.hidden = false;
            editButton.hidden = true;
            textarea.focus();
            textarea.setSelectionRange(textarea.value.length, textarea.value.length);
        });
    });

    // Clicks cancel when edit, hides the edit form and shows the original message again
    document.querySelectorAll('[data-message-edit-cancel]').forEach((cancelButton) => {
        cancelButton.addEventListener('click', () => {
            const bubble = cancelButton.closest('.chat-message-bubble');
            const messageBody = bubble?.querySelector('[data-message-body]');
            const editForm = bubble?.querySelector('[data-message-edit-form]');
            const editButton = bubble?.querySelector('[data-message-edit-open]');

            if (messageBody) {
                messageBody.hidden = false;
            }

            if (editForm) {
                editForm.hidden = true;
            }

            if (editButton) {
                editButton.hidden = false;
            }
        });
    });

    // Control the inbox search bar and filter dropdown
    const searchInput = document.querySelector('[data-conversation-search]');
    const searchFilter = document.querySelector('[data-conversation-search-filter]');
    const filterMenu = document.querySelector('[data-search-filter-menu]');
    const filterOptions = document.querySelector('.messages-search-filter-options');
    const filterLabel = document.querySelector('[data-filter-label]');
    const optionButtons = Array.from(document.querySelectorAll('[data-filter-option]'));
    const inboxViewButtons = Array.from(document.querySelectorAll('[data-inbox-view]'));
    const visibleCount = document.querySelector('[data-visible-conversation-count]');
    const visibleCountLabel = document.querySelector('[data-conversation-count-label]');
    const conversationList = document.querySelector('.conversation-list');
    const conversationRows = Array.from(document.querySelectorAll('[data-conversation-row]'));
    const noResults = document.querySelector('[data-conversation-no-results]');

    if (
        !searchInput ||
        !searchFilter ||
        !filterMenu ||
        !filterOptions ||
        !filterLabel ||
        optionButtons.length === 0
    ) {
        return;
    }

    const filterConversations = () => {
        const query = searchInput.value.trim().toLocaleLowerCase();
        const filter = searchFilter.dataset.filterValue || 'all';
        const activeView = document.querySelector('[data-inbox-view][aria-pressed="true"]')?.dataset.inboxView || 'all';
        let matchingCount = 0;

        // The search filter in all, name and messages
        // When user click unread, it only shows unread rows
        conversationRows.forEach((row) => {
            const name = (row.dataset.searchName || '').toLocaleLowerCase();
            const content = (row.dataset.searchContent || '').toLocaleLowerCase();
            const searchText = filter === 'name'
                ? name
                : filter === 'messages'
                    ? content
                    : `${name} ${content}`;
            const matchesSearch = query === '' || searchText.includes(query);
            const matchesView = activeView === 'all' || row.dataset.unread === 'true';
            const isVisible = matchesSearch && matchesView;

            row.hidden = !isVisible;
            matchingCount += isVisible ? 1 : 0;
        });

        if (conversationList && noResults) {
            conversationList.hidden = matchingCount === 0;
            noResults.hidden = matchingCount !== 0;
        }

        if (visibleCount) {
            visibleCount.textContent = String(matchingCount);
        }

        if (visibleCountLabel) {
            visibleCountLabel.textContent = matchingCount === 1 ? 'conversation' : 'conversations';
        }
    };

    const closeFilterMenu = () => {
        filterOptions.hidden = true;
        filterMenu.classList.remove('open');
        searchFilter.setAttribute('aria-expanded', 'false');
    };

    searchFilter.addEventListener('click', () => {
        const shouldOpen = filterOptions.hidden;

        filterOptions.hidden = !shouldOpen;
        filterMenu.classList.toggle('open', shouldOpen);
        searchFilter.setAttribute('aria-expanded', String(shouldOpen));
    });

    optionButtons.forEach((option) => {
        option.addEventListener('click', () => {
            const value = option.dataset.filterOption || 'all';

            searchFilter.dataset.filterValue = value;
            filterLabel.textContent = option.querySelector('span')?.textContent || 'All';

            optionButtons.forEach((button) => {
                button.setAttribute('aria-selected', String(button === option));
            });

            closeFilterMenu();
            filterConversations();
            searchInput.focus();
        });
    });

    inboxViewButtons.forEach((button) => {
        button.addEventListener('click', () => {
            inboxViewButtons.forEach((viewButton) => {
                const isActive = viewButton === button;
                viewButton.classList.toggle('active', isActive);
                viewButton.setAttribute('aria-pressed', String(isActive));
            });

            filterConversations();
        });
    });

    document.addEventListener('click', (event) => {
        if (!filterMenu.contains(event.target)) {
            closeFilterMenu();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeFilterMenu();
            searchFilter.focus();
        }
    });

    searchInput.addEventListener('input', filterConversations);
});
