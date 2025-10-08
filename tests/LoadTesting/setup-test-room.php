#!/usr/bin/env php
<?php

/**
 * Setup Test Room for Load Testing
 * 
 * Run this script to create a test room for Playwright tests:
 * 
 *   ./vendor/bin/sail artisan tinker --execute="require 'tests/LoadTesting/setup-test-room.php';"
 * 
 * Or just run in tinker manually.
 */

use Domain\User\Models\User;
use Domain\Campaign\Models\Campaign;
use Domain\Room\Models\Room;

echo "\n🚀 Creating test room for load testing...\n\n";

// Create test user
$user = User::factory()->create([
    'name' => 'Load Test User',
    'email' => 'loadtest@example.com',
]);
echo "✓ Created user: {$user->email}\n";

// Create campaign
$campaign = Campaign::factory()->create([
    'creator_id' => $user->id,
    'name' => 'Load Test Campaign',
]);
echo "✓ Created campaign: {$campaign->name}\n";

// Create room
$room = Room::factory()->create([
    'creator_id' => $user->id,
    'campaign_id' => $campaign->id,
    'name' => 'Load Test Room',
]);
echo "✓ Created room: {$room->name}\n";

echo "\n" . str_repeat("=", 60) . "\n";
echo "🎯 ROOM CODE: {$room->invite_code}\n";
echo str_repeat("=", 60) . "\n\n";

echo "📝 Update your test files with this room code:\n";
echo "   const ROOM_CODE = '{$room->invite_code}';\n\n";

echo "🔗 Room URL: http://localhost:8090/rooms/{$room->invite_code}/session\n\n";

echo "✅ Setup complete!\n";

