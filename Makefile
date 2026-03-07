up:
	docker compose up -d --build

deps:
	docker compose exec php composer install

db:
	docker compose exec php php bin/console doctrine:database:create --if-not-exists
	docker compose exec php php bin/console doctrine:migrations:migrate -n

dev: up deps db

# ---- Testy E2E (Behat) ----
# Szybkie testy jądra (API/Kernel) - bez przeglądarki
test-api:
	DEFAULT_URI=http://127.0.0.1:8000 APP_ENV=test APP_SECRET=test-secret vendor/bin/behat --suite=api

# Pełne testy E2E z przeglądarką (Panther + ChromeDriver)
test-e2e:
	DEFAULT_URI=http://127.0.0.1:8000 APP_ENV=test APP_SECRET=test-secret PANTHER_NO_SANDBOX=1 PANTHER_CHROME_ARGUMENTS='--no-sandbox --disable-dev-shm-usage --headless' vendor/bin/behat --suite=e2e

# Wszystkie testy
test: test-api test-e2e
