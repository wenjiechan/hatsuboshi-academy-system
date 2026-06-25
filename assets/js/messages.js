document.addEventListener('DOMContentLoaded', () => {
    // Controls the search box in compose.php
    const recipientSearch = document.querySelector('[data-recipient-search]');
    const recipientRows = Array.from(document.querySelectorAll('[data-recipient-row]'));
    const recipientNoResults = document.querySelector('[data-recipient-no-results]');
    const messageInput = document.querySelector('[data-message-input]');
    const characterCount = document.querySelector('[data-message-character-count]');
    const conversationThread = document.querySelector('[data-conversation-thread]');
    let setMessageEditVisibility = () => {};
    // Controls a menu for the whole conversation like archive, delete and mute
    const conversationActionMenu = document.querySelector('[data-conversation-action-menu]');
    const conversationActionToggle = document.querySelector('[data-conversation-action-toggle]');
    const conversationActionPanel = document.querySelector('[data-conversation-action-panel]');

    if (conversationActionMenu && conversationActionToggle && conversationActionPanel) {
        const closeConversationActionMenu = (restoreFocus = false) => {
            conversationActionPanel.hidden = true;
            conversationActionToggle.setAttribute('aria-expanded', 'false');

            if (restoreFocus) {
                conversationActionToggle.focus();
            }
        };

        // Opens or closes the action panel
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

    // Search recipient in compose.php
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

        // Track live updates
        const conversationId = Number.parseInt(conversationThread.dataset.conversationId || '', 10);
        let lastMessageId = Number.parseInt(conversationThread.dataset.lastMessageId || '0', 10);
        let editedAfter = '1970-01-01 00:00:00';
        let deletedAfter = '1970-01-01 00:00:00';
        let pollInProgress = false;

        // Converts system message types into labels
        const messageTypeLabels = {
            birthday: 'Birthday message',
            producer_add_request: 'Producer request',
            producer_remove_request: 'Release request',
            system: 'System message',
        };

        // Convert database datetime into a nicer display format
        const formatMessageTime = (dateValue) => {
            const parsedDate = new Date(dateValue.replace(' ', 'T'));

            if (Number.isNaN(parsedDate.getTime())) {
                return dateValue;
            }

            const parts = new Intl.DateTimeFormat('en-US', {
                month: 'short',
                day: 'numeric',
                year: 'numeric',
                hour: 'numeric',
                minute: '2-digit',
            }).formatToParts(parsedDate);
            const value = (type) => parts.find((part) => part.type === type)?.value || '';

            return `${value('month')} ${value('day')}, ${value('year')} at ${value('hour')}:${value('minute')} ${value('dayPeriod')}`;
        };

        // Controls whether a message is in normal view or edit mode
        setMessageEditVisibility = (bubble, isEditing) => {
            const messageBody = bubble?.querySelector('[data-message-body]');
            const editForm = bubble?.querySelector('[data-message-edit-form]');
            const actionMenu = bubble?.querySelector('[data-message-action-menu]');
            const textarea = editForm?.querySelector('textarea');

            if (!messageBody || !editForm) {
                return;
            }

            messageBody.hidden = isEditing;
            editForm.hidden = !isEditing;

            if (actionMenu) {
                actionMenu.hidden = isEditing;
            }

            if (isEditing && textarea) {
                textarea.focus();
                textarea.setSelectionRange(textarea.value.length, textarea.value.length);
            }
        };

        // Safely displays message text
        const renderMessageBody = (element, messageBody) => {
            element.replaceChildren();

            String(messageBody).split(/\r?\n/).forEach((line, index) => {
                if (index > 0) {
                    element.append(document.createElement('br'));
                }

                element.append(document.createTextNode(line));
            });
        };

        // Updates a message that was edited by polling
        const updateEditedMessage = (message) => {
            const article = conversationThread.querySelector(`[data-message-id="${message.id}"]`);
            const body = article?.querySelector('[data-message-body]');
            const meta = article?.querySelector('.chat-message-meta');

            if (!article || !body || !meta) {
                return;
            }

            renderMessageBody(body, message.body);

            if (!meta.querySelector('[data-message-edited]')) {
                const edited = document.createElement('span');
                edited.dataset.messageEdited = '';
                edited.textContent = 'Edited';
                meta.prepend(edited);
            }

            const editForm = article.querySelector('[data-message-edit-form]');
            const textarea = editForm?.querySelector('textarea');

            if (textarea && editForm.hidden) {
                textarea.value = message.body;
            }
        };

        // Changes a message into a deleted-message display
        const renderDeletedMessage = (article) => {
            const body = article?.querySelector('[data-message-body]');
            const meta = article?.querySelector('.chat-message-meta');

            if (!article || !body || !meta) {
                return;
            }

            article.classList.add('deleted');
            body.replaceChildren();

            const icon = document.createElement('i');
            icon.className = 'bi bi-slash-circle';
            icon.setAttribute('aria-hidden', 'true');

            const text = document.createElement('em');
            text.textContent = 'This message was deleted.';
            body.append(icon, text);

            meta.querySelector('[data-message-edited]')?.remove();
            meta.querySelector('[data-message-action-menu]')?.remove();
            article.querySelector('[data-message-edit-form]')?.remove();
        };

        // Create the ... menu for each message
        const createMessageActionMenu = (message) => {
            const menu = document.createElement('div');
            menu.className = 'chat-message-action-menu';
            menu.dataset.messageActionMenu = '';

            const toggle = document.createElement('button');
            toggle.type = 'button';
            toggle.className = 'chat-message-action-toggle';
            toggle.dataset.messageActionToggle = '';
            toggle.setAttribute('aria-label', 'Message options');
            toggle.setAttribute('aria-haspopup', 'menu');
            toggle.setAttribute('aria-expanded', 'false');
            toggle.innerHTML = '<i class="bi bi-three-dots" aria-hidden="true"></i>';
            menu.append(toggle);

            const panel = document.createElement('div');
            panel.className = 'chat-message-action-panel';
            panel.dataset.messageActionPanel = '';
            panel.setAttribute('role', 'menu');
            panel.hidden = true;

            if (message.can_edit) {
                const editButton = document.createElement('button');
                editButton.type = 'button';
                editButton.dataset.messageEditOpen = '';
                editButton.setAttribute('role', 'menuitem');
                editButton.innerHTML = '<i class="bi bi-pencil"></i><span>Edit</span>';
                panel.append(editButton);
            }

            // For delete, it creates a real form
            if (message.can_delete) {
                const form = document.createElement('form');
                form.method = 'post';
                form.action = '/gakumas-sms/messages/delete.php';
                form.className = 'chat-message-delete-form';
                form.setAttribute('role', 'none');

                const fields = {
                    csrf_token: conversationThread.dataset.csrfToken || '',
                    conversation_id: String(conversationId),
                    message_id: String(message.id),
                };

                Object.entries(fields).forEach(([name, value]) => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = name;
                    input.value = value;
                    form.append(input);
                });

                const deleteButton = document.createElement('button');
                deleteButton.type = 'submit';
                deleteButton.dataset.messageDeleteSubmit = '';
                deleteButton.setAttribute('role', 'menuitem');
                deleteButton.innerHTML = '<i class="bi bi-trash3"></i><span>Delete</span>';
                form.append(deleteButton);
                panel.append(form);
            }

            menu.append(panel);
            return menu;
        };

        // Create a full chat message element using JavaScript
        const createMessageElement = (message) => {
            const typeLabel = messageTypeLabels[message.message_type] || '';
            const article = document.createElement('article');
            article.className = `chat-message${message.is_own ? ' own' : ''}${typeLabel ? ' special' : ''}`;
            article.dataset.messageId = String(message.id);

            const bubble = document.createElement('div');
            bubble.className = 'chat-message-bubble';

            if (typeLabel) {
                const type = document.createElement('span');
                type.className = 'chat-message-type';
                type.textContent = typeLabel;
                bubble.append(type);
            }

            const body = document.createElement('p');
            body.dataset.messageBody = '';
            renderMessageBody(body, message.body);
            bubble.append(body);

            const meta = document.createElement('div');
            meta.className = 'chat-message-meta';

            if (message.edited_at) {
                const edited = document.createElement('span');
                edited.dataset.messageEdited = '';
                edited.textContent = 'Edited';
                meta.append(edited);
            }

            const time = document.createElement('time');
            time.dateTime = message.created_at;
            time.textContent = formatMessageTime(message.created_at);
            meta.append(time);

            if (message.can_edit || message.can_delete) {
                meta.append(createMessageActionMenu(message));
            }

            bubble.append(meta);

            if (message.can_edit) {
                const form = document.createElement('form');
                form.method = 'post';
                form.action = '/gakumas-sms/messages/edit.php';
                form.className = 'chat-message-edit-form';
                form.dataset.messageEditForm = '';
                form.hidden = true;

                const fields = {
                    csrf_token: conversationThread.dataset.csrfToken || '',
                    conversation_id: String(conversationId),
                    message_id: String(message.id),
                };

                Object.entries(fields).forEach(([name, value]) => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = name;
                    input.value = value;
                    form.append(input);
                });

                const label = document.createElement('label');
                label.className = 'visually-hidden';
                label.htmlFor = `editMessage${message.id}`;
                label.textContent = 'Edit message';
                form.append(label);

                const textarea = document.createElement('textarea');
                textarea.id = `editMessage${message.id}`;
                textarea.name = 'body';
                textarea.rows = 2;
                textarea.maxLength = 5000;
                textarea.required = true;
                textarea.value = message.body;
                form.append(textarea);

                const controls = document.createElement('div');
                const cancelButton = document.createElement('button');
                cancelButton.type = 'button';
                cancelButton.className = 'message-edit-cancel';
                cancelButton.dataset.messageEditCancel = '';
                cancelButton.textContent = 'Cancel';
                cancelButton.addEventListener('click', () => setMessageEditVisibility(bubble, false));

                const saveButton = document.createElement('button');
                saveButton.type = 'submit';
                saveButton.className = 'message-edit-save';
                saveButton.textContent = 'Save';

                controls.append(cancelButton, saveButton);
                form.append(controls);
                bubble.append(form);
            }

            article.append(bubble);

            if (message.deleted_at) {
                renderDeletedMessage(article);
            }

            return article;
        };

        // Main live chat function
        const pollMessages = async () => {
            if (!Number.isInteger(conversationId) || conversationId <= 0 || pollInProgress || document.hidden) {
                return;
            }

            pollInProgress = true;

            try {
                const params = new URLSearchParams({
                    conversation_id: String(conversationId),
                    after_id: String(lastMessageId),
                    edited_after: editedAfter,
                    deleted_after: deletedAfter,
                });
                // Send a request to message poll.php
                const response = await fetch(`/gakumas-sms/api/messages_poll.php?${params}`, {
                    headers: { Accept: 'application/json' },
                    cache: 'no-store',
                });
                const data = await response.json();

                if (response.status === 401 && data.redirect_url) {
                    window.location.assign(data.redirect_url);
                    return;
                }

                if (
                    !response.ok ||
                    !Array.isArray(data.messages) ||
                    !Array.isArray(data.edited_messages) ||
                    !Array.isArray(data.deleted_messages)
                ) {
                    return;
                }

                // Checks whether the user is near the buton of the chat
                const distanceFromBottom = conversationThread.scrollHeight
                // If the user scrolled up to read old messages, 
                // it will not force-scroll down
                    - conversationThread.scrollTop
                    - conversationThread.clientHeight;
                const shouldScroll = distanceFromBottom < 120;

                if (data.messages.length > 0) {
                    conversationThread.querySelector('.conversation-start-state')?.remove();

                    // Create and append messages that does not already exist on page
                    data.messages.forEach((message) => {
                        if (!conversationThread.querySelector(`[data-message-id="${message.id}"]`)) {
                            conversationThread.append(createMessageElement(message));
                        }
                    });

                    if (shouldScroll) {
                        conversationThread.scrollTop = conversationThread.scrollHeight;
                    }
                }

                // Handle edited messages from polling
                data.edited_messages.forEach(updateEditedMessage);
                 // Handle deleted messages from polling
                data.deleted_messages.forEach((message) => {
                    let article = conversationThread.querySelector(`[data-message-id="${message.id}"]`);

                    if (!article) {
                        article = createMessageElement({
                            ...message,
                            body: '',
                            edited_at: null,
                            can_edit: false,
                            can_delete: false,
                        });
                        conversationThread.append(article);
                    }

                    renderDeletedMessage(article);
                });

                // Update polling cursors
                lastMessageId = Number.parseInt(data.next_after_id, 10) || lastMessageId;
                conversationThread.dataset.lastMessageId = String(lastMessageId);

                // Updates the edited/deleted checking time
                if (typeof data.edited_cursor === 'string') {
                    editedAfter = data.edited_cursor;
                }

                if (typeof data.deleted_cursor === 'string') {
                    deletedAfter = data.deleted_cursor;
                }
            } catch (error) {
                // A temporary network error is retried on the next polling interval.
            } finally {
                pollInProgress = false;
            }
        };

        // Every 3 seconds it checks for message updates
        window.setInterval(pollMessages, 3000);
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) {
                pollMessages();
            }
        });
    }

    // Closes all ... menus, except the provided menu
    const closeMessageActionMenus = (exceptMenu = null) => {
        document.querySelectorAll('[data-message-action-menu]').forEach((menu) => {
            if (menu === exceptMenu) {
                return;
            }

            const panel = menu.querySelector('[data-message-action-panel]');
            const toggle = menu.querySelector('[data-message-action-toggle]');

            if (panel) {
                panel.hidden = true;
            }

            toggle?.setAttribute('aria-expanded', 'false');
        });
    };

    // It checked what the user clicked
    // If user clicks ..., it opens the menu
    // If user clicks Edit, it opens edit mode
    // If user clicks Delete, it asks confirmation
    // If user cancels, it stops the form
    document.addEventListener('click', (event) => {
        const actionToggle = event.target.closest('[data-message-action-toggle]');
        const editButton = event.target.closest('[data-message-edit-open]');
        const deleteButton = event.target.closest('[data-message-delete-submit]');

        if (actionToggle) {
            const menu = actionToggle.closest('[data-message-action-menu]');
            const panel = menu?.querySelector('[data-message-action-panel]');
            const shouldOpen = Boolean(panel?.hidden);

            closeMessageActionMenus(menu);

            if (panel) {
                panel.hidden = !shouldOpen;
                actionToggle.setAttribute('aria-expanded', String(shouldOpen));
            }

            return;
        }

        if (editButton) {
            const bubble = editButton.closest('.chat-message-bubble');
            closeMessageActionMenus();
            setMessageEditVisibility(bubble, true);
            return;
        }

        if (deleteButton && !window.confirm('Delete this message? This cannot be undone.')) {
            event.preventDefault();
            return;
        }

        if (!event.target.closest('[data-message-action-menu]')) {
            closeMessageActionMenus();
        }
    });

    // Clicks cancel when edit, hides the edit form and shows the original message again
    document.querySelectorAll('[data-message-edit-cancel]').forEach((cancelButton) => {
        cancelButton.addEventListener('click', () => {
            const bubble = cancelButton.closest('.chat-message-bubble');
            const messageBody = bubble?.querySelector('[data-message-body]');
            const editForm = bubble?.querySelector('[data-message-edit-form]');
            const actionMenu = bubble?.querySelector('[data-message-action-menu]');

            if (messageBody) {
                messageBody.hidden = false;
            }

            if (editForm) {
                editForm.hidden = true;
            }

            if (actionMenu) {
                actionMenu.hidden = false;
            }
        });
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeMessageActionMenus();
        }
    });

    // Control the inbox search bar and filter dropdown
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
            // Archived conversations
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

    // Updates the active tab button
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

    // Click button dropdown
    searchFilter.addEventListener('click', () => {
        const shouldOpen = filterOptions.hidden;

        filterOptions.hidden = !shouldOpen;
        filterMenu.classList.toggle('open', shouldOpen);
        searchFilter.setAttribute('aria-expanded', String(shouldOpen));
    });

    // Select option dropdown
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

    // Periodically reloads the inbox content without refreshing the page
    const pollInbox = async () => {
        if (!inboxLiveRegion || inboxPollInProgress || document.hidden) {
            return;
        }

        inboxPollInProgress = true;

        try {
            // Fetch the inbox.php
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
            // Parses the returned HTML
            const freshDocument = new DOMParser().parseFromString(html, 'text/html');
            // Extract
            const freshLiveRegion = freshDocument.querySelector('[data-inbox-live-region]');
            const freshSummary = freshDocument.querySelector('[data-inbox-summary]');

            if (!freshLiveRegion || !freshSummary) {
                return;
            }

            // Replaces the current page content
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
        // Inbox updates immediately when user returns to the tab
        window.setInterval(pollInbox, 3000);
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) {
                pollInbox();
            }
        });
    }
});
