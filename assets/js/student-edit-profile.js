// Js runs immediately, but its variables stay private
(() => {
const profileEditButton = document.getElementById('profileEditButton');
const profileCancelButton = document.getElementById('profileCancelButton');
const profileFormActions = document.getElementById('profileFormActions');
const profileEditableFields = Array.from(document.querySelectorAll('.profile-editable'));
const birthdayField = document.getElementById('birthday');
const zodiacField = document.getElementById('zodiac');
const birthdayPicker = document.querySelector('.profile-birthday-picker');
const birthdayPickerButton = document.getElementById('birthdayPickerButton');
const birthdayPickerLabel = document.getElementById('birthdayPickerLabel');
const birthdayPickerPopover = document.getElementById('birthdayPickerPopover');
const birthdayCard = birthdayPicker ? birthdayPicker.closest('.profile-card') : null;
const birthdayMonthSelect = document.getElementById('birthdayMonthSelect');
const birthdayDayGrid = document.getElementById('birthdayDayGrid');
const birthdayPrevMonth = document.getElementById('birthdayPrevMonth');
const birthdayNextMonth = document.getElementById('birthdayNextMonth');
const threeSizeField = document.getElementById('three_size');
const threeSizeInputs = Array.from(document.querySelectorAll('.profile-three-size-input'));
// Save original values when cancel
const originalProfileValues = new Map(profileEditableFields.map((field) => [field, field.value]));
const originalProfileTypes = new Map(profileEditableFields.map((field) => [field, field.type]));
const originalZodiacValue = zodiacField ? zodiacField.value : '';
const originalBirthdayValue = birthdayField ? birthdayField.value : '';
const originalThreeSizeValue = threeSizeField ? threeSizeField.value : '';
const monthNames = [
    'January', 'February', 'March', 'April', 'May', 'June',
    'July', 'August', 'September', 'October', 'November', 'December'
];
const currentYear = new Date().getFullYear();

// Birthday helpers
function parseMonthDay(value) {
    const trimmed = value.trim();
    const textMatch = trimmed.match(/^([A-Za-z]+)\s+(\d{1,2})$/);

    if (!textMatch) {
        return null;
    }

    const lowerMonthNames = monthNames.map((month) => month.toLowerCase());
    const monthText = textMatch[1].toLowerCase();
    const monthIndex = lowerMonthNames.indexOf(monthText);

    if (monthIndex === -1) {
        return null;
    }

    return {
        month: monthIndex + 1,
        day: Number(textMatch[2])
    };
}

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

// Update the zodiac after chossing birthday
function updateZodiacPreview() {
    if (!birthdayField || !zodiacField) {
        return;
    }

    const birthday = parseMonthDay(birthdayField.value);

    if (!birthday || birthday.month < 1 || birthday.month > 12 || birthday.day < 1 || birthday.day > 31) {
        zodiacField.value = originalZodiacValue;
        return;
    }

    zodiacField.value = zodiacFromMonthDay(birthday.month, birthday.day);
}

// Birthday picker
function formatMonthDay(month, day) {
    return `${monthNames[month - 1]} ${String(day).padStart(2, '0')}`;
}

function closeBirthdayPicker() {
    if (birthdayPickerPopover) {
        birthdayPickerPopover.classList.add('d-none');
    }

    if (birthdayCard) {
        birthdayCard.classList.remove('is-calendar-open');
    }
}

function setBirthdayValue(month, day) {
    if (!birthdayField || !birthdayPicker || !birthdayPickerLabel) {
        return;
    }

    const value = formatMonthDay(month, day);
    birthdayPicker.dataset.selectedMonth = String(month);
    birthdayPicker.dataset.selectedDay = String(day);
    birthdayField.value = value;
    birthdayPickerLabel.textContent = value;
    updateZodiacPreview();
}

function renderBirthdayDays() {
    if (!birthdayMonthSelect || !birthdayDayGrid || !birthdayPicker) {
        return;
    }

    const selectedMonth = Number(birthdayMonthSelect.value);
    const selectedDay = Number(birthdayPicker.dataset.selectedDay || 1);
    const daysInMonth = new Date(currentYear, selectedMonth, 0).getDate();
    const firstWeekday = new Date(currentYear, selectedMonth - 1, 1).getDay();

    birthdayDayGrid.innerHTML = '';

    for (let index = 0; index < firstWeekday; index += 1) {
        const spacer = document.createElement('span');
        spacer.className = 'profile-calendar-spacer';
        birthdayDayGrid.appendChild(spacer);
    }

    for (let day = 1; day <= daysInMonth; day += 1) {
        const dayButton = document.createElement('button');
        dayButton.type = 'button';
        dayButton.className = 'profile-calendar-day';
        dayButton.textContent = String(day);
        dayButton.setAttribute('aria-label', formatMonthDay(selectedMonth, day));

        if (day === selectedDay && selectedMonth === Number(birthdayPicker.dataset.selectedMonth || 1)) {
            dayButton.classList.add('is-selected');
        }

        dayButton.addEventListener('click', () => {
            setBirthdayValue(selectedMonth, day);
            renderBirthdayDays();
            closeBirthdayPicker();
        });

        birthdayDayGrid.appendChild(dayButton);
    }
}

// Edit mode
function setProfileEditMode(isEditing) {
    profileEditableFields.forEach((field) => {
        if (isEditing && field.dataset.editType) {
            field.type = field.dataset.editType;
            field.value = field.dataset.editValue ?? '';
        } else if (!isEditing && field.dataset.displayValue !== undefined) {
            field.type = originalProfileTypes.get(field) ?? 'text';
            field.value = field.dataset.displayValue;
        }

        if (field.tagName === 'SELECT') {
            field.disabled = !isEditing;
        } else {
            field.readOnly = !isEditing;
        }
    });

    if (birthdayPickerButton) {
        birthdayPickerButton.disabled = !isEditing;
    }

    if (!isEditing) {
        closeBirthdayPicker();
    }

    if (profileFormActions) {
        profileFormActions.classList.toggle('d-none', !isEditing);
    }

    if (profileEditButton) {
        profileEditButton.setAttribute('aria-pressed', isEditing ? 'true' : 'false');
        profileEditButton.innerHTML = isEditing ?
            '<i class="bi bi-eye" aria-hidden="true"></i> View Profile' :
            '<i class="bi bi-pen" aria-hidden="true"></i> Edit Profile';
    }
}

function syncThreeSizeValue() {
    if (!threeSizeField || threeSizeInputs.length !== 3) {
        return;
    }

    const values = threeSizeInputs.map((field) => field.value.trim());
    threeSizeField.value = values.every((value) => value !== '') ? values.join('/') : '';
}

// Events
if (profileEditButton) {
    profileEditButton.addEventListener('click', () => {
        const isEditing = profileEditButton.getAttribute('aria-pressed') !== 'true';
        setProfileEditMode(isEditing);
    });
}

if (profileCancelButton) {
    profileCancelButton.addEventListener('click', () => {
        profileEditableFields.forEach((field) => {
            if (field.tagName !== 'SELECT') {
                field.type = originalProfileTypes.get(field) ?? 'text';
            }
            field.value = originalProfileValues.get(field) ?? '';
            if (field.tagName === 'SELECT') {
                field.disabled = true;
            }
        });

        if (birthdayField && birthdayPickerLabel) {
            birthdayField.value = originalBirthdayValue;
            birthdayPickerLabel.textContent = originalBirthdayValue || 'Choose birthday';
            const birthday = parseMonthDay(originalBirthdayValue);

            if (birthday && birthdayPicker && birthdayMonthSelect) {
                birthdayPicker.dataset.selectedMonth = String(birthday.month);
                birthdayPicker.dataset.selectedDay = String(birthday.day);
                birthdayMonthSelect.value = String(birthday.month);
                renderBirthdayDays();
            }
        }

        if (zodiacField) {
            zodiacField.value = originalZodiacValue;
        }

        if (threeSizeField) {
            threeSizeField.value = originalThreeSizeValue;
        }

        setProfileEditMode(false);
    });
}

threeSizeInputs.forEach((field) => {
    field.addEventListener('input', syncThreeSizeValue);
});

if (birthdayPickerButton && birthdayPickerPopover) {
    birthdayPickerButton.addEventListener('click', () => {
        const isOpening = birthdayPickerPopover.classList.contains('d-none');
        birthdayPickerPopover.classList.toggle('d-none', !isOpening);

        if (birthdayCard) {
            birthdayCard.classList.toggle('is-calendar-open', isOpening);
        }

        renderBirthdayDays();
    });
}

if (birthdayMonthSelect) {
    birthdayMonthSelect.addEventListener('change', () => {
        const month = Number(birthdayMonthSelect.value);
        const selectedDay = Math.min(
            Number(birthdayPicker?.dataset.selectedDay || 1),
            new Date(currentYear, month, 0).getDate()
        );

        setBirthdayValue(month, selectedDay);
        renderBirthdayDays();
    });
}

if (birthdayPrevMonth && birthdayMonthSelect) {
    birthdayPrevMonth.addEventListener('click', () => {
        const currentMonth = Number(birthdayMonthSelect.value);
        birthdayMonthSelect.value = String(currentMonth === 1 ? 12 : currentMonth - 1);
        birthdayMonthSelect.dispatchEvent(new Event('change'));
    });
}

if (birthdayNextMonth && birthdayMonthSelect) {
    birthdayNextMonth.addEventListener('click', () => {
        const currentMonth = Number(birthdayMonthSelect.value);
        birthdayMonthSelect.value = String(currentMonth === 12 ? 1 : currentMonth + 1);
        birthdayMonthSelect.dispatchEvent(new Event('change'));
    });
}

document.addEventListener('click', (event) => {
    if (
        birthdayPicker
        && birthdayPickerPopover
        && !birthdayPickerPopover.classList.contains('d-none')
        && !birthdayPicker.contains(event.target)
    ) {
        closeBirthdayPicker();
    }
});

renderBirthdayDays();
})();
