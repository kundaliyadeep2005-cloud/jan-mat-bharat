<div align="center">

# 🗳️ जन-मत भारत — Jan-Mat Bharat

### *Your Vote • Your Right • Your Future*

> "मतदान राष्ट्र सेवा का पहला कदम है"

A **secure, biometric-powered online voting platform** built for India —  
featuring Face ID authentication, real-time results, and a full admin control center.

---

![PHP](https://img.shields.io/badge/PHP-8.3-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.4-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-ES6-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black)
![face-api.js](https://img.shields.io/badge/face--api.js-Biometric-FF6B6B?style=for-the-badge)
![License](https://img.shields.io/badge/License-Academic-green?style=for-the-badge)

</div>

---

## 📌 Table of Contents

- [About the Project](#-about-the-project)
- [Key Features](#-key-features)
- [Tech Stack](#-tech-stack)
- [Biometric System Deep Dive](#-biometric-system-deep-dive)
- [Project Structure](#-project-structure)
- [Database Schema](#-database-schema)
- [Installation & Setup](#-installation--setup)
- [How It Works — User Flow](#-how-it-works--user-flow)
- [Admin Panel](#-admin-panel)
- [Security Implementations](#-security-implementations)
- [Screenshots](#-screenshots)
- [Known Limitations](#-known-limitations)
- [Developer](#-developer)

---

## 📖 About the Project

**Jan-Mat Bharat (जन-मत भारत)** is a full-stack web application that simulates a secure, digital election system inspired by Indian democracy. The platform allows citizens to register, enroll their face biometrically, and cast a verified vote — all from a browser.

The unique feature of this project is its **two-factor biometric security layer**:
1. Face ID can be used to **log in** without a password.
2. Before casting a vote, the user must **pass a face scan gate** to prove they are physically present — preventing proxy voting.

This project was developed as an academic project at **RK University, Rajkot** — combining web development, AI/ML integration, and democratic values.

---

## ✨ Key Features

### 👤 Voter Side
- **Voter Registration** with mandatory face enrollment (biometric)
- **Voter ID Validation** — enforces real format: 3 capital letters + 7 digits (e.g., `ABC1234567`)
- **Age Verification** — blocks registration for users under 18
- **Dual Login System** — standard email/password OR Face ID (passwordless)
- **Biometric Face Gate** — face must be verified again before the Submit Vote button is unlocked
- **One Person, One Vote** — database-level enforcement with row-locking transactions
- **Real-time Results** — live vote counts, percentages, and state-wise turnout
- **Profile Management** — update name, photo, state, and password
- **NOTA Support** — "None of the Above" option included
- **Forgot Password** — secure reset via tokenized email link

### 🛡️ Admin Side
- **Dashboard** — total users, total votes, unread support messages
- **User Management** — view, block/activate, delete voters
- **Vote Analytics** — party-wise vote counts with progress bars and activity feed
- **Page Manager** — edit Home, About, Contact page content via UI
- **Settings Panel** — update site name, tagline, contact info, social links
- **Support Inbox** — read and respond to citizen messages

---

## 🛠️ Tech Stack

| Layer | Technology |
|---|---|
| **Backend** | PHP 8.3 |
| **Database** | MySQL 8.4 (via PDO) |
| **Frontend** | Bootstrap 5.3, custom CSS |
| **Biometrics** | face-api.js (TensorFlow.js based) |
| **ML Models** | SSD MobileNetv1, Face Landmark 68-point, Face Recognition Net |
| **Email** | PHPMailer (SMTP) |
| **Server** | Apache with `.htaccess` mod_rewrite |
| **Local Dev** | Laragon (Windows) |
| **Hosting** | Shared PHP hosting (hstn.me) |

---

## 🧠 Biometric System Deep Dive

This is the most technically complex part of the project. Here is exactly how it works:

### Models Used (loaded from `/models/`)

| Model | Purpose |
|---|---|
| `ssd_mobilenetv1` | Detects if a face is present in the video frame |
| `face_landmark_68` | Maps 68 key points on the face (eyes, nose, jaw, etc.) |
| `face_recognition` | Produces a 128-dimensional numerical "fingerprint" of the face |

### Registration (Enrollment)
1. User opens their webcam on the registration page.
2. `face_register.js` loads all three models from `/models/`.
3. `faceapi.detectSingleFace(video).withFaceLandmarks().withFaceDescriptor()` captures a **128-number float array** (the face descriptor).
4. This array is sent as a JSON string and stored in the `face_descriptor` column of the `users` table in MySQL.

### Login via Face ID
1. User clicks "Face Login" on the login page.
2. `face_login.js` captures a live face descriptor from the webcam.
3. The descriptor is sent via `fetch()` (AJAX POST) to `/php/face_login.php`.
4. The PHP file fetches every active user's stored descriptor from the DB.
5. For each user, it computes the **Euclidean distance** between the live descriptor and stored descriptor:
   ```
   distance = sqrt( Σ (live[i] - stored[i])² )
   ```
6. If the best match has a distance **< 0.55**, the user is authenticated and the PHP session is started.
7. If no face matches within the threshold, access is denied.

### Voting Face Gate
- When a voter selects a party, the Submit button is **disabled and locked**.
- A face scan section appears (`face_vote.js`).
- The voter must scan their face again. The same Euclidean distance check is run via `/php/face_verify_vote.php`.
- Only on success does the Submit button unlock (`opacity: 1`, `cursor: pointer`).
- This prevents someone from using another person's open session to cast a vote.

---

## 📁 Project Structure

```
jan-mat-bharat/
│
├── index.php                    # Entry point — redirects to php/index.php
├── .htaccess                    # Apache URL routing and MIME type config
│
├── php/                         # All user-facing pages
│   ├── index.php                # Home page (hero, parties, why vote)
│   ├── login.php                # Email/password login
│   ├── register.php             # Voter registration with face enrollment
│   ├── vote.php                 # Voting page with face gate
│   ├── results.php              # Real-time election results
│   ├── edit_profile.php         # Profile management
│   ├── about.php                # About page (CMS-driven)
│   ├── contact.php              # Contact/support form
│   ├── forgot_password.php      # Password reset request
│   ├── reset_password.php       # Password reset handler
│   ├── verify_otp.php           # OTP verification
│   ├── face_login.php           # AJAX endpoint: biometric login
│   ├── face_verify_vote.php     # AJAX endpoint: biometric vote gate
│   ├── logout.php               # Session destroy
│   └── help.php                 # Help page
│
├── admin/                       # Admin panel (protected)
│   ├── index.php                # Dashboard with stats
│   ├── login.php                # Admin login
│   ├── logout.php               # Admin session destroy
│   ├── users.php                # Manage voters
│   ├── votes.php                # Vote analytics
│   ├── manage_pages.php         # CMS for Home/About/Contact
│   ├── settings.php             # Site-wide settings
│   └── support.php              # Support message inbox
│
├── includes/                    # Shared PHP components
│   ├── db_connect.php           # PDO connection, BASE_URL, auto-migration
│   ├── header.php               # Navbar, session, Bootstrap import
│   ├── footer.php               # Footer with social links
│   ├── admin_auth.php           # Admin session guard
│   ├── admin_header.php         # Admin top bar
│   ├── admin_sidebar.php        # Admin navigation sidebar
│   ├── email_functions.php      # Email sending helpers
│   ├── mail_config.php          # SMTP configuration
│   ├── states.php               # Indian states list
│   └── PHPMailer/               # PHPMailer library
│       ├── PHPMailer.php
│       ├── SMTP.php
│       └── Exception.php
│
├── js/                          # Frontend JavaScript
│   ├── face-api.min.js          # face-api.js library (~664 KB)
│   ├── face_register.js         # Face enrollment on registration
│   ├── face_login.js            # Face-based login flow
│   ├── face_vote.js             # Face gate on voting page
│   ├── vote.js                  # Party selection UI logic
│   ├── login.js                 # Login form validation
│   ├── register.js              # Registration form validation
│   └── edit_profile.js          # Profile edit logic
│
├── css/                         # Stylesheets
│   ├── style.css                # Home page
│   ├── login.css                # Login page
│   ├── register.css             # Register page
│   ├── vote.css                 # Voting page
│   ├── results.css              # Results page
│   ├── admin.css                # Full admin panel
│   ├── admin_login.css          # Admin login
│   ├── navbar.css               # Navigation bar
│   ├── footer.css               # Footer
│   ├── about.css                # About page
│   ├── contact.css              # Contact page
│   └── edit_profile.css         # Profile edit page
│
├── models/                      # Face-API ML model weights
│   ├── ssd_mobilenetv1_model-*  # Face detector
│   ├── face_landmark_68_model-* # Landmark mapper
│   └── face_recognition_model-* # 128-D descriptor generator
│
├── images/                      # Party logos and profile photos
│   ├── bjp.png
│   ├── ins.webp
│   ├── aap.webp
│   ├── comunist.png
│   ├── trinamool.jpg
│   ├── bsp.webp
│   ├── nota.png
│   ├── ashoka-chakra.png
│   └── profiles/                # Uploaded voter profile photos
│
├── sql/
│   └── jan_mat_bharat.sql       # Full database schema + seed data
│
├── assets/                      # One-time utility scripts
│   ├── setup_db.php             # Initial database setup
│   ├── reset_admin.php          # Reset admin credentials
│   └── add_photo_column.php     # DB migration helper
│
└── logs/                        # Application logs directory
```

---

## 🗄️ Database Schema

The database has **6 core tables**:

| Table | Purpose |
|---|---|
| `users` | All voters and admins — stores credentials, face descriptor, voter ID |
| `votes` | Every cast vote (anonymous — stores party name and user_id, but vote is not publicly linked) |
| `parties` | Political parties with name, description, and logo path |
| `pages` | CMS content for Home, About, Contact pages (stored as JSON/HTML) |
| `settings` | Site-wide key-value config (site name, contact, social links) |
| `support_messages` | Contact form submissions from citizens |

### Key columns in `users` table:
```sql
id              INT AUTO_INCREMENT PRIMARY KEY
name            VARCHAR(100)
email           VARCHAR(150) UNIQUE
voter_id        VARCHAR(20) UNIQUE        -- Format: ABC1234567
dob             DATE
state           VARCHAR(100)
password        VARCHAR(255)              -- bcrypt hashed
face_descriptor TEXT                      -- 128-float JSON array
profile_photo   VARCHAR(255)
has_voted       TINYINT(1) DEFAULT 0      -- Prevents double voting
role            ENUM('user', 'admin')
status          ENUM('active', 'blocked')
reset_token     VARCHAR(64)               -- For password reset
reset_token_expiry DATETIME
created_at      TIMESTAMP
```

---

## ⚙️ Installation & Setup

### Prerequisites
- PHP 8.0 or higher
- MySQL 8.0 or higher
- Apache server with `mod_rewrite` enabled
- **Recommended for local dev:** [Laragon](https://laragon.org/) on Windows

---

### Step 1 — Clone the Repository
```bash
git clone https://github.com/YOUR_USERNAME/jan-mat-bharat.git
cd jan-mat-bharat
```

### Step 2 — Set Up the Database

1. Open **phpMyAdmin** (or MySQL CLI).
2. Create a new database:
   ```sql
   CREATE DATABASE jan_mat_bharat CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```
3. Import the schema:
   ```bash
   mysql -u root -p jan_mat_bharat < sql/jan_mat_bharat.sql
   ```

### Step 3 — Configure Database Connection

Open `includes/db_connect.php` and update your credentials:

```php
$host   = 'localhost';          // Your DB host
$user   = 'root';               // Your MySQL username
$pass   = '';                   // Your MySQL password
$dbname = 'jan_mat_bharat';     // Your database name
```

> ⚠️ **Security Note:** For production, move credentials to a `.env` file or server environment variables. Never commit real credentials to GitHub.

### Step 4 — Configure Email (Optional)

Open `includes/mail_config.php` and add your SMTP details (Gmail, Mailtrap, etc.) if you want password reset emails to work.

### Step 5 — Configure Apache

For **Laragon**, place the project folder inside `C:/laragon/www/`.

Make sure `mod_rewrite` is enabled. The `.htaccess` file handles all URL routing automatically.

### Step 6 — Set Up Admin Account

Navigate to: `http://localhost/jan-mat-bharat/assets/setup_db.php`

This creates the default admin account. Then delete or protect this file after setup.

Or visit `assets/reset_admin.php` to reset admin credentials manually.

### Step 7 — Access the Application

| URL | Description |
|---|---|
| `http://localhost/jan-mat-bharat/` | Home page |
| `http://localhost/jan-mat-bharat/php/login.php` | Voter login |
| `http://localhost/jan-mat-bharat/php/register.php` | Voter registration |
| `http://localhost/jan-mat-bharat/admin/login.php` | Admin login |

> **Note:** The ML models in `/models/` are large files (~6.5 MB total). Make sure they are present for face detection to work. Your `.htaccess` is already configured to serve `.bin` and `.json` model files with correct MIME types.

---

## 🔄 How It Works — User Flow

```
[New Voter]
     │
     ▼
Register → Fill: Name, Email, DOB, State, Voter ID, Password
     │
     ├─► Validate: Voter ID format (ABC1234567), Age (18+), No duplicates
     │
     ▼
Face Enrollment → Webcam opens → face-api.js captures 128-D descriptor
     │
     ▼
Stored in DB (users.face_descriptor)
     │
     ▼
[Login Page]
     │
     ├── Option A: Email + Password → Session started
     │
     └── Option B: Face ID → Webcam → Euclidean distance match → Session started
              │
              └── Threshold: distance < 0.55 required
     │
     ▼
[Voting Page]
     │
     ▼
Select Party (BJP / INC / AAP / CPI / TMC / BSP / NOTA)
     │
     ▼
Face Gate Appears → Webcam → Verify identity again
     │
     ├── Match confirmed → Submit button UNLOCKS
     │
     └── No match → Denied, cannot vote
     │
     ▼
Vote submitted inside MySQL Transaction with FOR UPDATE row lock
     │
     ├── users.has_voted = 1 (prevents second vote)
     └── INSERT into votes table (party + user_id)
     │
     ▼
[Results Page] — Live aggregated counts, percentages, state-wise turnout
```

---

## 🖥️ Admin Panel

The admin panel (`/admin/`) is protected by session-based auth (`admin_auth.php`). All pages require `role = 'admin'`.

| Page | URL | Feature |
|---|---|---|
| Dashboard | `/admin/index.php` | Stats: registered voters, total votes, unread messages |
| Users | `/admin/users.php` | View all voters, block/activate, delete accounts |
| Votes | `/admin/votes.php` | Party-wise vote count with progress bars + recent activity |
| Manage Pages | `/admin/manage_pages.php` | Edit Home/About/Contact content without touching code |
| Settings | `/admin/settings.php` | Site name, tagline, contact details, social media URLs |
| Support | `/admin/support.php` | Read citizen support messages |

---

## 🔐 Security Implementations

| Feature | Implementation |
|---|---|
| **Password Hashing** | `password_hash()` with `PASSWORD_DEFAULT` (bcrypt) |
| **Session Fixation Prevention** | `session_regenerate_id(true)` on every login |
| **SQL Injection Prevention** | PDO prepared statements used throughout |
| **Double Vote Prevention** | `FOR UPDATE` row lock inside a MySQL transaction |
| **Input Sanitization** | `htmlspecialchars()`, `filter_var()`, `trim()` on all inputs |
| **Voter ID Format Enforcement** | Regex: `/^[A-Z]{3}[0-9]{7}$/` |
| **Age Enforcement** | PHP `DateTime::diff()` check — must be 18+ |
| **Admin Route Protection** | `admin_auth.php` checks session role on every admin page |
| **Biometric Threshold** | Euclidean distance < 0.55 for face match |
| **MIME Type Security** | `X-Content-Type-Options: nosniff` header |
| **Clickjacking Protection** | `X-Frame-Options: SAMEORIGIN` header |
| **Password Reset** | Time-limited secure token via email link |

---

## ⚠️ Known Limitations

- **Database credentials** are currently hardcoded in `db_connect.php`. For production, these must be moved to environment variables.
- **Hardcoded file paths** in some SQL seed data reference the local environment setup.
- **CSRF protection** is not yet implemented on forms — a priority improvement for production.
- **Face recognition accuracy** depends on lighting conditions and camera quality. Not suitable for high-security environments without additional validation.
- **Mobile webcam** behavior is browser-dependent — testing on real devices is recommended.
- The project is designed for **educational/demo purposes** and is not intended for actual government elections.

---

## 👨‍💻 Developer

**Deep Kundaliya**  
Student — RK University, Rajkot, Gujarat 🇮🇳

> *"This project combines my passion for web development, AI integration, and civic responsibility. Every feature was built with the vision of making democracy digital, accessible, and secure."*

---

## 📄 License

This project was created for academic purposes at **RK University**.  
Feel free to fork, learn from, and build upon it.  
Please give credit if you use it as a reference. 🙏

---

<div align="center">

**जय हिंद 🇮🇳 | Jai Hind**

*Made with ❤️ for Indian Democracy*

</div>
