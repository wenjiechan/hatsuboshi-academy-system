const studentRequestForm = document.getElementById('studentRequestForm');
const studentRequestTypeInput = document.getElementById('studentRequestTypeInput');
const songAddModeInput = document.getElementById('songAddModeInput');
const requestTypeButtons = Array.from(document.querySelectorAll('[data-request-type]'));
const requestSections = Array.from(document.querySelectorAll('[data-request-section]'));
const producerRecipientChoice = document.querySelector('[data-recipient-choice="producer"]');
const producerRecipientInput = document.querySelector('[data-recipient-input="producer"]');
const adminRecipientInput = document.querySelector('[data-recipient-input="admin"]');
const songEditRecipientNote = document.getElementById('songEditRecipientNote');
const addModeButtons = Array.from(document.querySelectorAll('[data-song-add-mode]'));
const addModePanels = Array.from(document.querySelectorAll('[data-song-add-panel]'));
const requestBirthday = document.getElementById('requestBirthday');
const requestZodiac = document.getElementById('requestZodiac');
const requestBirthdayPicker = document.querySelector('.request-birthday-picker');
const requestBirthdayButton = document.getElementById('requestBirthdayButton');
const requestBirthdayLabel = document.getElementById('requestBirthdayLabel');
const requestBirthdayPopover = document.getElementById('requestBirthdayPopover');
const requestBirthdayMonthSelect = document.getElementById('requestBirthdayMonthSelect');
const requestBirthdayDayGrid = document.getElementById('requestBirthdayDayGrid');
const requestBirthdayPrevMonth = document.getElementById('requestBirthdayPrevMonth');
const requestBirthdayNextMonth = document.getElementById('requestBirthdayNextMonth');
const librarySongSearch = document.getElementById('librarySongSearch');
const librarySongOptions = Array.from(document.querySelectorAll('[data-song-option]'));
const librarySongEmpty = document.getElementById('librarySongEmpty');
const editSongSelect = document.getElementById('editSongSelect');
const editSongTitle = document.getElementById('editSongTitle');
const editSongTitleJp = document.getElementById('editSongTitleJp');
const editSongArtist = document.getElementById('editSongArtist');
const editSongDuration = document.getElementById('editSongDuration');
const editSongType = document.getElementById('editSongType');
const monthNames = [
    'January', 'February', 'March', 'April', 'May', 'June',
    'July', 'August', 'September', 'October', 'November', 'December'
];

function zodiacFromMonthDay(month, day) {
    if ((month === 1 && day >= 20) || (month === 2 && day <= 18)) return 'Aquarius';
    if ((month === 2 && day >= 19) || (month === 3 && day <= 20)) return 'Pisces';
    if ((month === 3 && day >= 21) || (month === 4 && day <= 19)) return 'Aries';
    if ((month === 4 && day >= 20) || (month === 5 && day <= 20)) return 'Taurus';
    if ((month === 5 && day >= 21) || (month === 6 && day <= 20)) return 'Gemini';
    if ((month === 6 && day >= 21) || (month === 7 && day <= 22)) return 'Cancer';
    if ((month === 7 && day >= 23) || (month === 8 && day <= 22)) return 'Leo';
    if ((month === 8 && day >= 23) || (month === 9 && day <= 22)) return 'Virgo';
    if ((month === 9 && day >= 23) || (month === 10 && day <= 22)) return 'Libra';
    if ((month === 10 && day >= 23) || (month === 11 && day <= 21)) return 'Scorpio';
    if ((month === 11 && day >= 22) || (month === 12 && day <= 21)) return 'Sagittarius';
    return 'Capricorn';
}

function formatMonthDay(month, day) {
    return `${monthNames[month - 1]} ${String(day).padStart(2, '0')}`;
}

function parseMonthDay(value) {
    const trimmed = value.trim();
    const textMatch = trimmed.match(/^([A-Za-z]+)\s+(\d{1,2})$/);

    if (!textMatch) {
        return null;
    }

    const monthIndex = monthNames.map((month) => month.toLowerCase()).indexOf(textMatch[1].toLowerCase());

    if (monthIndex === -1) {
        return null;
    }

    return {
        month: monthIndex + 1,
        day: Number(textMatch[2])
    };
}

