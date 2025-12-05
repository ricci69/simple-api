# Simple PHP API

A lightweight, modular PHP API handler with versioning, POST-only requests, SQLite storage, and rate limiting.

---

## Installation

```bash
# Install dependencies
composer install

# Set permissions for data folder and script
chmod 755 data/
chmod +x create-user.sh

# Create a test user via API call
curl -X POST http://localhost/simple-api/ \
  -H "Content-Type: application/json" \
  -d '{
        "action": "register",
        "email": "test@test.com",
        "password": "123456",
        "name": "NewUser"
      }'

# Or create a user via the command line script
./create-user.sh --email test@test.com --name TestUser --password 123456 --role user
```

---

## Usage

### Login and obtain a token

```bash
curl -X POST http://localhost/simple-api/ \
  -H "Content-Type: application/json" \
  -d '{
        "action": "login",
        "email": "test@test.com",
        "password": "123456"
      }'
```

### Use token to make API calls

```bash
curl -X POST http://localhost/simple-api/ \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -d '{"action":"profile"}'
```

### Public calls (no token required)

```bash
curl -X POST http://localhost/simple-api/ -d '{"action":"ping"}'
```

---

## Automatic API Versioning

You can call different API versions by specifying the `version` parameter:

```bash
# Default version (v1)
curl -X POST http://localhost/simple-api/ -d '{"action":"ping"}'

# Version 2
curl -X POST http://localhost/simple-api/ -d '{"version":"v2","action":"ping"}'
```

---

## Project Structure

* Each API action is a class located in `src/Actions/V1/` or `V2/`.
* To add new actions, create `YourActionName.php` and call it with `{"action":"youractionname"}`.

```
src/Actions/
├── ActionInterface.php    # Contract interface
├── BaseAction.php         # Common functionality
├── V1/                    # Version 1
│   ├── PingAction.php
│   ├── LoginAction.php
│   ├── RegisterAction.php
│   ├── ProfileAction.php
│   ├── LogoutAction.php
│   └── UsersListAction.php
└── V2/                    # Version 2
    └── PingAction.php
```

---

## Configuration

All settings can be customized in `config/config.php`:

| Parameter         | Description                                      |
| ----------------- | ------------------------------------------------ |
| `db_path`         | Path to the SQLite database                      |
| `jwt_secret`      | Secret key for JWT tokens                        |
| `jwt_expiration`  | Token expiration time in seconds (3600 = 1 hour) |
| `rate_limit`      | Max requests per hour per IP (100 = default)     |
| `allowed_origins` | Allowed CORS domains (use `['*']` for all)       |
| `api_version`     | Default API version if not specified (`'v1'`)    |
| `api_versions`    | List of available versions (`['v1', 'v2']`)      |
| `debug`           | Show detailed errors (`false` in production)     |

---

This setup allows you to have a clean, modular API that is easy to extend and maintain. Add new actions or API versions without touching the core logic.
