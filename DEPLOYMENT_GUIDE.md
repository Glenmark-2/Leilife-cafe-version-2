# 🚀 Hostinger Deployment Guide for Leilife v2

## ⚠️ Critical Issues to Fix Before Deployment

### 1. **HARDCODED LOCALHOST URL** ❌
**File:** `backend/services/MailService.php` (Line 51)
```php
// CURRENT (WRONG):
$verifyLink = "http://localhost/Leilife_2nd/public/index.php?page=verify&token=" . $token;

// SHOULD BE:
$verifyLink = getenv('APP_URL') . "/public/index.php?page=verify&token=" . $token;
```
**Action Required:** Add `APP_URL` to `.env` file and update MailService.php

---

### 2. **DEBUG MODE ENABLED** ⚠️
**Files with debug settings:**
- `backend/api/admin/update_product.php` (Lines 2-3)
- `backend/api/admin/add_product.php` (Lines 2-3)
- `backend/api/add_favorite.php` (Lines 2-4)

**Action Required:** Remove or comment out these lines:
```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

---

### 3. **HTTPS/SSL Configuration** 🔒
**File:** `backend/helpers/SessionManager.php` (Line 13)
```php
// CURRENT:
'secure' => false, // Set to true if HTTPS

// SHOULD BE (for production):
'secure' => true, // Hostinger provides free SSL
```

---

### 4. **CORS Headers Too Permissive** 🌐
**Issue:** 19 files have `Access-Control-Allow-Origin: *`
**Action Required:** Restrict CORS to your domain only (optional but recommended)

---

## 📋 Pre-Deployment Checklist

### Environment Configuration
- [ ] Copy `.env.example` to `.env` on Hostinger
- [ ] Update all `.env` variables for production:
  ```env
  # Database (from Hostinger MySQL panel)
  DB_HOST=localhost
  DB_NAME=u123456789_leilife
  DB_USER=u123456789_user
  DB_PASS=your_secure_password
  
  # Application URL (your domain)
  APP_URL=https://yourdomain.com
  
  # Google OAuth
  GOOGLE_CLIENT_ID=your_production_client_id
  
  # SMTP (Gmail or Hostinger email)
  SMTP_HOST=smtp.hostinger.com
  SMTP_PORT=587
  SMTP_USER=noreply@yourdomain.com
  SMTP_PASS=your_email_password
  SMTP_FROM_EMAIL=noreply@yourdomain.com
  SMTP_FROM_NAME=Leilife Support
  
  # PayMongo (switch to LIVE keys)
  PAYMONGO_PUBLIC_KEY=pk_live_...
  PAYMONGO_SECRET_KEY=sk_live_...
  PAYMONGO_WEBHOOK_SECRET=whsk_live_...
  
  # Pusher
  PUSHER_APP_ID=your_app_id
  PUSHER_KEY=your_key
  PUSHER_SECRET=your_secret
  PUSHER_CLUSTER=ap1
  
  # OpenRouteService
  ORS_API_KEY=your_api_key
  ```

### Files to Upload
- [ ] All project files EXCEPT:
  - `.git/` folder
  - `.env` (create new on server)
  - `vendor/` (root - already deleted)
  - `node_modules/` (if any)
  - `.DS_Store`
  - Local test files

### Files to Create on Server
- [ ] `.env` file with production credentials
- [ ] `.htaccess` for URL rewriting (see below)

### Database Setup
- [ ] Create MySQL database in Hostinger cPanel
- [ ] Import all SQL scripts from `backend/db_script/`:
  1. `create_tables.sql`
  2. `create_user_registrations_table.sql`
  3. `create_products_tables.sql`
  4. `create_order_tables.sql`
  5. `create_cart_tables.sql`
  6. `create_address_table.sql`
  7. `create_favorites_table.sql`
  8. `create_inbox_table.sql`
  9. `create_staff_tables.sql`
  10. All `add_*.sql` and `update_*.sql` files
- [ ] Verify all tables are created successfully

### Composer Dependencies
- [ ] SSH into Hostinger or use File Manager terminal
- [ ] Navigate to `backend/` directory
- [ ] Run: `composer install --no-dev --optimize-autoloader`

### Security Hardening
- [ ] Ensure `.env` is NOT publicly accessible
- [ ] Enable HTTPS/SSL (free with Hostinger)
- [ ] Update session secure flag to `true`
- [ ] Remove debug error reporting
- [ ] Verify file permissions (644 for files, 755 for directories)

### Third-Party Services
- [ ] **Google OAuth**: Add production domain to authorized origins
  - Go to Google Cloud Console
  - Add: `https://yourdomain.com`
- [ ] **PayMongo**: Switch to LIVE API keys
  - Update webhook URL to production domain
- [ ] **Pusher**: Verify production app settings
- [ ] **OpenRouteService**: Verify API key quota

---

## 🔧 Required Code Fixes

### Fix 1: Update MailService.php
Add `APP_URL` environment variable support:

```php
// Line 51 in backend/services/MailService.php
$appUrl = getenv('APP_URL') ?: 'http://localhost/Leilife_2nd';
$verifyLink = $appUrl . "/public/index.php?page=verify&token=" . $token;
```