function updateRequestZodiac() {
    if (!requestBirthday || !requestZodiac) {
        return;
    }

    if (!requestBirthday.value) {
        requestZodiac.value = '';
        return;
    }

    const birthday = parseMonthDay(requestBirthday.value);

    if (!birthday || birthday.month < 1 || birthday.month > 12 || birthday.day < 1 || birthday.day > 31) {
        requestZodiac.value = '';
        return;
    }

    requestZodiac.value = zodiacFromMonthDay(birthday.month, birthday.day);
}

function setRequestBirthday(month, day) {
    if (!requestBirthday || !requestBirthdayPicker || !requestBirthdayLabel) {
        return;
    }

    const value = formatMonthDay(month, day);
    requestBirthdayPicker.dataset.selectedMonth = String(month);
    requestBirthdayPicker.dataset.selectedDay = String(day);
    requestBirthday.value = value;
    requestBirthdayLabel.textContent = value;
    updateRequestZodiac();
}

function closeRequestBirthdayPicker() {
    if (requestBirthdayPopover) {
        requestBirthdayPopover.classList.add('d-none');
    }
}

function renderRequestBirthdayDays() {
    if (!requestBirthdayMonthSelect || !requestBirthdayDayGrid || !requestBirthdayPicker) {
        return;
    }

    const selectedMonth = Number(requestBirthdayMonthSelect.value);
    const selectedDay = Number(requestBirthdayPicker.dataset.selectedDay || 1);
    const daysInMonth = new Date(new Date().getFullYear(), selectedMonth, 0).getDate();
    const firstWeekday = new Date(new Date().getFullYear(), selectedMonth - 1, 1).getDay();

    requestBirthdayDayGrid.innerHTML = '';

    for (let index = 0; index < firstWeekday; index += 1) {
        const spacer = document.createElement('span');
        spacer.className = 'request-calendar-spacer';
        requestBirthdayDayGrid.appendChild(spacer);
    }

    for (let day = 1; day <= daysInMonth; day += 1) {
        const dayButton = document.createElement('button');
        dayButton.type = 'button';
        dayButton.className = 'request-calendar-day';
        dayButton.textContent = String(day);

        if (day === selectedDay && selectedMonth === Number(requestBirthdayPicker.dataset.selectedMonth || 1)) {
            dayButton.classList.add('is-selected');
        }

        dayButton.addEventListener('click', () => {
            setRequestBirthday(selectedMonth, day);
            renderRequestBirthdayDays();
            closeRequestBirthdayPicker();
        });

        requestBirthdayDayGrid.appendChild(dayButton);
    }
}

function fillSongCorrectionFields() {
    if (!editSongSelect) {
        return;
    }

    const selectedOption = editSongSelect.selectedOptions[0];
    const hasSong = selectedOption && selectedOption.value !== '';

    if (editSongTitle) {
        editSongTitle.value = hasSong ? (selectedOption.dataset.title || '') : '';
    }

    if (editSongTitleJp) {
        editSongTitleJp.value = hasSong ? (selectedOption.dataset.titleJp || '') : '';
    }

    if (editSongArtist) {
        editSongArtist.value = hasSong ? (selectedOption.dataset.artist || '') : '';
    }

    if (editSongDuration) {
        editSongDuration.value = hasSong ? (selectedOption.dataset.duration || '') : '';
    }

    if (editSongType) {
        editSongType.value = hasSong ? (selectedOption.dataset.songType || '') : '';
    }
}

function updateRecipientAvailability(selectedType) {
    const isSongEdit = selectedType === 'song_edit';

    if (producerRecipientChoice) {
        producerRecipientChoice.classList.toggle('is-disabled', isSongEdit);
    }

    if (producerRecipientInput) {
        producerRecipientInput.disabled = isSongEdit;
    }

    if (adminRecipientInput && isSongEdit) {
        adminRecipientInput.checked = true;
    }

    if (songEditRecipientNote) {
        songEditRecipientNote.classList.toggle('d-none', !isSongEdit);
    }
}

function setRequestType(selectedType) {
    if (studentRequestTypeInput) {
        studentRequestTypeInput.value = selectedType;
    }

    updateRecipientAvailability(selectedType);
}

