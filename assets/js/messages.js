document.addEventListener('DOMContentLoaded', () => {
    // Main conversation runtime. Smaller page-specific modules are loaded before this file.
    const messageInput = document.querySelector('[data-message-input]');
    const characterCount = document.querySelector('[data-message-character-count]');
    const conversationThread = document.querySelector('[data-conversation-thread]');
    const messageComposer = document.querySelector('[data-message-composer]');
    const messageSendButton = document.querySelector('[data-message-send-button]');
    const messageSendError = document.querySelector('[data-message-send-error]');
    const replyToMessageInput = document.querySelector('[data-reply-to-message-id]');
    const replyComposer = document.querySelector('[data-reply-composer]');
    const replyComposerSender = document.querySelector('[data-reply-composer-sender]');
    const replyComposerPreview = document.querySelector('[data-reply-composer-preview]');
    const replyCancel = document.querySelector('[data-reply-cancel]');
    const forwardModal = document.querySelector('[data-message-forward-modal]');
    const forwardMessageInput = document.querySelector('[data-forward-message-id]');
    let setMessageEditVisibility = () => {};
    let setReplyComposer = () => {};
    let clearReplyComposer = () => {};
    const messageModals = window.GakumasMessageModals || { open: () => {} };

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
        const supportsTypingIndicator = ['direct', 'group'].includes(conversationThread.dataset.conversationType || '');
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

        const conversationSearch = window.GakumasConversationSearch?.init({
            conversationThread,
            isGroupConversation,
            mentionMembers,
            messageTypeLabels,
            currentUserId,
        }) || {
            // Fallbacks keep the page usable if a split module is not loaded.
            hasFilters: () => false,
            isOpen: () => false,
            messageSearchDate: () => '',
            messageSenderId: () => '',
            run: () => {},
            searchableMessageText: () => '',
        };
        window.GakumasMessageMentions?.init({
            messageInput,
            isGroupConversation,
            mentionSuggestions,
            mentionMembers,
            currentUserId,
        });
        // Feature modules expose small controllers so polling/rendering can stay in this file.
        const readReceipts = window.GakumasMessageReadReceipts?.init({
            isGroupConversation,
            messageModals,
        }) || {
            createControl: () => null,
            openModal: () => {},
            updateAll: () => {},
        };
        const typingStatus = window.GakumasMessageTyping?.init({
            conversationThread,
            conversationId,
            messageInput,
            supportsTypingIndicator,
        }) || {
            stopNow: () => {},
            updateIndicator: () => {},
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

        const createReplyPreviewElement = (replyPreview) => {
            if (!replyPreview || !replyPreview.message_id) {
                return null;
            }

            const link = document.createElement('a');
            link.href = `#message-${replyPreview.message_id}`;
            link.className = `chat-reply-preview${replyPreview.is_deleted ? ' deleted' : ''}`;
            link.dataset.replyPreview = '';

            const sender = document.createElement('strong');
            sender.textContent = replyPreview.sender_display_name || 'Someone';
            const body = document.createElement('span');
            body.textContent = replyPreview.body || '[No text]';

            link.append(sender, body);
            return link;
        };

        clearReplyComposer = () => {
            if (replyToMessageInput) {
                replyToMessageInput.value = '';
            }

            if (replyComposer) {
                replyComposer.hidden = true;
            }

            if (replyComposerSender) {
                replyComposerSender.textContent = '';
            }

            if (replyComposerPreview) {
                replyComposerPreview.textContent = '';
            }
        };

        setReplyComposer = (messageId, sender, preview) => {
            if (!replyToMessageInput || !replyComposer || !replyComposerSender || !replyComposerPreview) {
                return;
            }

            replyToMessageInput.value = String(messageId || '');
            replyComposerSender.textContent = sender || 'Someone';
            replyComposerPreview.textContent = preview || '[No text]';
            replyComposer.hidden = false;
            messageInput?.focus();
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
            article.dataset.messageSearchText = conversationSearch.searchableMessageText(message);
            article.dataset.messageSearchDate = conversationSearch.messageSearchDate(message);
            article.dataset.messageSenderId = conversationSearch.messageSenderId(message);

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
            article.dataset.messageSearchText = '';
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

            if (message.can_reply) {
                const replyButton = document.createElement('button');
                replyButton.type = 'button';
                replyButton.dataset.messageReplyOpen = '';
                replyButton.dataset.replyMessageId = String(message.id);
                replyButton.dataset.replySender = message.sender_display_name || (message.is_own ? 'You' : 'Someone');
                replyButton.dataset.replyPreview = String(message.body || '').replace(/\s+/g, ' ').trim().slice(0, 90) || '[No text]';
                replyButton.setAttribute('role', 'menuitem');
                replyButton.innerHTML = '<i class="bi bi-reply"></i><span>Reply</span>';
                panel.append(replyButton);
            }

            if (message.can_forward && forwardModal) {
                const forwardButton = document.createElement('button');
                forwardButton.type = 'button';
                forwardButton.dataset.messageForwardOpen = '';
                forwardButton.dataset.forwardMessageId = String(message.id);
                forwardButton.setAttribute('role', 'menuitem');
                forwardButton.innerHTML = '<i class="bi bi-forward-fill"></i><span>Forward</span>';
                panel.append(forwardButton);
            }

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

        // Create a full chat message element using JavaScript
        const createMessageElement = (message) => {
            const typeLabel = messageTypeLabels[message.message_type] || '';
            const article = document.createElement('article');
            article.className = `chat-message${message.is_own ? ' own' : ''}${typeLabel ? ' special' : ''}`;
            article.dataset.messageId = String(message.id);
            article.dataset.messageSearchText = conversationSearch.searchableMessageText(message);
            article.dataset.messageSearchDate = conversationSearch.messageSearchDate(message);
            article.dataset.messageSenderId = conversationSearch.messageSenderId(message);
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

            if (message.forwarded_from_label && message.message_type !== 'system') {
                const forwardedLabel = document.createElement('span');
                forwardedLabel.className = 'chat-forwarded-label';
                forwardedLabel.innerHTML = '<i class="bi bi-forward-fill" aria-hidden="true"></i>';
                forwardedLabel.append(document.createTextNode(`Forwarded from ${message.forwarded_from_label}`));
                bubble.append(forwardedLabel);
            }

            const replyPreview = createReplyPreviewElement(message.reply_preview);

            if (replyPreview) {
                bubble.append(replyPreview);
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

            const readReceiptButton = readReceipts.createControl(message);

            if (readReceiptButton) {
                meta.append(readReceiptButton);
            }

            if (message.can_reply || message.can_forward || message.can_edit || message.can_delete || message.can_pin) {
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

                if (conversationSearch.isOpen() && conversationSearch.hasFilters()) {
                    conversationSearch.run(false, true);
                }

                readReceipts.updateAll(conversationThread, data.read_receipts);
                typingStatus.updateIndicator(data.typing_users);

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

                    if (conversationSearch.isOpen() && conversationSearch.hasFilters()) {
                        conversationSearch.run(false, true);
                    }

                    // Updates the latest message ID stores in Javascript and HTML
                    lastMessageId = Math.max(lastMessageId, Number.parseInt(data.message.id, 10) || 0);
                    conversationThread.dataset.lastMessageId = String(lastMessageId);
                    // Scroll to the latest message after sending
                    conversationThread.scrollTop = conversationThread.scrollHeight;

                    // Clear the textarea after sending successfully
                    messageInput.value = '';
                    messageInput.dispatchEvent(new Event('input'));
                    clearReplyComposer();
                    typingStatus.stopNow();
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
        const replyButton = event.target.closest('[data-message-reply-open]');
        const forwardButton = event.target.closest('[data-message-forward-open]');
        const deleteButton = event.target.closest('[data-message-delete-submit]');
        const clearConversationButton = event.target.closest('[data-conversation-clear-submit]');
        const groupRemoveButton = event.target.closest('[data-group-remove-submit]');
        const readReceiptButton = event.target.closest('[data-read-receipt]');
        const replyPreviewLink = event.target.closest('a[data-reply-preview]');

        if (replyPreviewLink) {
            const targetId = (replyPreviewLink.getAttribute('href') || '').replace('#message-', '');
            const targetMessage = targetId
                ? document.querySelector(`[data-message-id="${CSS.escape(targetId)}"]`)
                : null;

            if (targetMessage) {
                event.preventDefault();
                targetMessage.scrollIntoView({ behavior: 'smooth', block: 'center' });
                targetMessage.classList.add('reply-jump-target');
                window.setTimeout(() => {
                    targetMessage.classList.remove('reply-jump-target');
                }, 1800);
            }

            return;
        }

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

        if (readReceiptButton && readReceiptButton.dataset.readMode !== 'direct') {
            readReceipts.openModal(readReceiptButton);
            return;
        }

        if (editButton) {
            const bubble = editButton.closest('.chat-message-bubble');
            closeMessageActionMenus();
            setMessageEditVisibility(bubble, true);
            return;
        }

        if (replyButton) {
            closeMessageActionMenus();
            setReplyComposer(
                replyButton.dataset.replyMessageId || '',
                replyButton.dataset.replySender || 'Someone',
                replyButton.dataset.replyPreview || '[No text]'
            );
            return;
        }

        if (forwardButton) {
            closeMessageActionMenus();

            if (forwardMessageInput && forwardModal) {
                forwardMessageInput.value = forwardButton.dataset.forwardMessageId || '';
                forwardModal.querySelectorAll('input[name="target_conversation_id"]').forEach((input) => {
                    input.checked = false;
                });
                messageModals.open(forwardModal, forwardButton);
            }

            return;
        }

        if (deleteButton && !window.confirm('Delete this message? This cannot be undone.')) {
            event.preventDefault();
            return;
        }

        // Clear chat is private to this user, but still asks because the local history disappears.
        if (
            clearConversationButton &&
            !window.confirm('Clear this chat for you? Other members will still see the messages.')
        ) {
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

    replyCancel?.addEventListener('click', clearReplyComposer);

});
