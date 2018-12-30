.PHONY: tests
tests:
	./vendor/bin/phpunit

.PHONY: phpdoc
phpdoc:
	~/.composer/vendor/bin/phpdoc -d src -t phpdoc

