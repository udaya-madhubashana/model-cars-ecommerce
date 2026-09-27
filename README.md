# model-cars-ecommerce
E-Commerce store for model cars and Hot Wheels


## Week 06 / Database Notes

- Authentication is now intended to use the persistent MySQL `users` table; the previous session-only mock authentication fallback was removed because it could make a newly registered user appear logged in but fail after logout.
- Database configuration is in `php/config.php` and expects database `model_cars_db`.
- `sql/sync_sports_cars.sql` safely synchronizes the six Sports Cars in an existing database without dropping tables.
