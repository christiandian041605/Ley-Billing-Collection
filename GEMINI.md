# GEMINI.md - Project Context

## Project Overview

This project is the "SUNN SDP Tracker," a web-based administration panel for managing users, application settings, and viewing activity logs. The name suggests it's used for tracking a "Strategic Development Plan (SDP)."

**Key Technologies:**

*   **Back-end:** Vanilla PHP, using a mix of procedural and object-oriented programming. It directly uses the PDO extension for database communication.
*   **Front-end:** The UI is built with the **AdminLTE** template, which uses **Bootstrap 5** and **jQuery**. It uses the DataTables library for displaying tabular data.
*   **Database:** MySQL/MariaDB, as indicated by the PDO connection string in `config/database.php`.
*   **Web Server:** The application is designed to run on an AMP stack like XAMPP.

**Architecture:**

The application follows a simple, modular structure without a formal framework.

*   **Configuration:** A central `/config` directory holds the application (`app.php`) and database (`database.php`) settings.
*   **Modular Design:** Features are organized into directories (e.g., `/users`, `/app_setting`, `/activity_log`).
*   **Request Handling:** Each module typically contains an `index.php` to display the UI and a `process.php` to handle form submissions (Create, Update, Delete operations).
*   **Data Models:** Object-oriented classes are used to model database tables (e.g., `users/user.php` contains a `User` class). These classes encapsulate the database interaction logic for a specific entity.
*   **UI Composition:** The user interface is composed of shared template files like `header.php`, `sidebar.php`, `navbar.php`, and `footer.php`.
*   **Authentication:** A straightforward session-based authentication system is implemented. The `login/process_login.php` script verifies credentials and sets a `user_id` in the session. Most pages include `config/session.php` at the top to protect routes.
*   **Security:** The application implements CSRF token protection for all POST requests.

## Building and Running

This is a standard PHP application that does not require a build process.

**Setup and Execution:**

1.  **Environment:** A web server with PHP and a MySQL/MariaDB database is required (e.g., XAMPP, WAMP, MAMP).
2.  **Database Setup:**
    *   Create a new database named `sunn_sdp_tracker` (as specified in `config/database.php`).
    *   The database credentials in `config/database.php` are set to the default `root` with no password. Adjust if your environment differs.
    *   **TODO:** No database schema (`.sql` file) was found. The database tables need to be created manually based on the data models (`user.php`, `app_setting.php`, etc.) before the application will function correctly.
3.  **Running the Application:**
    *   Place the project files in the web server's document root (e.g., `/Applications/XAMPP/xamppfiles/htdocs/SUNN-SDP-Tracker`).
    *   Access the application in your browser via the configured base URL (e.g., `http://localhost/SUNN-SDP-Tracker`). You will be redirected to the login page.

## Development Conventions

*   **Database Table Naming:** Tables are prefixed with `tbl_` (e.g., `tbl_user`, `tbl_app_setting`).
*   **Form Processing:** All form submissions are handled by dedicated `process.php` files within each module.
*   **Stateful Logic:** The application is stateful and relies heavily on PHP sessions for authentication and flash messages (e.g., success/error notifications).
*   **Error Reporting:** Error reporting is enabled for development (`display_errors`, `display_startup_errors`).
*   **Activity Logging:** A custom `log_activity()` helper function is used to record user actions in detail, providing a clear audit trail.
*   **Dependencies:** Front-end dependencies (Bootstrap, jQuery, etc.) are loaded via CDNs, as seen in `header.php`.
