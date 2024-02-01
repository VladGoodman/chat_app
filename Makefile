THIS_FILE := $(lastword $(MAKEFILE_LIST))
CONSUMERS_AMOUNT=10

start:
	docker-compose -f ./.deploy/docker-compose.yml up -d && docker-compose -f ./.deploy/docker-compose.yml up -d --scale chat=$(CONSUMERS_AMOUNT) && docker-compose -f ./.deploy/docker-compose.yml exec gateway php artisan start
logs:
	docker-compose -f ./.deploy/docker-compose.yml logs chat -f
