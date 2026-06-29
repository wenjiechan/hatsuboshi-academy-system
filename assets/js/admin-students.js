document.querySelectorAll('[data-producer-toggle]').forEach((toggle) => {
    const form = toggle.closest('form');
    const wrap = form ? form.querySelector('[data-producer-select-wrap]') : null;
    const select = form ? form.querySelector('[data-producer-select]') : null;

    // When the admin choose Assigned, it shows the producer selsct box
    const updateProducerPicker = () => {
        const shouldShow = toggle.value === 'producer';

        if (wrap) {
            wrap.classList.toggle('d-none', !shouldShow);
        }

        if (select) {
            select.required = shouldShow;

            if (!shouldShow) {
                select.value = '';
            }
        }
    };

    toggle.addEventListener('change', updateProducerPicker);
    updateProducerPicker();
});

// Update the rank preview automatically when Vocal, Dance, or Visual changes.
document.querySelectorAll('[data-student-rank]').forEach((rankField) => {
    const form = rankField.closest('form');
    const statFields = ['vocal', 'dance', 'visual'].map((name) => form?.querySelector(`[name="${name}"]`));

    if (!form || statFields.some((field) => !field) || typeof window.calculateStudentRank !== 'function') {
        return;
    }

    const updateRankPreview = () => {
        rankField.value = window.calculateStudentRank(...statFields.map((field) => field.value));
    };

    statFields.forEach((field) => field.addEventListener('input', updateRankPreview));
    updateRankPreview();
});

// Gets the search input and all student rows
const adminStudentSearch = document.getElementById('adminStudentSearch');
const adminStudentRows = Array.from(document.querySelectorAll('.student-row'));
const adminStudentClassSections = Array.from(document.querySelectorAll('[data-student-class]'));
const adminStudentSearchEmpty = document.getElementById('adminStudentSearchEmpty');
const adminClassSortButton = document.getElementById('adminClassSortButton');

// Check every row
function updateAdminStudentVisibility() {
    const query = adminStudentSearch ? adminStudentSearch.value.trim().toLowerCase() : '';
    let visibleStudentCount = 0;

    adminStudentRows.forEach((row) => {
        const matchesSearch = !query || (row.dataset.studentSearch || '').includes(query);

        row.classList.toggle('d-none', !matchesSearch);

        if (matchesSearch) {
            visibleStudentCount += 1;
        }
    });

    adminStudentClassSections.forEach((section) => {
        // Hide the section if no students inside it
        const hasVisibleStudent = section.querySelector('.student-row:not(.d-none)');
        section.classList.toggle('d-none', !hasVisibleStudent);
    });

    if (adminStudentSearchEmpty) {
        adminStudentSearchEmpty.classList.toggle('d-none', visibleStudentCount > 0);
    }
}

if (adminStudentSearch) {
    adminStudentSearch.addEventListener('input', updateAdminStudentVisibility);
}

// Sort students inside each class
document.querySelectorAll('[data-admin-students-table]').forEach((table) => {
    const tableBody = table.querySelector('tbody');
    const sortButtons = table.querySelectorAll('[data-sort-column]');

    sortButtons.forEach((button) => {
        button.addEventListener('click', () => {
            const column = button.dataset.sortColumn;
            const currentDirection = button.dataset.sortDirection === 'desc' ? 'desc' : 'asc';
            const nextDirection = currentDirection === 'asc' ? 'desc' : 'asc';
            const rows = Array.from(tableBody.querySelectorAll('tr'));

            rows.sort((firstRow, secondRow) => {
                // Sort rows
                const firstValue = firstRow.dataset[`${column}Sort`] || '';
                const secondValue = secondRow.dataset[`${column}Sort`] || '';
                const result = firstValue.localeCompare(secondValue, undefined, { numeric: true, sensitivity: 'base' });

                return currentDirection === 'asc' ? result : -result;
            });

            // Re-adds the rows into the table body
            rows.forEach((row) => tableBody.appendChild(row));
            button.dataset.sortDirection = nextDirection;
            button.setAttribute('aria-label', `Sort students ${nextDirection === 'asc' ? 'ascending' : 'descending'}`);
            button.innerHTML = nextDirection === 'asc'
                ? '<i class="bi bi-sort-alpha-down" aria-hidden="true"></i>'
                : '<i class="bi bi-sort-alpha-up" aria-hidden="true"></i>';
            updateAdminStudentVisibility();
        });
    });
});

// Class sort button
if (adminClassSortButton) {
    adminClassSortButton.addEventListener('click', () => {
        const list = document.querySelector('.admin-student-list');
        const currentDirection = adminClassSortButton.dataset.sortDirection === 'desc' ? 'desc' : 'asc';
        const newDirection = currentDirection === 'asc' ? 'desc' : 'asc';
        const sections = Array.from(document.querySelectorAll('[data-student-class]'));

        sections.sort((firstSection, secondSection) => {
            const firstValue = firstSection.dataset.classSort || '';
            const secondValue = secondSection.dataset.classSort || '';
            const result = firstValue.localeCompare(secondValue, undefined, { numeric: true, sensitivity: 'base' });

            return newDirection === 'asc' ? result : -result;
        });

        sections.forEach((section) => {
            if (adminStudentSearchEmpty) {
                list.insertBefore(section, adminStudentSearchEmpty);
            } else {
                list.appendChild(section);
            }
        });

        // Change the button text
        adminClassSortButton.dataset.sortDirection = newDirection;
        adminClassSortButton.innerHTML = newDirection === 'asc'
            ? '<i class="bi bi-sort-alpha-down" aria-hidden="true"></i> Class: ASC'
            : '<i class="bi bi-sort-alpha-up" aria-hidden="true"></i> Class: DESC';
    });
}

