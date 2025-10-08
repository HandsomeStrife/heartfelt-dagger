/**
 * WebRTC Room Load Testing with Playwright
 * 
 * These tests spawn multiple browser instances to simulate
 * real load conditions on the WebRTC room system.
 * 
 * Run with: npx playwright test tests/LoadTesting/
 */

import { test, expect, chromium } from '@playwright/test';

// Configure test timeouts for long-running tests
test.setTimeout(120000); // 2 minutes per test

test.describe('WebRTC Room Load Testing', () => {
    
    test('10 simultaneous users joining room', async () => {
        const browsers = [];
        const contexts = [];
        const pages = [];
        
        // NOTE: Replace with a real room code from your database
        // You can create one via: php artisan tinker
        // >>> $room = Room::factory()->create(); echo $room->invite_code;
        const ROOM_CODE = 'LOAD-TEST'; // ⚠️ UPDATE THIS WITH REAL ROOM CODE
        
        try {
            // Spawn 10 browsers with fake media devices
            for (let i = 0; i < 10; i++) {
                const browser = await chromium.launch({
                    headless: true,
                    args: [
                        '--use-fake-ui-for-media-stream',
                        '--use-fake-device-for-media-stream',
                        '--disable-web-security', // For local testing
                    ]
                });
                browsers.push(browser);
                
                const context = await browser.newContext({
                    permissions: ['camera', 'microphone'],
                    viewport: { width: 1280, height: 720 }
                });
                contexts.push(context);
                
                const page = await context.newPage();
                pages.push(page);
            }
            
            console.log('✓ Spawned 10 browsers');
            
            // Navigate all users to room simultaneously
            await Promise.all(
                pages.map((page, index) => {
                    console.log(`Navigating browser ${index + 1}...`);
                    return page.goto(`/rooms/${ROOM_CODE}/session`);
                })
            );
            
            console.log('✓ All browsers navigated to room');
            
            // Wait for WebRTC initialization on all browsers
            await Promise.all(
                pages.map((page, index) => 
                    page.waitForFunction(() => {
                        return window.roomWebRTC !== undefined;
                    }, { timeout: 15000 })
                        .then(() => console.log(`Browser ${index + 1} initialized`))
                )
            );
            
            console.log('✓ All browsers initialized WebRTC');
            
            // Allow time for peer connections to establish
            await new Promise(resolve => setTimeout(resolve, 10000));
            
            // Collect metrics from all browsers
            const metrics = await Promise.all(
                pages.map(async (page, index) => {
                    const stats = await page.evaluate(() => {
                        return {
                            peerId: window.roomWebRTC?.simplePeerManager?.peerId,
                            connectedPeers: window.roomWebRTC?.simplePeerManager?.calls?.size || 0,
                            connectionState: window.roomWebRTC?.simplePeerManager?.peerServerState,
                            isInitialized: window.roomWebRTC?.isInitialized || false
                        };
                    });
                    
                    console.log(`Browser ${index + 1} stats:`, stats);
                    return stats;
                })
            );
            
            // Verify all browsers initialized
            for (const metric of metrics) {
                expect(metric.isInitialized).toBe(true);
                expect(metric.peerId).toBeTruthy();
            }
            
            console.log('✅ Load test completed successfully');
            
        } finally {
            // Cleanup: Close all browsers
            for (const browser of browsers) {
                await browser.close();
            }
            console.log('✓ Cleanup complete');
        }
    });
    
    test('Rapid connect/disconnect stress test', async () => {
        const browser = await chromium.launch({
            args: [
                '--use-fake-ui-for-media-stream',
                '--use-fake-device-for-media-stream'
            ]
        });
        const context = await browser.newContext({
            permissions: ['camera', 'microphone']
        });
        const page = await context.newPage();
        
        const ROOM_CODE = 'STRESS-TEST';
        await page.goto(`/rooms/${ROOM_CODE}/session`);
        
        // Wait for initial load
        await page.waitForFunction(() => window.roomWebRTC !== undefined);
        await page.waitForTimeout(2000);
        
        // Perform 5 disconnect/reconnect cycles
        for (let i = 0; i < 5; i++) {
            console.log(`Cycle ${i + 1}/5: Disconnecting...`);
            
            // Disconnect
            await page.evaluate(() => {
                if (window.roomWebRTC) {
                    window.roomWebRTC.destroy();
                }
            });
            
            await page.waitForTimeout(1000);
            
            console.log(`Cycle ${i + 1}/5: Reconnecting...`);
            
            // Reload page (simulates reconnect)
            await page.reload();
            await page.waitForFunction(() => window.roomWebRTC !== undefined);
            await page.waitForTimeout(2000);
            
            // Verify system is healthy
            const isHealthy = await page.evaluate(() => {
                return window.roomWebRTC?.simplePeerManager?.peer !== null;
            });
            
            expect(isHealthy).toBe(true);
            console.log(`✓ Cycle ${i + 1} completed`);
        }
        
        console.log('✅ Stress test completed');
        await browser.close();
    });
    
    test('Memory leak detection over time', async () => {
        const browser = await chromium.launch({
            args: [
                '--use-fake-ui-for-media-stream',
                '--use-fake-device-for-media-stream',
                '--enable-precise-memory-info' // Enable memory API
            ]
        });
        const context = await browser.newContext({
            permissions: ['camera', 'microphone']
        });
        const page = await context.newPage();
        
        const ROOM_CODE = 'MEMORY-TEST';
        await page.goto(`/rooms/${ROOM_CODE}/session`);
        await page.waitForFunction(() => window.roomWebRTC !== undefined);
        await page.waitForTimeout(3000);
        
        // Get baseline memory
        const initialMemory = await page.evaluate(() => {
            if (performance.memory) {
                return performance.memory.usedJSHeapSize;
            }
            return 0;
        });
        
        console.log(`Initial memory: ${(initialMemory / 1024 / 1024).toFixed(2)} MB`);
        
        // Simulate activity: create and destroy 20 mock peer connections
        for (let i = 0; i < 20; i++) {
            await page.evaluate((index) => {
                const testPeerId = `test-peer-${index}`;
                
                // Simulate adding a peer
                if (window.roomWebRTC?.slotOccupants) {
                    window.roomWebRTC.slotOccupants.set(`slot-${index}`, {
                        slotId: `slot-${index}`,
                        peerId: testPeerId,
                        userId: index,
                        characterName: `Test ${index}`,
                        stream: null
                    });
                }
                
                // Simulate removing the peer
                setTimeout(() => {
                    if (window.roomWebRTC?.slotOccupants) {
                        window.roomWebRTC.slotOccupants.delete(`slot-${index}`);
                    }
                }, 500);
            }, i);
            
            await page.waitForTimeout(600);
        }
        
        // Allow time for cleanup
        await page.waitForTimeout(3000);
        
        // Check final memory
        const finalMemory = await page.evaluate(() => {
            if (performance.memory) {
                return performance.memory.usedJSHeapSize;
            }
            return 0;
        });
        
        console.log(`Final memory: ${(finalMemory / 1024 / 1024).toFixed(2)} MB`);
        
        const memoryIncrease = (finalMemory - initialMemory) / (1024 * 1024);
        console.log(`Memory increase: ${memoryIncrease.toFixed(2)} MB`);
        
        // Memory shouldn't have grown more than 30MB
        expect(memoryIncrease).toBeLessThan(30);
        
        // Verify no lingering connections
        const cleanup = await page.evaluate(() => ({
            occupants: window.roomWebRTC?.slotOccupants?.size || 0,
            calls: window.roomWebRTC?.simplePeerManager?.calls?.size || 0
        }));
        
        console.log('Cleanup check:', cleanup);
        expect(cleanup.occupants).toBe(0);
        expect(cleanup.calls).toBe(0);
        
        console.log('✅ Memory leak test passed');
        await browser.close();
    });
});

