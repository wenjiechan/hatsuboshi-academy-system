(() => {
    // Handles both direct-message read ticks and group "Read by N" receipts.
    const receiptText = (count) => `Read by ${count}`;

    window.GakumasMessageReadReceipts = {
        init({ isGroupConversation, messageModals }) {
            const updateButton = (article, receipt) => {
                const button = article?.querySelector('[data-read-receipt]');

                if (!button || !receipt) {
                    return;
                }

                if (button.dataset.readMode === 'direct') {
                    const isRead = receipt.is_read === true || receipt.is_read === 1 || receipt.is_read === '1';
                    const readAt = receipt.read_at || '';

                    button.dataset.isRead = isRead ? '1' : '0';
                    button.dataset.readAt = readAt;
                    button.title = readAt ? `Read ${readAt}` : 'Read';
                    button.hidden = !isRead;
                    return;
                }

                const count = Number.parseInt(receipt.read_count, 10) || 0;
                button.dataset.readCount = String(count);
                button.dataset.readNames = receipt.read_names || '';
                button.dataset.readUsers = JSON.stringify(receipt.read_users || []);
                button.textContent = receiptText(count);
                button.hidden = count === 0;
            };

            const updateAll = (conversationThread, receipts) => {
                if (!Array.isArray(receipts)) {
                    return;
                }

                receipts.forEach((receipt) => {
                    const messageId = Number.parseInt(receipt.message_id, 10);
                    const article = conversationThread.querySelector(`[data-message-id="${messageId}"]`);
                    updateButton(article, receipt);
                });
            };

            const createControl = (message) => {
                if (!message.is_own || message.message_type === 'system' || message.deleted_at) {
                    return null;
                }

                const receipt = message.read_receipt || {};

                // Direct conversations use a quiet tick; groups use a clickable reader count.
                if (!isGroupConversation) {
                    const isRead = receipt.is_read === true || receipt.is_read === 1 || receipt.is_read === '1';
                    const readAt = receipt.read_at || '';
                    const tick = document.createElement('span');
                    tick.className = 'chat-read-tick';
                    tick.dataset.readReceipt = '';
                    tick.dataset.readMode = 'direct';
                    tick.dataset.isRead = isRead ? '1' : '0';
                    tick.dataset.readAt = readAt;
                    tick.title = readAt ? `Read ${readAt}` : 'Read';
                    tick.hidden = !isRead;
                    tick.innerHTML = '<i class="bi bi-check2-all" aria-hidden="true"></i><span>Read</span>';

                    return tick;
                }

                const count = Number.parseInt(receipt.read_count, 10) || 0;
                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'chat-read-receipt';
                button.dataset.readReceipt = '';
                button.dataset.readMode = 'group';
                button.dataset.readCount = String(count);
                button.dataset.readNames = receipt.read_names || '';
                button.dataset.readUsers = JSON.stringify(receipt.read_users || []);
                button.textContent = receiptText(count);
                button.hidden = count === 0;

                return button;
            };

            const openModal = (button) => {
                const modal = document.getElementById('readReceiptModal');
                const summary = modal?.querySelector('[data-read-receipt-summary]');
                const list = modal?.querySelector('[data-read-receipt-list]');

                if (!modal || !summary || !list) {
                    return;
                }

                const count = Number.parseInt(button.dataset.readCount, 10) || 0;
                let readers = [];

                // Polling stores the reader list in data-read-users for the modal.
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

                summary.textContent = receiptText(count);
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
                            ? `${roleDetail} - ${reader.read_at}`
                            : roleDetail;
                        text.append(name, detail);
                        item.append(avatar, text);
                        list.append(item);
                    });
                }

                messageModals.open(modal, button);
            };

            return {
                createControl,
                openModal,
                updateAll,
            };
        },
    };
})();
