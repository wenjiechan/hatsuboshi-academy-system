# ★ Hatsuboshi Gakuen Student Management System

A web-based platform for managing student schedules, training stats, and communication
at Hatsuboshi Academy. Inspired by **Gakuen Idolm@ster (学園アイドルマスター)**, the system
brings the academy's 13-idol roster online with role-based workflows, threaded messaging,
and per-idol theming.

---

## Overview

Hatsuboshi Gakuen is a full-stack PHP/MySQL application that models the day-to-day
operations of an idol academy. Producers and teachers coordinate the training of
student idols across vocal, dance, and visual disciplines. Students manage their own
profiles, view their schedules, build their song repertoire, and chat directly with
the staff assigned to them.

The Gakumas theme is a creative wrapper. Underneath is a security-conscious CRUD
system built around four user roles, prepared-statement data access, CSRF-protected
state changes, and a typed notification feed. The same architecture would apply to
any school, training program, or talent agency.

---

## Features

**Authentication & Access Control.** Four distinct roles — Admin, Producer, Teacher,
Student — with bcrypt password hashing, session regeneration on login, and server-side
ownership checks on every protected resource.

**Conversation-Based Messaging.** Threaded direct conversations between any two users,
with per-user state for last-read time, archive, mute, and soft-delete. Messages are
typed (text, system, birthday, producer requests) and deduplicated against double-submits.

**Producer ↔ Student Workflow.** Producers propose adding or removing students from
their roster through chat-integrated requests. Each request flows through a
`pending → accepted / rejected / cancelled / expired` lifecycle, with the student's
`producer_status` field updated atomically.

**Student Profiles.** Bilingual names, birthday, zodiac, blood type, hometown, hobbies,
special skills, school year, rank, and a personal bio. Students self-edit a curated
subset; producers and admins have full control. Avatar uploads are validated by
extension, MIME type, and size, and stored with randomized filenames.

**Song Catalog.** A shared repertoire of academy songs — solo, group, remix, or cover —
linked to students through a many-to-many relationship, so a single song can belong
to one idol or many.

**Notifications.** A typed, dedupe-keyed feed delivered through both an in-app bell
icon and a live JavaScript polling endpoint. Schedule-start alerts are auto-generated
by a cron script.

**Personalization.** Every user carries their own primary and secondary theme colors,
applied at runtime through CSS variables. Students receive a birthday banner on their
dashboard. Producers can be configured with personality-tailored greeting messages
per student.

**Background Jobs.** Cron-runnable scripts snapshot daily Vocal/Dance/Visual stats for
time-series charts and generate scheduled notifications for upcoming activities.

---

## Tech Stack

| Layer            | Choice                                       |
| ---------------- | -------------------------------------------- |
| Backend          | PHP 8.0+                                     |
| Database         | MySQL 8 / MariaDB 10.5+                      |
| Data Access      | PDO with prepared statements                 |
| Frontend         | Bootstrap 5, Chart.js, FullCalendar.js       |
| Icons & Fonts    | Bootstrap Icons, M PLUS Rounded 1c, Quicksand |
| Local Server     | XAMPP, Laragon, or MAMP                      |

---

## Getting Started

The repository is named `hatsuboshi-academy-system`, but the project folder and
database keep the shorter `gakumas-sms` naming used throughout the codebase.

```bash
# 1. Clone into the gakumas-sms folder inside your web root (htdocs/ or www/)
git clone https://github.com/wenjiechan/hatsuboshi-academy-system.git gakumas-sms
cd gakumas-sms

# 2. Create the database and import schema + seed data
mysql -u root -p -e "CREATE DATABASE gakumas_sms CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root -p gakumas_sms < database/schema.sql
mysql -u root -p gakumas_sms < database/sample_data.sql

# 3. (Linux/Mac only) make uploads writable
chmod -R 755 uploads/
```

Edit `config/database.php` only if your MySQL uses non-default credentials.
Visit `http://localhost/gakumas-sms/` and log in.

To run the background jobs, schedule them with cron (Linux/Mac) or Task Scheduler
(Windows):

```bash
# Every day at midnight: snapshot stats for charting
0 0 * * * php /path/to/gakumas-sms/scripts/daily_stat_snapshot.php

# Every minute: generate schedule-start notifications
* * * * * php /path/to/gakumas-sms/scripts/generate_notifications.php
```

---

## Demo Accounts

Passwords follow a simple rule, all lowercase, no spaces:

