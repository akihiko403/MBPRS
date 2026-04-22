# Render Setup Changes

This file summarizes the key changes made so the MBPRS app runs correctly on Render.

## Core App And Infrastructure Changes

- Switched the app from local MariaDB settings to Render PostgreSQL in [.env](c:/Users/admin/Desktop/test%20site/mbprs/.env:23), including SSL-required connection settings.
- Reworked [Dockerfile](c:/Users/admin/Desktop/test%20site/mbprs/Dockerfile:1) to use `php:8.3-apache`, add PostgreSQL support with `pdo_pgsql`, and build dependencies/assets in separate stages.
- Fixed the Docker build so Composer does not run Laravel scripts before `artisan` exists by using `--no-scripts` in the vendor stage and running package discovery later.
- Enabled Apache rewrite support properly for Laravel routes by allowing `.htaccess` overrides in the container.
- Added proxy trust in [bootstrap/app.php](c:/Users/admin/Desktop/test%20site/mbprs/bootstrap/app.php:13) so Laravel respects Render's forwarded HTTPS headers.
- Added forced HTTPS behavior for production via [AppServiceProvider.php](c:/Users/admin/Desktop/test%20site/mbprs/app/Providers/AppServiceProvider.php:5) and [ForceHttpsInProduction.php](c:/Users/admin/Desktop/test%20site/mbprs/app/Http/Middleware/ForceHttpsInProduction.php:1), so `http` requests redirect to `https`.
- Cleaned up favicon handling by replacing the empty root `public/favicon.ico`, adding `favicon.png`, and fixing the shared layout head in [app.blade.php](c:/Users/admin/Desktop/test%20site/mbprs/resources/views/layouts/app.blade.php:6).
- Updated UI details like the dashboard title and sidebar navigation icons so the deployed app branding matches the intended presentation.

## Render Deployment Notes

- Use `https://mbprs.onrender.com/`, not `http://mbprs.onrender.com/`.
- Clear cached Laravel artifacts after deploy with:

```bash
php artisan optimize:clear
```

- Make sure Render is using the latest pushed code or image, since stale deploys can keep old routes, views, or config behavior.

## Why These Changes Matter

- Render terminates SSL at the proxy layer, so Laravel must trust forwarded headers.
- Laravel routes like `/dashboard` and `/logout` depend on Apache rewrite rules and correct HTTPS detection.
- PostgreSQL on Render requires the correct driver and SSL-capable configuration.
- Docker needs to build Laravel in the right order so Composer and asset compilation do not fail during image creation.
