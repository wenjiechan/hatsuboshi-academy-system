// Handles inbox filtering and lightweight live refresh on the inbox page.
document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.querySelector('[data-conversation-search]');
    const searchFilter = document.querySelector('[data-conversation-search-filter]');
    const filterMenu = document.querySelector('[data-search-filter-menu]');
    const filterOptions = document.querySelector('.messages-search-filter-options');
    const filterLabel = document.querySelector('[data-filter-label]');
    const optionButtons = Array.from(document.querySelectorAll('[data-filter-option]'));
    const inboxLiveRegion = document.querySelector('[data-inbox-live-region]');
    const inboxSummary = document.querySelector('[data-inbox-summary]');
    let activeInboxView = 'all';
    let inboxPollInProgress = false;

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
        const conversationRows = Array.from(document.querySelectorAll('[data-conversation-row]'));
        const conversationList = document.querySelector('.conversation-list');
        const noResults = document.querySelector('[data-conversation-no-results]');
        const visibleCount = document.querySelector('[data-visible-conversation-count]');
        const visibleCountLabel = document.querySelector('[data-conversation-count-label]');
        let matchingCount = 0;

        // Filtering is client-side because inbox.php already renders the searchable rows.
        conversationRows.forEach((row) => {
            const name = (row.dataset.searchName || '').toLocaleLowerCase();
            const content = (row.dataset.searchContent || '').toLocaleLowerCase();
            const searchText = filter === 'name'
                ? name
                : filter === 'messages'
                    ? content
                    : `${name} ${content}`;
            const matchesSearch = query === '' || searchText.includes(query);
            const isArchived = row.dataset.archived === 'true';
            const matchesView = activeInboxView === 'archived'
                ? isArchived
                : activeInboxView === 'unread'
                    ? !isArchived && row.dataset.unread === 'true'
                    : !isArchived;
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

    const applyInboxViewState = () => {
        document.querySelectorAll('[data-inbox-view]').forEach((button) => {
            const isActive = button.dataset.inboxView === activeInboxView;
            button.classList.toggle('active', isActive);
            button.setAttribute('aria-pressed', String(isActive));
        });
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

    document.addEventListener('click', (event) => {
        const button = event.target.closest('[data-inbox-view]');

        if (button) {
            activeInboxView = button.dataset.inboxView || 'all';
            applyInboxViewState();
            filterConversations();
        }
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

    const pollInbox = async () => {
        if (!inboxLiveRegion || inboxPollInProgress || document.hidden) {
            return;
        }

        inboxPollInProgress = true;

        try {
            const response = await fetch('/gakumas-sms/messages/inbox.php', {
                headers: { Accept: 'text/html' },
                cache: 'no-store',
            });

            if (response.redirected && !response.url.includes('/messages/inbox.php')) {
                window.location.assign(response.url);
                return;
            }

            if (!response.ok) {
                return;
            }

            const html = await response.text();
            // Replace only the live inbox area so filters and page shell stay intact.
            const freshDocument = new DOMParser().parseFromString(html, 'text/html');
            const freshLiveRegion = freshDocument.querySelector('[data-inbox-live-region]');
            const freshSummary = freshDocument.querySelector('[data-inbox-summary]');

            if (!freshLiveRegion || !freshSummary) {
                return;
            }

            inboxLiveRegion.replaceChildren(...Array.from(freshLiveRegion.childNodes));

            if (inboxSummary) {
                inboxSummary.replaceChildren(...Array.from(freshSummary.childNodes));
            }

            applyInboxViewState();
            filterConversations();
        } catch (error) {
            // A temporary network error is retried on the next polling interval.
        } finally {
            inboxPollInProgress = false;
        }
    };

    if (inboxLiveRegion) {
        window.setInterval(pollInbox, 3000);
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) {
                pollInbox();
            }
        });
    }
});
