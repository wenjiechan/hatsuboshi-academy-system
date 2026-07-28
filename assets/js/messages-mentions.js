(() => {
    // Handles @ mention suggestions inside the message composer.
    window.GakumasMessageMentions = {
        init({
            messageInput,
            isGroupConversation,
            mentionSuggestions,
            mentionMembers,
            currentUserId,
        }) {
            if (!isGroupConversation || !mentionSuggestions || !messageInput) {
                return;
            }

            const hide = () => {
                mentionSuggestions.hidden = true;
                mentionSuggestions.replaceChildren();
            };

            const currentQuery = () => {
                const caret = messageInput.selectionStart || 0;
                const textBeforeCaret = messageInput.value.slice(0, caret);
                const atIndex = textBeforeCaret.lastIndexOf('@');

                if (atIndex < 0) {
                    return null;
                }

                const charBeforeAt = atIndex > 0 ? textBeforeCaret[atIndex - 1] : '';
                const query = textBeforeCaret.slice(atIndex + 1);

                // Mentions only start after whitespace or an opening parenthesis.
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

            const insert = (mentionValue) => {
                const mention = currentQuery();

                if (!mention) {
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
                hide();
            };

            const show = () => {
                const mention = currentQuery();

                if (!mention) {
                    hide();
                    return;
                }

                const everyoneOption = {
                    display_name: 'everyone',
                    role_detail: 'Notify everyone',
                    avatar: '/gakumas-sms/assets/images/avatars/default.webp',
                    mention_value: 'everyone',
                };
                // Do not suggest the current user, but allow @everyone for group announcements.
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
                    hide();
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

            messageInput.addEventListener('input', show);
            messageInput.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && !mentionSuggestions.hidden) {
                    event.preventDefault();
                    hide();
                }
            });

            mentionSuggestions.addEventListener('mousedown', (event) => {
                event.preventDefault();
            });

            mentionSuggestions.addEventListener('click', (event) => {
                const button = event.target.closest('[data-mention-value]');

                if (button) {
                    insert(button.dataset.mentionValue || '');
                }
            });

            document.addEventListener('click', (event) => {
                if (mentionSuggestions.contains(event.target) || messageInput.contains(event.target)) {
                    return;
                }

                hide();
            });
        },
    };
})();
