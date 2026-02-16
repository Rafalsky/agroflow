up:
	docker compose up -d --build

deps:
	docker compose exec php composer install

db:
	docker compose exec php php bin/console doctrine:database:create --if-not-exists
	docker compose exec php php bin/console doctrine:migrations:migrate -n

dev: up deps db
