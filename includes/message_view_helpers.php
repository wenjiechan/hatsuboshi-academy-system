<?php
// Displays messages time
function format_chat_message_time(string $date): string
{
    $timestamp = strtotime($date);

    return $timestamp ? date('M j, Y \a\t g:i A', $timestamp) : $date;
}

// Convert special messages into labels
function chat_message_type_label(string $type): ?string
{
    return match ($type) {
        MESSAGE_TYPE_BIRTHDAY => 'Birthday message',
        MESSAGE_TYPE_PRODUCER_ADD_REQUEST => 'Producer request',
        MESSAGE_TYPE_PRODUCER_REMOVE_REQUEST => 'Release request',
        MESSAGE_TYPE_SYSTEM => 'System message',
        default => null,
    };
}

function chat_sender_display_name(array $message): string
{
    if (trim((string) ($message['sender_display_name'] ?? '')) !== '') {
        return trim((string) $message['sender_display_name']);
    }

    return trim((string) ($message['sender_student_name'] ?? ''))
        ?: trim((string) ($message['sender_teacher_name'] ?? ''))
        ?: trim((string) ($message['sender_username'] ?? ''))
        ?: 'System';
}

function pinned_message_preview(string $body, int $limit = 120): string
{
    $body = trim(preg_replace('/\s+/', ' ', $body));

    return mb_strlen($body) > $limit
        ? mb_substr($body, 0, $limit - 1) . '...'
        : $body;
}

function chat_message_preview_text(array $message, int $limit = 120): string
{
    if (($message['message_type'] ?? '') === MESSAGE_TYPE_STICKER) {
        // Historic messages keep a readable fallback when a local pack changes later.
        return message_sticker_preview_label((string) ($message['sticker_key'] ?? ''));
    }

    $body = trim((string) ($message['body'] ?? ''));

    if ($body !== '') {
        return pinned_message_preview($body, $limit);
    }

    $attachments = $message['attachments'] ?? [];

    if (count($attachments) > 1) {
        return count($attachments) . ' attachments';
    }

    if (!empty($attachments[0])) {
        $attachment = $attachments[0];
        $prefix = ($attachment['attachment_type'] ?? '') === 'image' ? 'Photo: ' : 'File: ';

        return pinned_message_preview($prefix . (string) ($attachment['original_name'] ?? 'Attachment'), $limit);
    }

    return '[No text]';
}

function chat_message_sticker_html(?string $key): string
{
    // Resolve at render time so the stored message only needs the small sticker key.
    $sticker = message_sticker_public_data($key);

    if (!$sticker) {
        return '<span class="chat-message-sticker-unavailable" data-message-sticker>Sticker unavailable</span>';
    }

    $label = htmlspecialchars($sticker['label'], ENT_QUOTES, 'UTF-8');
    $url = htmlspecialchars($sticker['url'], ENT_QUOTES, 'UTF-8');

    return '<span class="chat-message-sticker" data-message-sticker>'
        . '<img src="' . $url . '" alt="' . $label . '" loading="lazy" decoding="async">'
        . '</span>';
}

function reply_message_preview(array $message): ?array
{
    if (empty($message['reply_to_message_id'])) {
        return null;
    }

    $body = !empty($message['reply_deleted_at'])
        ? 'This message was deleted.'
        : trim((string) ($message['reply_body'] ?? ''));

    if (($message['reply_message_type'] ?? '') === MESSAGE_TYPE_STICKER) {
        $body = message_sticker_preview_label((string) ($message['reply_sticker_key'] ?? ''));
    } elseif ($body === '' && !empty($message['reply_attachment_name'])) {
        $body = 'Attachment: ' . (string) $message['reply_attachment_name'];
    }

    return [
        'message_id' => (int) $message['reply_to_message_id'],
        'sender_display_name' => (string) ($message['reply_sender_display_name'] ?? 'Someone'),
        'body' => pinned_message_preview($body !== '' ? $body : '[No text]', 90),
        'is_deleted' => !empty($message['reply_deleted_at']),
    ];
}

function format_message_attachment_size(int $bytes): string
{
    if ($bytes < 1024) {
        return $bytes . ' B';
    }

    if ($bytes < 1024 * 1024) {
        return number_format($bytes / 1024, 1) . ' KB';
    }

    return number_format($bytes / (1024 * 1024), 1) . ' MB';
}

function chat_message_attachment_names(array $attachments): string
{
    return implode(' ', array_map(
        static fn(array $attachment): string => (string) ($attachment['original_name'] ?? ''),
        $attachments
    ));
}

