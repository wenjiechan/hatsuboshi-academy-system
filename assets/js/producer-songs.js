const songSearch = document.getElementById('songSearch');
const songTracks = Array.from(document.querySelectorAll('.song-track'));
const songSections = Array.from(document.querySelectorAll('.song-category-section'));
const songSearchEmpty = document.getElementById('songSearchEmpty');
const songSortButtons = Array.from(document.querySelectorAll('.song-sort-button'));
const removeSongButtons = Array.from(document.querySelectorAll('[data-confirm-remove]'));
const songAddSearch = document.getElementById('songAddSearch');
const songAddOptions = Array.from(document.querySelectorAll('[data-song-option]'));
const songAddSelectedId = document.getElementById('songAddSelectedId');
const songAddSubmit = document.getElementById('songAddSubmit');
const songAddEmpty = document.getElementById('songAddEmpty');
const songAddResults = document.getElementById('songAddResults');

if (songSearch) {
    songSearch.addEventListener('input', () => {
        const query = songSearch.value.trim().toLowerCase();
        let visibleTrackCount = 0;

        songTracks.forEach((track) => {
            const isVisible = !query || track.dataset.songSearch.includes(query);
            track.classList.toggle('d-none', !isVisible);

            if (isVisible) {
                visibleTrackCount += 1;
            }
        });

        songSections.forEach((section) => {
            const hasVisibleTrack = section.querySelector('.song-track:not(.d-none)');
            section.classList.toggle('d-none', !hasVisibleTrack);
        });

        if (songSearchEmpty) {
            songSearchEmpty.classList.toggle('d-none', visibleTrackCount > 0);
        }
    });
}

function songDurationToSeconds(duration) {
    const parts = duration.split(':').map(Number);

    if (parts.length !== 3 || parts.some(Number.isNaN)) {
        return 0;
    }

    return (parts[0] * 3600) + (parts[1] * 60) + parts[2];
}

songSortButtons.forEach((button) => {
    button.addEventListener('click', () => {
        const section = button.closest('.song-category-section');
        const list = section?.querySelector('.song-list');
        const currentDirection = button.dataset.sortDirection || 'asc';
        const directionMultiplier = currentDirection === 'asc' ? 1 : -1;
        const sortColumn = button.dataset.sortColumn;

        if (!list || !sortColumn) {
            return;
        }

        const tracks = Array.from(list.querySelectorAll('.song-track'));

        tracks.sort((firstTrack, secondTrack) => {
            const sortKey = `sort${sortColumn.charAt(0).toUpperCase() + sortColumn.slice(1)}`;
            const firstValue = firstTrack.dataset[sortKey] || '';
            const secondValue = secondTrack.dataset[sortKey] || '';

            if (sortColumn === 'number') {
                return (Number(firstValue) - Number(secondValue)) * directionMultiplier;
            }

            if (sortColumn === 'duration') {
                return (songDurationToSeconds(firstValue) - songDurationToSeconds(secondValue)) * directionMultiplier;
            }

            if (sortColumn === 'release') {
                return (new Date(firstValue || 0) - new Date(secondValue || 0)) * directionMultiplier;
            }

            return firstValue.trim().localeCompare(secondValue.trim(), undefined, {
                numeric: true,
                sensitivity: 'base'
            }) * directionMultiplier;
        });

        tracks.forEach((track) => list.appendChild(track));

        const nextDirection = currentDirection === 'asc' ? 'desc' : 'asc';
        const icon = button.querySelector('i');

        button.dataset.sortDirection = nextDirection;
        button.setAttribute('aria-label', `Sort ${sortColumn} ${nextDirection === 'asc' ? 'ascending' : 'descending'}`);

        if (icon) {
            const isNumeric = ['number', 'release', 'duration'].includes(sortColumn);
            icon.className = `bi ${isNumeric
                ? (nextDirection === 'asc' ? 'bi-sort-numeric-down' : 'bi-sort-numeric-up')
                : (nextDirection === 'asc' ? 'bi-sort-alpha-down' : 'bi-sort-alpha-up')}`;
        }
    });
});

removeSongButtons.forEach((button) => {
    button.addEventListener('click', (event) => {
        const songTitle = button.dataset.confirmRemove || 'this song';
        const confirmed = window.confirm(`Remove "${songTitle}" from this student? The global song will not be deleted.`);

        if (!confirmed) {
            event.preventDefault();
        }
    });
});

if (songAddSearch) {
    songAddSearch.addEventListener('input', () => {
        const query = songAddSearch.value.trim().toLowerCase();
        let visibleOptionCount = 0;

        if (songAddSelectedId) {
            songAddSelectedId.value = '';
        }

        if (songAddSubmit) {
            songAddSubmit.disabled = true;
        }

        songAddOptions.forEach((option) => {
            option.classList.remove('is-selected');
            option.setAttribute('aria-selected', 'false');

            const isVisible = query !== '' && option.dataset.songSearch.includes(query);
            option.classList.toggle('d-none', !isVisible);

            if (isVisible) {
                visibleOptionCount += 1;
            }
        });

        if (songAddResults) {
            songAddResults.classList.toggle('d-none', query === '');
        }

        if (songAddEmpty) {
            songAddEmpty.classList.toggle('d-none', query === '' || visibleOptionCount > 0);
        }
    });
}

songAddOptions.forEach((option) => {
    option.addEventListener('click', () => {
        songAddOptions.forEach((item) => {
            item.classList.remove('is-selected');
            item.setAttribute('aria-selected', 'false');
        });

        option.classList.add('is-selected');
        option.setAttribute('aria-selected', 'true');

        if (songAddSelectedId) {
            songAddSelectedId.value = option.dataset.songId || '';
        }

        if (songAddSubmit) {
            songAddSubmit.disabled = !option.dataset.songId;
        }
    });
});
