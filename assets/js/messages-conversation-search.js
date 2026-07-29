(() => {
    // Search controller for the conversation page. messages.js calls this after new messages arrive.
    window.GakumasConversationSearch = {
        init({
            conversationThread,
            isGroupConversation,
            mentionMembers,
            messageTypeLabels,
            currentUserId,
        }) {
            const searchToggle = document.querySelector('[data-conversation-message-search-toggle]');
            const searchPanel = document.querySelector('[data-conversation-message-search]');
            const searchInput = document.querySelector('[data-conversation-message-search-input]');
            const searchAfter = document.querySelector('[data-conversation-message-search-after]');
            const searchBefore = document.querySelector('[data-conversation-message-search-before]');
            const mentionSuggestions = document.querySelector('[data-search-mention-suggestions]');
            const searchCount = document.querySelector('[data-conversation-message-search-count]');
            const searchPrev = document.querySelector('[data-conversation-message-search-prev]');
            const searchNext = document.querySelector('[data-conversation-message-search-next]');
            const searchClear = document.querySelector('[data-conversation-message-search-clear]');
            let matches = [];
            let activeIndex = -1;

            // These values are written to each message row so searching works after polling adds messages.
            const searchableMessageText = (message) => [
                message.body || '',
                ...(Array.isArray(message.attachments)
                    ? message.attachments.map((attachment) => attachment.original_name || '')
                    : []),
                // Sticker labels make visual-only messages discoverable in conversation search.
                message.sticker?.label || '',
                message.sender_display_name || '',
                messageTypeLabels[message.message_type] || '',
            ].join(' ').trim().toLocaleLowerCase();

            const messageSearchDate = (message) => String(message.created_at || '').slice(0, 10);

            const messageSenderId = (message) => {
                const senderId = Number.parseInt(message.sender_id, 10);

                if (Number.isInteger(senderId) && senderId > 0) {
                    return String(senderId);
                }

                return message.is_own ? String(currentUserId) : '';
            };

            const hasFilters = () => Boolean(
                searchInput?.value.trim()
                || searchAfter?.value
                || searchBefore?.value
            );

            const hideMentionSuggestions = () => {
                if (mentionSuggestions) {
                    mentionSuggestions.hidden = true;
                    mentionSuggestions.replaceChildren();
                }
            };

            const selectedMentionMember = () => {
                if (!isGroupConversation || !searchInput) {
                    return null;
                }

                const query = searchInput.value.trim();
                const selectedSenderId = searchInput.dataset.searchSenderId || '';
                const selectedSenderName = searchInput.dataset.searchSenderName || '';

                // A selected @name searches by sender id instead of plain message text.
                if (
                    selectedSenderId !== ''
                    && selectedSenderName !== ''
                    && query.toLocaleLowerCase() === `@${selectedSenderName}`.toLocaleLowerCase()
                ) {
                    return mentionMembers.find((member) => String(member.user_id) === selectedSenderId) || null;
                }

                if (!query.startsWith('@')) {
                    return null;
                }

                const searchName = query.slice(1).trim().toLocaleLowerCase();

                if (searchName === '') {
                    return null;
                }

                return mentionMembers.find((member) => (
                    (member.display_name || '').toLocaleLowerCase() === searchName
                )) || null;
            };

            const setActiveMatch = (index, shouldScroll = true) => {
                matches.forEach((article) => {
                    article.classList.remove('search-active');
                });

                if (matches.length === 0) {
                    activeIndex = -1;

                    if (searchCount) {
                        searchCount.textContent = hasFilters() ? '0 results' : 'Search messages';
                    }

                    return;
                }

                activeIndex = (index + matches.length) % matches.length;
                const activeArticle = matches[activeIndex];
                activeArticle.classList.add('search-active');

                if (searchCount) {
                    searchCount.textContent = `${activeIndex + 1} / ${matches.length}`;
                }

                if (shouldScroll) {
                    activeArticle.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            };

            const run = (shouldScroll = true, preserveActiveMatch = false) => {
                if (!searchInput) {
                    return;
                }

                const query = searchInput.value.trim().toLocaleLowerCase();
                const afterDate = searchAfter?.value || '';
                const beforeDate = searchBefore?.value || '';
                const selectedMember = selectedMentionMember();
                const selectedSenderId = selectedMember ? String(selectedMember.user_id) : '';
                const articles = Array.from(conversationThread.querySelectorAll('.chat-message'));
                const activeMessageId = preserveActiveMatch
                    ? matches[activeIndex]?.dataset.messageId
                    : '';

                // Recalculate all matches because polling may add, edit, or delete messages.
                articles.forEach((article) => {
                    article.classList.remove('search-match', 'search-active');
                });

                if (query === '' && afterDate === '' && beforeDate === '') {
                    matches = [];
                    activeIndex = -1;

                    if (searchCount) {
                        searchCount.textContent = 'Search messages';
                    }

                    return;
                }

                matches = articles.filter((article) => {
                    const searchText = article.dataset.messageSearchText
                        || article.querySelector('[data-message-body]')?.textContent.toLocaleLowerCase()
                        || '';
                    const messageDate = article.dataset.messageSearchDate || '';
                    const matchesText = query === '' || selectedSenderId !== '' || searchText.includes(query);
                    const matchesSender = selectedSenderId === '' || article.dataset.messageSenderId === selectedSenderId;
                    const matchesAfter = afterDate === '' || (messageDate !== '' && messageDate >= afterDate);
                    const matchesBefore = beforeDate === '' || (messageDate !== '' && messageDate <= beforeDate);

                    return matchesText && matchesSender && matchesAfter && matchesBefore;
                });

                matches.forEach((article) => {
                    article.classList.add('search-match');
                });

                const preservedIndex = activeMessageId
                    ? matches.findIndex((article) => article.dataset.messageId === activeMessageId)
                    : -1;
                setActiveMatch(preservedIndex >= 0 ? preservedIndex : 0, shouldScroll);
            };

            const close = () => {
                if (!searchPanel || !searchToggle || !searchInput) {
                    return;
                }

                hideMentionSuggestions();
                searchPanel.hidden = true;
                searchToggle.setAttribute('aria-expanded', 'false');
                searchInput.value = '';
                delete searchInput.dataset.searchSenderId;
                delete searchInput.dataset.searchSenderName;

                if (searchAfter) {
                    searchAfter.value = '';
                }

                if (searchBefore) {
                    searchBefore.value = '';
                }

                run(false);
                searchToggle.focus();
            };

            const showMentionSuggestions = () => {
                if (!isGroupConversation || !mentionSuggestions || !searchInput) {
                    return;
                }

                const query = searchInput.value.trim();

                if (!query.startsWith('@')) {
                    hideMentionSuggestions();
                    return;
                }

                const searchName = query.slice(1).trim().toLocaleLowerCase();
                // In group search, @ opens the member picker and turns the query into a sender filter.
                const options = mentionMembers
                    .filter((member) => {
                        const searchText = `${member.display_name || ''} ${member.role_detail || ''}`.toLocaleLowerCase();

                        return searchName === '' || searchText.includes(searchName);
                    })
                    .slice(0, 7);

                mentionSuggestions.replaceChildren();

                if (options.length === 0) {
                    hideMentionSuggestions();
                    return;
                }

                options.forEach((member) => {
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.className = 'mention-suggestion-item';
                    button.dataset.searchMentionName = member.display_name || '';
                    button.dataset.searchMentionUserId = String(member.user_id || '');

                    const avatar = document.createElement('img');
                    avatar.src = member.avatar || '/gakumas-sms/assets/images/avatars/default.webp';
                    avatar.alt = '';

                    const copy = document.createElement('span');
                    const name = document.createElement('strong');
                    name.textContent = `@${member.display_name || 'member'}`;
                    const detail = document.createElement('small');
                    detail.textContent = member.role_detail || 'Member';

                    copy.append(name, detail);
                    button.append(avatar, copy);
                    mentionSuggestions.append(button);
                });

                mentionSuggestions.hidden = false;
            };

            if (searchToggle && searchPanel && searchInput) {
                searchToggle.addEventListener('click', () => {
                    const shouldOpen = searchPanel.hidden;
                    searchPanel.hidden = !shouldOpen;
                    searchToggle.setAttribute('aria-expanded', String(shouldOpen));

                    if (shouldOpen) {
                        window.requestAnimationFrame(() => {
                            searchInput.focus();
                            searchInput.select();
                            run(false);
                        });
                    }
                });

                searchInput.addEventListener('input', () => {
                    if (
                        searchInput.dataset.searchSenderName &&
                        searchInput.value.trim().toLocaleLowerCase()
                            !== `@${searchInput.dataset.searchSenderName}`.toLocaleLowerCase()
                    ) {
                        delete searchInput.dataset.searchSenderId;
                        delete searchInput.dataset.searchSenderName;
                    }

                    showMentionSuggestions();
                    run(true);
                });
                searchAfter?.addEventListener('input', () => run(true));
                searchBefore?.addEventListener('input', () => run(true));

                searchInput.addEventListener('keydown', (event) => {
                    if (event.key === 'Escape') {
                        event.preventDefault();
                        if (mentionSuggestions && !mentionSuggestions.hidden) {
                            hideMentionSuggestions();
                        } else {
                            close();
                        }
                        return;
                    }

                    if (event.key === 'Enter' && matches.length > 0) {
                        event.preventDefault();
                        setActiveMatch(activeIndex + (event.shiftKey ? -1 : 1), true);
                    }
                });

                mentionSuggestions?.addEventListener('mousedown', (event) => {
                    event.preventDefault();
                });

                mentionSuggestions?.addEventListener('click', (event) => {
                    const button = event.target.closest('[data-search-mention-name]');

                    if (!button || !searchInput) {
                        return;
                    }

                    searchInput.value = `@${button.dataset.searchMentionName || ''}`;
                    searchInput.dataset.searchSenderId = button.dataset.searchMentionUserId || '';
                    searchInput.dataset.searchSenderName = button.dataset.searchMentionName || '';
                    hideMentionSuggestions();
                    run(true);
                    searchInput.focus();
                });

                document.addEventListener('click', (event) => {
                    if (
                        mentionSuggestions?.contains(event.target) ||
                        searchInput.contains(event.target)
                    ) {
                        return;
                    }

                    hideMentionSuggestions();
                });

                searchPrev?.addEventListener('click', () => {
                    setActiveMatch(activeIndex - 1, true);
                });

                searchNext?.addEventListener('click', () => {
                    setActiveMatch(activeIndex + 1, true);
                });

                searchClear?.addEventListener('click', () => {
                    if (
                        searchInput.value.trim() === ''
                        && (searchAfter?.value || '') === ''
                        && (searchBefore?.value || '') === ''
                    ) {
                        close();
                        return;
                    }

                    searchInput.value = '';
                    delete searchInput.dataset.searchSenderId;
                    delete searchInput.dataset.searchSenderName;

                    if (searchAfter) {
                        searchAfter.value = '';
                    }

                    if (searchBefore) {
                        searchBefore.value = '';
                    }

                    run(false);
                    searchInput.focus();
                });
            }

            return {
                hasFilters,
                isOpen: () => Boolean(searchPanel && !searchPanel.hidden),
                messageSearchDate,
                messageSenderId,
                run,
                searchableMessageText,
            };
        },
    };
})();
