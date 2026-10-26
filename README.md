# model-cars-ecommerce
E-Commerce store for model cars and Hot Wheels


## Week 06 / Database Notes

- Authentication is now intended to use the persistent MySQL `users` table; the previous session-only mock authentication fallback was removed because it could make a newly registered user appear logged in but fail after logout.
- Database configuration is in `php/config.php` and expects database `model_cars_db`.
- `sql/sync_sports_cars.sql` safely synchronizes the six Sports Cars in an existing database without dropping tables.


## Week 07 / PayHere Sandbox

- PayHere Sandbox is integrated into `checkout.php`.
- The Card option posts the existing order total to `https://sandbox.payhere.lk/pay/checkout`.
- The mandatory MD5 hash is generated server-side using the PayHere Sandbox formula.
- PayHere credentials are stored in `php/payhere_config.php`.
- `php/payhere_config.php` is excluded by `.gitignore` so the Merchant Secret is not committed to Git.
- Run the project through XAMPP/localhost, not `file://`.
- In PayHere Sandbox, add `localhost` as a **Domain** (globe icon).
- Use the Sandbox test cards from the Week 07 practical PDF.
- After a successful Sandbox return, the customer is sent back to `checkout.php` with the order number.
- If the payment is cancelled, the customer is sent to `payment-cancel.php`.


## PayHere troubleshooting
- Checkout includes the required `notify_url` field.
- For local testing it points to `payment-notify.php`; PayHere server-to-server callbacks require a publicly reachable URL if notification handling is needed.
- If PayHere still reports a generic 'Something went wrong' error, verify the Integration record is exactly `localhost`, type is Domain, only one record exists for localhost, and the Merchant Secret belongs to that localhost record.

## Week 08 – Database Integration & Admin Dashboard

This version adds a protected database-backed admin area at `admin/index.php`.

Admin features:
- Dashboard summary counts
- Product CRUD: add/edit/search and safe activate/deactivate
- Customer order list and persistent order-status updates
- Inventory/stock monitoring and stock updates
- Server-side role protection (`users.role = admin`)
- CSRF protection for admin POST actions
- Public catalog hides deactivated products

Default admin account from the existing database schema:
- Email: `admin@modelcars.com`
- Password: `password123`

The admin area automatically adds the `products.is_active` column if the existing Week 7 database does not have it. The equivalent SQL migration is also provided at `sql/week8_admin_migration.sql`.


## Week 09 QA additions

- Added local password reset flow (`forgot-password.php`, `reset-password.php`). Reset tokens are stored hashed, expire after 30 minutes, and are single-use. SMTP is not configured; localhost displays the reset link for demonstration.
- Added Admin > Users management for listing users, changing roles and safely deleting accounts.
- Added a product Delete action. If an order already references the product, the product is deactivated instead of permanently deleted to preserve order history.
- Added catalog filters for minimum price, maximum price and model scale/type, alongside existing search/category/brand/sort filters.
- Added mobile collapsible navigation, 44px touch targets, overflow protections, stricter cart quantity validation, and fixed the AJAX cart's false-success message.
- QA notes and required manual test steps: `docs/Week09_QA_Bug_Log.md`.
- Existing database users can run `sql/week9_qa_migration.sql`; for a fresh database, use `sql/database.sql`. Do not import the full database script into a populated database because it drops existing tables.

## Run locally

1. Extract the ZIP into `C:\xampp\htdocs\ModelCars_Pro_Week8_AdminDashboard` (or another folder under `htdocs`).
2. Start Apache and MySQL from XAMPP.
3. Import `sql/database.sql` into phpMyAdmin only if creating a fresh database; otherwise preserve your existing database and run `sql/week9_qa_migration.sql`.
4. Confirm `php/config.php` database settings match your local MySQL.
5. Open `http://localhost/ModelCars_Pro_Week8_AdminDashboard/`.
6. Admin demo account in the supplied SQL: `admin@modelcars.com` / `password123` (change this password before public deployment).
