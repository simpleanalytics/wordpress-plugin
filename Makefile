WP_VERSION  ?= 6.8
WP_URL       = http://localhost:8100
WP_PATH      = /var/www/html
NETWORK      = simpleanalytics-wptest
WP_CLI       = docker run --rm --network $(NETWORK) -v $(PWD)/tmp/wp:$(WP_PATH) wordpress:cli

.PHONY: test-env test test-reset _wp-install

## Start containers + install WordPress (idempotent)
test-env: tmp/wp/wp-login.php
	docker compose up -d --build --wait
	$(MAKE) _wp-install

## Run the test suite
test:
	./vendor/bin/phpunit --colors=always

## Tear everything down and start fresh
test-reset:
	docker compose down -v
	rm -rf tmp/wp
	$(MAKE) test-env

# ── internals ──────────────────────────────────────────────────────────────────

tmp/wp/wp-login.php:
	mkdir -p tmp/wp
	curl -s -O https://wordpress.org/wordpress-$(WP_VERSION).tar.gz
	tar -xzf wordpress-$(WP_VERSION).tar.gz --strip-components=1 -C tmp/wp
	rm -f wordpress-$(WP_VERSION).tar.gz

_wp-install:
	rm -f tmp/wp/wp-config.php
	$(WP_CLI) wp config create \
		--dbname=wordpress --dbuser=root --dbpass=root \
		--dbhost=mysql --path=$(WP_PATH) --skip-check
	$(WP_CLI) wp core install \
		--url=$(WP_URL) --title="Test Site" \
		--admin_user=admin --admin_password=admin \
		--admin_email=test@example.com \
		--path=$(WP_PATH) --skip-email --allow-root
	$(WP_CLI) wp user create author author@local.test \
		--role=author --user_pass=author --path=$(WP_PATH) --allow-root
	$(WP_CLI) wp user create editor editor@local.test \
		--role=editor --user_pass=editor --path=$(WP_PATH) --allow-root
	$(WP_CLI) wp user create subscriber subscriber@local.test \
		--role=subscriber --user_pass=subscriber --path=$(WP_PATH) --allow-root
