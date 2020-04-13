cs: ## Fix PHP CS
	./vendor/bin/php-cs-fixer fix --verbose --rules=@Symfony,ordered_imports src/
	./vendor/bin/php-cs-fixer fix --verbose --rules=@Symfony,ordered_imports Tests/

test: ## Run test suite
	./vendor/bin/phpunit Tests

phpstan: ## Run PHPStan
	./vendor/bin/phpstan analyse --level=5

.PHONY: help

help: ## Display this help
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-8s\033[0m %s\n", $$1, $$2}'

.DEFAULT_GOAL := help
