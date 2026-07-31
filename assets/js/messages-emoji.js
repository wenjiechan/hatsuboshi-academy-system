window.GakumasMessageEmoji = (() => {
    const quickReactionEmojis = ['👍', '❤️', '😂', '😢', '🙏', '🔥', '✨'];
    const moreReactionEmojis = [
        '👀', '💬', '❓', '❗', '⚠️', '🚫', '👌', '🤝',
        '👎', '❌', '✅', '💯', '👏', '💪', '💡', '🙋',
        '😮', '😡', '😭', '🤣', '😅', '🎉', '🙌', '🤩',
        '🥺', '😊', '🥰', '💕', '😳', '😏', '😉', '😎',
        '😇', '😬', '🤭', '💭', '😤', '😒', '🙄', '🤔',
        '😵', '😆', '😝', '😈', '🤗', '🌷', '🌸', '🎀',
        '💫', '🌟', '👑', '☀️', '💸', '📌', '💤', '😴',
        '☕', '😌', '💅', '🥱', '😐', '🐺', '🍡', '✌️',
    ];
    const composerEmojiOptions = Array.from(new Set([
        ...quickReactionEmojis,
        ...moreReactionEmojis,
    ]));

    const init = ({
        conversationThread,
        conversationId,
        messageInput,
        messageComposer,
        messageEmojiToggle,
        messageSendError,
    }) => {
        let stopReactionPickerTracking = () => {};
        let positionActiveReactionPicker = () => {};

        const renderMessageReactions = (article, reactions = []) => {
            const bubble = article?.querySelector('.chat-message-bubble');
            const body = article?.querySelector('[data-message-body]');

            if (!article || !bubble || !body) {
                return;
            }

            let reactionList = article.querySelector('[data-message-reactions]');

            if (!Array.isArray(reactions) || reactions.length === 0) {
                reactionList?.remove();
                return;
            }

            if (!reactionList) {
                reactionList = document.createElement('div');
                reactionList.className = 'chat-message-reactions';
                reactionList.dataset.messageReactions = '';
                const attachments = article.querySelector('[data-message-attachments]');
                (attachments || body).after(reactionList);
            }

            reactionList.replaceChildren();

            reactions.forEach((reaction) => {
                const emoji = reaction.emoji || '';
                const count = Number.parseInt(reaction.count, 10) || 0;

                if (!emoji || count <= 0) {
                    return;
                }

                const button = document.createElement('button');
                button.type = 'button';
                button.className = `chat-message-reaction${reaction.reacted ? ' reacted' : ''}`;
                button.dataset.messageReaction = emoji;
                button.title = reaction.user_names || '';
                button.setAttribute('aria-label', `React with ${emoji}`);

                const emojiSpan = document.createElement('span');
                emojiSpan.textContent = emoji;
                const countText = document.createElement('strong');
                countText.textContent = String(count);

                button.append(emojiSpan, countText);
                reactionList.append(button);
            });
        };

        const closeReactionPickers = (exceptPicker = null) => {
            document.querySelectorAll('[data-reaction-picker]').forEach((picker) => {
                if (picker !== exceptPicker) {
                    picker.remove();
                }
            });

            if (!exceptPicker) {
                stopReactionPickerTracking();
                stopReactionPickerTracking = () => {};
                positionActiveReactionPicker = () => {};
            }
        };

        const openReactionPicker = (trigger, messageId) => {
            closeReactionPickers();

            if (!trigger || !messageId) {
                return;
            }

            const article = conversationThread.querySelector(
                `[data-message-id="${CSS.escape(String(messageId))}"]`
            );
            const bubble = article?.querySelector('.chat-message-bubble');

            if (!article || !bubble) {
                return;
            }

            const picker = document.createElement('div');
            picker.className = 'chat-reaction-picker';
            picker.dataset.reactionPicker = '';
            picker.dataset.reactionMessageId = String(messageId);
            picker.setAttribute('role', 'menu');

            const quickRow = document.createElement('div');
            quickRow.className = 'chat-reaction-picker-row';

            quickReactionEmojis.forEach((emoji) => {
                const button = document.createElement('button');
                button.type = 'button';
                button.dataset.reactionEmoji = emoji;
                button.setAttribute('role', 'menuitem');
                button.setAttribute('aria-label', `React with ${emoji}`);
                button.textContent = emoji;
                quickRow.append(button);
            });

            const moreButton = document.createElement('button');
            moreButton.type = 'button';
            moreButton.className = 'chat-reaction-more-toggle';
            moreButton.dataset.reactionMoreToggle = '';
            moreButton.setAttribute('aria-label', 'More reactions');
            moreButton.setAttribute('aria-expanded', 'false');
            moreButton.textContent = '+';
            quickRow.append(moreButton);
            picker.append(quickRow);

            const moreGrid = document.createElement('div');
            moreGrid.className = 'chat-reaction-more-grid';
            moreGrid.dataset.reactionMoreGrid = '';
            moreGrid.hidden = true;

            moreReactionEmojis.forEach((emoji) => {
                const button = document.createElement('button');
                button.type = 'button';
                button.dataset.reactionEmoji = emoji;
                button.setAttribute('role', 'menuitem');
                button.setAttribute('aria-label', `React with ${emoji}`);
                button.textContent = emoji;
                moreGrid.append(button);
            });

            picker.append(moreGrid);
            document.body.append(picker);

            positionActiveReactionPicker = () => {
                if (!picker.isConnected || !bubble.isConnected) {
                    closeReactionPickers();
                    return;
                }

                const bubbleRect = bubble.getBoundingClientRect();
                const pickerWidth = picker.offsetWidth;
                const viewportPadding = 8;
                const preferredLeft = article.classList.contains('own')
                    ? bubbleRect.right - pickerWidth
                    : bubbleRect.left;
                const left = Math.min(
                    Math.max(viewportPadding, preferredLeft),
                    Math.max(viewportPadding, window.innerWidth - pickerWidth - viewportPadding)
                );

                picker.style.left = `${Math.round(left)}px`;
                picker.style.top = `${Math.round(bubbleRect.bottom + 6)}px`;
            };

            const trackReactionPicker = () => positionActiveReactionPicker();
            window.addEventListener('resize', trackReactionPicker);
            window.addEventListener('scroll', trackReactionPicker, true);
            stopReactionPickerTracking = () => {
                window.removeEventListener('resize', trackReactionPicker);
                window.removeEventListener('scroll', trackReactionPicker, true);
            };

            window.requestAnimationFrame(positionActiveReactionPicker);
        };

        const toggleMoreReactions = (button) => {
            const picker = button?.closest('[data-reaction-picker]');
            const moreGrid = picker?.querySelector('[data-reaction-more-grid]');
            const isOpening = Boolean(moreGrid?.hidden);

            if (!moreGrid) {
                return;
            }

            moreGrid.hidden = !isOpening;
            button.setAttribute('aria-expanded', String(isOpening));
            window.requestAnimationFrame(positionActiveReactionPicker);
        };

        const submitReaction = async (messageId, emoji) => {
            if (!messageId || !emoji) {
                return;
            }

            const article = conversationThread.querySelector(
                `[data-message-id="${CSS.escape(String(messageId))}"]`
            );

            try {
                const formData = new FormData();
                formData.set('csrf_token', conversationThread.dataset.csrfToken || '');
                formData.set('conversation_id', String(conversationId));
                formData.set('message_id', String(messageId));
                formData.set('emoji', emoji);

                const response = await fetch('/gakumas-sms/messages/react.php', {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: formData,
                });
                const data = await response.json();

                if (response.status === 401 && data.redirect_url) {
                    window.location.assign(data.redirect_url);
                    return;
                }

                if (!response.ok || !data.success) {
                    throw new Error(data.error || 'The reaction could not be saved.');
                }

                renderMessageReactions(article, data.reactions);
            } catch (error) {
                if (messageSendError) {
                    messageSendError.textContent = error instanceof Error
                        ? error.message
                        : 'The reaction could not be saved.';
                    messageSendError.hidden = false;
                }
            }
        };

        const closeComposerEmojiPanel = () => {
            const panel = document.querySelector('[data-message-emoji-panel]');

            if (panel) {
                panel.hidden = true;
            }

            messageEmojiToggle?.setAttribute('aria-expanded', 'false');
        };

        // PHP embeds the fixed catalog once; this keeps the picker fast and self-contained.
        const stickerKeyInput = messageComposer?.querySelector('[data-message-sticker-key]');
        const stickerPacksElement = document.querySelector('[data-message-sticker-packs]');
        let stickerPacks = [];

        try {
            const parsedPacks = JSON.parse(stickerPacksElement?.textContent || '[]');
            stickerPacks = Array.isArray(parsedPacks) ? parsedPacks : [];
        } catch {
            stickerPacks = [];
        }

        const stickersByKey = new Map();
        stickerPacks.forEach((pack) => {
            (Array.isArray(pack.stickers) ? pack.stickers : []).forEach((sticker) => {
                if (sticker?.key && sticker?.url) {
                    stickersByKey.set(sticker.key, sticker);
                }
            });
        });

        const renderMessageSticker = (container, sticker) => {
            if (!container || !sticker?.url) {
                return false;
            }

            container.classList.add('chat-message-body-sticker');
            container.replaceChildren();

            const wrapper = document.createElement('span');
            wrapper.className = 'chat-message-sticker';
            wrapper.dataset.messageSticker = '';

            const image = document.createElement('img');
            image.src = sticker.url;
            image.alt = sticker.label || 'Sticker';
            image.loading = 'lazy';
            image.decoding = 'async';
            wrapper.append(image);
            container.append(wrapper);

            return true;
        };

        const hasSelectedSticker = () => Boolean(stickerKeyInput?.value);

        const clearSelectedSticker = () => {
            if (stickerKeyInput) {
                stickerKeyInput.value = '';
            }
        };

        // Stickers are standalone messages, so selecting one submits the composer immediately.
        const selectSticker = (key) => {
            if (!messageComposer || !stickerKeyInput || !stickersByKey.has(key)) {
                return;
            }

            const draftBody = messageInput?.value || '';

            if (draftBody !== '') {
                messageComposer.dataset.messageStickerDraft = draftBody;
                messageInput.value = '';
                messageInput.dispatchEvent(new Event('input', { bubbles: true }));
            } else {
                delete messageComposer.dataset.messageStickerDraft;
            }

            stickerKeyInput.value = key;
            closeComposerEmojiPanel();
            messageComposer.requestSubmit();

            if (draftBody !== '') {
                messageInput.value = draftBody;
                messageInput.dispatchEvent(new Event('input', { bubbles: true }));
            }
        };

        // Rebuild the grid whenever a user switches between available packs.
        const createStickerPicker = () => {
            const picker = document.createElement('div');
            picker.className = 'message-sticker-picker';
            picker.dataset.messageStickerPicker = '';
            picker.hidden = true;

            if (stickerPacks.length === 0) {
                picker.classList.add('empty');
                picker.innerHTML = '<i class="bi bi-sticky" aria-hidden="true"></i><strong>No sticker packs yet</strong>';
                return picker;
            }

            const tabs = document.createElement('div');
            tabs.className = 'message-sticker-pack-tabs';
            tabs.setAttribute('role', 'tablist');

            const grid = document.createElement('div');
            grid.className = 'message-sticker-grid';
            grid.dataset.messageStickerGrid = '';
            let activePackId = stickerPacks[0].id;

            const renderPack = (packId) => {
                const pack = stickerPacks.find((item) => item.id === packId) || stickerPacks[0];

                if (!pack) {
                    return;
                }

                activePackId = pack.id;
                tabs.replaceChildren();
                grid.replaceChildren();

                stickerPacks.forEach((item) => {
                    const tab = document.createElement('button');
                    tab.type = 'button';
                    tab.className = item.id === activePackId ? 'active' : '';
                    tab.setAttribute('role', 'tab');
                    tab.setAttribute('aria-selected', String(item.id === activePackId));
                    tab.title = item.name;
                    tab.textContent = item.name;
                    tab.addEventListener('click', (event) => {
                        event.preventDefault();
                        event.stopPropagation();
                        renderPack(item.id);
                    });
                    tabs.append(tab);
                });

                (Array.isArray(pack.stickers) ? pack.stickers : []).forEach((sticker) => {
                    if (!sticker?.key || !sticker?.url) {
                        return;
                    }

                    const button = document.createElement('button');
                    button.type = 'button';
                    button.className = 'message-sticker-choice';
                    button.dataset.messageStickerSelect = sticker.key;
                    button.setAttribute('aria-label', `Send ${sticker.label || 'sticker'}`);

                    const image = document.createElement('img');
                    image.src = sticker.url;
                    image.alt = '';
                    image.loading = 'lazy';
                    image.decoding = 'async';
                    button.append(image);
                    button.addEventListener('click', () => selectSticker(sticker.key));
                    grid.append(button);
                });
            };

            renderPack(activePackId);
            picker.append(tabs, grid);

            return picker;
        };

        const insertEmojiIntoComposer = (emoji) => {
            if (!messageInput || !emoji) {
                return;
            }

            const start = messageInput.selectionStart ?? messageInput.value.length;
            const end = messageInput.selectionEnd ?? messageInput.value.length;
            const before = messageInput.value.slice(0, start);
            const after = messageInput.value.slice(end);
            const nextValue = `${before}${emoji}${after}`;

            if (nextValue.length > Number.parseInt(messageInput.maxLength || '5000', 10)) {
                return;
            }

            messageInput.value = nextValue;
            const nextCursor = start + emoji.length;
            messageInput.focus();
            messageInput.setSelectionRange(nextCursor, nextCursor);
            messageInput.dispatchEvent(new Event('input', { bubbles: true }));
        };

        const ensureComposerEmojiPanel = () => {
            let panel = document.querySelector('[data-message-emoji-panel]');

            if (panel || !messageComposer) {
                return panel;
            }

            panel = document.createElement('div');
            panel.className = 'message-emoji-panel';
            panel.dataset.messageEmojiPanel = '';
            panel.hidden = true;

            const panelHeader = document.createElement('div');
            panelHeader.className = 'message-emoji-panel-header';
            panelHeader.textContent = 'Emoji';
            panel.append(panelHeader);

            const emojiGrid = document.createElement('div');
            emojiGrid.className = 'message-emoji-grid';
            emojiGrid.dataset.messageEmojiGrid = '';

            composerEmojiOptions.forEach((emoji) => {
                const button = document.createElement('button');
                button.type = 'button';
                button.dataset.messageEmojiInsert = emoji;
                button.setAttribute('aria-label', `Insert ${emoji}`);
                button.textContent = emoji;
                emojiGrid.append(button);
            });

            const stickerNextButton = document.createElement('button');
            stickerNextButton.type = 'button';
            stickerNextButton.className = 'message-emoji-next-tab';
            stickerNextButton.dataset.messagePanelMode = 'sticker';
            stickerNextButton.setAttribute('aria-label', 'Open stickers');
            stickerNextButton.innerHTML = '<i class="bi bi-sticky" aria-hidden="true"></i>';
            emojiGrid.append(stickerNextButton);
            panel.append(emojiGrid);

            panel.append(createStickerPicker());

            const panelFooter = document.createElement('div');
            panelFooter.className = 'message-emoji-panel-footer';
            panelFooter.innerHTML = `
                <button type="button" class="active" data-message-panel-mode="emoji" aria-label="Emoji tab" aria-pressed="true">
                    <i class="bi bi-emoji-smile" aria-hidden="true"></i>
                </button>
                <button type="button" data-message-panel-mode="sticker" aria-label="Sticker tab" aria-pressed="false">
                    <i class="bi bi-sticky" aria-hidden="true"></i>
                </button>
            `;
            panel.append(panelFooter);

            document.body.append(panel);
            return panel;
        };

        const positionComposerEmojiPanel = (panel) => {
            if (!panel || panel.hidden || !messageEmojiToggle) {
                return;
            }

            const toggleRect = messageEmojiToggle.getBoundingClientRect();
            const viewportPadding = 12;
            const panelWidth = panel.offsetWidth;
            const panelHeight = panel.offsetHeight;
            const left = Math.min(
                Math.max(
                    viewportPadding,
                    toggleRect.left + (toggleRect.width / 2) - (panelWidth / 2)
                ),
                Math.max(viewportPadding, window.innerWidth - panelWidth - viewportPadding)
            );
            const spaceAbove = toggleRect.top - viewportPadding;
            const top = spaceAbove >= panelHeight + 10
                ? toggleRect.top - panelHeight - 10
                : Math.min(toggleRect.bottom + 10, window.innerHeight - panelHeight - viewportPadding);

            panel.style.left = `${left}px`;
            panel.style.top = `${Math.max(viewportPadding, top)}px`;
        };

        const setComposerPanelMode = (mode) => {
            const panel = ensureComposerEmojiPanel();

            if (!panel) {
                return;
            }

            const isStickerMode = mode === 'sticker';
            const header = panel.querySelector('.message-emoji-panel-header');
            const emojiGrid = panel.querySelector('[data-message-emoji-grid]');
            const stickerPicker = panel.querySelector('[data-message-sticker-picker]');

            if (header) {
                header.textContent = isStickerMode ? 'Stickers' : 'Emoji';
            }

            if (emojiGrid) {
                emojiGrid.hidden = isStickerMode;
            }

            if (stickerPicker) {
                stickerPicker.hidden = !isStickerMode;
            }

            panel.querySelectorAll('[data-message-panel-mode]').forEach((button) => {
                const isActive = button.dataset.messagePanelMode === mode;
                button.classList.toggle('active', isActive);
                button.setAttribute('aria-pressed', String(isActive));
            });

            if (!panel.hidden) {
                window.requestAnimationFrame(() => positionComposerEmojiPanel(panel));
            }
        };

        const toggleComposerEmojiPanel = () => {
            const panel = ensureComposerEmojiPanel();

            if (!panel || !messageEmojiToggle) {
                return;
            }

            const isOpening = panel.hidden;
            closeReactionPickers();

            if (isOpening) {
                setComposerPanelMode('emoji');
            }

            panel.hidden = !isOpening;
            messageEmojiToggle.setAttribute('aria-expanded', String(isOpening));

            if (isOpening) {
                positionComposerEmojiPanel(panel);
            }
        };

        messageEmojiToggle?.addEventListener('click', (event) => {
            event.preventDefault();
            toggleComposerEmojiPanel();
        });

        window.addEventListener('resize', () => {
            positionComposerEmojiPanel(document.querySelector('[data-message-emoji-panel]'));
        });

        return {
            closeComposerEmojiPanel,
            closeReactionPickers,
            insertEmojiIntoComposer,
            clearSelectedSticker,
            hasSelectedSticker,
            openReactionPicker,
            renderMessageSticker,
            renderMessageReactions,
            setComposerPanelMode,
            submitReaction,
            toggleMoreReactions,
        };
    };

    return { init };
})();
