#!/bin/bash

echo "🔄 Resetting Playwright for Pest browser tests..."

echo "📦 Stopping Sail containers..."
./vendor/bin/sail down

echo "🚀 Starting Sail containers..."
./vendor/bin/sail up -d

echo "⏳ Waiting for containers to be ready..."
sleep 10

echo "🔧 Installing Playwright with system dependencies in root context..."
./vendor/bin/sail root-shell -c "npx playwright install-deps"

echo "🎭 Installing Playwright in user context..."
./vendor/bin/sail npx playwright install

echo "✅ Playwright reset complete! Your browser tests should now work."
