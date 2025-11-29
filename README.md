# Todo + Comments API

A RESTful API for managing Todos and Comments, built with Symfony 8.0 and PostgreSQL.

## Table of Contents

- [Requirements](#requirements)
- [Startup Instructions](#startup-instructions)
- [Test Run Instructions](#test-run-instructions)
- [API Endpoints](#api-endpoints)
- [Example curl Requests](#example-curl-requests)
- [Notes on Difficulties](#notes-on-difficulties)
- [Production Improvements](#production-improvements)

## Requirements

- Docker & Docker Compose
- PHP 8.4+ (for local development without Docker)
- Composer (for local development without Docker)

## Startup Instructions

### Using Docker (Recommended)

1. **Clone the repository and navigate to the project directory:**
   ```bash
   cd todo
   ```

2. **Run the initial setup (first run):**
   ```bash
   make setup
   ```
   This command performs the complete first-time setup:
   - Starts Docker containers (PostgreSQL, PHP app, Nginx)
   - Installs Composer dependencies
   - Runs database migrations
   - Installs and compiles frontend assets

3. **Access the application:**
   - API is available at: `http://localhost:8080`

4. **Stop the containers:**
   ```bash
   make down
   ```

### Other Makefile Commands

- `make up` - Start Docker containers only (use after initial setup)
- `make sh` - Open a shell inside the PHP container
- `make migrate` - Run database migrations
- `make test` - Run PHPUnit tests (locally)

### Local Development (Without Docker)

1. **Install dependencies:**
   ```bash
   composer install
   ```

2. **Configure environment:**
   - Copy `.env` to `.env.local` and update `DATABASE_URL` to point to your PostgreSQL instance

3. **Create database and run migrations:**
   ```bash
   bin/console doctrine:database:create
   bin/console doctrine:migrations:migrate
   ```

4. **Start the Symfony development server:**
   ```bash
   symfony serve
   ```

## Test Run Instructions

### Running Tests Locally

Tests use SQLite by default (configured in `.env.test`) for quick execution without external dependencies.

1. **Install dependencies (if not already done):**
   ```bash
   composer install
   ```

2. **Create test database and run migrations:**
   ```bash
   bin/console doctrine:database:create --env=test --if-not-exists
   bin/console doctrine:migrations:migrate --env=test --no-interaction
   ```

3. **Run all tests:**
   ```bash
   ./vendor/bin/phpunit
   ```
   Or use the Makefile shortcut:
   ```bash
   make test
   ```

### Running Tests in Docker

```bash
make sh
# Inside the container:
bin/console doctrine:database:create --env=test --if-not-exists
bin/console doctrine:migrations:migrate --env=test --no-interaction
./vendor/bin/phpunit
```

### Code Style

Check code style:
```bash
composer cs:check
```

Fix code style issues:
```bash
composer cs:fix
```

## API Endpoints

| Method | Endpoint                   | Description              |
|--------|----------------------------|--------------------------|
| GET    | `/todos`                   | List all todos           |
| POST   | `/todos`                   | Create a new todo        |
| GET    | `/todos/{id}`              | Get a todo by ID         |
| PATCH  | `/todos/{id}`              | Update a todo            |
| GET    | `/todos/{id}/comments`     | List comments for a todo |
| POST   | `/todos/{id}/comments`     | Add a comment to a todo  |

See `spec/openapi.yaml` for the complete API specification.

## Example curl Requests

### List All Todos

```bash
curl -X GET http://localhost:8080/todos \
  -H "Accept: application/json"
```

### Create a Todo

```bash
curl -X POST http://localhost:8080/todos \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"title": "Buy milk"}'
```

**Response (201 Created):**
```json
{
  "id": "550e8400-e29b-41d4-a716-446655440000",
  "title": "Buy milk",
  "status": "open",
  "createdAt": "2025-11-30T00:00:00+00:00",
  "updatedAt": "2025-11-30T00:00:00+00:00"
}
```

### Get a Todo by ID

```bash
curl -X GET http://localhost:8080/todos/{id} \
  -H "Accept: application/json"
```

### Update a Todo

```bash
curl -X PATCH http://localhost:8080/todos/{id} \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"title": "Buy bread", "status": "done"}'
```

**Note:** Status can be `open` or `done`.

### List Comments for a Todo

```bash
curl -X GET http://localhost:8080/todos/{id}/comments \
  -H "Accept: application/json"
```

### Add a Comment to a Todo

```bash
curl -X POST http://localhost:8080/todos/{id}/comments \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"message": "This is a comment"}'
```

**Response (201 Created):**
```json
{
  "id": "550e8400-e29b-41d4-a716-446655440001",
  "todoId": "550e8400-e29b-41d4-a716-446655440000",
  "message": "This is a comment",
  "createdAt": "2025-11-30T00:00:00+00:00"
}
```

**Note:** Comments cannot be added to todos with status `done` (returns 409 Conflict).

### Error Responses

**Validation Error (422):**
```json
{
  "code": "validation.failed",
  "message": "Validation failed",
  "violations": [
    {
      "propertyPath": "title",
      "message": "This value should not be blank.",
      "code": "c1051bb4-d103-4f74-8988-acbcafc7fdc3"
    }
  ]
}
```

**Not Found (404):**
```json
{
  "code": "not_found",
  "message": "Todo not found"
}
```

**Business Rule Violation (409):**
```json
{
  "code": "todo.comment.forbidden_on_done",
  "message": "Cannot add a comment to a completed Todo."
}
```

## Production Improvements

### Security

- [ ] Add Users
- [ ] Add authentication (JWT, OAuth2, or API keys)
- [ ] Implement rate limiting to prevent abuse
- [ ] Add CORS configuration for frontend security
- [ ] Add input sanitization for XSS prevention
- [ ] Add Redis caching for frequently accessed data
- [ ] Implement database query optimization and indexing
- [ ] Implement pagination for list endpoints
- [ ] Add comprehensive logging and monitoring
- [ ] Set up CI/CD pipeline with automated deployments
- [ ] Add static analysis (PHPStan)
- [ ] Add API versioning (URL or header-based)
- [ ] Add request ID tracking for debugging
- [ ] Implement soft deletes for todos
- [ ] Add filtering and sorting options for list endpoints

## API Documentation

Full OpenAPI 3.1 specification is available at `spec/openapi.yaml`.
