<?php
require_once '../includes/auth.php';
require_role('admin');

require_once '../config/database.php';
require_once '../includes/admin_student_helpers.php';

// Prepare the page data
$show_create_form = isset($_GET['create']) && $_GET['create'] === '1';
$success = admin_student_flash_success();
$error = admin_student_handle_post($pdo, $show_create_form);

$producers = admin_student_load_producers($pdo);
$students = admin_student_load_students($pdo);
$grouped_students = admin_student_group_students_by_class($students);
// Calculate the numbers shown at the top
$unassigned_student_count = count(array_filter(
    $students,
    static fn(array $student): bool => empty($student['producer_id'])
));
$class_count = count($grouped_students);
// The code finds that student
$edit_student_id = isset($_GET['edit']) ? (int) $_GET['edit'] : 0;
$edit_student = admin_student_find($students, $edit_student_id);

$create_birthday_timestamp = time();
$create_birthday_month_value = (int) date('n', $create_birthday_timestamp);
$create_birthday_day_value = (int) date('j', $create_birthday_timestamp);
$create_birthday_display = date('F d', $create_birthday_timestamp);

$birthday_months = student_edit_month_options();
$blood_type_options = student_edit_blood_type_options();
$rank_options = student_edit_rank_options();
$today_month_value = (int) date('n');
$today_day_value = (int) date('j');
$today_birthday_display = date('F d');
$today_zodiac = student_edit_zodiac_from_month_day($today_month_value, $today_day_value);
$page_title = 'Students';
$page_styles = ['/gakumas-sms/assets/css/pages/admin_student.css'];
require_once '../includes/header.php';
require_once '../includes/sidebar.php';
?>

