This project uses PHP and Composer for dependency management.

## Prerequisites
1.  **XAMPP (or MAMP/WAMP)**: Ensure PHP 8.0+ and MySQL are running.
2.  **Composer**: Required to install backend libraries (PHPMailer).

## Setup Instructions

### Windows & Mac

1.  **Clone the Repository**:
    ```bash
    git clone <repository_url>
    cd Leilife_2nd
    ```

2.  **Install Dependencies**:
    Navigate to the backend directory and run composer install.
    ```bash
    cd backend
    composer install
    ```
    *This will create the `backend/vendor` folder.*

3.  **Configure Environment**:
    -   Copy `.env.example` to `.env` in the project root.
    -   Fill in your Database credentials and Google/Mail secrets.
    ```bash
    # (Windows PowerShell)
    copy .env.example .env
    # (Mac/Linux)
    cp .env.example .env
    ```

4.  **Database Setup**:
    -   Import the SQL scripts in `backend/db_script/` to your MySQL database (`leilife_v2`).

5.  **Run**:
    -   Start Apache and MySQL in XAMPP control panel.
    -   Access via `http://localhost/Leilife_2nd/public/index.php?page=home`.
