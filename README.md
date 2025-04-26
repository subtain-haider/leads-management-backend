
## Getting Started Without Docker

To get started with this project:

```bash
git clone git@github.com:subtain-haider/leads-management-backend.git
cd leads-management-backend
composer install
cp .env.example .env
# Make sure to update your .env file with correct DB and other settings
php artisan key:generate 
php artisan migrate --force
# Optional: Run seeder
# php artisan db:seed --force
# Optional: Run tests
# php artisan test
php artisan serve
```

Make sure to set up your `.env` file with appropriate database and Sanctum configurations.


## Docker Setup

This application can be run using Docker for both development and production environments.

### Prerequisites

- Docker and Docker Compose installed on your system
- Git

### Quick Start With Docker

1. Clone the repository: 
```bash
git clone git@github.com:subtain-haider/leads-management-backend.git
cd leads-management-backend
```

2. Start the application:

For development:
```bash
docker compose up -d
```
The default app will be accessible at [http://localhost:8000](http://localhost:8000)  
The MySQL database runs on port `3307` by default.

> ℹ️ **Note**: If port `8000` (app) or `3307` (MySQL) are already in use on your system, you can update them in docker files. Make sure to also adjust these in your frontend or any client that connects to this backend.

### Environment

If you're using Docker, the `.env` file will be automatically created from `.env.example` on the first run.  
If you need to customize it (e.g., for SMTP, Redis, etc.), just update the `.env` file before running the containers.