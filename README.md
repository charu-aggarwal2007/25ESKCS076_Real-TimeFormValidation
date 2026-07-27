# Student Registration — Real-Time Form Validation System

A full-stack project: an HTML/CSS/JavaScript registration form with real-time
(client + server) validation, a PHP/MySQL backend, and a password-protected
admin panel that shows how many students have registered.

**Tech stack:** HTML5, CSS3, Vanilla JavaScript, PHP, MySQL (phpMyAdmin)

---

## 1. Folder structure

```
student-registration/
├── index.php                  → the public registration form
├── submit_registration.php    → handles form POST, validates + inserts into DB
├── check_duplicate.php        → AJAX endpoint: live email/phone uniqueness check
├── setup_admin.php            → run ONCE to create your admin login, then delete
├── config.php                 → database connection settings
├── assets/
│   ├── css/
│   │   ├── style.css          → main design system (form + shared styles)
│   │   └── admin.css          → admin panel specific styles
│   └── js/
│       └── validation.js      → all real-time validation logic
├── admin/
│   ├── login.php               → admin login page
│   ├── dashboard.php           → admin panel: stats + student list + search
│   └── logout.php
└── database/
    └── student_registration.sql → run this in phpMyAdmin to create tables
```

---

## 2. Requirements

- **XAMPP** (or WAMP/MAMP/LAMP) with Apache, PHP 7.4+, and MySQL
  Download: https://www.apachefriends.org/
- A browser
- (Optional) VS Code to view/edit the code

---

## 3. Setup steps

### Step 1 — Install XAMPP and start services
1. Install XAMPP.
2. Open the **XAMPP Control Panel** and click **Start** next to both
   **Apache** and **MySQL**.

### Step 2 — Copy the project into htdocs
1. Locate your XAMPP installation folder (e.g. `C:\xampp` on Windows,
   `/Applications/XAMPP` on Mac).
2. Copy the whole `student-registration` folder into `htdocs`, so you get:
   `C:\xampp\htdocs\student-registration\...`

### Step 3 — Create the database
1. Open your browser and go to: `http://localhost/phpmyadmin`
2. Click **Import** in the top menu.
3. Click **Choose File** and select `database/student_registration.sql`
   from the project folder.
4. Click **Go**. This creates the database `student_registration_db` with
   two tables: `students` and `admins`.

### Step 4 — Check your database credentials
Open `config.php` and confirm these match your MySQL setup (XAMPP defaults
shown below — usually you don't need to change anything):
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'student_registration_db');
```

### Step 5 — Create your admin account
1. In your browser, go to:
   `http://localhost/student-registration/setup_admin.php`
2. Enter a username and password (min. 8 characters) and submit.
3. **Delete `setup_admin.php` from the project folder afterward** — it's a
   one-time setup script and shouldn't be left on a live server.

### Step 6 — Open the registration form
Go to: `http://localhost/student-registration/index.php`

Fill the form — you'll see every field validate as you type:
- Green border + checkmark = valid
- Red border + message = invalid, with a specific reason
- Email and phone are checked **live against the database** for duplicates
- The password field has a strength meter
- The submit button only enables once every field passes
- The left panel shows a live "form completion" progress tracker

### Step 7 — View the admin panel
Go to: `http://localhost/student-registration/admin/login.php`
Log in with the admin account you created in Step 5.

You'll see:
- **Total registered students**
- **Registered today** / **this week**
- **Number of distinct courses**
- A searchable table of every registered student (name, contact, gender,
  course, registration date/time)

---

## 4. How the real-time validation works (for your report/viva)

**Client-side (`assets/js/validation.js`)**
- Listens to the `input`/`change` event on every field — validates as the
  student types, not just on submit.
- Regex checks: name (letters only), email format, 10-digit phone, address
  minimum length.
- Date of birth: calculates exact age from the date and rejects under-15 /
  future dates.
- Password: live strength meter scoring length, uppercase+lowercase,
  numbers, and symbols; confirm-password re-checks automatically whenever
  the password field changes.
- **AJAX duplicate check**: after the email/phone passes its format check,
  JavaScript calls `check_duplicate.php` via `fetch()` (debounced 500ms) to
  ask the database in real time whether that email/phone is already taken —
  this is what makes it genuinely "real-time" rather than just client-side
  regex.
- A progress ledger on the left tallies how many field-groups are complete
  and visually checks them off.
- The submit button stays disabled until every field is valid.

**Server-side (`submit_registration.php`, `check_duplicate.php`)**
- Never trusts the client: every rule (name format, email format, phone
  format, age range, password strength, address length) is re-validated in
  PHP before anything touches the database.
- Uses **prepared statements** (`mysqli` with bound parameters) everywhere
  to prevent SQL injection.
- Passwords are hashed with PHP's `password_hash()` (bcrypt) — never stored
  in plain text.
- The `students` table also has `UNIQUE` constraints on `email` and `phone`
  as a final safety net against race conditions.

---

## 5. Suggested additions if you want to extend it further

- Email confirmation after registration (using PHPMailer)
- Export the student list to CSV/Excel from the admin panel
- Edit/delete student records from the admin panel
- Pagination on the student table for large datasets
- "Forgot password" flow for the admin account

---

## 6. Notes for your project report

- **Frontend:** HTML5 semantic form, CSS3 (custom properties/design
  tokens, responsive grid layout, no framework), vanilla JavaScript
  (no libraries — good to highlight you wrote validation logic yourself).
- **Backend:** PHP with MySQLi, prepared statements, sessions for admin
  auth and flash messages.
- **Database:** MySQL via phpMyAdmin, two normalized tables.
- **Security practices used:** password hashing (bcrypt), prepared
  statements (SQL injection prevention), server-side re-validation,
  session-based admin authentication.
