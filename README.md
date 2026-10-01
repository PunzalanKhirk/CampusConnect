# CampusConnect - Command Hub

Administrative & moderation backend for the CampusConnect student forum.
PHP (vanilla MVC) + MySQL (PDO) + HTML/CSS/JS, built for local XAMPP hosting.

## Setup (first time)

1. **Copy the project** into your XAMPP htdocs folder:
   ```
   C:\xampp\htdocs\CampusConnect      (Windows)
   /Applications/XAMPP/htdocs/CampusConnect   (Mac)
   /opt/lampp/htdocs/CampusConnect    (Linux)
   ```

2. **Start Apache and MySQL** from the XAMPP control panel.

3. **Enable mod_rewrite** (only needed once per XAMPP install):
   - Open `xampp/apache/conf/httpd.conf`
   - Uncomment: `LoadModule rewrite_module modules/mod_rewrite.so`
   - Make sure your `<Directory "C:/xampp/htdocs">` block has `AllowOverride All`
   - Restart Apache

4. **Check your DB credentials** in `config/config.php` — the XAMPP defaults
   (`root` / no password) are already set. Change `URLROOT` here too if your
   folder isn't named `CampusConnect`.

5. **Run the installer** — visit this once in your browser:
   ```
   http://localhost/CampusConnect/public/setup.php
   ```
   This creates the `campusconnect` database, all tables, two working
   accounts, and a handful of demo reports so the Moderation Queue isn't
   empty. The page will show you the login credentials it just created.

6. **Log in**:
   ```
   http://localhost/CampusConnect/public/auth/login
   ```

   | Role                  | Email                        | Password           |
   |------------------------|-------------------------------|---------------------|
   | System Administrator   | admin@campusconnect.test      | Admin@12345         |
   | Student Moderator      | mod@campusconnect.test        | Moderator@12345     |

   **Change both passwords after your first login in production use** —
   these are demo credentials seeded for local development only.

7. **Delete or restrict `public/setup.php`** once installed. It refuses to
   run twice on its own (it writes `config/.installed` as a lock file), but
   a setup script should never be left reachable in a real deployment.

## If setup.php shows a database error

- Confirm MySQL is actually running (green in the XAMPP control panel).
- Confirm `DB_USER` / `DB_PASS` in `config/config.php` match your MySQL
  install (XAMPP defaults are `root` with an empty password).
- If you'd rather set the database up manually instead of using the
  installer, import `config/schema.sql` via phpMyAdmin, then still visit
  `setup.php` once — it will detect the existing tables and just seed the
  two accounts + demo data on top of them.

## Resetting the demo data

Drop the database and re-run setup:
```sql
DROP DATABASE campusconnect;
```
Then delete `config/.installed` and revisit `public/setup.php`.

## Project structure

See the code comments in `core/App.php`, `core/Middleware.php`, and
`app/models/ReportModel.php` for how routing, RBAC, and the anonymous-post
protection rule work.
