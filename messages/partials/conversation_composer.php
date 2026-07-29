<?php
$conversation_composer_context = $conversation_composer_context ?? [];
$composer_conversation_type = (string) ($conversation_composer_context['conversation_type'] ?? '');
$composer_conversation_id = (int) ($conversation_composer_context['conversation_id'] ?? 0);
$composer_csrf_token = (string) ($conversation_composer_context['csrf_token'] ?? '');
$composer_is_group_conversation = !empty($conversation_composer_context['is_group_conversation']);
$composer_group_mention_members = is_array($conversation_composer_context['group_mention_members'] ?? null)
    ? $conversation_composer_context['group_mention_members']
    : [];
?>

<footer class="conversation-input-area">
    <?php if ($composer_conversation_type === 'system'): ?>
        <div class="system-conversation-notice">
            <i class="bi bi-shield-check"></i>
            <span>System messages cannot be replied to.</span>
        </div>
    <?php else: ?>
        <?php if ($composer_is_group_conversation): ?>
            <script type="application/json" data-mention-members>
                <?= json_encode($composer_group_mention_members, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>
            </script>
            <div class="mention-suggestions" data-mention-suggestions hidden></div>
        <?php endif; ?>

        <div class="message-typing-indicator" data-typing-indicator hidden></div>
        <div class="message-send-error" role="alert" data-message-send-error hidden></div>
        <input type="hidden" name="reply_to_message_id" value="" form="messageComposer" data-reply-to-message-id>

        <div class="message-reply-composer" data-reply-composer hidden>
            <i class="bi bi-reply" aria-hidden="true"></i>
            <span>
                <strong data-reply-composer-sender></strong>
                <small data-reply-composer-preview></small>
            </span>
            <button type="button" aria-label="Cancel reply" data-reply-cancel>
                <i class="bi bi-x-lg" aria-hidden="true"></i>
            </button>
        </div>

        <form
            method="post"
            action="/gakumas-sms/messages/send.php"
            enctype="multipart/form-data"
            class="message-composer"
            id="messageComposer"
            data-message-composer
        >
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($composer_csrf_token, ENT_QUOTES, 'UTF-8') ?>">
            <!-- JavaScript fills this only when the user selects a sticker. -->
            <input type="hidden" name="sticker_key" value="" data-message-sticker-key>
            <input type="hidden" name="conversation_id" value="<?= $composer_conversation_id ?>">

            <div class="message-attachment-preview" data-message-attachment-preview hidden></div>

            <button
                type="button"
                class="message-emoji-toggle"
                aria-label="Choose emoji"
                aria-expanded="false"
                data-message-emoji-toggle
            >
                <i class="bi bi-emoji-smile" aria-hidden="true"></i>
            </button>

            <button
                type="button"
                class="message-attachment-toggle"
                aria-label="Attach photos or files"
                title="Attach photos or files"
                data-message-attachment-toggle
            >
                <i class="bi bi-paperclip" aria-hidden="true"></i>
            </button>
            <input
                type="file"
                name="attachments[]"
                accept="image/jpeg,image/png,image/gif,image/webp,.pdf,.txt,.md,.csv,.json,.xml,.rtf,.zip,.7z,.rar,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.odt,.ods,.odp"
                multiple
                data-message-attachment-input
            >

            <label for="messageBody" class="visually-hidden">Message</label>
            <textarea
                id="messageBody"
                name="body"
                rows="1"
                maxlength="5000"
                placeholder="Write a message"
                data-message-input
            ></textarea>

            <span class="message-character-count" data-message-character-count>0 / 5000</span>

            <button type="submit" class="message-send-button" aria-label="Send message" data-message-send-button>
                <i class="bi bi-send-fill"></i>
            </button>
        </form>
        <!-- The picker reads the local pack catalog without another request. -->
        <script type="application/json" data-message-sticker-packs><?= json_encode(message_sticker_packs(), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>
    <?php endif; ?>
</footer>
