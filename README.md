# Ethiopian Bookstore

Plain PHP bookstore (no framework) for browsing Ethiopian books, session cart, guest/customer checkout via **Chapa**, and an authenticated admin panel.

## Requirements

- PHP **8.2+** with extensions: `mysqli`, `curl`, `json`, `mbstring`, `fileinfo`
- MySQL **8+** (or MariaDB 10.5+)
- Composer 2

## Setup

```bash
cp .env.example .env
# edit DB_* and optional CHAPA_* / MAIL_*

composer install

mysql -u root -p < bookstore/database/migrations/001_schema.sql
mysql -u root -p < bookstore/database/migrations/002_seed.sql
```

Serve the app (document root = `bookstore/`):

```bash
cd bookstore && php -S localhost:8080
```

Open http://localhost:8080

Default admin (from seed):

- Username: `admin`
- Password: `password`

## Environment

| Variable | Purpose |
|----------|---------|
| `APP_URL` | Public base URL (used in Chapa return/callback) |
| `APP_DEBUG` | Show detailed errors when true |
| `APP_LANG` | Default `am` or `en` |
| `DB_*` | MySQL connection |
| `DELIVERY_FEE` | Added to order total (default 25) |
| `CHAPA_SECRET` | Chapa secret key (required for live payment) |
| `CHAPA_WEBHOOK_SECRET` | HMAC secret for `payment_callback.php` |
| `MAIL_FROM` / `MAIL_FROM_NAME` | Order confirmation sender |

Without `CHAPA_SECRET`, debug mode marks orders paid immediately so local checkout still works.

## Chapa webhook

Configure Chapa to POST to:

`{APP_URL}/payment_callback.php`

Return URL used by the app:

`{APP_URL}/payment_return.php?tx_ref=...`

If the PHP server uses `bookstore/` as docroot, URLs are as above. If the project root is the vhost, prefix paths with `/bookstore/`.

## Tests

```bash
composer test
# or
vendor/bin/phpunit
```

## Main flows

- Catalog: `index.php`, `books.php` (search + category + pagination), `book.php`
- Cart / checkout: `cart.php` → `checkout.php` → `purchase.php` → `process.php` → Chapa → `payment_return.php`
- Customer: `register.php`, `login.php`, `my_orders.php`
- Admin: `admin.php` → dashboard, books CRUD, orders, customers
- Contact messages stored in `messages` table

## Security notes

- Admin and customer passwords use `password_hash` / `password_verify` (legacy SHA-1 admin hashes auto-upgrade on login)
- CSRF tokens on state-changing forms
- Prepared statements for request-bound queries
- Uploads allowlisted to jpg/png/webp with random filenames