if (studentRequestForm) {
    studentRequestForm.addEventListener('reset', () => {
        window.setTimeout(() => {
            const birthday = parseMonthDay(requestBirthday?.value || '');

            if (birthday && requestBirthdayPicker && requestBirthdayMonthSelect && requestBirthdayLabel) {
                requestBirthdayPicker.dataset.selectedMonth = String(birthday.month);
                requestBirthdayPicker.dataset.selectedDay = String(birthday.day);
                requestBirthdayMonthSelect.value = String(birthday.month);
                requestBirthdayLabel.textContent = requestBirthday.value;
                renderRequestBirthdayDays();
            }

            updateRequestZodiac();
            fillSongCorrectionFields();
            const activeType = document.querySelector('[data-request-type].is-active')?.dataset.requestType || 'profile_update';
            setRequestType(activeType);
        }, 0);
    });
}

if (requestBirthday) {
    updateRequestZodiac();
}

if (requestBirthdayButton && requestBirthdayPopover) {
    requestBirthdayButton.addEventListener('click', () => {
        const isOpening = requestBirthdayPopover.classList.contains('d-none');
        requestBirthdayPopover.classList.toggle('d-none', !isOpening);

        if (isOpening) {
            renderRequestBirthdayDays();
        }
    });
}

if (requestBirthdayMonthSelect) {
    requestBirthdayMonthSelect.addEventListener('change', () => {
        const month = Number(requestBirthdayMonthSelect.value);
        const currentDay = Math.min(
            Number(requestBirthdayPicker?.dataset.selectedDay || 1),
            new Date(new Date().getFullYear(), month, 0).getDate()
        );

        if (requestBirthdayPicker) {
            requestBirthdayPicker.dataset.selectedDay = String(currentDay);
        }

        renderRequestBirthdayDays();
    });
}

if (requestBirthdayPrevMonth && requestBirthdayMonthSelect) {
    requestBirthdayPrevMonth.addEventListener('click', () => {
        const currentMonth = Number(requestBirthdayMonthSelect.value);
        requestBirthdayMonthSelect.value = String(currentMonth === 1 ? 12 : currentMonth - 1);
        requestBirthdayMonthSelect.dispatchEvent(new Event('change'));
    });
}

if (requestBirthdayNextMonth && requestBirthdayMonthSelect) {
    requestBirthdayNextMonth.addEventListener('click', () => {
        const currentMonth = Number(requestBirthdayMonthSelect.value);
        requestBirthdayMonthSelect.value = String(currentMonth === 12 ? 1 : currentMonth + 1);
        requestBirthdayMonthSelect.dispatchEvent(new Event('change'));
    });
}

document.addEventListener('click', (event) => {
    if (
        requestBirthdayPicker
        && requestBirthdayPopover
        && !requestBirthdayPopover.classList.contains('d-none')
        && !requestBirthdayPicker.contains(event.target)
    ) {
        closeRequestBirthdayPicker();
    }
});

renderRequestBirthdayDays();

requestTypeButtons.forEach((button) => {
    button.addEventListener('click', () => {
        const selectedType = button.dataset.requestType;

        requestTypeButtons.forEach((item) => {
            item.classList.toggle('is-active', item === button);
        });

        requestSections.forEach((section) => {
            section.classList.toggle('is-active', section.dataset.requestSection === selectedType);
        });

        setRequestType(selectedType);
    });
});

setRequestType(document.querySelector('[data-request-type].is-active')?.dataset.requestType || 'profile_update');

addModeButtons.forEach((button) => {
    button.addEventListener('click', () => {
        const selectedMode = button.dataset.songAddMode;

        addModeButtons.forEach((item) => {
            item.classList.toggle('is-active', item === button);
        });

        addModePanels.forEach((panel) => {
            panel.classList.toggle('is-active', panel.dataset.songAddPanel === selectedMode);
        });

        if (songAddModeInput) {
            songAddModeInput.value = selectedMode;
        }
    });
});

if (librarySongSearch) {
    librarySongSearch.addEventListener('input', () => {
        const query = librarySongSearch.value.trim().toLowerCase();
        let visibleCount = 0;

        librarySongOptions.forEach((option) => {
            const isVisible = !query || option.dataset.songSearch.includes(query);
            option.classList.toggle('d-none', !isVisible);

            if (isVisible) {
                visibleCount += 1;
            }
        });

        if (librarySongEmpty) {
            librarySongEmpty.classList.toggle('d-none', visibleCount > 0);
        }
    });
}

if (editSongSelect) {
    editSongSelect.addEventListener('change', fillSongCorrectionFields);
    fillSongCorrectionFields();
}
