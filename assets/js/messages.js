document.addEventListener('DOMContentLoaded', () => {
    // Controls the search box in compose.php
    const recipientSearch = document.querySelector('[data-recipient-search]');
    const recipientRows = Array.from(document.querySelectorAll('[data-recipient-row]'));
    const recipientNoResults = document.querySelector('[data-recipient-no-results]');
    const messageInput = document.querySelector('[data-message-input]');
    const characterCount = document.querySelector('[data-message-character-count]');
    const conversationThread = document.querySelector('[data-conversation-thread]');
    const messageComposer = document.querySelector('[data-message-composer]');
    const messageSendButton = document.querySelector('[data-message-send-button]');
    const messageSendError = document.querySelector('[data-message-send-error]');
    let setMessageEditVisibility = () => {};
    // Controls a menu for the whole conversation like archive, delete and mute
    const conversationActionMenu = document.querySelector('[data-conversation-action-menu]');
    const conversationActionToggle = document.querySelector('[data-conversation-action-toggle]');
    const conversationActionPanel = document.querySelector('[data-conversation-action-panel]');
    const modalOpenButtons = Array.from(document.querySelectorAll('[data-modal-open]'));
    const modals = Array.from(document.querySelectorAll('.message-modal'));
    const modalSearchInputs = Array.from(document.querySelectorAll('[data-modal-search]'));
    let activeModalTrigger = null;
    let openReadReceiptModal = () => {};

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

    const openModal = (modal, trigger) => {
        if (!modal) {
            return;
        }

        modals.forEach((existingModal) => closeModal(existingModal, false));
        activeModalTrigger = trigger;
        modal.hidden = false;
        document.body.classList.add('message-modal-open');

        if (conversationActionPanel && conversationActionToggle) {
            conversationActionPanel.hidden = true;
            conversationActionToggle.setAttribute('aria-expanded', 'false');
        }

        modal.querySelector('button, input, textarea, select, a[href]')?.focus();
    };

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
                // Check each recipient row.
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
        const isGroupConversation = conversationThread.dataset.conversationType === 'group';
        const currentUserId = Number.parseInt(conversationThread.dataset.currentUserId || '0', 10);
        const mentionSuggestions = document.querySelector('[data-mention-suggestions]');
        const mentionMembersScript = document.querySelector('[data-mention-members]');
        let mentionMembers = [];
        let lastMessageId = Number.parseInt(conversationThread.dataset.lastMessageId || '0', 10);
        let editedAfter = '1970-01-01 00:00:00';
        let deletedAfter = '1970-01-01 00:00:00';
        let pollInProgress = false;

        if (mentionMembersScript?.textContent) {
            try {
                mentionMembers = JSON.parse(mentionMembersScript.textContent);
            } catch (error) {
                mentionMembers = [];
            }
        }

        const currentMentionMember = mentionMembers.find(
            (member) => Number.parseInt(member.user_id, 10) === currentUserId
        );
        const currentUserDisplayName = currentMentionMember?.display_name || '';

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

        const escapeRegExp = (value) => String(value).replace(/[.*+?^${}()|[\]\\]/g, '\\$&');

        const createMentionPattern = () => {
            if (!isGroupConversation) {
                return null;
            }

            const mentionNames = Array.from(new Set([
                ...mentionMembers
                    .map((member) => member.display_name)
                    .filter(Boolean),
                'everyone',
            ]))
                .sort((left, right) => right.length - left.length)
                .map(escapeRegExp);

            if (mentionNames.length === 0) {
                return null;
            }

            return new RegExp(`(^|[\\s(])@(${mentionNames.join('|')})(?=$|[\\s.,!?;:)\\]])`, 'giu');
        };

        // Safely displays message text and applies viewer-specific mention highlighting.
        const renderMessageBody = (element, messageBody) => {
            element.replaceChildren();
            const mentionPattern = createMentionPattern();

            String(messageBody).split(/\r?\n/).forEach((line, index) => {
                if (index > 0) {
                    element.append(document.createElement('br'));
                }

                if (!mentionPattern) {
                    element.append(document.createTextNode(line));
                    return;
                }

                let lastIndex = 0;
                mentionPattern.lastIndex = 0;

                for (const match of line.matchAll(mentionPattern)) {
                    const prefix = match[1] || '';
                    const mentionName = match[2] || '';
                    const matchIndex = (match.index || 0) + prefix.length;

                    if (matchIndex > lastIndex) {
                        element.append(document.createTextNode(line.slice(lastIndex, matchIndex)));
                    }

                    const mention = document.createElement('span');
                    mention.className = 'chat-mention';

                    // Everyone sees mention text; only the mentioned viewer sees the highlight background.
                    if (
                        mentionName.toLocaleLowerCase() === 'everyone' ||
                        mentionName.toLocaleLowerCase() === currentUserDisplayName.toLocaleLowerCase()
                    ) {
                        mention.classList.add('chat-mention-targeted');
                    }

                    mention.textContent = `@${mentionName}`;
                    element.append(mention);
                    lastIndex = (match.index || 0) + match[0].length;
                }

                if (lastIndex < line.length) {
                    element.append(document.createTextNode(line.slice(lastIndex)));
                }
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
            meta.querySelector('[data-read-receipt]')?.remove();
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

            if (message.can_pin) {
                const form = document.createElement('form');
                form.method = 'post';
                form.action = '/gakumas-sms/messages/pin.php';
                form.className = 'chat-message-pin-form';
                form.setAttribute('role', 'none');

                const fields = {
                    csrf_token: conversationThread.dataset.csrfToken || '',
                    conversation_id: String(conversationId),
                    message_id: String(message.id),
                    action: message.pinned_at ? 'unpin' : 'pin',
                };

                Object.entries(fields).forEach(([name, value]) => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = name;
                    input.value = value;
                    form.append(input);
                });

                const pinButton = document.createElement('button');
                pinButton.type = 'submit';
                pinButton.setAttribute('role', 'menuitem');
                pinButton.innerHTML = message.pinned_at
                    ? '<i class="bi bi-pin-angle"></i><span>Unpin</span>'
                    : '<i class="bi bi-pin-angle-fill"></i><span>Pin</span>';
                form.append(pinButton);
                panel.append(form);
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

        const createRequestStatusBadge = (message) => {
            if (
                !['producer_add_request', 'producer_remove_request'].includes(message.message_type)
                || !message.request_status
            ) {
                return null;
            }

            const badge = document.createElement('span');
            badge.className = `chat-request-status chat-request-status-${message.request_status}`;
            badge.dataset.requestStatus = '';
            badge.textContent = String(message.request_status)
                .replaceAll('_', ' ')
                .replace(/\b\w/g, (letter) => letter.toUpperCase());

            return badge;
        };

        const createRequestActionForm = (message) => {
            if (!message.can_respond_request || !message.request_id) {
                return null;
            }

            const form = document.createElement('form');
            form.method = 'post';
            form.action = '/gakumas-sms/messages/request_action.php';
            form.className = 'chat-request-actions';
            form.dataset.requestActionForm = '';

            const csrf = document.createElement('input');
            csrf.type = 'hidden';
            csrf.name = 'csrf_token';
            csrf.value = conversationThread.dataset.csrfToken || '';
            form.append(csrf);

            const requestId = document.createElement('input');
            requestId.type = 'hidden';
            requestId.name = 'request_id';
            requestId.value = String(message.request_id);
            form.append(requestId);

            const rejectButton = document.createElement('button');
            rejectButton.type = 'submit';
            rejectButton.name = 'action';
            rejectButton.value = 'reject';
            rejectButton.className = 'chat-request-button reject';
            rejectButton.innerHTML = '<i class="bi bi-x-lg" aria-hidden="true"></i>Reject';

            const acceptButton = document.createElement('button');
            acceptButton.type = 'submit';
            acceptButton.name = 'action';
            acceptButton.value = 'accept';
            acceptButton.className = 'chat-request-button accept';
            acceptButton.innerHTML = '<i class="bi bi-check-lg" aria-hidden="true"></i>Accept';

            form.append(rejectButton, acceptButton);
            return form;
        };

        const updateRequestMessageStatus = (requestId, requestStatus) => {
            const requestArticle = Array.from(conversationThread.querySelectorAll('.chat-message'))
                .find((article) => {
                    const requestInput = article.querySelector('input[name="request_id"]');

                    return requestInput?.value === String(requestId);
                });

            if (!requestArticle) {
                return;
            }

            const statusText = String(requestStatus)
                .replaceAll('_', ' ')
                .replace(/\b\w/g, (letter) => letter.toUpperCase());
            const meta = requestArticle.querySelector('.chat-message-meta');
            let statusBadge = requestArticle.querySelector('[data-request-status]');

            if (!statusBadge && meta) {
                statusBadge = document.createElement('span');
                statusBadge.dataset.requestStatus = '';
                meta.prepend(statusBadge);
            }

            if (statusBadge) {
                statusBadge.className = `chat-request-status chat-request-status-${requestStatus}`;
                statusBadge.textContent = statusText;
            }

            if (requestStatus !== 'pending') {
                requestArticle.querySelector('[data-request-action-form]')?.remove();
            }
        };

        const readReceiptText = (count) => `Read by ${count}`;

        const updateReadReceiptButton = (article, receipt) => {
            const button = article?.querySelector('[data-read-receipt]');

            if (!button || !receipt) {
                return;
            }

            const count = Number.parseInt(receipt.read_count, 10) || 0;
            button.dataset.readCount = String(count);
            button.dataset.readNames = receipt.read_names || '';
            button.dataset.readUsers = JSON.stringify(receipt.read_users || []);
            button.textContent = readReceiptText(count);
            button.hidden = count === 0;
        };

        const updateReadReceipts = (receipts) => {
            if (!Array.isArray(receipts)) {
                return;
            }

            receipts.forEach((receipt) => {
                const messageId = Number.parseInt(receipt.message_id, 10);
                const article = conversationThread.querySelector(`[data-message-id="${messageId}"]`);
                updateReadReceiptButton(article, receipt);
            });
        };

        const createReadReceiptButton = (message) => {
            if (!isGroupConversation || !message.is_own || message.message_type === 'system' || message.deleted_at) {
                return null;
            }

            const receipt = message.read_receipt || {};
            const count = Number.parseInt(receipt.read_count, 10) || 0;
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'chat-read-receipt';
            button.dataset.readReceipt = '';
            button.dataset.readCount = String(count);
            button.dataset.readNames = receipt.read_names || '';
            button.dataset.readUsers = JSON.stringify(receipt.read_users || []);
            button.textContent = readReceiptText(count);
            button.hidden = count === 0;

            return button;
        };

        const updateTypingIndicator = (typingUsers) => {
            const indicator = document.querySelector('[data-typing-indicator]');
            let typingHideTimer = updateTypingIndicator.hideTimer || null;

            if (!indicator || !Array.isArray(typingUsers) || typingUsers.length === 0) {
                if (indicator) {
                    window.clearTimeout(typingHideTimer);
                    updateTypingIndicator.hideTimer = window.setTimeout(() => {
                        indicator.hidden = true;
                        indicator.textContent = '';
                    }, 900);
                }

                return;
            }

            const names = typingUsers.map((user) => user.display_name).filter(Boolean);

            if (names.length === 0) {
                window.clearTimeout(typingHideTimer);
                updateTypingIndicator.hideTimer = window.setTimeout(() => {
                    indicator.hidden = true;
                    indicator.textContent = '';
                }, 900);
                return;
            }

            window.clearTimeout(typingHideTimer);
            indicator.hidden = false;
            indicator.textContent = names.length === 1
                ? `${names[0]} is typing...`
                : `${names.slice(0, 2).join(' and ')} are typing...`;
        };

        const hideMentionSuggestions = () => {
            if (mentionSuggestions) {
                mentionSuggestions.hidden = true;
                mentionSuggestions.replaceChildren();
            }
        };

        const currentMentionQuery = () => {
            if (!messageInput) {
                return null;
            }

            const caret = messageInput.selectionStart || 0;
            const textBeforeCaret = messageInput.value.slice(0, caret);
            const atIndex = textBeforeCaret.lastIndexOf('@');

            if (atIndex < 0) {
                return null;
            }

            const charBeforeAt = atIndex > 0 ? textBeforeCaret[atIndex - 1] : '';
            const query = textBeforeCaret.slice(atIndex + 1);

            if (charBeforeAt && !/\s|\(/.test(charBeforeAt)) {
                return null;
            }

            if (/\s/.test(query)) {
                return null;
            }

            return {
                atIndex,
                query: query.toLocaleLowerCase(),
                caret,
            };
        };

        const insertMention = (mentionValue) => {
            const mention = currentMentionQuery();

            if (!messageInput || !mention) {
                return;
            }

            const insertText = `@${mentionValue} `;
            const before = messageInput.value.slice(0, mention.atIndex);
            const after = messageInput.value.slice(mention.caret);
            const nextCaret = before.length + insertText.length;

            messageInput.value = before + insertText + after;
            messageInput.focus();
            messageInput.setSelectionRange(nextCaret, nextCaret);
            messageInput.dispatchEvent(new Event('input'));
            hideMentionSuggestions();
        };

        const showMentionSuggestions = () => {
            if (!isGroupConversation || !mentionSuggestions || !messageInput) {
                return;
            }

            const mention = currentMentionQuery();

            if (!mention) {
                hideMentionSuggestions();
                return;
            }

            const everyoneOption = {
                display_name: 'everyone',
                role_detail: 'Notify everyone',
                avatar: '/gakumas-sms/assets/images/avatars/default.webp',
                mention_value: 'everyone',
            };
            const memberOptions = mentionMembers
                .filter((member) => {
                    if (Number.parseInt(member.user_id, 10) === currentUserId) {
                        return false;
                    }

                    const searchText = `${member.display_name || ''} ${member.role_detail || ''}`.toLocaleLowerCase();
                    return mention.query === '' || searchText.includes(mention.query);
                })
                .slice(0, 7)
                .map((member) => ({
                    ...member,
                    mention_value: member.display_name,
                }));
            const options = [
                ...(everyoneOption.mention_value.includes(mention.query) ? [everyoneOption] : []),
                ...memberOptions,
            ];

            mentionSuggestions.replaceChildren();

            if (options.length === 0) {
                hideMentionSuggestions();
                return;
            }

            options.forEach((option) => {
                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'mention-suggestion-item';
                button.dataset.mentionValue = option.mention_value || option.display_name || '';

                const avatar = document.createElement('img');
                avatar.src = option.avatar || '/gakumas-sms/assets/images/avatars/default.webp';
                avatar.alt = '';

                const copy = document.createElement('span');
                const name = document.createElement('strong');
                name.textContent = `@${option.display_name || 'member'}`;
                const detail = document.createElement('small');
                detail.textContent = option.role_detail || 'Member';

                copy.append(name, detail);
                button.append(avatar, copy);
                mentionSuggestions.append(button);
            });

            mentionSuggestions.hidden = false;
        };

        openReadReceiptModal = (button) => {
            const modal = document.getElementById('readReceiptModal');
            const summary = modal?.querySelector('[data-read-receipt-summary]');
            const list = modal?.querySelector('[data-read-receipt-list]');

            if (!modal || !summary || !list) {
                return;
            }

            const count = Number.parseInt(button.dataset.readCount, 10) || 0;
            let readers = [];

            try {
                readers = JSON.parse(button.dataset.readUsers || '[]');
            } catch (error) {
                readers = [];
            }

            if (!Array.isArray(readers) || readers.length === 0) {
                readers = (button.dataset.readNames || '')
                    .split(',')
                    .map((name) => name.trim())
                    .filter(Boolean)
                    .map((name) => ({
                        display_name: name,
                        avatar: '/gakumas-sms/assets/images/avatars/default.webp',
                    }));
            }

            summary.textContent = readReceiptText(count);
            list.replaceChildren();

            if (readers.length === 0) {
                const empty = document.createElement('div');
                empty.className = 'read-receipt-empty';
                empty.textContent = 'No one has read this message yet.';
                list.append(empty);
            } else {
                readers.forEach((reader) => {
                    const item = document.createElement('div');
                    item.className = 'read-receipt-item';

                    const avatar = document.createElement('img');
                    avatar.src = reader.avatar || '/gakumas-sms/assets/images/avatars/default.webp';
                    avatar.alt = '';
                    avatar.className = 'read-receipt-avatar';

                    const text = document.createElement('span');
                    const name = document.createElement('strong');
                    name.textContent = reader.display_name || 'Someone';
                    const detail = document.createElement('small');
                    const roleDetail = reader.role_detail || reader.role || 'Member';
                    detail.textContent = reader.read_at
                        ? `${roleDetail} · ${reader.read_at}`
                        : roleDetail;
                    text.append(name, detail);
                    item.append(avatar, text);
                    list.append(item);
                });
            }

            openModal(modal, button);
        };

        // Create a full chat message element using JavaScript
        const createMessageElement = (message) => {
            const typeLabel = messageTypeLabels[message.message_type] || '';
            const article = document.createElement('article');
            article.className = `chat-message${message.is_own ? ' own' : ''}${typeLabel ? ' special' : ''}`;
            article.dataset.messageId = String(message.id);
            article.id = `message-${message.id}`;

            const bubble = document.createElement('div');
            bubble.className = 'chat-message-bubble';

            if (typeLabel) {
                const type = document.createElement('span');
                type.className = 'chat-message-type';
                type.textContent = typeLabel;
                bubble.append(type);
            }

            if (
                isGroupConversation &&
                !message.is_own &&
                message.message_type !== 'system' &&
                message.sender_display_name
            ) {
                const sender = document.createElement('span');
                sender.className = 'chat-message-sender';
                sender.textContent = message.sender_display_name;
                bubble.append(sender);
            }

            const body = document.createElement('p');
            body.dataset.messageBody = '';
            renderMessageBody(body, message.body);
            bubble.append(body);

            const meta = document.createElement('div');
            meta.className = 'chat-message-meta';

            const requestStatusBadge = createRequestStatusBadge(message);

            if (requestStatusBadge) {
                meta.append(requestStatusBadge);
            }

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

            const readReceiptButton = createReadReceiptButton(message);

            if (readReceiptButton) {
                meta.append(readReceiptButton);
            }

            if (message.can_edit || message.can_delete || message.can_pin) {
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

            const requestActionForm = createRequestActionForm(message);

            if (requestActionForm) {
                bubble.append(requestActionForm);
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

                if (Array.isArray(data.request_statuses)) {
                    data.request_statuses.forEach((request) => {
                        updateRequestMessageStatus(request.request_id, request.request_status);
                    });
                }

                updateReadReceipts(data.read_receipts);
                updateTypingIndicator(data.typing_users);

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

        // Send a message in the background without reloading the page
        if (messageComposer && messageInput && messageSendButton) {
            let typingStopTimer = null;
            let lastTypingState = false;

            const sendTypingStatus = async (isTyping) => {
                if (!isGroupConversation || lastTypingState === isTyping) {
                    return;
                }

                lastTypingState = isTyping;

                const formData = new FormData();
                formData.set('csrf_token', conversationThread.dataset.csrfToken || '');
                formData.set('conversation_id', String(conversationId));
                formData.set('is_typing', isTyping ? '1' : '0');

                try {
                    await fetch('/gakumas-sms/api/message_typing.php', {
                        method: 'POST',
                        headers: {
                            Accept: 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: formData,
                        keepalive: !isTyping,
                    });
                } catch (error) {
                    // Typing indicators are best-effort only.
                }
            };

            const queueTypingStop = () => {
                window.clearTimeout(typingStopTimer);
                typingStopTimer = window.setTimeout(() => {
                    sendTypingStatus(false);
                }, 1800);
            };

            if (isGroupConversation) {
                messageInput.addEventListener('input', () => {
                    showMentionSuggestions();

                    if (messageInput.value.trim() === '') {
                        window.clearTimeout(typingStopTimer);
                        sendTypingStatus(false);
                        return;
                    }

                    sendTypingStatus(true);
                    queueTypingStop();
                });

                messageInput.addEventListener('blur', () => {
                    window.clearTimeout(typingStopTimer);
                    sendTypingStatus(false);
                });

                messageInput.addEventListener('keydown', (event) => {
                    if (event.key === 'Escape' && mentionSuggestions && !mentionSuggestions.hidden) {
                        event.preventDefault();
                        hideMentionSuggestions();
                    }
                });

                mentionSuggestions?.addEventListener('mousedown', (event) => {
                    event.preventDefault();
                });

                mentionSuggestions?.addEventListener('click', (event) => {
                    const button = event.target.closest('[data-mention-value]');

                    if (!button) {
                        return;
                    }

                    insertMention(button.dataset.mentionValue || '');
                });

                document.addEventListener('click', (event) => {
                    if (
                        mentionSuggestions?.contains(event.target) ||
                        messageInput.contains(event.target)
                    ) {
                        return;
                    }

                    hideMentionSuggestions();
                });

                window.addEventListener('beforeunload', () => {
                    sendTypingStatus(false);
                });
            }

            messageInput.addEventListener('keydown', (event) => {
                if (event.key !== 'Enter' || event.shiftKey || event.isComposing) {
                    return;
                }

                event.preventDefault();

                if (messageInput.value.trim() === '') {
                    return;
                }

                messageComposer.requestSubmit();
            });

            messageComposer.addEventListener('submit', async (event) => {
                //Javascript takes control of the submit process
                event.preventDefault();

                if (messageInput.value.trim() === '') {
                    messageInput.focus();
                    return;
                }

                // Prevents the user from clicking Send many times quickly
                messageSendButton.disabled = true;

                // Clear previous error message before trying again
                if (messageSendError) {
                    messageSendError.hidden = true;
                    messageSendError.textContent = '';
                }

                try {
                    // Send the form to the URL
                    const response = await fetch(messageComposer.action, {
                        method: 'POST',
                        headers: {
                            Accept: 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: new FormData(messageComposer),
                    });

                    if (response.redirected) {
                        window.location.assign(response.url);
                        return;
                    }

                    // Read the JSON response from PHP
                    const data = await response.json();

                    if (!response.ok || !data.success || !data.message) {
                        throw new Error(data.error || 'The message could not be sent.');
                    }

                    //Checks whether the message already exists in the chat
                    if (!conversationThread.querySelector(`[data-message-id="${data.message.id}"]`)) {
                        conversationThread.querySelector('.conversation-start-state')?.remove();
                        conversationThread.append(createMessageElement(data.message));
                    }

                    // Updates the latest message ID stores in Javascript and HTML
                    lastMessageId = Math.max(lastMessageId, Number.parseInt(data.message.id, 10) || 0);
                    conversationThread.dataset.lastMessageId = String(lastMessageId);
                    // Scroll to the latest message after sending
                    conversationThread.scrollTop = conversationThread.scrollHeight;

                    // Clear the textarea after sending successfully
                    messageInput.value = '';
                    messageInput.dispatchEvent(new Event('input'));
                    hideMentionSuggestions();
                    window.clearTimeout(typingStopTimer);
                    sendTypingStatus(false);
                    messageInput.focus();
                } catch (error) {
                    if (messageSendError) {
                        messageSendError.textContent = error instanceof Error
                            ? error.message
                            : 'The message could not be sent.';
                        messageSendError.hidden = false;
                    }
                } finally {
                    messageSendButton.disabled = false;
                }
            });
        }

        conversationThread.addEventListener('submit', async (event) => {
            const requestForm = event.target.closest('[data-request-action-form]');

            if (!requestForm) {
                return;
            }

            event.preventDefault();

            const submitter = event.submitter || requestForm.querySelector('button[type="submit"]:focus');
            const formData = new FormData(requestForm);

            if (submitter?.name) {
                formData.set(submitter.name, submitter.value);
            }

            requestForm.querySelectorAll('button').forEach((button) => {
                button.disabled = true;
            });

            try {
                const requestUrl = requestForm.getAttribute('action') || '/gakumas-sms/messages/request_action.php';
                const response = await fetch(requestUrl, {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: formData,
                });
                const responseText = await response.text();
                let data = {};

                try {
                    data = responseText ? JSON.parse(responseText) : {};
                } catch (parseError) {
                    if (response.redirected) {
                        window.location.assign(response.url);
                        return;
                    }

                    throw new Error('The server returned an HTML page instead of JSON. Please refresh and try again.');
                }

                if (response.status === 401 && data.redirect_url) {
                    window.location.assign(data.redirect_url);
                    return;
                }

                if (!response.ok || !data.success) {
                    throw new Error(data.error || 'The request could not be updated.');
                }

                updateRequestMessageStatus(data.request_id, data.request_status);

                if (data.message && !conversationThread.querySelector(`[data-message-id="${data.message.id}"]`)) {
                    conversationThread.append(createMessageElement(data.message));
                    lastMessageId = Math.max(lastMessageId, Number.parseInt(data.message.id, 10) || 0);
                    conversationThread.dataset.lastMessageId = String(lastMessageId);
                }

                conversationThread.scrollTop = conversationThread.scrollHeight;
            } catch (error) {
                requestForm.querySelectorAll('button').forEach((button) => {
                    button.disabled = false;
                });

                if (messageSendError) {
                    messageSendError.textContent = error instanceof Error
                        ? error.message
                        : 'The request could not be updated.';
                    messageSendError.hidden = false;
                }
            }
        });

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
                panel.removeAttribute('style');
            }

            toggle?.setAttribute('aria-expanded', 'false');
        });
    };

    const positionMessageActionPanel = (toggle, panel) => {
        const toggleRect = toggle.getBoundingClientRect();
        const gap = 5;
        const viewportPadding = 8;

        panel.style.position = 'fixed';
        panel.style.zIndex = '2000';
        panel.style.visibility = 'hidden';
        panel.hidden = false;

        const panelWidth = panel.offsetWidth;
        const panelHeight = panel.offsetHeight;
        const left = Math.min(
            Math.max(viewportPadding, toggleRect.right - panelWidth),
            window.innerWidth - panelWidth - viewportPadding
        );
        const preferredTop = toggleRect.bottom + gap;
        const top = preferredTop + panelHeight + viewportPadding > window.innerHeight
            ? Math.max(viewportPadding, toggleRect.top - panelHeight - gap)
            : preferredTop;

        panel.style.left = `${left}px`;
        panel.style.top = `${top}px`;
        panel.style.right = 'auto';
        panel.style.bottom = 'auto';
        panel.style.visibility = '';
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
        const groupRemoveButton = event.target.closest('[data-group-remove-submit]');
        const readReceiptButton = event.target.closest('[data-read-receipt]');

        if (actionToggle) {
            const menu = actionToggle.closest('[data-message-action-menu]');
            const panel = menu?.querySelector('[data-message-action-panel]');
            const shouldOpen = Boolean(panel?.hidden);

            closeMessageActionMenus(menu);

            if (panel) {
                actionToggle.setAttribute('aria-expanded', String(shouldOpen));

                if (shouldOpen) {
                    positionMessageActionPanel(actionToggle, panel);
                } else {
                    panel.hidden = true;
                    panel.removeAttribute('style');
                }
            }

            return;
        }

        if (readReceiptButton) {
            openReadReceiptModal(readReceiptButton);
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

        if (groupRemoveButton) {
            const memberName = groupRemoveButton.dataset.memberName || 'this member';

            if (!window.confirm(`Remove ${memberName} from this group?`)) {
                event.preventDefault();
                return;
            }
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
