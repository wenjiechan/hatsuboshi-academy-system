const intervals = [];
const listeners = {};

class FakeElement {
    constructor(tagName = 'div') {
        this.tagName = tagName;
        this.dataset = {};
        this.children = [];
        this.className = '';
        this.hidden = false;
        this.style = {};
        this.scrollTop = 0;
        this.scrollHeight = 1000;
        this.clientHeight = 400;
        this.textContent = '';
        this.classList = {
            add: (...names) => {
                const classes = new Set(this.className.split(/\s+/).filter(Boolean));
                names.forEach((name) => classes.add(name));
                this.className = Array.from(classes).join(' ');
            },
        };
    }

    append(...nodes) {
        this.children.push(...nodes);
        this.scrollHeight += nodes.length * 50;
    }

    prepend(...nodes) {
        this.children.unshift(...nodes);
    }

    replaceChildren(...nodes) {
        this.children = [...nodes];
    }

    querySelector(selector) {
        if (selector === '.conversation-start-state') {
            return null;
        }

        const matches = (element) => {
            if (!(element instanceof FakeElement)) {
                return false;
            }

            if (selector === '[data-message-body]') {
                return Object.hasOwn(element.dataset, 'messageBody');
            }

            if (selector === '[data-message-edited]') {
                return Object.hasOwn(element.dataset, 'messageEdited');
            }

            if (selector === '[data-message-edit-form]') {
                return Object.hasOwn(element.dataset, 'messageEditForm');
            }

            if (selector === '.chat-message-meta') {
                return element.className === 'chat-message-meta';
            }

            if (selector === '.chat-message-delete-form') {
                return element.className === 'chat-message-delete-form';
            }

            const messageIdMatch = selector.match(/^\[data-message-id="(\d+)"\]$/);
            return messageIdMatch
                ? element.dataset.messageId === messageIdMatch[1]
                : false;
        };

        const find = (elements) => {
            for (const element of elements) {
                if (matches(element)) {
                    return element;
                }

                if (element instanceof FakeElement) {
                    const nested = find(element.children);

                    if (nested) {
                        return nested;
                    }
                }
            }

            return null;
        };

        return find(this.children);
    }

    get renderedText() {
        if (this.children.length === 0) {
            return this.textContent;
        }

        return this.children.map((child) => {
            if (child instanceof FakeElement) {
                return child.renderedText;
            }

            return child.textContent || '';
        }).join('');
    }

    get firstElementChild() {
        return this.children.find((child) => child instanceof FakeElement) || null;
    }

    get lastElementChild() {
        for (let index = this.children.length - 1; index >= 0; index -= 1) {
            if (this.children[index] instanceof FakeElement) {
                return this.children[index];
            }
        }

        return null;
    }

    setAttribute() {}
    addEventListener() {}
    focus() {}
    setSelectionRange() {}
    remove() {}
}

const conversationThread = new FakeElement();
conversationThread.dataset = {
    conversationId: '7',
    lastMessageId: '10',
    csrfToken: 'test-token',
};

globalThis.document = {
    hidden: false,
    addEventListener(type, callback) {
        listeners[type] = callback;
    },
    querySelector(selector) {
        return selector === '[data-conversation-thread]' ? conversationThread : null;
    },
    querySelectorAll() {
        return [];
    },
    createElement(tagName) {
        return new FakeElement(tagName);
    },
    createTextNode(text) {
        return { textContent: text };
    },
};

globalThis.window = {
    setInterval(callback) {
        intervals.push(callback);
        return 1;
    },
    location: {
        assign() {},
    },
};

let responseMessageId = 11;
globalThis.fetch = async () => ({
    status: 200,
    ok: true,
    async json() {
        return {
            messages: [{
                id: responseMessageId,
                body: 'Polling test',
                message_type: 'text',
                created_at: '2026-06-25 11:30:00',
                edited_at: null,
                deleted_at: null,
                is_own: false,
                can_edit: false,
                can_delete: false,
            }],
            edited_messages: [],
            deleted_messages: [],
            next_after_id: responseMessageId,
            edited_cursor: '2026-06-25 11:30:10',
            deleted_cursor: '2026-06-25 11:30:10',
        };
    },
});

await import('../assets/js/messages.js');
listeners.DOMContentLoaded();

conversationThread.scrollHeight = 1000;
conversationThread.clientHeight = 400;
conversationThread.scrollTop = 550;
await intervals[0]();
const nearBottomAutoScrolled = conversationThread.scrollTop === conversationThread.scrollHeight;

responseMessageId = 12;
conversationThread.scrollHeight = 1200;
conversationThread.clientHeight = 400;
conversationThread.scrollTop = 100;
await intervals[0]();
const readingOlderMessagesPositionPreserved = conversationThread.scrollTop === 100;

globalThis.fetch = async () => ({
    status: 200,
    ok: true,
    async json() {
        return {
            messages: [],
            edited_messages: [{
                id: 11,
                body: 'Edited in real time',
                edited_at: '2026-06-25 11:30:15',
            }],
            deleted_messages: [],
            next_after_id: 12,
            edited_cursor: '2026-06-25 11:30:20',
            deleted_cursor: '2026-06-25 11:30:20',
        };
    },
});
await intervals[0]();
const editedArticle = conversationThread.querySelector('[data-message-id="11"]');
const editedBody = editedArticle?.querySelector('[data-message-body]');
const editedLabel = editedArticle?.querySelector('[data-message-edited]');
const editedMessageUpdatedInPlace = editedBody?.renderedText === 'Edited in real time'
    && editedLabel?.textContent === 'Edited';

globalThis.fetch = async () => ({
    status: 200,
    ok: true,
    async json() {
        return {
            messages: [],
            edited_messages: [],
            deleted_messages: [{
                id: 11,
                message_type: 'text',
                created_at: '2026-06-25 11:30:00',
                deleted_at: '2026-06-25 11:30:25',
                is_own: false,
            }],
            next_after_id: 12,
            edited_cursor: '2026-06-25 11:30:30',
            deleted_cursor: '2026-06-25 11:30:30',
        };
    },
});
await intervals[0]();
const deletedArticle = conversationThread.querySelector('[data-message-id="11"]');
const deletedBody = deletedArticle?.querySelector('[data-message-body]');
const deletedMessageUpdatedInPlace = deletedArticle?.className.includes('deleted')
    && deletedBody?.renderedText === 'This message was deleted.';

const results = {
    nearBottomAutoScrolled,
    readingOlderMessagesPositionPreserved,
    editedMessageUpdatedInPlace,
    deletedMessageUpdatedInPlace,
    appendedMessages: conversationThread.children.length,
};

console.log(JSON.stringify(results, null, 2));

if (
    !nearBottomAutoScrolled ||
    !readingOlderMessagesPositionPreserved ||
    !editedMessageUpdatedInPlace ||
    !deletedMessageUpdatedInPlace
) {
    throw new Error('Live message behavior did not match expectations.');
}
