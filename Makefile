.PHONY: install tests tests.ci phpstan.run tests.var-dump-checker.ci

install:
	composer install

tests: phpstan.run tests.var-dump-checker.ci
tests.ci: phpstan.run tests.var-dump-checker.ci phpcs-fixer-dry

phpstan.run:
	vendor/bin/phpstan analyse --level=1 src

tests.var-dump-checker.ci:
	vendor/bin/var-dump-check --symfony --exclude vendor --exclude demo .

#--------------------------------------------------------------------------------
# PHPCSFIXER
#--------------------------------------------------------------------------------
phpcs-fixer:
	vendor/bin/php-cs-fixer fix --config=.php-cs-fixer.dist.php -v

phpcs-fixer-dry:
	vendor/bin/php-cs-fixer fix --config=.php-cs-fixer.dist.php -v --dry-run

phpcs-fixer-stop:
	vendor/bin/php-cs-fixer fix --config=.php-cs-fixer.dist.php -v --dry-run --stop-on-violation
