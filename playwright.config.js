/**
 * Playwright Configuration for WebRTC Load Testing
 * 
 * @see https://playwright.dev/docs/test-configuration
 */

import { defineConfig, devices } from '@playwright/test';

export default defineConfig({
  testDir: './tests/LoadTesting',
  
  // Maximum time one test can run
  timeout: 120 * 1000,
  
  // Run tests in files in parallel
  fullyParallel: false, // WebRTC tests should run sequentially to avoid port conflicts
  
  // Fail the build on CI if you accidentally left test.only in the source code
  forbidOnly: !!process.env.CI,
  
  // Retry on CI only
  retries: process.env.CI ? 2 : 0,
  
  // Opt out of parallel tests on CI
  workers: process.env.CI ? 1 : 1,
  
  // Reporter to use
  reporter: [
    ['html'],
    ['list']
  ],
  
  // Shared settings for all the projects below
  use: {
    // Base URL to use in actions like `await page.goto('/')`
    baseURL: 'http://localhost:8090',
    
    // Collect trace when retrying the failed test
    trace: 'on-first-retry',
    
    // Screenshot on failure
    screenshot: 'only-on-failure',
    
    // Video on failure
    video: 'retain-on-failure',
  },
  
  // Configure projects for major browsers
  projects: [
    {
      name: 'chromium',
      use: { 
        ...devices['Desktop Chrome'],
        // Enable WebRTC fake devices
        launchOptions: {
          args: [
            '--use-fake-ui-for-media-stream',
            '--use-fake-device-for-media-stream',
            '--enable-precise-memory-info'
          ]
        }
      },
    },
  ],
  
  // Run your local dev server before starting the tests
  // Uncomment if you want Playwright to automatically start the server
  // webServer: {
  //   command: 'npm run dev',
  //   port: 80,
  //   reuseExistingServer: !process.env.CI,
  // },
});

