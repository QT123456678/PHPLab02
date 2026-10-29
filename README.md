# Workshop Booking Portal — PHPLab02

PHP (no framework, no database). Bookings are kept in `$_SESSION`.

## Features
- Catalog with GET search/filter (`index.php`)
- Booking form with server-side validation + CSRF + POST/Redirect/GET (`booking.php`)
- My Bookings dashboard (`dashboard.php`) with **View Details** and **Edit**
- **View Booking Detail** — `booking_detail.php?ref=BK-XXXXXX` (GET, read-only)
- **Edit Booking** — `booking_edit.php?ref=BK-XXXXXX` (GET prefill, POST update, CSRF, validation, amount recalculated on server, 303 redirect)
- Theme cookie (`preference.php`), Clear Session (`logout.php`), Request Inspector (`request_demo.php`)

## Run
```bash
php -S localhost:8000
```
Open http://localhost:8000/ (do not open files with `file://`). Or put the folder in XAMPP `htdocs` and open `http://localhost/php-s07-workshop/`.

## Structure
```
index.php  booking.php  booking_detail.php  booking_edit.php
dashboard.php  preference.php  logout.php  request_demo.php
lib/bootstrap.php  lib/functions.php
data/workshops.php
assets/style.css
```

## Limitations
Session-only demo: no database, no real login, no global seat inventory.
