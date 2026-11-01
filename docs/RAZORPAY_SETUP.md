# Razorpay setup (FoodHub)

FoodHub uses a **single platform Razorpay account** (test or live keys in `.env`). Restaurant owners do not connect their own merchant accounts in this demo.

## Environment variables

```env
RAZORPAY_ENABLED=true
RAZORPAY_KEY_ID=rzp_test_xxxx
RAZORPAY_KEY_SECRET=xxxx
RAZORPAY_WEBHOOK_SECRET=whsec_xxxx
```

If `RAZORPAY_ENABLED` is false or keys are missing, checkout uses **mock payment** (no Razorpay UI). This is the default for local development and PHPUnit.

## Local checkout flow

1. Log in as a customer (`customer@foodhub.test` / `password` after seeding).
2. Open a restaurant menu, add items to cart, go to **Checkout**.
3. With mock mode: click pay once — order is confirmed immediately.
4. With Razorpay enabled: click pay → Razorpay modal → use [test cards](https://razorpay.com/docs/payments/payments/test-card-details/).

## Webhooks (optional but recommended)

1. Expose your app (e.g. `ngrok http 8000`).
2. In Razorpay Dashboard → Webhooks, add URL: `https://YOUR_HOST/webhooks/razorpay`
3. Subscribe to `payment.captured` and/or `order.paid`.
4. Copy the webhook secret into `RAZORPAY_WEBHOOK_SECRET`.

Run a queue worker so webhook jobs process:

```bash
php artisan queue:work
```

## After pulling these changes

```bash
composer install
php artisan migrate
php artisan db:seed
php artisan storage:link
php artisan serve
```
