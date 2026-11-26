# Gapcorn rewards

A PHP, Bootstrap 5.0.2, and MySQL demo for a store's first sign-in reward.

## Hosted site

[Open the Gapcorn demo](https://gapcorn-demo.site.je/)

## Run on WampServer

1. Start Apache and MySQL in WampServer. Use MySQL 8.0.16 or newer and PHP 8.2 or newer. Enable PHP → PHP extensions → pdo_mysql if needed, then restart Apache.
2. Copy the project into `C:\wamp64\www\gapcorn`, or the `www` folder for your WampServer installation. Include `.htaccess` and skip `.git`.
3. Open [phpMyAdmin](http://localhost/phpmyadmin/) and select the MySQL server if prompted. Import `schema.sql` to create the `gapcorn` database, its tables, and the initial welcome reward.
4. Open [Admin sign-in](http://localhost/gapcorn/admin-login.php) or [Customer sign-in](http://localhost/gapcorn/).

Set the admin email and a PHP password hash in the ignored `config.local.php` file using the `admin_email` and `admin_password_hash` keys. You can also use the `GAPCORN_ADMIN_EMAIL` and `GAPCORN_ADMIN_HASH` environment variables. Customers create accounts from the customer sign-in screen. In phpMyAdmin, open **gapcorn → customers → Browse** to see registered emails and point balances. Welcome reward history is in `reward_settings` and is managed under **Master Entries → Reward Management**.

The local demo defaults to MySQL at `127.0.0.1:3306` with the user `root` and a blank password. If your settings differ, add them to `config.local.php` using the `host`, `port`, `name`, `user`, and `password` keys. You can also set the corresponding `GAPCORN_DB_HOST`, `GAPCORN_DB_PORT`, `GAPCORN_DB_NAME`, `GAPCORN_DB_USER`, and `GAPCORN_DB_PASSWORD` environment variables. If you change the database name, update it in `schema.sql` before import.

## Move data from SQLite

After importing `schema.sql` into a fresh MySQL database, enable `pdo_sqlite` and `pdo_mysql` in PHP CLI. Run the following from the project folder before creating accounts or editing balances in MySQL.

```powershell
php migrate-sqlite.php 'C:\wamp64\gapcorn-data\your-file.sqlite'
```

Replace `your-file.sqlite` with the filename of your existing database. The migration copies accounts, point balances, activity timestamps, reward settings, and demo balances. The source database stays available as a backup.

## Demonstrate the reward

1. Create a customer account in a private browser window and verify that the welcome popup awards 10 points.
2. In a separate browser session, sign in as admin. Select **Update reward** and save 20. The reward history shows the effective date and time in IST. The dialog preselects the current time. To schedule a change, choose a future date and time using **Effective date** and **Effective time (IST)**.
3. Create a second customer account and check its 20-point award. Sign in to the first account again to check its original 10-point balance.
4. In the admin dashboard, open **Customers** and try **Edit points** on an individual customer.

## Project layout

- Root PHP files handle customer and admin requests.
- app/ contains the session, database, and layout code shared by those pages.
- views/ contains the admin sections and welcome reward popup.
- assets/ contains the browser code, styles, demo avatars, and local Bootstrap files.
- schema.sql and migrate-sqlite.php handle database setup and migration.

## Build frontend assets

Run npm ci and npm run build from the project folder. Webpack bundles Bootstrap 5.0.2 and the app JavaScript and CSS into assets/dist/ and copies the avatar image. Use npm run watch while editing frontend code.

When deploying a built copy, upload assets/dist/ alongside the rest of the PHP project. PHP uses the bundle when all three outputs are present. A copy without build outputs uses the original local assets. Node.js is only needed on the build machine.
