#!/bin/bash
# Runs after `wp-env start` to set up test users and admin password.
# Safe to re-run: user creation failures (already exists) are ignored.
set -e

WP_ENV="./node_modules/.bin/wp-env"

echo "Creating test users..."
$WP_ENV run cli wp user create author author@local.test --role=author --user_pass=author --allow-root 2>/dev/null || true
$WP_ENV run cli wp user create editor editor@local.test --role=editor --user_pass=editor --allow-root 2>/dev/null || true
$WP_ENV run cli wp user create subscriber subscriber@local.test --role=subscriber --user_pass=subscriber --allow-root 2>/dev/null || true

echo "wp-env setup complete."
