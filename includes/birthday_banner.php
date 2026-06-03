<?php
// use $birthday_students if already exists
$birthday_students = $birthday_students ?? [];

// Only show banner if there are birthday students
if (!empty($birthday_students)):

    // Takes the first birthday student for the banner theme color
    $featured_birthday_student = $birthday_students[0];

    //Get all birthday names
    $birthday_names = array_map(
        fn ($student) => $student['name'] ?? 'Idol',
        $birthday_students
    );
    $birthday_name_text = implode(', ', $birthday_names);

    // Set birthday banner colors
    $birthday_primary = birthday_banner_color(
        $featured_birthday_student['theme_primary_color'] ?? null,
        '#FF6B9D'
    );
    $birthday_secondary = birthday_banner_color(
        $featured_birthday_student['theme_secondary_color'] ?? null,
        '#FFB3D1'
    );

    //Check if there are multiple birthday students
    $birthday_is_multiple = count($birthday_students) > 1;
?>
<section class="birthday-banner"
    style="--birthday-primary: <?= htmlspecialchars($birthday_primary, ENT_QUOTES, 'UTF-8') ?>; --birthday-secondary: <?= htmlspecialchars($birthday_secondary, ENT_QUOTES, 'UTF-8') ?>;">
    // Sparkles decoration
    <div class="birthday-sparkles" aria-hidden="true">
        <span></span>
        <span></span>
        <span></span>
        <span></span>
    </div>

    // Birthday avatar stack
    <div class="birthday-avatar-stack" aria-hidden="true">
        <?php foreach ($birthday_students as $birthday_student): ?>
        <img src="/gakumas-sms/assets/images/avatars/idols/<?= rawurlencode($birthday_student['name'] ?? 'Idol') ?>1.png"
            alt="" class="birthday-avatar">
        <?php endforeach; ?>
    </div>

    <div class="birthday-banner-copy">
        <p class="dashboard-eyebrow">Hatsuboshi Birthday</p>
        <h2>
            <?= htmlspecialchars($birthday_is_multiple ? 'Today is their birthday' : 'Today is ' . $birthday_name_text . '\'s birthday', ENT_QUOTES, 'UTF-8') ?>
        </h2>
        <p>
            <?= htmlspecialchars($birthday_is_multiple ? $birthday_name_text . ' are celebrating their special day.' : $birthday_name_text . ' is celebrating her special day.', ENT_QUOTES, 'UTF-8') ?>
        </p>
    </div>

    <div class="birthday-banner-icon" aria-hidden="true">
        <i class="bi bi-stars"></i>
    </div>
</section>
<?php endif; ?>
