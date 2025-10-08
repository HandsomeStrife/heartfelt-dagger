# WebRTC Load Testing Suite

This directory contains Playwright-based load tests for the WebRTC room system.

## ⚠️ Important: Setup Required

**Before running tests, you MUST create a test room:**

```bash
# Option 1: Run the setup script
./vendor/bin/sail artisan tinker
>>> require 'tests/LoadTesting/setup-test-room.php';

# Option 2: Manual setup in tinker
./vendor/bin/sail artisan tinker
>>> $user = User::factory()->create();
>>> $campaign = Campaign::factory()->create(['creator_id' => $user->id]);
>>> $room = Room::factory()->create(['creator_id' => $user->id, 'campaign_id' => $campaign->id]);
>>> echo "Room code: " . $room->invite_code;

# Copy the room code and update the test files!
```

## Prerequisites

```bash
# Install Playwright and browsers (first time only)
npm install
npx playwright install chromium
```

## Running Tests

### Start Here: Simple Connection Test

```bash
# 1. Create a test room (see above)
# 2. Update ROOM_CODE in simple-connection-test.spec.js
# 3. Run the simple test first
npx playwright test tests/LoadTesting/simple-connection-test.spec.js --headed
```

This will help you verify:
- ✅ Room is accessible
- ✅ WebRTC initializes
- ✅ No authentication issues

### Full Load Tests

Once the simple test works:

```bash
# Run all load tests (headless)
npm run test:load

# Run with visible browsers (watch the action)
npm run test:load:headed

# Run with debugging tools
npm run test:load:debug

# View test report
npm run test:load:report
```

## Available Tests

### simple-connection-test.spec.js (Start here!)
- ✅ Single user connection test
- ✅ 2 users connecting to same room
- Better error messages and debugging

### room-load-test.spec.js (Advanced)
1. **10 Simultaneous Users** - Tests concurrent connections
2. **Rapid Connect/Disconnect** - Stress tests connection lifecycle
3. **Memory Leak Detection** - Monitors memory usage over time

## Troubleshooting

### "Room not found" (404 error)
→ You need to create a test room first (see Setup Required above)

### "Authentication redirect" (401/302)
→ Tests are being redirected to login
→ Solutions:
  - Make room publicly accessible
  - Add authentication to tests (see advanced guide)

### "WebRTC failed to initialize"
→ Check that Reverb server is running: `npm run peerserver`
→ Check browser console logs in test output
→ Look at screenshot: `test-results/webrtc-timeout-debug.png`

### Tests timeout after 2 minutes
→ Increase timeout in test file: `test.setTimeout(180000);` (3 minutes)
→ Check if your app is running on correct port (8090)
→ Verify Reverb WebSocket server is accessible

### Connection refused errors
→ Ensure your app is running: `npm run dev`
→ Verify port 8090 is correct in `playwright.config.js`

## Configuration

### Timeouts
Default: 60 seconds per test

Increase if needed in test file:
```javascript
test.setTimeout(120000); // 2 minutes
```

### Ports
App runs on: `http://localhost:8090` (configured in `playwright.config.js`)

### Headless Mode
- Development: `headless: false` (see browsers)
- CI/CD: `headless: true` (faster)

## CI/CD Integration

These tests can be run in GitHub Actions or other CI/CD pipelines. See `supporting-docs/WEBRTC_LOAD_TESTING_GUIDE.md` for examples.

## Next Steps

1. ✅ Create test room
2. ✅ Update ROOM_CODE in test files
3. ✅ Run simple-connection-test.spec.js
4. ✅ If that works, try room-load-test.spec.js
5. ✅ Add to CI/CD pipeline
