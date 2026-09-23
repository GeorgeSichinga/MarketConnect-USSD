# Market Connect — Admin / Working Prototype

This folder contains the working version of Market Connect: a PHP + MySQL
prototype that simulates a USSD menu (the kind of "dial a code and reply
with numbers" service used on basic phones) through a normal web page,
built and tested with XAMPP.

It connects smallholder farmers with buyers of **maize, soybeans,
groundnuts, common beans, sweet potatoes, rice, chilli pepper, tobacco,
pigeon peas, and tomatoes**, giving both sides access to market prices,
weather updates, and a simple way to post and confirm sales.

> The `index.php` and other files at the repository **root** are an earlier
> static demo (no database). This `admin/` folder is the current, working
> version with a real backend.

## Getting it running locally (XAMPP)

1. **Copy this whole `admin` folder** into `C:\xampp\htdocs\projects\marketcon\`
   (or wherever your `htdocs` folder is), so the path becomes
   `C:\xampp\htdocs\projects\marketcon\admin\`.
2. Start **Apache** and **MySQL** from the XAMPP Control Panel.
3. **Create your local config files** — these are left out of the repo on
   purpose because they'd otherwise contain your personal DB password and
   API key:
   - Copy `db_connection.example.php` → `db_connection.php`, and fill in
     your own MySQL username/password.
   - Copy `config.example.php` → `config.php`, and add your own free
     [OpenWeatherMap](https://openweathermap.org/api) API key.
4. **Set up the database** — open phpMyAdmin
   (`http://localhost/phpmyadmin`), create a database named
   `market_connect`, click its **SQL** tab, and run these two files
   (paste each one's contents and click Go), in this order:
   1. `step1_setup.sql` — creates the MySQL user used by `db_connection.php`
      (edit the password inside it first, then use that same password in
      step 3 above).
   2. `market_connect_current.sql` — creates every table and loads the
      current data (produce list, users, listings, prices, transactions,
      activity log, etc.) in one go.

   The other SQL files (`admin_panel_setup.sql`, `admin_prices_unit_setup.sql`,
   `transactions_setup.sql`) don't need to be run separately — they're kept
   as a record of how the database changed step by step, and everything
   they do is already included in `market_connect_current.sql`.

5. Visit `http://localhost/projects/marketcon/admin/index.php` to register
   an account, then log in.

## Becoming an Admin

The system has three account types: Farmer/Seller, Buyer/Trader, and Admin.
Every new registration starts as Farmer/Seller. To make an account an
Admin, either:
- Edit the phone number inside `admin_panel_setup.sql` before running it
  (one-time bootstrap for the very first admin), **or**
- Once at least one Admin account exists, that Admin can promote any other
  registered user from **Admin Panel → Manage Users → Change Type**.

Being an Admin doesn't block someone from also using Sell/Buy/Weather —
the same phone number can be used for both testing and admin duties.

## Main pages

| Page | What it does |
|---|---|
| `index.php` | Register a new account |
| `login.php` | Log in |
| `select_role.php` | Main menu (Sell, Buy, Listings, Prices, Weather, My Transactions, Admin Panel) |
| `seller_dashboard.php` / `buyer_dashboard.php` | Step-by-step form to post a sell listing or buy request |
| `market_listings.php` | Browse all buy/sell listings, express interest in one |
| `my_transactions.php` | Confirm or decline a sale once both sides have shown interest |
| `market_prices.php` | Public, read-only market price list |
| `weather_dashboard.php` | Live weather by district (OpenWeatherMap) |
| `admin_panel.php` and `admin_*.php` | Admin-only: manage users, prices, listings, and the activity log |

## Notes

- This is a **prototype/development build** — not yet connected to a real
  USSD gateway or SMS provider. The web forms simulate what a USSD session
  would look like.
- `db_connection.php` and `config.php` are deliberately left out of this
  repo — only the placeholder `.example.php` versions are included. Copy
  them as shown above and never commit real database passwords or API
  keys to a public repository.
