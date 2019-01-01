.PHONY: tests
tests:
	./vendor/bin/phpunit

.PHONY: tests-ci
tests-ci:
	./vendor/bin/phpunit --coverage-text --coverage-clover=build/coverage.xml

.PHONY: phpdoc
phpdoc:
	~/.composer/vendor/bin/phpdoc -d src -t phpdoc

.PHONY: phpcs
phpcs:
	~/.composer/vendor/bin/phpcs --standard=PSR2 src

