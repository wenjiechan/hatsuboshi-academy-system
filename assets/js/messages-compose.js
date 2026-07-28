// Handles the recipient search on the compose page only.
document.addEventListener('DOMContentLoaded', () => {
    const recipientSearch = document.querySelector('[data-recipient-search]');
    const recipientRows = Array.from(document.querySelectorAll('[data-recipient-row]'));
    const recipientNoResults = document.querySelector('[data-recipient-no-results]');

    if (!recipientSearch || recipientRows.length === 0 || !recipientNoResults) {
        return;
    }

    recipientSearch.addEventListener('input', () => {
        const query = recipientSearch.value.trim().toLocaleLowerCase();
        let visibleCount = 0;

        // Rows keep their searchable text in data-recipient-search from PHP.
        recipientRows.forEach((row) => {
            const searchText = (row.dataset.recipientSearch || '').toLocaleLowerCase();
            const isVisible = query === '' || searchText.includes(query);

            row.hidden = !isVisible;
            visibleCount += isVisible ? 1 : 0;
        });

        recipientNoResults.hidden = visibleCount !== 0;
    });
});
