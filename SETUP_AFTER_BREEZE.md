# FoodHub — Breeze is installed

Laravel Breeze (**Livewire + Volt**) is set up. FoodHub routes are merged in `routes/web.php`.

## Commands you may still need

```bash
php artisan migrate
php artisan db:seed
# or only reset admin password / account:
php artisan db:seed --class=AdminUserSeeder
php artisan queue:work   # order confirmation emails
```

Or run everything with: `composer dev`

## Demo logins (password: `password`)

| Email | Role |
|-------|------|
| admin@foodhub.test | Admin → `/admin` |
| customer@foodhub.test | Customer |

## Auth URLs (Breeze)

- `/login`, `/register`
- `/profile` (account settings)
- `/forgot-password`, `/reset-password/{token}`

## Public FoodHub URLs

- `/` home, `/menu`, `/cart`, `/checkout`, `/orders` (auth), `/booking`, `/contact`

Architecture: [FOODHUB_PLAN.md](FOODHUB_PLAN.md)
