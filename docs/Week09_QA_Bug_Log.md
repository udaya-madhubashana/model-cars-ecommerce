# ModelCars Pro — Week 09 QA Diary

Date: 2026-10-26

## Static review and changes made

| Area | Finding | Change made | Verification status |
|---|---|---|---|
| User management | Password reset option was missing | Added forgot-password.php and reset-password.php with hashed one-time tokens, 30-minute expiry, single-use behavior, and a SQL/runtime table setup | PHP syntax checked; live email delivery is not configured, local reset link is shown on localhost only |
| Admin users | Admin panel did not include user management | Added admin/users.php for listing users, role changes and safe deletion; protects current admin and last admin | PHP syntax checked; requires live DB test |
| Product management | Product removal was not available as a button | Added delete action; products referenced by orders are deactivated to preserve history | PHP syntax checked; requires live DB test |
| Product filters | Category/brand existed; price and scale/type filters missing | Added minimum/maximum price and scale filters, preserving selected filters while sorting | PHP syntax checked; requires live DB test |
| Mobile layout | Existing mobile navigation wrapped into a long row | Added accessible collapsible menu at small widths, touch target sizing and overflow protection | CSS/JS reviewed; must test in browser device mode |
| Cart quantity | Invalid quantities could be coerced to 1 or treated as remove | Rejects zero/negative/invalid quantity requests and instructs users to use Remove | PHP syntax checked; live cart test required |
| AJAX cart | Network failure showed a false success toast | Now shows an error when the cart request fails | JS syntax checked |
| Shop navigation | Duplicate cart badge was present in shop navigation | Removed duplicate badge | Static check |

## Required manual browser checks before submission

1. Start Apache and MySQL in XAMPP; import `sql/database.sql` only into a fresh database, or use `sql/week9_qa_migration.sql` for an existing database. Do not re-import `database.sql` over an existing database because it contains DROP TABLE statements.
2. Open `http://localhost/ModelCars_Pro_Week8_AdminDashboard/` (adjust folder name if different). Check registration, login/logout, profile update and order history.
3. Add an item, change quantity, remove it, and check subtotal + shipping total. Try quantity 0 and -1; they must be rejected.
4. Check category, brand, minimum/maximum price, scale/type, search and sort together.
5. Test forgot password on localhost; reset link is displayed locally because SMTP/email delivery is not configured.
6. Sign in as admin; test add/edit/delete product, order status update and user role management. Use a test account for destructive tests.
7. Press F12, enable device toolbar, test widths 375px and 390px; confirm no horizontal page scroll and menu opens/closes.
8. Test PayHere Sandbox only after verifying Merchant ID/secret and the registered `localhost` domain in PayHere. A local browser return is not equivalent to server-verified payment; `payment-notify.php` remains a placeholder and should not be used for real payments.
9. Take screenshots of the working site and before/after bug fixes for the project diary.

## Important limits

This package was statically checked in the current environment. A live XAMPP/MySQL browser session and real PayHere Sandbox transaction were not available here, so those integration checks must be completed on the submission computer. Password reset email delivery requires SMTP/email configuration; the local reset link is provided for a localhost demonstration.