updateAdminStudentVisibility();


(() => {
    const monthNames = [
        'January', 'February', 'March', 'April', 'May', 'June',
        'July', 'August', 'September', 'October', 'November', 'December',
    ];

    const currentYear = 2000;

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

    function closeBirthdayPicker(picker) {
        const popover = picker.querySelector('[data-admin-birthday-popover]');
        const card = picker.closest('.profile-card');

        if (popover) {
            popover.classList.add('d-none');
        }

        if (card) {
            card.classList.remove('is-calendar-open');
        }
    }

    // Update the birthday and zodiac
    function setBirthdayValue(picker, month, day) {
        const birthdayField = picker.querySelector('[data-admin-birthday-value]');
        const birthdayLabel = picker.querySelector('[data-admin-birthday-label]');
        const zodiacField = picker.closest('form')?.querySelector('[data-admin-zodiac]');
        const value = formatMonthDay(month, day);

        picker.dataset.selectedMonth = String(month);
        picker.dataset.selectedDay = String(day);

        if (birthdayField) {
            birthdayField.value = value;
        }

        if (birthdayLabel) {
            birthdayLabel.textContent = value;
        }

        if (zodiacField) {
            zodiacField.value = zodiacFromMonthDay(month, day);
        }
    }

function renderBirthdayDays(picker) {
    const monthSelect = picker.querySelector('[data-admin-birthday-month]');
    const dayGrid = picker.querySelector('[data-admin-birthday-days]');

    if (!monthSelect || !dayGrid) {
        return;
    }

    const selectedMonth = Number(monthSelect.value);
    const selectedDay = Number(picker.dataset.selectedDay || 1);
    const selectedStoredMonth = Number(picker.dataset.selectedMonth || selectedMonth);
    const daysInMonth = new Date(currentYear, selectedMonth, 0).getDate();
    const firstWeekday = new Date(currentYear, selectedMonth - 1, 1).getDay();

    dayGrid.innerHTML = '';

    for (let index = 0; index < firstWeekday; index += 1) {
        const spacer = document.createElement('span');
        spacer.className = 'profile-calendar-spacer';
        dayGrid.appendChild(spacer);
    }

    for (let day = 1; day <= daysInMonth; day += 1) {
        const dayButton = document.createElement('button');

        dayButton.type = 'button';
        dayButton.className = 'profile-calendar-day';
        dayButton.textContent = String(day);
        dayButton.setAttribute('aria-label', formatMonthDay(selectedMonth, day));

        if (day === selectedDay && selectedMonth === selectedStoredMonth) {
            dayButton.classList.add('is-selected');
        }

        dayButton.addEventListener('click', () => {
            setBirthdayValue(picker, selectedMonth, day);
            renderBirthdayDays(picker);
            closeBirthdayPicker(picker);
        });

        dayGrid.appendChild(dayButton);
    }
}

    document.querySelectorAll('[data-admin-birthday-picker]').forEach((picker) => {
        const button = picker.querySelector('[data-admin-birthday-button]');
        const popover = picker.querySelector('[data-admin-birthday-popover]');
        const monthSelect = picker.querySelector('[data-admin-birthday-month]');
        const prevButton = picker.querySelector('[data-admin-birthday-prev]');
        const nextButton = picker.querySelector('[data-admin-birthday-next]');
        const card = picker.closest('.profile-card');

        if (button && popover) {
            button.addEventListener('click', () => {
                const isOpening = popover.classList.contains('d-none');

                popover.classList.toggle('d-none', !isOpening);

                if (card) {
                    card.classList.toggle('is-calendar-open', isOpening);
                }

                renderBirthdayDays(picker);
            });
        }

        if (monthSelect) {
            monthSelect.addEventListener('change', () => {
                const month = Number(monthSelect.value);
                const maxDay = new Date(currentYear, month, 0).getDate();
                const day = Math.min(Number(picker.dataset.selectedDay || 1), maxDay);

                setBirthdayValue(picker, month, day);
                renderBirthdayDays(picker);
            });
        }

        if (prevButton && monthSelect) {
            prevButton.addEventListener('click', () => {
                const currentMonth = Number(monthSelect.value);
                monthSelect.value = String(currentMonth === 1 ? 12 : currentMonth - 1);
                monthSelect.dispatchEvent(new Event('change'));
            });
        }

        if (nextButton && monthSelect) {
            nextButton.addEventListener('click', () => {
                const currentMonth = Number(monthSelect.value);
                monthSelect.value = String(currentMonth === 12 ? 1 : currentMonth + 1);
                monthSelect.dispatchEvent(new Event('change'));
            });
        }

        renderBirthdayDays(picker);
    });

    document.addEventListener('click', (event) => {
        document.querySelectorAll('[data-admin-birthday-picker]').forEach((picker) => {
            const popover = picker.querySelector('[data-admin-birthday-popover]');

            if (
                popover &&
                !popover.classList.contains('d-none') &&
                !picker.contains(event.target)
            ) {
                closeBirthdayPicker(picker);
            }
        });
    });
})();
