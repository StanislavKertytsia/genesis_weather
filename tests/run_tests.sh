#!/bin/bash

set -e

echo "🚀 Starting test runner..."

if [ -z "$1" ]; then
  echo "🧪 Running ALL PHPUnit tests..."
  docker-compose exec -T app ./vendor/bin/phpunit tests/
else
  TEST_NAME="$1"
  echo "🧪 Running PHPUnit test: $TEST_NAME"
  docker-compose exec -T app ./vendor/bin/phpunit "tests/$(find tests/ -type f -name "*${TEST_NAME}Test.php" | head -n 1)"
fi

echo "✅ Tests finished!"
