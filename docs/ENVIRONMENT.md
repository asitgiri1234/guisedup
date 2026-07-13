# Environment Setup (Windows 11)

One-time developer environment for the Guised Up monorepo. All installs were done at
**user scope** where possible (no machine-wide changes beyond the pre-existing
PostgreSQL service and Android Studio, which require admin).

## Toolchain

| Tool          | Version     | Notes                                                            |
|---------------|-------------|------------------------------------------------------------------|
| Node.js       | 22.13.0     | pre-installed                                                    |
| npm           | 10.9.2      | pre-installed                                                    |
| Python        | 3.13.1      | pre-installed                                                    |
| Git           | 2.50.1      | pre-installed                                                    |
| PHP           | 8.3.32      | winget `PHP.PHP.8.3`; `php.ini` configured with Laravel exts     |
| Composer      | 2.10.2      | phar install on PATH                                             |
| Laravel       | 13.19.0     | `backend/`                                                       |
| Expo / RN     | SDK 57 / 0.86 | `mobile/` (React 19)                                           |
| PostgreSQL    | 18.3        | Windows service `postgresql-x64-18`, port 5432                   |
| pgvector      | see `sql/`  | extension enabled on the app database                            |
| Android Studio| 2026.1.1    | + SDK (API 35), AVD `guisedup_pixel` (Pixel 7, Android 15)       |
| Python deps   | FastAPI/Uvicorn | `python-service/venv` (`requirements.txt`)                  |

## PHP
`php.ini` created from `php.ini-development` with these extensions enabled:
`openssl, mbstring, curl, pdo_pgsql, pgsql, fileinfo, zip, pdo_sqlite, sqlite3, gd,
intl, pdo_mysql, exif`.

## PostgreSQL
- Host `127.0.0.1`, port `5432`, superuser `postgres`.
- App database: **`guisedup`**.
- **Credentials are not stored in the repo.** Set `DB_PASSWORD` (and friends) in
  `backend/.env`, which is gitignored. See `backend/.env.example` for the keys.
- `pgvector` is enabled via `sql/001_enable_pgvector.sql`.

## Android (Expo)
- SDK at `%LOCALAPPDATA%\Android\Sdk`; `ANDROID_HOME`/`ANDROID_SDK_ROOT` set (User scope).
- PATH additions: `platform-tools`, `emulator`, `cmdline-tools\latest\bin`.
- Start the emulator: `emulator -avd guisedup_pixel`, then `cd mobile && npm run android`.
- Hardware acceleration (WHPX) is available; emulator boot was verified.
- iOS is intentionally not configured (requires macOS).

## PATH additions (User scope)
- `…\AppData\Local\Composer` and `…\AppData\Roaming\Composer\vendor\bin`
- `C:\Program Files\PostgreSQL\18\bin`
- `…\Android\Sdk\platform-tools`, `…\emulator`, `…\cmdline-tools\latest\bin`

> Open a new terminal after setup so PATH / env-var changes take effect.
