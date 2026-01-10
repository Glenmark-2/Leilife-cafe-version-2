# Final Deployment Checklist for Hostinger

Your application code has been refactored to be **Production Ready**. It now uses dynamic paths and environment variables.

## 0. Urgent: Google Login Fix

For your specific domain `leilifecafe.bscs3b.com`, you MUST update your Google Cloud Console to fix the "Origin Mismatch" error.

1.  Go to [Google Cloud Console](https://console.cloud.google.com/apis/credentials).
2.  Select your project.
3.  Edit your **OAuth 2.0 Client ID**.
4.  Add the following to **Authorized JavaScript origins**:
    *   `https://leilifecafe.bscs3b.com`
5.  Save and wait 5-10 minutes.

## 1. File Upload

1.  Upload all files from your local `Leilife_2nd` folder to `public_html` (or the specific folder for your subdomain) on Hostinger.
2.  If `leilifecafe.bscs3b.com` points to `public_html/leilife_2nd`, upload the contents there.
3.  If `leilifecafe.bscs3b.com` points to `public_html`, upload there.

## 2. Environment Configuration (.env)

Create a `.env` file in the root of your deployment with these values:

```ini
APP_ENV=production
DB_HOST=localhost
DB_NAME=u123456789_leilife (Update with YOUR Hostinger DB Name)
DB_USER=u123456789_admin (Update with YOUR Hostinger DB User)
DB_PASS=YourStrongPassword

# URL Configuration
# For your subdomain, this should be:
BASE_URL=https://leilifecafe.bscs3b.com

# Pusher
PUSHER_APP_ID=...
PUSHER_KEY=...
PUSHER_SECRET=...
PUSHER_CLUSTER=ap1

# SMTP
SMTP_HOST=smtp.hostinger.com
SMTP_PORT=587
SMTP_USER=no-reply@leilifecafe.bscs3b.com
SMTP_PASS=YourEmailPassword
SMTP_FROM_EMAIL=no-reply@leilifecafe.bscs3b.com
SMTP_FROM_NAME=Leilife Cafe
```

## 3. Database Migration

1.  Export your local database `leilife_v2` to a `.sql` file.
2.  Go to Hostinger -> phpMyAdmin.
3.  Import the `.sql` file into your Hostinger database.

## 4. Entry Point

- Access the site via: `https://leilifecafe.bscs3b.com/public/index.php`
- OR if you configured the domain root to point to the `public/` folder: `https://leilifecafe.bscs3b.com/`

## 5. Security Checks

- `display_errors` is disabled in `public/index.php`.
- `requireLogin` is enabled in `public/admin.php` and `driver.php`.
