# ★ Hatsuboshi Gakuen — Student Schedule Management System

> A PHP/MySQL web application inspired by **Gakuen Idolm@ster (学園アイドルマスター / Gakumas)**.
> Updated for the 2026 roster of **13 playable idols**.

A web-based student schedule management system themed around Hatsuboshi Academy. Students (idols) manage their training schedules, track stats, view their profiles, and communicate with producers and teachers. Producers and teachers act as administrators who manage student data, lessons, and academy events.

The Gakumas theme is a creative wrapper — under the hood this is a solid, security-conscious PHP/MySQL CRUD application built around role-based authentication.

---

## 📖 Table of Contents

- [Features](#-features)
- [Tech Stack](#-tech-stack)
- [Requirements](#-requirements)
- [Installation](#-installation)
- [Project Structure](#-project-structure)
- [User Roles](#-user-roles)
- [The 13 Idols](#-the-13-idols-2026-roster)
- [Database Schema](#-database-schema-overview)
- [Theming](#-theming)
- [Security](#-security)
- [Roadmap](#-roadmap)
- [Credits & References](#-credits--references)
- [License](#-license)

---

## ✨ Features

### Core (MVP)
- 🔐 **Authentication** — login, logout, hashed passwords, session management
- 🛡 **Role-based access control** — Producer, Teacher, Student
- 👤 **Student profiles** — view & limited self-edit
- 📅 **Schedule system** — weekly/monthly calendar view via FullCalendar.js
- 📊 **Stat tracking** — Vocal, Dance, Visual with history
- 💬 **User-to-user messaging** — inbox, sent, compose, reply, soft-delete
- 🎵 **Song list** — each idol maintains a personal repertoire; songs can be solo or shared between unit members
- 🔔 **Notification center** — bell icon with unread badge

### Enhanced
- 🎤 **Lesson management** — assign lessons, update stats automatically
- 🏆 **Audition / event system** — HIF (Hatsuboshi Idol Festival) auditions with stat requirements
- 📈 **Charts & analytics** — Chart.js-powered stat progression
- 🎨 **Per-idol theme switching** — each idol applies her signature color palette
- 💌 **Personality-based producer messages** — context-aware greetings tailored to each idol's personality

### Optional / Stretch
- 👯 Unit/group management (Re;IRIS, SyngUp!, etc.)
- 📔 Memory/diary system
- 🏅 Achievement & badge system
- 🎂 Birthday reminders
- 🌙 Dark mode
- 📱 PWA support

---

## 🛠 Tech Stack

| Category        | Choice                              |
|-----------------|-------------------------------------|
| Backend         | PHP 8.0+                            |
| Database        | MySQL 8 / MariaDB 10.5+             |
| DB Access       | PDO with prepared statements        |
| CSS Framework   | Bootstrap 5                         |
| Calendar        | FullCalendar.js                     |
| Charts          | Chart.js                            |
| Icons           | Bootstrap Icons / Boxicons          |
| Fonts           | M PLUS Rounded 1c, Quicksand        |
| Local Server    | XAMPP / Laragon / MAMP              |

---

## 📋 Requirements

- PHP 8.0 or higher (with PDO MySQL extension enabled)
- MySQL 8.0+ or MariaDB 10.5+
- A local web server (Apache via XAMPP/Laragon recommended)
- A modern browser (Chrome, Firefox, Edge, Safari)
- Optional: Git for version control

---

## 🚀 Installation

### 1. Clone the repository

```bash
git clone https://github.com/wenjiechan/hatsuboshi-academy-system.git
cd gakumas-sms
```

### 2. Move into your web server's document root

For XAMPP, drop the folder into `htdocs/`. For Laragon, into `www/`.

### 3. Create the database

Open phpMyAdmin (or your MySQL client of choice) and run:

```sql
CREATE DATABASE gakumas-sms
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
```

### 4. Import the schema and seed data

```bash
mysql -u root -p gakumas-sms < database/schema.sql
mysql -u root -p gakumas-sms < database/sample_data.sql
```

The sample data populates **13 idols**, a few sample teachers, one producer, and example schedule entries.

### 5. Configure the database connection (if needed)

The default `config/database.php` is set up for a standard XAMPP/Laragon install:

```php
<?php
return [
    'host'     => 'localhost',
    'dbname'   => 'gakumas-sms',
    'username' => 'root',
    'password' => '',
    'charset'  => 'utf8mb4',
];
```

If your MySQL uses different credentials, edit `config/database.php` to match. Otherwise, no changes are needed.

### 6. Set folder permissions

Make sure `uploads/profiles/` is writable by your web server (Linux/Mac only; not required on Windows XAMPP).

```bash
chmod -R 755 uploads/
```

### 7. Launch

Visit `http://localhost/gakumas-sms/` and log in with one of the [demo accounts](#-demo-accounts).

---

## 📁 Project Structure

```
gakumas-sms/
│
├── config/
│   └── database.php
│
├── includes/
│   ├── auth.php
│   ├── header.php
│   ├── footer.php
│   ├── sidebar.php
│   └── message_helpers.php
│
├── student/
│   ├── dashboard.php
│   ├── profile.php
│   ├── schedule.php
│   ├── stats.php
│   ├── lessons.php
│   └── settings.php
│
├── teacher/
│   ├── dashboard.php
│   ├── students.php
│   ├── schedules.php
│   └── lessons.php
│
├── admin/
│   ├── dashboard.php
│   ├── students.php
│   ├── student_add.php
│   ├── student_edit.php
│   ├── student_view.php
│   ├── student_delete.php
│   ├── schedules.php
│   ├── events.php
│   ├── lessons.php
│   └── reports.php
│
├── messages/                   ← Shared messaging system
│   ├── inbox.php
│   ├── sent.php
│   ├── view.php
│   ├── compose.php
│   ├── send.php
│   └── delete.php
│
├── api/
│   └── unread_count.php
│
├── assets/
│   ├── css/
│   ├── js/
│   └── images/
│       └── avatars/
│
├── uploads/                    ← User-uploaded files (contents gitignored)
│   └── profiles/
│       └── .gitkeep
│
├── database/
│   ├── schema.sql
│   └── sample_data.sql
│
├── screenshots/                ← Add when project is ready to showcase
│
├── .gitignore
├── index.php                   (landing)
├── login.php
├── logout.php
├── LICENSE
└── README.md
```

---

## 👥 User Roles

| Role         | Permissions                                                                                       |
|--------------|---------------------------------------------------------------------------------------------------|
| **Producer** | Full access. CRUD on all students, schedules, lessons, events. Message anyone. View reports.     |
| **Teacher**  | Manage assigned students. Create lessons in their specialty (Vocal/Dance/Visual). Update stats. |
| **Student**  | View own profile & schedule. Edit bio/hobbies. Send & receive messages. Cannot edit others.     |

> ⚠️ **Always check user role on every protected page.** Don't rely on UI hiding — back it up with server-side checks.

---

## 🌟 The 13 Idols (2026 Roster)

The original 9 idols who launched the academy in 2024, plus 4 additions through 2025.

### Original 9 (Launch — May 2024)

| # | Name (English) | Japanese | Theme Color | Personality (one line) |
|---|----------------|----------|-------------|------------------------|
| 1 | Saki Hanami    | 花海咲季   | Red/Pink    | Hyper-competitive athlete, top entrance-exam scorer |
| 2 | Temari Tsukimura | 月村手毬 | Purple/Navy | Former middle-school idol, cool exterior / secretly lazy |
| 3 | Kotone Fujita  | 藤田ことね | Yellow/Gold | Money-loving, fan-praise-driven |
| 4 | Lilja Katsuragi | 葛城リーリヤ | Light Blue  | Swedish exchange student, shy but iron-willed |
| 5 | Sumika Shiun   | 紫雲清夏   | Green       | Cheerful ex-ballerina hiding a knee injury |
| 6 | Hiro Shinosawa | 篠澤広     | Dark Red    | Eccentric genius, already a university graduate |
| 7 | Mao Arimura    | 有村麻央   | Royal Purple | Princely 3rd-year, idol dorm leader |
| 8 | China Kuramoto | 倉本千奈   | Pink        | Sheltered heiress, genuinely earnest |
| 9 | Rinami Himesaki | 姫崎莉波 | Peach/Cream | 3rd-year student council secretary, sisterly type |

### New Additions (2024–2025)

| # | Name (English) | Japanese | Theme Color | Implemented | Personality (one line) |
|---|----------------|----------|-------------|-------------|------------------------|
| 10 | Ume Hanami    | 花海佑芽   | Coral Orange | Nov 2024 | Saki's younger sister; energetic athlete, looks up to her sister as both idol and rival |
| 11 | Sena Juo      | 十王星南   | Star Gold | 2025     | Academy No. 1 idol, the genuine "star" everyone is chasing |
| 12 | Misuzu Hataya | 秦谷美鈴   | Moonlit Blue | May 2025 | Outwardly serene, inwardly arrogant; former *SyngUp!* member alongside Temari |
| 13 | Tsubame Amaya | 雨夜燕    | Swallow Navy | Nov 2025 | Student council vice president; Academy No. 2; childhood friend & self-declared rival of Sena |

> 📚 For authentic character dialogue, personality logic, and quotes, **always check the latest Japanese sources** (Pixiv百科事典, ニコニコ大百科, note.com, Game8, AppMedia, and the official site at https://gakuen.idolmaster-official.jp/). The game continues to receive updates, and older English wiki summaries may be out of date.

---

## 🗄 Database Schema (Overview)

Core tables (see `database/schema.sql` for full DDL):

- **`users`** — authentication for all roles (producer, teacher, student). Username is unique; passwords stored as bcrypt hashes via `password_hash()`.
- **`teachers`** — teacher profile linked 1-to-1 to a user, with specialty (vocal/dance/visual).
- **`students`** — idol profile: bilingual name (English + Japanese), birthday, blood type, height, hometown, school year, rank, vocal/dance/visual stats, bio, theme color, and assigned producer.
- **`schedules`** — calendar entries linked to a student: activity type, title, date, start/end time, location, status (scheduled/completed/cancelled).
- **`lessons`** — lesson catalog: type (vocal/dance/visual), stat gain, description.
- **`songs`** — catalog of songs in the academy's repertoire: title, artist, duration, release date, notes, lyrics URL. Songs are shared resources — the same song can be linked to one student (solo) or many students (units/duets).
- **`student_songs`** — many-to-many join between students and songs. Students can add songs to their own list; producers and teachers can add songs to any student's list. A `UNIQUE(student_id, song_id)` constraint prevents duplicate entries.
- **`stat_history`** — append-only log of every stat change (old value, new value, reason, timestamp).
- **`events`** — auditions, concerts, and photoshoots with stat requirements per discipline.
- **`event_participants`** — many-to-many join between events and students, with a `result` field (1st/2nd/3rd/etc.). A student can only be entered into the same event once.
- **`messages`** — user-to-user messages with read tracking, soft delete per side, and threaded replies via `parent_message_id`.
- **`notifications`** — in-app notification feed with type, title, body, and read state.
- **`producer_messages`** — pre-written personality-based message pool, indexed by student + message type for fast random selection.

### Referential integrity

The schema uses foreign keys throughout with carefully chosen `ON DELETE` rules:

- **`CASCADE`** where the child record can't exist without its parent — e.g., deleting a `user` drops their `student`/`teacher` profile, deleting a `student` drops their `schedules` and `event_participants`.
- **`SET NULL`** for history-preserving tables — `stat_history`, `messages.sender_id`/`receiver_id`, `schedules.created_by`, and `students.producer_id` keep their rows even after the referenced user is deleted, so historical records aren't lost.

### Indexes

In addition to primary and unique keys, common query patterns have dedicated indexes:

- `messages (receiver_id, is_read, is_deleted_by_receiver)` — fast unread inbox counts
- `schedules (student_id, date)` — fast calendar lookups per student
- `notifications (user_id, is_read)` — fast unread badge counts
- `producer_messages (student_id, message_type)` — fast random message selection

---

## 🎨 Theming

The system uses CSS custom properties so themes can be swapped at runtime by adding a class to `<body>`:

```css
/* Original 9 */
.theme-saki    { --primary: #FF5485; --secondary: #FFCCD9; }
.theme-temari  { --primary: #7B6DAA; --secondary: #D6CFE8; }
.theme-kotone  { --primary: #FFD454; --secondary: #FFF1B8; }
.theme-lilja   { --primary: #B8D4F0; --secondary: #E5F0F9; }
.theme-sumika  { --primary: #7DD87D; --secondary: #C5EFC5; }
.theme-hiro    { --primary: #8B3A4A; --secondary: #D8B8C0; }
.theme-mao     { --primary: #A594C5; --secondary: #DCD3E8; }
.theme-china   { --primary: #F5B6C5; --secondary: #FAD9E0; }
.theme-rinami  { --primary: #F5D0C5; --secondary: #FCE8E0; }

/* New 4 (2024–2025) */
.theme-ume     { --primary: #FF8C42; --secondary: #FFD4B8; }
.theme-sena    { --primary: #FFE066; --secondary: #FFF4B8; }
.theme-misuzu  { --primary: #5B8BC4; --secondary: #C5D8EC; }
.theme-tsubame { --primary: #2E4A6B; --secondary: #B8C5D6; }
```

On login, the active user's theme class is added to `<body>` and every component re-themes automatically.

### Stat colors (consistent across all themes)

- Vocal → Pink (`#FF6B9D`)
- Dance → Blue (`#6BB6FF`)
- Visual → Yellow (`#FFD454`)

---

## 🔒 Security

This project takes security seriously — graders look for it, and habits formed here carry into real-world work.

- ✅ Passwords hashed with `password_hash()` / verified with `password_verify()`
- ✅ All queries use **PDO prepared statements** with parameter binding (no string concatenation)
- ✅ `session_regenerate_id(true)` on login to prevent session fixation
- ✅ Session cookies set with `httponly`, `samesite=Lax` (and `secure` in production)
- ✅ All output escaped via `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`
- ✅ CSRF tokens on every state-changing POST request
- ✅ Server-side input validation with `filter_var()` and whitelists
- ✅ File upload checks: extension whitelist, MIME type verification, size limit, randomized filenames
- ✅ Role + ownership verification on every protected resource (a student can never read another student's messages by guessing IDs)

---

## 🗺 Roadmap

### Phase 1 — MVP ✅
Authentication, RBAC, Student CRUD, Schedule CRUD, Dashboard, Profile, Stats, Messaging, Notifications

### Phase 2 — Enhancements 🚧
Auditions, rank promotion, charts/analytics, personality-based messages, theme switching

### Phase 3 — Optional 💭
Unit management, memory/diary, achievements, advanced reports, real-time chat, PWA, dark mode

---

## 📚 Credits & References

### Inspiration
- **Gakuen Idolm@ster (学園アイドルマスター)** — © Bandai Namco Entertainment Inc.
- This is a non-commercial **student/educational project**. All character names, themes, and visual references belong to their respective rights holders.

### Research Sources

**Official**
- Official site — https://gakuen.idolmaster-official.jp/
- Official music label — https://gakuen-label.idolmaster-official.jp/
- Official X (Twitter) — `#学マス` `#GakuenIdolmaster`

**Japanese fan resources (highest quality)**
- Pixiv百科事典 — dic.pixiv.net
- ニコニコ大百科
- note.com (long-form fan analysis)
- Game8 — https://game8.jp/gakuen-idolmaster
- AppMedia, Gamer.jp, 学マスwiki (seesaawiki)

**English resources**
- Project iM@S Wiki — https://project-imas.wiki
- Fandom Wiki — https://gakuen-idolmaster.fandom.com
- r/IdolMaster on Reddit

### Useful Japanese search queries
- `学マス [name] 性格` — personality
- `学マス [name] 口調` — speech style
- `学マス [name] 名言` — famous quotes
- `学マス [name] 考察` — analysis
- `学マス [name] エピソード` — episodes

> 💡 Use [DeepL](https://www.deepl.com/) for translating Japanese sources — it handles Japanese far more naturally than Google Translate.

---

## 📄 License

This project is released for **educational purposes only**. The Gakumas theme, character names, and related IP belong to **Bandai Namco Entertainment Inc.** and the rightful copyright holders. No commercial use, redistribution of character assets, or claim of ownership over the source material is intended.

Original source code in this repository is released under the [MIT License](LICENSE), but **the Gakumas-themed content, art, and character data are not covered by this license** and must not be used commercially.

---

<p align="center">
  <strong>★ Hatsuboshi Gakuen wishes you success! ★</strong><br>
  <em>A polished, finished project always stands out more than an over-ambitious unfinished one.</em>
</p>