(() => {
    // Sends typing status and renders the "is typing" line for direct/group conversations.
    window.GakumasMessageTyping = {
        init({
            conversationThread,
            conversationId,
            messageInput,
            supportsTypingIndicator,
        }) {
            let typingStopTimer = null;
            let lastTypingState = false;

            // Avoid sending duplicate typing states while the user keeps typing.
            const sendStatus = async (isTyping) => {
                if (!supportsTypingIndicator || lastTypingState === isTyping) {
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

            const queueStop = () => {
                window.clearTimeout(typingStopTimer);
                typingStopTimer = window.setTimeout(() => {
                    sendStatus(false);
                }, 1800);
            };

            const stopNow = () => {
                window.clearTimeout(typingStopTimer);
                sendStatus(false);
            };

            const updateIndicator = (typingUsers) => {
                const indicator = document.querySelector('[data-typing-indicator]');
                let hideTimer = updateIndicator.hideTimer || null;

                // Delay hiding slightly so polling does not make the indicator flicker.
                if (!indicator || !Array.isArray(typingUsers) || typingUsers.length === 0) {
                    if (indicator) {
                        window.clearTimeout(hideTimer);
                        updateIndicator.hideTimer = window.setTimeout(() => {
                            indicator.hidden = true;
                            indicator.textContent = '';
                        }, 900);
                    }

                    return;
                }

                const names = typingUsers.map((user) => user.display_name).filter(Boolean);

                if (names.length === 0) {
                    window.clearTimeout(hideTimer);
                    updateIndicator.hideTimer = window.setTimeout(() => {
                        indicator.hidden = true;
                        indicator.textContent = '';
                    }, 900);
                    return;
                }

                window.clearTimeout(hideTimer);
                indicator.hidden = false;
                indicator.textContent = names.length === 1
                    ? `${names[0]} is typing...`
                    : `${names.slice(0, 2).join(' and ')} are typing...`;
            };

            if (supportsTypingIndicator && messageInput) {
                messageInput.addEventListener('input', () => {
                    if (messageInput.value.trim() === '') {
                        stopNow();
                        return;
                    }

                    sendStatus(true);
                    queueStop();
                });

                messageInput.addEventListener('blur', stopNow);
                window.addEventListener('beforeunload', stopNow);
            }

            return {
                stopNow,
                updateIndicator,
            };
        },
    };
})();
