# Digital Downloads

Digital Downloads is a Laravel-based music marketplace and download platform. Artists and administrators can manage releases, tracks, genres, artwork, audio files, and waveforms, while customers can discover music, purchase releases, and access their downloads.

## Features

- Public music and release catalogue
- Customer registration, login, profile management, and session management
- Authenticated download library with protected file downloads
- Release, track, and genre management
- Audio metadata and waveform support
- Payment processing through M-Pesa, PayPal, and CPay/card gateways
- Payment status polling, callbacks, invoices, and transaction exports
- Customer notifications and Firebase Cloud Messaging tokens
- Paid service/task workflows with administrator file fulfilment
- Filament administration panel with Livewire components
- Optional S3-compatible storage for media files

## Technology

- PHP 8.2 or 8.3
- Laravel 12
- Laravel Sanctum
- Filament 5
- Livewire 4
- MySQL or another Laravel-supported database
- Vite and npm
- Firebase PHP SDK
- Flysystem S3 adapter

## Requirements

Install the following before starting:

- PHP 8.2 or later with the extensions required by Laravel
- Composer
- Node.js and npm
- MySQL, MariaDB, or another configured Laravel database
- Credentials for any payment, mail, Firebase, or object-storage services used locally

## Installation

Clone the repository and enter the project directory:

```bash
git clone https://github.com/molefigog/Musik.git
cd Musik
```

Install backend and frontend dependencies:

```bash
composer install
npm install
```

Create the local environment file and application key:

```bash
cp .env.example .env
php artisan key:generate
```

On Windows PowerShell, use `Copy-Item .env.example .env` instead of `cp`.

Configure the database and application URLs in `.env`, then run the migrations:

```bash
php artisan migrate
```

Create the public storage link for locally stored media:

```bash
php artisan storage:link
```

## Configuration

Never commit `.env` or service credentials. At minimum, configure these areas for a working local installation:

- `APP_URL` and the frontend URL used by payment redirects
- `DB_*` database settings
- Mail settings for invoices and transaction reports
- Firebase credentials for notifications, if enabled
- M-Pesa, PayPal, and CPay credentials for payment testing
- Filesystem settings for local or S3-compatible media storage
- Queue settings if background jobs are enabled

Payment credentials should use sandbox or test mode during development. Review `config/payments.php`, `config/firebase.php`, `config/filesystems.php`, and `config/mail.php` alongside the corresponding `.env` values.

## Running Locally

For the complete development stack, run:

```bash
composer run dev
```

This starts the Laravel server, queue listener, log viewer, and Vite development server. To run individual processes instead:

```bash
php artisan serve
npm run dev
```

The API is served by Laravel. The frontend URL is controlled by the Vite configuration and application environment settings.

The Filament administration panel is available at `/admin` when the application is running. Access depends on the configured user and authorization rules.

## API Areas

The API routes are defined in `routes/api.php` and include:

- Authentication and profile management
- Music, releases, genres, and user resources
- Protected download library and file access
- Payment gateways, M-Pesa, PayPal, CPay, and callbacks
- Notifications and Firebase device tokens
- Customer tasks and administrator task fulfilment

Sanctum authentication is required for protected endpoints. Public catalogue endpoints are available for browsing music and individual tracks.

## Testing and Code Quality

Run the test suite with:

```bash
php artisan test
```

Format PHP files with Laravel Pint:

```bash
./vendor/bin/pint
```

Build the frontend for production with:

```bash
npm run build
```

## Production Checklist

Before deploying:

1. Set `APP_ENV=production` and `APP_DEBUG=false`.
2. Use production database, mail, payment, Firebase, and storage credentials.
3. Run `php artisan migrate --force`.
4. Build frontend assets with `npm run build`.
5. Configure the web server document root to the `public` directory.
6. Configure a worker for queued jobs such as payment polling and invoice delivery.
7. Restrict media storage and verify that downloads require the expected authorization.
8. Configure HTTPS and ensure payment callback URLs match the deployed application URL.

## Repository Layout

```text
app/          Application code, controllers, models, jobs, services, and Filament resources
config/       Application, payment, storage, Firebase, and feature configuration
database/     Migrations, factories, and seeders
public/       Web entry point and built/public assets
resources/    Frontend assets and views
routes/       Web and API route definitions
tests/        Feature and unit tests
```

## Security Notes

Keep `.env`, private keys, Firebase credentials, payment secrets, and production storage credentials outside version control. Rotate any credential that may have been exposed and use provider sandbox environments while developing payment flows.
