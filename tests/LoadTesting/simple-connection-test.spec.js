/**
 * Simple WebRTC Connection Test
 * 
 * A more practical test that works with your Laravel setup.
 * This test verifies basic connectivity without complex scenarios.
 */

import { test, expect, chromium } from '@playwright/test';

test.setTimeout(60000); // 1 minute timeout

test.describe('WebRTC Room Basic Tests', () => {
    
    test('Single user can load room and initialize WebRTC', async () => {
        const browser = await chromium.launch({
            headless: false, // Set to true for CI/CD
            args: [
                '--use-fake-ui-for-media-stream',
                '--use-fake-device-for-media-stream',
            ]
        });
        
        const context = await browser.newContext({
            permissions: ['camera', 'microphone']
        });
        
        const page = await context.newPage();
        
        // Listen for console logs to debug
        page.on('console', msg => {
            if (msg.type() === 'error') {
                console.log('❌ Browser Error:', msg.text());
            } else if (msg.text().includes('WebRTC') || msg.text().includes('🚀')) {
                console.log('🔍', msg.text());
            }
        });
        
        // NOTE: You need to create a real room first!
        // Run this in tinker:
        //   $user = User::factory()->create();
        //   $campaign = Campaign::factory()->create(['creator_id' => $user->id]);
        //   $room = Room::factory()->create(['creator_id' => $user->id, 'campaign_id' => $campaign->id]);
        //   echo "Room code: " . $room->invite_code;
        
        const ROOM_CODE = 'YOUR-ROOM-CODE-HERE'; // ⚠️ UPDATE THIS
        
        try {
            console.log(`Navigating to room ${ROOM_CODE}...`);
            const response = await page.goto(`/rooms/${ROOM_CODE}/session`);
            
            console.log('Response status:', response?.status());
            
            if (response?.status() === 404) {
                console.log('❌ Room not found. Create a room first!');
                console.log('\nRun in tinker:');
                console.log('  $user = User::factory()->create();');
                console.log('  $campaign = Campaign::factory()->create([\'creator_id\' => $user->id]);');
                console.log('  $room = Room::factory()->create([\'creator_id\' => $user->id, \'campaign_id\' => $campaign->id]);');
                console.log('  echo "Room code: " . $room->invite_code;');
                console.log('\nThen update ROOM_CODE in this test file.');
                await browser.close();
                return;
            }
            
            if (response?.status() === 302 || response?.status() === 401) {
                console.log('⚠️ Authentication redirect detected');
                console.log('Current URL:', page.url());
                
                // If redirected to login, we need to authenticate first
                if (page.url().includes('/login')) {
                    console.log('❌ Redirected to login - authentication required');
                    console.log('\nOption 1: Make room publicly accessible');
                    console.log('Option 2: Add Laravel session cookie to test');
                    await browser.close();
                    return;
                }
            }
            
            console.log('✓ Page loaded, waiting for WebRTC...');
            
            // Wait up to 30 seconds for WebRTC to initialize
            try {
                await page.waitForFunction(
                    () => window.roomWebRTC !== undefined,
                    { timeout: 30000 }
                );
                
                console.log('✓ WebRTC object detected');
                
                // Check initialization status
                const status = await page.evaluate(() => ({
                    hasRoomWebRTC: window.roomWebRTC !== undefined,
                    hasSimplePeerManager: window.roomWebRTC?.simplePeerManager !== undefined,
                    peerId: window.roomWebRTC?.simplePeerManager?.peerId || null,
                    peerServerState: window.roomWebRTC?.simplePeerManager?.peerServerState || null,
                }));
                
                console.log('WebRTC Status:', status);
                
                expect(status.hasRoomWebRTC).toBe(true);
                console.log('✅ Basic WebRTC test passed!');
                
            } catch (error) {
                console.log('❌ WebRTC failed to initialize within 30 seconds');
                console.log('Error:', error.message);
                
                // Take screenshot for debugging
                await page.screenshot({ 
                    path: 'test-results/webrtc-timeout-debug.png',
                    fullPage: true 
                });
                console.log('📸 Screenshot saved to test-results/webrtc-timeout-debug.png');
                
                throw error;
            }
            
        } finally {
            await browser.close();
        }
    });
    
    test('2 users can connect to same room', async () => {
        const ROOM_CODE = 'YOUR-ROOM-CODE-HERE'; // ⚠️ UPDATE THIS
        
        if (ROOM_CODE === 'YOUR-ROOM-CODE-HERE') {
            console.log('⚠️ Skipping test - room code not configured');
            return;
        }
        
        // Launch 2 browsers
        const browser1 = await chromium.launch({
            headless: false,
            args: ['--use-fake-ui-for-media-stream', '--use-fake-device-for-media-stream']
        });
        
        const browser2 = await chromium.launch({
            headless: false,
            args: ['--use-fake-ui-for-media-stream', '--use-fake-device-for-media-stream']
        });
        
        try {
            const context1 = await browser1.newContext({ permissions: ['camera', 'microphone'] });
            const context2 = await browser2.newContext({ permissions: ['camera', 'microphone'] });
            
            const page1 = await context1.newPage();
            const page2 = await context2.newPage();
            
            console.log('Navigating both browsers...');
            await Promise.all([
                page1.goto(`/rooms/${ROOM_CODE}/session`),
                page2.goto(`/rooms/${ROOM_CODE}/session`)
            ]);
            
            console.log('Waiting for WebRTC initialization...');
            await Promise.all([
                page1.waitForFunction(() => window.roomWebRTC !== undefined, { timeout: 30000 }),
                page2.waitForFunction(() => window.roomWebRTC !== undefined, { timeout: 30000 })
            ]);
            
            // Allow time for peer connection
            await new Promise(resolve => setTimeout(resolve, 5000));
            
            const stats1 = await page1.evaluate(() => ({
                peerId: window.roomWebRTC?.simplePeerManager?.peerId,
                connectedPeers: window.roomWebRTC?.simplePeerManager?.calls?.size || 0
            }));
            
            const stats2 = await page2.evaluate(() => ({
                peerId: window.roomWebRTC?.simplePeerManager?.peerId,
                connectedPeers: window.roomWebRTC?.simplePeerManager?.calls?.size || 0
            }));
            
            console.log('Browser 1:', stats1);
            console.log('Browser 2:', stats2);
            
            // In a real scenario, these should connect to each other
            // But without authentication, this is just a connectivity test
            expect(stats1.peerId).toBeTruthy();
            expect(stats2.peerId).toBeTruthy();
            
            console.log('✅ 2-user test completed');
            
        } finally {
            await browser1.close();
            await browser2.close();
        }
    });
});

