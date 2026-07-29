window.GakumasMessageActions = (() => {
    const init = ({
        messageInput,
        replyToMessageInput,
        replyComposer,
        replyComposerSender,
        replyComposerPreview,
        replyCancel,
        forwardModal,
        forwardMessageInput,
        messageModals,
        readReceipts,
        reactionUI,
    }) => {
        const setMessageEditVisibility = (bubble, isEditing) => {
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

        const clearReplyComposer = () => {
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

        const setReplyComposer = (messageId, sender, preview) => {
            if (!replyToMessageInput || !replyComposer || !replyComposerSender || !replyComposerPreview) {
                return;
            }

            replyToMessageInput.value = String(messageId || '');
            replyComposerSender.textContent = sender || 'Someone';
            replyComposerPreview.textContent = preview || '[No text]';
            replyComposer.hidden = false;
            messageInput?.focus();
        };

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

        document.addEventListener('click', (event) => {
            const actionToggle = event.target.closest('[data-message-action-toggle]');
            const editButton = event.target.closest('[data-message-edit-open]');
            const editCancelButton = event.target.closest('[data-message-edit-cancel]');
            const reactButton = event.target.closest('[data-message-react-open]');
            const reactionMoreButton = event.target.closest('[data-reaction-more-toggle]');
            const reactionEmojiButton = event.target.closest('[data-reaction-emoji]');
            const existingReactionButton = event.target.closest('[data-message-reaction]');
            const composerEmojiButton = event.target.closest('[data-message-emoji-insert]');
            const composerPanelModeButton = event.target.closest('[data-message-panel-mode]');
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

            if (reactButton) {
                reactionUI.openReactionPicker(
                    reactButton,
                    reactButton.dataset.reactMessageId || ''
                );
                closeMessageActionMenus();
                return;
            }

            if (reactionMoreButton) {
                reactionUI.toggleMoreReactions(reactionMoreButton);
                return;
            }

            if (reactionEmojiButton) {
                const picker = reactionEmojiButton.closest('[data-reaction-picker]');
                const messageId = picker?.dataset.reactionMessageId || '';
                const emoji = reactionEmojiButton.dataset.reactionEmoji || '';
                reactionUI.closeReactionPickers();
                reactionUI.submitReaction(messageId, emoji);
                return;
            }

            if (existingReactionButton) {
                const article = existingReactionButton.closest('[data-message-id]');
                const messageId = article?.dataset.messageId || '';
                const emoji = existingReactionButton.dataset.messageReaction || '';
                reactionUI.submitReaction(messageId, emoji);
                return;
            }

            if (composerEmojiButton) {
                reactionUI.insertEmojiIntoComposer(
                    composerEmojiButton.dataset.messageEmojiInsert || ''
                );
                return;
            }

            if (composerPanelModeButton) {
                reactionUI.setComposerPanelMode(
                    composerPanelModeButton.dataset.messagePanelMode || 'emoji'
                );
                return;
            }

            if (readReceiptButton && readReceiptButton.dataset.readMode !== 'direct') {
                readReceipts.openModal(readReceiptButton);
                return;
            }

            if (editButton) {
                closeMessageActionMenus();
                setMessageEditVisibility(editButton.closest('.chat-message-bubble'), true);
                return;
            }

            if (editCancelButton) {
                setMessageEditVisibility(editCancelButton.closest('.chat-message-bubble'), false);
                closeMessageActionMenus();
                reactionUI.closeReactionPickers();
                reactionUI.closeComposerEmojiPanel();
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

            if (
                clearConversationButton
                && !window.confirm('Clear this chat for you? Other members will still see the messages.')
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

            if (!event.target.closest('[data-reaction-picker]')) {
                reactionUI.closeReactionPickers();
            }

            if (
                !event.target.closest('[data-message-emoji-panel]')
                && !event.target.closest('[data-message-emoji-toggle]')
            ) {
                reactionUI.closeComposerEmojiPanel();
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closeMessageActionMenus();
                reactionUI.closeReactionPickers();
                reactionUI.closeComposerEmojiPanel();
            }
        });

        replyCancel?.addEventListener('click', clearReplyComposer);

        return {
            clearReplyComposer,
            closeMessageActionMenus,
            setMessageEditVisibility,
            setReplyComposer,
        };
    };

    return { init };
})();