- **Students:** first name + birthday in `MMDD` format (e.g. Saki Hanami, born April 2 → `saki0402`)
- **Producer:** `producer001`
- **Admin:** `admin001`
- **Teachers:** discipline + `001` (e.g. `vocal001`, `dance001`, `visual001`)

| Role     | Username           | Password      |
| -------- | ------------------ | ------------- |
| Admin    | `Kunio Juo`        | `admin001`    |
| Producer | `Producer`         | `producer001` |
| Teacher  | `Vocal Trainer`    | `vocal001`    |
| Teacher  | `Dance Trainer`    | `dance001`    |
| Teacher  | `Visual Trainer`   | `visual001`   |
| Student  | `Saki Hanami`      | `saki0402`    |
| Student  | `Temari Tsukimura` | `temari0603`  |
| Student  | `Kotone Fujita`    | `kotone0429`  |
| Student  | `Lilja Katsuragi`  | `lilja0724`   |
| Student  | `Sumika Shiun`     | `sumika1111`  |
| Student  | `Hiro Shinosawa`   | `hiro1221`    |
| Student  | `China Kuramoto`   | `china0801`   |
| Student  | `Ume Hanami`       | `ume0401`     |
| Student  | `Misuzu Hataya`    | `misuzu0206`  |
| Student  | `Mao Arimura`      | `mao0118`     |
| Student  | `Rinami Himesaki`  | `rinami0305`  |
| Student  | `Sena Juo`         | `sena1207`    |
| Student  | `Tsubame Amaya`    | `tsubame0520` |

Usernames contain spaces — log in with the exact name as shown.

---

## User Roles

| Role         | Responsibilities                                                                          |
| ------------ | ----------------------------------------------------------------------------------------- |
| **Admin**    | System-level oversight. Manages all users, roles, and global settings.                    |
| **Producer** | Manages assigned roster. CRUD on students, schedules, lessons, events, and songs.         |
| **Teacher**  | Trains assigned students in their specialty (vocal, dance, or visual). Updates stats.     |
| **Student**  | Views own profile and schedule. Edits bio, hobbies, and avatar. Chats with staff.         |

Role and ownership are verified server-side on every protected resource. UI hiding is
treated as a UX convenience, never as the access boundary.

---

## Project Structure

```
gakumas-sms/
├── admin/        Admin-only pages — global user, student, producer, and teacher management
├── producer/     Producer workspace — roster, schedules, events, lessons, reports
├── teacher/      Teacher workspace — assigned students, lessons
├── student/      Student workspace — dashboard, profile, schedule, stats, songs
├── messages/     Conversation-based chat — inbox, threads, compose, archive, mute
├── api/          JSON polling endpoints for live messaging and notifications
├── scripts/      Cron jobs — daily stat snapshots and notification generation
├── includes/     Auth, layout, and reusable helpers for messages, stats, and theming
├── assets/       CSS, JavaScript, and images (avatars, decoration, logos)
├── config/       Database configuration
├── database/     schema.sql and sample_data.sql
├── tests/        Integration tests
├── uploads/      User-uploaded files (gitignored)
└── index.php · login.php · logout.php · notifications.php
```

---

## Security

The system follows established PHP security practices end-to-end:

- Passwords are hashed with `password_hash()` (bcrypt) and verified with `password_verify()`.
- All database queries use **PDO prepared statements** with parameter binding; no query is built by string concatenation.
- `session_regenerate_id(true)` is called on login to prevent session fixation.
- Session cookies are set with `httponly` and `samesite=Lax` flags, plus `secure` in production.
- All output is escaped via `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`.
- **CSRF tokens** are required on every state-changing POST request.
- Input is validated server-side with `filter_var()` and whitelist comparisons.
- File uploads are validated by extension, MIME type, and size, and renamed before storage.
- Resource access is gated by both role and ownership — a student cannot access another student's data by guessing IDs.
- Messages and notifications carry deduplication keys to prevent double-submits and duplicate alerts.

---

## License & Credits

Inspired by **Gakuen Idolm@ster (学園アイドルマスター)** — © Bandai Namco Entertainment Inc.
This is a non-commercial educational project. Character names, themes, and visual
references belong to their respective rights holders.

The source code in this repository is released under the [MIT License](LICENSE).
The Gakumas-themed content, character data, and visual references are **not** covered
by this license and must not be used commercially.

Research drew on the official site at <https://gakuen.idolmaster-official.jp/>, along
with Pixiv百科事典, ニコニコ大百科, note.com, Game8, and the Project iM@S Wiki.