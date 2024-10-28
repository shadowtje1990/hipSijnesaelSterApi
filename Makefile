phpstan:
	vendor/bin/phpstan analyse
.PHONY: phpstan


.PHONY: phpcs
phpcs: 
	vendor/bin/php-cs-fixer fix --no-ansi --no-interaction --dry-run --diff

phpcs-fix:
	vendor/bin/php-cs-fixer fix --no-ansi --no-interaction --diff
