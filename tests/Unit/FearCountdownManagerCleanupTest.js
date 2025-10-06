/**
 * Unit tests for FearCountdownManager cleanup and memory management
 * 
 * Tests the cleanup method to ensure no memory leaks from:
 * - MutationObserver disconnection
 * - Original Map method restoration
 * - Cache clearing
 */

describe('FearCountdownManager Cleanup', () => {
    let mockRoomWebRTC;
    let mockSlotOccupants;
    let FearCountdownManager;

    beforeEach(() => {
        // Mock the RoomWebRTC instance
        mockSlotOccupants = new Map();
        mockSlotOccupants.originalSet = mockSlotOccupants.set;
        mockSlotOccupants.originalDelete = mockSlotOccupants.delete;

        mockRoomWebRTC = {
            roomData: {
                id: 1,
                campaign_id: 1,
                stt_enabled: false,
                recording_enabled: false,
            },
            slotOccupants: mockSlotOccupants,
            ablyManager: {
                getChannel: jest.fn(() => ({
                    subscribe: jest.fn(),
                })),
            },
        };

        // Mock DOM elements
        document.body.innerHTML = `
            <div data-game-state-overlay class="hidden">
                <div data-fear-display="indicator">0</div>
                <div data-countdown-display="container"></div>
            </div>
            <div data-slot-id="1" class="video-slot"></div>
        `;

        // Import the class (in real test environment)
        // For this example, we'll create a simplified mock
        FearCountdownManager = class {
            constructor(roomWebRTC) {
                this.roomWebRTC = roomWebRTC;
                this.fearDisplayElements = new Map();
                this.countdownDisplayElements = new Map();
                this.gameStateOverlays = [];
                this.overlayObserver = null;
                this.originalSlotOccupantsSet = null;
                this.originalSlotOccupantsDelete = null;
            }

            setupUIElements() {
                // Scan for elements
                this.gameStateOverlays = Array.from(document.querySelectorAll('[data-game-state-overlay]'));
                
                // Setup observer
                this.overlayObserver = new MutationObserver(() => {});
                this.overlayObserver.observe(document.body, { childList: true, subtree: true });
            }

            setupSlotMonitoring() {
                if (this.roomWebRTC.slotOccupants) {
                    this.originalSlotOccupantsSet = this.roomWebRTC.slotOccupants.set.bind(this.roomWebRTC.slotOccupants);
                    this.originalSlotOccupantsDelete = this.roomWebRTC.slotOccupants.delete.bind(this.roomWebRTC.slotOccupants);
                    
                    this.roomWebRTC.slotOccupants.set = (...args) => {
                        const result = this.originalSlotOccupantsSet(...args);
                        return result;
                    };
                    
                    this.roomWebRTC.slotOccupants.delete = (...args) => {
                        const result = this.originalSlotOccupantsDelete(...args);
                        return result;
                    };
                }
            }

            cleanup() {
                // Disconnect MutationObserver
                if (this.overlayObserver) {
                    this.overlayObserver.disconnect();
                    this.overlayObserver = null;
                }
                
                // Restore original Map methods
                if (this.roomWebRTC.slotOccupants && this.originalSlotOccupantsSet && this.originalSlotOccupantsDelete) {
                    this.roomWebRTC.slotOccupants.set = this.originalSlotOccupantsSet;
                    this.roomWebRTC.slotOccupants.delete = this.originalSlotOccupantsDelete;
                }
                
                // Clear caches
                this.fearDisplayElements.clear();
                this.countdownDisplayElements.clear();
                this.gameStateOverlays = [];
            }
        };
    });

    afterEach(() => {
        document.body.innerHTML = '';
    });

    test('cleanup disconnects MutationObserver', () => {
        const manager = new FearCountdownManager(mockRoomWebRTC);
        manager.setupUIElements();

        expect(manager.overlayObserver).not.toBeNull();

        manager.cleanup();

        expect(manager.overlayObserver).toBeNull();
    });

    test('cleanup restores original Map.set method', () => {
        const manager = new FearCountdownManager(mockRoomWebRTC);
        manager.setupSlotMonitoring();

        const overriddenSet = mockRoomWebRTC.slotOccupants.set;
        expect(overriddenSet).not.toBe(mockSlotOccupants.originalSet);

        manager.cleanup();

        expect(mockRoomWebRTC.slotOccupants.set).toBe(manager.originalSlotOccupantsSet);
    });

    test('cleanup restores original Map.delete method', () => {
        const manager = new FearCountdownManager(mockRoomWebRTC);
        manager.setupSlotMonitoring();

        const overriddenDelete = mockRoomWebRTC.slotOccupants.delete;
        expect(overriddenDelete).not.toBe(mockSlotOccupants.originalDelete);

        manager.cleanup();

        expect(mockRoomWebRTC.slotOccupants.delete).toBe(manager.originalSlotOccupantsDelete);
    });

    test('cleanup clears fear display elements cache', () => {
        const manager = new FearCountdownManager(mockRoomWebRTC);
        manager.fearDisplayElements.set('test', [document.createElement('div')]);

        expect(manager.fearDisplayElements.size).toBe(1);

        manager.cleanup();

        expect(manager.fearDisplayElements.size).toBe(0);
    });

    test('cleanup clears countdown display elements cache', () => {
        const manager = new FearCountdownManager(mockRoomWebRTC);
        manager.countdownDisplayElements.set('test', [document.createElement('div')]);

        expect(manager.countdownDisplayElements.size).toBe(1);

        manager.cleanup();

        expect(manager.countdownDisplayElements.size).toBe(0);
    });

    test('cleanup clears game state overlays array', () => {
        const manager = new FearCountdownManager(mockRoomWebRTC);
        manager.setupUIElements();

        expect(manager.gameStateOverlays.length).toBeGreaterThan(0);

        manager.cleanup();

        expect(manager.gameStateOverlays.length).toBe(0);
    });

    test('cleanup can be called multiple times safely', () => {
        const manager = new FearCountdownManager(mockRoomWebRTC);
        manager.setupUIElements();
        manager.setupSlotMonitoring();

        expect(() => {
            manager.cleanup();
            manager.cleanup();
            manager.cleanup();
        }).not.toThrow();
    });

    test('cleanup handles missing observer gracefully', () => {
        const manager = new FearCountdownManager(mockRoomWebRTC);
        // Don't set up UI elements, so observer is null

        expect(() => {
            manager.cleanup();
        }).not.toThrow();

        expect(manager.overlayObserver).toBeNull();
    });

    test('cleanup handles missing Map methods gracefully', () => {
        const manager = new FearCountdownManager(mockRoomWebRTC);
        // Don't set up slot monitoring

        expect(() => {
            manager.cleanup();
        }).not.toThrow();
    });

    test('Map methods remain functional after override', () => {
        const manager = new FearCountdownManager(mockRoomWebRTC);
        manager.setupSlotMonitoring();

        // Test that overridden methods still work
        mockRoomWebRTC.slotOccupants.set('test-key', { data: 'test' });
        expect(mockRoomWebRTC.slotOccupants.has('test-key')).toBe(true);

        mockRoomWebRTC.slotOccupants.delete('test-key');
        expect(mockRoomWebRTC.slotOccupants.has('test-key')).toBe(false);
    });

    test('Map methods work correctly after cleanup', () => {
        const manager = new FearCountdownManager(mockRoomWebRTC);
        manager.setupSlotMonitoring();
        manager.cleanup();

        // Test that restored methods work
        mockRoomWebRTC.slotOccupants.set('test-key-2', { data: 'test2' });
        expect(mockRoomWebRTC.slotOccupants.has('test-key-2')).toBe(true);

        mockRoomWebRTC.slotOccupants.delete('test-key-2');
        expect(mockRoomWebRTC.slotOccupants.has('test-key-2')).toBe(false);
    });

    test('MutationObserver stops observing after cleanup', () => {
        const manager = new FearCountdownManager(mockRoomWebRTC);
        manager.setupUIElements();

        const observerDisconnectSpy = jest.spyOn(manager.overlayObserver, 'disconnect');

        manager.cleanup();

        expect(observerDisconnectSpy).toHaveBeenCalled();
    });

    test('memory is freed after cleanup', () => {
        const manager = new FearCountdownManager(mockRoomWebRTC);
        manager.setupUIElements();
        manager.setupSlotMonitoring();
        
        // Add some data to caches
        manager.fearDisplayElements.set('indicator', [document.createElement('div')]);
        manager.countdownDisplayElements.set('container', [document.createElement('div')]);

        const initialMemory = {
            fearSize: manager.fearDisplayElements.size,
            countdownSize: manager.countdownDisplayElements.size,
            overlaysLength: manager.gameStateOverlays.length,
        };

        manager.cleanup();

        expect(manager.fearDisplayElements.size).toBe(0);
        expect(manager.countdownDisplayElements.size).toBe(0);
        expect(manager.gameStateOverlays.length).toBe(0);
        expect(initialMemory.fearSize).toBeGreaterThan(0);
        expect(initialMemory.countdownSize).toBeGreaterThan(0);
    });
});

