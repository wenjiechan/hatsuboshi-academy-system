<a id="readme-top"></a>

<!-- PROJECT HEADER -->
<div align="center">

  <h1 align="center">★ Hatsuboshi Gakuen ★</h1>
  <h3 align="center">Student Schedule Management System</h3>
  <p align="center"><i>初星学園 — where every idol's first star begins to shine.</i></p>

  <p align="center">
    A web-based platform for running an idol academy — role-based workflows,
    threaded messaging, daily stat tracking, and per-idol theming.
    <br />
    Inspired by <b>Gakuen Idolm@ster (学園アイドルマスター)</b>.
    <br />
    <br />
    <a href="https://github.com/wenjiechan/hatsuboshi-academy-system/issues">Report Bug</a>
    ✦
    <a href="https://github.com/wenjiechan/hatsuboshi-academy-system/issues">Request Feature</a>
  </p>
</div>

> ✨ *Welcome to Hatsuboshi Academy, Producer-san.*
> *Thirteen idol candidates await your guidance. Train them in Vocal, Dance, and Visual,*
> *coordinate their schedules, and help them shine.*

---

<!-- TABLE OF CONTENTS -->
<details>
  <summary>★ Table of Contents</summary>

- [About The Academy](#about-the-academy)
  - [Built With](#built-with)
- [Getting Started](#getting-started)
  - [Prerequisites](#prerequisites)
  - [Installation](#installation)
  - [Background Jobs](#background-jobs)
- [Usage](#usage)
  - [Demo Accounts](#demo-accounts)
- [The 13 Idols](#the-13-idols)
- [Features](#features)
- [Academy Roles](#academy-roles)
- [Project Structure](#project-structure)
- [Security](#security)
- [License](#license)
- [Acknowledgments](#acknowledgments)

</details>

<!-- ABOUT THE ACADEMY -->
## About The Academy

Hatsuboshi Gakuen is a full-stack PHP/MySQL application that models the day-to-day
operations of an idol academy. Producers and teachers coordinate the training of
student idols across the three pillars — **Vocal**, **Dance**, and **Visual**.
Students manage their own profiles, build their song repertoire, and chat
directly with the staff assigned to them.

The Gakumas theme is a creative wrapper. Underneath is a security-conscious CRUD
system built around four user roles, prepared-statement data access,
CSRF-protected state changes, and a typed notification feed. The same architecture
would suit any school, training program, or talent agency.

<p align="right">(<a href="#readme-top">★ back to top</a>)</p>

### Built With

- [PHP 8.0+](https://www.php.net/)
- [MySQL 8 / MariaDB 10.5+](https://mariadb.org/)
- [PDO](https://www.php.net/manual/en/book.pdo.php) (prepared statements)
- [Bootstrap 5](https://getbootstrap.com/)
- [Chart.js](https://www.chartjs.org/)
- [FullCalendar.js](https://fullcalendar.io/)
- [Bootstrap Icons](https://icons.getbootstrap.com/)

<p align="right">(<a href="#readme-top">★ back to top</a>)</p>

<!-- GETTING STARTED -->
## Getting Started

Set up your own branch of the academy in a few steps.

### Prerequisites

- **PHP 8.0+** with the `pdo_mysql` extension enabled
- **MySQL 8.0+** or **MariaDB 10.5+**
- A local web server — Apache (XAMPP, Laragon, or MAMP)
- A modern browser (Chrome, Firefox, Edge, Safari)
- Git for version control

### Installation

The repository is named `hatsuboshi-academy-system`, but the project folder and
database keep the shorter `gakumas-sms` naming used throughout the codebase.

1. Clone the repository into your web server's document root (`htdocs/` or `www/`):
   ```bash
   git clone https://github.com/wenjiechan/hatsuboshi-academy-system.git gakumas-sms
   cd gakumas-sms
   ```
2. Create the database:
   ```sql
   CREATE DATABASE gakumas_sms
     CHARACTER SET utf8mb4
     COLLATE utf8mb4_unicode_ci;
   ```
3. Import the schema and seed data:
   ```bash
   mysql -u root -p gakumas_sms < database/schema.sql
   mysql -u root -p gakumas_sms < database/sample_data.sql
   ```
4. (Linux/Mac only) make the uploads folder writable:
   ```bash
   chmod -R 755 uploads/
   ```
5. Edit `config/database.php` if your MySQL uses non-default credentials.
6. Visit `http://localhost/gakumas-sms/` and step onto the stage ★

### Background Jobs

The academy never sleeps. Schedule the two cron scripts with cron (Linux/Mac)
or Task Scheduler (Windows):

```bash
# Nightly stat snapshot for charting
0 0 * * * php /path/to/gakumas-sms/scripts/daily_stat_snapshot.php

# Schedule-start alerts (deduped per day)
* * * * * php /path/to/gakumas-sms/scripts/generate_notifications.php
```

<p align="right">(<a href="#readme-top">★ back to top</a>)</p>

<!-- USAGE -->
## Usage

After installation, sign in with any of the seeded accounts to walk the halls of
Hatsuboshi. The Producer and Admin roles have the broadest access; Students have
the narrowest. Try the messaging flow, the producer add/remove request, and the
song catalog to see most of the project's surface area.

### Demo Accounts

Passwords follow a simple rule, all lowercase, no spaces:

- 🎤 **Students** → first name + birthday in `MMDD` format (e.g. `saki0402`)
- 🎬 **Producer** → `producer001`
- 🛡 **Admin** → `admin001`
- 🎓 **Teachers** → discipline + `001` → `vocal001` · `dance001` · `visual001`

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

> ✨ Usernames contain spaces — type them exactly as shown.

<p align="right">(<a href="#readme-top">★ back to top</a>)</p>

<!-- THE 13 IDOLS -->
## The 13 Idols

Each idol carries her own theme color, applied across the entire UI at runtime
through CSS variables.

| # | Idol               | 日本語       | Theme Color                                                                 |
| - | ------------------ | ------------ | --------------------------------------------------------------------------- |
| 1 | Saki Hanami        | 花海 咲季    | ![#E30F25](https://placehold.co/12x12/E30F25/E30F25.png) `#E30F25` Red       |
| 2 | Temari Tsukimura   | 月村 手毬    | ![#0C7BBB](https://placehold.co/12x12/0C7BBB/0C7BBB.png) `#0C7BBB` Blue      |
| 3 | Kotone Fujita      | 藤田 ことね  | ![#F8C112](https://placehold.co/12x12/F8C112/F8C112.png) `#F8C112` Gold      |
| 4 | Lilja Katsuragi    | 葛城 リーリヤ| ![#7DC4D4](https://placehold.co/12x12/7DC4D4/7DC4D4.png) `#7DC4D4` Sky       |
| 5 | Sumika Shiun       | 紫雲 清夏    | ![#7CFC00](https://placehold.co/12x12/7CFC00/7CFC00.png) `#7CFC00` Green     |
| 6 | Hiro Shinosawa     | 篠澤 広      | ![#00AFCC](https://placehold.co/12x12/00AFCC/00AFCC.png) `#00AFCC` Teal      |
| 7 | China Kuramoto     | 倉本 千奈    | ![#F68B1F](https://placehold.co/12x12/F68B1F/F68B1F.png) `#F68B1F` Orange    |
| 8 | Ume Hanami         | 花海 佑芽    | ![#EA533A](https://placehold.co/12x12/EA533A/EA533A.png) `#EA533A` Coral     |
| 9 | Misuzu Hataya      | 秦谷 美鈴    | ![#7A99CF](https://placehold.co/12x12/7A99CF/7A99CF.png) `#7A99CF` Slate     |
| 10| Mao Arimura        | 有村 麻央    | ![#7F1184](https://placehold.co/12x12/7F1184/7F1184.png) `#7F1184` Purple    |
| 11| Rinami Himesaki    | 姫崎 莉波    | ![#F6ADC6](https://placehold.co/12x12/F6ADC6/F6ADC6.png) `#F6ADC6` Pink      |
| 12| Sena Juo           | 十王 星南    | ![#F6AE54](https://placehold.co/12x12/F6AE54/F6AE54.png) `#F6AE54` Amber     |
| 13| Tsubame Amaya      | 雨夜 燕      | ![#7B68EE](https://placehold.co/12x12/7B68EE/7B68EE.png) `#7B68EE` Violet    |

<p align="right">(<a href="#readme-top">★ back to top</a>)</p>

<!-- FEATURES -->
## Features

### 🎭 Identity & Access

- **Four-role authentication** — Admin, Producer, Teacher, Student
- **Role and ownership** enforced server-side on every protected route
- **Hardened sessions** — bcrypt passwords, session regeneration on login, secure cookie flags
- **CSRF tokens** on every state-changing POST

### 🌟 Student Management

- **Rich profiles** — bilingual names, birthday, zodiac, blood type, height, weight, three-size, hometown, hobbies, special skill, school year, rank, bio
- **Safe avatar uploads** — extension whitelist, MIME verification, size limit, randomized filenames
- **Self-edit** for trusted fields, full control for Producers and Admin
- **Rank progression** from Debut through the academy ranks

### 📅 Schedules

- **One-off schedule entries** with date, time, location, and status
- **Recurring weekly templates** that auto-populate the calendar
- **FullCalendar.js** for weekly and monthly views
- **Schedule-start alerts** fired automatically before each session

### 📊 Stats & Progression

- **Vocal / Dance / Visual** tracking with current values and historical log
- **Daily stat snapshots** powering clean time-series charts via Chart.js
- **Append-only stat history** with reason and timestamp on every change

### 🎤 Lessons & Events

- **Lesson catalog** organized by discipline with stat gain values
- **Audition, concert, and photoshoot events** with stat requirements
- **Event participation tracking** with placement results

### 💬 Conversation-Based Messaging

- **Threaded chats** between any two users — no inbox/sent split
- **Per-user state** — last-read, archive, mute, soft-delete
- **Typed messages** — text, system, birthday, producer requests
- **Live polling** through `api/messages_poll.php` for near-real-time updates

### 🤝 Producer ↔ Student Workflow

- **Chat-integrated add/remove requests** with `pending → accepted · rejected · cancelled · expired` lifecycle
- **`producer_status`** updates atomically with each transition
- **Personality-tailored producer greetings** drawn from a per-student message pool

### 🎵 Song Catalog

- **Song types** — Solo · Group · Remix · Cover with English and Japanese titles
- **Many-to-many** student linking — a song can belong to one idol or many
- **Per-student repertoire** managed by the student, Producer, or Admin

### 🔔 Notifications

- **Typed feed** with action URLs, dedupe keys, and `responded_at` tracking
- **Live JS polling** + auto-generated schedule-start alerts via cron
- **In-app bell** with unread badge

### ✨ Personalization & Polish

- **Per-idol theming** — primary and secondary colors stored per user, applied via CSS variables
- **Birthday banner** on the student's dashboard
- **Dark mode** support
- **Click-sparkle** micro-interactions for an academy-appropriate feel

### ⏰ Background Jobs

- `scripts/daily_stat_snapshot.php` — snapshots stats for charting
- `scripts/generate_notifications.php` — fires deduped schedule alerts

<p align="right">(<a href="#readme-top">★ back to top</a>)</p>

<!-- ACADEMY ROLES -->
## Academy Roles

| Role         | Responsibilities                                                                         |
| ------------ | ---------------------------------------------------------------------------------------- |
| **Admin**    | System-level oversight. Manage all users, roles, and global academy settings.            |
| **Producer** | Manage assigned roster. CRUD on students, schedules, lessons, events, and songs.         |
| **Teacher**  | Train assigned students in their specialty (vocal, dance, or visual). Update stats.      |
| **Student**  | View own profile and schedule. Edit bio, hobbies, and avatar. Chat with staff.           |

Role and ownership are verified server-side on every protected resource. UI hiding
is treated as a UX convenience, never as the access boundary.

<p align="right">(<a href="#readme-top">★ back to top</a>)</p>

<!-- PROJECT STRUCTURE -->
## Project Structure

```
gakumas-sms/
├── admin/        Admin pages — global user, student, producer, and teacher management
├── producer/     Producer workspace — roster, schedules, events, lessons, reports
├── teacher/      Teacher workspace — assigned students, lessons
├── student/      Student workspace — dashboard, profile, schedule, stats, songs
├── messages/     Conversation-based chat — inbox, threads, compose, archive, mute
├── api/          JSON polling endpoints for live messaging and notifications
├── scripts/      Cron jobs — daily stat snapshots and notification generation
├── includes/     Auth, layout, helpers for messages, stats, and theming
├── assets/       CSS, JavaScript, and images
├── config/       Database configuration
├── database/     schema.sql · sample_data.sql · research notes
├── tests/        Integration tests
├── uploads/      User uploads (gitignored)
└── index.php · login.php · logout.php · notifications.php
```

<p align="right">(<a href="#readme-top">★ back to top</a>)</p>

<!-- SECURITY -->
## Security

End-to-end PHP security practices, applied consistently:

- ★ Passwords hashed with `password_hash()` (bcrypt), verified with `password_verify()`
- ★ All queries use **PDO prepared statements** with parameter binding
- ★ `session_regenerate_id(true)` on login to prevent session fixation
- ★ Session cookies set with `httponly` and `samesite=Lax` (plus `secure` in production)
- ★ All output escaped via `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`
- ★ **CSRF tokens** on every state-changing POST request
- ★ Server-side input validation with `filter_var()` and whitelist comparisons
- ★ File uploads validated by extension, MIME type, and size, with randomized filenames
- ★ Role *and* ownership verified on every protected resource — IDs cannot be guessed
- ★ Dedupe keys on messages and notifications prevent double-submits and duplicate alerts

<p align="right">(<a href="#readme-top">★ back to top</a>)</p>

<!-- LICENSE -->
## License

Distributed under the MIT License. See [`LICENSE`](LICENSE) for more information.

The Gakumas-themed content, character names, and visual references are **not**
covered by this license. They belong to **Bandai Namco Entertainment Inc.** and
must not be used commercially.

<p align="right">(<a href="#readme-top">★ back to top</a>)</p>

<!-- ACKNOWLEDGMENTS -->
## Acknowledgments

- [Gakuen Idolm@ster (学園アイドルマスター)](https://gakuen.idolmaster-official.jp/) — © Bandai Namco Entertainment Inc.
- [Pixiv百科事典](https://dic.pixiv.net/) and ニコニコ大百科 — character research
- [Game8 — 学園アイドルマスター攻略](https://game8.jp/gakuen-idolmaster)
- [Project iM@S Wiki](https://project-imas.wiki)
- [Best-README-Template](https://github.com/othneildrew/Best-README-Template) — README structure

<p align="right">(<a href="#readme-top">★ back to top</a>)</p>

---

<div align="center">
  <p>
    ★ <i>Hatsuboshi Gakuen wishes you success, Producer-san.</i> ★
    <br />
    <sub>初星学園 — 一番星になる。</sub>
  </p>
</div>