<main class="dashboard-main admin-students-main">
    <section class="admin-students-content">
        <section class="admin-students-hero">
            <div>
                <p class="dashboard-eyebrow">School Admin</p>
                <h2>Students</h2>
                <p>Create student accounts, manage profiles, and control producer assignments.</p>
            </div>

            <div class="admin-students-summary-grid" aria-label="Student summary">
                <div>
                    <span>Total Students</span>
                    <strong><?= count($students) ?></strong>
                </div>

                <div>
                    <span>Unassigned</span>
                    <strong><?= (int) $unassigned_student_count ?></strong>
                </div>

                <div>
                    <span>Classes</span>
                    <strong><?= (int) $class_count ?></strong>
                </div>
            </div>
        </section>

        <?php if ($success !== ''): ?>
        <div class="alert alert-success" role="alert"><?= e($success) ?></div>
        <?php endif; ?>

        <?php if ($error !== ''): ?>
        <div class="alert alert-danger" role="alert"><?= e($error) ?></div>
        <?php endif; ?>

        <section class="students-toolbar">
            <label class="students-search" for="adminStudentSearch">
                <i class="bi bi-search" aria-hidden="true"></i>
                <input type="search" id="adminStudentSearch"
                    placeholder="Search student name, class, producer, or status">
            </label>

            <button type="button" class="students-pending-button" id="adminClassSortButton" data-sort-direction="asc">
                <i class="bi bi-sort-alpha-down" aria-hidden="true"></i>
                Class: ASC
            </button>

            <?php if ($edit_student || $show_create_form): ?>
            <a href="/gakumas-sms/admin/students.php" class="btn btn-primary students-add-button">
                <i class="bi bi-list-ul" aria-hidden="true"></i>
                Student List
            </a>
            <?php else: ?>
            <a href="/gakumas-sms/admin/students.php?create=1#create-student"
                class="btn btn-primary students-add-button">
                <i class="bi bi-plus-lg" aria-hidden="true"></i>
                Create New Student
            </a>
            <?php endif; ?>
        </section>

        <!--The page shows the create form-->
        <?php if ($show_create_form && !$edit_student): ?>
        <section class="profile-card" id="create-student">
            <div class="profile-card-heading">
                <i class="bi bi-person-plus" aria-hidden="true"></i>
                <h2>Create Student Account and Profile</h2>
            </div>

            <form action="/gakumas-sms/admin/students.php?create=1#create-student" method="post"
                enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="action" value="create_student">

                <div class="admin-form-sections">
                    <fieldset class="admin-form-section">
                        <legend><i class="bi bi-key" aria-hidden="true"></i><span>Login Account</span></legend>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="username" class="form-label">Login Username</label>
                                <input type="text" id="username" name="username" class="form-control" maxlength="50"
                                    required>
                            </div>

                            <div class="col-md-6">
                                <label for="temporary_password" class="form-label">Temporary Password</label>
                                <input type="text" id="temporary_password" name="temporary_password"
                                    class="form-control" minlength="6" required>
                            </div>
                        </div>
                    </fieldset>


                    <fieldset class="admin-form-section">
                        <legend><i class="bi bi-person-badge" aria-hidden="true"></i><span>Identity</span></legend>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label">Student Name</label>
                                <input type="text" id="name" name="name" class="form-control" maxlength="100" required>
                            </div>

                            <div class="col-md-6">
                                <label for="name_jp" class="form-label">Japanese Name</label>
                                <input type="text" id="name_jp" name="name_jp" class="form-control" maxlength="100"
                                    required>
                            </div>

                            <div class="col-md-4">
                                <label for="birthday" class="form-label">Birthday</label>

                                <div class="profile-birthday-picker admin-birthday-picker" data-admin-birthday-picker
                                    data-selected-month="<?= (int) $today_month_value ?>"
                                    data-selected-day="<?= (int) $today_day_value ?>">
                                    <input type="hidden" id="birthday" name="birthday"
                                        value="<?= e($today_birthday_display) ?>" data-admin-birthday-value>

                                    <button type="button" class="form-control profile-birthday-button"
                                        data-admin-birthday-button>
                                        <span data-admin-birthday-label><?= e($today_birthday_display) ?></span>
                                        <i class="bi bi-calendar3" aria-hidden="true"></i>
                                    </button>

                                    <div class="profile-birthday-popover d-none" data-admin-birthday-popover>
                                        <div class="profile-birthday-controls">
                                            <button type="button" class="profile-calendar-nav" data-admin-birthday-prev
                                                aria-label="Previous month">
                                                <i class="bi bi-chevron-left" aria-hidden="true"></i>
                                            </button>

                                            <select class="form-select" data-admin-birthday-month>
                                                <?php foreach ($birthday_months as $month_number => $month_name): ?>
                                                <option value="<?= (int) $month_number ?>"
                                                    <?= (int) $month_number === (int) $today_month_value ? 'selected' : '' ?>>
                                                    <?= e($month_name) ?>
                                                </option>
                                                <?php endforeach; ?>
                                            </select>

                                            <button type="button" class="profile-calendar-nav" data-admin-birthday-next
                                                aria-label="Next month">
                                                <i class="bi bi-chevron-right" aria-hidden="true"></i>
                                            </button>
                                        </div>

                                        <div class="profile-calendar-weekdays" aria-hidden="true">
                                            <span>Sun</span>
                                            <span>Mon</span>
                                            <span>Tue</span>
                                            <span>Wed</span>
                                            <span>Thu</span>
                                            <span>Fri</span>
                                            <span>Sat</span>
                                        </div>

                                        <div class="profile-calendar-days" data-admin-birthday-days></div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label for="zodiac" class="form-label">Zodiac</label>
                                <input type="text" id="zodiac" name="zodiac" class="form-control"
                                    value="<?= e($today_zodiac) ?>" readonly data-admin-zodiac>
                            </div>

                            <div class="col-md-4">
                                <label for="blood_type" class="form-label">Blood Type</label>
                                <select id="blood_type" name="blood_type" class="form-select" required>
                                    <option value="">Choose blood type</option>
                                    <?php foreach ($blood_type_options as $blood_type_option): ?>
                                    <option value="<?= e($blood_type_option) ?>"><?= e($blood_type_option) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label for="hometown" class="form-label">Hometown</label>
                                <input type="text" id="hometown" name="hometown" class="form-control" maxlength="50">
                            </div>

                            <div class="col-md-4">
                                <label for="avatar" class="form-label">Avatar</label>
                                <input type="file" id="avatar" name="avatar" class="form-control"
                                    accept="image/jpeg,image/png,image/webp" required>
                            </div>
                    </fieldset>

                    <fieldset class="admin-form-section">
                        <legend><i class="bi bi-person-check" aria-hidden="true"></i><span>Producer Assignment</span>
                        </legend>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="assignment_type" class="form-label">Assignment</label>
                                <select id="assignment_type" name="assignment_type" class="form-select"
                                    data-producer-toggle>
                                    <option value="unassigned">Unassigned</option>
                                    <option value="producer">Assigned</option>
                                </select>
                            </div>

                            <div class="col-md-6 d-none" data-producer-select-wrap>
                                <label for="producer_id" class="form-label">Producer Name</label>
                                <select id="producer_id" name="producer_id" class="form-select" data-producer-select>
                                    <option value="">Choose producer</option>
                                    <?php foreach ($producers as $producer): ?>
                                    <option value="<?= (int) $producer['id'] ?>"><?= e($producer['username']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </fieldset>

                    <fieldset class="admin-form-section">
                        <legend><i class="bi bi-mortarboard" aria-hidden="true"></i><span>Academy Details</span>
                        </legend>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="school_year" class="form-label">Class</label>
                                <div class="input-group">
                                    <span class="input-group-text">Class</span>
                                    <input type="text" id="school_year" name="school_year" class="form-control"
                                        placeholder="1-1" pattern="\d+-\d+" required>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label for="rank" class="form-label">Rank</label>
                                <select id="rank" name="rank" class="form-select" required>
                                    <?php foreach ($rank_options as $rank_option): ?>
                                    <option value="<?= e($rank_option) ?>"><?= e($rank_option) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>


                        </div>
                    </fieldset>

                    <fieldset class="admin-form-section">
                        <legend><i class="bi bi-rulers" aria-hidden="true"></i><span>Physical Details</span></legend>
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="height" class="form-label">Height</label>
                                <div class="input-group">
                                    <input type="number" id="height" name="height" class="form-control" min="100"
                                        max="220" required>
                                    <span class="input-group-text">cm</span>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <label for="weight" class="form-label">Weight</label>
                                <div class="input-group">
                                    <input type="number" id="weight" name="weight" class="form-control" min="20"
                                        max="200" required>
                                    <span class="input-group-text">kg</span>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Three Size</label>
                                <div class="profile-three-size">
                                    <input type="number" name="three_size_bust"
                                        class="form-control profile-three-size-input" min="40" max="150" placeholder=""
                                        required>
                                    <span class="profile-three-size-separator">/</span>
                                    <input type="number" name="three_size_waist"
                                        class="form-control profile-three-size-input" min="40" max="150" placeholder=""
                                        required>
                                    <span class="profile-three-size-separator">/</span>
                                    <input type="number" name="three_size_hip"
                                        class="form-control profile-three-size-input" min="40" max="150" placeholder=""
                                        required>
                                </div>
                            </div>
                        </div>
                    </fieldset>

                    <fieldset class="admin-form-section">
                        <legend><i class="bi bi-bar-chart-line" aria-hidden="true"></i><span>Performance and
                                Theme</span></legend>
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="vocal" class="form-label">Vocal</label>
                                <input type="number" id="vocal" name="vocal" class="form-control" min="0" value="0"
                                    required>
                            </div>

                            <div class="col-md-3">
                                <label for="dance" class="form-label">Dance</label>
                                <input type="number" id="dance" name="dance" class="form-control" min="0" value="0"
                                    required>
                            </div>

                            <div class="col-md-3">
                                <label for="visual" class="form-label">Visual</label>
                                <input type="number" id="visual" name="visual" class="form-control" min="0" value="0"
                                    required>
                            </div>

                            <div class="col-12 admin-form-row-break" aria-hidden="true"></div>

                            <div class="col-md-5 col-lg-4">
                                <label for="theme_primary_color" class="form-label">Primary Color</label>
                                <input type="color" id="theme_primary_color" name="theme_primary_color"
                                    class="form-control form-control-color" value="<?= e(DEFAULT_THEME_PRIMARY) ?>"
                                    required>
                            </div>

                            <div class="col-md-5 col-lg-4">
                                <label for="theme_secondary_color" class="form-label">Secondary Color</label>
                                <input type="color" id="theme_secondary_color" name="theme_secondary_color"
                                    class="form-control form-control-color" value="<?= e(DEFAULT_THEME_SECONDARY) ?>"
                                    required>
                            </div>
                        </div>
                    </fieldset>

                    <fieldset class="admin-form-section">
                        <legend><i class="bi bi-journal-text" aria-hidden="true"></i><span>Optional Notes</span>
                        </legend>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="special_skill" class="form-label">Special Skill</label>
                                <textarea id="special_skill" name="special_skill" class="form-control"
                                    rows="3"></textarea>
                            </div>

                            <div class="col-md-6">
                                <label for="hobbies" class="form-label">Hobbies</label>
                                <textarea id="hobbies" name="hobbies" class="form-control" rows="3"></textarea>
                            </div>

                            <div class="col-12">
                                <label for="bio" class="form-label">Bio</label>
                                <textarea id="bio" name="bio" class="form-control" rows="4"></textarea>
                            </div>
                        </div>
                    </fieldset>
                </div>

                <div class="profile-form-actions">
                    <a href="/gakumas-sms/admin/students.php" class="btn btn-secondary admin-cancel-button">
                        <i class="bi bi-x-lg" aria-hidden="true"></i>
                        Cancel Create
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save" aria-hidden="true"></i>
                        Create Student
                    </button>
                </div>
            </form>
        </section>
        <?php endif; ?>

        <?php if ($edit_student): ?>
        <?php
            $edit_three_size_parts = student_edit_split_three_size($edit_student['three_size'] ?? '');
            $edit_school_year_code = student_edit_class_code($edit_student['school_year'] ?? '');
            $edit_birthday_timestamp = !empty($edit_student['birthday']) ? strtotime($edit_student['birthday']) : false;
            $edit_birthday_display = $edit_birthday_timestamp ? date('F d', $edit_birthday_timestamp) : $today_birthday_display;
            $edit_birthday_month_value = $edit_birthday_timestamp ? (int) date('n', $edit_birthday_timestamp) : $today_month_value;
            $edit_birthday_day_value = $edit_birthday_timestamp ? (int) date('j', $edit_birthday_timestamp) : $today_day_value;
            ?>
        <section class="profile-card" id="edit-student">
            <div class="profile-card-heading">
                <i class="bi bi-pencil-square" aria-hidden="true"></i>
                <h2>Edit Student and Profile: <?= e($edit_student['name']) ?></h2>
            </div>

            <form action="/gakumas-sms/admin/students.php?edit=<?= (int) $edit_student['id'] ?>#edit-student"
                method="post" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="action" value="update_student">
                <input type="hidden" name="student_id" value="<?= (int) $edit_student['id'] ?>">

                <div class="admin-form-sections">
                    <fieldset class="admin-form-section">
                        <legend><i class="bi bi-person-badge" aria-hidden="true"></i><span>Identity</span></legend>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="edit_name" class="form-label">Student Name</label>
                                <input type="text" id="edit_name" name="name" class="form-control" maxlength="100"
                                    value="<?= e($edit_student['name']) ?>" required>
                            </div>

                            <div class="col-md-6">
                                <label for="edit_name_jp" class="form-label">Japanese Name</label>
                                <input type="text" id="edit_name_jp" name="name_jp" class="form-control" maxlength="100"
                                    value="<?= e($edit_student['name_jp']) ?>" required>
                            </div>

                            <div class="col-md-4">
                                <label for="edit_birthday" class="form-label">Birthday</label>

                                <div class="profile-birthday-picker admin-birthday-picker" data-admin-birthday-picker
                                    data-selected-month="<?= (int) $edit_birthday_month_value ?>"
                                    data-selected-day="<?= (int) $edit_birthday_day_value ?>">
                                    <input type="hidden" id="edit_birthday" name="birthday"
                                        value="<?= e($edit_birthday_display) ?>" data-admin-birthday-value>

                                    <button type="button" class="form-control profile-birthday-button"
                                        data-admin-birthday-button>
                                        <span data-admin-birthday-label><?= e($edit_birthday_display) ?></span>
                                        <i class="bi bi-calendar3" aria-hidden="true"></i>
                                    </button>

                                    <div class="profile-birthday-popover d-none" data-admin-birthday-popover>
                                        <div class="profile-birthday-controls">
                                            <button type="button" class="profile-calendar-nav" data-admin-birthday-prev
                                                aria-label="Previous month">
                                                <i class="bi bi-chevron-left" aria-hidden="true"></i>
                                            </button>

                                            <select class="form-select" data-admin-birthday-month>
                                                <?php foreach ($birthday_months as $month_number => $month_name): ?>
                                                <option value="<?= (int) $month_number ?>"
                                                    <?= (int) $month_number === (int) $edit_birthday_month_value ? 'selected' : '' ?>>
                                                    <?= e($month_name) ?>
                                                </option>
                                                <?php endforeach; ?>
                                            </select>

                                            <button type="button" class="profile-calendar-nav" data-admin-birthday-next
                                                aria-label="Next month">
                                                <i class="bi bi-chevron-right" aria-hidden="true"></i>
                                            </button>
                                        </div>

                                        <div class="profile-calendar-weekdays" aria-hidden="true">
                                            <span>Sun</span>
                                            <span>Mon</span>
                                            <span>Tue</span>
                                            <span>Wed</span>
                                            <span>Thu</span>
                                            <span>Fri</span>
                                            <span>Sat</span>
                                        </div>

                                        <div class="profile-calendar-days" data-admin-birthday-days></div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label for="edit_zodiac" class="form-label">Zodiac</label>
                                <input type="text" id="edit_zodiac" name="zodiac" class="form-control" maxlength="100"
                                    value="<?= e($edit_student['zodiac']) ?>" readonly data-admin-zodiac>
                            </div>

                            <div class="col-md-4">
                                <label for="edit_blood_type" class="form-label">Blood Type</label>
                                <select id="edit_blood_type" name="blood_type" class="form-select" required>
                                    <option value="">Choose blood type</option>
                                    <?php foreach ($blood_type_options as $blood_type_option): ?>
                                    <option value="<?= e($blood_type_option) ?>"
                                        <?= ($edit_student['blood_type'] ?? '') === $blood_type_option ? 'selected' : '' ?>>
                                        <?= e($blood_type_option) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label for="edit_hometown" class="form-label">Hometown</label>
                                <input type="text" id="edit_hometown" name="hometown" class="form-control"
                                    maxlength="50" value="<?= e($edit_student['hometown']) ?>">
                            </div>

                            <div class="col-md-4">
                                <label for="edit_avatar" class="form-label">Replace Avatar</label>
                                <input type="file" id="edit_avatar" name="avatar" class="form-control"
                                    accept="image/jpeg,image/png,image/webp">
                            </div>
                        </div>
                    </fieldset>


                    <fieldset class="admin-form-section">
                        <legend><i class="bi bi-person-check" aria-hidden="true"></i><span>Producer Assignment</span>
                        </legend>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="edit_assignment_type" class="form-label">Assignment</label>
                                <select id="edit_assignment_type" name="assignment_type" class="form-select"
                                    data-producer-toggle>
                                    <option value="unassigned"
                                        <?= empty($edit_student['producer_id']) ? 'selected' : '' ?>>Unassigned</option>
                                    <option value="producer"
                                        <?= !empty($edit_student['producer_id']) ? 'selected' : '' ?>>Assigned</option>
                                </select>
                            </div>

                            <div class="col-md-6 <?= empty($edit_student['producer_id']) ? 'd-none' : '' ?>"
                                data-producer-select-wrap>
                                <label for="edit_producer_id" class="form-label">Producer Name</label>
                                <select id="edit_producer_id" name="producer_id" class="form-select"
                                    data-producer-select>
                                    <option value="">Choose producer</option>
                                    <?php foreach ($producers as $producer): ?>
                                    <option value="<?= (int) $producer['id'] ?>"
                                        <?= (int) ($edit_student['producer_id'] ?? 0) === (int) $producer['id'] ? 'selected' : '' ?>>
                                        <?= e($producer['username']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </fieldset>

                    <fieldset class="admin-form-section">
                        <legend><i class="bi bi-mortarboard" aria-hidden="true"></i><span>Academy Details</span>
                        </legend>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="edit_school_year" class="form-label">Class</label>
                                <div class="input-group">
                                    <span class="input-group-text">Class</span>
                                    <input type="text" id="edit_school_year" name="school_year" class="form-control"
                                        value="<?= e($edit_school_year_code) ?>" pattern="\d+-\d+" required>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label for="edit_rank" class="form-label">Rank</label>
                                <select id="edit_rank" name="rank" class="form-select" required>
                                    <?php foreach ($rank_options as $rank_option): ?>
                                    <option value="<?= e($rank_option) ?>"
                                        <?= ($edit_student['rank'] ?? '') === $rank_option ? 'selected' : '' ?>>
                                        <?= e($rank_option) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>


                        </div>
                    </fieldset>

                    <fieldset class="admin-form-section">
                        <legend><i class="bi bi-rulers" aria-hidden="true"></i><span>Physical Details</span></legend>
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="edit_height" class="form-label">Height</label>
                                <div class="input-group">
                                    <input type="number" id="edit_height" name="height" class="form-control" min="100"
                                        max="220" value="<?= e((string) $edit_student['height']) ?>" required>
                                    <span class="input-group-text">cm</span>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <label for="edit_weight" class="form-label">Weight</label>
                                <div class="input-group">
                                    <input type="number" id="edit_weight" name="weight" class="form-control" min="20"
                                        max="200" value="<?= e((string) $edit_student['weight']) ?>" required>
                                    <span class="input-group-text">kg</span>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Three Size</label>
                                <div class="profile-three-size">
                                    <input type="number" name="three_size_bust"
                                        class="form-control profile-three-size-input" min="40" max="150"
                                        value="<?= e($edit_three_size_parts[0] ?? '') ?>" required>
                                    <span class="profile-three-size-separator">/</span>
                                    <input type="number" name="three_size_waist"
                                        class="form-control profile-three-size-input" min="40" max="150"
                                        value="<?= e($edit_three_size_parts[1] ?? '') ?>" required>
                                    <span class="profile-three-size-separator">/</span>
                                    <input type="number" name="three_size_hip"
                                        class="form-control profile-three-size-input" min="40" max="150"
                                        value="<?= e($edit_three_size_parts[2] ?? '') ?>" required>
                                </div>
                            </div>
                        </div>
                    </fieldset>

                    <fieldset class="admin-form-section">
                        <legend><i class="bi bi-bar-chart-line" aria-hidden="true"></i><span>Performance and
                                Theme</span></legend>
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="edit_vocal" class="form-label">Vocal</label>
                                <input type="number" id="edit_vocal" name="vocal" class="form-control" min="0"
                                    value="<?= (int) $edit_student['vocal'] ?>" required>
                            </div>

                            <div class="col-md-3">
                                <label for="edit_dance" class="form-label">Dance</label>
                                <input type="number" id="edit_dance" name="dance" class="form-control" min="0"
                                    value="<?= (int) $edit_student['dance'] ?>" required>
                            </div>

                            <div class="col-md-3">
                                <label for="edit_visual" class="form-label">Visual</label>
                                <input type="number" id="edit_visual" name="visual" class="form-control" min="0"
                                    value="<?= (int) $edit_student['visual'] ?>" required>
                            </div>

                            <div class="col-12 admin-form-row-break" aria-hidden="true"></div>

                            <div class="col-md-5 col-lg-4">
                                <label for="edit_theme_primary_color" class="form-label">Primary Color</label>
                                <input type="color" id="edit_theme_primary_color" name="theme_primary_color"
                                    class="form-control form-control-color"
                                    value="<?= e($edit_student['theme_primary_color'] ?: DEFAULT_THEME_PRIMARY) ?>"
                                    required>
                            </div>

                            <div class="col-md-5 col-lg-4">
                                <label for="edit_theme_secondary_color" class="form-label">Secondary Color</label>
                                <input type="color" id="edit_theme_secondary_color" name="theme_secondary_color"
                                    class="form-control form-control-color"
                                    value="<?= e($edit_student['theme_secondary_color'] ?: DEFAULT_THEME_SECONDARY) ?>"
                                    required>
                            </div>
                        </div>
                    </fieldset>

                    <fieldset class="admin-form-section">
                        <legend><i class="bi bi-journal-text" aria-hidden="true"></i><span>Optional Notes</span>
                        </legend>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="edit_special_skill" class="form-label">Special Skill</label>
                                <textarea id="edit_special_skill" name="special_skill" class="form-control"
                                    rows="3"><?= e($edit_student['special_skill']) ?></textarea>
                            </div>

                            <div class="col-md-6">
                                <label for="edit_hobbies" class="form-label">Hobbies</label>
                                <textarea id="edit_hobbies" name="hobbies" class="form-control"
                                    rows="3"><?= e($edit_student['hobbies']) ?></textarea>
                            </div>

                            <div class="col-12">
                                <label for="edit_bio" class="form-label">Bio</label>
                                <textarea id="edit_bio" name="bio" class="form-control"
                                    rows="4"><?= e($edit_student['bio']) ?></textarea>
                            </div>
                        </div>
                    </fieldset>
                </div>

                <div class="profile-form-actions">
                    <a href="/gakumas-sms/admin/students.php" class="btn btn-secondary admin-cancel-button">
                        <i class="bi bi-x-lg" aria-hidden="true"></i>
                        Cancel Edit
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save" aria-hidden="true"></i>
                        Save Profile
                    </button>
                </div>
            </form>
        </section>
        <?php endif; ?>

        <section class="admin-student-list">
            <?php if (empty($grouped_students)): ?>
            <div class="empty-dashboard-state compact">
                <strong>No students yet</strong>
                <p>Use the Create New Student button to add the first student account.</p>
            </div>
            <?php else: ?>
            <!--Students are grouped by class-->
            <?php foreach ($grouped_students as $class_name => $class_students): ?>
            <section class="students-class-section" data-student-class
                data-class-sort="<?= e(admin_student_sort_text($class_name)) ?>">
                <div class="students-class-heading">
                    <div>
                        <i class="bi bi-people-fill" aria-hidden="true"></i>
                        <h2><?= e($class_name) ?></h2>
                    </div>

                    <span><?= count($class_students) ?> student<?= count($class_students) === 1 ? '' : 's' ?></span>
                </div>

                <div class="students-table-wrap">
                    <table class="table align-middle students-table" data-admin-students-table>
                        <thead>
                            <tr>
                                <th>
                                    Student
                                    <button type="button" class="student-sort-button" data-sort-column="student"
                                        data-sort-direction="asc" aria-label="Sort students ascending">
                                        <i class="bi bi-sort-alpha-down" aria-hidden="true"></i>
                                    </button>
                                </th>
                                <th>Rank</th>
                                <th>Producer</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($class_students as $student): ?>
                            <?php
                                        $producer_status = $student['producer_status'] ?? 'unassigned';
                                        $producer_status_label = ucwords(str_replace('_', ' ', $producer_status));
                                        $producer_display = $student['producer_name'] ?: 'Unassigned';
                                        $avatar_path = admin_student_avatar_path($student['avatar'] ?? '');
                                        $search_text = admin_student_sort_text(implode(' ', [
                                            $student['name'] ?? '',
                                            $student['name_jp'] ?? '',
                                            $class_name,
                                            $student['rank'] ?? '',
                                            $producer_display,
                                            $producer_status,
                                        ]));
                                        ?>
                            <tr class="student-row" data-student-search="<?= e($search_text) ?>"
                                data-student-sort="<?= e(admin_student_sort_text($student['name'] ?? '')) ?>">
                                <td data-sort-value="<?= e(admin_student_sort_text($student['name'] ?? '')) ?>">
                                    <div class="student-name-cell">
                                        <img src="<?= e($avatar_path) ?>" alt="<?= e($student['name']) ?>"
                                            class="student-avatar">
                                        <div>
                                            <strong><?= e($student['name']) ?></strong>
                                            <?php if (!empty($student['name_jp'])): ?>
                                            <small lang="ja"><?= e($student['name_jp']) ?></small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td><?= e($student['rank'] ?: 'Debut') ?></td>
                                <td><?= e($producer_display) ?></td>
                                <td>
                                    <span
                                        class="student-status student-status-<?= e(str_replace('_', '-', $producer_status)) ?>">
                                        <?= e($producer_status_label) ?>
                                    </span>
                                </td>
                                <td class="text-end student-actions">
                                    <a href="/gakumas-sms/admin/students.php?edit=<?= (int) $student['id'] ?>#edit-student"
                                        class="student-action-button" aria-label="Edit <?= e($student['name']) ?>"
                                        title="Edit student">
                                        <i class="bi bi-pencil" aria-hidden="true"></i>
                                    </a>

                                    <?php if (!empty($student['producer_id'])): ?>
                                    <form action="/gakumas-sms/admin/students.php" method="post" class="d-inline">
                                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                        <input type="hidden" name="action" value="remove_producer">
                                        <input type="hidden" name="student_id" value="<?= (int) $student['id'] ?>">
                                        <button type="submit" class="student-action-button danger"
                                            aria-label="Remove producer from <?= e($student['name']) ?>"
                                            title="Remove producer"
                                            onclick="return confirm(<?= htmlspecialchars(json_encode('Remove producer from ' . $student['name'] . '?'), ENT_QUOTES, 'UTF-8') ?>);">
                                            <i class="bi bi-person-dash" aria-hidden="true"></i>
                                        </button>
                                    </form>
                                    <?php else: ?>
                                    <span class="student-action-muted" title="No producer assigned">
                                        <i class="bi bi-person-dash" aria-hidden="true"></i>
                                    </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
            <?php endforeach; ?>

            <section class="empty-dashboard-state students-search-empty d-none" id="adminStudentSearchEmpty">
                <strong>No matching students</strong>
                <p>Try another name, class, producer, rank, or status.</p>
            </section>
            <?php endif; ?>
        </section>
    </section>
</main>

<script src="/gakumas-sms/assets/js/admin-students.js"></script>

<?php require_once '../includes/footer.php'; ?>