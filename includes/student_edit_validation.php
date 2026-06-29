<?php

function student_edit_blood_type_options(): array
{
    return ['A', 'B', 'AB', 'O'];
}

function student_edit_month_options(): array
{
    return [
        1 => 'January',
        2 => 'February',
        3 => 'March',
        4 => 'April',
        5 => 'May',
        6 => 'June',
        7 => 'July',
        8 => 'August',
        9 => 'September',
        10 => 'October',
        11 => 'November',
        12 => 'December',
    ];
}

// Checks string length
function student_edit_text_length(string $value): int
{
    return function_exists('mb_strlen') ? mb_strlen($value, 'UTF-8') : strlen($value);
}

// Check the birthday format
function student_edit_parse_month_day(string $value): ?array
{
    $formats = ['!F d', '!F j'];

    foreach ($formats as $format) {
        $date = DateTime::createFromFormat($format, $value);
        $errors = DateTime::getLastErrors();
        $has_errors = $errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0);

        if ($date && !$has_errors) {
            return [
                'month' => (int) $date->format('m'),
                'day' => (int) $date->format('d'),
            ];
        }
    }

    return null;
}

function student_edit_split_three_size(?string $value): array
{
    if (!$value || !preg_match('/^(\d{2,3})[\/-](\d{2,3})[\/-](\d{2,3})$/', trim($value), $matches)) {
        return ['', '', ''];
    }

    return [$matches[1], $matches[2], $matches[3]];
}

function student_edit_class_code(?string $value): string
{
    return trim(preg_replace('/^Class\s+/i', '', trim((string) $value)));
}

// Calculate the zodiac
function student_edit_zodiac_from_month_day(int $month, int $day): string
{
    return match (true) {
        ($month === 1 && $day >= 20) || ($month === 2 && $day <= 18) => 'Aquarius',
        ($month === 2 && $day >= 19) || ($month === 3 && $day <= 20) => 'Pisces',
        ($month === 3 && $day >= 21) || ($month === 4 && $day <= 19) => 'Aries',
        ($month === 4 && $day >= 20) || ($month === 5 && $day <= 20) => 'Taurus',
        ($month === 5 && $day >= 21) || ($month === 6 && $day <= 20) => 'Gemini',
        ($month === 6 && $day >= 21) || ($month === 7 && $day <= 22) => 'Cancer',
        ($month === 7 && $day >= 23) || ($month === 8 && $day <= 22) => 'Leo',
        ($month === 8 && $day >= 23) || ($month === 9 && $day <= 22) => 'Virgo',
        ($month === 9 && $day >= 23) || ($month === 10 && $day <= 22) => 'Libra',
        ($month === 10 && $day >= 23) || ($month === 11 && $day <= 21) => 'Scorpio',
        ($month === 11 && $day >= 22) || ($month === 12 && $day <= 21) => 'Sagittarius',
        default => 'Capricorn',
    };
}

// Validation function
function validate_student_edit_profile(array $post, array $student): array
{
    // Birthday validation
    $birthday_input = trim($post['birthday'] ?? '');
    $birthday = $student['birthday'] ?? null;
    $zodiac = trim((string) ($student['zodiac'] ?? ''));
    $birthday_parts = null;

    if ($birthday_input !== '') {
        $birthday_parts = student_edit_parse_month_day($birthday_input);

        if ($birthday_parts) {
            $birthday_year = !empty($student['birthday'])
                ? (int) date('Y', strtotime((string) $student['birthday']))
                : 2000;
            $birthday = sprintf('%04d-%02d-%02d', (int) $birthday_year, $birthday_parts['month'], $birthday_parts['day']);
            $zodiac = student_edit_zodiac_from_month_day($birthday_parts['month'], $birthday_parts['day']);
        }
    }

    $class_code = student_edit_class_code($post['school_year'] ?? '');

    // Build a clean data array
    $data = [
        'name' => trim($post['name'] ?? ''),
        'name_jp' => trim($post['name_jp'] ?? ''),
        'birthday' => $birthday,
        'zodiac' => $zodiac,
        'blood_type' => strtoupper(trim($post['blood_type'] ?? '')),
        'height' => trim($post['height'] ?? ''),
        'weight' => trim($post['weight'] ?? ''),
        'school_year' => $class_code !== '' ? 'Class ' . $class_code : '',
        // Clean rank and performance stat values from the submitted form.
        'vocal' => max(0, (int) ($post['vocal'] ?? 0)),
        'dance' => max(0, (int) ($post['dance'] ?? 0)),
        'visual' => max(0, (int) ($post['visual'] ?? 0)),
    ];

    $three_size_parts = [
        trim($post['three_size_bust'] ?? ''),
        trim($post['three_size_waist'] ?? ''),
        trim($post['three_size_hip'] ?? ''),
    ];
    $three_size_has_any_value = implode('', $three_size_parts) !== '';
    $three_size_is_complete = $three_size_parts[0] !== '' && $three_size_parts[1] !== '' && $three_size_parts[2] !== '';
    $three_size_numbers_are_valid = true;

    foreach ($three_size_parts as $three_size_part) {
        if ($three_size_part !== '' && (!ctype_digit($three_size_part) || (int) $three_size_part < 40 || (int) $three_size_part > 150)) {
            $three_size_numbers_are_valid = false;
            break;
        }
    }

    $data['three_size'] = $three_size_is_complete ? implode('/', $three_size_parts) : '';

    $error = '';

    // Validation rules summary
    if ($data['name'] === '') {
        $error = 'Student name is required.';
    } elseif (student_edit_text_length($data['name']) > 100) {
        $error = 'Student name must be 100 characters or less.';
    } elseif (student_edit_text_length($data['name_jp']) > 100) {
        $error = 'Japanese name must be 100 characters or less.';
    } elseif ($birthday_input !== '' && empty($birthday_parts)) {
        $error = 'Birthday must use month and day, for example June 22.';
    } elseif ($data['blood_type'] !== '' && !in_array($data['blood_type'], student_edit_blood_type_options(), true)) {
        $error = 'Please choose a valid blood type.';
    } elseif ($data['height'] !== '' && (!ctype_digit($data['height']) || (int) $data['height'] < 100 || (int) $data['height'] > 220)) {
        $error = 'Height must be a number between 100 and 220 cm.';
    } elseif ($data['weight'] !== '' && (!ctype_digit($data['weight']) || (int) $data['weight'] < 20 || (int) $data['weight'] > 200)) {
        $error = 'Weight must be a number between 20 and 200 kg.';
    } elseif ($three_size_has_any_value && !$three_size_is_complete) {
        $error = 'Please complete all three size numbers.';
    } elseif (!$three_size_numbers_are_valid) {
        $error = 'Each Three Size value must be between 40 and 150.';
    } elseif ($class_code !== '' && !preg_match('/^\d+-\d+$/', $class_code)) {
        $error = 'Class must use a format like 1-1.';
    }

    return [
        'error' => $error,
        'data' => $data,
    ];
}
