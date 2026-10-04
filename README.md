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
