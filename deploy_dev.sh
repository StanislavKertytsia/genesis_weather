#!/bin/bash

set -euo pipefail

RESET_DB=false
if [ "${1:-}" == "--resetdb" ]; then
  RESET_DB=true
fi

echo "🚀 Starting project setup..."

echo "🔄 Launching Docker containers..."
docker-compose up -d --build

sleep 5

if [ "$RESET_DB" = true ]; then
  echo "🧨 Resetting PostgreSQL volume..."

  docker-compose down
  docker volume rm "${PWD##*/}_pgdata" || echo "🔍 Volume not found, skipping..."

  echo "🔄 Relaunching containers after DB reset..."
  docker-compose up -d --build
  sleep 5

  echo "🗃 Running database migrations..."
  docker-compose exec app php bin/console doctrine:migrations:migrate --no-interaction

  echo "🌱 Loading data fixtures..."
  docker-compose exec app php bin/console doctrine:fixtures:load --no-interaction
else
  echo "🗃 Running database migrations..."
  docker-compose exec app php bin/console doctrine:migrations:migrate --no-interaction
fi

echo "🧹 Clearing Symfony cache..."
docker-compose exec app php bin/console cache:clear

echo "🧹 Flushing Redis cache..."
docker-compose exec app redis-cli -h redis FLUSHALL

echo "✅ Project setup complete."
