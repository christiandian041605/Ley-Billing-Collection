# SUNN SDP Tracker

Minimal PHP application (plain PHP + PDO + MySQL).

Quick start
-----------
1. Install XAMPP (or other LAMP) and ensure Apache + MySQL are running.
2. Place the project in your web root (example path used in this repo: `/Applications/XAMPP/xamppfiles/htdocs/SUNN-SDP-Tracker`).
3. Import the DB dump:

```bash
mysql -u root -p sunn_sdp_tracker < database/sunn_sdp_tracker.sql
```

4. (Optional) Copy `.env.example` to `.env` and update values. `config/database.php` reads environment variables if present.
5. Visit: `http://localhost/SUNN-SDP-Tracker/` in your browser.

Developer utilities
-------------------
- `generate_hash.php` — simple page to create password hashes for seeding users.
- `health_check.php` — returns a JSON status of DB connectivity. Example: `http://localhost/SUNN-SDP-Tracker/health_check.php`.

Security notes
--------------
- Passwords are hashed with `password_hash()`.
- Sessions configured in `config/session.php` with `httponly` and `samesite` options. Enable HTTPS for production and set `secure=true`.
- CSRF helper available at `helpers/csrf.php` — use `csrf_input_field()` inside forms and `verify_csrf_token($_POST['csrf_token'])` on POST handlers.

Next improvements (suggested)
---------------------------
1. Audit all forms to include CSRF tokens. Use the helper in `helpers/csrf.php`.
2. Replace plaintext DB credentials with environment-managed secrets and create a non-root DB user for production.
3. Add tests and a minimal CI pipeline.
