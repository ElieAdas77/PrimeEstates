# PrimeEstates

A full-stack real estate listing platform built with PHP, MySQL, and vanilla JavaScript. Users can browse, search, and favorite properties, submit their own listings for review, and admins moderate submissions through a dedicated dashboard.

**Live demo:** http://primeestates.atwebpages.com
**Repo:** https://github.com/EliAdas77/PrimeEstates

> Note: the live demo runs on free hosting without HTTPS, so browsers will show a "Not secure" notice. This is expected on the free tier and doesn't affect functionality.

## Features

- **Authentication** — registration, login, and password reset via email, with bcrypt password hashing and session-based auth
- **Security** — CSRF token verification on every state-changing request, IP-based rate limiting on login/registration/password-reset, and server-side validation throughout
- **Property listings** — search and filter by location, type, price, and bedrooms, with client-side pagination and an image gallery modal (multi-photo carousel with thumbnails)
- **User submissions** — logged-in users can submit properties with multiple photo uploads, which stay in a "pending" state until approved
- **Admin dashboard** — a separate moderation panel to approve, reject, or reset property submissions, and review contact messages
- **Image upload validation** — uploaded files are checked against their actual MIME type and re-verified with `getimagesize()`, not just their file extension, before being saved
- **Favorites** — logged-in users can save properties and view them in a dedicated list
- **Responsive design** — mobile-first layout with a collapsible nav menu, tested across breakpoints

## Tech stack

- **Backend:** PHP (mysqli, prepared statements throughout)
- **Database:** MySQL
- **Frontend:** Vanilla JavaScript (ES6), HTML5, CSS3 — no frameworks
- **Email:** Custom SMTP client built from raw sockets (no external mail library)

## Project structure

```
├── index.php               # Homepage, property listings
├── admin.php                # Admin moderation dashboard
├── Resetpassword.php        # Password reset landing page
├── css/                     # Stylesheets
├── js/                      # Frontend JavaScript, one file per feature
├── php/                     # Backend endpoints and shared logic
│   ├── config.php           # DB connection + CSRF helpers
│   ├── imageUploadHelper.php
│   ├── ratelimiter.php
│   └── ...
└── uploads/properties/      # User-uploaded property images
```

## Running locally

1. Clone the repo into your local server's web root (e.g. XAMPP's `htdocs/`)
2. Create a MySQL database and import the schema (see `/database` if included, or export your own from a running instance)
3. Update `php/config.php` with your local database credentials
4. Copy `php/mailconfig.local.php.example` to `php/mailconfig.local.php` and fill in real SMTP credentials if you want password-reset emails to work locally
5. Visit `index.php` through your local server

## Security notes

`php/mailconfig.local.php` (SMTP credentials) is intentionally excluded from this repo via `.gitignore` — it's created locally/on the server and never committed.