### Fix 2: Remove Debug Settings
Comment out or remove from these files:
- `backend/api/admin/update_product.php`
- `backend/api/admin/add_product.php`
- `backend/api/add_favorite.php`

### Fix 3: Enable Secure Sessions
Update `backend/helpers/SessionManager.php`:
```php
// Line 13
'secure' => (getenv('APP_ENV') === 'production'), // Auto-detect based on environment
```

---

## 📁 Recommended .htaccess Configuration

Create `.htaccess` in your root directory:

```apache
# Enable Rewrite Engine
RewriteEngine On

# Force HTTPS
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# Protect .env file
<Files .env>
    Order allow,deny
    Deny from all
</Files>

# Protect vendor directory
<DirectoryMatch "vendor">
    Order allow,deny
    Deny from all
</DirectoryMatch>

# Default routing to public/index.php
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ public/index.php?page=$1 [QSA,L]
```

---

## 🚀 Deployment Steps

### Step 1: Prepare Files Locally
1. Fix all critical issues listed above
2. Test thoroughly on localhost
3. Create a ZIP of your project (exclude `.git`, `vendor/`)

### Step 2: Upload to Hostinger
1. Log in to Hostinger File Manager or use FTP
2. Upload ZIP to `public_html/` directory
3. Extract the ZIP file
4. Delete the ZIP file

### Step 3: Configure Environment
1. Create `.env` file in root directory
2. Copy content from `.env.example`
3. Update all values with production credentials

### Step 4: Install Dependencies
1. Access SSH terminal (Hostinger provides this)
2. Navigate to `backend/` directory:
   ```bash
   cd public_html/backend
   ```
3. Install Composer dependencies:
   ```bash
   composer install --no-dev --optimize-autoloader
   ```

### Step 5: Setup Database
1. Go to Hostinger cPanel → MySQL Databases
2. Create new database and user
3. Import all SQL scripts from `backend/db_script/`
4. Update `.env` with database credentials

### Step 6: Configure Domain
1. Point your domain to Hostinger nameservers
2. Enable SSL certificate (free in Hostinger)
3. Test HTTPS access

### Step 7: Test Everything
- [ ] Homepage loads correctly
- [ ] User registration works
- [ ] Email verification sends
- [ ] Google login works
- [ ] Products display
- [ ] Cart functionality
- [ ] Order placement
- [ ] PayMongo payment
- [ ] Admin dashboard
- [ ] Driver app
- [ ] Real-time updates (Pusher)
- [ ] Map functionality

---

## 🔍 Post-Deployment Verification

### Test These Critical Flows:
1. **User Registration** → Email verification → Login
2. **Add to Cart** → Checkout → Payment → Order tracking
3. **Admin Panel** → Order management → Status updates
4. **Driver App** → Accept order → Update location → Complete delivery
5. **Real-time Updates** → Order status changes reflect immediately

### Monitor These:
- PHP error logs (in cPanel)
- Database connections
- Email delivery
- Payment webhooks
- Pusher connection status

---

## 🆘 Troubleshooting

### Issue: "500 Internal Server Error"
- Check PHP error logs in cPanel
- Verify `.htaccess` syntax
- Check file permissions (644/755)
- Ensure `vendor/autoload.php` exists

### Issue: Database Connection Failed
- Verify `.env` credentials match Hostinger MySQL
- Use `127.0.0.1` instead of `localhost` if needed
- Check if database user has proper privileges

### Issue: Emails Not Sending
- Verify SMTP credentials
- Check if port 587 is open
- Try Hostinger's SMTP instead of Gmail
- Check spam folder

### Issue: Pusher Not Working
- Verify Pusher credentials in `.env`
- Check browser console for connection errors
- Ensure HTTPS is enabled (Pusher requires it)

### Issue: PayMongo Webhooks Failing
- Update webhook URL in PayMongo dashboard
- Verify webhook secret matches `.env`
- Check if HTTPS is enabled

---

## 📊 Performance Optimization (Optional)

### Enable OPcache
Add to `php.ini` or `.htaccess`:
```ini
opcache.enable=1
opcache.memory_consumption=128
opcache.max_accelerated_files=10000
```

### Enable Gzip Compression
Add to `.htaccess`:
```apache
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css text/javascript application/javascript
</IfModule>
```

### Browser Caching
Add to `.htaccess`:
```apache
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/jpg "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/gif "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
</IfModule>
```

---

## ✅ Final Checklist

Before going live:
- [ ] All critical fixes applied
- [ ] `.env` configured with production values
- [ ] Database imported successfully
- [ ] Composer dependencies installed
- [ ] SSL certificate active
- [ ] All third-party services configured
- [ ] Test user registration flow
- [ ] Test order placement flow
- [ ] Test admin functions
- [ ] Test driver functions
- [ ] Monitor error logs for 24 hours
- [ ] Backup database regularly

---

## 📞 Support Resources

- **Hostinger Support**: Available 24/7 via live chat
- **PayMongo Docs**: https://developers.paymongo.com
- **Pusher Docs**: https://pusher.com/docs
- **Google OAuth**: https://console.cloud.google.com

---

**Good luck with your deployment! 🎉**
