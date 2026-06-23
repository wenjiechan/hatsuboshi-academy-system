(function () {
    const pollUrl = '/gakumas-sms/api/notifications_poll.php';
    const badges = Array.from(document.querySelectorAll('[data-notification-badge]'));
    const toastRegion = document.querySelector('[data-notification-toasts]');
    const shownKey = 'gakumasShownNotifications';
    let shownIds = new Set();

    try {
        shownIds = new Set(JSON.parse(sessionStorage.getItem(shownKey) || '[]').map(String));
    } catch (error) {
        shownIds = new Set();
    }

    function saveShownIds() {
        sessionStorage.setItem(shownKey, JSON.stringify(Array.from(shownIds).slice(-50)));
    }

    function updateBadges(count) {
        const label = count > 99 ? '99+' : String(count);

        badges.forEach((badge) => {
            badge.textContent = label;
            badge.classList.toggle('d-none', count <= 0);
            badge.setAttribute('aria-label', `${count} unread notifications`);
        });
    }

    function showToast(notification) {
        if (!toastRegion || shownIds.has(String(notification.id))) {
            return;
        }

        shownIds.add(String(notification.id));
        saveShownIds();

        const toast = document.createElement('aside');
        toast.className = 'notification-toast';
        toast.setAttribute('role', 'status');

        const icon = document.createElement('span');
        icon.className = 'notification-toast-icon';
        icon.innerHTML = '<i class="bi bi-bell"></i>';

        const content = document.createElement('div');
        content.className = 'notification-toast-content';

        const title = document.createElement('strong');
        title.textContent = notification.title || 'Notification';
        content.appendChild(title);

        if (notification.body) {
            const body = document.createElement('p');
            body.textContent = notification.body;
            content.appendChild(body);
        }

        const closeButton = document.createElement('button');
        closeButton.type = 'button';
        closeButton.className = 'notification-toast-close';
        closeButton.setAttribute('aria-label', 'Dismiss notification');
        closeButton.innerHTML = '<i class="bi bi-x-lg"></i>';
        closeButton.addEventListener('click', () => toast.remove());

        if (notification.action_url) {
            toast.addEventListener('click', (event) => {
                if (event.target.closest('button')) {
                    return;
                }
                window.location.href = notification.action_url;
            });
            toast.classList.add('is-clickable');
        }

        toast.append(icon, content, closeButton);
        toastRegion.prepend(toast);

        window.setTimeout(() => {
            toast.remove();
        }, 15000);
    }

    async function pollNotifications() {
        try {
            const response = await fetch(pollUrl, {
                headers: { Accept: 'application/json' },
                credentials: 'same-origin',
            });

            if (!response.ok) {
                const errorData = await response.json().catch(() => null);

                if (errorData && errorData.redirect_url) {
                    window.location.href = errorData.redirect_url;
                }

                return;
            }

            const data = await response.json();
            const count = Number(data.unread_count || 0);
            updateBadges(count);

            (data.notifications || []).reverse().forEach(showToast);
        } catch (error) {
            // Polling is progressive enhancement; failures should not block the page.
        }
    }

    if (badges.length === 0) {
        return;
    }

    pollNotifications();
    window.setInterval(pollNotifications, 10000);
})();
