# 🚀 Quick Deployment Checklist

## ✅ Pre-Deployment (Local)
- [x] Fixed hardcoded localhost URL in MailService.php
- [x] Removed debug error_reporting from production files
- [x] Updated SessionManager for auto-secure cookies in production
- [x] Added APP_URL and APP_ENV to .env.example
- [x] Created .htaccess for security and performance
- [x] Removed duplicate vendor directory (root)
- [ ] Test all features on localhost one final time

## 📦 Files to Upload to Hostinger
Upload everything EXCEPT:
- ❌ `.git/` folder
- ❌ `.env` file (create new on server)
- ❌ `vendor/` in root (already deleted)
- ❌ `.DS_Store`
- ❌ Any local test files

## ⚙️ Server Configuration (Hostinger)

### 1. Create .env file on server
```env
DB_HOST=localhost
DB_NAME=u123456789_leilife
DB_USER=u123456789_user
DB_PASS=your_secure_password

APP_URL=https://yourdomain.com
APP_ENV=production

GOOGLE_CLIENT_ID=your_production_client_id

SMTP_HOST=smtp.hostinger.com
SMTP_PORT=587
SMTP_USER=noreply@yourdomain.com
SMTP_PASS=your_email_password
SMTP_FROM_EMAIL=noreply@yourdomain.com
SMTP_FROM_NAME=Leilife Support

PAYMONGO_PUBLIC_KEY=pk_live_...
PAYMONGO_SECRET_KEY=sk_live_...
PAYMONGO_WEBHOOK_SECRET=whsk_live_...

PUSHER_APP_ID=your_app_id
PUSHER_KEY=your_key
PUSHER_SECRET=your_secret
PUSHER_CLUSTER=ap1

ORS_API_KEY=your_api_key
```

### 2. Install Composer Dependencies
```bash
cd public_html/backend
composer install --no-dev --optimize-autoloader
```

### 3. Import Database
Import all SQL files from `backend/db_script/` in this order:
1. create_tables.sql
2. create_user_registrations_table.sql
3. create_products_tables.sql
4. create_order_tables.sql
5. create_cart_tables.sql
6. create_address_table.sql
7. create_favorites_table.sql
8. create_inbox_table.sql
9. create_staff_tables.sql
10. All add_*.sql files
11. All update_*.sql files

### 4. Update Third-Party Services
- [ ] Google OAuth: Add production domain to authorized origins
- [ ] PayMongo: Switch to LIVE keys, update webhook URL
- [ ] Pusher: Verify production settings
- [ ] OpenRouteService: Verify API key

### 5. Enable SSL
- [ ] Activate free SSL in Hostinger cPanel
- [ ] Uncomment HTTPS redirect in .htaccess (lines 4-6)

## 🧪 Post-Deployment Testing

### Critical Flows to Test:
- [ ] Homepage loads
- [ ] User registration → Email verification → Login
- [ ] Google Sign-In
- [ ] Browse products
- [ ] Add to cart
- [ ] Checkout process
- [ ] PayMongo payment (test mode first!)
- [ ] Order tracking
- [ ] Admin dashboard login
- [ ] Admin order management
- [ ] Driver app login
- [ ] Driver order acceptance
- [ ] Real-time updates (Pusher)
- [ ] Map functionality

### Check These:
- [ ] All images load correctly
- [ ] No console errors
- [ ] HTTPS is working
- [ ] Email verification sends
- [ ] Payment webhooks work
- [ ] Database connections stable

## 🐛 Common Issues & Fixes

### "500 Internal Server Error"
→ Check PHP error logs in cPanel
→ Verify .htaccess syntax
→ Check file permissions (644 for files, 755 for dirs)

### "Database Connection Failed"
→ Verify .env credentials
→ Use 127.0.0.1 instead of localhost
→ Check database user privileges

### "Emails Not Sending"
→ Use Hostinger SMTP instead of Gmail
→ Verify SMTP credentials
→ Check spam folder

### "Pusher Not Connecting"
→ Verify credentials in .env
→ Ensure HTTPS is enabled
→ Check browser console

## 📊 Performance Monitoring

After deployment, monitor:
- [ ] PHP error logs (first 24 hours)
- [ ] Database query performance
- [ ] Page load times
- [ ] Email delivery rate
- [ ] Payment success rate
- [ ] Pusher connection stability

## 🔒 Security Checklist

- [ ] .env file is not publicly accessible
- [ ] HTTPS/SSL is active
- [ ] Session secure flag enabled (auto in production)
- [ ] Debug mode disabled
- [ ] File permissions correct
- [ ] Database user has minimal privileges
- [ ] Regular backups scheduled

## 📞 Support Contacts

- Hostinger Support: 24/7 live chat
- PayMongo: developers.paymongo.com
- Pusher: pusher.com/docs
- Google Cloud: console.cloud.google.com

---

**Status:** Ready for deployment! 🎉

All critical issues have been fixed. Follow this checklist step-by-step for a smooth deployment.
