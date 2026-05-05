# PDFCoreLab API

PDFCoreLab is an asynchronous PDF compression API built with Laravel and Ghostscript. Upload a PDF, choose a compression preset, receive a queued job immediately, and poll the API for status and signed download links.

## Features

- Asynchronous PDF compression with queued jobs.
- Five Ghostscript presets: `screen`, `ebook`, `printer`, `prepress`, and `default`.
- API-key authentication for protected endpoints.
- Signed download URLs for original and compressed files.
- OpenAPI 3.1 spec with a Swagger UI endpoint.
- Retention cleanup command for completed and failed jobs.
- Pest feature tests covering the main API flow.

## Stack

- PHP 8.4
- Laravel 13
- MySQL
- Queue workers backed by the database driver
- Vite and Tailwind CSS 4 for the landing page and docs UI shell
- Ghostscript for the actual PDF compression step

## API Surface

### Authentication

Protected endpoints accept either:

- `X-API-Key: your-api-key`
- `Authorization: Bearer your-api-key`

### Endpoints

- `GET /api/v1/pdf-compressions`
- `POST /api/v1/pdf-compressions`
- `GET /api/v1/pdf-compressions/{public_id}`
- `GET /api/v1/pdf-compressions/{public_id}/download/original` via signed URL
- `GET /api/v1/pdf-compressions/{public_id}/download/compressed` via signed URL
- `GET /api/v1/docs`
- `GET /api/v1/docs/openapi.json`

### Example Request

```bash
curl -X POST http://localhost:8000/api/v1/pdf-compressions \
	-H "X-API-Key: your-api-key" \
	-F "pdf=@document.pdf" \
	-F "preset=ebook"
```

The API returns `202 Accepted` and a resource payload containing the compression status and time-limited download links.

## Local Setup

### Requirements

- PHP 8.4+
- Composer
- Node.js 20+
- MySQL
- Ghostscript

On macOS you can install Ghostscript with:

```bash
brew install ghostscript
```

### Installation

```bash
git clone https://github.com/SourovCodes/PDFCoreLab-api.git
cd PDFCoreLab-api
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
```

### Development

Run the full local development stack:

```bash
composer dev
```

That starts:

- the Laravel HTTP server
- the queue listener
- application log streaming
- the Vite development server

If you prefer to run pieces separately:

```bash
php artisan serve
php artisan queue:listen --tries=1 --timeout=0
npm run dev
```

## Configuration

The main PDF compression settings are exposed through environment variables:

```dotenv
GHOSTSCRIPT_BINARY=gs
PDF_COMPRESSION_SOURCE_DISK=local
PDF_COMPRESSION_OUTPUT_DISK=local
PDF_COMPRESSION_SOURCE_DIRECTORY=pdf-compressions/originals
PDF_COMPRESSION_OUTPUT_DIRECTORY=pdf-compressions/compressed
PDF_COMPRESSION_MAX_UPLOAD_SIZE_KB=51200
PDF_COMPRESSION_PROCESS_TIMEOUT_SECONDS=300
PDF_COMPRESSION_RETENTION_DAYS=7
```

## Maintenance

Delete old completed and failed compression jobs with:

```bash
php artisan pdf:cleanup
```

Override the retention window if needed:

```bash
php artisan pdf:cleanup --days=14
```

## Deployment

This repository is configured for automatic production deployments with Deployer and GitHub Actions.

### What happens on push

Every push to `main` triggers [.github/workflows/deploy.yml](.github/workflows/deploy.yml), which:

- connects to your server over SSH
- updates the code with Deployer
- installs production Composer dependencies
- runs `npm ci` and `npm run build` on the server
- runs Laravel migrations
- refreshes optimized caches
- restarts queue workers

### Files

- `deploy.php` contains the Deployer recipe
- `.github/workflows/deploy.yml` runs the deploy automatically on pushes to `main`

### Required GitHub Secrets

Add these repository or environment secrets before enabling production deploys:

- `DEPLOY_HOST`: server hostname or IP
- `DEPLOY_USER`: SSH user used for deployment
- `DEPLOY_PATH`: absolute deployment path on the server
- `DEPLOY_SSH_PRIVATE_KEY`: private key for the deploy user
- `DEPLOY_PORT`: optional SSH port, defaults to `22`
- `DEPLOY_PHP_BINARY`: optional PHP binary name such as `php8.4`
- `DEPLOY_ENV_FILE`: optional full production `.env` content used only if the shared `.env` file does not exist yet

### Server Requirements

Your target server needs:

- PHP 8.4+
- Composer
- Node.js 20+
- Ghostscript
- a database configured for the application
- a process manager for Laravel queue workers

Make sure your web server points to the Deployer-managed `current/public` directory.

### Manual Deploy

You can still deploy manually from your machine with:

```bash
composer deploy
```

## Testing

Run the test suite with:

```bash
php artisan test --compact
```

## OpenAPI Docs

When the app is running locally, the interactive API docs are available at:

- `http://localhost:8000/api/v1/docs`

The raw OpenAPI document is available at:

- `http://localhost:8000/api/v1/docs/openapi.json`

## Contributing

Please read [CONTRIBUTING.md](CONTRIBUTING.md) before opening a pull request.

## Security

Please read [SECURITY.md](SECURITY.md) for responsible disclosure instructions.

## License

This project is licensed under the [MIT License](LICENSE).
