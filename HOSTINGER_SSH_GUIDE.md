# How to Use SSH on Hostinger to Install Composer Dependencies

This guide will help you connect to your Hostinger server via SSH and install the missing Google Login library and other dependencies properly.

## Step 1: Get Your SSH Credentials
1. Log in to your **Hostinger hPanel**.
2. Go to **Websites** -> **Manage** (for `leilifecafe.bscs3b.com` or your main domain).
3. In the left sidebar, look for **Advanced** -> **SSH Access**.
4. Here you will see:
   - **SSH IP**: (e.g., 123.456.78.90)
   - **SSH Port**: (usually 65002)
   - **Username**: (e.g., u123456789)
   - **Password**: This is usually your **FTP password** or Main Account password. If you forgot it, click "Change Password" on this page.
5. **Enable SSH Access** if it says "SSH Access is Disabled".

## Step 2: Connect via Terminal (Windows PowerShell or Command Prompt)
1. Open **PowerShell** or **Command Prompt** (cmd) on your computer.
2. Type the following command (replace with your actual username and IP):
   ```bash
   ssh -p 65002 u123456789@123.456.78.90
   ```
   *Note: `-p 65002` specifies the port. Hostinger commonly uses 65002.*
3. Press **Enter**.
4. If asked "Are you sure you want to continue connecting?", type `yes` and press Enter.
5. Enter your **Password** when prompted. 
   *(Note: You won't see typing or asterisks on screen. Just type blindly and hit Enter.)*

## Step 3: Navigate to Your Project Folder
Once connected, you are in the root directory. You need to go to where your `composer.json` file is.

1. List files to see where you are:
   ```bash
   ls
   ```
3. **Locate your `public_html` folder**:
   - **Scenario A (Standard)**: If you see `public_html` in the list, type:
     ```bash
     cd public_html
     ```
   - **Scenario B (Multi-Domain)**: You see a `domains` folder (This matches your current screen). Type:
     ```bash
     cd domains
     ls
     cd leilifecafe.bscs3b.com   <-- (Or whatever your domain folder is named)
     cd public_html
     ```
4. **Locate `composer.json`**:
   Once inside `public_html`, check if your files are there or in a subfolder (like `backend`).
   ```bash
   ls
   ```
   *   If you see `composer.json`, you are in the right place.
   *   If you uploaded your code into a subfolder (e.g., `Leilife_2nd` or `backend`), `cd` into it:
       ```bash
       cd backend
       ```

   Verify `composer.json` exists in the current folder:
   ```bash
   ls composer.json
   ```
   *(If it says "No such file", you are in the wrong folder. Look around with `ls` or `cd ..`)*

## Step 4: Install Dependencies
Now run the composer install command. Hostinger usually has Composer pre-installed.

1. Run:
   ```bash
   composer install
   ```
   *   If `composer` command is not found (rare), try `php composer.phar install` or checking Hostinger docs.
2. **Wait** for it to finish. It will download `google/apiclient`, `phpmailer`, `pusher`, etc., into a `vendor/` folder.

## Step 5: Verify
1. Run `ls -F` and check if you see a `vendor/` folder ending with a slash.
2. Go to your website browser and try **Google Login** again. It should work perfectly now!