function chat_message_attachments_html(array $attachments): string
{
    if ($attachments === []) {
        return '';
    }

    $html = '<div class="chat-message-attachments" data-message-attachments>';

    foreach ($attachments as $attachment) {
        $name = (string) ($attachment['original_name'] ?? 'Attachment');
        $url = (string) ($attachment['url'] ?? '');
        $download_url = (string) ($attachment['download_url'] ?? $url);
        $size = format_message_attachment_size((int) ($attachment['file_size'] ?? 0));
        $escaped_name = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        $escaped_url = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
        $escaped_download_url = htmlspecialchars($download_url, ENT_QUOTES, 'UTF-8');
        $escaped_size = htmlspecialchars($size, ENT_QUOTES, 'UTF-8');

        if (($attachment['attachment_type'] ?? '') === 'image') {
            $html .= '<figure class="chat-image-attachment">';
            $html .= '<a href="' . $escaped_url . '" target="_blank" rel="noopener" aria-label="Open ' . $escaped_name . '">';
            $html .= '<img src="' . $escaped_url . '" alt="' . $escaped_name . '" loading="lazy" decoding="async">';
            $html .= '</a>';
            $html .= '<figcaption><span title="' . $escaped_name . '">' . $escaped_name . '</span>';
            $html .= '<a href="' . $escaped_download_url . '" aria-label="Download ' . $escaped_name . '">';
            $html .= '<i class="bi bi-download" aria-hidden="true"></i></a></figcaption>';
            $html .= '</figure>';
            continue;
        }

        $html .= '<a class="chat-file-attachment" href="' . $escaped_download_url . '">';
        $html .= '<i class="bi bi-file-earmark-text" aria-hidden="true"></i>';
        $html .= '<span><strong title="' . $escaped_name . '">' . $escaped_name . '</strong>';
        $html .= '<small>' . $escaped_size . '</small></span>';
        $html .= '<i class="bi bi-download" aria-hidden="true"></i>';
        $html .= '</a>';
    }

    $html .= '</div>';

    return $html;
}

function group_member_role_detail(array $member): string
{
    return ($member['role'] ?? '') === 'teacher' && !empty($member['specialty'])
        ? ucfirst((string) $member['specialty']) . ' teacher'
        : ucfirst((string) ($member['role'] ?? 'member'));
}

function chat_message_body_html(
    string $body,
    array $mention_members,
    string $current_user_display_name,
    bool $is_group_conversation
): string
{
    if (!$is_group_conversation) {
        return nl2br(htmlspecialchars($body, ENT_QUOTES, 'UTF-8'));
    }

    $mention_names = array_values(array_unique(array_filter(array_map(
        static fn(array $member): string => (string) ($member['display_name'] ?? ''),
        $mention_members
    ))));
    $mention_names = array_merge($mention_names, array_values(array_unique(array_filter(array_map(
        static fn(array $member): string => (string) ($member['real_display_name'] ?? ''),
        $mention_members
    )))));
    $mention_names[] = 'everyone';
    $mention_names = array_values(array_unique(array_filter($mention_names)));
    usort($mention_names, static fn(string $left, string $right): int => mb_strlen($right) <=> mb_strlen($left));
    $mention_pattern = implode('|', array_map(static fn(string $name): string => preg_quote($name, '/'), $mention_names));

    if ($mention_pattern === '') {
        return nl2br(htmlspecialchars($body, ENT_QUOTES, 'UTF-8'));
    }

    $pattern = '/(^|[\s(])@(' . $mention_pattern . ')(?=$|[\s.,!?;:)\]])/iu';
    $html = '';
    $last_end = 0;

    if (preg_match_all($pattern, $body, $matches, PREG_OFFSET_CAPTURE) !== false) {
        foreach ($matches[0] as $index => $match) {
            [$full_match, $offset] = $match;
            $prefix = (string) $matches[1][$index][0];
            $name = (string) $matches[2][$index][0];
            $mention_start = $offset + strlen($prefix);

            if ($mention_start > $last_end) {
                $html .= nl2br(htmlspecialchars(substr($body, $last_end, $mention_start - $last_end), ENT_QUOTES, 'UTF-8'));
            }

            $mention_text = '@' . $name;
            // All valid mentions are colored; only mentions for the current viewer get the background.
            $is_targeted = strcasecmp($name, 'everyone') === 0 || strcasecmp($name, $current_user_display_name) === 0;
            $class = 'chat-mention' . ($is_targeted ? ' chat-mention-targeted' : '');
            $html .= '<span class="' . $class . '">' . htmlspecialchars($mention_text, ENT_QUOTES, 'UTF-8') . '</span>';
            $last_end = $offset + strlen($full_match);
        }
    }

    if ($last_end < strlen($body)) {
        $html .= nl2br(htmlspecialchars(substr($body, $last_end), ENT_QUOTES, 'UTF-8'));
    }

    return $html !== '' ? $html : nl2br(htmlspecialchars($body, ENT_QUOTES, 'UTF-8'));
}

function chat_message_reactions_html(array $reactions): string
{
    if (empty($reactions)) {
        return '';
    }

    $html = '<div class="chat-message-reactions" data-message-reactions>';

    foreach ($reactions as $reaction) {
        $emoji = (string) ($reaction['emoji'] ?? '');
        $count = (int) ($reaction['count'] ?? 0);

        if ($emoji === '' || $count <= 0) {
            continue;
        }

        $html .= sprintf(
            '<button type="button" class="chat-message-reaction%s" data-message-reaction="%s" title="%s" aria-label="%s"><span>%s</span><strong>%d</strong></button>',
            !empty($reaction['reacted']) ? ' reacted' : '',
            htmlspecialchars($emoji, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars((string) ($reaction['user_names'] ?? ''), ENT_QUOTES, 'UTF-8'),
            htmlspecialchars('React with ' . $emoji, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($emoji, ENT_QUOTES, 'UTF-8'),
            $count
        );
    }

    $html .= '</div>';

    return $html;
}

